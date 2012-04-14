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

// $Id: index_business_logic_controller.php 6210 2008-07-25 17:29:44Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/businesslogic/index/index_business_logic_controller.php $

require_once CORE_DIR . '/businesslogic/business_logic_controller.php';

require_once CORE_DIR.'/models/forum/forum_model.php';
require_once CORE_DIR.'/models/forum/thread_model.php';
require_once CORE_DIR.'/models/forum/forum_read.php';
require_once CORE_DIR.'/models/news/news_model.php';
require_once CORE_DIR.'/models/news/news_entry.php';
require_once CORE_DIR.'/models/news/news_entry_feed_proxy.php';
require_once CORE_DIR.'/models/event/event_entry.php';
require_once CORE_DIR.'/models/event/event_entry_feed_proxy.php';
require_once CORE_DIR.'/models/event/event_entry_cal_proxy.php';

require_once CORE_DIR . '/utils/client_infos.php';

// load some useful constants for working on <form>-input
require_once CORE_DIR . '/constants/form_constants.php';
require_once CORE_DIR . '/constants/value_constants.php';

require_once MODEL_DIR . '/base/banner_model.php';
require_once MODEL_DIR . '/base/banner_click_model.php';

require_once CORE_DIR . '/utils/global_ipc.php';

/**
 * The index site with short forumoverview, the news and more 
 * This is our first site 
 * and our 'Spielwiese' for the new MVC concept.
 */
class IndexBusinessLogicController extends BusinessLogicController {
	
	public function __construct($ajaxView = false) {
        parent::__construct($ajaxView);
	}	
	
    /** 
     * Defoult Methode: we want to see the Overview of all Forums 
     */
    protected function getDefaultMethod() {
        return 'home';
    }
    
    /**
      * List of al methods who ar allowed
      */
    public function getAllowedMethods() {
        $array = parent::getAllowedMethods();
        array_push($array, 
            'home',
            
            'bannerClick',
            
            'addNewsEntry',
            'editNewsEntry',
            'delNewsEntry',
            'moveNewsEntry',
	        'newsArchive',
	        
	        'showEvents',
	        'addEvents',
            'editEvents',
	        'delEvents',
            'eventsCal',
            
            'imprint',
			'privacy',
			'terms_of_use',
            'smileys',
            'faq',
            'help',
            'helpNew',
            'helpFormatcode',
            
            'toolbar',
            'toolbarDownload',
            
            'NewsFeed',
            'EventsFeed',
            'ajaxNewsPreview',
            'ajaxToolbar',
            
            'flatView',
	        
            'chat',
            
            'easterEgg',
            'showVideo',
            'jobsOnUniHelp'
            );
        return $array;
      }
    
    public function getMethodObject($method) {
        if ('home' == $method) {
            return new BLCMethod(NAME_HOME, rewrite_index(array()), null, '/home/news/rss', NAME_NEWS_RSS);
        } else if ('imprint' == $method) {
            return new BLCMethod(NAME_IMPRINT, rewrite_index(array('imprint' => true)), BLCMethod::getDefaultMethod());
        } else if ('privacy' == $method) {
            return new BLCMethod(NAME_PRIVACY, rewrite_index(array('privacy' => true)), BLCMethod::getDefaultMethod());
        } else if ('terms_of_use' == $method) {
            return new BLCMethod(NAME_TERMS_OF_USE, rewrite_index(array('termsOfUse' => true)), BLCMethod::getDefaultMethod());
        } else if ('faq' == $method) {
            return new BLCMethod(NAME_FAQ, rewrite_help(array('faq' => true)), $this->getMethodObject('help'));
        } else if ('helpFormatcode' == $method) {
            return new BLCMethod(NAME_FORMATCODE, rewrite_help(array('formatcode' => true)), $this->getMethodObject('help'));
        } else if ('help' == $method ) {
            return new BLCMethod(NAME_HELP, null, BLCMethod::getDefaultMethod());
        } else if ('helpNew' == $method) {
            return new BLCMethod('Was ist neu?', null, BLCMethod::getDefaultMethod());
        } else if ('newsArchive' == $method){
        	$parent = $this->getMethodObject('home');
            return new BLCMethod(NAME_NEWS_ARCHIEV, '/home/oldnews', $parent);
        } else if ('addNewsEntry' == $method){
            $parent = $this->getMethodObject('home');
            return new BLCMethod(NAME_NEWS_ADD, '/home/news/add', $parent);
        } else if ('editNewsEntry' == $method){
            $parent = $this->getMethodObject('home');
            $parameters = $this->getParameters('editNewsEntry');
            return new BLCMethod(NAME_NEWS_EDIT, '/home/news/'.$parameters['entry']->id.'/edit', $parent);
        } else if ('delNewsEntry' == $method){
            $parent = $this->getMethodObject('home');
            $parameters = $this->getParameters('delNewsEntry');
            return new BLCMethod(NAME_NEWS_DEL, '/home/news/'.$parameters['entry']->id.'/del', $parent);
        } else if ('toolbar' == $method) {
            return new BLCMethod(NAME_TOOLBAR, rewrite_index(array('toolbar' => true)), BLCMethod::getDefaultMethod());
        } else if ('easterEgg' == $method) {
            return new BLCMethod('verborgene Seite', '/orgasm', BLCMethod::getDefaultMethod());
        } else if ('showEvents' == $method) {
            // TODO: use parameters here to build correct link
            return new BLCMethod(NAME_EVENTS, rewrite_index(array('events' => true)), BLCMethod::getDefaultMethod(), '/index.php?mod=index&method=EventsFeed&view=ajax',NAME_EVENTS_FEED);
        } else if ('addEvents' == $method) {
            // TODO: use parameters here to build correct link
            return new BLCMethod(NAME_EVENTS, rewrite_index(array('events' => true)), BLCMethod::getDefaultMethod());
        } else if ('editEvents' == $method) {
            // TODO: use parameters here to build correct link
            return new BLCMethod(NAME_EVENTS, rewrite_index(array('events' => true)), BLCMethod::getDefaultMethod());
        } else if ('delEvents' == $method) {
            // TODO: use parameters here to build correct link
            return new BLCMethod(NAME_EVENTS, rewrite_index(array('events' => true)), BLCMethod::getDefaultMethod());
        }
        
        else if ('flatOverView' == $method) {
            return new BLCMethod('Wohnungsmarkt', '/flatmarket', BLCMethod::getDefaultMethod());
        }
        else if ('flatView' == $method) {
            return new BLCMethod('Wohnungsdetails', '/flatmarket', $this->getMethodObject('flatOverView'));
        }
        
        else if ('jobsOnUniHelp' == $method) {
            return new BLCMethod('Jobs und Praktika', '/jobs', BLCMethod::getDefaultMethod());
        }
        else if ('showVideo' == $method) {
            return new BLCMethod('UniHelp Video', '', BLCMethod::getDefaultMethod());
        }
        
        return parent::getMethodObject($method);
    }
    
    protected function collectParameters($method) {
    	$parameters = array();
        if('editNewsEntry' == $method){
            /* get the right NewsModel */
            $parameters['entry'] = Session::getInstance()->getEntryDataChecked('news', InputValidator::getRequestData('newsId',0));
            if ($parameters['entry'] == false){    
                $parameters['entry'] = NewsEntryModel::getNewsById(InputValidator::getRequestData('newsId',0));
            }
        }elseif('delNewsEntry' == $method){
            /* get the right NewsModel */
            $parameters['entry'] = NewsEntryModel::getNewsById(InputValidator::getRequestData('newsId',0));
        }
        
        
        $this->_parameters[$method] = $parameters;

        parent::collectParameters($method);
    }
    
    protected function bannerClick() {
        
        if(!array_key_exists('banner_id', $_REQUEST))
            return $this->defaultView();
            
        $bId = $_REQUEST['banner_id'];
        
        if(!is_numeric($bId))
            return $this->defaultView();
        
        $banner = BannerModel::getBannerById($bId);
        
        if($banner == null)
            return $this->defaultView();
            
        $banner->addClick();
        
        $clickModel = new BannerClickModel();
        $clickModel->bannerId = $bId;
        $clickModel->ip = ClientInfos::getClientIP();
        
        $cUser = Session::getInstance()->getVisitor();
        
        if ($cUser->isExternal()) {
            $clickModel->user_ext_id = $cUser->id;
        } else {
            $clickModel->user_int_id = $cUser->id;
        }
            
        $clickModel->save();
        
        header('Location: ' . $banner->getDestURL());
    }
    
    protected function getHomeThreadBoxesView() {
		$views = array();
		
		/*
		 * general threads
		 */
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'index_threads.tpl');

        // get the current user
        $cUser = Session::getInstance()->getVisitor();
        
        $cacheKey = 'index|boxes';
        if ($cUser->isLoggedIn()) {
            $groupIds = '|9';
            foreach ($cUser->getGroupMembership() as $g) {
                $groupIds .= '0' . $g->id;
            }
            $cacheKey .= $groupIds;
        } else {
            $cacheKey .= '|loggedoff';
        }
        
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        $main->setCacheParameter(-1, $cacheKey);
        
        self::observeIPC(
            new GlobalIPC, 
            array('FORUM_THREAD_CHANGED'),
            $main, 'index');
        
        // initialize ForumRead
        ForumRead::getInstance($cUser, true);
        
        $threadNumber = 4;
        if ($cUser->isLoggedIn()) {
            // for logged-in users we have more space to display threads
            $threadNumber = 6;
        }
		
		if (!$main->isCached()) {
            $main->assign('threads_overview', ThreadModel::getAllThreadsByLatestUser($cUser, $threadNumber, 0,'desc', false, 'default'));
        }
		
		array_push($views, $main);
		
		
		/*
		 * additional box
		 */
        require_once MODEL_DIR . '/sports/soccer_games_model.php';
        $emGames = SoccerGamesModel::getByToday();
		$hour = date('H');
		if (count($emGames) > 0 && $hour >= 18 && $hour <= 22) {
			$game = $emGames[0];
            // show first unfinished game
			foreach ($emGames as $g) {
				if (!$g->isFinished()) {
					$game = $g;
                    break;
				}
			}
			$additionalBox = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'index_threads_soccer.tpl');
			$additionalBox->assign('game', $game);
        } else if (defined('WEBCAM_URL') and WEBCAM_URL != '') {
			$additionalBox = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'index_threads_webcam.tpl');
			$url = WEBCAM_URL;
			if (strpos($url, '?') !== false) {
				$url .= '&r=' . rand();
			} else {
				$url .= '?r=' . rand();
			}
			$additionalBox->assign('url', $url);
			$additionalBox->assign('title', defined('WEBCAM_TITLE') ? WEBCAM_TITLE : '');
        } else {
			$additionalBox = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'index_threads_courses.tpl');
			$additionalBox->assign('threads_course', ThreadModel::getAllCourseThreadsByUser($threadNumber, 0 ,'desc', false, $cUser));
        }
        array_push($views, $additionalBox);
        
        return $views;
    }
    
    /**
     * This is the view of the Homepage of Unihelp
     * <b>template: index.tpl<b>
     * This Parameter the Template gets<ul>
     * <li><var>lastest_user_username</var><b>string</b>the name of the latest User who regester</li>
     * <li><var>threads</var><b>array of ThreadModel</b> the last 5 Threads of Chategory 'Fäscher'</li>
     * <li><var>visitor</var><b>UserModel</b> the User himself (just the visitor)</li>
     * <li><var>news</var><b>array of NewsEntryModel</b> the latest News</li>
     * <li><var>isAdd</var><b>boolean</b> if we are in addNews mod</li>
     * </ul>
     */	
    protected function home() {
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'index.tpl');
        
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        // get the current user
        $cUser = Session::getInstance()->getVisitor(); 
        
        $main->setCacheParameter(-1, 'index|main|' . ($cUser->isLoggedIn() ? 'on' : 'off'));
        
        self::observeIPC(
            new GlobalIPC, 
            array('NEW_USER'),
            $main, 'index|main');

               
        
        $main->assign('threadsBoxes', $this->getHomeThreadBoxesView());
        
        if (!$main->isCached()) {
                    
            $newUser = UserProtectedModel::getUserByNewest();
            $main->assign('newUser',$newUser);        
            
            $main->assign('latest_user_number',UserProtectedModel::countUser());
        }
        
        /* filter for the public news */
        $newsFilter = new NewsFilter();
        $newsFilter->addFilter(NewsFilter::FILTER_START_DATE, '<=', NewsFilter::CURRENT_DATE, '');
        $newsFilter->addFilter(NewsFilter::FILTER_END_DATE, '>=', NewsFilter::CURRENT_DATE);
        $newsFilter->addFilter(NewsFilter::FILTER_VISIBLE, '=', true);
        
        $news = NewsEntryModel::getEntryIdsByFilter($newsFilter);
        $newsEntryViews = array();
        foreach ($news as $id) {
            array_push($newsEntryViews, $this->getNewsEntryView($id, defined('CACHETEST')));
        }
        
        /* News reading - news box */
        //$main->assign('news', NewsEntryModel::getEntriesByFilter($newsFilter));
        $main->assign('news', $newsEntryViews);
        //$main->assign('_news', $this->getNewsEntryView(1, defined('CACHETEST')));
        
        // only group members can review news
        if (count($cUser->getGroupMembership()) > 0) {
            /* filter for news that needs a review */
            $newsFilter = new NewsFilter();
            $newsFilter->addFilter(NewsFilter::FILTER_VISIBLE, '=', false, '');
            $newsFilter->addFilter(NewsFilter::FILTER_GROUPS, '=', $cUser->getGroupsByRight('NEWS_ENTRY_EDIT'));
    
            /* news for review */        
            $main->assign('invisibleNews', NewsEntryModel::getEntriesByFilter($newsFilter));
        }
        
        /* set as central template */
        $this->setCentralView($main, false, false); 

        /* output */   
        $this->view();
    }
    
    protected function getNewsEntryView($id, $enableCaching) {
        $entryView = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/news/part_news_entry.tpl');
        
        if ($enableCaching) {
            $entryView->enableCaching();
        }
        $cacheKey = 'news|' . $id; 
        // cache only for an hour in order to update "comments"-statistics
        $entryView->setCacheParameter(3600, $cacheKey);
        
        self::observeIPC(
            new GlobalIPC, 
            array('NEWS_CHANGED'),
            $entryView, 'news');
        
        $groupId = null;
        if (!$entryView->isCached() or !self::getCachedIds($cacheKey, $groupId)) {
            $entry = NewsEntryModel::getNewsById($id);
            
            $groupId = $entry->getGroupId();
            $entryView->assign('newsentry', $entry);
            self::cacheIds($cacheKey, array($groupId), true);
        } else {
            $groupId = $groupId[0];
        }
        
        $entryView->assign('newsentryGroupId', $groupId);
        $entryView->assign('newsentryId', $id);        
        return $entryView;
    }
    
   protected function newsArchive($displayView = true) {
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'modules/news/news_archive.tpl');
      
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        /* News reading - news box */
        $page = array_key_exists('page', $_REQUEST) ? $_REQUEST['page'] : 1;
        
        $cacheKey = 'news|' . $page;
        
        // CACHEME: cache "only" half a day, because
        // we want old news entries to expire
        $main->setCacheParameter(43200, $cacheKey);
      
        /* get the current user */
        $cUser = Session::getInstance()->getVisitor();        
                
        /** no invible Threads on the index page */
        $showInvisible = false;
        
        /* filter for the public news */
        $newsFilter = new NewsFilter();
        $newsFilter->addFilter(NewsFilter::FILTER_END_DATE, '<=', NewsFilter::CURRENT_DATE, '');
        $newsFilter->addFilter(NewsFilter::FILTER_VISIBLE, '=', true);

        
        if (!$main->isCached()) {
            $count = NewsEntryModel::countEntryIdsByFilter($newsFilter);
            $main->assign('counter', NewsModel::nonLinearCounter(ceil($count/V_NEWS_ARCHIV_ENTRIES_PER_PAGE), $page));
            $main->assign('page', $page);    
        }
        
        //$main->assign('news', NewsEntryModel::getEntriesByFilter($newsFilter, V_NEWS_ARCHIV_ENTRIES_PER_PAGE, ($page-1) * V_NEWS_ARCHIV_ENTRIES_PER_PAGE, 'desc', $count));
        $news = NewsEntryModel::getEntryIdsByFilter($newsFilter, V_NEWS_ARCHIV_ENTRIES_PER_PAGE, ($page-1) * V_NEWS_ARCHIV_ENTRIES_PER_PAGE, 'desc');
        $newsEntryViews = array();
        foreach ($news as $id) {
            array_push($newsEntryViews, $this->getNewsEntryView($id, defined('CACHETEST')));
        }
        
        /* News reading - news box */
        $main->assign('news', $newsEntryViews);
        
        /* set as central template */
        $this->setCentralView($main); 
        
        /* you want to get the Template back */ 
        if(!$displayView)
            return $main;
          
        /* output */   
        $this->view();
    } 
    
    /**
     * this will add a News or will show the addNewsMode
     * 
     * execute defaultView()
     * add the following var to the template
     * <ul>
     * <li><var>isPreview</var> <b>boolean</b> if we are in PreviewMode</li>
     * <li><var>isAdd</var> <b>boolean</b> we are in addNewsMode</li>
     * <li><var>entryToEdit</var> <b>NewsEntryModel</b> the model we edit or we want to preview</li>
     * <li><var>captionWarnung</var> <b>boolean</b> if it is a error we will show the user a warning</li>
     * </u1>
     * 
     */
    protected function addNewsEntry(){
    	/* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        if (!($cUser->hasGroupRight('NEWS_ENTRY_ADD') || $cUser->hasRight('NEWS_ENTRY_ADMIN'))){
            $this->rightsMissingView('NEWS_ENTRY_ADD'); 
        }
        
        if (!array_key_exists(F_ENTRY_RANDID, $_REQUEST)) {
            $entryRandId = Session::getInstance()->generateNewRandomId();
        } else {
            $entryRandId = $_REQUEST[F_ENTRY_RANDID];
        }
        
        /* test for preview mode */
        
        /*** show the input form ***/
        
        if(array_key_exists('showInput',$_REQUEST) && !$this->previewMode && !$this->uploadMode && empty($_POST['save']) && empty($_POST['makeVisible'])){
            $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'modules/news/add_news.tpl');
            $main->assign('newsForen', ForumModel::getForumByMayContainNews());
            
            $main->assign('isAdd', true);
            $main->assign('randid', $entryRandId);
            $main->assign('maxAttachmentSize', GlobalSettings::getGlobalSetting('ENTRY_NEWS_MAX_ATTACHMENT_SIZE') * 1024);
            
            $this->setCentralView($main);
            $this->view();
            return;
        }
        
        if(array_key_exists('groupId',$_REQUEST)){
            $groupId = $_REQUEST['groupId'];
        }else{
            $this->errorView(ERR_NO_GROUP);                
        } 
        
        /* is the user allowed to make a post */
        if(! ($cUser->hasGroupRight('NEWS_ENTRY_ADD',$groupId) || $cUser->hasRight('NEWS_ENTRY_ADMIN') ) ){
            $this->rightsMissingView('NEWS_ENTRY_ADD');     
        }
        
        $formFields = array(
            'caption'   => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 200), 'escape' => true),
            'entryText' => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false),
            'openerText'=> array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false)
             );
        // some validity checks and HTML-escape
        $this->validateInput($formFields);         
        
        /*** generate the model ***/
        $openerText = $_POST['openerText'];
        $entryText = $_POST['entryText'];
        if (array_key_exists('caption', $_POST)){
            $nullParser = ParserFactory::getFormatcodeNullParser();    
            $caption = $nullParser->parse(trim($_POST['caption']));
        }
        else{
            $caption = '';
        }   
        
       //$content_raw = null, $author = null, $parseSettings = array (), $caption = ''
        $entry = Session::getInstance()->getEntryDataChecked('news', null);
        if ($entry == false){
            /* build new entry model */
            $entry = new NewsEntryModel($openerText, $entryText, $cUser, self::getParseSettings(), $groupId, $caption);        
        } else {
            $entry->update($openerText, $entryText, self::getParseSettings(), $groupId, $caption);
        }
        
        /* bulid the target time */
        $startDate = parent::getSmartyDate($_REQUEST, 'start');
        $endDate   = parent::getSmartyDate($_REQUEST, 'end');

        if(empty($startDate)){
            $this->errorView(ERR_NEWS_START_DATE, E_USER_ERROR);
        }
        if(empty($endDate)){
        	$this->errorView(ERR_NEWS_END_DATE, E_USER_ERROR);
        }

        if(!InputValidator::isValidFutureDate($endDate)){
            $this->errors['endDate'] = ERR_NEWS_END_DATE;
        }
        
        $entry->setStartDate($startDate);        
        $entry->setEndDate($endDate);        
       
        $entry->setSticky(array_key_exists('isSticky', $_REQUEST) && 
                    ($cUser->hasGroupRight('NEWS_ENTRY_STICKY',$groupId) || $cUser->hasRight('NEWS_ENTRY_ADMIN') ));                                    
        
        /* get the template for postprocessing */
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'modules/news/add_news.tpl');
        $main->assign('newsForen', ForumModel::getForumByMayContainNews());
                
        // handle attachment(s) additions and removements
        if (!$this->handleAttachment($entry, $cUser, GlobalSettings::getGlobalSetting('ENTRY_NEWS_MAX_ATTACHMENT_SIZE') * 1024, true, $error)) {
            if ($error == AttachmentHandler::ERROR_MIME) {
				$this->errors['invalidFile'] = ERR_ATTACHMENT_IMAGE;
			} else {
				$this->errors['largeFile'] = ERR_ATTACHMENT;
			}
        }
        
        $targetForum = ForumModel::getForumById(InputValidator::getRequestData('newsForenId',-1));
        if(null == $targetForum  || !$targetForum->hasMayContainNews()){
        	$group = GroupModel::getGroupById($groupId);
            $targetForum = $group->getForum();
        }

        /* test wether preview is activated */
        if ($this->previewMode || $this->uploadMode || count($this->errors) > 0) {
            Session::getInstance()->storeEntryData('news', $entry);
   
            $main->assign('isPreview', $this->previewMode);
            $main->assign('isAdd', true);
            $main->assign('randid', $entryRandId);
            $main->assign('maxAttachmentSize', GlobalSettings::getGlobalSetting('ENTRY_NEWS_MAX_ATTACHMENT_SIZE') * 1024);
            $main->assign('entryToEdit', $entry);
            $main->assign('targetForum',$targetForum);
            
            $this->setCentralView($main);
            $this->view();
            return;
        }
        
        if(array_key_exists('makeVisible', $_REQUEST)){
            $entry->setVisible(true);
            $this->addThread($entry, false, $targetForum);
        }else{
            $entry->setVisible(false);
        }
        
        Session::getInstance()->deleteEntryData('news');
        
        if (!Session::getInstance()->removeRandomId($entryRandId)) {
            header('Location: ' . rewrite_index(array('extern' => true)));
            return;
        }
        
        $entry->postIp = ClientInfos::getClientIP();
        $entry->save();        
        
        self::notifyIPC(new GlobalIPC, 'NEWS_CHANGED');
        
        //$this->defaultView();
        header('Location: ' . rewrite_index(array('extern' => true)));
    }
      /**
     * this will edit a News or will show the editNewsMode
     * 
     * execute defaultView()
     * add the following var to the template
     * <ul>
     * <li><var>isPreview</var> <b>boolean</b> if we are in PreviewMode</li>
     * <li><var>entryToEdit</var> <b>NewsEntryModel</b> the model we edit or we want to preview</li>
     * </u1>
     * 
     */
    protected function editNewsEntry(){
    	/* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        $parameters = $this->getParameters('editNewsEntry');

        /* get the right NewsModel */
        $entryToEdit = $parameters['entry'];        
 
        /* we need a newId */    
        if($entryToEdit == null){
            $this->errorView(ERR_NO_NEWS);
        }
 
        /* is the user allowed to edit a post */
        if(! ($cUser->hasGroupRight('NEWS_ENTRY_EDIT',$entryToEdit->getGroupId()) || $cUser->hasRight('NEWS_ENTRY_ADMIN') ) ){
            $this->rightsMissingView('NEWS_ENTRY_EDIT');                       
        }
        
    	 if(  !$cUser->hasGroupRight('NEWS_ENTRY_EDIT',$entryToEdit->getGroupId()) ){
    		$this->addLog('edit a NewsEntry');
    	 }
	
        /* test for preview mode */
        
        $showEntryValues = array_key_exists('showEntryValues', $_REQUEST) && empty($_POST['newsId']);
        
        /* if only the edit form will shown */
        if(!$showEntryValues){
        	
               $formFields = array(
                'caption'   => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 200), 'escape' => true),
                'entryText' => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false),
                'openerText'=> array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false)
                 );
            // some validity checks and HTML-escape
            $this->validateInput($formFields); 
            
             /* get Data */
            $entryText = $_POST['entryText'];
            if (array_key_exists('caption', $_POST)){
                $nullParser = ParserFactory::getFormatcodeNullParser();    
                $caption = $nullParser->parse(trim($_POST['caption']));
            }
            else{
                $caption = '';
            }
            $openerText = $_POST['openerText'];
            
            /* bulid the target time */
            $startDate = parent::getSmartyDate($_REQUEST, 'start');
            $endDate   = parent::getSmartyDate($_REQUEST, 'end');
    
            if(empty($startDate)){
                $this->errorView(ERR_NEWS_START_DATE, E_USER_ERROR);
            }
            if(empty($endDate)){
                $this->errorView(ERR_NEWS_END_DATE, E_USER_ERROR);
            }
            
            /*if(!InputValidator::isValidFutureDate($endDate)){
                $this->errors['endDate'] = ERR_NEWS_END_DATE;
            }*/
            /*
             * is not confortable, may we use a 'notice' latter, but this has blocked
             * to edit old News
             * schnueptus (24.06.2007)
             */
        }    
        
        /* get the template for postprocessing */
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'modules/news/add_news.tpl');
        $main->assign('newsForen', ForumModel::getForumByMayContainNews());
        $this->setCentralView($main);            
        
        
        // handle attachment(s) additions and removements
        if (!$showEntryValues && !$this->handleAttachment($entryToEdit, $cUser, GlobalSettings::getGlobalSetting('ENTRY_MAX_ATTACHMENT_SIZE') * 1024, true, $error)) {
			if ($error == AttachmentHandler::ERROR_MIME) {
				$this->errors['invalidFile'] = ERR_ATTACHMENT_IMAGE;
			} else {
				$this->errors['largeFile'] = ERR_ATTACHMENT;
			}
        }
        
        $targetForum = ForumModel::getForumById(InputValidator::getRequestData('newsForenId',-1));
        if(null == $targetForum  || !$targetForum->hasMayContainNews()){
            $group = $entryToEdit->getGroup();
            $targetForum = $group->getForum();
        }
            
        /* should the array information be shown */
        if ($showEntryValues || $this->previewMode || $this->uploadMode || count($this->errors) > 0) {
            	           
            /* 
             * it's an preview. set flags for the template designer and
             * change the entry so that the new version is displayed 
             */
            if( $this->previewMode || $this->uploadMode) {
                
                $entryToEdit->setStartDate($startDate);
                $entryToEdit->setEndDate($endDate);
                $entryToEdit->setCaption($caption);
                $entryToEdit->setContentRaw($entryText, $openerText);
                if($cUser->hasGroupRight('NEWS_ENTRY_STICKY',$entryToEdit->getGroupId()) || $cUser->hasRight('NEWS_ENTRY_ADMIN')){
        		      $entryToEdit->setSticky(array_key_exists('isSticky', $_REQUEST));
                }
                Session::getInstance()->storeEntryData('news', $entryToEdit);
            }
            #var_dump($entryToEdit);
            $main->assign('isPreview', $this->previewMode);
            $main->assign('entryToEdit', $entryToEdit);
            $main->assign('maxAttachmentSize', GlobalSettings::getGlobalSetting('ENTRY_NEWS_MAX_ATTACHMENT_SIZE') * 1024);
            $main->assign('targetForum',$targetForum);
            
            $this->view();
            return;
            
        }
        
        /* no rechable code */
        $entryToEdit->setStartDate($startDate);
        $entryToEdit->setEndDate($endDate);
        $entryToEdit->setCaption($caption);             
        $entryToEdit->setContentRaw($entryText, $openerText);
        if(!$entryToEdit->isVisible() && array_key_exists('makeVisible', $_REQUEST)){
            $entryToEdit->setVisible(true);
        }elseif($entryToEdit->isVisible() && $cUser->hasRight('NEWS_ENTRY_ADMIN') && array_key_exists('removeVisible', $_REQUEST)){
            $entryToEdit->setVisible(false);
        }
        
        if($cUser->hasGroupRight('NEWS_ENTRY_STICKY',$entryToEdit->getGroupId()) || $cUser->hasRight('NEWS_ENTRY_ADMIN')){
            $entryToEdit->setSticky(array_key_exists('isSticky', $_REQUEST));
        }
        
        $entryToEdit->postIp = ClientInfos::getClientIP();    
        $entryToEdit->setAuthor($cUser);
        
        $this->editThread($entryToEdit, $targetForum);
        $entryToEdit->save(); 
        Session::getInstance()->deleteEntryData('news');
        
        self::notifyIPC(new GlobalIPC, 'NEWS_CHANGED');

        //$this->defaultView();
        header('Location: ' . rewrite_index(array('extern' => true)));        
    }
    
     protected function delNewsEntry(){
     	
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        $parameters = $this->getParameters('delNewsEntry');

        /* get the right NewsModel */
        $entry = $parameters['entry'];        
 
        /* we need a newId */    
        if($entry == null){
            $this->errorView(ERR_NO_NEWS);
        }
        
        /* 
         * is the user allowed to del a post 
         * - is admin
         * - news was not published
         */
        if(!$cUser->hasRight('NEWS_ENTRY_ADMIN') && !($cUser->hasGroupRight('NEWS_ENTRY_EDIT',$entry->getGroupId() && $entry->isVisible()) ) ){
            $this->rightsMissingView('NEWS_ENTRY_ADMIN');                       
        }
        
        /* is the delete confirmend? */
        if(!array_key_exists('deleteConfirmation', $_REQUEST)) {
            return $this->confirmationView(NAME_NEWS_DEL.': ' . $entry->getGroup()->getName(),
                                           DO_ACTION_NEWS_DEL,
                                           '/index.php?mod=index&dest=modul&method=delNewsEntry&newsId=' . $entry->id . '&deleteConfirmation=yes',
                                           rewrite_index(array()));          
        }
        
        $this->addLog('del a NewsEntry');
        
        $entry->delete();
        
        self::notifyIPC(new GlobalIPC, 'NEWS_CHANGED');
        
        header('Location: ' . rewrite_index(array('extern' => true))); 
     }
    
    protected function addThread(&$news, $addAllAtta = false, $targetForum){
        // current user logged in user
        $cUser = Session::getInstance()->getVisitor();
        
        $thread = new ThreadModel();
        $thread->setForumId($targetForum->id);
        $thread->setCaption($news->getCaption());
        $thread->setVisible($news->isVisible());
        $thread->setClosed(false);
        $thread->setSticky($news->isSticky());       
        
        $str = '[opener] ' . $news->getOpenerRaw() . ' [/opener]' . $news->getContentRaw();
        $entry = new ThreadEntryModel($str, $cUser, array(),  null, false, $news->getCaption());
        $entry->setGroupId($news->getGroupId());
        
        $DB = Database::getHandle();            
        $DB->StartTrans();
        
        $thread->save();
        
        $entry->setThreadId($thread->id);
        foreach ($news->getAttachmentsToAdd() as $atm){
            $entry->addAttachment($atm);
        }
        if ($addAllAtta){
        	foreach ($news->getAttachments() as $atm){
                $entry->addAttachment($atm);
            }
        }
        $entry->save();
        
        if (!$DB->CompleteTrans()){
           throw new DBException(DB_TRANSACTION_FAILED);
        }
        $news->setThreadId($thread->id);
    }
    
    protected function editThread(&$news, $targetForum){
    	/* no thread and news is preview */
        if($news->getThreadId() == null && !$news->isVisible()){
    		return;
    	}
        if($news->getThreadId() == null){
        	return $this->addThread($news, true, $targetForum);
        }
        
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();        
        
        $thread = $news->getThread();
        $thread->setCaption($news->getCaption());
        $thread->setVisible($news->isVisible());
        $thread->setClosed(false);
        $thread->setSticky($news->isSticky());       
        
        $str = '[opener] ' . $news->getOpenerRaw() . ' [/opener]' . $news->getContentRaw();
        $entry = $thread->getFirstEntry();
        $entry->setCaption($news->getCaption());
        $entry->setContentRaw($str);
        $entry->hiddenChanges = true;
        $entry->setGroupId($news->getGroupId());
        $entry->setAuthor($cUser);
        
        $DB = Database::getHandle();            
        $DB->StartTrans();
        
        $thread->save();
        
        /*foreach($news->getAttachmentsToAdd() as $atm){
            $entry->addAttachment($atm);
        }*/
        foreach($news->getAttachments() as $atm){
            $entry->addAttachment($atm);
        }
        $entry->save();
        
        if (!$DB->CompleteTrans()){
           throw new DBException(DB_TRANSACTION_FAILED);
        }
                
        // update related forum
        $cacheKey = 'forum|' . $thread->id . '|1';
        // TODO: find a better way than instanciating the template here
        // perhaps it would suffice to move the view creation to one function
        // that is also called on normal thread entry view
        $threadEntriesView = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),'modules/forum/thread_entries.tpl');
        $threadEntriesView->enableCaching();
        $threadEntriesView->clearCache($cacheKey);
        
        self::clearCacheIds($cacheKey);
        
        $threadEntriesState = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/forum/internal/part_thread_entry.tpl');
        $threadEntriesState->enableCaching();
        $threadEntriesState->clearCache('forum|thread|' . $entry->id);
        
        
    }
    
    protected function moveNewsEntry(){
        
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        

        /* we need a newId */    
        if(!array_key_exists('newsId',$_REQUEST)){
            $this->errorView(ERR_NO_NEWS);
        }
        
        /* get the right NewsModel */
        $entry = NewsEntryModel::getNewsById($_REQUEST['newsId']);
        
        /* 
         * is the user allowed to del a post 
         * - is admin
         * - news was not published
         */
        if(!$cUser->hasRight('NEWS_ENTRY_ADMIN') && !($cUser->hasGroupRight('NEWS_ENTRY_EDIT',$entryToEdit->getGroupId() && $entryToEdit->isVisible()) ) ){
            $this->rightsMissingView('NEWS_ENTRY_ADMIN');                       
        }

        //var_dump($entry);
        $entry->setEndDate(date('Y-m-d', time() - (3600*24)));

        $entry->save();
        
        header('Location: ' . rewrite_index(array('extern' => true))); 
     }
    
    protected static function getEventsView($weeks) {
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'modules/event/overview.tpl');
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        self::observeIPC(
            new GlobalIPC, 
            array('EVENT_CHANGED'),
            $main, 'event');
        
        $main->setCacheParameter(86400, 'event|' . $weeks);
        
        if (!$main->isCached()) {
            // get all events in the next weeks
            $events = EventEntryModel::getAllEvents('CURRENT_DATE', $weeks * 7);
            
            $main->assign('events',$events);
            $main->assign('weeksToShow',$weeks);
        }
        
        return $main;
    }
    
    protected function showEvents(){
        $cUser = Session::getInstance()->getVisitor();
        $weeks = (int) InputValidator::getRequestData('weeks',4);
        
        $main = self::getEventsView($weeks);
        
        if (array_key_exists('EventId', $_REQUEST)){
            $event = EventEntryModel::getEventById($_REQUEST['EventId']);
            $main->assign('eventToShow',$event);
        }
        
        $this->setCentralView($main, false);
        $this->view();
    }
    
    
    protected function addEvents(){
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        //TODO
        if (!array_key_exists(F_ENTRY_RANDID, $_REQUEST)) {
            $entryRandId = Session::getInstance()->generateNewRandomId();
        } else {
            $entryRandId = $_REQUEST[F_ENTRY_RANDID];
        }
        
        //is done here, because I need to check the lenght after parsing
        if (array_key_exists('caption', $_POST)){
            $nullParser = ParserFactory::getFormatcodeNullParser();    
            $_POST['caption'] = $nullParser->parse(trim($_POST['caption']));
        }
        
        $formFields = array(
            'caption'   => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 200), 'escape' => true),
            'descriptionText' => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false)
             );
        // some validity checks and HTML-escape
        $this->validateInput($formFields);         
        
        /*** generate the model ***/
        $contentRaw = $_POST['descriptionText'];
        $caption = $_POST['caption'];
        
       //$content_raw = null, $author = null, $parseSettings = array (), $caption = ''

		if(array_key_exists('EventId',$_REQUEST))
		{
			$entry = EventEntryModel::getEventById($_REQUEST['EventId']);
			$entry->setCaption($caption);
			$entry->setContentRaw($contentRaw);
            $entry->setParseSettings(self::getParseSettings());
			//Todo: Author ändern
		}
		else
		{
        	$entry = new EventEntryModel($contentRaw, $cUser, $caption, self::getParseSettings());
		}        
        
        $groupId = (int) InputValidator::getRequestData('groupId',0);
        if($groupId>0){
            $entry->setGroupId($groupId);
        } 
        
        if (!($groupId != 0 && $cUser->hasGroupRight('CALENDAR_EVENT_GLOBAL_ADD',$groupId) ) && 
            !$cUser->hasRight('CALENDAR_EVENT_ADMIN') &&
            !($groupId == 0 && $cUser->hasRight('CALENDAR_EVENT_GLOBAL_ADD_USER') ) )
        {
            $this->rightsMissingView('CALENDAR_EVENT_GLOBAL_ADD');
        }
        
        /* bulid the target time */
        $startDate = parent::getSmartyDate($_REQUEST, 'start', true);
        $endDate   = parent::getSmartyDate($_REQUEST, 'end', true);
		
		if(empty($startDate)){
            $this->errorView(ERR_NEWS_START_DATE, E_USER_ERROR);
        }
        if(empty($endDate)){
        	$this->errorView(ERR_NEWS_END_DATE, E_USER_ERROR);
        }

        if(!InputValidator::isValidFutureDate($endDate)){
            $this->errors['endDate'] = ERR_NEWS_END_DATE;
        }
        
        $entry->setStartDate($startDate);        
        $entry->setEndDate($endDate); 
        
        $weeks = InputValidator::getRequestData('weeks', 1);

        /* test wether preview is activated */
        if ($this->previewMode || $this->uploadMode || count($this->errors) > 0) {
            /* get the template for postprocessing */
            $main = self::getEventsView($weeks);
            
            //$main->assign('isPreview', $this->previewMode);
            $main->assign('randid', $entryRandId);
            $main->assign('eventToEdit', $entry);
            $main->assign('eventToShow', $entry);
            
            $this->setCentralView($main);
            $this->view();
            return;
        }
        
        
        /*if (!Session::getInstance()->removeRandomId($entryRandId)) {
            header('Location: /home');
            return;
        }*/
        
        $entry->postIp = ClientInfos::getClientIP();
        $entry->save(); 
        
        self::notifyIPC(new GlobalIPC, 'EVENT_CHANGED');       
        
        //$this->defaultView();
        header('Location: ' . rewrite_index(array('events' => true, 'weeks' => $weeks, 'extern' => true)));
    }
    
    protected function editEvents(){
    	
       if (array_key_exists('EditEventId', $_REQUEST)){
            
            $cUser = Session::getInstance()->getVisitor();
            $weeks = (int) InputValidator::getRequestData('weeks',1);
            
            $main = self::getEventsView($weeks);
            
            $entry = EventEntryModel::getEventById($_REQUEST['EditEventId']);
         
            if (!($entry->getGroupId() !== null && $cUser->hasGroupRight('CALENDAR_EVENT_GLOBAL_ADD',$entry->getGroupId()) ) && 
                !$cUser->hasRight('CALENDAR_EVENT_ADMIN') &&
                !($entry->getGroupId() == 0 && 
                $cUser->hasRight('CALENDAR_EVENT_GLOBAL_ADD_USER') && $cUser->equals($entry->getAuthor()) ) )
            {
                $this->rightsMissingView('CALENDAR_EVENT_GLOBAL_ADD');
            }
         
            $main->assign('eventToEdit',$entry);
            $this->setCentralView($main,false);
            $this->view();
            return;
        }
        
        $this->addEvents();
    }
    
    protected function delEvents(){
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        /* get the right NewsModel */
        $entry = EventEntryModel::getEventById(InputValidator::getRequestData('EventId', 0));
        if ($entry == null){
            $this->errorView(ERR_NO_EVENT);
        }
        
        if (!($entry->getGroupId() !== null && $cUser->hasGroupRight('CALENDAR_EVENT_GLOBAL_ADD',$entry->getGroupId()) ) && 
            !$cUser->hasRight('CALENDAR_EVENT_ADMIN') &&
            !($entry->getGroupId() == 0 && $cUser->hasRight('CALENDAR_EVENT_GLOBAL_ADD_USER') && $cUser->equals($entry->getAuthor()) ) )
        {
            $this->rightsMissingView('CALENDAR_EVENT_GLOBAL_ADD');
        }
        
        $weeks = (int) InputValidator::getRequestData('weeks',1);
        
         /* is the delete confirmend? */
        if(!array_key_exists('deleteConfirmation', $_REQUEST)) {
            return $this->confirmationView(NAME_EVENT_DEL_FROM.': ' . $entry->getGroup()->getName(),
                                           DO_ACTION_EVENT_DEL,
                                           '/index.php?mod=index&dest=modul&method=delEvents&EventId=' . $entry->id . '&deleteConfirmation=yes',
                                           rewrite_index(array('events' => true, 'weeks' => $weeks, 'extern' => true)));        
        }
        
        $entry->delete();
        
        self::notifyIPC(new GlobalIPC, 'EVENT_CHANGED'); 
        
        header('Location: ' . rewrite_index(array('events' => true, 'weeks' => $weeks, 'extern' => true)));
     }
    
    /**
     * handles attachment (add and delete) requests with respect to given entry
     * 
     * @param BaseEntryModel $entry entry to add attachments to
     * @param UserModel $recvUser user whom the attachments are assigned to (concerns especially upload path)
     * @param boolean if true, method will look for attachments to be removed (via POST/delattach)
     * @return boolean false, if file was too big; otherwise true, if upload was successful 
     * @throws CoreException if arguments of method are invalid or upload failed
     */
    protected function handleAttachment($entry, $recvUser, $maxAttachmentSize, $checkForAttachmentRemove, &$error) {
        
        if(!array_key_exists('file_attachment1',$_FILES))
            return true;
        
        // check validity of arguments
        if (!($entry instanceof BaseEntryModel)) {
            throw new CoreException( Logging::getErrorMessage(GENERAL_ARGUMENT_INVALID, 'entry'));
        }
        if (!($recvUser instanceof UserModel)) {
            throw new CoreException( Logging::getErrorMessage(GENERAL_ARGUMENT_INVALID, 'recvUser'));
        }
        
        if ($_FILES['file_attachment1']['size']) {
            // check accumulated attachment size limit
            // TODO: use separate limit for news entries
            if ($entry->getAccumulatedAttachmentSize() + $_FILES['file_attachment1']['size'] > $maxAttachmentSize) {
                return false;
            }
            
            // username of session object should not induce security risk
            // this username is contrainted by the database to digits and chars
            $atm = AttachmentHandler::handleNewsPicture( $_FILES['file_attachment1'],
                AttachmentHandler::getAdjointPath($recvUser), true, $maxAttachmentSize,
                array('maxwidth' => 350, 'maxheight' => 350) );
            // check, if uploaded file is too big
            if ($atm === AttachmentHandler::ERROR_MIME || $atm === AttachmentHandler::ERROR_SIZE) {
				$error = $atm;
                return false;
            }

            // add attachment to object
            $entry->addAttachment( $atm );
        }
        
        // read all attachment ids that are to be deleted
        // these are in POST: delattach<id>
        // note: attachments that are not yet in the database
        // have negative ids!
        foreach ($_POST as $key => $val) {
            if (preg_match('/delattach(-?\d+)/', $key, $matches)) {
                $entry->deleteAttachmentById($matches[1]);
            }
        }
        return true;
    }
    
    protected function chat() {
        $cUser = Session::getInstance()->getVisitor();
        if (!$cUser->isRegularLocalUser()) {
            header('Location: ' . rewrite_index(array('extern'=>1)));
            return;
        }

        $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'chat.tpl');
        $view->assign('user', $cUser);
        $view->display();
    }
    
    protected function faq(){
        $this->simpleView('help/faq.tpl');
    }
    
    protected function help(){
        // TODO: move into help dir
        $show =  InputValidator::getRequestData('show','help');
        if($show == 'unihelp'){
        	$this->simpleView('help/help_unihelp.tpl');
        	return;
        }
        elseif($show == 'community')
        {
        	$this->simpleView('help/help_community.tpl');
        	return;
        }
        $this->simpleView('help/help.tpl');
    }
    //TODO: find better name
    protected function helpNew() {
        $this->simpleView('help/help_new.tpl');
    }
    
    protected function helpFormatcode(){
        $this->simpleView('help/bbcode.tpl');
    }
    
    protected function imprint(){
        $this->simpleView('legal/imprint.tpl');
    }
    
    protected function privacy(){
        $this->simpleView('legal/privacy.tpl');
    }
    
    protected function terms_of_use(){
        $this->simpleView('legal/terms_of_use.tpl');
    }
    
    protected function smileys(){
        require_once MODEL_DIR . '/base/smiley_model.php';
        
        if (array_key_exists('nojs', $_REQUEST)) {
            $template = 'smileys_nojs.tpl';
            $cache = 'smileys';
        } else {
            $template = 'smileys.tpl';
            $cache = 'smileys';
        }
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, $template);
        $main->setCacheParameter(86400, $cache);
        
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        if (!$main->isCached()) {
            if (array_key_exists('nojs', $_REQUEST)) {
                $smileys = SmileyModel::getAllSmileys(false);
                $smileysNumber = count($smileys);
                
                // split smileys array into 5 equal sized subarrays
                $splitedSmileys = array();
                $count = 0;
                $subarray = array();
                foreach ($smileys as $s) {
                    array_push($subarray, $s);
                    ++$count;
                    if ($count > $smileysNumber / 5) {
                        array_push($splitedSmileys, $subarray);
                        $subarray = array();
                        $count = 0;
                    }
                }
                array_push($splitedSmileys, $subarray);
                $smileys = $splitedSmileys;
            } else {
                $smileys = SmileyModel::getAllSmileys(false);
            }
            
            
            $main->assign('smileys', $smileys);
            $main->assign('smiley_path', SMILEY_URL);
        }
        
        $main->display();
    }
    
    protected function toolbar() {
        //TODO: hilfe in den Hilfebreich verschieben?
        if(array_key_exists('help',$_REQUEST)){
        	$this->simpleView('toolbar_help.tpl');
            return;
        }
        $this->simpleView('toolbar.tpl');
    }
   
    protected function toolbarDownload() {
		$toolbarBinDir = BASE . '/contrib/toolbar';
	
        $uid = InputValidator::getRequestData('uid', 0);
		if ($uid == 0) {
			$name = $toolbarBinDir . '/unihelp2.xpi';
		} else {
			$userId = substr($uid, 0, -40);
			$userNameHash = substr($uid, -40);
			$user = UserProtectedModel::getUserById($userId);
			if ($user == null || sha1($user->getUsername()) != $userNameHash) {
				exit;
			}
			$cmd = $toolbarBinDir . '/create_toolbar_xpi2.sh ' . $userId . ' ' . escapeshellarg($user->getUsername()) .
				' ' . $toolbarBinDir;
				//var_dump($cmd);
			exec($cmd);
			
			$name = '/tmp/toolbar/' . (int)$userId . '/unihelp2.xpi';
		}
        $fp = fopen($name, 'rb');
        
        // send the right headers for mozilla extension
        header("Content-Type: application/x-xpinstall"); 
        header("Content-Length: " . filesize($name));
        
        // dump the XPI and stop the script
        fpassthru($fp);
        
		if ($uid != 0) {
			// clean up temporary files
			exec($toolbarBinDir . '/cleanup_toolbar.sh ' . (int)$cUser->id);
		}
        return;
    }
    
     protected function NewsFeed() {
        $feedMetadata = array(
            'title' => NAME_NEWS_RSS,
            'description' => NAME_NEWS_RSS,
            'url' => rewrite_index(array("extern" => true))
        );
        $main = $this->showFeed(null, $feedMetadata, false);
        
        if (defined('CACHETEST')) {
            $main->enableCaching();
            $main->setCacheParameter(7200, 'news|latest');
        }
        
        self::observeIPC(
            new GlobalIPC, 
            array('NEWS_CHANGED'),
            $main, 'news|latest');
        
        if (!$main->isCached()) {
            /* filter for the public news */
            $newsFilter = new NewsFilter();
            $newsFilter->addFilter(NewsFilter::FILTER_START_DATE, '<=', NewsFilter::CURRENT_DATE, '');
            $newsFilter->addFilter(NewsFilter::FILTER_END_DATE, '>=', NewsFilter::CURRENT_DATE);
            $newsFilter->addFilter(NewsFilter::FILTER_VISIBLE, '=', true);
            
            $entries = NewsEntryModel::getEntriesByFilter($newsFilter);
            $entries = BaseModel::applyProxy($entries, new NewsEntryFeedProxy);

            $main->assign('entries', $entries);
        }
        
        $main->display(true);
    }
    
    private static function prepareICalText($preparedText) {
        // TEXT according to RFC 2445
        $preparedText = str_replace("\\",'\\',$preparedText);
        $preparedText = str_replace(',','\,',$preparedText);
        $preparedText = str_replace(';','\;',$preparedText);
        $preparedText = str_replace("\r","",$preparedText);
        $preparedText = str_replace("\n",'\n',$preparedText);
        return $preparedText;
    }

    protected function eventsCal() {
        $events = EventEntryModel::getAllEvents('CURRENT_DATE', 28, 'asc', false);
        $entries = BaseModel::applyProxy($events, new EventEntryCalProxy);

        header("Content-Type: text/calendar");

        // iCal according to RFC 2445
        print "BEGIN:VCALENDAR\r\n";
        print "VERSION:2.0\r\n";
        print "PRODID:-//UniHelp calendar//NONSGML v1.0//EN\r\n";
        foreach ($entries as $e) {
            print "BEGIN:VEVENT\r\n";
            print "UID:"  . $e->getCalUid() . "\r\n";
            print "DTSTART;TZID=" . date('e:Ymd\THis', $e->getCalStartTime()) . "\r\n";
            print "DTEND;TZID=" . date('e:Ymd\THis', $e->getCalEndTime()) . "\r\n";
            print "SUMMARY:" . self::prepareICalText($e->getCalSummary()) . "\r\n";
            print "CLASS:" . ($e->getCalPublic() ? "PUBLIC" : "PRIVATE") . "\r\n";
            print "DESCRIPTION:" . self::prepareICalText($e->getCalDescription()) . "\r\n";
            print "END:VEVENT\r\n";
        }
        print "END:VCALENDAR\r\n";
    }
    
    protected function EventsFeed() {
        $feedMetadata = array(
            'title' => NAME_EVENTS,
            'description' => NAME_EVENTS_FEED,
            'url' => rewrite_index(array("extern" => true, "events" => true))
        );
        $main = $this->showFeed(null, $feedMetadata, false);
        
         if (defined('CACHETEST')) {
            $main->enableCaching();
            $main->setCacheParameter(7200, 'news|latest');
        }
        
        self::observeIPC(
            new GlobalIPC, 
            array('EVENT_CHANGED'),
            $main, 'event');
        
        $main->setCacheParameter(86400, 'event_feed');
       
  
        
        if (!$main->isCached()) {
            $events = EventEntryModel::getAllEvents('CURRENT_DATE', 28, 'asc', false);
            $entries = BaseModel::applyProxy($events, new EventEntryFeedProxy);
            $main->assign('entries', $entries);
        }
        
        $main->display(true);
    }
    
    protected function ajaxToolbar() {
        $toolbarVersion = TOOLBAR_VERSION;
        
        if (!array_key_exists('uid', $_REQUEST) or !array_key_exists('username', $_REQUEST)) {
            // check for old toolbars
            if (array_key_exists('userid', $_REQUEST) and array_key_exists('username', $_REQUEST)) {
                // give old JSON output with upgrade notice
                print ' { currentVersion : "' . $toolbarVersion . '", username : "' . $_REQUEST['username'] . '" }';
            }
            exit;
        }
        
        if (($cUser = Session::getInstance()->getVisitor()) and $cUser->isRegularLocalUser() and $cUser->id != $_REQUEST['uid']) {
            Logging::getInstance()->logSecurity('wrong toolbar parameter');
            exit;
        }
        
    	if ($_REQUEST['uid'] == '__USERID__') {
        	exit;
        }
        $user = UserProtectedModel::getUserById($_REQUEST['uid']);
        if ($user == null) {
        	exit;
        }
        if ($user->getUsername() != $_REQUEST['username']) {
            Logging::getInstance()->logSecurity('wrong toolbar parameter');
            exit;
        }
        
        //$main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'toolbar_json.tpl');
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'toolbar_xml.tpl');
        $main->setCacheParameter(900, 'toolbar|' . $user->getCachekey());
        
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        if (!$main->isCached()) {
            require_once MODEL_DIR . '/base/friend_model.php';
            require_once MODEL_DIR . '/course/course_model.php';
            
            $friends = FriendModel::getFriendsByUser($user);
            $friendsString = array();
            foreach ($friends as $f) {
                array_push($friendsString, $f->getUsername());
            }
            
            $courses = CourseModel::getCoursesByUser($user);
            $coursesString = array();
            foreach ($courses as $c) {
                array_push($coursesString, array($c->id, $c->getName()));
            }
            
            $main->assign('newForumPostings', ForumRead::getInstance($user)->countThreads());
            $main->assign('courses', $coursesString);
            $main->assign('friends', $friendsString);
        }
        
        $main->assign('points', $user->getPoints());
        $main->assign('newEntries', $user->getGBEntriesUnread());
        $main->assign('newPMs', $user->getPMsUnread());
        $main->assign('username', $user->getUsername());
        $main->assign('currentVersion', $toolbarVersion);
        
        
        $main->display(true);
    }
    
    protected function ajaxNewsPreview(){
    	$cUser = Session::getInstance()->getVisitor();
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/news/news_preview.tpl');
        
        $formFields = array(
            'caption'   => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 200), 'escape' => true),
            'entryText' => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false),
            'openerText'=> array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false)
             );
        // some validity checks and HTML-escape
        $this->validateInput($formFields);         
        
        if(count($this->errors) > 0 && false){
        	echo "Fehler";
            return;
        }
        
        /*** generate the model ***/
        $openerText = $_POST['openerText'];
        $entryText = $_POST['entryText'];
        if (array_key_exists('caption', $_POST)){
            $nullParser = ParserFactory::getFormatcodeNullParser();    
            $caption = $nullParser->parse(trim($_POST['caption']));
        }
        else{
            $caption = '';
        }    
        
        if(array_key_exists('groupId',$_REQUEST)){
            $groupId = $_REQUEST['groupId'];
        }else{
            echo "keine Organisation";
            return;                
        } 
        
        $parser = array();
        $parser[BaseEntryModel::PARSE_AS_FORMATCODE] = ($_REQUEST[F_ENABLE_FORMATCODE] == 'true');
        $parser[BaseEntryModel::PARSE_AS_SMILEYS] = ($_REQUEST[F_ENABLE_SMILEYS] == 'true');        

        if(array_key_exists('newsId',$_REQUEST) && $_REQUEST['newsId'] != 'false'){
        	 /* get the right NewsModel */
            $entry = Session::getInstance()->getEntryDataChecked('news', $_REQUEST['newsId']);
            if ($entry == false){    
                $entry = NewsEntryModel::getNewsById($_REQUEST['newsId']);
            } 
            $entry->update($openerText, $entryText, $parser, $entry->getGroupId(), $caption);
        }else{       
            //$content_raw = null, $author = null, $parseSettings = array (), $caption = ''
            $entry = Session::getInstance()->getEntryDataChecked('news', null);
            if ($entry == false){
                /* build new entry model */
                $entry = new NewsEntryModel($openerText, $entryText, $cUser, $parser, $groupId, $caption);        
            } else {
                $entry->update($openerText, $entryText, $parser, $groupId, $caption);
            }
        }
        
        /* bulid the target time */
        $startDate = parent::getSmartyDate($_REQUEST, 'start');
        $endDate   = parent::getSmartyDate($_REQUEST, 'end');

        if(empty($startDate) or empty($endDate)){
        	echo "kein datum";
            return;
        }

        /*if(!InputValidator::isValidFutureDate($endDate)){
            $this->errors['endDate'] = ERR_NEWS_END_DATE;
        }*/
        
        $entry->setStartDate($startDate);        
        $entry->setEndDate($endDate);
        
        $main->assign('entryToEdit', $entry);
        $main->display();
        
    }
    
    protected function easterEgg(){
    	$this->simpleView('easter_egg.tpl');
    }
    
    protected function showVideo(){
        $this->simpleView('show_video.tpl');
    }
    
    protected function flatView(){
        $this->simpleView('modules/flatmarket/flat_view.tpl');
    }
    
    protected function jobsOnUniHelp(){
        $this->simpleView('help/jobs.tpl');
    }
}
?>
