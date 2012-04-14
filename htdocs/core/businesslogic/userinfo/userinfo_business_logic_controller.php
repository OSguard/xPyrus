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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/businesslogic/userinfo/userinfo_business_logic_controller.php $

require_once CORE_DIR . '/businesslogic/business_logic_controller.php';

require_once MODEL_DIR . '/base/user_protected_model.php';
require_once MODEL_DIR . '/base/friend_model.php';

//require_once MODEL_DIR . '/blog/diary_model.php';
//require_once MODEL_DIR . '/gb/guestbook_model.php';

// load some useful constants for working on <form>-input
require_once CORE_DIR . '/constants/form_constants.php';
require_once CORE_DIR . '/constants/value_constants.php';

require_once CORE_DIR . '/utils/attachment_handler.php';
require_once CORE_DIR . '/utils/user_ipc.php';

define('USERINFO_TEMPLATE_DIR', 'modules/userinfo/');

/**
 * The former /user_info.php.
 * Contains user information, statistics, diary and guestbook
 * @package Controller
 * @author linap
 * @version $Id: userinfo_business_logic_controller.php 5807 2008-04-12 21:23:22Z trehn $
 */
class UserinfoBusinessLogicController extends BusinessLogicController {
    /**
     * @var UserModel
     * user the page is about
     */
    protected $userModel;
    /**
     * @var DiaryModel
     * diary of user
     */
    protected $diaryModel;
    /**
     * @var GuestbookModel
     * guestbook of user
     */
    protected $guestbookModel;
    /**
     * @var UserModel
     * user who is visiting the page
     */
    protected $visitingUserModel;
    /**
     * @var boolean
     * true, if visitor doesn't view his own page
     */
    protected $isExternalView;
    /**
     * @var int
     * describes the relationship the user has with the visitor
     * in terms of FriendModel-constants
     */
    protected $userVisitorRelationship = FriendModel::HAS_NO_RELATION;
    /**
     * @var BaseEntryModel
     * in case an entry is going to be edited,
     * this variable contains the related entry model
     */
    protected $editEntryModel;
    /**
     * @var int
     * the size in bytes an user entry attachmentmay have at most
     */
    protected $maxAttachmentSize;
    
    /**
     * @var string
     * random entry sequence identifier (to avoid unwanted double-postings)
     */
    protected $entryRandId;

    protected $currentState;
    const STATE_ADD = 1;
    const STATE_EDIT = 2;
    const STATE_QUOTE = 3;
    const STATE_COMMENT = 4;

    /**
     * @var array of int
     * holds the page number, at which diary/guestbook are displayed
     */
    protected $elementPages;

    /**
     * @var array of int
     * holds the number to display on a diary/guestbook page
     */
    protected $entriesPerPage;

    /**
     * determines which content is displayed in the current tab pane
     */
    protected $tabPane;

    /**
     * @var associative array containing the entry ids that are to be highlighted
     */
    protected $markIds;

	public function __construct($ajaxView = false) {
		parent::__construct($ajaxView);

		// by default, no entry is going to be edited
		$this->editEntryModel = null;

		// set default state: add, so that add entry dialog is going to be displayed
		$this->currentState = self::STATE_ADD;
    }

    protected function isValidUser() {
        return $this->userModel != null;
    }
    
    protected function showUnknownUser() {
        $this->errorView('Ham wa nich :D');
    }
    
    protected function showDeletedUser() {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'user_info_deleted.tpl');
        $main->assign('userinfo_user', $this->userModel);
        $this->setCentralView($main);
        $this->view();
    }
    
    protected function createUserObject($showPageOnError = true) {
        if ($this->userModel != null) {
            return $this->userModel;
        }
        // create model of user the page is about
        $username = InputValidator::getRequestData('username', '');
        // if we have no username, but a regular session user, we show his profile
        if ($username == '' and Session::getInstance()->getVisitor()->isRegularLocalUser()) {
            $this->userModel = Session::getInstance()->getVisitor();
        } else {
            $this->userModel = UserProtectedModel::getUserByUsername($username);
        }
        
        if (!$this->isValidUser()) {
            if ($showPageOnError) {
                $this->showUnknownUser();
                exit;
            } else {
                return null;
            }
        }
        
        if ($this->userModel->getPersonTypeId() == PersonTypeModel::getPersonTypeByName('gelöscht')->id) {
            if ($showPageOnError) {
                $this->showDeletedUser();
                exit;
            } else {
                return null;
            }
        }
        
        
        return $this->userModel;
    }

    protected function preProcess() {
        $this->createUserObject();
                
        if (!array_key_exists(F_ENTRY_RANDID, $_REQUEST)) {
            $this->entryRandId = Session::getInstance()->generateNewRandomId();
        } else {
            $this->entryRandId = $_REQUEST[F_ENTRY_RANDID];
        }

        $this->visitingUserModel = Session::getInstance()->getVisitor();

        $this->userVisitorRelationship = FriendModel::getRelationType($this->userModel, $this->visitingUserModel);
        
        // set privacy context
        $this->determinePrivacyContextLevel();

		$this->isExternalView = (!$this->visitingUserModel->equals($this->userModel) and
	                           $this->visitingUserModel->isLoggedIn());

        $this->maxAttachmentSize = GlobalSettings::getGlobalSetting('ENTRY_MAX_ATTACHMENT_SIZE') * 1024;

        require_once MODEL_DIR . '/blog/diary_model.php';
        $this->diaryModel = new DiaryModel($this->userModel);

        require_once MODEL_DIR . '/gb/guestbook_model.php';
        $this->guestbookModel = new GuestbookModel($this->userModel);

        // default diary and guestbook pages are the first
        $this->elementPages['diary'] = array_key_exists('diarypage', $_REQUEST)
                    ? $_REQUEST['diarypage'] : V_BLOG_START_PAGE;
        $this->elementPages['guestbook'] = array_key_exists('gbpage', $_REQUEST)
                    ? $_REQUEST['gbpage'] :  V_GUESTBOOK_START_PAGE;

        $this->entriesPerPage['diary']      = V_BLOG_ENTRIES_PER_PAGE;
        $this->entriesPerPage['guestbook'] = V_GUESTBOOK_ENTRIES_PER_PAGE;

        if (array_key_exists('gbfilter', $_REQUEST)) {
            $this->visitingUserModel->setGBFilterShow( $_REQUEST['gbfilter'] == 1 );
            $this->visitingUserModel->save();
        }
        if (array_key_exists('diaryfilter', $_REQUEST)) {
            $this->visitingUserModel->setDiaryFilterShow( $_REQUEST['diaryfilter'] == 1 );
            $this->visitingUserModel->save();
        }
    }

    /**
     * default methhod: show user page
     */
    protected function getDefaultMethod() {
        return 'showUserInfo';
    }


    /**
     * set methods that are allowed for calling by the controller
     */
    protected function getAllowedMethods() {
        // allow viewing and some data manipulation
        return array_merge(parent::getAllowedMethods(),
           array (
            /* main */
            'showUserInfo',

            /* diary */
			'addDiaryEntry',
            'prepareEditDiaryEntry',
            'editDiaryEntry',
            'deleteDiaryEntry',

            /* guestbook */
			'addGBEntry',
            'prepareEditGBEntry',
            'editGBEntry',
            'quoteGBEntry',
            'prepareCommentGBEntry',
            'commentGBEntry',
            'deleteGBEntry',

            // reporting
            'reportDiary',
            'reportGB',

            /* friends */
			'addFriend',
            'removeFriend',

            /*extras*/
            'reverseFriendlist',

            /*Ajax*/
            'ajaxUserGB',
            'ajaxGuestbookStats',
            'ajaxUserStats',
            'ajaxUserContact',
            'ajaxSmallWorld',
            'ajaxUserAward',
            'ajaxUserDescription',
            'ajaxGuestbook',
            'ajaxDiaryFilterShow',
            'ajaxGuestbookFilterShow',
            'ajaxGuestbookPreview',
            'ajaxDiaryPreview'
        )
        );
    }
    
    public function getMethodObject($method) {
        $parameters = $this->getParameters($method);
        if ('showUserInfo' == $method or
            'addDiaryEntry' == $method or
            'addGBEntry' == $method or
            'prepareEditDiaryEntry' == $method or
            'prepareEditGBEntry' == $method or
            'quoteGBEntry' == $method or
            'deleteDiaryEntry' == $method or
            'deleteGBEntry' == $method or
            'prepareCommentGBEntry' == $method or
            'addFriend' == $method or
            'removeFriend' == $method or
            'smallWorld' == $method or
            'advancedStats' == $method) {
            if ($parameters['user'] == null) {
                return BLCMethod::getDefaultMethod();
            }
            return new BLCMethod($parameters['user']->getName(),
                rewrite_userinfo(array('user' => $parameters['user'])),
                BLCMethod::getDefaultMethod());
        }elseif('reverseFriendlist' == $method){
            $this->visitingUserModel = Session::getInstance()->getVisitor();
            return new BLCMethod(NAME_REVERSE_FRIENDLIST,
                rewrite_userinfo(array('user' => $this->visitingUserModel, 'reverseFriendlist' => true)),
                $this->getMethodObject('showUserInfo'));    
        }
        
        return parent::getMethodObject($method);
    }
    
    protected function collectParameters($method) {
        $parameters = array();
        if ('showUserInfo' == $method or
            'addDiaryEntry' == $method or
            'addGBEntry' == $method or
            'prepareEditDiaryEntry' == $method or
            'prepareEditGBEntry' == $method or
            'quoteGBEntry' == $method or
            'deleteDiaryEntry' == $method or
            'deleteGBEntry' == $method or
            'prepareCommentGBEntry' == $method or
            'addFriend' == $method or
            'removeFriend' == $method or
            'smallWorld' == $method or
            'advancedStats' == $method) {
            $parameters['user'] = $this->createUserObject(false);
        }
        $this->_parameters[$method] = $parameters;
        
        parent::collectParameters($method);
    }

    protected function showUserInfo() {
        $this->preProcess();
        $this->defaultView();
    }

    /**
     * tries to add a new diary entry or previews a new
     * actually a wrapper for _addDiaryEntry
     */
    public function addDiaryEntry() {
        //print "should add diary entry :D";
        $this->preProcess();

        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('BLOG_ENTRY_ADD')) {
            $this->rightsMissingView('BLOG_ENTRY_ADD');
        }

        $this->currentState = self::STATE_ADD;
        if ($this->_addDiaryEntry()) {
            self::notifyIPC(new UserIPC($this->userModel->id), 'DIARY_CHANGED');
            header('Location: ' . rewrite_userinfo(
                array('user' => $this->userModel,
                      'diarypage' => $this->elementPages['diary'],
                      'gbpage' => $this->elementPages['guestbook'], 
                      'extern' => true)));
            exit;
        }

        // show changed page
        $this->defaultView();
    }

    /**
     * prepares a diary entry for editing, i.e. display it in the
     * <textarea> at the bottom
     * actually a wrapper for _prepareEditDiaryEntry
     */
    public function prepareEditDiaryEntry() {
        //print "should prepare diary entry :D";
        $this->preProcess();

        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('BLOG_ENTRY_EDIT') and
            !$this->visitingUserModel->hasRight('BLOG_ENTRY_ADMIN')) {
            return;
        }

        if ($this->_prepareEditDiaryEntry($this->visitingUserModel->hasRight('BLOG_ENTRY_ADMIN'))) {
            $this->currentState = self::STATE_EDIT;
        }

        // show changed page
        $this->defaultView();
    }

    /**
     * updates a diary entry according to POSTed data
     * actually a wrapper for _editDiaryEntry
     */
    public function editDiaryEntry() {
        //print "should update diary entry :D";
        $this->preProcess();

        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('BLOG_ENTRY_EDIT') and
            !$this->visitingUserModel->hasRight('BLOG_ENTRY_ADMIN')) {
                    $this->rightsMissingView('BLOG_ENTRY_EDIT');
        }
        //VAR
        //var_dump($this->previewMode);

        if (!($this->previewMode or $this->uploadMode)) {
            // after the update action, we got back to normal add mode
            $this->currentState = self::STATE_ADD;
        } else {
            // update action has not take place; still in edit mode
            $this->currentState = self::STATE_EDIT;
        }
        if ($this->_editDiaryEntry()) {
    		if (!$this->visitingUserModel->equals($this->userModel)) {
    			$this->addLog('edit other\'s diary entry');
    		}
    	    
            if (defined('CACHETEST')) {
                $cacheKey = $this->getDiaryEntryCachekey($_REQUEST['diaryid']);
                // TODO: find a better way than instanciating the template here
                // perhaps it would suffice to move the view creation to one function
                // that is also called on normal thread entry view
                $threadEntriesView = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_diary_entry.tpl');
                $threadEntriesView->enableCaching();
                $threadEntriesView->clearCache($cacheKey);
                self::clearCacheIds($cacheKey);
            }
    	    self::notifyIPC(new UserIPC($this->userModel->id), 'DIARY_CHANGED');
        	header('Location: ' . rewrite_userinfo(
                array('user' => $this->userModel,
                      'diarypage' => $this->elementPages['diary'],
                      'extern' => true)));
            exit;
        }

        // show changed page
        $this->defaultView();
    }

    /**
     * deletes an entry from the diary
     * actually a wrapper for _deleteDiaryEntry
     */
    public function deleteDiaryEntry() {
        //print "should delete diary entry :D";
        $this->preProcess();

        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('BLOG_ENTRY_DELETE') and
            !$this->visitingUserModel->hasRight('BLOG_ENTRY_ADMIN')) {
            return;
        }

        $this->currentState = self::STATE_ADD;
        $this->_deleteDiaryEntry($this->visitingUserModel->hasRight('BLOG_ENTRY_ADMIN'));
        
        self::notifyIPC(new UserIPC($this->userModel->id), 'DIARY_CHANGED');
        
        if (!$this->visitingUserModel->equals($this->userModel)) {
			$this->addLog('delete other\'s diary entry');
        }
	
        // show changed page
        $this->defaultView();
    }

    /**
     * tries to add a new guestbook entry or previews a new
     * actually a wrapper for _addDiaryEntry
     */
    public function addGBEntry() {
        //print "should add gb entry :D";
        $this->preProcess();
        
        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('GB_ENTRY_ADD')) {
        	$this->rightsMissingView('GB_ENTRY_ADD');
        }
        if ($this->userVisitorRelationship == FriendModel::IS_FOE) {
            $this->errorView(ERR_ON_IGNORELIST);
            exit;
        }

        $this->currentState = self::STATE_ADD;
        if ($this->_addGBEntry()) {
        	self::notifyIPC(new UserIPC($this->userModel->id), 'GUESTBOOK_CHANGED');
            header('Location: ' . rewrite_userinfo(
                array('user' => $this->userModel,
                      'gbpage' => $this->elementPages['guestbook'], 
                      'extern' => true)));
            exit;
        }

        // show changed page
        $this->defaultView();
    }

    /**
     * prepares a guestbook entry for editing, i.e. display it in the
     * <textarea> at the bottom
     * actually a wrapper for _prepareEditGBEntry
     */
    public function prepareEditGBEntry() {
        //print "should prepare guestbook entry :D";
        $this->preProcess();

        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('GB_ENTRY_EDIT') and
            !$this->visitingUserModel->hasRight('GB_ENTRY_ADMIN')) {
            $this->rightsMissingView('GB_ENTRY_EDIT');
        }
        
        if ($this->_prepareEditGBEntry($this->visitingUserModel->hasRight('GB_ENTRY_ADMIN'))) {
            $this->currentState = self::STATE_EDIT;
        }

        // show changed page
        $this->defaultView();
    }

    /**
     * updates a guestbook entry according to POSTed data
     * actually a wrapper for _editGBEntry
     */
    public function editGBEntry() {
        //print "should update guestbook entry :D";
        $this->preProcess();

        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('GB_ENTRY_EDIT') and
            !$this->visitingUserModel->hasRight('GB_ENTRY_ADMIN')) {
                    $this->rightsMissingView('GB_ENTRY_EDIT');
        }

        if (!($this->previewMode or $this->uploadMode)) {
            // after the update action, we get back to normal add mode
            $this->currentState = self::STATE_ADD;
        } else {
            // update action has not take place; still in edit mode
            $this->currentState = self::STATE_EDIT;
        }
        if ($this->_editGBEntry($this->visitingUserModel->hasRight('GB_ENTRY_ADMIN'))) {
    		if (!$this->visitingUserModel->equals($this->userModel)) {
    			$this->addLog('edit other\'s guestbook entry');
    		}
    
            if (defined('CACHETEST')) {
                $cacheKey = $this->getGuestbookEntryCachekey($_REQUEST['gbid']);
                // TODO: find a better way than instanciating the template here
                // perhaps it would suffice to move the view creation to one function
                // that is also called on normal thread entry view
                $threadEntriesView = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_guestbook_entry.tpl');
                $threadEntriesView->enableCaching();
                $threadEntriesView->clearCache($cacheKey);
                self::clearCacheIds($cacheKey);
            }
    		self::notifyIPC(new UserIPC($this->userModel->id), 'GUESTBOOK_CHANGED');
            header('Location: ' . rewrite_userinfo(
                array('user' => $this->userModel,
                      'gbpage' => $this->elementPages['guestbook'],
                      'extern' => true)));
            exit;
        }

        // show changed page
        $this->defaultView();
    }

    /**
      * prepares a guestbook entry for quoting in another (new) entry
      * actually a wrapper for _quoteGBEntry
      */
    public function quoteGBEntry() {
        //print "should quote guestbook entry :D";
        $this->preProcess();

        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('GB_ENTRY_QUOTE')) {
                $this->rightsMissingView('GB_ENTRY_QUOTE');
        }

        $this->currentState = self::STATE_QUOTE;
        $this->_quoteGBEntry();
        
        // show changed page
        $this->defaultView();
    }

    /**
      * prepares commenting on a guestbook entry
      * actually a wrapper for _prepareCommentGBEntry
      */
    public function prepareCommentGBEntry() {
        //print "should prepare commenting guestbook entry :D";
        $this->preProcess();

        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('GB_ENTRY_COMMENT')) {
                $this->rightsMissingView('GB_ENTRY_COMMENT');
        }

        $this->currentState = self::STATE_COMMENT;
        $this->_prepareCommentGBEntry();

        // show changed page
        $this->defaultView();
    }

    /**
      * updates the comment of a guestbook entry according to POSTed data
      * actually a wrapper for _commentGBEntry
     */
    public function commentGBEntry() {
        //print "should update guestbook entry :D";
        $this->preProcess();
        
        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('GB_ENTRY_COMMENT')) {
            $this->rightsMissingView('GB_ENTRY_COMMENT');
        }

        // after the update action, we got back to normal add mode
        $this->currentState = self::STATE_ADD;

        $this->_commentGBEntry();
        if (defined('CACHETEST')) {
            $cacheKey = $this->getGuestbookEntryCachekey($_REQUEST['gbid']);
            // TODO: find a better way than instanciating the template here
            // perhaps it would suffice to move the view creation to one function
            // that is also called on normal thread entry view
            $threadEntriesView = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_guestbook_entry.tpl');
            $threadEntriesView->enableCaching();
            $threadEntriesView->clearCache($cacheKey);
            self::clearCacheIds($cacheKey);
        }
        self::notifyIPC(new UserIPC($this->userModel->id), 'GUESTBOOK_CHANGED');

        // there is no preview mode for this kind of action
        // therefore we can redirect in any case
    	header('Location: ' . rewrite_userinfo(
            array('user' => $this->userModel,
                  'gbpage' => $this->elementPages['guestbook'],
                  'extern' => true)));
        exit;
    }

    /**
     * deletes an entry from the guestbook
     * actually a wrapper for _deleteGBEntry
     */
    public function deleteGBEntry() {
        //print "should delete gb entry :D";
        $this->preProcess();

        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('GB_ENTRY_DELETE') and
            !$this->visitingUserModel->hasRight('GB_ENTRY_ADMIN')) {
                    $this->rightsMissingView('GB_ENTRY_DELETE');
        }

        $this->currentState = self::STATE_ADD;
        $this->_deleteGBEntry($this->visitingUserModel->hasRight('GB_ENTRY_ADMIN'));

        self::notifyIPC(new UserIPC($this->userModel->id), 'GUESTBOOK_CHANGED');
	
    	if (!$this->visitingUserModel->equals($this->userModel) or $this->visitingUserModel->hasRight('GB_ENTRY_ADMIN')) {
			$this->addLog('delete other\'s guestbook entry');
	    }
	
        // show changed page
        $this->defaultView();
    }

    /**
     * tries to add user the page is about to visitors friendlist
     * actually a wrapper for _addFriend
     */
    public function addFriend() {
        //print "should add friend :D";
        $this->preProcess();
        
        if ($this->visitingUserModel->equals($this->userModel)) {
            $this->errorView(ERR_FRIENDLIST_SELF);
            exit;
        }
        if ($this->userVisitorRelationship == FriendModel::IS_FOE) {
            $this->errorView(ERR_ON_IGNORELIST);
            exit;
        }

        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('FRIENDLIST_MODIFY')) {
                    $this->rightsMissingView('FRIENDLIST_MODIFY');
        }

        $this->currentState = self::STATE_ADD;
        $this->_addFriend();
        
        self::notifyIPC(new UserIPC($this->visitingUserModel->id), 'FRIENDLIST_CHANGED');

        // show changed page
        $this->defaultView();
    }

    /**
     * tries to remove user the page is about from visitors friendlist
     * actually a wrapper for _removeFriend
     */
    public function removeFriend() {
        //print "should add friend :D";
        $this->preProcess();

        // check if visitor's rights suffice for the action
        if (!$this->visitingUserModel->hasRight('FRIENDLIST_MODIFY')) {
            $this->rightsMissingView('FRIENDLIST_MODIFY');
        }

        $this->currentState = self::STATE_ADD;
        $this->_removeFriend();
        
        self::notifyIPC(new UserIPC($this->visitingUserModel->id), 'FRIENDLIST_CHANGED');

        // show changed page
        $this->defaultView();
    }

    public function reportGB() {
        $this->preProcess();

        if (!array_key_exists('entryid', $_REQUEST)) {
            $this->errorView(ERR_NO_ENTRY_TO_REPORT);
        }

        $entry = $this->guestbookModel->getEntryById($_REQUEST['entryid'], null, false);

        $mantisBLC = ControllerFactory::createSupportHandlingController();
        if (null == $mantisBLC) {
            throw new CoreException (Logging::getErrorMessage(CORE_CONTROLLER_FAILED, ControllerFactory::createSupportHandlingControllerName()));
        }
        $mantisBLC->postProcess(null, array(F_SOURCE_CAT => F_SOURCE_REPORT_ENTRY,
                                            F_DIRECTLINK => rewrite_userinfo(array('linkGBEntryId'=>$entry->id, 'user'=>$this->userModel,
                                            	'extern'=>1) )."#gbentry".$entry->id,
                                            F_SOURCE =>  "Guestbook",
                                            F_ENTRY_TEXT => $entry->getContentRaw()));
        /*header('Location: /mantis?' . F_SOURCE_CAT . '=' . F_SOURCE_REPORT_ENTRY .
                                '&' . F_DIRECTLINK . '=' . "http" .
                                '&' . F_SOURCE . '=' . "gb" .
                                '&' . F_ENTRY_TEXT . '=' . "text");*/
    }

    /******************************************************
     *
     * following: methods that the wrapper calls
     *
     ******************************************************/

    /**
     * @return boolean true iff entry has been added
     */
    protected function _addDiaryEntry() {
        // check minimum condition for successful formular submission
        if (empty($_POST[F_BLOG_CONTENT_RAW])) {
            self::addIncompleteField($this->errors, F_BLOG_CONTENT_RAW, ERR_DIARY_NO_TEXT);
        }
        if (strlen($_POST[F_BLOG_CONTENT_RAW]) > V_ENTRY_MAX_CHARS) {
            self::addIncompleteField($this->errors, F_BLOG_CONTENT_RAW, ERR_ENTRY_TOO_LONG);
        }
        
        $_POST[F_BLOG_CONTENT_RAW] = InputValidator::requireEncoding($_POST[F_BLOG_CONTENT_RAW]);

        // try to fetch entry from session (needed in preview mode)
        if (!($entry = Session::getInstance()->getEntryDataChecked('diary', null))) {
        	// instanciate new diary entry with POSTed data
            $entry = new DiaryEntry($_POST[F_BLOG_CONTENT_RAW], $this->visitingUserModel, self::getParseSettings());
        } else {
        	// replace existing content
        	$entry->setContentRaw($_POST[F_BLOG_CONTENT_RAW]);
            // renew parse settings
            $entry->setParseSettings(self::getParseSettings());
        }

        // handle attachment(s) additions and removements
        if (!$this->handleAttachment($entry, $this->userModel, $this->maxAttachmentSize)) {
        	self::addIncompleteField($this->errors, 'file_attachment1', ERR_ATTACHMENT);
        }

        // we need no parsing here, because setContentRaw cleared
        // the pre-parsed string, so that at next access
        // automatic parsing will be triggered

        // distinguish between preview and non-preview mode
        if (!($this->previewMode or $this->uploadMode) and count($this->errors) == 0) {
            if (Session::getInstance()->removeRandomId($this->entryRandId)) {
                // add entry
                $entry->save();
            }
            
            // remove entry from session, we don't need it any more
            Session::getInstance()->deleteEntryData('diary');
            
            $this->entryRandId = Session::getInstance()->generateNewRandomId();

            return true;
        } else {
            $this->editEntryModel = $entry;
            // store entry in session, we need it next time
            Session::getInstance()->storeEntryData('diary', $entry);

            return false;
        }

    }

    /**
     * @return boolean
     */
    protected function _prepareEditDiaryEntry($admin) {
        // check minimum condition for successful formular submission
        if (!array_key_exists('diaryid', $_REQUEST)) {
            return false;
        }

        $this->editEntryModel = $this->diaryModel->getEntryById($_REQUEST['diaryid'], $this->visitingUserModel, !$admin);
        Session::getInstance()->storeEntryData('diary', $this->editEntryModel);

        return $this->editEntryModel != null;
    }

    /**
     * @return boolean
     */
    protected function _editDiaryEntry() {
        if (!array_key_exists('diaryid', $_REQUEST)) {
            return false;
        }

        // check minimum condition for successful formular submission
        if (empty($_POST[F_BLOG_CONTENT_RAW])) {
            self::addIncompleteField($this->errors, F_BLOG_CONTENT_RAW, ERR_DIARY_NO_TEXT);
        }
        if (strlen($_POST[F_BLOG_CONTENT_RAW]) > V_ENTRY_MAX_CHARS) {
            self::addIncompleteField($this->errors, F_BLOG_CONTENT_RAW, ERR_ENTRY_TOO_LONG);
        }

        // fetch stored diary entry to be edited from session
        $this->editEntryModel = Session::getInstance()->getEntryDataChecked('diary', $_REQUEST['diaryid']);
        // ensure, that entry exists
        if (!$this->editEntryModel) {
            return false;
        }

        // update content of entry and parse it again
        $this->editEntryModel->setContentRaw($_POST[F_BLOG_CONTENT_RAW]);

        // determine, if an "last edited"-string is to be added
        if (!array_key_exists(F_ENABLE_UPDATE_NOTICE, $_POST) and $this->visitingUserModel->hasRight('BLOG_ENTRY_EDIT_WITHOUT_NOTICE')) {
            $showUpdateNotice = false;
        } else {
            $showUpdateNotice = true;
        }

        // handle attachment(s) additions and removements
        if (!$this->handleAttachment($this->editEntryModel, $this->userModel, $this->maxAttachmentSize)) {
        	self::addIncompleteField($this->errors, 'file_attachment1', ERR_ATTACHMENT);
        }
        $this->editEntryModel->setParseSettings(self::getParseSettings());
        $this->editEntryModel->parse($showUpdateNotice);

        //VAR
        //var_dump($this->editEntryModel);

        // distinguish between preview and non-preview mode
        if (!($this->previewMode or $this->uploadMode) and count($this->errors) == 0) {
        	// edit entry
            $this->editEntryModel->save();
            $this->editEntryModel = null;

            // remove entry from session, we don't need it any more
            Session::getInstance()->deleteEntryData('diary');
            return true;
        }
        return false;
    }

    /**
     * @return boolean
     */
    protected function _deleteDiaryEntry($admin) {
        // check minimum condition for successful formular submission
        if (!array_key_exists('diaryid', $_REQUEST)) {
            return false;
        }

        // delete the entry, iff entry belongs to diary model owner
        $this->diaryModel->deleteEntryById($_REQUEST['diaryid'], !$admin);

        return true;
    }

    /**
     * @return boolean
     */
    protected function _addGBEntry() {
        // check minimum condition for successful formular submission
        if ($_POST[F_GUESTBOOK_WEIGHTING] != -1 and
            $_POST[F_GUESTBOOK_WEIGHTING] !=  0 and
            $_POST[F_GUESTBOOK_WEIGHTING] !=  1) {
            Logging::getInstance()->logSecurity('invalid range for user guestbook weighting', false);
            return false;
        }

        if (strlen($_POST[F_GUESTBOOK_CONTENT_RAW]) > V_ENTRY_MAX_CHARS) {
            self::addIncompleteField($this->errors, F_GUESTBOOK_CONTENT_RAW, ERR_ENTRY_TOO_LONG);
        }
        if (empty($_POST[F_GUESTBOOK_CONTENT_RAW])) {
            self::addIncompleteField($this->errors, F_GUESTBOOK_CONTENT_RAW, ERR_GUESTBOOK_NO_TEXT);
        }
        
        $_POST[F_GUESTBOOK_CONTENT_RAW] = InputValidator::requireEncoding($_POST[F_GUESTBOOK_CONTENT_RAW]);

        // if user may not give points for guestbook entry
        // set weighting to zero
        if (!$this->mayGiveGuestbookPoint()) {
        	$_POST[F_GUESTBOOK_WEIGHTING] = 0;
        }

        // try to fetch entry from session (needed in preview mode)
        if (!($entry = Session::getInstance()->getEntryDataChecked('guestbook', null)) or
                !($entry->getRecipient()->equals($this->userModel))) {
            // instanciate new guestbook entry with POSTed data
            $entry = new GuestbookEntry($_POST[F_GUESTBOOK_CONTENT_RAW], $this->visitingUserModel, $this->userModel, self::getParseSettings());
        } else {
            // replace existing content
            $entry->setContentRaw($_POST[F_GUESTBOOK_CONTENT_RAW]);
            // renew parse settings
            $entry->setParseSettings(self::getParseSettings());
        }

        // handle attachment(s) additions and removements
        if (!$this->handleAttachment($entry, $this->userModel, $this->maxAttachmentSize)) {
            self::addIncompleteField($this->errors, 'file_attachment1', ERR_ATTACHMENT);
        }

        // we need no parsing here, because setContentRaw cleared
        // the pre-parsed string, so that at next access
        // automatic parsing will be triggered

        // determine value/points of guestbook entry
        $value = $_POST[F_GUESTBOOK_WEIGHTING];
        // check, if admin points are available
        if ($this->visitingUserModel->hasRight('GB_ENTRY_GIVE_MULTIPLE_POINTS')) {
            $value += $_POST[F_GUESTBOOK_WEIGHTING_ADDITIONAL];
        }
        $entry->setWeighting($value);

        // distinguish between preview and non-preview mode
        if (!($this->previewMode or $this->uploadMode) and count($this->errors) == 0) {
            if (Session::getInstance()->removeRandomId($this->entryRandId)) {        
                // open transaction, because we update entry and points
                $DB = Database::getHandle();
                $DB->StartTrans();
    
                // update user's points
                $psNormal = PointSourceModel::getPointSourceByName('GB_ENTRY');
                $psAdmin = PointSourceModel::getPointSourceByName('GB_ENTRY_ADMIN');
    
                $pointsNormal = $_POST[F_GUESTBOOK_WEIGHTING];
                $pointsAdmin = 0;
                if (array_key_exists(F_GUESTBOOK_WEIGHTING_ADDITIONAL, $_POST)) {
                    $pointsAdmin = $_POST[F_GUESTBOOK_WEIGHTING_ADDITIONAL];
                }
    
                $pointsSumDelta = $pointsNormal * $psNormal->getPointsSum() +
                    $pointsAdmin * $psAdmin->getPointsSum();
                $pointsFlowDelta = $pointsNormal * $psNormal->getPointsFlow() +
                    $pointsAdmin * $psAdmin->getPointsFlow();
    
                $this->userModel->increaseUnihelpPoints($pointsSumDelta,
                                                        $pointsFlowDelta);
                $this->userModel->save();
                // add entry
                $entry->save();
    
                $DB->CompleteTrans();
    
                $userIPC = new UserIPC($this->userModel->id);
                $userIPC->setFlag('GB_ENTRIES_CHANGED');
                
                if ($value != 0) {
                    self::notifyIPC($userIPC, 'POINTS_CHANGED');
                }
            }
            
            $this->entryRandId = Session::getInstance()->generateNewRandomId();
            
            // remove entry from session, we don't need it any more
            Session::getInstance()->deleteEntryData('guestbook');
            return true;
        } else {
            $this->editEntryModel = $entry;
            // store entry in session, we need it next time
            Session::getInstance()->storeEntryData('guestbook', $entry);
            return false;
        }
    }

    /**
     * @param boolean $admin true, iff editing user may perform administrative tasks
     * @return boolean
     */
    protected function _prepareEditGBEntry($admin) {
        // check minimum condition for successful formular submission
        if (!array_key_exists('gbid', $_REQUEST)) {
            return false;
        }

        // if in admin mode, allow edit of others' guestbook entries
        $this->editEntryModel = $this->guestbookModel->getEntryById($_REQUEST['gbid'], $this->visitingUserModel, !$admin);
        Session::getInstance()->storeEntryData('guestbook', $this->editEntryModel);

        return $this->editEntryModel != null;
    }

    protected function _editGBEntry() {
        // check minimum condition for successful formular submission
        if (!array_key_exists('gbid', $_REQUEST)) {
            return false;
        }

        if (strlen($_POST[F_GUESTBOOK_CONTENT_RAW]) > V_ENTRY_MAX_CHARS) {
            self::addIncompleteField($this->errors, F_GUESTBOOK_CONTENT_RAW, ERR_ENTRY_TOO_LONG);
        }
        if (empty($_POST[F_GUESTBOOK_CONTENT_RAW])) {
            self::addIncompleteField($this->errors, F_GUESTBOOK_CONTENT_RAW, ERR_GUESTBOOK_NO_TEXT);
        }


        // fetch stored guestbook entry to be edited from session
        $this->editEntryModel = Session::getInstance()->getEntryDataChecked('guestbook', $_REQUEST['gbid']);
        // ensure, that entry exists
        if (!$this->editEntryModel) {
            return false;
        }

        // update content of entry and parse it again
        $this->editEntryModel->setContentRaw($_POST[F_GUESTBOOK_CONTENT_RAW]);
        // determine, if an "last edited"-string is to be added
        if (!array_key_exists(F_ENABLE_UPDATE_NOTICE,$_POST) and $this->visitingUserModel->hasRight('GB_ENTRY_EDIT_WITHOUT_NOTICE')) {
            $showUpdateNotice = false;
        } else {
            $showUpdateNotice = true;
        }

        // handle attachment(s) additions and removements
        if (!$this->handleAttachment($this->editEntryModel, $this->userModel, $this->maxAttachmentSize)) {
        	self::addIncompleteField($this->errors, 'file_attachment1', ERR_ATTACHMENT);
        }

        $this->editEntryModel->setParseSettings(self::getParseSettings());
        $this->editEntryModel->parse($showUpdateNotice);

        // var_dump($this->editEntryModel);
        // distinguish between preview and non-preview mode
        if (!($this->previewMode or $this->uploadMode) and count($this->errors) == 0) {
        	// edit entry
            $this->editEntryModel->save();
            $this->editEntryModel = null;

            // remove entry from session, we don't need it any more
            Session::getInstance()->deleteEntryData('guestbook');
            return true;
        }

        return false;
    }

    /**
      * @return boolean
      */
    protected function _quoteGBEntry() {
        // check minimum condition for successful formular submission
        if (!array_key_exists('gbid', $_REQUEST)) {
            return false;
        }
        $visitorGuestbookModel = new GuestbookModel($this->visitingUserModel);
        $quoteEntry = $visitorGuestbookModel->getEntryById($_REQUEST['gbid'], $this->userModel, true);

        // create brand new gb entry model
        $this->editEntryModel = new GuestbookEntry($quoteEntry->getQuote());
        //$this->editEntryModel->quoteWithUser($this->userModel);
        $this->editEntryModel->setParseSettings(array(F_ENABLE_FORMATCODE => true,
                                                      F_ENABLE_SMILEYS => true));
        // must delete edit entry model
        // otherwise there will be conflicts on entry preview
        Session::getInstance()->deleteEntryData('guestbook');

        return $this->editEntryModel != null;
    }

    /**
      * @return boolean
      */
    protected function _prepareCommentGBEntry() {
        // check minimum condition for successful formular submission
        if (!array_key_exists('gbid', $_REQUEST)) {
            return false;
        }

        $this->editEntryModel = $this->guestbookModel->getEntryById($_REQUEST['gbid'], $this->visitingUserModel, false);
        if (!$this->editEntryModel->getRecipient()->equals($this->visitingUserModel)) {
        	$this->editEntryModel = null;
        }

        Session::getInstance()->storeEntryData('guestbook', $this->editEntryModel);

        return $this->editEntryModel != null;
    }

    /**
      * @return boolean
      */
    protected function _commentGBEntry() {
        // check minimum condition for successful formular submission
        if (!array_key_exists('gbid', $_POST)) {
            return false;
        }

        // fetch stored guestbook entry to be edited from session
        $this->editEntryModel = Session::getInstance()->getEntryDataChecked('guestbook', $_POST['gbid']);
        // ensure, that entry exists
        if (!$this->editEntryModel) {
            return false;
        }

        if (empty($_POST[F_GUESTBOOK_CONTENT_RAW])) {
            $this->editEntryModel->removeComment();
        } else {
            $_POST[F_GUESTBOOK_CONTENT_RAW] = InputValidator::requireEncoding($_POST[F_GUESTBOOK_CONTENT_RAW]);
            
            // escape comment
            // do not use SimpleParser here in order to avoid multi-line comments
            $comment = htmlspecialchars(substr($_POST[F_GUESTBOOK_CONTENT_RAW], 0, V_GB_COMMENT_MAX_CHARS));

            // update content of entry and parse it again
            $this->editEntryModel->setComment($comment);
        }
        // TODO: avoid parsing with unknown settings / overriding previous "edit_with_notice" sessions
        $this->editEntryModel->parse(false);

        $this->editEntryModel->save();
        Session::getInstance()->deleteEntryData('guestbook');

        return true;
    }

    /**
     * @param boolean $admin
     * @return boolean
     */
    protected function _deleteGBEntry($admin) {
        // check minimum condition for successful formular submission
        if (!array_key_exists('gbid', $_REQUEST)) {
            return false;
        }

        // delete the entry, iff entry belongs to diary model owner
        // or user has administrative privileges
        $this->guestbookModel->deleteEntryById($_REQUEST['gbid'], $admin);

        return true;
    }

    /**
     * @return boolean
     */
    protected function _addFriend() {
        // instanciate new friend
        $friend = new FriendModel($this->visitingUserModel, $this->userModel);

        // add friend to friendlist
        $friend->addToFriendlist();

        return true;
    }

    /**
     * @return boolean
     */
    protected function _removeFriend() {
        // instanciate old friend
        $friend = new FriendModel($this->visitingUserModel, $this->userModel);

        // remove friend from friendlist
        $friend->removeFromFriendlist();

        return true;
    }
    
    protected function getGuestbookEntryCachekey($gid) {
        return 'userinfo|' . $this->userModel->getCachekey() . '|guestbook|entry|' . $gid;
    }
    protected function getDiaryEntryCachekey($did) {
        return 'userinfo|' . $this->userModel->getCachekey() . '|diary|entry|' . $did;
    }
    protected function getGuestbookPageCachekey($available, $page = null) {
        $str = 'userinfo|' . $this->userModel->getCachekey() . '|guestbook|' . ($available ? 'av' : 'notav');
        if ($page != null) {
            $str .= '|' . $page;
        }
        return $str;
    }
    protected function getDiaryPageCachekey($available, $page = null) {
        $str = 'userinfo|' . $this->userModel->getCachekey() . '|diary|' . ($available ? 'av' : 'notav');
        if ($page != null) {
            $str .= '|' . $page;
        }
        return $str;
    }
    
    protected function getDiaryEntryView($id, $enableCaching, $markEntry = false) {
        $entryView = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_diary_entry.tpl');
        
        if ($enableCaching && !$markEntry) {
            $entryView->enableCaching();
        }
        $cacheKey = $this->getDiaryEntryCachekey($id); 
        $entryView->setCacheParameter(-1, $cacheKey);
        
        $authorId = null;
        if (!$entryView->isCached()) {
            $entry = DiaryEntry::getEntryById($id);
            $entryView->assign('diaryentry', $entry);
        }
        
        $entryView->assign('diaryentryId', $id);
        $entryView->assign('mark_diary_entry', $markEntry);
        $entryView->assign('visitor', Session::getInstance()->getVisitor());
        return $entryView;
    }
    
    protected function getGuestbookEntryView($id, $enableCaching, $markEntry = false) {
        $entryView = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_guestbook_entry.tpl');
        
        if ($enableCaching && !$markEntry) {
            $entryView->enableCaching();
        }
        $cacheKey = $this->getGuestbookEntryCachekey($id); 
        $entryView->setCacheParameter(-1, $cacheKey);

        
        $authorId = null;
        if (!$entryView->isCached() or !self::getCachedIds($cacheKey, $authorId)) {
            $entry = GuestbookEntry::getEntryById($id);
            if ($entry->getAuthor() == null) {
                $entry->setAuthor(new UserAnonymousModel);
            }

            if (!$this->isExternalView and $entry->isUnread()) {
                GuestbookEntry::setReadButDontDisplay(array($id));
            }
            
            $author = $entry->getAuthor();
            $entryView->assign('guestbookentry', $entry);
            // mark external user ids with a trailing 'e'
            self::cacheIds($cacheKey, array($author->id . ($author->isExternal() ? 'e' : '')), true);
        } else {
            $author = $authorId[0];
        }
        
        $entryView->assign('guestbookentryAuthor', $author);
        $entryView->assign('guestbookentryId', $id);
        $entryView->assign('mark_guestbook_entry', $markEntry);
        $entryView->assign('visitor', Session::getInstance()->getVisitor());
        
        return $entryView;
    }

    protected function getGuestbookView($permissions, $isFiltered, $filterErrors) {
        $gbEntries = null;
        
        $guestbookAvailable = ($this->userModel->isGBpublic() || $this->visitingUserModel->isLoggedIn()) && 
            $this->userVisitorRelationship != FriendModel::IS_FOE;
        
        // check, if we have to highlight a certain entry
        $markGuestbookId = 0;
        if (array_key_exists('linkgbid', $_REQUEST)) {
            // determine page the specified entry is on
            // if entry is invalid, first page will be shown (cf. implementation of getEntriesAfterEntryId)
            $entries = $this->guestbookModel->getEntriesAfterEntryId($_REQUEST['linkgbid']);
            $this->elementPages['guestbook'] = floor($entries / $this->entriesPerPage['guestbook']) + V_GUESTBOOK_START_PAGE;
            $markGuestbookId = $_REQUEST['linkgbid'];
        }
        
        // we always want per-entry based cache
        $wantEntryCache = true;

        // if user visits his own page, handle unread guestbook entries
        if (!$this->isExternalView and $this->visitingUserModel->getGBEntriesUnread() > 0) {
            $DB = Database::getHandle();

            // start transaction here to get a correct number of unread entries
            $DB->StartTrans();

            // prefetch guestbook entries; need to mark them as read
            /*$gbEntries = $this->guestbookModel->getAllParsedEntries( $this->entriesPerPage['guestbook'],
                        $this->entriesPerPage['guestbook'] * ($this->elementPages['guestbook']-1),
                        'desc',
                        !$this->visitingUserModel->hasRight('GB_ENTRY_ADMIN') );*/
            $gbEntries = $this->guestbookModel->getEntryIds( $this->entriesPerPage['guestbook'],
                        $this->entriesPerPage['guestbook'] * ($this->elementPages['guestbook']-1),
                        'desc');

            // adjust user's unread entry counter
            // is done in database
            //$this->visitingUserModel->setReadGBEntries($unread);
            //$this->visitingUserModel->save();
            
            // force reload of gb entries number next time
            $userIPC = new UserIPC($this->visitingUserModel->id);
            $userIPC->setFlag('GB_ENTRIES_CHANGED');

            $DB->CompleteTrans();
            
            $wantEntryCache = false;
        }
                
        $guestbookView = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_guestbook.tpl');
        
        // enable caching only, if we do not have to filter
        if (defined('CACHETEST') and !$isFiltered) {
            $guestbookView->enableCaching();
        }
        
        $cacheKey = $this->getGuestbookPageCachekey($guestbookAvailable, $this->elementPages['guestbook']);
        $guestbookView->setCacheParameter(-1, $cacheKey);

        self::observeIPC(
            new UserIPC($this->userModel->id), 
            array('GUESTBOOK_CHANGED'),
            $guestbookView, $this->getGuestbookPageCachekey($guestbookAvailable));

        if ($guestbookAvailable) {
            $gbEntriesView = array();
            if (!$guestbookView->isCached() or !self::getCachedIds($cacheKey, $gbEntries)) {
                if ($gbEntries == null) {
                    /*$gbEntries = $this->guestbookModel->getAllParsedEntries( $this->entriesPerPage['guestbook'],
                                $this->entriesPerPage['guestbook'] * ($this->elementPages['guestbook']-1),
                                'desc',
                                !$this->visitingUserModel->hasRight('GB_ENTRY_ADMIN') );*/
                    $gbEntries = $this->guestbookModel->getEntryIds( $this->entriesPerPage['guestbook'],
                                $this->entriesPerPage['guestbook'] * ($this->elementPages['guestbook']-1),
                                'desc');
                }
                $guestbookView->assign('userinfo_guestbookcounter', $this->guestbookModel->getCounter($this->elementPages['guestbook']));
                self::cacheIds($cacheKey, $gbEntries);
            }
            foreach ($gbEntries as $gid) {
                if ($gid == '') {
                    continue;
                }
                $gbEntriesView[] = $this->getGuestbookEntryView($gid, $wantEntryCache, $markGuestbookId == $gid);
                foreach ($gbEntriesView as &$ev) {
                    $ev->assign('userinfo_permissions', $permissions);
                }
            }
            $guestbookView->assign('userinfo_guestbook', $gbEntriesView);
            
            if ($this->visitingUserModel->isGBFilterShow()) {
                $guestbookView->assign('userinfo_guestbookfilters', Session::getInstance()->getUserData('guestbook_filter'));
                $guestbookView->assign('userinfo_guestbookfilters_show', true);
            }
            $guestbookView->assign('userinfo_guestbookpage', $this->elementPages['guestbook']);
            $guestbookView->assign('userinfo_user', $this->userModel);       
            $guestbookView->assign('userinfo_permissions', $permissions);
            $guestbookView->assign('userinfo_editentry', $this->editEntryModel);
            $guestbookView->assign('userinfo_is_external_view', $this->isExternalView);
            $guestbookView->assign('userinfo_filtererrors', $filterErrors);
            $guestbookView->assign('guestbook_available', true);
            
            $guestbookView->assign('visitor', $this->visitingUserModel);
            $guestbookView->assign('central_errors', $this->errors);
        } else {
            $guestbookView->assign('guestbook_available', false);
        }
        
        return $guestbookView;
    }

    protected function getDiaryView($permissions, $isFiltered, $filterErrors) {
        $diaryView = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_diary.tpl');
        
        $diaryAvailable = ($this->userModel->isDiarypublic() || $this->visitingUserModel->isLoggedIn()) && 
            $this->userVisitorRelationship != FriendModel::IS_FOE;
        
        // check, if we have to highlight a certain entry
        $markDiaryId = 0;
        if (array_key_exists('linkdiaryid', $_REQUEST)) {
            // determine page the specified entry is on
            // if entry is invalid, first page will be shown (cf. implementation of getEntriesAfterEntryId)
            $entries = $this->diaryModel->getEntriesAfterEntryId($_REQUEST['linkdiaryid']);
            $this->elementPages['diary'] = floor($entries / $this->entriesPerPage['diary']) + V_BLOG_START_PAGE;
            $markDiaryId = $_REQUEST['linkdiaryid'];
        }
        
        if (defined('CACHETEST') and !$isFiltered) {
            $diaryView->enableCaching();
        }
        
        /*$basicCacheKey = 'userinfo|' . $this->userModel->getCachekey() . '|diary';
        $cacheKey = $basicCacheKey . '|' . $this->elementPages['diary'];*/
        $cacheKey = $this->getDiaryPageCachekey($diaryAvailable, $this->elementPages['diary']);
        $diaryView->setCacheParameter(-1, $cacheKey);
        
        self::observeIPC(
            new UserIPC($this->userModel->id), 
            array('DIARY_CHANGED'),
            $diaryView, $this->getDiaryPageCachekey($diaryAvailable));
        
        if($diaryAvailable){
            $diaryEntriesView = array();
            $diaryEntries = null;
            if (!$diaryView->isCached() or !self::getCachedIds($cacheKey, $diaryEntries)) {
                if ($diaryEntries == null) {
                    $diaryEntries = $this->diaryModel->getEntryIds( $this->entriesPerPage['diary'],
                        $this->entriesPerPage['diary'] * ($this->elementPages['diary']-1) );
                }
                $diaryView->assign('userinfo_diarycounter', $this->diaryModel->getCounter($this->elementPages['diary']));
                self::cacheIds($cacheKey, $diaryEntries);
            }
            foreach ($diaryEntries as $did) {
                if ($did == '') {
                    continue;
                }
                $diaryEntriesView[] = $this->getDiaryEntryView($did, true, $markDiaryId == $did);
                foreach ($diaryEntriesView as &$ev) {
                    $ev->assign('userinfo_permissions', $permissions);
                }
            }
            $diaryView->assign('userinfo_diary', $diaryEntriesView);
           
            // dynamic variables
            
            if ($this->visitingUserModel->isDiaryFilterShow()) {
                $diaryView->assign('userinfo_diaryfilters', Session::getInstance()->getUserData('diary_filter'));
                $diaryView->assign('userinfo_diaryfilters_show', true);
            }
            $diaryView->assign('userinfo_diarypage', $this->elementPages['diary']);
            $diaryView->assign('userinfo_user', $this->userModel);       
            $diaryView->assign('userinfo_permissions', $permissions);
            $diaryView->assign('userinfo_editentry', $this->editEntryModel);
            $diaryView->assign('userinfo_filtererrors', $filterErrors);
            $diaryView->assign('diary_available', true);
            
            $diaryView->assign('visitor', $this->visitingUserModel);
            $diaryView->assign('central_errors', $this->errors);
        }
        else{
        	$diaryView->assign('diary_available', false);
        }
        
        return $diaryView;
    }
    /**
	 * default view method
	 */
	protected function defaultView() {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'user_info.tpl');
        
        // determine filter settings
        $filterErrors = array();
        $filtered = $this->determineFilters($filterErrors);
        
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        $basicCacheKey = 'userinfo|' . $this->userModel->getCachekey() . '|boxinfo';
        $cacheKey = $basicCacheKey . '|' . PrivacyContext::getContext()->getLevel()->id;

        self::observeIPC(new UserIPC($this->userModel->id), 
            array('FRIENDLIST_CHANGED', 'GROUPS_CHANGED', 'POINTS_CHANGED', 'PROFILE_CHANGED', 'PRIVACY_CHANGED'),
            $main, 
            $basicCacheKey);
        // timeout of half an hour seems reasonable
        // the period has to be quite short because
        // the user's online status is displayed
        //         (linap, 24.05.2007)
        $main->setCacheParameter(1800, $cacheKey);


        // things. we have to keep dynamic
        // - entryfield
        // - userinfo_onignorelist
        // - right-based entry buttons
        // - filters
        // - tabpane
        // - preview
        // - permissions
        
        // gather relevant permissions, such as editing|quoting|deleting
        $permissions = $this->determinePermissions();
        $main->assign('userinfo_permissions', $permissions);
        
        $main->assign('userinfo_diary_view', $this->getDiaryView($permissions, $filtered['diary'], $filterErrors));
        $main->assign('userinfo_gb_view', $this->getGuestbookView($permissions, $filtered['guestbook'], $filterErrors));
        
        
        // dynamic variables here
        $main->assign('randid', $this->entryRandId);
        $main->assign('userinfo_user', $this->userModel);
        
        $main->assign('userinfo_onignorelist', $this->userVisitorRelationship == FriendModel::IS_FOE);
        $main->assign('userinfo_is_external_view', $this->isExternalView);
        
        // decide, whether diary, guestbook entry or none of these may be posted
        $main->assign('userinfo_entrymode', $this->determineEntryMode());
        // provide information, whether we are in ADD or EDIT state
        $main->assign('userinfo_state', $this->currentState);


        $main->assign('userinfo_editentry', $this->editEntryModel);
        $main->assign('isGuestbook', $this->editEntryModel instanceof GuestbookEntry);

        $main->assign('userinfo_tabpane', $this->getTabPane());
        $main->assign('userinfo_tabpanemode', $_REQUEST['tabpane']);        

        

        // increment profile view counter
        $visitUserInfoPages = Session::getInstance()->getUserData('visitUserInfoPages');
        if(!$visitUserInfoPages){
        	$visitUserInfoPages = array();
        }      
        if(!array_key_exists($this->userModel->id,$visitUserInfoPages)){
            $this->userModel->incrementProfileViews();
            $this->userModel->save();
            $visitUserInfoPages[$this->userModel->id] = time();
        }
        $newVisits = array();  
        foreach($visitUserInfoPages as $key => $visitTime){
            if($visitTime > time() - V_USER_INFO_VISIT_LIFETIME){
                $newVisits[$key] = $visitTime;
            }
        }
        Session::getInstance()->storeUserData('visitUserInfoPages',$newVisits);
                
        $main->assign('isPreview', $this->previewMode);
        
        // provide information about maximal allowed attachment size
        // one time in bytes and one time in kilobytes
        $main->assign('userinfo_maxattachmentsize', $this->maxAttachmentSize);
        $main->assign('userinfo_maxattachmentsize_kb', $this->maxAttachmentSize/1024);
        //var_dump("main", $main->isCached());
        // rely on smarty to set !isCached on filters and any other conditions
        // given above to disable caching
        if (!$main->isCached()) {
            $main->assign('userinfo_friends', FriendModel::getFriendsByUser($this->userModel));
            // due to asymmetry of unihelp friendlist, we need two variables here;
            // the first one indicates that $visitor has $user on his/her friendlist
            $main->assign('userinfo_is_friend', FriendModel::isFriendOf($this->visitingUserModel, $this->userModel));
        }
        
        
        // we don't need that at the moment
        //$main->assign('userinfo_aboForum', ForumModel::getAboForumsByUser($this->userModel));

        $this->setCentralView($main);

        $this->view();
	}

	/******************************************************
	 *
	 * helper methods
	 *
	 ******************************************************/

    /**
     * determines and applies filter settings to guestbook and diary
     * if specified and available
     */
	protected function determineFilters(&$filterErrors) {
		$gbFilterOptions = array();
        $diaryFilterOptions = array();

        $hasFilter = array('guestbook' => false, 'diary' => false);
        
        // set user id of profile to filter at
        $gbFilterOptions[BaseFilter::FILTER_AT]   = $this->userModel->id;
        $diaryFilterOptions[BaseFilter::FILTER_AT] = $this->userModel->id;

        // check, if we have to clear filter settings
        if (isset($_POST['gb_submittype_reset'])) {
            Session::getInstance()->deleteUserData('guestbook_filter');
            return $hasFilter;
        }
        if (isset($_POST['diary_submittype_reset'])) {
            Session::getInstance()->deleteUserData('diary_filter');
            return $hasFilter;
        }

        /*************
         * GUESTBOOK
         *************/
        if (!empty($_POST['gbfilterauthor'])) {
            $gbFilterOptions[BaseFilter::FILTER_AUTHOR] = array();
            // ignore multiple whitespace between author names on split
            $authors = preg_split('/\s+/', trim($_POST['gbfilterauthor']));

            $filterAuthor = null;
            foreach ($authors as $author) {
                // check for external user
                if (($pos = strpos($author, '@')) !== false) {
                    // for external users: determine city and username
                    $city = CityModel::getCityByName(substr($author,$pos+1));
                    if ($city != null) {
                       $filterAuthor = UserExternalModel::getUserByUsername(substr($author,0,$pos), $city);
                    }
                } else {
                    $filterAuthor = UserProtectedModel::getUserByUsername($author);
                }

                if ($filterAuthor != null) {
                   array_push($gbFilterOptions[BaseFilter::FILTER_AUTHOR], $filterAuthor);
                } else {
                    $filterErrors['gbfilterauthor'] = true;
                }
            }
        } else if (array_key_exists('gbfilterauthor', $_POST)) {
            // author is empty, so we can remove filtering by it
            $gbFilterOptions[BaseFilter::FILTER_AUTHOR] = array();
        }

        if (array_key_exists('gbfilterdatefrom_Year', $_POST) and $_POST['gbfilterdatefrom_Year'] != '') {
            $year = $_POST['gbfilterdatefrom_Year'];
            $month = ($_POST['gbfilterdatefrom_Month'] != '') ? $_POST['gbfilterdatefrom_Month'] : '01';
            $day = ($_POST['gbfilterdatefrom_Day'] != '') ? $_POST['gbfilterdatefrom_Day'] : '01';
            $date = $year . '-' . $month . '-' . $day;
            if (!array_key_exists(BaseFilter::FILTER_ENTRYDATE, $gbFilterOptions) or
                    $gbFilterOptions[BaseFilter::FILTER_ENTRYDATE] == null) {
                $gbFilterOptions[BaseFilter::FILTER_ENTRYDATE] = array();
            }
            $gbFilterOptions[BaseFilter::FILTER_ENTRYDATE]['from'] = $date;
        }
        if (array_key_exists('gbfilterdateto_Year', $_POST) and $_POST['gbfilterdateto_Year'] != '') {
            $year = $_POST['gbfilterdateto_Year'];
            $month = ($_POST['gbfilterdateto_Month'] != '') ? $_POST['gbfilterdateto_Month'] : '01';
            $day = ($_POST['gbfilterdateto_Day'] != '') ? $_POST['gbfilterdateto_Day'] : '01';
            $date = $year . '-' . $month . '-' . $day;
            if (!array_key_exists(BaseFilter::FILTER_ENTRYDATE, $gbFilterOptions) or
                    $gbFilterOptions[BaseFilter::FILTER_ENTRYDATE] == null) {
                $gbFilterOptions[BaseFilter::FILTER_ENTRYDATE] = array();
            }
            $gbFilterOptions[BaseFilter::FILTER_ENTRYDATE]['to'] = $date;
        }

        // check session for filter options
        if (count($gbFilterOptions) <= 1 and ($tmp = Session::getInstance()->getUserData('guestbook_filter'))) {
        	// if filter settings apply for this site, take them
            if ($tmp[BaseFilter::FILTER_AT] == $this->userModel->id) {
                $gbFilterOptions = $tmp;
            }
            // otherwise discard them
            else {
            	Session::getInstance()->deleteUserData('guestbook_filter');
            }
        } else if (count($gbFilterOptions) > 1) {
        	Session::getInstance()->storeUserData('guestbook_filter', $gbFilterOptions);
        }

        // one entry (FILTER_AT) always exists
        if (count($gbFilterOptions) > 1) {
        	if ($this->visitingUserModel->hasRight('GB_ENTRY_ADMIN')) {
                $this->guestbookModel->setFilter(GuestbookModel::getFilterClass($gbFilterOptions));
                $hasFilter['guestbook'] = true;
            } else if ($this->visitingUserModel->hasRight('GB_FILTER')) {
                if ($this->visitingUserModel->equals($this->userModel)) {
                    $this->guestbookModel->setFilter(GuestbookModel::getFilterClass($gbFilterOptions));
                    $hasFilter['guestbook'] = true;
                }
            } else {
            	$this->rightsMissingView('GB_FILTER');
            }
        }

        /*************
         * BLOG
         *************/

        if (array_key_exists('diaryfilterdatefrom_Year', $_POST) and $_POST['diaryfilterdatefrom_Year'] != '') {
            $year = $_POST['diaryfilterdatefrom_Year'];
            $month = ($_POST['diaryfilterdatefrom_Month'] != '') ? $_POST['diaryfilterdatefrom_Month'] : '01';
            $day = ($_POST['diaryfilterdatefrom_Day'] != '') ? $_POST['diaryfilterdatefrom_Day'] : '01';
            $date = $year . '-' . $month . '-' . $day;
            if (!array_key_exists(BaseFilter::FILTER_ENTRYDATE, $diaryFilterOptions) or
                    $diaryFilterOptions[BaseFilter::FILTER_ENTRYDATE] == null) {
                $diaryFilterOptions[BaseFilter::FILTER_ENTRYDATE] = array();
            }
            $diaryFilterOptions[BaseFilter::FILTER_ENTRYDATE]['from'] = $date;
        }
        if (array_key_exists('diaryfilterdateto_Year', $_POST) and $_POST['diaryfilterdateto_Year'] != '') {
            $year = $_POST['diaryfilterdateto_Year'];
            $month = ($_POST['diaryfilterdateto_Month'] != '') ? $_POST['diaryfilterdateto_Month'] : '01';
            $day = ($_POST['diaryfilterdateto_Day'] != '') ? $_POST['diaryfilterdateto_Day'] : '01';
            $date = $year . '-' . $month . '-' . $day;
            if (!array_key_exists(BaseFilter::FILTER_ENTRYDATE, $diaryFilterOptions) or
                    $diaryFilterOptions[BaseFilter::FILTER_ENTRYDATE] == null) {
                $diaryFilterOptions[BaseFilter::FILTER_ENTRYDATE] = array();
            }
            $diaryFilterOptions[BaseFilter::FILTER_ENTRYDATE]['to'] = $date;
        }

        // check session for filter options
        if (count($diaryFilterOptions) <= 1 and ($tmp = Session::getInstance()->getUserData('diary_filter'))) {
            // if filter settings apply for this site, take them
            if ($tmp[BaseFilter::FILTER_AT] == $this->userModel->id) {
                $diaryFilterOptions = $tmp;
            }
            // otherwise discard them
            else {
            	Session::getInstance()->deleteUserData('diary_filter');
            }
        } else if (count($diaryFilterOptions) > 1) {
            Session::getInstance()->storeUserData('diary_filter', array_merge(array('for_user' => $this->userModel->getUsername()), $diaryFilterOptions));
        }

        // one entry (FILTER_AT) always exists
        if (count($diaryFilterOptions) > 1) {
            if ($this->visitingUserModel->hasRight('BLOG_FILTER')) {
                $this->diaryModel->setFilter(DiaryModel::getFilterClass($diaryFilterOptions));
                $hasFilter['diary'] = true;
            } else {
                $this->rightsMissingView('BLOG_FILTER');
            }
        }
        
        return $hasFilter;
	}


	private function determineEntryMode() {
        // check if target user is active; if not, guestbook is frozen
        if (!$this->userModel->isActive()) {
        	return 'frozen';
        }

        // in order to recognize admin diary edits here
        // we have to check the type of editEntryModel
        if (!$this->isExternalView or ($this->editEntryModel instanceof DiaryEntry)) {
            if ($this->currentState == self::STATE_COMMENT and
                $this->visitingUserModel->hasRight('GB_ENTRY_COMMENT')) {
                return 'comment';
            }
            if ($this->editEntryModel instanceof GuestbookEntry and
                $this->visitingUserModel->hasRight('GB_ENTRY_ADMIN')) {
                return 'guestbook';
            }
            if ($this->currentState == self::STATE_ADD and
                $this->visitingUserModel->hasRight('BLOG_ENTRY_ADD')) {
                return 'diary';
            }
            if ($this->currentState == self::STATE_EDIT and
                $this->visitingUserModel->hasRight('BLOG_ENTRY_EDIT')) {
                return 'diary';
            }
            if ($this->editEntryModel instanceof DiaryEntry and
                $this->visitingUserModel->hasRight('BLOG_ENTRY_ADMIN')) {
            	return 'diary';
            }
        } else if ($this->isExternalView) {
            if (($this->currentState == self::STATE_ADD or $this->currentState == self::STATE_QUOTE) and
                $this->visitingUserModel->hasRight('GB_ENTRY_ADD')) {
                return 'guestbook';
            }
            if ($this->currentState == self::STATE_EDIT and
                $this->visitingUserModel->hasRight('GB_ENTRY_EDIT')) {
                return 'guestbook';
            }
        }

        // if no previous branch has applied, set mode to prohibited
        return 'prohibited';
    }

    private function determinePermissions() {
        $permissions = array();

        $permissions['diary_edit'] = $this->visitingUserModel->hasRight('BLOG_ENTRY_EDIT') &&
                                    !$this->isExternalView;
        $permissions['diary_delete'] = $this->visitingUserModel->hasRight('BLOG_ENTRY_DELETE') &&
                                      !$this->isExternalView;
        $permissions['diary_admin'] = $this->visitingUserModel->hasRight('BLOG_ENTRY_ADMIN');

        // in entry mode we have to determine, whether "update notice" may be suppressed
        if ($this->currentState == self::STATE_EDIT and !$this->isExternalView) {
            $permissions['diary_edit_without_notice'] = $this->visitingUserModel->hasRight('BLOG_ENTRY_EDIT_WITHOUT_NOTICE');
        } 
        if ($this->currentState == self::STATE_EDIT) {
            $permissions['guestbook_edit_without_notice'] = $this->visitingUserModel->hasRight('GB_ENTRY_EDIT_WITHOUT_NOTICE');
        }

        $permissions['guestbook_admin'] = $this->visitingUserModel->hasRight('GB_ENTRY_ADMIN');
        $permissions['guestbook_edit'] = $this->visitingUserModel->hasRight('GB_ENTRY_EDIT');
        $permissions['guestbook_delete'] = $this->visitingUserModel->hasRight('GB_ENTRY_DELETE');
        $permissions['guestbook_comment'] = $this->visitingUserModel->hasRight('GB_ENTRY_COMMENT');
        $permissions['guestbook_quote'] = $this->visitingUserModel->hasRight('GB_ENTRY_QUOTE');


        $permissions['guestbook_point'] = $this->mayGiveGuestbookPoint();
        $permissions['guestbook_give_multiple_points'] = $this->visitingUserModel->hasRight('GB_ENTRY_GIVE_MULTIPLE_POINTS');

        $permissions['friendlist_modify'] = $this->visitingUserModel->hasRight('FRIENDLIST_MODIFY') && $this->isExternalView;

        $permissions['guestbook_filter'] = ($this->visitingUserModel->hasRight('GB_FILTER') and !$this->isExternalView) || $this->visitingUserModel->hasRight('GB_ENTRY_ADMIN');
        $permissions['diary_filter'] = ($this->visitingUserModel->hasRight('BLOG_FILTER') and $this->userModel->getDiaryEntries() >= V_BLOG_MIN_FILTER_ENTRIES);

        return $permissions;
    }

    /**
     * determine and set level of privacy context
     */
    protected function determinePrivacyContextLevel() {
        // determine level of privacy context
        // start from restrictive levels to open levels

        // if visiting user is on ignore list, display as few details as possible
        if ($this->userVisitorRelationship == FriendModel::IS_FOE) {
            // grant access to all details which are classified up to all
            PrivacyContext::getContext()->setLevelByName('all');
            return;
        }
        
        if ($this->visitingUserModel->equals($this->userModel)) {
            // grant access to all details which are classified up to no one
            PrivacyContext::getContext()->setLevelByName('no one');
            return;
        }

        if ($this->userVisitorRelationship == FriendModel::IS_FRIEND) {
            // grant access to all details which are classified up to on friendlist
            PrivacyContext::getContext()->setLevelByName('on friendlist');
            return;
        }

        if ($this->visitingUserModel->isLoggedIn()) {
            // grant access to all details which are classified up to logged in
            PrivacyContext::getContext()->setLevelByName('logged in');
            return;
        }

        // grant access to all details which are classified up to all
        PrivacyContext::getContext()->setLevelByName('all');
    }

    /**
     * decides, whether user may give points for guestbook entry or not
     * @return boolean
     */
    protected function mayGiveGuestbookPoint() {
    	// 24 hours limit
    	return !$this->guestbookModel->hasEntryLately($this->visitingUserModel, 24);
    }

    protected function reverseFriendlist() {
        $this->visitingUserModel = Session::getInstance()->getVisitor();

        if (!$this->visitingUserModel->hasRight('FEATURE_REVERSE_FRIENDLIST')) {
            $this->rightsMissingView('FEATURE_REVERSE_FRIENDLIST');
            return;
        }

        $revFriends = FriendModel::getFriendsReverseByUser($this->visitingUserModel);
        $myFriends = FriendModel::getFriendsByUser($this->visitingUserModel);

        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'reverse_friendlist.tpl');
        // CACHEME: how could be cache this page??
        $main->setCacheParameter(3600, 'rev_friendlist|'. $this->visitingUserModel->getUsername());

        $main->assign('reversefriends', $revFriends);
        $main->assign('myfriends', $myFriends);
        $this->setCentralView($main);
        $this->view();

    }

    protected function getTabPane() {
        $tabPane = null;
        if (array_key_exists('tabpane', $_REQUEST)) {
            $tabPane = $_REQUEST['tabpane'];
        }

        if ($tabPane == 'description') {
        	return $this->ajaxUserDescription(false, false);
        } else if ($tabPane == 'smallworld') {
        	return $this->ajaxSmallWorld(false, false);
        } else if ($tabPane == 'user_stats') {
            return $this->ajaxUserStats(false, false);
        } else if ($tabPane == 'user_contact') {
            return $this->ajaxUserContact(false, false);
        } else if ($tabPane == 'guestbook_stats') {
        	return $this->ajaxGuestbookStats(false,false);
        } else if ($tabPane == 'user_awards') {
        	return $this->ajaxUserAward(false,false);
        }
        $_REQUEST['tabpane'] = 'description';
        return $this->ajaxUserDescription(false, false);
    }

    protected function ajaxRightsMissingView($rightName, $display) {
    	Logging::getInstance()->logSecurity('insufficient rights for ' . $rightName, false);
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_missingrights.tpl');
        // CACHEME: how could be cache this page??
        $main->setCacheParameter(86400, 'userinfo');

        if ($display) {
            $main->display();
        } else {
            return $main->fetch();
        }
    }

    protected function ajaxUserStats($display = true) {
    	$this->visitingUserModel = $session = Session::getInstance()->getVisitor();

        if(!$this->visitingUserModel->isLoggedIn()){
            return null;
        }

        // try to get usermodel for this page
        if ($this->createUserObject(false) == null) {
            return null;
        }

        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_user_stats.tpl');
        
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        // CACHEME: moderately cache for 15 minutes; invalidation is impractical
        $main->setCacheParameter(900, 'userinfo|' . $this->userModel->getCachekey() . '|userstats');

        $main->assign('userinfo_user', $this->userModel);

        if ($display) {
            $main->display();
        } else {
        	return $main->fetch();
        }
    }

    protected function ajaxUserContact($display = true) {
        $this->visitingUserModel = $session = Session::getInstance()->getVisitor();

        // try to get usermodel for this page
        if ($this->createUserObject(false) == null) {
            return null;
        }

        $this->userVisitorRelationship = FriendModel::getRelationType($this->userModel, $this->visitingUserModel);

        // set privacy context
        $this->determinePrivacyContextLevel();

        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_user_contact.tpl');
        
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        $basicCacheKey = 'userinfo|' . $this->userModel->getCachekey() . '|contact';
        $cacheKey = $basicCacheKey . '|' . PrivacyContext::getContext()->getLevel()->id;
        
        self::observeIPC(new UserIPC($this->userModel->id), 
            array('CONTACT_CHANGED', 'PRIVACY_CHANGED'),
            $main, 
            $basicCacheKey);
        $main->setCacheParameter(7200, $cacheKey);
        
        $main->assign('userinfo_user', $this->userModel);

        if ($display) {
            $main->display();
        } else {
            return $main->fetch();
        }
    }

    protected function ajaxGuestbookStats($display = true) {
        $this->visitingUserModel = $session = Session::getInstance()->getVisitor();

        if(!$this->visitingUserModel->isLoggedIn()){
            if (DEVEL === true) {
                error_reporting(E_WARNING);
            }
            return $this->ajaxRightsMissingView('GB_ADVANCED_STATS', $display);
        }

        // try to get usermodel for this page
        if ($this->createUserObject(false) == null) {
            return null;
        }

        // user needs either feature to view own stats or admin rights
        if (!($this->userModel->equals($this->visitingUserModel) and $this->visitingUserModel->hasRight('GB_ADVANCED_STATS'))
                and !$this->visitingUserModel->hasRight('GB_ADVANCED_STATS_ALL')){
            if (DEVEL === true) {
                error_reporting(E_WARNING);
            }
            return $this->ajaxRightsMissingView('GB_ADVANCED_STATS', $display);
        }

        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_guestbook_stats.tpl');
        // CACHEME: how could be cache this page??
        $main->setCacheParameter(900, 'friendlist');

        require_once MODEL_DIR . '/gb/guestbook_model.php';
        $gb = new GuestbookModel($this->userModel);
        $main->assign('guestbookstats', $gb->getEntryStatistics());

        if ($display) {
            $main->display();
        } else {
            return $main->fetch();
        }
    }
    
    protected function ajaxUserAward($display = true, $preProcess = true){
    	
        if($preProcess){
            $this->createUserObject();
        }
        
        require_once MODEL_DIR . '/award/user_award_model.php';
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_award.tpl');
    
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        $cacheKey = 'userinfo|' . $this->userModel->getCachekey() . '|user_awards';
        
        self::observeIPC(new UserIPC($this->userModel->id), 
            array('AWARD_CHANGED'),
            $main, 
            $cacheKey);
        
        $main->setCacheParameter(7200, $cacheKey);
        
        if (!$main->isCached()) {
            $Awards = UserAwardModel::getAwardByUser($this->userModel);
                
            $main->assign('userinfo_user', $this->userModel);
            $main->assign('awards',$Awards);
        } 
        
        if ($display) {
            $main->display();
        } else {
            return $main->fetch();
        }
    }
    
    protected function ajaxUserDescription($display = true, $preProcess = true) {
        if ($preProcess) {
            $this->visitingUserModel = $session = Session::getInstance()->getVisitor();

            if(!$this->visitingUserModel->isLoggedIn()){
                return null;
            }

            // try to get usermodel for this page
            if ($this->createUserObject(false) == null) {
                return null;
            }
        }

        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_description.tpl');
        
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        $cacheKey = 'userinfo|' . $this->userModel->getCachekey() . '|description';
        
        self::observeIPC(new UserIPC($this->userModel->id), 
            array('PROFILE_CHANGED'),
            $main, 
            $cacheKey);
        
        $main->setCacheParameter(7200, $cacheKey);
        $main->assign('userinfo_user', $this->userModel);

        if ($display) {
            $main->display();
        } else {
            return $main->fetch();
        }
    }

    protected function ajaxSmallWorld($display = true) {
        $this->visitingUserModel = $session = Session::getInstance()->getVisitor();

        if (!$this->visitingUserModel->isLoggedIn()){
            if (DEVEL === true) {
                error_reporting(E_WARNING);
            }
            return $this->ajaxRightsMissingView('FEATURE_SMALLWORLD', $display);
        }

        // try to get usermodel for this page
        if ($this->createUserObject(false) == null) {
            return null;
        }


        if (!$this->visitingUserModel->hasRight('FEATURE_SMALLWORLD')) {
        	if (DEVEL === true) {
        		error_reporting(E_WARNING);
        	}
            return $this->ajaxRightsMissingView('FEATURE_SMALLWORLD', $display);
        }

        $suser = array();
        if (!$this->visitingUserModel->equals($this->userModel)) {
            require_once CORE_DIR . '/utils/funny_features.php';
            $smallworld = FunnyFeatures::smallWorld($this->visitingUserModel, $this->userModel);

            $suser = array();
            if (count($smallworld) != 0) {
                $users = UserProtectedModel::getUsersByIds($smallworld);
                foreach($smallworld as $sworld){
                    array_push($suser, $users[$sworld]);
                }
            }
        }

        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_smallworld.tpl');
        // CACHEME: how could be cache this page??
        $main->setCacheParameter(3600, 'smallworld|'. $this->visitingUserModel->getUsername() . '|' . $this->userModel->getUsername());

        $main->assign('sUserModels',$suser);
        $main->assign('targetuser', $this->userModel);

        if ($display) {
            $main->display();
        } else {
            return $main->fetch();
        }
    }

    protected function ajaxGuestbook($display = true) {
       $this->visitingUserModel = $session = Session::getInstance()->getVisitor();

        if(!$this->visitingUserModel->isLoggedIn()){
            if (DEVEL === true) {
                error_reporting(E_WARNING);
            }
            return $this->ajaxRightsMissingView('FEATURE_SMALLWORLD', $display);
        }

        // try to get usermodel for this page
        if ($this->createUserObject(false) == null) {
            return null;
        }

        require_once MODEL_DIR . '/gb/guestbook_model.php';
        $this->guestbookModel = new GuestbookModel($this->userModel);

        // prefetch guestbook entries; need to mark them as read
        $gbEntries = $this->guestbookModel->getAllParsedEntries( 10, 0,
                    'desc',
                    !$this->visitingUserModel->hasRight('GB_ENTRY_ADMIN') );
        // if user visits his own page, handle unread guestbook entries
        if (!$this->isExternalView) {
            $unread = GuestbookEntry::setReadButDontDisplay($gbEntries);
            // adjust user's unread entry counter
            $this->visitingUserModel->setReadGBEntries($unread);
            $this->visitingUserModel->save();
        }

        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'part_guestbook.tpl');
        // CACHEME: how could be cache this page??
        $main->setCacheParameter(3600, 'userinfogb|'. $this->visitingUserModel->getUsername() . '|' . $this->userModel->getUsername());

        $main->assign('userinfo_guestbook',$gbEntries);

        if ($display) {
            $main->display();
        } else {
            return $main->fetch();
        }
    }

    protected function ajaxUserGB(){

        $this->visitingUserModel = $session = Session::getInstance()->getVisitor();

        if(!$this->visitingUserModel->isLoggedIn()){
        	return null;
        }

        // try to get usermodel for this page
        if ($this->createUserObject(false) == null) {
            return null;
        }

        require_once MODEL_DIR . '/gb/guestbook_model.php';
        $this->guestbookModel = new GuestbookModel($this->userModel);

        $this->elementPages['guestbook'] = array_key_exists('gbpage', $_REQUEST)
                ? $_REQUEST['gbpage'] :  V_GUESTBOOK_START_PAGE;

        $this->entriesPerPage['guestbook'] = V_GUESTBOOK_ENTRIES_PER_PAGE;

        $gbEntries = $this->guestbookModel->getAllParsedEntries( $this->entriesPerPage['guestbook'],
                $this->entriesPerPage['guestbook'] * ($this->elementPages['guestbook']-1),
                'desc',
                !$this->visitingUserModel->hasRight('GB_ENTRY_ADMIN') );

        foreach($gbEntries as $g){
        	$g->loadForAjax();
        }

        print json_encode($gbEntries);
    }

    protected function ajaxGuestbookFilterShow() {
        $this->visitingUserModel = $session = Session::getInstance()->getVisitor();

        if(!$this->visitingUserModel->isLoggedIn()){
            return;
        }

        $displayFilter = ($_REQUEST['show'] == 'true');
        $this->visitingUserModel->setGBFilterShow($displayFilter);
        $this->visitingUserModel->save();
    }

    protected function ajaxDiaryFilterShow() {
        $this->visitingUserModel = $session = Session::getInstance()->getVisitor();

        if(!$this->visitingUserModel->isLoggedIn()){
            return;
        }

        $displayFilter = ($_REQUEST['show'] == 'true');
        $this->visitingUserModel->setDiaryFilterShow($displayFilter);
        $this->visitingUserModel->save();
    }
    
    
    protected function ajaxDiaryPreview(){
        $user = $this->createUserObject(false);
        
        if (empty($_POST[F_BLOG_CONTENT_RAW])) {
            return null;
        }
        if (strlen($_POST[F_BLOG_CONTENT_RAW]) > V_ENTRY_MAX_CHARS) {
            return null;
        }        
        
        // try to fetch entry from session (needed in preview mode)
        if (!($entry = Session::getInstance()->getEntryDataChecked('diary', null))) {
            // instanciate new diary entry with POSTed data
            $entry = new DiaryEntry($_POST[F_BLOG_CONTENT_RAW], $user, self::getAjaxParseSettings());
        } else {
            // replace existing content
            $entry->setContentRaw($_POST[F_BLOG_CONTENT_RAW]);
            // renew parse settings
            $entry->setParseSettings(self::getAjaxParseSettings());
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'userinfo_preview.tpl');
        $main->assign('userinfo_editentry',$entry);
        $main->display();
           
    }
    
    protected function ajaxGuestbookPreview(){
    	$this->createUserObject(false);
        $this->visitingUserModel = Session::getInstance()->getVisitor();
       
        // check minimum condition for successful formular submission
        if ($_POST[F_GUESTBOOK_WEIGHTING] != -1 and
            $_POST[F_GUESTBOOK_WEIGHTING] !=  0 and
            $_POST[F_GUESTBOOK_WEIGHTING] !=  1) {            
            return false;
        }

        if (strlen($_POST[F_GUESTBOOK_CONTENT_RAW]) > V_ENTRY_MAX_CHARS) {
            return false;
        }
        if (empty($_POST[F_GUESTBOOK_CONTENT_RAW])) {
            return false;
        }

        // try to fetch entry from session (needed in preview mode)
        if (!($entry = Session::getInstance()->getEntryDataChecked('guestbook', null)) or
                !($entry->getRecipient()->equals($this->userModel))) {
            // instanciate new guestbook entry with POSTed data
            $entry = new GuestbookEntry($_POST[F_GUESTBOOK_CONTENT_RAW], $this->visitingUserModel, $this->userModel, self::getAjaxParseSettings());
        } else {
            // replace existing content
            $entry->setContentRaw($_POST[F_GUESTBOOK_CONTENT_RAW]);
            // renew parse settings
            $entry->setParseSettings(self::getAjaxParseSettings());
        }


        // we need no parsing here, because setContentRaw cleared
        // the pre-parsed string, so that at next access
        // automatic parsing will be triggered

        // determine value/points of guestbook entry
        $value = $_POST[F_GUESTBOOK_WEIGHTING];
        // check, if admin points are available
        if ($this->visitingUserModel->hasRight('GB_ENTRY_GIVE_MULTIPLE_POINTS')) {
            $value += $_POST[F_GUESTBOOK_WEIGHTING_ADDITIONAL];
        }
        $entry->setWeighting($value);
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USERINFO_TEMPLATE_DIR . 'userinfo_preview.tpl');
        $main->assign('userinfo_editentry',$entry);
        $main->assign('isGuestbook',true);
        $main->display();
        
    }
}
?>
