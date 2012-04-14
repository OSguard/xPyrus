<?php
#
# xPyrus - Framework for Community and knowledge exchange
# Copyright (C) 2003-2008 UniHelp e.V., Germany
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, only version 3 of the
# License.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program. If not, see http://www.gnu.org/licenses/agpl.txt
#
/*
 * Created on 14.06.2006
 *
 */
require_once CORE_DIR . '/businesslogic/business_logic_controller.php';

require_once CORE_DIR.'/models/forum/category_model.php';
require_once CORE_DIR.'/models/forum/forum_model.php';
require_once CORE_DIR.'/models/forum/thread_model.php';
require_once CORE_DIR.'/models/forum/thread_entry_model.php';
require_once CORE_DIR.'/models/forum/forum_read.php';
require_once MODEL_DIR . '/forum/thread_model_feed_proxy.php';
require_once CORE_DIR.'/models/news/news_entry.php';

require_once CORE_DIR . '/utils/client_infos.php';
require_once CORE_DIR . '/utils/attachment_handler.php';
require_once CORE_DIR . '/utils/global_ipc.php';
require_once CORE_DIR . '/utils/forum_ipc.php';

// load some useful constants for working on <form>-input
require_once CORE_DIR . '/constants/form_constants.php';
require_once CORE_DIR . '/constants/value_constants.php';

define('FORUM_TEMPLATE_DIR', 'modules/forum/');

class ForumBusinessLogicController extends BusinessLogicController {
    /**
     * @var array of int
     * holds the number to display on a threads/entrys page
     */
    protected $entriesPerPage;
    
    public function __construct($ajaxView = false) {
        parent::__construct($ajaxView);
        $this->entriesPerPage['entries'] = V_FORUM_THREAD_ENTRIES_PER_PAGE;
        $this->entriesPerPage['thread'] = V_FORUM_THREADS_PER_PAGE; 
    }   


   /** 
     * default method: we want to see the overview of all Forums 
     */
    protected function getDefaultMethod() {
        return 'viewAllForum';
    }
    
    /**
      * List of al methods who ar allowed
      */
    public function getAllowedMethods() {
        $array = parent::getAllowedMethods();
        array_push($array, 
        
            /* thread entry actions */
            'addThreadEntry',
            'editThreadEntry',
            'delThreadEntry',
            'viewThreadEntries',
            'viewThreadEntry',
            'viewThreadEntryHistory',
			'viewThreadLastRead',
            'reportThreadEntry',
            
            /* thread actions */
            'addThread',
            'editThread',
            'delThread',
            'viewAllThreads',
            'viewTagThreads', 
            
            /* forum actions */
            'addForum',
            'editForum',
            'delForum',
            'rePosForum',
            'editTagsForum',
            'viewAllForum', 

            /* category actions */            
            'addCategory',
            'editCategory',
            'delCategory',
            'rePosCategory',
            
            /* thread state actions */
            'ThreadStickyState',
            'ThreadCloseState',
            'ThreadVisibleState',

            'viewLatestThreads',
            
            'searchThreadEntries',
            
            'ForumAbo',
            'ForumRating',
            
            'forumFeed',
            
            'ajaxPreview');
        return $array;
    }
    
    public function getMethodObject($method) {
        $parameters = $this->getParameters($method);
        if ('viewAllForum' == $method) {
            return new BLCMethod(NAME_FORUM_ALL,
                rewrite_forum(array()),
                BLCMethod::getDefaultMethod());
        } else if ('viewLatestThreads' == $method) {
            return new BLCMethod(NAME_FORUM_LATEST,
                rewrite_forum(array('latest' => true)),
                $this->getMethodObject('viewAllForum'),
                rewrite_forum(array('rss'=>true)), NAME_FORUM_LATEST_RSS);  
        } else if ('searchThreadEntries' == $method) {
            return new BLCMethod(NAME_FORUM_SEARCH,
                rewrite_forum(array('search' => true)),
                $this->getMethodObject('viewAllForum'));
        } else if ('viewThreadEntries' == $method) {
            $thread = $parameters['thread'];
            // first crumb is the forum overview
            $parentMethod = $this->getMethodObject('viewAllForum');
            // then we have the category
            $parentMethod = new BLCMethod($thread->getCategoryName(),
                    rewrite_forum(array()) . '#cat' . $thread->getCategoryId(),
                    $parentMethod);
            
            // we use a stack here to reverse the order of parent forums
            $forumStack = array();
            $forum = $thread->getForum();
            while ($forum->hasParent()) {
                $forumStack[] = $forum;
                $forum = $forum->getParent();
            }
            $forumStack[] = $forum;
            while ($forum = array_pop($forumStack)) {
                $parentMethod = new BLCMethod($forum->getName(),
                    rewrite_forum(array('forumId' => $forum->id)),
                    $parentMethod);
            }
            
            // finally we can add the thread
            return new BLCMethod($thread->getCaption(),
                rewrite_forum(array('threadId' => $thread->id, 'page' => $parameters['page'])),
                $parentMethod);
        } else if ('viewAllThreads' == $method) {
            $forum = $parameters['forum'];
            // first crumb is the forum overview
            $parentMethod = $this->getMethodObject('viewAllForum');
            // then we have the category
            $parentMethod = new BLCMethod($forum->getCategoryName(),
                    rewrite_forum(array()) . '#cat' . $forum->getCategoryId(),
                    $parentMethod);
            
            $currentForum = $forum;
            // we use a stack here to reverse the order of parent forums
            $forumStack = array();
            while ($forum->hasParent()) {                
                $forum = $forum->getParent();
                $forumStack[] = $forum;
            }
            $forumStack[] = $forum;
            
            $page = array_key_exists('page',$_REQUEST) ? $parameters['page'] : 1;
            // ignore bottom entry of forumStack because this the $currentForum, see below
            while (count($forumStack) > 1 && $forum = array_pop($forumStack)) {
                $parentMethod = new BLCMethod($forum->getName(),
                    rewrite_forum(array('forumId' => $forum->id)),
                    $parentMethod);
            }
            
            // the last forum is the one we started with
            return new BLCMethod($currentForum->getName(),
                    rewrite_forum(array('forumId' => $currentForum->id, 'page'=>$page)),
                    $parentMethod,
                    rewrite_forum(array('forumId' => $currentForum->id, 'rss'=>true)), NAME_FORUM_LATEST_RSS);
        } else if ('viewThreadEntry' == $method) {
            // we want to use the already collected parameters again
            // because thread and page should have been determined
            $this->copyParameters('viewThreadEntry', 'viewThreadEntries');
            return $this->getMethodObject('viewThreadEntries');
        } else if ('viewTagThreads' == $method) {
            $tag = $parameters['tag'];
            return new BLCMethod(NAME_FORUM_VIRTUAL . ' ' . $tag->getName(),
                    rewrite_forum(array('tag' => $tag)),
                    $this->getMethodObject('viewAllForum'));
        } else if ('viewThreadEntryHistory' == $method){
            $entry = $parameters['entry'];   	
            $this->copyParameters('viewThreadEntryHistory', 'viewThreadEntry');
            $parent = $this->getMethodObject('viewThreadEntry');
            return new BLCMethod(NAME_FORUM_HISTORY . ': ' . $entry->getCaption(),
                    rewrite_forum(array('historyEntryId' => $entry->id)),
                    $parent);
        } else if ('delCategory' == $method){
        	$parentMethod = $this->getMethodObject('viewAllForum');
            $cat = $parameters['category'];
            $parentMethod = new BLCMethod($cat->getName(),
                    rewrite_forum(array()).'#cat'.$cat->id,
                    $parentMethod);
            return new BLCMethod(BLCMethod::getQuotedName($cat->getName(), NAME_DELETE),
                    rewrite_forum(array('delCategoryId' => $cat->id)),
                    $parentMethod);
        } else if ('editCategory' == $method){
            $parentMethod = $this->getMethodObject('viewAllForum');
            $cat = $parameters['category'];
            $parentMethod = new BLCMethod($cat->getName(),
                    rewrite_forum(array()).'#cat'.$cat->id,
                    $parentMethod);
            return new BLCMethod(BLCMethod::getQuotedName($cat->getName(), NAME_EDIT),
                    rewrite_forum(array('editCategoryId' => $cat->id)),
                    $parentMethod);
        } else if ('delForum' == $method){
            $parentMethod = $this->getMethodObject('viewAllThreads');
            $forum = $parameters['forum'];
            return new BLCMethod(BLCMethod::getQuotedName($forum->getName(), NAME_DELETE),
                    rewrite_forum(array('delForumId' => $forum->id)),
                    $parentMethod);
        } else if ('editForum' == $method){
            $parentMethod = $this->getMethodObject('viewAllThreads');
            $forum = $parameters['forum'];
            return new BLCMethod(BLCMethod::getQuotedName($forum->getName(), NAME_EDIT),
                    rewrite_forum(array('editForumId' => $forum->id)),
                    $parentMethod);
        } else if ('editTagsForum' == $method){
            $parentMethod = $this->getMethodObject('viewAllThreads');
            $forum = $parameters['forum'];
            return new BLCMethod('Tags von '.$forum->getName(),
                    rewrite_forum(array('editTagsForumId' => $forum->id)),
                    $parentMethod);
        } else if ('editThread' == $method){
        	$thread = $parameters['thread'];
            $this->_parameters['viewAllThreads']['forum'] = $thread->getForum();
            $parentMethod = $this->getMethodObject('viewAllThreads');
            return new BLCMethod(BLCMethod::getQuotedName($thread->getCaption(), NAME_EDIT),
                    rewrite_forum(array('editThreadId' => $thread->id)),
                    $parentMethod);
        } else if ('delThread' == $method){
            $thread = $parameters['thread'];
            $this->_parameters['viewAllThreads']['forum'] = $thread->getForum();
            $parentMethod = $this->getMethodObject('viewAllThreads');
            return new BLCMethod(BLCMethod::getQuotedName($thread->getCaption(), NAME_DELETE),
                    rewrite_forum(array('delThreadId' => $thread->id)),
                    $parentMethod);
        } else if ('addThreadEntry' == $method){
            $this->copyParameters('addThreadEntry', 'viewThreadEntries');
            return $this->getMethodObject('viewThreadEntries');
        } else if ('editThreadEntry' == $method) {
            // we want to use the already collected parameters again
            // because thread and page should have been determined
            $this->copyParameters('editThreadEntry', 'viewThreadEntries');
            $parentMethod = $this->getMethodObject('viewThreadEntries');
            return new BLCMethod(BLCMethod::getQuotedName($parameters['thread']->getCaption(), NAME_EDIT),
                    rewrite_forum(array('editEntryId' => $parameters['entry']->id)),
                    $parentMethod);
        } else if ('delThreadEntry' == $method) {
            // we want to use the already collected parameters again
            // because thread and page should have been determined
            $this->copyParameters('delThreadEntry', 'viewThreadEntries');
            $parentMethod = $this->getMethodObject('viewThreadEntries');
            return new BLCMethod(BLCMethod::getQuotedName($parameters['entry']->getCaption(), NAME_DELETE),
                    rewrite_forum(array('delEntryId' => $parameters['entry']->id)),
                    $parentMethod);
        } 
        
        return parent::getMethodObject($method);
    }
    
    protected function collectParameters($method) {
        $parameters = array();
        if ('viewThreadEntries' == $method) {
            $parameters['thread'] = ThreadModel::getThreadById(InputValidator::getRequestData('threadId', 0));
            $parameters['page'] = InputValidator::getRequestData('page', 1);
        } else if ('viewAllThreads' == $method) {
            $parameters['forum'] = ForumModel::getForumById(InputValidator::getRequestData('forumId', 0));
            $parameters['page'] = InputValidator::getRequestData('page', 0);
        } else if ('viewThreadEntry' == $method) {
            $entry = ThreadEntryModel::getThreadEntryById(InputValidator::getRequestData('entryId', 0));
            if ($entry) {
                $parameters['thread'] = $entry->getThread();
                // compute the right page to show the specified entry
                $parameters['page'] = ceil($entry->getNrInThread() / $this->entriesPerPage['entries']);
            } else {
                $parameters['thread'] = null;
                $parameters['page'] = 0;
            }
        } else if ('viewTagThreads' == $method) {
            $parameters['tag'] = TagModel::getTagById(InputValidator::getRequestData('tagId', 0));
            $parameters['page'] = InputValidator::getRequestData('page', 1);
        } else if ('viewThreadEntryHistory' == $method) {
            $entry = ThreadEntryModel::getThreadEntryById(InputValidator::getRequestData('entryId', 0));
            $parameters['entry'] = $entry;
            if ($entry) {
                $parameters['thread'] = $entry->getThread();
                $parameters['page'] = ceil($entry->getNrInThread() / $this->entriesPerPage['entries']);
            }else{
            	$parameters['thread'] = null;
                $parameters['page'] = 0;
            }
        } else if ('delCategory' == $method){
        	$parameters['category'] = CategoryModel::getCategoryById(InputValidator::getRequestData('catId',0));
        } else if ('editCategory' == $method){
            $parameters['category'] = CategoryModel::getCategoryById(InputValidator::getRequestData('catId',0));
        } else if ('delForum' == $method){
            $parameters['forum'] = ForumModel::getForumById(InputValidator::getRequestData('forumId',0));
        } else if ('editForum' == $method){
            $parameters['forum'] = ForumModel::getForumById(InputValidator::getRequestData('forumId',0));
        } else if ('editTagsForum' == $method){
            $parameters['forum'] = ForumModel::getForumById(InputValidator::getRequestData('forumId',0));
        } else if('editThread' == $method){
        	$parameters['thread'] = ThreadModel::getThreadById(InputValidator::getRequestData('threadId', 0));
        } else if('delThread' == $method){
            $parameters['thread'] = ThreadModel::getThreadById(InputValidator::getRequestData('threadId', 0));
        } else if ('addThreadEntry' == $method) {
            if (array_key_exists('quoteId', $_REQUEST)){
            	$parameters['quoteEntry'] = ThreadEntryModel::getThreadEntryById($_REQUEST['quoteId']);
                $parameters['thread'] = $parameters['quoteEntry']->getThread();
                $parameters['page'] = $parameters['thread']->getTotalPages();
            }else{
                $parameters['thread'] = ThreadModel::getThreadById(InputValidator::getRequestData('threadId', 0));
                $parameters['page'] = InputValidator::getRequestData('page',1);
            }
            
        } else if ('editThreadEntry' == $method) {
            $entry = Session::getInstance()->getEntryDataChecked('forum', InputValidator::getRequestData('entryId', 0));
            if (!$entry) {
                $entry = ThreadEntryModel::getThreadEntryById(InputValidator::getRequestData('entryId', 0));
            }
            if ($entry) {
                $parameters['thread'] = $entry->getThread();
                // compute the right page to show the specified entry
                $parameters['page'] = ceil($entry->getNrInThread() / $this->entriesPerPage['entries']);
            } else {
                $parameters['thread'] = null;
                $parameters['page'] = 0;
            }
            $parameters['entry'] = $entry;
        } else if ('delThreadEntry' == $method) {
            $entry = ThreadEntryModel::getThreadEntryById(InputValidator::getRequestData('entryId', 0));
            if ($entry) {
                $parameters['thread'] = $entry->getThread();
                // compute the right page to show the specified entry
                $parameters['page'] = ceil($entry->getNrInThread() / $this->entriesPerPage['entries']);
            } else {
                $parameters['thread'] = null;
                $parameters['page'] = 0;
            }
            $parameters['entry'] = $entry;
        }
        $this->_parameters[$method] = $parameters;

        parent::collectParameters($method);
    }
    
    protected static function getForumThreadLineView($cat, $forum, $read) {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'internal/forum_thread_line.tpl');
        
        $cacheKey = 'forum|line|' . $forum->id;
        
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        $main->setCacheParameter(-1, $cacheKey . '|' . 
                (Session::getInstance()->getVisitor()->isLoggedIn() ? 'on' : 'off')
            );
            
        self::observeIPC(
                new ForumIPC($forum->id), 
                array('THREAD_CHANGED'),
                $main, $cacheKey);
        
        $main->assign('show_order', $cat->getType());
        $main->assign('forumRead', $read);
        $main->assign('cat', $cat);
        $main->assign('f', $forum);
        
        //$cUser = Session::getInstance()->getVisitor();
        
        return $main;
    }
      
    /**
     * Shows All Forums Group by Category
     * 
     * @param $displayView boolean indicates that the view should be shown. 
     *                              If the central view is postprocessed e.g. see
     *                              editCategory() set to false.
     */
    protected function viewAllForum($displayView = true){
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'overview.tpl');
        
        // CACHEME: (linap, 10.05.2007) do not cache this page itself, because it is very user specific because of
        // the administrative buttons and groups and courses ...
        $cUser = Session::getInstance()->getVisitor();
        
        $fora = ForumModel::getAllParentForumsByCategories(CategoryModel::getAllCategories(), $cUser->isLoggedIn());
        
        /**
         * if user is loggin an is internal
         * we collect all Fora of the selceted course of the user
         * and all Fora of his group Membership
         */
        if ($cUser->isLoggedIn() and !$cUser->isExternal()){
            /*
             * Hack to get old Study Fora
             */
            $oldCat = CategoryModel::getOldCourseCategory();
            if($oldCat !== null){
                $oldCatForums = ForumModel::getAllParentForumsByCategories(array($oldCat), $cUser->isLoggedIn());
                $oldStudyFora = $oldCatForums[0]->forums;
            }else{
            	$oldStudyFora = array();
            }
            
            $courseCat = CategoryModel::getCourseCategory();
            $courseCat->forums = array_merge($oldStudyFora,ForumModel::getCourseForumsByUser($cUser, $courseCat));
            $fora[] = $courseCat;
            //var_dump($courseCat);
            
            $groupCat = CategoryModel::getGroupCategory();
            $groupCat->forums = ForumModel::getGroupForumsByUser($cUser);
            $fora[] = $groupCat;
        }
        
        #var_dump($fora);               
        
        $read = ForumRead::getInstance($cUser);
        $main->assign('forumRead', $read);
        $main->assign('forum', $fora);
        //var_dump($fora);
        foreach ($fora as $cat) {
            foreach ($cat->forums as &$f) {
                if ($f instanceof ForumModel) {
                    $f = self::getForumThreadLineView($cat, $f, $read);
                }
            }
            //var_dump($cat->forums);
        }
        
        // if user has admin rights, gather all possible templates
        if ($cUser->hasRight('FORUM_CATEGORY_ADMIN')) {
            $templates = scandir(BASE . "/template/" . Session::getInstance()->getTemplateDirectory() . '/modules/forum/user_entry_info');
    
            $newTemplates = array();
            foreach ($templates as $t){
                if (substr($t, -4, strlen($t)) == '.tpl') {
                    $newTemplates[] = $t;
                }
            }
            $main->assign('userEntryInfoTemplates', $newTemplates);
        }
        
        if (array_key_exists('addForum',$_REQUEST)){
            $main->assign('showAddForum', $_REQUEST['addForum']);
            $main->assign('details_visible', DetailsVisibleModel::getAllDetailsVisible('general'));
        }
        /**
         * show the tag forum
         */
        if ($cUser->isLoggedIn() and !$cUser->isExternal()){
            /* search all tags of the User */
            $linkedTags = array();
            foreach($cUser->getStudyPathsId() as $studyPathId){
                $linkedTags = $linkedTags + TagModel::getTagByStudyPath($studyPathId);
            }
        
            //var_dump($linkedTags);
            
            $tagThreads = array();
            $tagStats = array();
            foreach($linkedTags as $tag){
                // load lastest Thread for every tag
                $tagThread = ThreadModel::getAllThreadsByTag($tag->id, 1);
                if (array_key_exists(0, $tagThread)) {
                    $tagThreads[$tag->id] = $tagThread[0];
                } else {
                    $tagThreads[$tag->id] = null;
                }  
                // load all stats for a tag    
                $tagStats[$tag->id] = ThreadModel::getThreadStatsByTag($tag->id);    
            }
            //var_dump($tagStats);
            
            $main->assign('linkedTags', $linkedTags);
            $main->assign('tagThreads', $tagThreads);
            $main->assign('tagStats', $tagStats);
        }
        
        $this->setCentralView($main);
        
        /* 
         * if the view is postprocessed return the central 
         * view and don't show the view
         */
        if ($displayView) {
            $this->view();
        } else {
            return $main;
        }
    }
    
    /**
     * Show all Threads of one single Forum
     * 
     * The forum that is displayed can be given by $_REQUEST['forumId']
     * or the parameter $newForumId
     * 
     * @param $newForumId number id of the forum that should be shown
     * @param $displayView boolean indicates that the view should displayed or returned for postprocessing
     */
    protected function viewAllThreads($newForumId = null, $displayView = true){
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'thread_overview.tpl');

        // CACHEME: how could be cache this page??
        $main->setCacheParameter(900, '/modules/forum/forum_thread_overview');
        
        /* test if a forum is given */
        if ($newForumId == null && !array_key_exists('forumId', $_REQUEST)){
            $this->errorView(ERR_FORUM_SHOW);
        }
        if ($newForumId != null){
            $forumId = $newForumId;
        } else {
            $forumId = $_REQUEST['forumId'];
        }
        
        $forum = ForumModel::getForumById($forumId);
        if ($forum == null){
            $this->errorView(ERR_FORUM_EXIST);
        }
        
        $cUser = Session::getInstance()->getVisitor();
        // determine random entry id to avoid unwanted double postings
        if (!array_key_exists(F_ENTRY_RANDID, $_REQUEST)) {
            $entryRandId = Session::getInstance()->generateNewRandomId();
        } else {
            $entryRandId = $_REQUEST[F_ENTRY_RANDID];
        }
        $forumVisible = $forum->getVisibleObj();
        if (!$cUser->isLoggedIn() && $forumVisible->name == 'logged in'){
            $this->errorView(ERR_FORUM_LOGIN);
        }
        if ($forumVisible->name == 'group' && !$cUser->isMemberOfGroup($forum->getGroupId())){
            $this->errorView(ERR_FORUM_GROUP);
        }
        
        $forum->setPage(array_key_exists('page', $_REQUEST) ? $_REQUEST['page'] : 1);
        
        $showInvisible = false;
        
        /** only monderator should see invisible threads */
        if ($forum->isModerator($cUser)) {
            $showInvisible = true;
        }        
        
        /* templates für categorien oder foren */
        $templates = scandir(BASE . "/template/" . Session::getInstance()->getTemplateDirectory() . '/modules/forum/user_entry_info');
        $newTemplates = array();
        foreach( $templates as $t ){
            if (substr($t, -4, strlen($t)) == '.tpl') {
                $newTemplates[] = $t;
            }
        }                            
        $main->assign('userEntryInfoTemplates', $newTemplates);
        /* end - templates für categorien oder foren */
        
        $main->assign('randid', $entryRandId);        
        $main->assign('forum', $forum );
        
        if ($forum->isGroupForum() && $cUser->isMemberOfGroup($forum->getGroupId())){
            $isGroup = true;
        }else{
        	$isGroup = false;
        }
        $main->assign('subforums', ForumModel::getForumByParentId($forum->id, $cUser->isLoggedIn(), $isGroup) );
        if (array_key_exists('addsub',$_REQUEST)){
            $main->assign('addsub', true);
             if($forum->isGroupForum()){
                $main->assign('details_visible', DetailsVisibleModel::getAllDetailsVisible('group'));
            }else{
                $main->assign('details_visible', DetailsVisibleModel::getAllDetailsVisible('general'));
            }
        }
        
        $main->assign('cat', CategoryModel::getCategoryById($forum->getCategoryId()));
        $threads = ThreadModel::getAllThreadsByForumId($forum->id, $forum->getThreadsPerPage(), ($forum->getPage()-1)*$forum->getThreadsPerPage() ,'desc', $showInvisible);
        $main->assign('threads', $threads);
         
        /* forum read */        
        $read = ForumRead::getInstance($cUser);

        $main->assign('forumRead', $read);      
        $this->setCentralView($main);
        
        $main->assign('pointsAnonymous',PointSourceModel::getPointSourceByName('FORUM_ANONYMOUS_POSTING')); 
        
        /* should the view display or postprocessed */
        if ($displayView) {
            $this->view();
        } else {
            return $main;
        } 
    }
    
    /**
     * view the latest Threads
     */
    protected function viewLatestThreads(){
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'thread_view_latest.tpl');
        $cUser = Session::getInstance()->getVisitor();
        
        $show = array_key_exists('show', $_REQUEST) ? $_REQUEST['show'] : 'all';
        if ($show != 'community' && $show != 'studies' && $show != 'abo'){
            $show = 'all';
        }
        if($show == 'abo' && !$cUser->isRegularLocalUser()){
           $show = 'all';	
        }
        
        
        $cacheKey = 'forum|latest|' . $show;
        if ($cUser->isLoggedIn()) {
            $groupIds = '|9';
            foreach ($cUser->getGroupMembership() as $g) {
                $groupIds .= '0' . $g->id;
            }
            $cacheKey .= $groupIds;
        } else {
            $cacheKey .= '|loggedoff';
        }
        
        $main->setCacheParameter(-1, $cacheKey);
        
        // cache general overview only at the momemnt
        if ($show == 'all' or $show == 'community') {
            if (defined('CACHETEST')) {
                $main->enableCaching();
            }
            
            self::observeIPC(
                new GlobalIPC, 
                array('FORUM_THREAD_CHANGED'),
                $main, 'forum|latest');
        }
        
        $cUser = Session::getInstance()->getVisitor();
        $showInvisible = false;
        
        // initialize ForumRead
        ForumRead::getInstance($cUser, true);
        
        if (!$main->isCached()) {
            if($show == 'community'){
                $main->assign('threads', ThreadModel::getAllThreadsByLatestUser($cUser, V_FORUM_LATEST_THREADS_PER_PAGE, 0,'desc', $showInvisible, 'default'));
            } elseif($show == 'studies') {
                $main->assign('threads', ThreadModel::getAllCourseThreadsByUser(5, 0 ,'desc', $showInvisible, $cUser, true));
            } elseif($show == 'abo'){
				 $main->assign('threads', ThreadModel::getAllAboThreadsByUserId($cUser->id, 2*V_FORUM_LATEST_THREADS_PER_PAGE, 0,'desc', $showInvisible, ''));
            }else {
                $main->assign('threads', ThreadModel::getAllThreadsByLatestUser($cUser, V_FORUM_LATEST_THREADS_PER_PAGE, 0,'desc', $showInvisible, ''));
            }
        }

        $main->assign('show',$show);
        
        //$main->assign('forumRead', $read);
                
        $this->setCentralView($main);
        $this->view();
    }
    
    protected static function getThreadEntryView($id, $thread, $forum) {
        //
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'internal/part_thread_entry.tpl');
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        $cacheKey = self::getThreadEntryCachekey($id);
        $main->setCacheParameter(7200, $cacheKey);
        
        $authorId = null;
        if (!$main->isCached() or !self::getCachedIds($cacheKey, $authorId)) {
            $entry = ThreadEntryModel::getThreadEntryById($id);
            $entry->setThread($thread);
            if ($entry->getAuthor() == null) {
                $entry->setAuthor(new UserAnonymousModel);
            }
            
            $author = $entry->getAuthor();
            self::cacheIds($cacheKey, array($author->id), true);
            $authorId = $author->id;
            
            $main->assign('entry', $entry);
        } else {
            $author = $authorId[0];
            // $author is already the id
            $authorId = $author;
        }

        $main->assign('author', $author);
        $main->assign('authorId', $authorId);
        $main->assign('thread', $thread);
        $main->assign('forum', $forum);
        $main->assign('entryId', $id);
        $main->assign('visitor', Session::getInstance()->getVisitor());
        
        return $main;
    }
        
    /**
     * show all Entrys of one single Thread
     * 
     * @param $thread model of a thread entry from which the entries should be shown
     * @param $displayView boolean indicates that the should be shown, if false the view is returned 
     *          and can postprocessed for an example see editThread()
     */
    protected function viewThreadEntries($displayView = true, $parameters = null){
        if ($parameters == null) {
            $parameters = $this->getParameters('viewThreadEntries');
        }

        if ($parameters['thread'] == null) {
            $this->errorView(ERR_FORUM_THREAD_SHOW);
        }
        $page = $parameters['page'];
        $thread = $parameters['thread'];

        // determine random entry id to avoid unwanted double postings
        if (!array_key_exists(F_ENTRY_RANDID, $_REQUEST)) {
            $entryRandId = Session::getInstance()->generateNewRandomId();
        } else {
            $entryRandId = $_REQUEST[F_ENTRY_RANDID];
        }
        
        $cUser = Session::getInstance()->getVisitor();
        
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'thread_entries.tpl');
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        $cacheKey = self::getThreadPageCachekey($thread->id, $page);
        $main->setCacheParameter(7200, $cacheKey);
        
            
        /*if ($thread == null) {
            Logging::getInstance()->logSecurity('viewThreadEntries(): no thread in the DB with the given id');
        }*/
        
        /* if the thread has been moved */
        if ($thread->getLinkToThread() != null){
            $thread = $thread->getLinkToThread();
        }
        
        $forum = $thread->getForum();
        
        $forumVisible = $forum->getVisibleObj();
        if (!$cUser->isLoggedIn() && $forumVisible->name == 'logged in'){
            $this->errorView(ERR_FORUM_LOGIN);
        }
        if ($forumVisible->name == 'group' && !$cUser->isMemberOfGroup($forum->getGroupId())){
            $this->errorView(ERR_FORUM_GROUP);
        }
        
        if (!$thread->isVisible() && !$forum->isModerator($cUser)){
            Logging::getInstance()->logSecurity('want to see a invible thread without be a moderator');
        }
        
        // increase the Counter
        $visitThreads = Session::getInstance()->getUserData('visitThreads');
        if(!$visitThreads){
            $visitThreads = array();
        }      
        if(!array_key_exists($thread->id,$visitThreads)){
            $thread->incViewCounter();
            $visitThreads[$thread->id] = time();
        }
        $newVisits = array();  
        foreach($visitThreads as $key => $visitTime){
            if($visitTime > time() - V_FORUM_THREAD_VISIT_LIFETIME){
                $newVisits[$key] = $visitTime;
            }
        }
        Session::getInstance()->storeUserData('visitThreads',$newVisits);
        
        
        $main->assign('randid', $entryRandId);
        $main->assign('thread', $thread);
        $main->assign('forum', $forum);
        
        $read = ForumRead::getInstance($cUser);
        $read->setRead($forum->id, $thread->id);
        
        // get the right page
        $thread->setPage($page);

        $threadEntryViews = array();
        $threadEntryIds = null;
        if (!$main->isCached() or !self::getCachedIds($cacheKey, $threadEntryIds)) {
            $threadEntryIds = ThreadEntryModel::getEntryIdsByThreadId($thread->id, $this->entriesPerPage['entries'], (($thread->getPage()-1) * $this->entriesPerPage['entries']) );
            self::cacheIds($cacheKey, $threadEntryIds);
        }
        foreach ($threadEntryIds as $tid) {
            array_push($threadEntryViews, self::getThreadEntryView($tid, $thread, $forum));
        }
        
        $main->assign('threadEntries', $threadEntryViews);
        // TODO: move this into cached area - but how??
        $main->assign('pointsAnonymous', PointSourceModel::getPointSourceByName('FORUM_ANONYMOUS_POSTING'));
        
        $this->setCentralView($main);
        
        /* if not postprocessed show template else return the view so it can edited */
        if ($displayView) {
            $this->view();
        } else {
            return $main;
        }
    }
    
    /**
     * shows all ThreadEntries of a given threadId and Page
     */
    protected function viewThreadEntry() {
        $main = $this->viewThreadEntries(false, $this->getParameters('viewThreadEntry'));
        
        $this->setCentralView($main);
        $this->view();
    }
    
    protected function viewThreadEntryHistory() {
        $parameters = $this->getParameters('viewThreadEntryHistory');
        $entry = $parameters['entry'];
        if ($entry == null){
            $this->errorView(ERR_NO_ENTRY);
        }
        
        $cUser = Session::getInstance()->getVisitor();
        $forum = $entry->getThread()->getForum();
        
        if (!$forum->isModerator($cUser)){
            $this->rightsMissingView('FORUM_MODERATOR');
        }

        require_once CORE_DIR.'/models/forum/thread_entry_log_model.php';
        $entrys_all = ThreadEntryLogModel::getHistoryById($entry->id);
        
        $entrys = array();
        $entryRaw = '';
        foreach($entrys_all as $en){
            if ($entryRaw != $en->getContentRaw()){
                $entrys[] = $en;
            }
            $entryRaw = $en->getContentRaw();
        }
        
        //var_dump($entrys);
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'thread_entry_log.tpl');
        // CACHEME (linap, 10.05.2007): we don't need to cache this page assuming only very few hits
        $main->setCacheParameter(900, 'modules|forum');
        
        $main->assign('entryHistory', $entrys);
        $main->assign('entryNow', $entry);
        $main->assign('forum', $forum);
        
        $this->setCentralView($main);
        $this->view();
        
    }
    
    /**
     * show all Threads by a given Thread
     */
    protected function viewTagThreads($displayView = true){
        $parameters = $this->getParameters('viewTagThreads');
        $tag = $parameters['tag'];
        $page = $parameters['page'];
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'thread_virtuell.tpl');

        // CACHEME (linap, 10.05.2007): do not cache this page at the moment because of too much 
        // user specific content
        $main->setCacheParameter(900, 'modules|forum|tag');
        
        // test for a given tag
        if ($tag == null) {
            $this->errorView(ERR_NO_TAG);
        }
        
        $cUser = Session::getInstance()->getVisitor();
        
        $tagStats = ThreadModel::getThreadStatsByTag($tag->id);
        $pageNumbers = ceil($tagStats['number_of_threads']/V_FORUM_THREADS_PER_PAGE);
        $counter = ThreadModel::nonLinearCounter($pageNumbers, $page);
        
        $threads = ThreadModel::getAllThreadsByTag($tag->id, V_FORUM_THREADS_PER_PAGE, ($page-1) * V_FORUM_THREADS_PER_PAGE);
        
        $main->assign('page_counter',$counter);
        $main->assign('page',$page);  
        $main->assign('pageNumbers', $pageNumbers);      
        
        $main->assign('threads', $threads);
        $main->assign('tag', $tag);
        
        $read = ForumRead::getInstance($cUser);
        $main->assign('forumRead', $read);
                
        $this->setCentralView($main);
        
        /* should the view display or postprocessed */
        if ($displayView) {
            $this->view();
        } else {
            return $main;
        } 
    }
    
    /**
     * notifies about changes in all parent fora of given thread
     *  (or given forum)
     * @param ThreadModel
     * @param ForumModel may be null, so that forum of given thread will be used
     */
    protected static function notifyAllParentFora($thread, $forum = null) {
        if (null == $forum) {
            $forum = $thread->getForum();
        }

        // traverse all parent fora and notify them
        while ($forum->hasParent()) {
            self::notifyIPC(new ForumIPC($forum->id), 'THREAD_CHANGED');
            $forum = $forum->getParent();
        }
        self::notifyIPC(new ForumIPC($forum->id), 'THREAD_CHANGED');
        
        // and set gerenal forum change flag
        self::notifyIPC(new GlobalIPC, 'FORUM_THREAD_CHANGED');
    }
    
    /**
     * add a entry to a thread
     */
    protected function addThreadEntry($newThread = null, $escapeCaption = true) {
        
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        // determine random entry id to avoid unwanted double postings
        if (!array_key_exists(F_ENTRY_RANDID, $_REQUEST)) {
            $entryRandId = Session::getInstance()->generateNewRandomId();
        } else {
            $entryRandId = $_REQUEST[F_ENTRY_RANDID];
        }
        
        /* is the user allowed to make a post */
        if (!$cUser->hasRight('FORUM_THREAD_ENTRY_ADD')){
            $this->rightsMissingView('FORUM_THREAD_ENTRY_ADD');
        }
        
        /********************************
         * to quote a forum entry
         *******************************/
        
        /* quote the values the entry */
        if (array_key_exists('quoteId', $_REQUEST)){
            
            /* show the entry that will be edit in the text field */
            $entryToQuote = ThreadEntryModel::getThreadEntryById($_REQUEST['quoteId']);
            if ($entryToQuote == null)
                $this->errorView(ERR_NO_ENTRY);
            
            $page = ceil( $entryToQuote->getNrInThread() / $this->entriesPerPage['entries'] );
            $_REQUEST['page'] = $page; 
            
            /* get the template for postprocessing */
            $main = $this->viewThreadEntries(false, array('thread' => $entryToQuote->getThread(), 'page' => $page));
           
            $main->assign('entryToEdit', $entryToQuote);            
            $main->assign('isQuote', true);
            $this->view();
            return;
        }    

        /******* end of quote *******************/
        if ($newThread != null) {
            $thread = $newThread;
        } else{
        	$parameters = $this->getParameters('addThreadEntry');
            $thread = $parameters['thread'];
        }
        
        if ($thread == null) {
            $this->errorView(ERR_FORUM_THREAD_SHOW);
        }
        
        $formFields = array(        
        'entryText' => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false),
        'caption'=> array('required' => false, 'check' => 'isValidAlmostAlways', 'escape' => $escapeCaption)
         );

        // some validity checks and HTML-escape
        $this->validateInput($formFields);
        
        if (strlen($_POST['entryText']) > V_ENTRY_MAX_CHARS) {
            self::addIncompleteField($this->errors, 'entryText', ERR_ENTRY_TOO_LONG);
        }
        if (strlen($_POST['caption']) > V_CAPTION_MAX_CHARS) {
           self::addIncompleteField($this->errors, 'caption', ERR_ENTRY_TITLE_TOO_LONG);
        }
        
        $entryText = trim($_POST['entryText']);
        if (array_key_exists('caption', $_POST)) {
            $nullParser = ParserFactory::getFormatcodeNullParser();    
            $caption = $nullParser->parse(trim($_POST['caption']));
        } else {
            $caption = '';
        }
        
        /* if user want to post a anonym posting */
        if (array_key_exists('for_group', $_POST) && $_POST['for_group'] == -1){
            if ($cUser->hasEnoughPoints('FORUM_ANONYMOUS_POSTING')){
                $isAnonymous = true;
            }
            else{
                $this->errors['enable_anonymous'] = ERR_FORUM_POINTS;
                $isAnonymous = false; 
            }
        } else {
            $isAnonymous = false;   
        }

        /* it is not allowed to add entries on closed thread */
        if ($thread->isClosed()){
            Logging::getInstance()->logSecurity('Thread is closed');
        }             
        
        /****************** get the Entry Model ************/
        if ($newThread){
            $entry = Session::getInstance()->getEntryDataChecked('newthread', null);
        }else{
            $entry = Session::getInstance()->getEntryDataChecked('forum', null);    
        }
        
        if ($entry != false){
            $entry->setContentRaw($entryText);
            $entry->setCaption($caption);
            $entry->setAnonymous($isAnonymous);
            $entry->setParseSettings(self::getParseSettings());
        }else{
            $entry = new ThreadEntryModel($entryText, $cUser, self::getParseSettings(), $thread->id, $isAnonymous, $caption);
        }

        if (array_key_exists('for_group', $_POST)) {
          if ($cUser->hasGroupRight('FORUM_GROUP_THREAD_ENTRY_ADD', $_POST['for_group'])) {
            $entry->setGroupId($_POST['for_group']);
          } else {
            $entry->setGroupId(0);
          }
        }
        
        /*** handle attachment(s) additions and removements ***/
        
        if (!$this->handleAttachment($entry, $cUser, GlobalSettings::getGlobalSetting('ENTRY_MAX_ATTACHMENT_SIZE') * 1024)) {
            $this->errors['attachemnt'] = ERR_ATTACHMENT;
        }
        
        /***** test wether preview is activated *****/
        
        if ($this->previewMode || $this->uploadMode || count($this->errors) > 0) {
            /* get the template for postprocessing */
            $main = $this->viewThreadEntries(false, array('thread' => $thread, 'page'=>InputValidator::getRequestData('page',1)));
                        
            $entry->setForum($thread->getForum());
            
            $entry->parse(false);
   
            /* preview we have to edit the entry */
            if ($this->previewMode) {
                $main->assign('isPreview', true);
            }else{
                $main->assign('isPreview', false);
            }
            $main->assign('isAdd', true);
            $main->assign('entryToEdit', $entry);

            Session::getInstance()->storeEntryData('forum', $entry);
            
            $this->view();
            return false;
        }
        /*** preview mode end ****/
        
        // if we have no suitable entry random id, we do nothing here        
        if ($newThread == null and !Session::getInstance()->removeRandomId($entryRandId)) {
            // trigger creation of new entry rand id
            unset($_REQUEST[F_ENTRY_RANDID]);
            $this->viewThreadEntries(true, array('thread' => $thread));
            return;
        }
        
        $entry->setPostIP(ClientInfos::getClientIP());
        /* add a link to the forum */
        if ($newThread != null){
            $entry->setForum($thread->getForum());
        }
        $entry->setThreadId($thread->id);
        
        // open transaction, because we update entry and points
        $DB = Database::getHandle();
        $DB->StartTrans();
        
        // lock involved tables to avoid deadlock on
        // multiple concurrent actions
        $DB->execute('LOCK TABLE ' . DB_SCHEMA . '.forum_fora, 
                                 ' . DB_SCHEMA . '.forum_threads,
                                 ' . DB_SCHEMA . '.forum_thread_entries 
                           IN SHARE ROW EXCLUSIVE MODE');
        
        $entry->save();
        
        /*** calculate points ****/
        if ($thread->getForum()->hasEnabledPoints()){
            $ps = PointSourceModel::getPointSourceByName('FORUM_POSTING');
            $cUser->increaseUnihelpPoints($ps->getPointsSum(), $ps->getPointsFlow());
            $cUser->save();
        }
        
        if ($entry->isAnonymous()){
            $ps = PointSourceModel::getPointSourceByName('FORUM_ANONYMOUS_POSTING');
            $cUser->increaseUnihelpPoints($ps->getPointsSum(), $ps->getPointsFlow());
            $cUser->save();
        }
                
        $DB->CompleteTrans();
        
        self::notifyAllParentFora($thread);
        
        // we don't need the entry any more
        Session::getInstance()->deleteEntryData('forum');
        
        /*** genarate output ***/
        
        if ($newThread == null){        
            /* do this: $this->viewThreadEntries($threadId);*/
            $page = ceil(($thread->getNumberOfEntries() + 1) /  $this->entriesPerPage['entries']);
            $token = md5 (uniqid ('fuck the caching browser'));
        
            // clear cache
            if (defined('CACHETEST')) {
                // we have to clean all subpages of the thread
                // because page counters may have changed
                $cacheKey = self::getThreadPageCachekey($thread->id);
                // TODO: find a better way than instanciating the template here
                // perhaps it would suffice to move the view creation to one function
                // that is also called on normal thread entry view
                $threadEntriesView = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'thread_entries.tpl');
                $threadEntriesView->enableCaching();
                $threadEntriesView->clearCache($cacheKey);
                
                self::clearCacheIds($cacheKey);
            }
        
            $url = rewrite_forum(array('threadId' => $thread->id , 'page' => $page , 'rand' => $token, 'extern' => true ));
            header('Location: ' . $url);
        }
        
        return true;
    }

    /**
     * function to edit thread entries
     */
    protected function editThreadEntry() {
        $parameters = $this->getParameters('editThreadEntry');
        $entryToEdit = $parameters['entry'];
        
        $cUser = Session::getInstance()->getVisitor();
        
        if ($entryToEdit == null){
            Logging::getInstance()->logSecurity('editThreadEntry: entry with the given id does no exists');
        }
        
        /* test rights that the user allowed to make a edit */
        if ( (!$entryToEdit->getAuthor()->equals($cUser) || !$cUser->hasRight('FORUM_THREAD_ENTRY_EDIT')) && !$entryToEdit->getForum()->isModerator($cUser) ){
            $this->rightsMissingView('FORUM_THREAD_ENTRY_EDIT');
        }

        if( $entryToEdit->getForum()->isModerator($cUser)|| ((int)$entryToEdit->getTimeEntry())+(V_FORUM_EDIT_THREAD_ENTRY_TIME*3600) > time()){
        	$addMode = false;
        }else{
        	$addMode = true;
        }
        
        /**
         *  look if the entry is a newposting and if true forward to edit the news posting
         */
        if ($entryToEdit->getNrInThread() == 1){
            $news = NewsEntryModel::getNewsByThreadId( $entryToEdit->getThreadId() );
            if ($news != null){
                header('Location: /home/news/' . $news->id . '/edit');
                return true;
            }
        }
        
        // handle attachment(s) additions and removements
        if (!$this->handleAttachment($entryToEdit, $cUser, GlobalSettings::getGlobalSetting('ENTRY_MAX_ATTACHMENT_SIZE') * 1024)) {
            $this->errors['attachemnt'] = ERR_ATTACHMENT;
        }
        
        if (!empty($_REQUEST['save']) || $this->previewMode){
            $formFields = array(
                'entryText' => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false),
                'caption'=> array('required' => false, 'check' => 'isValidAlmostAlways', 'escape' => true)
             );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);
            
            if (strlen($_POST['entryText']) > V_ENTRY_MAX_CHARS) {
               self::addIncompleteField($this->errors, 'entryText', ERR_ENTRY_TOO_LONG);
            }
            if (strlen($_POST['caption']) > V_CAPTION_MAX_CHARS) {
               self::addIncompleteField($this->errors, 'caption', ERR_ENTRY_TITLE_TOO_LONG);
            }
        }
        
        /* should the array information be shown */
        if (!array_key_exists('save', $_POST) ||
            $this->previewMode || $this->uploadMode || count($this->errors) > 0) {
    
            // get the template for postprocessing
            $this->copyParameters('editThreadEntry', 'viewThreadEntries');
            $main = $this->viewThreadEntries(false);

            /* 
             * it's an preview. set flags for the template designer and
             * change the entry so that the new version is displayed 
             */
            if ($this->previewMode || $this->uploadMode || count($this->errors) > 0) {
                
                /* preview we have to edit the entry */
                if ($this->previewMode) {
                    $main->assign('isPreview', true);
                }
                    
                /* search for a caption */
                if ($entryToEdit->getNrInThread() != 1){
                    $nullParser = ParserFactory::getFormatcodeNullParser();
                    $entryToEdit->setCaption($nullParser->parse(trim($_POST['caption'])));
                }
                if($addMode){
                	$raw = Session::getInstance()->getEntryData('forum_' . $_REQUEST['entryId'].'_raw');
                    if($raw == null){
                    	$raw = $entryToEdit->getContentRaw();
                        Session::getInstance()->storeEntryData('forum_' . $_REQUEST['entryId'].'_raw', $raw );
                    } 
                    $comment = $_POST['entryText'];
                    $text = $raw . "\n\n" . $comment;
                    $entryToEdit->setContentRaw($text);
                    $main->assign('comment',$comment);
                    $main->assign('raw',$raw);
                }else{
                    $entryToEdit->setContentRaw($_POST['entryText']);
                }
                $entryToEdit->setParseSettings(self::getParseSettings());
                // TODO: rights check
            }else{
                $entryToEdit->parse();
                Session::getInstance()->storeEntryData('forum', $entryToEdit);
            }
            
            $main->assign('entryToEdit', $entryToEdit);
            $main->assign('addMode', $addMode);
            
            $this->view();
            return;
        }
            
        /* the edited entry should saved */    
                         
                       
        /* for better handling - search data */
        $entryId = $_REQUEST['entryId'];
        $entryText = trim($_POST['entryText']);
        if ($entryToEdit->getThread() == null){
            Logging::getInstance()->logSecurity('editThreadEntry: thread with the given id does no exists');
        }

        /* it is not allowed to edit Entries on CLosed thread */
        if ($entryToEdit->getThread()->isClosed()){
            Logging::getInstance()->logSecurity('Thread is closed');
        } 
        
        /* search for a caption */
        if (array_key_exists('caption', $_REQUEST) && $entryToEdit->getNrInThread() != 1){    
            $nullParser = ParserFactory::getFormatcodeNullParser();
            $caption = $nullParser->parse(trim($_POST['caption']));
            $entryToEdit->setCaption($caption);
        }
        if ($entryToEdit->getForum()->isModerator($cUser)){
            $entryToEdit->hiddenChanges = array_key_exists('hidden_changes',$_REQUEST);
        }
        
        $entryToEdit->setPostIP(ClientInfos::getClientIP());
    
        /* set the new content */       
        if($addMode){
            $raw = Session::getInstance()->getEntryData('forum_' . $_REQUEST['entryId'].'_raw');
            if($raw == null){
                $raw = $entryToEdit->getContentRaw();Session::getInstance()->storeEntryData('forum_' . $_REQUEST['entryId'].'_raw', $raw );
            } 
            $comment = $_POST['entryText'];
            $text = $raw . "\n\n" . $comment;
            $entryToEdit->setContentRaw($text);
        }else{
            $entryToEdit->setContentRaw($entryText);
        }
        $entryToEdit->setParseSettings(self::getParseSettings());
        
        if (array_key_exists('for_group', $_POST)) {
          if ($cUser->isMemberOfGroup($_POST['for_group'])) {
            $entryToEdit->setGroupId($_POST['for_group']);
          } else {
            $entryToEdit->setGroupId(0);
          }
        }
        
        $entryToEdit->setAuthor($cUser);
        /* save the edited entry and show the thread again */
        $entryToEdit->save();
        Session::getInstance()->deleteEntryData('forum');
        Session::getInstance()->deleteEntryData('forum_' . $_REQUEST['entryId'].'_raw');
        if ( !$entryToEdit->getAuthor()->equals($cUser) && $entryToEdit->getForum()->isModerator($cUser) ){
            $this->addLog('User is Admin and Change a Entry where he is not author');
        }
        
        /* do this $this->viewThreadEntries($threadId);*/
        $page = array_key_exists('page', $_REQUEST) ? $_REQUEST['page'] : 1;
        
        // clear cache
        if (defined('CACHETEST')) {
            $cacheKey = self::getThreadPageCachekey($entryToEdit->getThread()->id, $page);
            // TODO: find a better way than instanciating the template here
            // perhaps it would suffice to move the view creation to one function
            // that is also called on normal thread entry view
            $threadEntriesView = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'thread_entries.tpl');
            $threadEntriesView->enableCaching();
            $threadEntriesView->clearCache($cacheKey);
            
            self::clearCacheIds($cacheKey);
            
            $threadEntriesState = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'internal/part_thread_entry.tpl');
            $threadEntriesState->enableCaching();
            $threadEntriesState->clearCache(self::getThreadEntryCachekey($entryToEdit->id));
        }
        
        $token = md5 (uniqid ('fuck the caching browser'));
        $url = rewrite_forum(array('threadId' => $entryToEdit->getThreadId() , 'page' => $page , 'rand' => $token, 'extern' => true ));
        header('Location: ' . $url);
    }
   
   protected function delThreadEntry() {
        $parameters = $this->getParameters('delThreadEntry');
        $entry = $parameters['entry'];
        
        // test given input
        if (!$entry) {
            $this->errorView(ERR_NO_ENTRY);        
        }
        
        $cUser = Session::getInstance()->getVisitor();

        /* does the forum exists? */
        if ($entry == null) {
            Logging::getInstance()->logSecurity('delThreadEntry(): The given entry id does not exists!');
        }
        if (!$cUser->hasRight('FORUM_CATEGORY_ADMIN')) {
            $this->rightsMissingView('FORUM_CATEGORY_ADMIN');
        }
        if ($entry->id == $entry->getThread()->getFirstEntry()->id){
            $this->errorView(ERR_FORUM_THREAD_DEL_FIRST_ENTRY);
        }
        
        if (!array_key_exists('deleteConfirmation', $_REQUEST)) {
            return $this->confirmationView(NAME_ENTRY_DEL_FROM.': ' . $entry->getAuthor()->getUsername(),
                                           DO_ACTION_FORUM_ENTRY_DEL,
                                           rewrite_forum(array('delEntryId' => $entry->id, 'confirmation' => 1)),
                                           rewrite_forum(array()));            
        }  
                        
        
        $this->addLog('delete a ThreadEntry');
        
        $entry->delete();
        
        self::notifyAllParentFora($entry->getThread());
        
        // clear cache
        if (defined('CACHETEST')) {
            $cacheKey = self::getThreadPageCachekey($entry->getThread()->id);
            // TODO: find a better way than instanciating the template here
            // perhaps it would suffice to move the view creation to one function
            // that is also called on normal thread entry view
            $threadEntriesView = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'thread_entries.tpl');
            $threadEntriesView->enableCaching();
            $threadEntriesView->clearCache($cacheKey);
            
            self::clearCacheIds($cacheKey);
            
            $threadEntriesState = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'internal/part_thread_entry.tpl');
            $threadEntriesState->enableCaching();
            $threadEntriesState->clearCache(self::getThreadEntryCachekey($entry->id));
        }
        
        // all header-Locations must have an external url
        $url = rewrite_forum(array('threadId' => $entry->getThreadId(), 'extern' => true));
        header('Location: ' . $url);      
    }
    
   protected function reportThreadEntry() {

        if (!array_key_exists('entryId', $_REQUEST)) {
            $this->errorView(ERR_NO_ENTRY_TO_REPORT);
        }

        $entry = ThreadEntryModel::getThreadEntryById($_REQUEST['entryId']);

        $mantisBLC = ControllerFactory::createSupportHandlingController();
        if (null == $mantisBLC) {
            throw new CoreException (Logging::getErrorMessage(CORE_CONTROLLER_FAILED, ControllerFactory::createSupportHandlingControllerName()));
        }
        $mantisBLC->postProcess(null, array(F_SOURCE_CAT => F_SOURCE_REPORT_ENTRY,
                                            F_DIRECTLINK => rewrite_forum(array('entryId'=>$entry->id,'extern'=>1) ),
                                            F_SOURCE =>  "Forum",
                                            F_ENTRY_TEXT => $entry->getContentRaw()));
    }
    
    /**
     * add a entry to a thread
     */
    protected function addThread() {
         /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        // determine random entry id to avoid unwanted double postings
        if (!array_key_exists(F_ENTRY_RANDID, $_REQUEST)) {
            $entryRandId = Session::getInstance()->generateNewRandomId();
        } else {
            $entryRandId = $_REQUEST[F_ENTRY_RANDID];
        }
        
        /* is the user allowed to make a post */
        if (!$cUser->hasRight('FORUM_THREAD_ENTRY_ADD')){
            $this->rightsMissingView('FORUM_THREAD_ENTRY_ADD');
        }
        if (!array_key_exists('forumId', $_REQUEST)){
            $this->errorView(ERR_FORUM_SHOW);
        }
        $forumId = $_REQUEST['forumId'];
       
        $formFields = array(
            'caption'   => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 200), 'escape' => true),
            'entryText' => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false)            
             );
        // some validity checks and HTML-escape
        $this->validateInput($formFields);            

        if (strlen($_POST['entryText']) > V_ENTRY_MAX_CHARS) {
            self::addIncompleteField($this->errors, 'entryText', ERR_ENTRY_TOO_LONG);
        }

        $entryText = trim($_POST['entryText']);
        $nullParser = ParserFactory::getFormatcodeNullParser();
        $threadCaption = $nullParser->parse(trim($_POST['caption']));          
        
        $forum = ForumModel::getForumById($forumId);
            
        /* test that the model exists */
        if ($forum == null){
            Logging::getInstance()->logSecurity('Given Forum does not exists!');
        }
        
        //var_dump($forum);
        
        if (!$forum->hasEnabledPostings()){
            Logging::getInstance()->logSecurity('Forum not allowed to add Threads');
        }
        
        /* build thread for saving / preview */
        $thread = new ThreadModel();
        $thread->setForumId($forumId);
        $thread->setCaption($threadCaption);
        $thread->setVisible(true);
        $thread->setClosed(false);
         
        /* only moderators can make Thread sticky */
        if ($forum->isModerator($cUser)){
            $thread->setSticky(array_key_exists('isSticky', $_REQUEST));
        }
        else{
            $thread->setSticky(false);
        }
        
        /* if user want to post a anonym posting */
        if (array_key_exists('enable_anonymous',$_REQUEST)){
            if ($cUser->hasEnoughPoints('FORUM_ANONYMOUS_POSTING')){
                $isAnonymous = true;
            }
            else{
                $this->errors['enable_anonymous'] = ERR_FORUM_POINTS;
            }   
        } else {
            $isAnonymous = false;   
        }
        
        /*** preview should be shown ***/
                    
        if ($this->previewMode || $this->uploadMode || count($this->errors) > 0) {
            $entryToEdit = Session::getInstance()->getEntryData('newthread', null);
            if ($entryToEdit != false){
                $entryToEdit->setContentRaw($entryText);
                $entryToEdit->setCaption($threadCaption);
                $entryToEdit->setAnonymous($isAnonymous);
                $entryToEdit->setParseSettings(self::getParseSettings());
            }else{
                $entryToEdit = new ThreadEntryModel($entryText, $cUser, null,  null, $isAnonymous, $threadCaption);
            }
            
            $entryToEdit->setForum($forum);
            
            // handle attachment(s) additions and removements
            if (!$this->handleAttachment($entryToEdit, $cUser, GlobalSettings::getGlobalSetting('ENTRY_MAX_ATTACHMENT_SIZE') * 1024)) {
               $this->errors['attachement'] = ERR_ATTACHMENT;
            }
            
            $main = $this->viewAllThreads($forumId, false);
            $main->assign('entryToEdit', $entryToEdit);
            $main->assign('threadToEdit', $thread);
            $main->assign('isPreview', true);
            $main->assign('isAdd', true);
                                
            $this->view();
   
            Session::getInstance()->storeEntryData('newthread', $entryToEdit);                                
            return true;
        }
        /*** end preview mode ***/
        
        // if we have no suitable entry random id, we do nothing here        
        if (!Session::getInstance()->removeRandomId($entryRandId)) {
            // trigger creation of new entry rand id
            unset($_REQUEST[F_ENTRY_RANDID]);
            $this->viewAllThreads($forumId);
            return;
        }
        // due to header-Location below, we don't need to generate a new random id here
        
            
        $DB = Database::getHandle();            
        $DB->StartTrans();
        
        // lock involved tables to avoid deadlock on
        // multiple concurrent actions
        $DB->execute('LOCK TABLE ' . DB_SCHEMA . '.forum_fora, 
                                 ' . DB_SCHEMA . '.forum_threads,
                                 ' . DB_SCHEMA . '.forum_thread_entries 
                           IN SHARE ROW EXCLUSIVE MODE');
        
        /* thread is build on top to support preview */
        $thread->save();
          
        /* result in the ThreadId of the new Thread  */  
        $newThreadId = $thread->id;
        
        // add the first Entry to the new Thread 
        // and don't escape the caption of the entry (has already been parsed here)
        if (!$this->addThreadEntry($thread, false, true)){
            return false;  
        }
        if (!$DB->CompleteTrans()){
           throw new DBException(DB_TRANSACTION_FAILED);
        }
        
        // remove object from session, we don't need it anymore
        Session::getInstance()->deleteEntryData('newthread');
        
        /* Jump to the new Thread an show this one */
        /* do this $this->viewThreadEntries($newThreadId); */        
        $url = rewrite_forum(array('threadId' => $newThreadId, 'extern' => true ));
        header('Location: '.$url);
    }

    protected function editThread() {

        $parameters = $this->getParameters('editThread');
        
        $cUser = Session::getInstance()->getVisitor();
        $thread = $parameters['thread'];
        
        /* no thread with the given id found */
        if ($thread == null) {
            Logging::getInstance()->logSecurity('editThread(): no thread with the given id found!');
        }

        $forum = $thread->getForum();
        
        /* is a user a moderator or it's a hack? */
        if (!$forum->isModerator($cUser)) {
            Logging::getInstance()->logSecurity('You didn\'t have enough rights to edit this thread!');
        }
            
        /* show at first the infos about the thread the user want not save the thread */
        if (array_key_exists('showValues', $_REQUEST)) {
            $main = $this->viewAllThreads($forum->id, false);
            $main->assign('threadToEdit', $thread);
            $main->assign('categories', ForumModel::getAllForumsByCategories(CategoryModel::getAllCategories(),true));                                
            $this->view();
            return;
        }   
        
         $formFields = array(
            'caption'   => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 200), 'escape' => true)                        
             );
        // some validity checks and HTML-escape
        $this->validateInput($formFields);
        if (count($this->errors) > 0){
            $this->errorView(ERR_FORUM_THREAD_CAPTION);
        }
        
        $nullParser = ParserFactory::getFormatcodeNullParser();
        $thread->setCaption($nullParser->parse(trim($_POST['caption'])));
        $thread->setSticky(array_key_exists('isSticky', $_REQUEST));

        $this->addLog('edit a thread');

        /* we need a transaction for saving and liking */
        $DB  = Database::getHandle();
        $DB->StartTrans();
        
        $thread->save();
        
        /* the thread should linked in another forum */
        if (array_key_exists('linkThread', $_REQUEST)) {

            /* test that a destination is given */
            if (!array_key_exists('linkThreadDest', $_REQUEST)) {
                $this->errorView(ERR_FORUM_THREAD_LINK);
            }
            if ($thread->getForumId() != $_REQUEST['linkThreadDest']){    
                $destForum = ForumModel::getForumById($_REQUEST['linkThreadDest']);
                
                /* does the forum exists that was given? */
                if ($destForum == null) {
                    Logging::getInstance()->logSecurity('editThread: given forum for linking does not exists!');
                }
    
                /* link the thread to the new forum */                
                $thread->linkToForum($destForum);
        $this->addLog('Thread is moved');
            }
        }
        if (!$DB->CompleteTrans()){
            throw new DBException(DB_TRANSACTION_FAILED);
        }
        
        /* do this: $this->viewAllThreads($forum->id); */
        //header('Location: /index.php?mod=forum&method=viewAllThreads&forumId='.$forum->id);
        $url = rewrite_forum(array('forum' => $forum, 'extern' => true ));
        header('Location: ' . $url);
    }   
    
    protected function delThread() {

        $parameters = $this->getParameters('editThread');
        
        $cUser = Session::getInstance()->getVisitor();
        $thread = $parameters['thread'];

        /* does the forum exists? */
        if ($thread == null) {
            Logging::getInstance()->logSecurity('delThread(): The given thread id does not exists!');
        }
       if (!$cUser->hasRight('FORUM_CATEGORY_ADMIN')) {
            $this->rightsMissingView('FORUM_CATEGORY_ADMIN');
        }        
        
         /* is the delete confirmend? */
        if (!array_key_exists('deleteConfirmation', $_REQUEST)) {
        	return $this->confirmationView(NAME_FORUM_THREAD_DEL.': ' . $thread->getCaption(),
                                           DO_ACTION_FORUM_THREAD_DEL,
                                           '/index.php?mod=forum&dest=modul&method=delThread&threadId=' . $thread->id . '&deleteConfirmation=yes',
                                           rewrite_forum(array()));            
        }                         
                        
        $this->addLog('delete Thread');
            
        $thread->delete();
        
        self::notifyAllParentFora($thread);
        
        /* do this: $this->viewAllForum();*/
        //header('Location: /index.php?mod=forum&method=viewThreadEntries&threadId='.$entry->getThreadId());
        $url = rewrite_forum(array('forumId' => $thread->getForumId(), 'extern' => true ));
        header('Location: '.$url);      
    }
    
    /**
     * add a forum to category
     */
    protected function addForum() {
        
        $cUser = Session::getInstance()->getVisitor();        
        
        $formFields = array(
            'description'   => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false),
            'forumName'   => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 200), 'escape' => true)                        
             );
        // some validity checks and HTML-escape
        $this->validateInput($formFields);
        if (count($this->errors) > 0){
            $this->errorView(ERR_FORUM_ADD);
        }
        
        /* test given input */
        if (!array_key_exists('categoryId', $_REQUEST) ||
            !array_key_exists('forumTemplate', $_REQUEST) ) {
            $this->errorView(ERR_FORUM_ADD);        
        }
        
        /* be sure that the filename includes no .. */
        if (!AttachmentHandler::isSafeFilename($_REQUEST['forumTemplate'])){
            Logging::getInstance()->logSecurity('Hack detected! Abort!');
        }
        
        /* for easier use */
        $categoryId = $_POST['categoryId'];
        $descRaw = trim($_POST['description']);
        $forumName = trim($_POST['forumName']);    
        
        $category = CategoryModel::getCategoryById($categoryId);
        if ($category == null){
            Logging::getInstance()->logSecurity('addForum: given category does not exists!');
        }
        
        $parentForum = null;
        if (array_key_exists('parentId', $_REQUEST)){
            $parentForum = ForumModel::getForumById($_REQUEST['parentId']);         
        }
        
        if (!$category->isModerator($cUser) && ($parentForum == null || !$parentForum->isModerator($cUser) )){
            Logging::getInstance()->logSecurity('addForum: You aren\'t a moderator for this category.');
        }        
        
        /* build new forum model */
        $forum = new ForumModel();
        $forum->setCategoryId($categoryId);
        $forum->setDescriptionRaw($descRaw);
        $forum->setName($forumName);
        
        if($parentForum !== null){
            $forum->setParentId($parentForum->id);                  
        } 
        
        $forum->setForumTemplate($_REQUEST['forumTemplate']);
        $forum->setVisibleId((int) $_REQUEST['visible']);
        $forum->setEnabledFormatcode(isset($_REQUEST['enableFormatcode']));
        $forum->setEnabledHTML(isset($_REQUEST['enableHtml']));
        $forum->setEnabledSmileys(isset($_REQUEST['enableSmileys']));
        $forum->setEnabledPostings(array_key_exists('enablePostings',$_REQUEST));
        $forum->setMayContainNews(array_key_exists('mayContainNews',$_REQUEST));
        if ($cUser->hasRight('FORUM_POINT_ADMIN')){
            $forum->setEnabledPoints(array_key_exists('enablePoints', $_REQUEST));
        }
        
        /* parse the moderators */
        if (array_key_exists('moderators', $_REQUEST)) {
            $usernames = explode(',', $_REQUEST['moderators']);
            $usernames = array_map('trim',$usernames);
            $users = UserProtectedModel::getUsersByUsernames($usernames);
            $forum->setModerators($users);
        }

        $this->addLog('add a new Forum');

        /* save the new model */
        $DB = Database::getHandle();
        
        $DB->startTrans();        
        
        $forum->save();
        $newForumId = $forum->id;
        
        if($forum->hasParent() && $forum->getParent()->isGroupForum()){
        	$forum->addGroup($forum->getParent()->getGroupId());
        }

        if (!$DB->CompleteTrans()){
            throw new DBException(DB_TRANSACTION_FAILED);
        }    
 
        $url = rewrite_forum(array('forumId' => $newForumId, 'extern' => true ));
        header('Location: '.$url);
    }
    
    protected function editForum() {

        /* test given input */
        if (!array_key_exists('forumId', $_REQUEST)) {
            $this->errorView(ERR_FORUM_SHOW);        
        }               
        
        $cUser = Session::getInstance()->getVisitor();
        $forum = ForumModel::getForumById($_REQUEST['forumId']);
        
        /* does the forum exists? */
        if ($forum == null) {
            Logging::getInstance()->logSecurity('editForum: The given forum id does not exists!');
        }
        if ($forum->hasParent()){
            $parentForum = $forum->getParent();     
        }
        
        if (!$forum->getCategory()->isModerator($cUser) && ($parentForum == null || !$parentForum->isModerator($cUser) )){
            Logging::getInstance()->logSecurity('editForum: Your account don\'t have enough rights to edit the forum!');
        }
        
        /* test wether the values of the forum should be shown */
        if (array_key_exists('showValues', $_REQUEST)) {
            
            /* get the template for postprocessing */
            if ($forum->hasParent()){      
                /* if it is a subforum */            
                $main = $this->viewAllThreads($forum->getParent()->id, false);
            }else{
            $main = $this->viewAllForum(false);
            }
            $main->assign('forumToEdit', $forum);
            $main->assign('forumCategory', $forum->getCategory());
            if($forum->isGroupForum() && !$forum->isGroupDefaultForum()){
                $main->assign('details_visible', DetailsVisibleModel::getAllDetailsVisible('group'));
            }else{
            	$main->assign('details_visible', DetailsVisibleModel::getAllDetailsVisible('general'));
            }
            
            $this->view();
            return;
        }

        $formFields = array(
            'description'   => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false),
            'forumName'  => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 200), 'escape' => true)
             );
        // some validity checks and HTML-escape
        $this->validateInput($formFields);
        if (count($this->errors) > 0){
            $this->errorView(ERR_FORUM_EDIT);
        }        
        
        /* be sure that the filename includes no .. */
        if (!AttachmentHandler::isSafeFilename($_POST['forumTemplate'])){
            Logging::getInstance()->logSecurity('Hack detected! Abort!');
        }
        
        //var_dump($_REQUEST);        
        /* write new forum model values */
        $forum->setDescriptionRaw($_POST['description']);
        $forum->setName($_POST['forumName']);
        $forum->setVisibleId((int)$_REQUEST['visible']);
        $forum->setEnabledFormatcode(!empty($_REQUEST['enableFormatcode']));
        $forum->setEnabledHTML(!empty($_REQUEST['enableHtml']));
        $forum->setEnabledSmileys(!empty($_REQUEST['enableSmileys']));
        $forum->setEnabledPostings(array_key_exists('enablePostings',$_REQUEST));
        $forum->setMayContainNews(array_key_exists('mayContainNews',$_REQUEST));
        $forum->setImportant(array_key_exists('isImportant',$_REQUEST));
        if ($cUser->hasRight('FORUM_POINT_ADMIN')){
            $forum->setEnabledPoints(array_key_exists('enablePoints', $_REQUEST));
        }
        $forum->setForumTemplate($_POST['forumTemplate']);
        
        /* parse the moderators */
        if (array_key_exists('moderators', $_REQUEST)) {
            $usernames = explode(',', $_REQUEST['moderators']);
            $usernames = array_map('trim',$usernames);
            $users = UserProtectedModel::getUsersByUsernames($usernames);
            $forum->setModerators($users);
        }
    
        $this->addLog('edit a Forum and Moderators');
    
        //var_dump($forum);
        /* save new values */
        $forum->save();
        
        /* use on subforum */
        if ($forum->hasParent()){
            $this->viewAllThreads($forum->getParent()->id);
            return true;
        }
          
        /* do this: $this->viewAllForum();*/
        $url = rewrite_forum(array('extern' => true));
        header('Location: '.$url);     
    }
    
    protected function delForum() {
        
        $parameters = $this->getParameters('delForum');
        
        $cUser = Session::getInstance()->getVisitor();
        $forum = $parameters['forum'];

        /* does the forum exists? */
        if ($forum == null) {
            Logging::getInstance()->logSecurity('delForum(): The given forum id does not exists!');
        }
        if (!$forum->getCategory()->isModerator($cUser)) {
            Logging::getInstance()->logSecurity('delForum(): Your account don\'t have enough rights to delete the forum!');
        }
        
         /* is the delete confirmend? */
        if (!array_key_exists('deleteConfirmation', $_REQUEST)) {
            return $this->confirmationView(NAME_FORUM_DEL.': ' . $forum->getName(),
                                           DO_ACTION_FORUM_DEL,
                                           '/index.php?mod=forum&dest=modul&method=delForum&forumId=' . $forum->id . '&deleteConfirmation=yes',
                                           rewrite_forum(array()));            
        }  
        
        $this->addLog('delete a Forum');
                  
        $forum->delete();
        
        self::notifyAllParentFora(null, $forum);
        
        /* do this: $this->viewAllForum();*/
        //header('Location: /index.php?mod=forum&method=viewAllForum');
        $url = rewrite_forum(array('extern' => true));
        header('Location: '.$url);      
    }
    /**
     * changes the position in the forum list to display
     */
    protected function rePosForum(){
         /* test given input */
         if (!array_key_exists('forumId', $_REQUEST)) {
            $this->errorView(ERR_FORUM_SHOW);        
        }
        
        $cUser = Session::getInstance()->getVisitor();
        $forum = ForumModel::getForumById($_REQUEST['forumId']);

        /* does the forum exists? */
        if ($forum == null) {
            Logging::getInstance()->logSecurity('rePosForum(): The given forum id does not exists!');
        }
        if (!$forum->getCategory()->isModerator($cUser) && !$cUser->hasRight('FORUM_CATEGORY_ADMIN')) {
            $this->rightsMissingView('FORUM_CATEGORY_ADMIN');
        }
        if (!array_key_exists('position',$_REQUEST) || !( $_REQUEST['position']=='up' || $_REQUEST['position']=='down' ) ){
            $this->errorView(ERR_FORUM_EDIT);
        }
        
        $this->addLog('change postion of a Forum');
    
        $forum->rePosition($_REQUEST['position']);
        
        /* use on subforum */
        if ($forum->hasParent()){      
            //var_dump($forum->parent->id);        
            /* do this: $this->viewAllThreads($forum->parent->id);*/
            //header('Location: /index.php?mod=forum&method=viewAllThreads&forumId='.$forum->parent->id);
            $url = rewrite_forum(array('forum' => $forum->getParent(), 'extern' => true ));
            header('Location: '.$url);
            return true;
        }
        /* use normal main forum */
        /* do this: $this->viewAllForum();*/
        //header('Location: /index.php?mod=forum&method=viewAllForum');
        $url = rewrite_forum(array('extern' => true));
        header('Location: '.$url);
    }

    /**
     * add a category to the database
     */    
    protected function addCategory() {
        
        $formFields = array(
            'description'   => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => true),
            'name' => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 200), 'escape' => true)                        
             );
        // some validity checks and HTML-escape
        $this->validateInput($formFields);
        if (count($this->errors) > 0){
            $this->errorView(ERR_CATEGORY_ADD);
        }
        
        /* test given input */
        if (!array_key_exists('categoryTemplate', $_REQUEST)) {
            $this->errorView(ERR_FORUM_NO_TEMPLATE);        
        }
        
        /* be sure that the filename includes no .. */
        if (!AttachmentHandler::isSafeFilename($_REQUEST['categoryTemplate'])){
            Logging::getInstance()->logSecurity('Hack detected! Abort!');
        }
        
        /* check that the user has the right to create categories */
        $cUser = Session::getInstance()->getVisitor();
        if (!$cUser->hasRight('FORUM_CATEGORY_ADMIN')){
            $this->rightsMissingView('FORUM_CATEGORY_ADMIN');
        }
            
        /* for easier use */
        $desc = $_POST['description'];
        $name = $_POST['name'];        
        
        /* build new category model */
        $c = new CategoryModel();
        $c->setName($name);
        $c->setDefaultTemplate($_REQUEST['categoryTemplate']);
        $c->setDescriptionRaw($desc);
        
        /* parse the moderators */
        if (array_key_exists('moderators', $_REQUEST)) {
            $usernames = explode(',', $_REQUEST['moderators']);
            $usernames = array_map('trim',$usernames);
            $users = UserProtectedModel::getUsersByUsernames($usernames);
            $c->setModerators($users);
        }
        if (array_key_exists('defaultForumModerators', $_REQUEST)) {
            $usernames = explode(',', $_REQUEST['defaultForumModerators']);
            $usernames = array_map('trim',$usernames);
            $users = UserProtectedModel::getUsersByUsernames($usernames);
            $c->setDefaultForumModerators($users);
        }
        
        $this->addLog('add a new Category');
    
        /* save the new model */
        $c->save();
        /* do this: $this->viewAllForum(); */
        //header('Location: /index.php?mod=forum&method=viewAllForum');
        $url = rewrite_forum(array('extern' => true));
        header('Location: '.$url);
    }

    /**
     * edit a category
     */    
    protected function editCategory() {

        $cUser = Session::getInstance()->getVisitor();                
        
        if (!$cUser->hasRight('FORUM_CATEGORY_ADMIN')){
                 $this->rightsMissingView('FORUM_CATEGORY_ADMIN');
        }
        
        if(!array_key_exists('catId', $_REQUEST)){
            Logging::getInstance()->logSecurity('editCategory(): The given category id does not exists!');
        }
        
        $category = CategoryModel::getCategoryById($_REQUEST['catId']);

        /* does the category exists? */
        if ($category == null) {
            Logging::getInstance()->logSecurity('editCategory(): The given category id does not exists!');
        }

        /* state one -> infos should shown in the form */
        if (array_key_exists('showValues', $_REQUEST)) {
            $main = $this->viewAllForum(false);

            $main->assign('categoryToEdit', $category);
            $this->view();
            return;
        }
                
        /* category is edited an should saved */
        
        $formFields = array(
            'description'   => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => true),
            'name' => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 200), 'escape' => true)                        
             );
        // some validity checks and HTML-escape
        $this->validateInput($formFields);
        if (count($this->errors) > 0){
            $this->errorView(ERR_CATEGORY_EDIT);
        }
        
        /* test given input */
        if (!array_key_exists('categoryTemplate', $_REQUEST) ||
            !array_key_exists('catId', $_REQUEST)) {
            $this->errorView(ERR_CATEGORY_ADD);        
        }

        /* be sure that the filename includes no .. */
        if (!AttachmentHandler::isSafeFilename($_REQUEST['categoryTemplate'])){
            Logging::getInstance()->logSecurity('Hack detected! Abort!');
        }
            
        /* for easier use */
        $desc = trim($_POST['description']);
        $name = trim($_POST['name']);        
        
        /* build new category model */
        $category->setName($name);
        $category->setDescriptionRaw($desc);
        $category->setDefaultTemplate($_REQUEST['categoryTemplate']);
        
        
        /* parse the moderators */
        if (array_key_exists('moderators', $_REQUEST)) {
            $usernames = explode(',', $_REQUEST['moderators']);
            $usernames = array_map('trim',$usernames);
            $users = UserProtectedModel::getUsersByUsernames($usernames);
            $category->setModerators($users);
        }
        if (array_key_exists('defaultForumModerators', $_REQUEST)) {
            $usernames = explode(',', $_REQUEST['defaultForumModerators']);
            $usernames = array_map('trim',$usernames);
            $users = UserProtectedModel::getUsersByUsernames($usernames);
            $category->setDefaultForumModerators($users);
        }
        
        $this->addLog('edit Category and Moderators');
    
        /* save the new model */
        $category->save();
        
        /* do this: $this->viewAllForum(); */
        //header('Location: /index.php?mod=forum&method=viewAllForum');
        $url = rewrite_forum(array('extern' => true));
        header('Location: '.$url);
    }

    protected function delCategory() {
        
        $parameters = $this->getParameters('delCategory');
        
        /* check that the user has the right to delete categories */
        $cUser = Session::getInstance()->getVisitor();
        if (!$cUser->hasRight('FORUM_CATEGORY_ADMIN')){
            $this->rightsMissingView('FORUM_CATEGORY_ADMIN');
        }

        $category = $parameters['cat'];
            
        /* does the category exists? */
        if ($category == null) {
            Logging::getInstance()->logSecurity('editCategory(): The given category id does not exists!');
        }
        
         /* is the delete confirmend? */
        if (!array_key_exists('deleteConfirmation', $_REQUEST)) {
            return $this->confirmationView(NAME_CATEGORY_DEL.': ' . $category->getName(),
                                           DO_ACTION_CATEGORY_DEL,
                                           '/index.php?mod=forum&dest=modul&method=delCategory&catId=' . $category->id . '&deleteConfirmation=yes',
                                           rewrite_forum(array()));            
        }        
        
        $this->addLog('delete a Cactegory');
    
        $category->delete();
        
        /* do this: $this->viewAllForum();*/
        //header('Location: /index.php?mod=forum&method=viewAllForum');
        $url = rewrite_forum(array('extern' => true));
        header('Location: '.$url);
    }
    
    /**
     * changes the position in the forum list to display
     */
    protected function rePosCategory(){
         /* test given input */
         if (!array_key_exists('catId', $_REQUEST)) {
            $this->errorView(ERR_CATEGORY_EDIT);        
        }
        
        $cUser = Session::getInstance()->getVisitor();
        $cat = CategoryModel::getCategoryById($_REQUEST['catId']);

        /* does the forum exists? */
        if ($cat == null) {
            Logging::getInstance()->logSecurity('rePosCategory(): The given forum id does not exists!');
        }
        if (!$cUser->hasRight('FORUM_CATEGORY_ADMIN')) {
            $this->rightsMissingView('FORUM_CATEGORY_ADMIN');
        }if (!array_key_exists('position',$_REQUEST) || !( $_REQUEST['position']=='up' || $_REQUEST['position']=='down' ) ){
            $this->errorView(ERR_CATEGORY_EDIT);
        }
        
        $this->addLog('change the postion of a Category');
    
        $cat->rePosition($_REQUEST['position']);
        
        /* use normal main forum */
        /* do this: $this->viewAllForum(); */
        //header('Location: /index.php?mod=forum&method=viewAllForum');
        $url = rewrite_forum(array('extern' => true));
        header('Location: '.$url);
    }
    
    protected function searchThreadEntries() {
        if (TSEARCH2_AVAILABLE != 'true') {
            $this->errorView(ERR_FUNCTION_NOT_AVAILABLE);
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            FORUM_TEMPLATE_DIR . 'search_thread_entries.tpl');
        
        $cUser = Session::getInstance()->getVisitor();
        
        // by default empty query string
        $query = '';
        
        if (array_key_exists(F_SEARCH_SUBMIT, $_REQUEST)) {
            $threads = ThreadEntryModel::getThreadEntryByFulltext(
                    ParserFactory::getRawParser()->parseQuery($_REQUEST[F_SEARCH_QUERY]),
                    10, null, 
                    $cUser->isLoggedIn(), $cUser->getGroupMembership());
            $main->assign('threadEntries', $threads);
            $query = $_REQUEST[F_SEARCH_QUERY];
        }
        
        $main->assign('query', $query);
        $this->setCentralView($main);
        
        $this->view();
    }
    
    /**
     * make a thread sticky
     */
    protected function ThreadStickyState() {
        /* check if information given */
        if (!array_key_exists('threadId', $_REQUEST)){
            $this->errorView(ERR_FORUM_THREAD_SHOW);
        }
        
        $thread = ThreadModel::getThreadById($_REQUEST['threadId']);
        if ($thread == null){
            Logging::getInstance()->logSecurity('toggleThreadStickyState: given thread does not exists!');
        }
        
        $cUser = Session::getInstance()->getVisitor();
        if (!$thread->getForum()->isModerator($cUser)){
            Logging::getInstance()->logSecurity('You aren\'t a moderator for this forum!.');
        }
        if (array_key_exists('isSticky',$_REQUEST) && ($_REQUEST['isSticky'] == 'true' || $_REQUEST['isSticky'] == 'false')){
            $thread->setSticky($_REQUEST['isSticky']);
            $thread->save();
        }
        else{
            $this->errorView(ERR_FORUM_THREAD_EDIT);
        }
        
        $this->addLog('change Thread sticky');
    
        /* show the Threads from current forum */
        /* do this: $this->viewAllThreads($thread->forumId);*/
        $url = rewrite_forum(array('forumId' => $thread->getForumId(), 'extern' => true ));
        header('Location: '.$url);
    }
    
    /**
     * close a thread
     */
    protected function ThreadCloseState() {
        /* check if information given */
        if (!array_key_exists('threadId', $_REQUEST)){
            $this->errorView(ERR_FORUM_THREAD_SHOW);
        }
        
        $thread = ThreadModel::getThreadById($_REQUEST['threadId']);
        $cUser = Session::getInstance()->getVisitor();
        if ($thread == null){
            Logging::getInstance()->logSecurity('toggleThreadCloseState: given thread does not exists!');
        }
        if (!$thread->getForum()->isModerator($cUser)){
            Logging::getInstance()->logSecurity('You aren\'t a moderator for this forum!.');
        }
        if (array_key_exists('isClosed',$_REQUEST) && ($_REQUEST['isClosed'] == 'true' || $_REQUEST['isClosed'] == 'false')){
            $thread->setClosed($_REQUEST['isClosed']);
            $thread->save();
        } else {
            throw new ArgumentException('isClosed');
        }

        
        $this->addLog('change Thread close');
    
        /* show the Threads from current forum */
        /* do this: $this->viewAllThreads($thread->forumId);*/
        $url = rewrite_forum(array('forumId' => $thread->getForumId(), 'extern' => true ));
        header('Location: '.$url);
    }
    
    /**
     * make a thread sticky
     */
    protected function ThreadVisibleState() {
        /* check if information given */
        if (!array_key_exists('threadId', $_REQUEST)){
            $this->errorView(ERR_FORUM_THREAD_SHOW);
        }
        
        $thread = ThreadModel::getThreadById($_REQUEST['threadId']);        
        $cUser = Session::getInstance()->getVisitor();
        if ($thread == null){
            Logging::getInstance()->logSecurity('toggleThreadVisibleState: given thread does not exists!');
        }
        if (!$thread->getForum()->isModerator($cUser)){
            Logging::getInstance()->logSecurity('You aren\'t a moderator for this forum!.');
        }
        if (array_key_exists('isVisible',$_REQUEST) && ($_REQUEST['isVisible'] == 'true' || $_REQUEST['isVisible'] == 'false')){
            $thread->setVisible($_REQUEST['isVisible']);
            $thread->save();
        }
        else{
            $this->errorView(ERR_FORUM_THREAD_EDIT);
        }
        
        $this->addLog('change Thread visible');
    
        /* show the Threads from current forum */
        /* do this: $this->viewAllThreads($thread->forumId);*/
        //header('Location: /index.php?mod=forum&method=viewAllThreads&forumId='.$thread->forumId);
        $url = rewrite_forum(array('forumId' => $thread->getForumId(), 'extern' => true ));
        header('Location: '.$url);
    }
    
    protected function editTagsForum(){

        $parameters = $this->getParameters('editTagsForum');
        
        $cUser = Session::getInstance()->getVisitor();
        $forum = $parameters['forum'];
        
        /* does the forum exists? */
        if ($forum == null) {
            Logging::getInstance()->logSecurity('editForum: The given forum id does not exists!');
        }
        if (!$cUser->hasRight('TAG_MAP_ADMIN')) {
            $this->rightsMissingView('TAG_MAP_ADMIN');
        }
        if (array_key_exists('saveTag', $_POST)){
            $signTags = $_REQUEST['newTags'];
            //var_dump($signTags);
            
            TagModel::setForumTag($forum->id, $signTags);
            
            $this->addLog('edit FormTags');
            if ($forum->hasParent()){
                /* do this: $this->viewAllThreads($forum->parent->id);*/
                //header('Location: /index.php?mod=forum&method=viewAllThreads&forumId='.$forum->parent->forumId);
                $url = rewrite_forum(array('forumId' => $forum->getParent()->id, 'extern' => true ));
                header('Location: '.$url);
                return;
            }
            
            /* go back to all Forum */    
            /* dot this: $this->viewAllForum(true);*/
            //header('Location: /index.php?mod=forum&method=viewAllForum');
            $url = rewrite_forum(array('extern' => true));
            header('Location: '.$url);
            return;
        }
        
        /* the edit view */
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . 'edit_forum_tags.tpl');
        // CACHEME: how could be cache this page??
        $main->setCacheParameter(900, '/modules/forum/edit_forum_tags');
        
        $tags = TagModel::getAllTags();
        
        $main->assign('tags', $tags);
        $main->assign('forum', $forum);

        $this->setCentralView($main);
        $this->view();
    }
    
    protected function ForumAbo(){
        
        $cUser = Session::getInstance()->getVisitor();
        if(!$cUser->isRegularLocalUser()){
        	$this->errorView(ERR_FUNCTION_NOT_AVAILABLE);
        }
        
        if (empty($_REQUEST['threadId'])){
            $this->errorView(ERR_FORUM_THREAD_SHOW);
        }
        
        $threadId = $_REQUEST['threadId'];
        $thread = ThreadModel::getThreadById($threadId);
        if ($thread == null){
            $this->errorView(ERR_FORUM_THREAD_SHOW);
        }
        if (!empty($_REQUEST['remove'])){
            $thread->removeAbo($cUser->id);
        }else{
            $thread->addAbo($cUser->id);
        }
        
        header('Location: '.rewrite_forum(array('threadId' => $thread->id, 'extern' => true)));
        return true;
    }
    
    protected function ForumRating(){
        
        $cUser = Session::getInstance()->getVisitor();
        
        if (!$cUser->hasRight('FORUM_THREAD_RATING')) {
            $this->rightsMissingView('FORUM_THREAD_RATING');
            return;
        }
        if (!empty($_REQUEST['threadId'])){
            $threadId = $_REQUEST['threadId'];
        }
        else{
            $this->errorView(ERR_FORUM_THREAD_SHOW);
        }

        $thread = ThreadModel::getThreadById($threadId);
        
        if ($thread == null) {
            Logging::getInstance()->logSecurity(ERR_FORUM_THREAD_SHOW);
        }
        if (!empty($_REQUEST['rateid'])){
            $ratingUser = UserProtectedModel::getUserById($_REQUEST['rateid']);
            if ($ratingUser == null){
                $this->errorView(ERR_FORUM_THREAD_RATING_USER);
            }
        }
        else{
            $this->errorView(ERR_FORUM_THREAD_RATING_USER);
        }
        if ($cUser->equals($ratingUser)){
            Logging::getInstance()->logSecurity(ERR_FORUM_THREAD_RATING_OWN, false);
        }
        if (empty($_REQUEST['rating']) || !( $_REQUEST['rating'] == 'pos' || $_REQUEST['rating'] == 'neg' ) ){
            $this->errorView(ERR_FORUM_THREAD_RATING_NOVALUE);
        }
        if ($_REQUEST['rating'] == 'pos'){
            $value = 1;
        }
        else{
            $value = -1;
        }
        if (!$thread->isAuthor($cUser)){
            Logging::getInstance()->logSecurity(ERR_FORUM_THREAD_RATING_NOWRITE, false);
        }
        if (!$thread->isAuthor($ratingUser)){
            Logging::getInstance()->logSecurity(ERR_FORUM_THREAD_RATING_NOAUTHOR, false);
        }
        if ($thread->hasUserRating($ratingUser, $cUser)){
            $this->errorView(ERR_FORUM_THREAD_RATING_REPEAT);
        }
        
        $thread->addRating($ratingUser, $cUser, $value);
        
        $page = array_key_exists('page', $_REQUEST) ? $_REQUEST['page'] : 1 ;
        header('Location: '.rewrite_forum(array('threadId' => $thread->id, 'page'=> $page, 'extern' => true)));
        return true;
    }
    
    protected function forumFeed() {
        $forumId = InputValidator::getRequestData('forumId', null);
        $userAuth = InputValidator::getRequestData('userauth', null);
        $entries = null;
        $feedMetadata = null;
        if (null == $forumId) {
            $feedMetadata = array(
                'title' => NAME_FORUM_LATEST_RSS,
                'description' => NAME_FORUM_LATEST_RSS,
                'url' => rewrite_forum(array("extern" => true))
            );            
        } else {
            $forum = ForumModel::getForumById($forumId);
            $cUser = Session::getInstance()->getVisitor();
            $authUser = null;
            if ($forum == null) {
                die();
            }
            if (!$cUser->isLoggedIn() && $userAuth != null) {
                $authUser = verifyURLHash($userAuth);
            }
            $forumVisible = $forum->getVisibleObj();
            if ($authUser == null && !$cUser->isLoggedIn() && $forumVisible->name == 'logged in'){
                $this->errorView(ERR_FORUM_LOGIN);
            }
            if ($forumVisible->name == 'group' && !(($authUser != null && $authUser->isMemberOfGroup($forum->getGroupId())) || $cUser->isMemberOfGroup($forum->getGroupId()))){
                $this->errorView(ERR_FORUM_GROUP);
            }
            
            $entries = ThreadModel::getAllThreadsByForumId($forumId);
            
            $name = "";
            if (count($entries) > 0) {
                $name = $entries[0]->getForum()->getName();
            }
            $entries = BaseModel::applyProxy($entries, new ThreadModelFeedProxy);
            
            $feedMetadata = array(
                'title' => NAME_FORUM_LATEST_RSS . " / " . $name,
                'description' => NAME_FORUM_LATEST_RSS . " / " . $name,
                'url' => rewrite_forum(array("forumId" => $forumId, "extern" => true))
            );
        }
        
        if (null == $forumId) {
            $main = $this->showFeed(null, $feedMetadata, false);
       
            if (defined('CACHETEST')) {
                $main->enableCaching();
                $main->setCacheParameter(7200, 'forum|latest');
            }
            
            self::observeIPC(
                new GlobalIPC, 
                array('FORUM_THREAD_CHANGED'),
                $main, 'forum|latest');
            
            if (!$main->isCached()) {
                $entries = ThreadModel::getAllThreadsByLatestUser(new UserAnonymousModel, 10, 0, 'desc', false, '');
                $entries = BaseModel::applyProxy($entries, new ThreadModelFeedProxy);
                $main->assign('entries', $entries);
            }
            
            // display as XML
            $main->display(true);
        } else {
            $this->showFeed($entries, $feedMetadata);
        }
    }
    
    protected static function getThreadPageCachekey($threadId, $page = null) {
        $str = 'forum|' . $threadId;
        if ($page != null) {
            $str .= '|' . $page;
        }
        return $str;
    }
    
    protected static function getThreadEntryCachekey($threadEntryId) {
        return 'forum|thread|' . $threadEntryId;
    }
    
    
    protected function ajaxPreview(){
    	$cUser = Session::getInstance()->getVisitor();
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), FORUM_TEMPLATE_DIR . '/internal/preview.tpl');
        
        $formFields = array(        
        'entryText' => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false),
        'caption'=> array('required' => false, 'check' => 'isValidAlmostAlways', 'escape' => true)
         );

        // some validity checks and HTML-escape
        $this->validateInput($formFields);
        
        if (strlen($_POST['entryText']) > V_ENTRY_MAX_CHARS) {
            self::addIncompleteField($this->errors, 'entryText', ERR_ENTRY_TOO_LONG);
        }
        if (strlen($_POST['caption']) > V_CAPTION_MAX_CHARS) {
           self::addIncompleteField($this->errors, 'caption', ERR_ENTRY_TITLE_TOO_LONG);
        }
        
        $entryText = trim($_POST['entryText']);
        if (array_key_exists('caption', $_POST)){
            $nullParser = ParserFactory::getFormatcodeNullParser();    
            $caption = $nullParser->parse(trim($_POST['caption']));
        }
        else{
            $caption = '';
        }
        /* if user want to post a anonym posting */
        if (array_key_exists('for_group', $_POST) && $_POST['for_group'] == -1){
            if ($cUser->hasEnoughPoints('FORUM_ANONYMOUS_POSTING')){
                $isAnonymous = true;
            }
            else{
                $this->errors['enable_anonymous'] = ERR_FORUM_POINTS;
                $isAnonymous = false; 
            }
        } else {
            $isAnonymous = false;   
        }
        $threadId = $_REQUEST['threadId'];
        
        $editMode = !empty($_REQUEST['entryId']);
        /* User only allow to add text on edit */
        $editModeAddText = false;
        
        /* search entry objekt */
        // Search for Objekt which want to edit
        if($editMode){
            $entry = Session::getInstance()->getEntryDataChecked('forum', $_REQUEST['entryId']);
            if ($entry == false){
                /* show the entry that will be edit in the text field */
                $entry = ThreadEntryModel::getThreadEntryById($_REQUEST['entryId']);
            }
            
            if( $entry->getForum()->isModerator($cUser)|| ((int)$entry->getTimeEntry())+(V_FORUM_EDIT_THREAD_ENTRY_TIME*3600) > time()){
                $editModeAddText = false;
            }else{
                $editModeAddText = true;
            }
            
        // search for a singe entry
        }elseif($threadId != 'new'){
            $entry = Session::getInstance()->getEntryDataChecked('forum', null);
        // search for a entry while creating thread
        }else{
        	$entry = Session::getInstance()->getEntryDataChecked('newthread', null); 
        }
        
        $parser = self::getAjaxParseSettings();
        if ($entry != false){
            if($editModeAddText){
            	$raw = Session::getInstance()->getEntryData('forum_' . $_REQUEST['entryId'].'_raw');
                if($raw == null){
                    $raw = $entry->getContentRaw();
                    Session::getInstance()->storeEntryData('forum_' . $_REQUEST['entryId'].'_raw', $raw );
                } 
                $comment = $_POST['entryText'];
                $text = $raw . "\n\n" . $comment;
                $entry->setContentRaw($text);
            }
            else{
                $entry->setContentRaw($entryText);
            }
            $entry->setCaption($caption);
            if(!$editMode){
                $entry->setAnonymous($isAnonymous);
            }
            $entry->setParseSettings($parser);
        //create new objekt
        }else{
            if($threadId == 'new'){
                $entry = new ThreadEntryModel($entryText, $cUser, $parser, null, $isAnonymous, $caption);
                if(!empty($_REQUEST['forumId'])){
                    $forum = ForumModel::getForumById($_REQUEST['forumId']);
                    $entry->setForum($forum);
                }
            }else{
                $entry = new ThreadEntryModel($entryText, $cUser, $parser, $threadId, $isAnonymous, $caption);
            }
        }
        
        if (array_key_exists('for_group', $_REQUEST) && !$editMode) {
          if ($cUser->hasGroupRight('FORUM_GROUP_THREAD_ENTRY_ADD', $_REQUEST['for_group'])) {
            $entry->setGroupId($_REQUEST['for_group']);
          } else {
            $entry->setGroupId(0);
          }
        }
        
        //var_dump($entry);
        
        $main->assign('isPreview',true);
        $main->assign('entryToEdit',$entry);
        
        
        $main->display();
    }
	
	public function viewThreadLastRead() {
		$threadId = InputValidator::getRequestData('threadId', 0);
		$lastEntryId = ForumRead::getInstance(Session::getInstance()->getVisitor(), false)->lastEntryId($threadId);
		
		if ($lastEntryId > 0) {
			header('Location: ' . rewrite_forum(array('entryId' => $lastEntryId, 'extern' => true)));
		} else {
			header('Location: ' . rewrite_forum(array('threadId' => $threadId, 'extern' => true)));
		}
	}
}
?>
