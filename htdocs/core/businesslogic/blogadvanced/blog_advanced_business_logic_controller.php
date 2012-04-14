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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/businesslogic/blogadvanced/blog_advanced_business_logic_controller.php $

require_once MODEL_DIR . '/base/user_model.php';
require_once MODEL_DIR . '/base/base_filter.php';
require_once MODEL_DIR . '/blog/user_blog_advanced_model.php';
require_once MODEL_DIR . '/blog/group_blog_advanced_model.php';
require_once MODEL_DIR . '/blog/blog_advanced_comment_feed_proxy.php';
require_once MODEL_DIR . '/blog/blog_advanced_entry_feed_proxy.php';

require_once CORE_DIR . '/businesslogic/blog_based_business_logic_controller.php';

require_once CORE_DIR . '/utils/client_infos.php';
require_once CORE_DIR . '/utils/attachment_handler.php';
require_once CORE_DIR . '/utils/input_validator.php';
require_once CORE_DIR . '/utils/global_ipc.php';
require_once CORE_DIR . '/utils/captcha_computation.php';
require_once CORE_DIR . '/utils/notifier_factory.php';

// load some useful constants for working on <form>-input
require_once CORE_DIR . '/constants/form_constants.php';
require_once CORE_DIR . '/constants/value_constants.php';

define('BLOG_ADVANCED_TEMPLATE_DIR', 'modules/blogadvanced/');

/**
 * @package controller
 * @author linap
 * @version $Id: blog_advanced_business_logic_controller.php 5807 2008-04-12 21:23:22Z trehn $
 */
class BlogAdvancedBusinessLogicController extends BlogBasedLogicController {
    protected $blogModel;
    
    public function __construct($ajaxView = false) {
        parent::__construct($ajaxView);
    }
        
    /**
     * set methods that are allowed for calling by the controller
     */
    protected function getAllowedMethods() {
        // allow viewing and some data manipulation
        return array_merge(parent::getAllowedMethods(),
            array (
                'showBlogStart',
                'showBlogStartFeed',
                'showBlogStartCommentFeed',
                'showUserBlog',
                'showUserBlogFeed',
                'showUserBlogEntry',
                'showUserBlogCommentFeed',
                
                'editUserBlogEntry',
                'editUserBlogCategories',
                'editUserBlogMisc',
                'editUserBlogVisibility',
                'addUserBlogTrackback',
                'exportUserBlog',
                
                'ajaxPreviewEntry',
                
                'createUserBlog',
                'showTermsOfUse',
            )
        );
    }
    
    protected function getDefaultMethod() {
        return 'showBlogStart';
    }
    
    protected function collectParameters($method) {
        $this->preProcess(true, false);
        
        $parameters = array();
        if('showUserBlogEntry' == $method){
            // fetch only blog entries that belong to blog's owner
            $parameters['entry'] = $this->blogModel->getEntryById(InputValidator::getRequestData('entry_id', 0), true);
        }
        
        $this->_parameters[$method] = $parameters;

        parent::collectParameters($method);
    }
    
    protected static function getDefaultMethodObject() {
        return new BLCMethod(NAME_BLOGOSPHERE, rewrite_blog(array()), 
            BLCMethod::getDefaultMethod(), rewrite_blog(array('feed' => 'rss2')), BLOGOSPHERE_TITLE);
    }
    
    public function getMethodObject($method) {
        $this->preProcess(true, false);
        
        if ('showBlogStart' == $method) {
            return self::getDefaultMethodObject();
        } else if ('showUserBlog' == $method or substr($method, 0, 8) == 'editUser') {
            return new BLCMethod($this->blogModel->getTitle(), rewrite_blog(array('owner' => $this->blogModel->getOwner())), 
                self::getDefaultMethodObject(), 
                rewrite_blog(array('owner' => $this->blogModel->getOwner(), 'feed' => 'rss2')), $this->blogModel->getTitle());
        } else if ('showUserBlogEntry' == $method) {
            $entry = $this->getParameter('showUserBlogEntry', 'entry');
            $title = $this->blogModel->getTitle() . ': ' . $entry->getTitle();
            return new BLCMethod($title, rewrite_blog(array('owner' => $this->blogModel->getOwner(), 'entry' => $entry)), 
                $this->getMethodObject('showUserBlog'));
        }
        
        return parent::getMethodObject($method);
    }
        
    protected function preProcess($hideInvisible = true, $showPageOnError = true) {
    	if ($this->blogModel != null) {
    	    return;
    	}
    	
        if (array_key_exists('bloguser',$_REQUEST)) {
            $username = $_REQUEST['bloguser'];
            // TODO: migrate/use old usernames in order to keep permalinks
            $blogUser = UserProtectedModel::getUserByUsername($username);
            $this->blogModel = UserBlogAdvancedModel::getBlog($blogUser, $hideInvisible);
            
        } else if (array_key_exists('blogorgas',$_REQUEST)) {
            // organisation string is $id_$groupName
            $groupId = strtok($_REQUEST['blogorgas'], '_');
            $blogGroup = GroupModel::getGroupById((int) $groupId);
            $this->blogModel = GroupBlogAdvancedModel::getBlog($blogGroup, $hideInvisible);
        }

        if ($showPageOnError and $this->blogModel == null) {
            $this->errorView(ERR_BLOG_DOESNT_EXIST);
            exit;
        }
    }
    
    protected function showBlogStart() {
    	$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            BLOG_ADVANCED_TEMPLATE_DIR . 'blog_start.tpl');
        // CACHEME: how could be cache this page??
        
        $cUser = Session::getInstance()->getVisitor();
        // check, if visitor has right to create own blog
        $blogCreate = $cUser->hasRight('BLOG_ADVANCED_CREATE') && 
            UserBlogAdvancedModel::getBlog($cUser) == null;

        $entries = BlogAdvancedModel::getAllParsedEntriesFromAllUsers();
        $main->assign('blog_entries', $entries);
        $main->assign('blog_create', $blogCreate);

        $this->setCentralView($main, null);
        $this->getSmartyView()->assign('show_sidebar_alternative', true);
        $this->view();
    }
    
    protected function showBlogStartFeed() {
        $_entries = BlogAdvancedModel::getAllParsedEntriesFromAllUsers();
        $entries = array();
        // flatten _entries array(timestamp => array(entries of this day))
        foreach ($_entries as $day) {
            foreach ($day as $e) {
                $entries[] = $e;
            }
        }
        $entries = BaseModel::applyProxy($entries, new BlogAdvancedEntryFeedProxy);
        $feedMetadata = array(
            'title' => BLOGOSPHERE_TITLE,
            'description' => BLOGOSPHERE_DESCRIPTION,
            'url' => rewrite_blog(array("extern" => true)),
        );
        
        $this->showFeed($entries, $feedMetadata);
    }
    
    protected function showBlogStartCommentFeed() {
        $comments = BaseModel::applyProxy(BlogAdvancedCommentModel::getLatestComments(null), new BlogAdvancedCommentFeedProxy);
        $feedMetadata = array(
            'title' => BLOGOSPHERE_TITLE . ' (' . NAME_COMMENTS . ')',
            'description' => NAME_COMMENTS . ': ' . BLOGOSPHERE_DESCRIPTION,
            'url' => rewrite_blog(array("extern" => true)),
        );
        
        $this->showFeed($comments, $feedMetadata);
    }
    
    protected function showUserBlog() {
        // initialize blog model etc.
        $this->preProcess();

        $blogPage = (array_key_exists('page', $_GET)) ? $_GET['page'] : V_BLOG_ADVANCED_START_PAGE;
        if ($blogPage < V_BLOG_ADVANCED_START_PAGE) {
            $blogPage = V_BLOG_ADVANCED_START_PAGE;
        }

        // category filtering
        $categoryId = (array_key_exists('cat_id',$_GET)) ? $_GET['cat_id'] : null;

        // date filtering
        $date = array();
        // we need at least year and month
        // TODO: do we need ctype-checks?
        if (array_key_exists('year', $_GET) and array_key_exists('month', $_GET) and
            ctype_digit($_GET['year']) and ctype_digit($_GET['month'])) {
            array_push($date, $_GET['year']);
            array_push($date, $_GET['month']);
            if (array_key_exists('day', $_GET) and ctype_digit($_GET['day'])) {
                array_push($date, $_GET['day']);
            }
        }
        $categoryId = (array_key_exists('cat_id',$_GET)) ? $_GET['cat_id'] : null;

        $this->blogView(true, $categoryId, $date, $blogPage);
    }
    
    protected function showUserBlogFeed() {
        // initialize blog model etc.
        $this->preProcess();
        
        $categoryId = null;
        // fetch entries and do not group them per date
        $entries = BaseModel::applyProxy($this->blogModel->getAllParsedEntries($categoryId, V_BLOG_ADVANCED_ENTRIES_PER_FEED, 0, false),
            new BlogAdvancedEntryFeedProxy);
        $feedMetadata = array(
            'title' => $this->blogModel->getTitle(),
            'description' => $this->blogModel->getSubtitle(),
            'url' => rewrite_blog(array("extern" => true)),
        );
        
        $this->showFeed($entries, $feedMetadata);
    }
    
    protected function showUserBlogEntry() {
        // initialize blog model etc.
        $this->preProcess();
        
        $entry = $this->getParameter('showUserBlogEntry', 'entry');
    
        // generate random string for comment posting
        if (!Session::getInstance()->getUserData('random_blog_advanced')) {
            Session::getInstance()->storeUserData('random_blog_advanced', md5(uniqid(rand())));
        }
        
        // if we have a valid entry, we can operate on it;
        // otherwise show standard blog page 
        if ($entry != null) {
            if (InputValidator::getRequestData('change_notification', false)) {
                $this->editUserCommentNotification($entry);
            }

            
            // if a comment was supplied handle it
            if (array_key_exists('comment_submit', $_POST)) {
                $this->editUserBlogComment($entry);
                return;
            }

            if (array_key_exists('deletecomment', $_REQUEST) and
                    ($comment = BlogAdvancedCommentModel::getCommentById($entry, 
                                $_REQUEST['deletecomment']))) {
                $comment->delete();
                // show normal blog entry view after delete;
                // due to lazy loading we need not to update
                // the comments in $entry-object
            }
            if (array_key_exists('deletetrackback', $_REQUEST) and
                    ($trackback = BlogAdvancedTrackbackModel::getTrackbackById($entry, 
                                $_REQUEST['deletetrackback']))) {
                $trackback->delete();
                // show normal blog entry view after delete;
                // due to lazy loading we need not to update
                // the trackbacks in $entry-object
            }
            
            $this->blogEntryView($entry);
        } else {
        	$this->blogView();
        }
    }
    
    protected function showUserBlogCommentFeed() {
        // initialize blog model etc.
        $this->preProcess();
        
        $comments = BaseModel::applyProxy(BlogAdvancedCommentModel::getLatestComments($this->blogModel), new BlogAdvancedCommentFeedProxy);
        $feedMetadata = array(
            'title' => $this->blogModel->getTitle() . ' (' . NAME_COMMENTS . ')',
            'description' => NAME_COMMENTS . ': ' . $this->blogModel->getSubtitle(),
            'url' => rewrite_blog(array("extern" => true)),
        );
        
        $this->showFeed($comments, $feedMetadata);
    }
    
    protected function editUserCommentNotification($entry) {
        // initialize blog model etc.
        $this->preProcess();
        
        $cUser = Session::getInstance()->getVisitor();
        
        // check, if entry allows comments
        if (!$entry->isAllowComments()) {
            $this->errorView('Zu diesem Blog-Eintrag können zur Zeit keine Kommentare verfasst werden.');
            exit;
        }
        
        // form fields and their requirements
        $formFields = array(
            'entry_notify_comments'      => array('required' => true, 'check' => 'isValidNotification'),
                           );
        // some validity checks and HTML-escape
        $this->validateInput($formFields);
        
        if (count($this->errors) == 0) {
            $DB = Database::getHandle();
            $DB->StartTrans();
            
            $entry->removeSubscriptor($cUser);
            $type = InputValidator::getRequestData('entry_notify_comments', 'none');
            if ($type != 'none') {
                $entry->addSubscriptor($cUser, $type);
            }
            
            $DB->CompleteTrans();
        }
    }
    
    protected function editUserBlogEntry() {
        // initialize blog model etc.
        $this->preProcess();
        
        // rights check
        $cUser = Session::getInstance()->getVisitor();
        if (!($cUser->hasRight('BLOG_ADVANCED_OWN_ADMIN') and $this->blogModel->isAdministrativeAuthority($cUser))) {
        	$this->rightsMissingView('BLOG_ADVANCED_OWN_ADMIN');
            exit;
        }
        
        // the categories are cached, so we can safely call the function
        // here without performance loss
        $categories = $this->blogModel->getCategories();
        
        $entry = null;
        
        if (!Session::getInstance()->getUserData('random_blog_advanced')) {
        	Session::getInstance()->storeUserData('random_blog_advanced', md5(uniqid(rand())));
        }
        
        if (array_key_exists('entry_id', $_REQUEST)) {
            // look if suitable object is found in session
            $entry = Session::getInstance()->getEntryDataChecked('blogAdvanced', $_REQUEST['entry_id']);
            // if not, load entry
            if ($entry === false) {
                // fetch only blog entries that belong to blog's owner
                $entry = $this->blogModel->getEntryById($_GET['entry_id'], true);
            }
        } else {
        	// if entry is new, a version could be in session though
            $entry = Session::getInstance()->getEntryDataChecked('blogAdvanced', 0);
        }
        
        if (array_key_exists('save', $_POST) or
            $this->previewMode or $this->uploadMode) {
            
            // form fields and their requirements
            $formFields = array(
                        // do NOT escape entrytext
                'entry_text'           => array('required' => true,  'check' => 'isValidAlmostAlways', 'escape' => false),
                'entry_title'          => array('required' => true,  'check' => 'isValidByLength', 'params' => array('lengthLo' => 2, 'lengthHi' => 250)),
                'entry_trackbacks'     => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'entry_allow_comments' => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'entry_notify_comments'=> array('required' => true, 'check' => 'isValidNotification'),
                               );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);            
            //var_dump($_POST['entry_text']);
            if (!$entry) {
                $entry = new BlogAdvancedEntry($_POST['entry_text'], $cUser, self::getParseSettings());
            } else {
            	$entry->setContentRaw($_POST['entry_text']);
                $entry->setParseSettings(self::getParseSettings());
            }
            
            if ($this->blogModel->getOwner() instanceof GroupModel) {
            	$entry->setGroup($this->blogModel->getOwner()); 
            }
            
            // common properties
            $entry->setTitle($_POST['entry_title']);
            $entry->setAllowComments(array_key_exists('entry_allow_comments', $_POST));
            $entryCategories = array();
            if (!empty($_POST['entry_category'])) {
                foreach ($_POST['entry_category'] as $catId) {
                    if (array_key_exists($catId, $categories)) {
                        $entryCategories[$catId] = $categories[$catId];
                    }
                }
            }
            $entry->setCategories($entryCategories);
            
            /*** handle attachment(s) additions and removements ***/
        
            if (!$this->handleAttachment($entry, $cUser, GlobalSettings::getGlobalSetting('ENTRY_BLOG_MAX_ATTACHMENT_SIZE') * 1024)) {
                $this->errors['attachemnt'] = ERR_ATTACHMENT;
            }
        
            // save entry only, if given random string is valid
            if (   array_key_exists('save', $_POST) and
                   count($this->errors) == 0 and
                   array_key_exists('randomstring', $_POST) and
                   Session::getInstance()->getUserData('random_blog_advanced') == $_POST['randomstring']) {
                
                self::notifyIPC(new GlobalIPC, 'BLOG_CHANGED');
                
                $DB = Database::getHandle();
                $DB->StartTrans();
                
                $entry->save();
                
                $notification = InputValidator::getRequestData('entry_notify_comments', 'none');
                // remove prior subscriptions
                $entry->removeSubscriptor($cUser);
                if ($notification != 'none') {
                    $entry->addSubscriptor($cUser, $notification);
                }
                
                $DB->CompleteTrans();
                // send trackbacks, if specified
                if (!empty($_REQUEST['entry_trackbacks'])) {
                    $urls = explode(' ', $_REQUEST['entry_trackbacks']);
                    // TODO: use errors-array, mark failed trackbacks
                    foreach ($urls as $url) {
                        $this->sendRemoteTrackback($url, $entry);
                    }
                }
                Session::getInstance()->deleteEntryData('blogAdvanced');
                Session::getInstance()->deleteUserData('random_blog_advanced');
                $this->blogView();
                return;
            }
        }
        // delete entry, if neccessary 
        if (array_key_exists('entry_delete', $_POST)) {
            $entry->delete();
            Session::getInstance()->deleteEntryData('blogAdvanced');
            Session::getInstance()->deleteUserData('random_blog_advanced');
            $this->blogView();
            return;
        } 
        
        // from now we are still in edit/preview mode
        if ($entry) {
    	   Session::getInstance()->storeEntryData('blogAdvanced', $entry);	
    	}
    	$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            BLOG_ADVANCED_TEMPLATE_DIR . 'edit_entry.tpl');
    
        $main->assign('blog_categories', $this->blogModel->getCategories());
        $main->assign('blog_owner', $this->blogModel->getOwner());
        $main->assign('blog_editentry', $entry);
        $main->assign('blog_errors', $this->errors);
        // handle trackback uris as extra variable because they are not stored in an entry object
        if (!empty($_POST['entry_trackbacks'])) {
        	$main->assign('blog_entrytrackbacks', $_POST['entry_trackbacks']);
        }
        $main->assign('blog_randomstring', Session::getInstance()->getUserData('random_blog_advanced'));
        $main->assign('isPreview', $this->previewMode);
        $main->assign('admin_mode', 'post');
        
        $this->setCentralView($main, $this->blogModel);
        
        $this->view();
    }
    
    protected function editUserBlogCategories() {
        // initialize blog model etc.
        $this->preProcess();
        
        $cUser = Session::getInstance()->getVisitor();
        // rights check
        if (!($cUser->hasRight('BLOG_ADVANCED_OWN_ADMIN') and $this->blogModel->isAdministrativeAuthority($cUser))) {
            $this->errorView('Dir fehlen die Rechte diese Aktion auszuführen.');
            exit;
        }
        
        $categories = $this->blogModel->getCategories();
        $editCategory = null;
                
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            BLOG_ADVANCED_TEMPLATE_DIR . 'edit_categories.tpl');
        
        // an existing category id indicates a category to edit
        if (!empty($_REQUEST['cat_id'])) {
            if (!array_key_exists($_REQUEST['cat_id'], $categories)) {
                Logging::getInstance()->logSecurity('wrong category id for blog');
            }
            
            $editCategory = $categories[$_REQUEST['cat_id']];            
        }
        
        // check, if we have to save the category
        if (array_key_exists('cat_submit', $_POST)) {
            // form fields and their requirements
            $formFields = array(
                'cat_name'       => array('required' => true,  'check' => 'isValidByLength', 'params' => array('lengthLo' => 2, 'lengthHi' => 80)),
                                );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);
            
            if (!$editCategory) {
               $editCategory = new BlogAdvancedCategoryModel(null, $this->blogModel->getOwner());
            }
            $editCategory->name = $_POST['cat_name'];
                        
            if (count($this->errors) == 0) {
                try {
                    $editCategory->save();
                    $editCategory = null;
                    // re-fetch categories
                    $categories = $this->blogModel->getCategories();
                } catch (DBException $e) {
                	// check for violation of unique constraint
                    if (strpos($e->getMessage(), BlogAdvancedCategoryModel::DUPLICATE_CATEGORY) !== false) {
                		$this->errors['duplicateCategory'] = 'Eine Kategorie mit diesem Namen hast Du bereits angelegt.';
                	} else {
                        // re-throw exception
                        throw $e;                		
                	}
                }
            }
        }
        
        $main->assign('blog_editcategory', $editCategory);
        $main->assign('blog_categories', $categories);
        $main->assign('blog_errors', $this->errors);
        $main->assign('blog_owner', $this->blogModel->getOwner());
        $main->assign('admin_mode', 'category');
        
        $this->setCentralView($main, $this->blogModel);
        
        $this->view();
    }
    
    protected function editUserBlogMisc() {
        // initialize blog model etc.
        $this->preProcess();
        
        $cUser = Session::getInstance()->getVisitor();
        // rights check
        if (!($cUser->hasRight('BLOG_ADVANCED_OWN_ADMIN') and $this->blogModel->isAdministrativeAuthority($cUser))) {
            $this->errorView('Dir fehlen die Rechte diese Aktion auszuführen.');
            exit;
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            BLOG_ADVANCED_TEMPLATE_DIR . 'edit_misc.tpl');
        
        // check, if we have to save the category
        if (array_key_exists('misc_submit', $_POST)) {
            // form fields and their requirements
            $formFields = array(
                'blog_title'       => array('required' => false,  'check' => 'isValidByLength', 'params' => array('lengthLo' => 2, 'lengthHi' => 100)),
                'blog_subtitle'    => array('required' => false,  'check' => 'isValidByLength', 'params' => array('lengthHi' => 150)),
                               );
            $this->validateInput($formFields);
            
            $this->blogModel->setTitle($_POST['blog_title']);
            $this->blogModel->setSubtitle($_POST['blog_subtitle']);
            
            if (count($this->errors) == 0) {
                $this->blogModel->save();
            }
        }
        
        $main->assign('blog_errors', $this->errors);
        $main->assign('blog_model', $this->blogModel);
        $main->assign('blog_owner', $this->blogModel->getOwner());
        $main->assign('admin_mode', 'misc');
        
        $this->setCentralView($main, $this->blogModel);
        
        $this->view();
    }
    
    protected function editUserBlogComment($entry) {
        // initialize blog model etc.
        $this->preProcess();
        
        $cUser = Session::getInstance()->getVisitor();
        
        // check, if entry allows comments
        if (!$entry->isAllowComments()) {
        	$this->errorView('Zu diesem Blog-Eintrag können zur Zeit keine Kommentare verfasst werden.');
            exit;
        }
        
        $comment = new BlogAdvancedCommentModel;
        $comment->authorUnihelp = ($cUser->isAnonymous()) ? null : $cUser;
        
        // form fields and their requirements
        $formFields = array(
            'comment_name'       => array('required' => true,  'check' => 'isValidByLength', 'params' => array('lengthLo' => 2, 'lengthHi' => 50)),
            'comment_email'      => array('required' => false, 'check' => 'isValidMail'),
            'comment_comment'    => array('required' => true,  'check' => 'isValidByLength', 'params' => array('lengthLo' => 2), 'escape' => false /* do not escape, as we do parse before output */),
            'comment_captcha'    => array('required' => true,  'check' => 'isValidCaptcha', 'params' => array('captcha' => Session::getInstance()->getViewData('captcha', null))),
                           );
        // if user is internal, we don't need extra information
        if ($comment->authorUnihelp) {
            unset($formFields['comment_captcha']);
            unset($formFields['comment_email']);
        }
        // some validity checks and HTML-escape
        $this->validateInput($formFields);
        
        if ($comment->authorUnihelp) {
        	$comment->authorName = htmlspecialchars($comment->authorUnihelp->getUsername());
        } else {
        	$comment->authorName = $_POST['comment_name'];
        }
        $comment->blogEntry = $entry;
        $comment->comment = $_POST['comment_comment'];
        $comment->email = $_POST['comment_email'];
                
        // if no error occured, we can save the entry
        // otherwise user has to check his data
        if (count($this->errors) == 0 and
            array_key_exists('randomstring', $_POST) and
            Session::getInstance()->getUserData('random_blog_advanced') == $_POST['randomstring']) {
            
            // delete previous random string
            Session::getInstance()->deleteUserData('random_blog_advanced');
            $comment->save();
            
            $subscriptors = $entry->getSubscriptors();
            foreach ($subscriptors as $k => $sub) {
                $user = $sub['user'];
                $type = $sub['type'];

                if (!$comment->authorUnihelp or !$comment->authorUnihelp->equals($user)) {
                    $text = ViewFactory::getSmartyView(USER_TEMPLATE_DIR, 'mail/blog_comment_notification.tpl');
                    $text->assign('user', $user);
                    $text->assign('blog', $this->blogModel);
                    $text->assign('url', rewrite_blog(array('extern' => true,
                                                            'owner' => $this->blogModel->getOwner(),
                                                            'comment' => $comment)));
                    $success = NotifierFactory::createNotifierByName($type)
                                    ->notify($user, CAPTION_BLOG_NEW_COMMENT, $text->fetch());
                }
            }
            
            $this->blogEntryView($entry);
        } else {
            $view = $this->blogEntryView($entry, false);
            $view->assign('comment_edit', $comment);
            $view->assign('comment_errors', $this->errors);
            
            $this->setCentralView($view, $this->blogModel);
            $this->view();
        }
    }
    
    protected function editUserBlogVisibility() {
        // initialize blog model etc.
        $this->preProcess(false);
        
        // rights check
        $cUser = Session::getInstance()->getVisitor();
        if (!$cUser->hasRight('BLOG_ADVANCED_ADMIN')) {
            $this->errorView('Dir fehlen die Rechte diese Aktion auszuführen.');
            exit;
        }
        
        if (array_key_exists('blog_submit', $_REQUEST)) {
        	$this->blogModel->setInvisible(!array_key_exists('blog_visible', $_REQUEST));
            $this->blogModel->save();
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            BLOG_ADVANCED_TEMPLATE_DIR . 'edit_visibility.tpl');        
        
        $main->assign('blog_owner', $this->blogModel->getOwner());
        $main->assign('blog_model', $this->blogModel);

        $this->setCentralView($main, $this->blogModel);
        
        $this->view();
    }
    
    protected function ajaxPreviewEntry(){
    	
        $cUser = Session::getInstance()->getVisitor();
        $_REQUEST['bloguser'] = $cUser->getUsername();
        
        // initialize blog model etc.
        $this->preProcess();
        
        // rights check
        if (!($cUser->hasRight('BLOG_ADVANCED_OWN_ADMIN') and $this->blogModel->isAdministrativeAuthority($cUser))) {
            exit;
        }        
        
        $entry = null;
        
        if (!Session::getInstance()->getUserData('random_blog_advanced')) {
            Session::getInstance()->storeUserData('random_blog_advanced', md5(uniqid(rand())));
        }
        
        if (array_key_exists('entry_id', $_REQUEST) && $_REQUEST['entry_id'] != 'false') {
            // look if suitable object is found in session
            $entry = Session::getInstance()->getEntryDataChecked('blogAdvanced', $_REQUEST['entry_id']);
            // if not, load entry
            if ($entry === false) {
                // fetch only blog entries that belong to blog's owner
                $entry = $this->blogModel->getEntryById($_GET['entry_id'], true);
            }
        } else {
            // if entry is new, a version could be in session though
            $entry = Session::getInstance()->getEntryDataChecked('blogAdvanced', 0);
        }
        
        // form fields and their requirements
        $formFields = array(
                    // do NOT escape entrytext
            'entry_text'           => array('required' => true,  'check' => 'isValidAlmostAlways', 'escape' => false),
            'entry_title'          => array('required' => true,  'check' => 'isValidByLength', 'params' => array('lengthLo' => 2, 'lengthHi' => 250)),
            'entry_trackbacks'     => array('required' => false, 'check' => 'isValidAlmostAlways'),
            'entry_allow_comments' => array('required' => false, 'check' => 'isValidAlmostAlways'),
                           );
        // some validity checks and HTML-escape
        $this->validateInput($formFields);            
        //var_dump($_POST['entry_text']);
        if (!$entry) {
            $entry = new BlogAdvancedEntry($_POST['entry_text'], $cUser, self::getAjaxParseSettings());
        } else {
            $entry->setContentRaw($_POST['entry_text']);
            $entry->setParseSettings(self::getAjaxParseSettings());
        }
        
        if ($this->blogModel->getOwner() instanceof GroupModel) {
            $entry->setGroup($this->blogModel->getOwner()); 
        }
        
        // common properties
        $entry->setTitle($_POST['entry_title']);
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/blogadvanced/blog_entry_preview.tpl');
        $main->assign('blog_editentry',$entry);
        $main->display();
    }
    
    protected function createUserBlog() {
        $blogUser = Session::getInstance()->getVisitor();
        
        if (!$blogUser->hasRight('BLOG_ADVANCED_CREATE')) {
            $this->rightsMissingView('BLOG_ADVANCED_CREATE');
        }
        if (UserBlogAdvancedModel::getBlog($blogUser) != null) {
            $this->errorView(ERR_BLOG_EXISTS);
        }
        $blogModel = null;
        
        if (!empty($_POST['create_submit'])) {
        	// form fields and their requirements
            $formFields = array(
                'blog_title'          => array('required' => false, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 2, 'lengthHi' => 100)),
                'blog_subtitle'       => array('required' => false, 'check' => 'isValidByLength', 'params' => array('lengthHi' => 150)),
                               );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);
            
            $blogModel = new UserBlogAdvancedModel($blogUser);
            if (!empty($_POST['blog_title'])) {
                $blogModel->setTitle($_POST['blog_title']);
            } else {
            	$blogModel->setTitle('UniHelp-Blog von ' . $blogUser->getUsername());
            }
            $blogModel->setSubtitle($_POST['blog_subtitle']);
            
            if (count($this->errors) == 0) {
                //var_dump($blogModel);
                
                // start transaction because we want to create a blog
                // and add user to a new role
                $DB = Database::getHandle();
                $DB->StartTrans();
                
                $blogModel->save();
                $role = RoleModel::getRoleByName('blog_owners');
                $role->addUsers(array($blogUser));
                
                // force rights reload after role membership change
                $blogUser->removeFromUserOnlineList();
                
                // make known to user model that it has got a blog now
                $blogUser->clearHasBlogCache();
                
                $DB->CompleteTrans();
                
                header('Location: ' . rewrite_blog(array('owner' => $blogUser, 'extern' => true)));
                exit;
            }            
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            BLOG_ADVANCED_TEMPLATE_DIR . 'create_blog.tpl');        
        
        $main->assign('visitor', $blogUser);
        $main->assign('blog_model', $blogModel);
        $main->assign('blog_errors', $this->errors);
        $this->setCentralView($main, null);
        $this->view();
    }
    
    protected function showTermsOfUse() {
    	$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            BLOG_ADVANCED_TEMPLATE_DIR . 'terms_of_use.tpl');
        $main->display();
    }
    
    protected function addUserBlogTrackback() {
    	// initialize blog model etc.
        $this->preProcess();
        
        if (empty($_REQUEST['url'])) {
        	self::sendTrackbackFailure();
            return;
        }
        
        // fetch only blog entries that belong to blog's owner
        $entry = $this->blogModel->getEntryById($_GET['entry_id'], true);
        
        try {
            $trackback = new BlogAdvancedTrackbackModel;
            $trackback->weblogURL = htmlspecialchars($_REQUEST['url']);
            $trackback->weblogName = htmlspecialchars($_REQUEST['blog_name']);
            $trackback->title = htmlspecialchars(
                    empty($_REQUEST['title']) ? $_REQUEST['url'] : $_REQUEST['title']);
            $trackback->body = htmlspecialchars($_REQUEST['excerpt']);
            $trackback->blogEntry = $entry;
            
            $trackback->save();
        } catch (Exception $e) {
            self::sendTrackbackFailure();
            return;
        }
        
        self::sendTrackbackSuccess();
    }
    
    public function test() {
    	$this->preProcess();
        $this->sendRemoteTrackback('http://s9y.sunburner/comment.php?type=trackback&entry_id=4',
            BlogAdvancedEntry::getEntryById(3));
    }
    
    protected function sendRemoteTrackback($url, $entry) {
    	$blogOwner = $this->blogModel->getOwner();
        
        require_once BASE . '/lib/lib-pear/HTTP/Request.php';
        $options = array('allowRedirects' => true, 'maxRedirects' => 5, 'method' => 'POST');
        $req = new HTTP_Request($url, $options);
        $data =      'url=' . rawurlencode(rewrite_blog(array(
                                'owner' => $blogOwner,
                                'entry' => $entry,
                                'extern' => 1,
                                )))
                . '&title=' . rawurlencode(htmlspecialchars_decode($entry->title))
            . '&blog_name=' . rawurlencode(htmlspecialchars_decode($this->blogModel->getTitle()))
              . '&excerpt=' . rawurlencode(substr(strip_tags($entry->content),0,255)); 
        $req->addRawPostData($data, true);
        
        // TODO: change error_reporting due to buggy PEAR (OS_WINDOWS notice)
        $last_error = error_reporting(0);
        $res = $req->sendRequest();
        error_reporting($last_error);

        if (PEAR::isError($res)) {
            return false;
        }
    
        $res = $req->getResponseBody();
        return self::isTrackbackSuccess($res);
        //var_dump($res);
        //return $fContent;
    }
    
    /**
     * Check a HTTP response if it is a valid XML trackback response
     * @note copied and adapted from s9y
     * 
     * @param   string  HTTP Response string
     * @return  boolean
     */
    protected static function isTrackbackSuccess($resp) {
        if (preg_match('@<error>(\d+)</error>@', $resp, $matches)) {
            if ((int) $matches[1] === 0) {
                return true;
            } else {
                if (preg_match('@<message>([^<]+)</message>@', $resp, $matches)) {
                    //return $matches[1];
                    return false;
                }
                else {
                    //return 'unknown error';
                    return false;
                }
            }
        }
        return true;
    }
    
    private static function sendTrackbackFailure() {
        header('Content-type: text/xml');
        print '<?xml version="1.0" encoding="iso-8859-1"?>' . "\n";
        print '<response>
    <error>1</error>
    <message>Danger Will Robinson, trackback failed.</message>
</response>';
        exit;
    }
    
    private static function sendTrackbackSuccess() {
        header('Content-type: text/xml');
        print '<?xml version="1.0" encoding="iso-8859-1"?>' . "\n";
        print '<response>
    <error>0</error>
</response>';
        exit;
    }
    
    // caching for blog entries is too complicated at the moment
    // because the edit button has to be displayed with every entry
    // and this depends on author and owner of entry
    // (and owner may be a user or group) so that a quite simple
    // caching strategy is rendered virtually impossible
    //               (linap, 28.05.07)
    /*
    protected function getBlogCachekeyBasic() {
        return 'blog|' . $this->blogModel->getOwner()->getHash();
    }
    
    protected function getBlogCachekey($categoryId = null, $archiveDate = array(), $blogPage = V_BLOG_ADVANCED_START_PAGE) {
        $cacheKey = $this->getBlogCachekeyBasic();
        $cacheKey .= '|' . $blogPage;
        $cacheKey .= '|' . (($categoryId == null) 
                                ? '0' 
                                : $categoryId);
        $cacheKey .= '|' . ((count($archiveDate) <= 1) 
                                ? '0' 
                                : implode('-', $archiveDate));
        return $cacheKey;
    }
    */
        
    protected function blogView($display = true, $categoryId = null, $archiveDate = array(), $blogPage = V_BLOG_ADVANCED_START_PAGE) {
    	$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            BLOG_ADVANCED_TEMPLATE_DIR . 'blog_overview.tpl');

        // CACHEME: improve caching
        
        $archiveDateEpoch = null;
        $archiveDateEpochDay = null;
            
        // if we want date-filtering
        if (count($archiveDate) > 1){
            $filter = BlogAdvancedModel::getFilterClass(
                array(BaseFilter::FILTER_ENTRYDATE_SINGLE => $archiveDate)
                );
            $this->blogModel->setFilter($filter);
            if (count($archiveDate) == 2) {
                $archiveDateEpoch = mktime(0,0,0,$archiveDate[1], 1, $archiveDate[0]);
            } else {
            	$archiveDateEpochDay = mktime(0,0,0,$archiveDate[1], $archiveDate[2], $archiveDate[0]);
            }
        }
        
        $entries = $this->blogModel->getAllParsedEntries($categoryId, V_BLOG_ADVANCED_ENTRIES_PER_PAGE, 
                    ($blogPage-1) * V_BLOG_ADVANCED_ENTRIES_PER_PAGE);
        $categories = $this->blogModel->getCategories();
        $this->prepareCalendarData($main, $archiveDate);
        
        $main->assign('blog_model', $this->blogModel);
        
        $main->assign('blog_entries', $entries);
        $main->assign('blog_categories', $categories);
        $main->assign('blog_selected_category', ($categoryId) ? $categories[$categoryId] : null);
        // depending on whether the archive date includes the day,
        // different variables are given to the template
        if ($archiveDateEpochDay) {
            $main->assign('blog_selected_archive_day', $archiveDateEpochDay);
        } else {
            $main->assign('blog_selected_archive', $archiveDateEpoch);
        }
        $main->assign('blog_page', $blogPage);
        $main->assign('blog_entries_number', $this->blogModel->getEntriesNumber($categoryId));
        $main->assign('blog_pages_number', ceil($this->blogModel->getEntriesNumber($categoryId) / V_BLOG_ADVANCED_ENTRIES_PER_PAGE));
        $main->assign('blog_archives_months', self::getArchivesMonths());

        if (!$display) {
        	return $main;
        }

        // assign some variables that the blog master template needs, too
        $smartyView = $this->getSmartyView();
        $smartyView->assign('show_sidebar', true);
        
     
        if ($categoryId) {
            $smartyView->assign('blog_selected_category', $categories[$categoryId]);
        }
        // depending on whether the archive date includes the day,
        // different variables are given to the template
        if ($archiveDateEpochDay) {
            $smartyView->assign('blog_selected_archive_day', $archiveDateEpochDay);
        } else {
            $smartyView->assign('blog_selected_archive', $archiveDateEpoch);
        }
        $this->setCentralView($main, $this->blogModel);
        $this->view();
    }
    
    protected function prepareCalendarData($main, $date = null) {
    	if ($date == null) {
    		$date = explode('-', date('Y-m'));
    	}
        
        $minMaxDate = $this->blogModel->getMinMaxDate();
        $calendarDate = array();
        $calendarDate['current'] = mktime(0,0,0,$date[1], 1, $date[0]);
        $nextDate = mktime(0,0,0,$date[1]+1, 1, $date[0]);
        if ($nextDate < time() /*$minMaxDate['max']*/ ) {
            $calendarDate['next'] = explode('-', date('Y-m', $nextDate));
        }
        $prevDate = mktime(0,0,0,$date[1], 0, $date[0]);
        if ($prevDate > $minMaxDate['min']) {
            $calendarDate['previous'] = explode('-', date('Y-m', $prevDate));
        }
        
        $main->assign('blog_calendar', $this->getCalendarDays($date));
        $main->assign('blog_calendar_date', $calendarDate);
    }
    
    
    
    protected function blogEntryView($entry, $display = true) {
    	$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            BLOG_ADVANCED_TEMPLATE_DIR . 'blog_entry_detailed.tpl');        
        // CACHEME: improve caching
        
        $this->prepareCalendarData($main);
        
        $main->assign('blog_entry', $entry);
        $main->assign('blog_categories', $this->blogModel->getCategories());
        $main->assign('blog_owner', $this->blogModel->getOwner());
        $main->assign('blog_archives_months', self::getArchivesMonths());
        $main->assign('blog_randomstring', Session::getInstance()->getUserData('random_blog_advanced'));
        $main->assign('visitor', Session::getInstance()->getVisitor());
        
        $captcha = new CaptchaComputation();
        $captcha->generate();
        $main->assign('comment_captcha', $captcha);
        /*
        $oldCaptcha = Session::getInstance()->getViewData('captcha', null);
        if ($oldCaptcha != null) {
            $oldCaptcha->cleanup();   
        }
        Session::getInstance()->storeViewData('captcha_old', $oldCaptcha);*/
        Session::getInstance()->storeViewData('captcha', $captcha);
        
        if (!$display) {
            return $main;
        }
        
        // assign some variables that the blog master template needs, too
        $smartyView = $this->getSmartyView();
        $smartyView->assign('show_sidebar', true);
        
        $this->setCentralView($main, $this->blogModel);
        
        $this->view();
    }
   
    protected static function getArchivesMonths($lastMonths = 3) {
        $year = date('Y');
        $month = date('m');
        
        $archivesMonth = array();
        for ($d=0; $d<$lastMonths; ++$d) {
            $t = mktime(0,0,0,$month - $d + 1, 0, $year);
            array_push($archivesMonth, 
                array('name' => strftime('%B %Y', $t),
                      'dateForLink' => explode('-', date('Y-m', $t))));
        }
        
        return $archivesMonth;
    } 
    
    /*
     * date = array(year,month)
     */
    protected function getCalendarDays($date) {
        $firstDay = mktime(0,0,0,$date[1], 1, $date[0]);
        // day of week for first day of given month
        $firstDayDoW = date('N', $firstDay);
        
        // determine days on which a blog entry was made
        $blogDays = $this->blogModel->getEntryDays($date[0], $date[1]);
        
        // indicator for a day not to display
        $invalidDay = array('day' => 0);
        
        // return array
        $days = array();
        // intermediate arrays for weeks
        $week = array();
        // pad first week with empty days so that first entry in array is Monday (1)
        for ( ; $firstDayDoW > 1; --$firstDayDoW) {
        	array_push($week, $invalidDay);
        }
        
        $toDay = explode('-', date('d-m'));
        for ($day=1; date('m', $currentDate = mktime(0,0,0,$date[1], $day, $date[0])) == $date[1]; ++$day) {
            // each 7 days a week is full
            if (count($week) == 7) {
            	array_push($days, $week);
                $week = array();
            }
            // expand date by current date
            $date[2] = $day;
            array_push($week, array('day' => $day, 'hasEntries' => array_key_exists($day, $blogDays),
                                    'dateForLink' => $date,
                                    'today' => ($toDay[0] == $day && $toDay[1] == $date[1])));
        }
        
        // fill last week with empty days
        while (count($week) < 7) {
            array_push($week, $invalidDay);
        }
        array_push($days, $week);
        return $days;
    }
    
    protected function exportUserBlog() {
        // TODO
    }
}

?>
