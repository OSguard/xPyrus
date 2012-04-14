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
// $Id: admin_business_logic_controller.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/businesslogic/i_am_god/admin_business_logic_controller.php $
require_once CORE_DIR . '/businesslogic/business_logic_controller.php';
require_once CORE_DIR . '/models/base/banner_model.php';
require_once CORE_DIR . '/models/base/tag_model.php';
require_once CORE_DIR . '/models/base/user_mail_model.php';
require_once CORE_DIR . '/models/base/study_path_model.php';
require_once CORE_DIR . '/models/pm/pm_entry_model.php';
//require_once CORE_DIR . '/models/user/user_warning_model.php';
require_once CORE_DIR . '/utils/input_validator.php';
require_once CORE_DIR . '/utils/notifier_factory.php';
require_once CORE_DIR . '/utils/client_infos.php';
require_once CORE_DIR . '/constants/form_constants.php';
require_once CORE_DIR . '/constants/value_constants.php';

require_once CORE_DIR . '/utils/global_ipc.php';

define('ADMIN_TEMPLATE_DIR', 'modules/admin/');

/**
 * @author kyle
 */
class AdminBusinessLogicController extends BusinessLogicController {
    
    public function __construct($ajaxView = false) {
        parent::__construct($ajaxView);
    }   

    public function process(){
    	$this->addLog('AdminBLC');
        parent::process();
    }

    protected function getDefaultMethod() {
        return 'overview';
    }       
    
    /**
      * List of al methods that are allowed
      */
    public function getAllowedMethods() {
        $array = parent::getAllowedMethods();
        array_push($array,
            'overview',
         
            'showUserWarnings', 
            'editUserWarnings', 
            'editUserRights', 
            'editGroupRights',
            'showAllEntriesByAuthor',
            'purgeUsers',
            
            'writeSystemPM',

            'editUserRoleMembership', 
            'editUserGroupMembership', 

            'editGroup', 
            'deleteGroup', 

            'editRole', 
            'deleteRole',
            
            'editStudyPaths',
            
            'editBanner',
            
            'editTag',
            
            'editFeatures',
            
            'editPointSources',
            
            'generateSmileyArray',
            'generateGlobalSettingsArray',
			
			'editFiles',
			'editCourses',
			'freeDownload',
			'deleteFile',
			'deleteFileVersion',
            
            'coursesMerge',
            
            'searchEntries',
            
            'showEmailLog',
            'showStats'
            );
        return $array;
    }
    
    public function getMethodObject($method) {
    	
        if('overview' != $method){
        	$parentMethod = new BLCMethod('Adminbereich',
                    rewrite_admin(array()),
                    BLCMethod::getDefaultMethod());
        }else{
        	$parentMethod = BLCMethod::getDefaultMethod();
        }
        
        return new BLCMethod('Admin: '.$method,
                    rewrite_admin(array()).'?method='.$method,
                    $parentMethod);
        
        //return parent::getMethodObject($method);
    }
    
    protected function overview(){
    	
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        $rights = array('PROFILE_ADMIN',
                        'GB_ENTRY_ADMIN',
                        'PM_SEND_AS_SYSTEM',
                        'ROLE_ADMIN',
                        'GROUP_ADMIN',
                        'ACCESS_STATS',
                        'COURSE_ADMIN',
                        'COURSE_FILE_ADMIN',
                        'TAG_ADMIN',
                        'FEATURE_ADMIN',
                        'POINT_SOURCE_ADMIN',
                        'SMILEY_ADMIN',
                        'GLOBAL_SETTINGS_ADMIN',
                        'BANNER_ADMIN',
                        );
        $acces = false;
        foreach($rights as $right){
        	if($cUser->hasRight($right)){
        		$acces = true;
        	}
        }
        
        if($acces == false){
        	$this->rightsMissingView('i_am_god');
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'overview.tpl');
        

        
        if(array_key_exists('username',$_REQUEST) && $cUser->hasRight('PROFILE_ADMIN')){
        	if (strpos($_REQUEST['username'], '@')) {
                $users = UserModel::searchByEmail($_REQUEST['username'], 30);
            } else {
                $users = UserModel::searchUser($_REQUEST['username'], 30, false, true);
            }
            if(sizeof($users) == 1){
            	$main->assign('user', $users[0]);
            }else{
                $main->assign('users', $users);
                $main->assign('userSearch', $_REQUEST['username']);
            }
        }elseif(array_key_exists('user',$_REQUEST) && $cUser->hasRight('PROFILE_ADMIN')){
        	// fetch invisible users, too
            $user = UserModel::getUserByUsername($_REQUEST['user'], false);
            $main->assign('user', $user);
        }
        $this->setCentralView($main, false);
        $this->view();
    }
	
    
    protected function showUserWarnings() {
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('PROFILE_ADMIN')) {
            $this->rightsMissingView('PROFILE_ADMIN');
        }
        
        $warnings = UserWarningModel::getAllWarningsByLatest();
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'warnings.tpl');
        $main->assign('user_warnings', $warnings);
        
        $this->setCentralView($main, false);
        $this->view();
    }
    
    protected function editUserWarnings() {
        
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('USER_WARNING_ADD')) {
            $this->rightsMissingView('USER_WARNING_ADD');
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'edit_user_warnings.tpl');
        $this->setCentralView($main, false);
        
        /* rights should saved */
        if (array_key_exists('save', $_REQUEST)) {
            $user = UserProtectedModel::getUserById($_REQUEST['userId'], false);
            
            $DB = Database::getHandle();
            $DB->StartTrans();
            
            if (array_key_exists('impose', $_REQUEST)) {
                $warning = new UserWarningModel();
                $warning->type = $_REQUEST['warning_type'];
                $warning->setReason($_REQUEST['reason']);
                $warning->declaredUntil = time() + $_REQUEST['duration'] * 86400;
                $warning->user = $user;
                $warning->save();
                
                // if it is not just a notice, send a message to the user
                if ($warning->type != $warning->TYPE_GREEN) {
                    $text = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'mail/user_warning.tpl');
                    $text->assign('reason', $_REQUEST['reason']);
                    $text->assign('user', $user);
                    if ($warning->type == $warning->TYPE_RED){
                    	NotifierFactory::createNotifierByName('email')->notify($user, CAPTION_USER_WARNING, $text->fetch());                      
                    }

                    $success = NotifierFactory::createNotifierByName('pm')->notify($user, CAPTION_USER_WARNING, $text->fetch());
                    if (!$success) {
                        Logging::getInstance()->logWarning('sending system pm failed');
                    }
                }
                
                // remove the have of the w-points
                if ($warning->type == $warning->TYPE_RED) {
                   PrivacyContext::getContext()->setLevelByName('no one');
                   $user->increaseUnihelpPoints(0, 
                        -($user->getPointsEconomic() * GlobalSettings::getGlobalSetting('POINT_SOURCES_FLOW_MULTIPLICATOR') / 2));
                   $user->save();
                }
                
                // notify about changes
                self::notifyIPC(new UserIPC($user->id), 'PROFILE_CHANGED');
                
            } else {
                $warnings = UserWarningModel::getAllWarningsByUser($user);
                foreach ($_POST as $key => $val) {
                    if (strpos($key, 'suspend') === 0) {
                        $suspendId = substr($key, 7);
                        if (array_key_exists($suspendId, $warnings)) {
                            $warnings[$suspendId]->expire();
                            $warnings[$suspendId]->save();
                        }
                    }
                }
            }
            
            // NOTE: roles are currently not automatically expired here
            // that is done by another cron-script
            
            $latestWarning = UserWarningModel::getLatestWarningByUser($user);
            if ($latestWarning != null) {
                $role = null;
                if ($latestWarning->type == $latestWarning->TYPE_YELLOW) {
                    $role = RoleModel::getRoleByName('card_yellow');
                } else if ($latestWarning->type == $latestWarning->TYPE_YELLOWRED) {
                    $role = RoleModel::getRoleByName('card_yellow_red');
                } else if ($latestWarning->type == $latestWarning->TYPE_RED) {
                    $role = RoleModel::getRoleByName('card_red');
                }
                if ($role != null) {
                    $role->addUsers(array($user));
                }
            }
            
            $DB->CompleteTrans();
            
            // enforce rights reload for user just worked at
            $user->removeFromUserOnlineList();
        }                     

        /* show the rights of the user */
        $username = trim($_REQUEST['username']);
        $user = UserProtectedModel::getUserByUsername($username, false);
        
        if ($user == null) {
            $this->errorView(ERR_NO_USER);
        }
        
        
        $main->assign('showWarnings', 'true');
        $main->assign('user', $user);
        
        $main->assign('user_warnings', UserWarningModel::getAllWarningsByUser($user));
        $main->assign('warnEmty', new UserWarningModel() );
        $this->view();
        return;
    }
    
    
    /**
     * edit the rights of a single user
     */
    protected function editUserRights() {
        
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('USER_RIGHT_ADMIN')){
            $this->rightsMissingView('USER_RIGHT_ADMIN');
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'edit_user_rights.tpl');
        $this->setCentralView($main, false);
        
        /* rights should saved */
        if(array_key_exists('save', $_REQUEST)) {
            $user = UserProtectedModel::getUserById($_REQUEST['userId'], false);
            
            $rightsGranted = array();
            if(array_key_exists('granted', $_REQUEST))
                $rightsGranted = $_REQUEST['granted'];

            $rightsNoMoreGranted = array();
            if(array_key_exists('noMoreGranted', $_REQUEST))
                $rightsNoMoreGranted = $_REQUEST['noMoreGranted'];
            
            RightModel::setUserRights($user->id, $rightsGranted, true);
            RightModel::setUserRights($user->id, $rightsNoMoreGranted, false);
            
            // enforce rights reload for user just worked at
            $user->removeFromUserOnlineList();
        }
          

        /* show the rights of the user */        
        if(empty($user)){
            $username = trim($_REQUEST['username']);
            $user = UserProtectedModel::getUserByUsername($username, false);
        }
        
        if($user == null){
            $this->errorView(ERR_NO_USER);  
        }      
        
        $main->assign('showRights', 'true');
        $main->assign('user', $user);
        
        $main->assign('rights', RightModel::getAllUserRights());        
        $main->assign('userRights', RightModel::getAllExplicitRightsByUser($user, false));
        $main->assign('allUserRights', RightModel::getGrantedUserRightsByUserId($user));
        
        $main->assign('roleRights', RightModel::getAllExplicitRightsByRoleIds($user->getRoleIds(), false));
        $main->assign('roles', RoleModel::getRolesByIds($user->getRoleIds()));

        $this->view();
        return;
    }
    
    
    protected function editUserRoleMembership() {
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('ROLE_ADMIN')){
            $this->rightsMissingView('ROLE_ADMIN');
        }

        $roleToEdit = null;
        $isAdd = false;
        $isDel = false;
        
        /* get our parameter in an easy to handle way */
        if(array_key_exists('roleId', $_REQUEST)) {
            $roleToEdit = RoleModel::getRoleById($_REQUEST['roleId']);
            
            if($roleToEdit == null)
                Logging::getInstance()->logSecurity('editUserRoleMembership: '.ERR_ROLE_NOT_EXISTING_ADMIN);
        } else {
            Logging::getInstance()->logSecurity('editUserRoleMembership: '.ERR_ROLE_NOT_GIVEN_ADMIN);
        }
        
        if(array_key_exists('del', $_REQUEST)) 
            $isDel = true;
        if(array_key_exists('add', $_REQUEST)) 
            $isAdd = true;
            
        if(($isAdd && $isDel) || (!$isAdd && !$isDel))
            Logging::getInstance()->logSecurity('editUserRoleMembership: '.ERR_ADD_OR_DEL_ADMIN);
        if(!array_key_exists('users', $_REQUEST))
            Logging::getInstance()->logSecurity('editUserRoleMembership: '.ERR_USER_NOT_GIVEN_ADMIN);
        $users = UserProtectedModel::getUsersByUsernames(explode(',', $_REQUEST['users']));

        if($isAdd) {
            $roleToEdit->addUsers($users);
        } else {
            $roleToEdit->delUsers($users);
        }
        
        $url = rewrite_admin( array('roles' => true, 'extern' => true) );
        header('Location: '. $url);

    }

    protected function editUserGroupMembership() {
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('ROLE_ADMIN')){
            $this->rightsMissingView('ROLE_ADMIN');
        }

        $groupToEdit = null;
        $isAdd = false;
        $isDel = false;
        
        /* get our parameter in an easy to handle way */
        if(array_key_exists('groupId', $_REQUEST)) {
            $groupToEdit = GroupModel::getGroupById($_REQUEST['groupId']);
            
            if($groupToEdit == null)
                Logging::getInstance()->logSecurity('editUserGroupMembership: '.ERR_NO_GROUP);
        } else {
            Logging::getInstance()->logSecurity('editUserGroupMembership: '.ERR_GROUP_NOT_GIVEN_ADMIN);
        }
        
        if(array_key_exists('del', $_REQUEST)) 
            $isDel = true;
        if(array_key_exists('add', $_REQUEST)) 
            $isAdd = true;
            
        if(($isAdd && $isDel) || (!$isAdd && !$isDel))
            Logging::getInstance()->logSecurity('editUserGroupMembership: '.ERR_ADD_OR_DEL_ADMIN);
        if(!array_key_exists('users', $_REQUEST))
            Logging::getInstance()->logSecurity('editUserGroupMembership: '.ERR_USER_NOT_GIVEN_ADMIN);
        $users = UserProtectedModel::getUsersByUsernames(explode(',', $_REQUEST['users']));

        if($isAdd) {
            $groupToEdit->addUsers($users);
            // announce change via IPC
            foreach ($users as $user) {
                self::notifyIPC(new UserIPC($user->id), 'GROUPS_CHANGED');
            }
        } else {
            $groupToEdit->delUsers($users);
            // announce change via IPC
            foreach ($users as $user) {
                self::notifyIPC(new UserIPC($user->id), 'GROUPS_CHANGED');
            }
        }

        $url = rewrite_admin( array('groups' => true, 'extern' => true) );
        header('Location: '. $url);
    }
    
    protected function writeSystemPM(){
    	
        $cUser = Session::getInstance()->getVisitor();
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'write_system_pm.tpl');
        
        if(!$cUser->hasRight('PM_SEND_AS_SYSTEM')){
        	$this->rightsMissingView('PM_SEND_AS_SYSTEM');
        }
        $user = null;
        if(array_key_exists('toAll',$_REQUEST)){
        	$main->assign('toAll',true);
            $recipients = 'toAll';
        }
        elseif(array_key_exists('toOnline',$_REQUEST)){
            $main->assign('toOnline',true);
            $recipients = 'toOnline';
        }
        elseif(array_key_exists('user',$_REQUEST)){
            $user = UserProtectedModel::getUserByUsername($_REQUEST['user']);
            $recipients = array($user);
        }
        
        if(array_key_exists('save',$_REQUEST)){
        	
            $formFields = array(
            'entryText'   => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false)
            );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);
            if (count($this->errors) > 0){
                $this->errorView('Fehler ist aufgetreten');
            }  
            
            $text = $_REQUEST['entryText'];
            if(array_key_exists('mantisBug',$_REQUEST)){
                $text .= "\n\n" . '[url='.rewrite_mantis(array('extern'=>1, 'type' => 'to'. $_REQUEST['mantisBug'] )).']antworten[/url]';
            }
            $caption = 'Nachricht vom UniHelp Support';
            
            $success = PmEntryModel::sendSystemPm($text, $caption,
                 'UniHelp Support', $recipients, array_key_exists('enable_formatcode',$_REQUEST), array_key_exists('enable_smileys',$_REQUEST));
                 
            if (!$success) {
                Logging::getInstance()->logWarning('sending system pm failed');
            }    
            
            if(array_key_exists('mantisBug',$_REQUEST)){
            	$bugText = '-' . $cUser->getUsername() . '- schreibt an \'' . $user->getUsername() . '\':' . "\n\n" . $text;
                include CORE_DIR . '/businesslogic/mantis/mantis_business_logic_controller.php';
                MantisBusinessLogicController::addNote($_REQUEST['mantisBug'], $bugText);
                
                header('Location: http://bugs.unihelp.de/view.php?id='.$_REQUEST['mantisBug']);
                return;
                
            }else{
            	if($user){
                    header('Location: '.rewrite_admin(array('user'=>$user, 'extern' => true)));
                }else{
                	header('Location: '.rewrite_admin(array('extern' => true)));
                }
            } 
        }        
        $main->assign('user', $user);
        if(array_key_exists('mantisBug',$_REQUEST)){
        	$main->assign('mantisBug',$_REQUEST['mantisBug']);
        }
        $this->setCentralView($main, false);
        $this->view();
    }
    
    
    protected function searchEntries() {
        if (!Session::getInstance()->getVisitor()->hasRight('GB_ENTRY_ADMIN')) {
            $this->rightsMissingView('GB_ENTRY_ADMIN');
            return;
        }
        
        require_once MODEL_DIR . '/gb/guestbook_entry.php';
        require_once MODEL_DIR . '/gb/guestbook_model.php';
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'search_entries.tpl');
        
        $numberEntries = 30;
        
        $filterOptions = array();
        $filterErrors = array();
        if (InputValidator::getRequestData('search', false)) {
            if (!empty($_POST['filterauthor'])) {
                $filterOptions[BaseFilter::FILTER_AUTHOR] = array();
                // ignore multiple whitespace between author names on split
                $authors = preg_split('/\s+/', trim($_POST['filterauthor']));
    
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
                        array_push($filterOptions[BaseFilter::FILTER_AUTHOR], $filterAuthor);
                    } else {
                        $filterErrors['filterauthor'] = true;
                    }
                }
            } else if (array_key_exists('filterauthor', $_POST)) {
                // author is empty, so we can remove filtering by it
                $filterOptions[BaseFilter::FILTER_AUTHOR] = array();
            }
            if (count($filterOptions[BaseFilter::FILTER_AUTHOR]) == 0) {
                unset($filterOptions[BaseFilter::FILTER_AUTHOR]);
            }
    
            if (array_key_exists('filterdatefrom_Year', $_POST) and $_POST['filterdatefrom_Year'] != '') {
                $year = $_POST['filterdatefrom_Year'];
                $month = ($_POST['filterdatefrom_Month'] != '') ? $_POST['filterdatefrom_Month'] : '01';
                $day = ($_POST['filterdatefrom_Day'] != '') ? $_POST['filterdatefrom_Day'] : '01';
                $date = $year . '-' . $month . '-' . $day;
                if (!array_key_exists(BaseFilter::FILTER_ENTRYDATE, $filterOptions) or
                        $filterOptions[BaseFilter::FILTER_ENTRYDATE] == null) {
                    $filterOptions[BaseFilter::FILTER_ENTRYDATE] = array();
                }
                $filterOptions[BaseFilter::FILTER_ENTRYDATE]['from'] = $date;
            }
            if (array_key_exists('filterdateto_Year', $_POST) and $_POST['filterdateto_Year'] != '') {
                $year = $_POST['filterdateto_Year'];
                $month = ($_POST['filterdateto_Month'] != '') ? $_POST['filterdateto_Month'] : '01';
                $day = ($_POST['filterdateto_Day'] != '') ? $_POST['filterdateto_Day'] : '01';
                $date = $year . '-' . $month . '-' . $day;
                if (!array_key_exists(BaseFilter::FILTER_ENTRYDATE, $filterOptions) or
                        $filterOptions[BaseFilter::FILTER_ENTRYDATE] == null) {
                    $filterOptions[BaseFilter::FILTER_ENTRYDATE] = array();
                }
                $filterOptions[BaseFilter::FILTER_ENTRYDATE]['to'] = $date;
            }
            
            $text = trim(InputValidator::getRequestData('filtertext', ''));
            if ($text != '') {
                $filterOptions[BaseFilter::FILTER_TEXT] = $text;
            }
        }
        
        if (count($filterOptions) > 0) {
            $gbFilter = GuestbookModel::getFilterClass($filterOptions);
            $e = GuestbookEntry::getEntriesByFilter($gbFilter, $numberEntries);
            
            $remaining = 0;
            if (count($e) == $numberEntries) {
                $remaining = GuestbookEntry::countEntriesByFilter($gbFilter) - $numberEntries;
            }
            
            $main->assign('gbentries', $e);
            $main->assign('gbentries_remaining', $remaining);

            $forumFilter = ThreadModel::getFilterClass($filterOptions);
            $e = ThreadEntryModel::getEntriesByFilter($forumFilter, $numberEntries);
            
            $remaining = 0;
            if (count($e) == $numberEntries) {
                $remaining = ThreadEntryModel::countEntriesByFilter($forumFilter) - $numberEntries;
            }
            $main->assign('threadentries', $e);
            $main->assign('threadentries_remaining', $remaining);
        }
        
        $main->assign('filter', $filterOptions);
        $main->assign('filtererrors', $filterErrors);

        $this->setCentralView($main, false);
        $this->view();
    }
    
	

    
    
    /**
     * safely purges user account
     * @param UserModel
     */
    protected static function _purgeUser($u) {
        require_once MODEL_DIR . '/gb/guestbook_model.php';
        require_once MODEL_DIR . '/pm/pm_model.php';
        require_once MODEL_DIR . '/course/course_file_model.php';
        require_once MODEL_DIR . '/forum/thread_model.php';

        $DB = Database::getHandle();
        $DB->StartTrans();
        
        $user = new UserModel;
        $universities = UniversityModel::getAllUniversities();
        $user = UserModel::createFromRegisterData(substr(md5($u->getUsername()),24),
                    'somestrangepasswordthatnobodyshouldknow', 
                    // take first university we can get
                    current($universities)->id,
                    PersonTypeModel::getPersonTypeByName('gelöscht')->id);
        $user->setActive(false);
        $user->save();
        
        GuestbookModel::replaceAuthor($u, $user);
        ThreadModel::replaceAuthor($u, $user);
        PmModel::replaceAuthor($u, $user);
        CourseFileModel::replaceAuthor($u, $user);
        
        $oldUsername = $u->getUsername();
        // delete user and also free his username for further use
        // as we intend to insert a new user with the same username
        $u->delete(true);
        
        $user->setUsername($oldUsername);
        //var_dump($user);
        $user->save();
        
        $DB->CompleteTrans();
    }
    
    protected function purgeUsers() {
        if (!Session::getInstance()->getVisitor()->hasRight('USER_DELETE')) {
            $this->rightsMissingView('USER_DELETE');
        }
        
        if (array_key_exists('requestPurgeConfirmation', $_REQUEST) and array_key_exists('usersToDelete', $_REQUEST)) {
            $usersToDelete = UserModel::getUsersByIds($_REQUEST['usersToDelete'], '', false);
            
            if (count($usersToDelete) > 0) {
                $userString = '';
                $userIds = '';
                foreach ($usersToDelete as $u) {
                    $userString .= $u->getUsername() . ',';
                    $userIds    .= $u->id . ',';
                }
                $userString = substr($userString, 0, -1);
                $userIds = substr($userIds, 0, -1);
                
                return $this->confirmationView(NAME_USERS_DEL.': ' . $userString,
                                           DO_ACTION_USERS_DEL,
                                           rewrite_admin(array('purgeusers' => 1, 'purgeIds' => $userIds)),
                                           rewrite_admin(array('purgeusers' => 1)));            
            }
        } else if (!array_key_exists('requestPurgeConfirmation', $_REQUEST) and array_key_exists('purgeIds', $_REQUEST)) {
            $usersToDelete = UserModel::getUsersByIds(explode(',', $_REQUEST['purgeIds']), '', false);
            
            foreach ($usersToDelete as $u) {
                $uid = $u->id;
                self::_purgeUser($u);
                
                // log purging for backup purposes
                Logging::getInstance()->logUserDelete(Logging::getErrorMessage(USER_ACCOUNT_PURGED, time(), $uid));
            }
            
            header('Location: ' . rewrite_admin(array('extern' => 1, 'purgeusers' => 1)));
        }
        
        if (array_key_exists('recoverSubmit', $_REQUEST) and is_array($_REQUEST['usersToDelete'])){
        	$usersToRecover = UserModel::getUsersByIds($_REQUEST['usersToDelete'], '', false);
            foreach($usersToRecover as $u){
            	$u->removeFromRecycleBin();
            }
        }
        
        $users = UserModel::getUsersToDelete(); 
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'purge_users.tpl');
        $main->assign('users_to_delete', $users);

        $this->setCentralView($main, false);
        $this->view();
        
        return; 
    }        
    
    protected function editRole() {
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('ROLE_ADMIN')){
            $this->rightsMissingView('ROLE_ADMIN');
        }

        $roleToEdit = null;
        $isSave = false;
        
        /* get our parameter in an easy to handle way */
        if(array_key_exists('roleId', $_REQUEST)) {
            $roleToEdit = RoleModel::getRoleById($_REQUEST['roleId']);
            
            if($roleToEdit == null){
                Logging::getInstance()->logSecurity('editRole: '.ERR_ROLE_NOT_EXISTING_ADMIN);
            }
        }
        
        if(array_key_exists('save', $_REQUEST)){
            $isSave = true;
        }
            
        /* we should save the role */
        if($isSave) {
            if($roleToEdit == null) {
                $roleToEdit = new RoleModel();                
            }

            if(!array_key_exists('name', $_REQUEST) ||
                !array_key_exists('description', $_REQUEST)) {
                Logging::getInstance()->logSecurity('editRole: '.ERR_ROLE_NOT_GIVEN_ADMIN);
            }
            
            $name = trim($_REQUEST['name']);
            $description = trim($_REQUEST['description']);

            if($name == '' || $description == ''){
                die('editRole: '.ERR_NO_NAME_OR_DESCRIPTION);
            }

            $roleToEdit->name = $name;
            $roleToEdit->description = $description;
            
            $roleToEdit->save();
                
            /* set valid rights */
            $newRights = array();
            if(array_key_exists('rights', $_REQUEST)){
            	$newRights = $_REQUEST['rights'];
            }
            RightModel::setRoleRights($roleToEdit->id, $newRights);
            $newRights = array();
            if(array_key_exists('NOrights', $_REQUEST)){
                $newRights = $_REQUEST['NOrights'];
            }   
            RightModel::setRoleRights($roleToEdit->id, $newRights, false);
            $roleToEdit = null;
        }
        
        /*if($roleToEdit != null){
        	var_dump($roleToEdit->getRoleRights());
        }*/
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'edit_role.tpl');
        $main->assign('roles', RoleModel::getAllRoles());
        $main->assign('allRights', RightModel::getAllUserRights());
        $main->assign('roleToEdit', $roleToEdit);

        $this->setCentralView($main, false);
        $this->view();
    }
    
    
    protected function deleteRole() {
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('ROLE_ADMIN')){
            $this->rightsMissingView('ROLE_ADMIN');
        }

        $roleToEdit = null;
        
        /* get our parameter in an easy to handle way */
        if(array_key_exists('roleId', $_REQUEST)) {
            $roleToEdit = RoleModel::getRoleById($_REQUEST['roleId']);
            
            if($roleToEdit == null){
                Logging::getInstance()->logSecurity("deleteRole: ".ERR_ROLE_NOT_EXISTING_ADMIN);
            }
        } else {
            Logging::getInstance()->logSecurity("deleteRole: ".ERR_ROLE_NOT_GIVEN_ADMIN);
        }
        
        $url = rewrite_admin( array('roles' => true, 'extern' => true) );
        
        if(!array_key_exists('confirmation', $_REQUEST)) {
            return $this->confirmationView(NAME_ROLE_DEL.': ' . $roleToEdit->name,
                                           DO_ACTION_ROLE_DEL,
                                           rewrite_admin( array('role' => $roleToEdit, 'delok'=> true) ),
                                           $url);        
        }
        
        $roleToEdit->delete();
        
        header('Location: '. $url);
    }
    
    protected function editGroup() {
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('GROUP_ADMIN')){
        	$this->rightsMissingView('GROUP_ADMIN');
        };

        $groupToEdit = null;
        $isSave = false;
        
        /* get our parameter in an easy to handle way */
        if(array_key_exists('groupId', $_REQUEST)) {
            $groupToEdit = GroupModel::getGroupById($_REQUEST['groupId']);
            
            if($groupToEdit == null){
                $this->errorView('editGroup: '.ERR_NO_GROUP);
            }
        }
        
        if(array_key_exists('save', $_REQUEST)){
            $isSave = true;
        }
            
        /* we should save the group */
        if($isSave) {
            if($groupToEdit == null) {
                $groupToEdit = new GroupModel();
            }
            
             $formFields = array(
            'description'   => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 10, 'lengthHi' => 250), 'escape' => true),
            'name'   => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 3, 'lengthHi' => 200), 'escape' => true),
            'title'   => array('required' => false, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 3, 'lengthHi' => 200), 'escape' => true)
             );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);
            
            if(count($this->errors) > 0){
                $this->errorView(ERR_GROUP_EDIT);
            }
            $groupToEdit->title = $_POST['title'];
            $groupToEdit->name = $_POST['name'];
            $groupToEdit->description = $_POST['description'];
            $groupToEdit->isVisible = array_key_exists('isVisible', $_REQUEST);    
                
            $groupToEdit->save();
            $groupToEdit = null;
            
            self::notifyIPC(new GlobalIPC, 'GROUP_CHANGED');
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'edit_group.tpl');
        $main->assign('groups', GroupModel::getAllGroups());
        $main->assign('groupToEdit', $groupToEdit);

        $this->setCentralView($main, false);
        $this->view();
    }
    
    protected function editGroupRights(){
        
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('USER_RIGHT_ADMIN')){
            $this->rightsMissingView('USER_RIGHT_ADMIN');
        }
        
        /* get our parameter in an easy to handle way */
        if(array_key_exists('groupId', $_REQUEST)) {
            $group = GroupModel::getGroupById($_REQUEST['groupId']);
            
            if($group == null){
                $this->errorView('editGroupRights: '.ERR_NO_GROUP);
            }
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'edit_group_rights.tpl');
        $this->setCentralView($main, false);
        
        /* rights should saved */
        if(array_key_exists('save', $_POST)) {
            
            $group = GroupModel::getGroupById($_REQUEST['groupId']);
            
            if($group == null){
                $this->errorView(ERR_NO_GROUP);
            }
            
            $groupRightsGranted = array();
            if(array_key_exists('granted', $_REQUEST)){
                $groupRightsGranted = $_REQUEST['granted'];
            }
                
            //var_dump($groupRightsGranted);   
            
            RightModel::setGroupRights($group->id, $groupRightsGranted);
            header('Location: '.rewrite_admin(array('groups'=>true, 'extern' => true)));
            return;
        }
        
        $groupUserRights = RightModel::getAllExplicitRightsByGroup($group, false);
        $groupRights = RightModel::getAllGroupRights();
        
        //var_dump($groupUserRights);
        
        $main->assign('groupUserRights',$groupUserRights);
        $main->assign('groupRights', $groupRights);
        
        $main->assign('userGroup', $group);
        
        $this->view();
    }
    
    protected function deleteGroup() {
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('GROUP_ADMIN')){
            $this->rightsMissingView('GROUP_ADMIN');
        }

        $groupToEdit = null;
        
        /* get our parameter in an easy to handle way */
        if(array_key_exists('groupId', $_REQUEST)) {
            $groupToEdit = GroupModel::getGroupById($_REQUEST['groupId']);
            
            if($groupToEdit == null)
                Logging::getInstance()->logSecurity('deleteGroup: '.ERR_NO_GROUP);
        } else {
            Logging::getInstance()->logSecurity('deleteGroup: '.ERR_GROUP_NOT_GIVEN_ADMIN);
        }
        
        $url = rewrite_admin( array('groups' =>  true) );
        
        if(!array_key_exists('confirmation', $_REQUEST)) {
            return $this->confirmationView(NAME_GROUP_DEL.': ' . $groupToEdit->name,
                                           DO_ACTION_GROUP_DEL,
                                           rewrite_admin( array('group' => $groupToEdit, 'delok' => true) ),
                                           $url);       
        }
        
        $groupToEdit->delete();
        header('Location: '. $url);
    }
    
    protected function editStudyPaths(){
        
        /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('COURSE_ADMIN')){
			$this->rightsMissingView('COURSE_ADMIN');
		}
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'edit_study_paths.tpl');     
        
        if(array_key_exists('save',$_POST)){
            if(!array_key_exists('sp_name',$_REQUEST) || $_REQUEST['sp_name'] == ''){
                $this->errorView(ERR_NO_STUDY_PATH);
            }
            
            $spId = array_key_exists('sp_id',$_REQUEST) ? $_REQUEST['sp_id'] : null;
            
            $newStudyPath = new StudyPathModel($spId, 
                                         $_REQUEST['sp_name'],
                                         $_REQUEST['sp_name_short'],
                                         $_REQUEST['sp_name_english'],
                                         $_REQUEST['sp_description'],
                                         array_key_exists('sp_available', $_REQUEST));
            $newStudyPath->setUniId($_REQUEST['uniId']);                                         
            $newStudyPath->save();
        }
        
        if(array_key_exists('saveTag', $_POST)){
        	$signTags = $_REQUEST['newTags'];
            //var_dump($signTags);
            
            if (!array_key_exists('studyId', $_REQUEST)            
                 ||($studyPath = StudyPathModel::getStudyPathById($_REQUEST['studyId'])) == null ){
				throw new ArgumentNullException("studyId");
            }
                
            TagModel::setStudyPathTag($studyPath->id, $signTags);
        }
        
                        
        $paths = StudyPathModel::getAllStudyPaths();
        $main->assign('study_paths', $paths);
        $main->assign('universities',UniversityModel::getAllUniversities());
        
        if(array_key_exists('edit_mode',$_REQUEST) && array_key_exists('sp_id',$_REQUEST)){
            
            $pathToEdit = StudyPathModel::getStudyPathById($_REQUEST['sp_id']);

            $main->assign('pathToEdit', $pathToEdit);
        }
        
        if(array_key_exists('tag_mode',$_REQUEST) && array_key_exists('sp_id',$_REQUEST)){
        	
            $tags = TagModel::getAllTags();
            //$signTags = TagModel::getTagByStudyPath($_REQUEST['sp_id']); 
            $studyTag = StudyPathModel::getStudyPathById($_REQUEST['sp_id']);
             
            $main->assign('tags' ,$tags);
            $main->assign('studyTag', $studyTag);
            
        }
        
        $this->setCentralView($main, false);

        parent::view();
    }
    
    
    protected function editCourses(){
        /* current user logged in user */
        $session = Session::getInstance();
        $cUser = $session->getVisitor();
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'edit_courses.tpl');
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('COURSE_ADMIN')){
            $this->rightsMissingView('COURSE_ADMIN');
        }
        $id = InputValidator::getRequestData('id', $this->createID());
        $session_data = $session->getUserData($id);
        if(array_key_exists('saveNew',$_REQUEST)){
            
             $formFields = array(
            'course_name'   => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 200), 'escape' => true),
            'course_name_english'   => array('required' => false, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 200), 'escape' => true),
            'course_name_short'   => array('required' => false, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 2, 'lengthHi' => 10), 'escape' => true),                       
             );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);
            
            if(sizeof($this->errors)>0){
                $this->errorView('Daten nicht gültig');
            }
            
            $newCourse = new CourseModel(null, 
                                         $_REQUEST['course_name'],
                                         $_REQUEST['course_name_short'],
                                         $_REQUEST['course_name_english']);                                         
            //var_dump($newCourse);
            $newCourse->save();
        }
        
        if ((!isset($_REQUEST['save']) && !isset($_REQUEST['filter']) && !isset($_REQUEST['filter_reset'])) 
            || !$session_data ){
            $courses = CourseModel::getAllCourses();
            $session_data = array( 'filteredCourses' => array(0), 'id'=>$id, 'courses'=>$courses);
            $session->storeUserData($id, $session_data);
            $this->setTemplateVars($main, array('courses' => $courses, 'id'=>$id, 'filteredCourses' => array(0), 
                'filteredDescription'=>""));
        }
        elseif (isset($_REQUEST['filter_reset']) && $session_data && !array_key_exists(F_STATUS_SUBMITED, $session_data)){
            //reset all filters to default value
            $filteredCourses = 0;
            $courses = $session_data['courses'];
            $this->setTemplateVars($main, array('courses' => $courses, 'id'=>$id, 'filteredCourses'=>array(0)));
        }
        elseif (isset($_REQUEST['filter']) && $session_data && !array_key_exists(F_STATUS_SUBMITED, $session_data)){ 
            $filteredCourses = InputValidator::getRequestData('courseBox', array());
            $filteredDescription = trim(InputValidator::getRequestData('courseDescription', ''));
            $courses = $session_data['courses'];
            
            $showAll = (is_array($filteredCourses) ? in_array(0, $filteredCourses) : true);
            $noCoursesSelected = (is_array($filteredCourses) ? count($filteredCourses)==0 : true);
            if ( $showAll || ($noCoursesSelected && !$filteredDescription) ){
            //if 'Kein Filter' (No filter) is clicked or if nothing is clicked at all and no description is given either => assume retrieval of all files
                $coursesToShow = $courses;
                $filteredDescription = '';
            }elseif ( !$noCoursesSelected && !$showAll ){
            //if courses other than 'Kein Filter' have been selected => retrieve respective courses
                $tmp = array();
                foreach ($filteredCourses as $course_id){
                    //extract all CourseFileModels from $courses that meet criteria CourseFileModel->id = $course_id; make array 0-based so that easy access can be granted
                    $tmp2 = array_values(array_filter($courses, create_function('$e', 'return ($e->id == '.$course_id.');' )));
                    if (count($tmp2)==0){
                        $this->errors['Eingabefehler'] = "Ungueltige Eingabewerte; Fach-ID falsch.";
                    }else{
                        $tmp[] = $tmp2[0];
                    }
                $coursesToShow = $tmp;              
                }
                $filteredDescription = '';
            }elseif ( $filteredDescription && ($noCoursesSelected || $showAll) ){
                //if a description was given an no other selections have been made
                $coursesToShow = CourseModel::searchCourse($filteredDescription, false, true);
            }else{
                //avoid undefined status...
                $this->addIncompleteField($this->errors, 'description', ERR_FORM_NOT_VALID);
                $this->addIncompleteField($this->errors, 'courseBox', ERR_FORM_NOT_VALID);
                $coursesToShow = array();
                $filteredDescription = '';
            }
            $session_data['coursesToShow'] = $coursesToShow;
            $session->storeUserData($id, $session_data);
            $this->setTemplateVars($main, array('courses' => $courses, 'id'=>$id, 'filteredCourses'=>$filteredCourses, 
                'coursesToShow'=>$coursesToShow, 'startOutput'=>'true', 'filteredDescription'=>$filteredDescription));
        }
        elseif (isset($_REQUEST['save']) && $session_data && !array_key_exists(F_STATUS_SUBMITED, $session->getUserData($id)) ){
            $coursesToShow = $session_data['coursesToShow'];
            $courses = $session_data['courses'];
            $id = $session_data['id'];
            $filteredCourses = $session_data['filteredCourses'];
            /*
             * FIXME user right InputValidator for escape
             */
            foreach ($coursesToShow as $course){
                $courseTouched = false;
                
                $tmp = trim(InputValidator::getRequestData('course_'.$course->id, -1));
                if (strcasecmp($tmp, $course->getName())!=0){
                    $courseTouched = true;
                }
                $course->setName( $tmp );

                $tmp = trim(InputValidator::getRequestData('courseEng_'.$course->id, ""));
                if (strcasecmp($tmp, $course->getNameEnglish())!=0){
                    $courseTouched = true;
                }
                $course->setNameEnglish($tmp);
                
                $tmp = trim(InputValidator::getRequestData('courseShort_'.$course->id, ""));
                if (strcasecmp($tmp, $course->getNameShort())!=0){
                    $courseTouched = true;
                }
                $course->setNameShort($tmp);
                
                if (count($this->errors) == 0 && $courseTouched) {
                    try {    
                        $course->save();
                    } catch (DBException $e) {
                        // TODO: error handling
                        $this->errors['course_'.$course->id] = $e->getMessage();
                    }
                }
            }
            //everything was fine
            if (count($this->errors)==0){
                $session->deleteUserData($id);
                unset($session_data);
                $session_data = array();
                $session_data[F_STATUS_SUBMITED] = true;
                $session->storeUserData($id, $session_data);

                $this->setTemplateVars($main, array(/*'courses' => $courses, 'id'=>$id, 'filteredCourses'=>$filteredCourses, 
                'coursesToShow'=>$coursesToShow,*/ 'ackNeeded'=> true));

            }else{
                $this->setTemplateVars($main, array('courses' => $courses, 'id'=>$id, 'filteredCourses'=>$filteredCourses, 
                'coursesToShow'=>$coursesToShow, 'errors'=> 'Fehler beim Ändern der Daten aufgetreten.'));
            }
        }else{//undefined call to function => show empty form fields
            $courses = array();/*CourseModel::getAllCourses();*/
            //$this->addIncompleteField($this->errors, 'courseBox', ERR_FORM_NOT_VALID);
            $this->setTemplateVars($main, array('courses' => $courses, 'id'=>$id, 'filteredCourses'=>array(),
                'filteredDescription'=>""));
        }
        $this->setCentralView($main, false);
        parent::view();
    }
    
    private static function createID(){
        return md5(uniqid (rand(), true));
    }
    
    
    private function setTemplateVars($template, $vars = array()){
        foreach ($vars as $key => $value){
            $template->assign($key, $value);
        }
    }
    
    private function retrieveFiles($session_data){
        $filteredCourses = $session_data['filteredCourses'];
        $filteredCategory = $session_data['filteredCategory'];
        $filteredSemester = $session_data['filteredSemester'];
        $filteredDescription = $session_data['filteredDescription'];
        $filteredFilename = $session_data['filteredFilename'];
        $coursefiles_orderDirstring = $session_data['coursefiles_orderDirstring'];
        $coursefiles_orderstring = $session_data['coursefiles_orderstring'];
        $courses = $session_data['courses'];
        
        $coursesToRetrieve = array();
        //if no course(s) selected or 'No filter' ('Kein Filter') selected ;-) (see template for explanation...) use all courses
        if (count($filteredCourses)==0 || $filteredCourses[0]==0){
            $coursesToRetrieve = $courses;
        }
        else{//get CourseModels from session instead of re-retrieving from database
            $tmp = array();
            foreach ($filteredCourses as $course_id){
                //extract all CourseFileModels from $courses that meet criteria CourseFileModel->id = $course_id; make array 0-based so that easy access can be granted
                $tmp2 = array_values(array_filter($courses, create_function('$e', 'return ($e->id == '.$course_id.');' )));
                if (count($tmp2)==0){
                    $this->errors['Eingabefehler'] = "Ungueltige Eingabewerte; Fach-ID falsch.";
                }else{
                    $tmp[] = $tmp2[0];
                }
            $coursesToRetrieve = $tmp;
            }
        }
        //build filter options array
        $filterOptions = array( 
            CourseFileFilter::FILTER_CATEGORY => 
                (empty($filteredCategory) ? null : CourseFileCategoryModel::getCategoryById($filteredCategory)), 
            CourseFileFilter::FILTER_SEMESTER => 
                (empty($filteredSemester) ? null : CourseFileSemesterModel::getSemesterById($filteredSemester)),
            CourseFileFilter::FILTER_DESCRIPTION => 
                (empty($filteredDescription) ? null : $filteredDescription),
            CourseFileFilter::FILTER_FILENAME => 
                (empty($filteredFilename) ? null : $filteredFilename),
            CourseFileFilter::FILTER_ORDERDIR => 
                (empty($coursefiles_orderDirstring) ? null : $coursefiles_orderDirstring),
            CourseFileFilter::FILTER_ORDER =>
                (empty($coursefiles_orderstring) ? null : $coursefiles_orderstring)
            );
        $filterOptions = array_filter($filterOptions, create_function('$b', 'return !(is_null($b) || empty($b)) ;'));
        $filter = new CourseFileFilter($filterOptions);
        $files = array();
        //retrieve files matching the criteria given by $filterOptions
        try{
            $f = CourseFileModel::getCourseFilesByCourses($coursesToRetrieve, null, $filter, -1);
            if (count($f)>=2){
                $files = $f[1];//files are saved on second position in array
            }
        }
        catch (DBException $e){
            $this->errors['DB_Fehler'] = Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$e->ErrorMsg() );
        }
        return $files;
    }

    //stream file to admin for free so no costs will be charged and the download figures won't be changed
    protected function freeDownload(){
        $cUser = Session::getInstance()->getVisitor();
        // check, if user's rights suffice
        if (!$cUser->hasRight('COURSE_FILE_ADMIN')) {
            $this->rightsMissingView('COURSE_FILE_ADMIN');
        }
        $fileID = InputValidator::getRequestData('file', null);
        // check, if id a valid file is specified
        if (!$fileID || $fileID <= 0 || null == ($file = CourseFileRevisionModel::getRevisionById($fileID))) {
            $this->errorView('Ungültige Datei ausgewählt.');
        }
        if ($file){
            // output via stream
            $this->fileView($file);
        } else{
            $this->errorView('Datei konnte nicht heruntergeladen werden.');
        }
    }
    
    
    //delete file completely including all versions/revisions
    protected function deleteFile(){
        $cUser = Session::getInstance()->getVisitor();
        // check, if user's rights suffice
        if (!$cUser->hasRight('COURSE_FILE_ADMIN')) {
            $this->rightsMissingView('COURSE_FILE_ADMIN');
        }
        $fileID = InputValidator::getRequestData('file', false);
        if (!$fileID || $fileID <= 0 || null == ($courseFile =  CourseFileModel::getCourseFileById($fileID))){
            $this->errorView('Datei konnte nicht geloescht werden.');
        }else{
            $courseFile->deleteFile();
       }
        $url = rewrite_admin(array('editFiles'=>true, 'extern' => true));
        header('Location: '.$url);
    }
    
    protected function deleteFileVersion(){
       $cUser = Session::getInstance()->getVisitor();
        // check, if user's rights suffice
        if (!$cUser->hasRight('COURSE_FILE_ADMIN')) {
            $this->rightsMissingView('COURSE_FILE_ADMIN');
        }
        
        $fileID = InputValidator::getRequestData('file', false);
        $file = CourseFileRevisionModel::getRevisionById($fileID);
        $courseFile = CourseFileModel::getCourseFileById($file->courseFileId);
        
         /* is the delete confirmend? */
        if (!array_key_exists('deleteConfirmation', $_REQUEST)) {
            return $this->confirmationView(NAME_FILE_DEL.': ' . $courseFile->getFileName(),
                                           DO_ACTION_FILE_DEL,
                                           '/index.php?mod=i_am_god&dest=modul&method=deleteFileVersion&file=' . $file->id . '&deleteConfirmation=yes',
                                           rewrite_course(array('courseFile'=>$courseFile)));        
        } 
        
        if (!$fileID || $fileID <= 0 || null == $file){
            $this->errorView('Datei konnte nicht geloescht werden.');
        }else{
            $file->deleteFile();                      
        }
        
        $url = rewrite_course(array('courseFile'=>$courseFile, 'extern' => true));
        header('Location: '.$url);
    }
        
    protected function editFiles(){
    //possible states from form: filter/filter_reset/save
        /* current user logged in user */
        $session = Session::getInstance();
        $cUser = $session->getVisitor();

        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'edit_files.tpl');
        
        /* is the user allowed to make a post */
        if(!$cUser->hasRight('COURSE_FILE_ADMIN')){
            $this->rightsMissingView('COURSE_FILE_ADMIN');
        }

        $id = InputValidator::getRequestData('id', $this->createID());
        $session_data = $session->getUserData($id);
        
        //in the very beginning there was the word...
        if ((!isset($_REQUEST['filter']) && !isset($_REQUEST['filter_reset']) && !isset($_REQUEST['save']))
            || !$session_data){
            $session_data = array();
            $session_data['coursefilescategories'] = CourseFileCategoryModel::getAllCategories();
            $session_data['coursefilessemesters'] = CourseFileSemesterModel::getAllSemesters();
            $session_data['coursefileratingcategories'] = CourseFileRatingCategoryModel::getAllCategories();
            $session_data['courses'] = CourseModel::getAllCourses();
            $session->storeUserData($id, $session_data);
            
            $filteredCategory = 0;
            $filteredSemester = 0;
            $filteredDescription = "";
            $filteredFilename = "";
            $filteredCourses = array();
            $coursefiles_orderstring = "";
            $coursefiles_orderDirstring = "";
            $this->setTemplateVars($main, array('filteredCategory' => $filteredCategory, 'filteredSemester'=>$filteredSemester,
                'filteredDescription' => $filteredDescription, 'filteredFilename'=>$filteredFilename, 'filteredCourses' => $filteredCourses,
                'coursefiles_orderstring'=>$coursefiles_orderstring, 'coursefiles_orderDirstring'=>$coursefiles_orderDirstring,
                'coursefilescategories'=>$session_data['coursefilescategories'], 'coursefilessemesters'=>$session_data['coursefilessemesters'],
                'coursefileratingcategories'=>$session_data['coursefileratingcategories'], 'id'=>$id, 'courses'=>$session_data['courses'] ));
        }
        elseif (isset($_REQUEST['filter_reset']) && $session_data && !array_key_exists(F_STATUS_SUBMITED, $session_data)){
            //reset all filters to default value
            $filteredCategory = 0;
            $filteredSemester = 0;
            $filteredDescription = "";
            $filteredFilename = "";
            $filteredCourses = array();
            $coursefiles_orderstring = "";
            $coursefiles_orderDirstring = "";
            $coursefilescategories = $session_data['coursefilescategories'];
            $coursefilessemesters = $session_data['coursefilessemesters'];
            $coursefileratingcategories = $session_data['coursefileratingcategories'];
            $courses = $session_data['courses'];
            $this->setTemplateVars($main, array('filteredCategory' => $filteredCategory, 'filteredSemester'=>$filteredSemester,
                'filteredDescription' => $filteredDescription, 'filteredFilename'=>$filteredFilename, 'filteredCourses' => $filteredCourses,
                'coursefiles_orderstring'=>$coursefiles_orderstring, 'coursefiles_orderDirstring'=>$coursefiles_orderDirstring,
                'coursefilescategories'=>$session_data['coursefilescategories'], 'coursefilessemesters'=>$session_data['coursefilessemesters'],
                'coursefileratingcategories'=>$session_data['coursefileratingcategories'], 'id'=>$id, 'courses'=>$session_data['courses']));
        }
        
        elseif (isset($_REQUEST['filter']) && $session_data && !array_key_exists(F_STATUS_SUBMITED, $session_data)){
            $filteredCategory = InputValidator::getRequestData('category', 0);
            $filteredSemester = InputValidator::getRequestData('semester', 0);
            $filteredDescription = InputValidator::getRequestData('description', "");
            $filteredFilename = InputValidator::getRequestData('filename', "");
            $filteredCourses = InputValidator::getRequestData('courseBox', array());
            $coursefiles_orderstring = InputValidator::getRequestData('order', "");
            $coursefiles_orderDirstring = InputValidator::getRequestData('orderDir', "");
            $coursefilescategories = $session_data['coursefilescategories'];
            $coursefilessemesters = $session_data['coursefilessemesters'];
            $coursefileratingcategories = $session_data['coursefileratingcategories'];
            $courses = $session_data['courses'];
            
            $session_data['filteredCategory'] = $filteredCategory;
            $session_data['filteredSemester'] = $filteredSemester;
            $session_data['filteredDescription'] = $filteredDescription;
            $session_data['filteredFilename'] = $filteredFilename;
            $session_data['filteredCourses'] = $filteredCourses;
            $session_data['coursefiles_orderstring'] = $coursefiles_orderstring;
            $session_data['coursefiles_orderDirstring'] = $coursefiles_orderDirstring;
            //retrieve files
            $files = $this->retrieveFiles($session_data);
            //store files in session
            $session_data['coursefiles'] = $files;
            
            $session->storeUserData($id, $session_data);
            $this->setTemplateVars($main, array(
                'filteredCategory' => $filteredCategory, 'filteredSemester'=>$filteredSemester,
                'filteredDescription' => $filteredDescription, 'filteredFilename'=>$filteredFilename, 'filteredCourses' => $filteredCourses,
                'coursefiles_orderstring'=>$coursefiles_orderstring, 'coursefiles_orderDirstring'=>$coursefiles_orderDirstring,
                'coursefilescategories'=>$session_data['coursefilescategories'], 'coursefilessemesters'=>$session_data['coursefilessemesters'],
                'coursefileratingcategories'=>$session_data['coursefileratingcategories'], 'id'=>$id, 'startOutput'=>true, 
                'courses'=>$session_data['courses'], 'coursefiles' => $files)
            );          
        }               
        elseif (isset($_REQUEST['save']) && $session_data && !array_key_exists(F_STATUS_SUBMITED, $session_data) ){
            $coursesFiles = $session_data['coursefiles'];
            foreach ($coursesFiles as $courseFile){
                $fileTouched = false;
                $delFile = (isset($_REQUEST['deleteCoursefile_'.$courseFile->id]));
                if ($delFile){
                    try{
                        $courseFile->deleteFile();
                        $fileTouched = true;                        
                    }
                    catch (DBException $e){
                        $this->errors['deleteCoursefile_'.$courseFile->id] = 'Konnte Datei nicht loeschen; '.$e->getMessage();
                    }
                }
                
                $tmp = (isset($_REQUEST['costs_'.$courseFile->id]) ? $_REQUEST['costs_'.$courseFile->id] : -1);
                if ($tmp != $courseFile->getCosts()){
                    $fileTouched = true;
                }
                $courseFile->setCosts( ($tmp >= 1 && $tmp <= 10) ? $tmp : V_COURSE_FILES_DEFAULT_COSTS );               
                
                $tmp = trim((isset($_REQUEST['description_'.$courseFile->id]) ? $_REQUEST['description_'.$courseFile->id] : ""));
                if (strcasecmp($tmp, trim($courseFile->getDescription()))==0){
                    $fileTouched = true;
                }
                $courseFile->setDescription($tmp);
                
                $tmp = (isset($_REQUEST['semester_'.$courseFile->id]) ? $_REQUEST['semester_'.$courseFile->id] : "");
                if (strcasecmp($tmp, $courseFile->getSemesterId())==0){
                    $fileTouched = true;
                }               
                if ( in_array( $tmp, CourseFileSemesterModel::getAllSemesterIDs() ) ){
                    $courseFile->setSemester($tmp);
                }else{
                    $this->addIncompleteField($this->errors, 'semester_'.$courseFile->id, ERR_FORM_NOT_VALID);
                }
                
                $tmp = (isset($_REQUEST['category_'.$courseFile->id]) ? $_REQUEST['category_'.$courseFile->id] : "");
                if (strcasecmp($tmp, $courseFile->getCategoryId())==0){
                    $fileTouched = true;
                }               
                if ( in_array($tmp, CourseFileCategoryModel::getAllCategoryIDs()) ){
                    $courseFile->setCategory($tmp);
                } else{
                    $this->addIncompleteField($this->errors, 'category_'.$courseFile->id, ERR_FORM_NOT_VALID);
                }
                
                $tmp = (isset($_REQUEST['course_'.$courseFile->id]) ? $_REQUEST['course_'.$courseFile->id] : "");
                if ( in_array($tmp, CourseModel::getAllCoursesIds()) ){
                    $courseFile->setCourse($tmp);
                } else{
                    $this->addIncompleteField($this->errors, 'course_'.$courseFile->id, ERR_FORM_NOT_VALID);
                }
                
                if (count($this->errors) == 0 && !$delFile && $fileTouched) {
                    try {    
                        $courseFile->save();
                    } catch (DBException $e) {
                        // TODO: error handling
                        $this->errors['courseFile_'.$courseFile->id] = $e->getMessage();
                    }
                }
            }
            //everything was fine
            if (count($this->errors)==0){
                //re-retrieve the now changed files
                $files = $this->retrieveFiles($session_data);
                $this->setTemplateVars($main, array(
                    'filteredCategory' => $session_data['filteredCategory'], 
                    'filteredSemester'=> $session_data['filteredSemester'],
                    'filteredDescription' => $session_data['filteredDescription'], 
                    'filteredFilename'=> $session_data['filteredFilename'], 
                    'filteredCourses' => $session_data['filteredCourses'],
                    'coursefiles_orderstring'=> $session_data['coursefiles_orderstring'], 
                    'coursefiles_orderDirstring'=> $session_data['coursefiles_orderDirstring'],
                    'coursefilescategories'=>$session_data['coursefilescategories'], 
                    'coursefilessemesters'=>$session_data['coursefilessemesters'],
                    'coursefileratingcategories'=>$session_data['coursefileratingcategories'], 
                    'id'=>$id, 
                    'startOutput'=>true, 
                    'courses'=>$session_data['courses'], 
                    'coursefiles' => $files, 
                    'ackNeeded'=> true)
                );
                $session->deleteUserData($id);
                unset($session_data);
                $session_data = array();
                $session_data[F_STATUS_SUBMITED] = true;
                $session->storeUserData($id, $session_data);                
            }else{          
                $this->setTemplateVars($main, array('errors'=>'Fehler beim Ändern der Daten aufgetreten.',
                    'filteredCategory' => $session_data['filteredCategory'], 'filteredSemester'=> $session_data['filteredSemester'],
                    'filteredDescription' => $session_data['filteredDescription'], 
                    'filteredFilename'=> $session_data['filteredFilename'], 'filteredCourses' => $session_data['filteredCourses'],
                    'coursefiles_orderstring'=> $session_data['coursefiles_orderstring'], 
                    'coursefiles_orderDirstring'=> $session_data['coursefiles_orderDirstring'],
                    'coursefilescategories'=>$session_data['coursefilescategories'], 
                    'coursefilessemesters'=>$session_data['coursefilessemesters'],
                    'coursefileratingcategories'=>$session_data['coursefileratingcategories'], 'id'=>$id, 'startOutput'=>true, 
                    'courses'=>$session_data['courses'], 'coursefiles' => $files, 'ackNeeded'=> true)
                );
                $session->deleteUserData($id);
                unset($session_data);
                $session_data = array();
                $session_data[F_STATUS_SUBMITED] = true;
                $session->storeUserData($id, $session_data);
            }
        }else{
            $session_data = array();
            $session_data['coursefilescategories'] = CourseFileCategoryModel::getAllCategories();
            $session_data['coursefilessemesters'] = CourseFileSemesterModel::getAllSemesters();
            $session_data['coursefileratingcategories'] = CourseFileRatingCategoryModel::getAllCategories();
            $session_data['courses'] = CourseModel::getAllCourses();
            $session->storeUserData($id, $session_data);
            
            $filteredCategory = 0;
            $filteredSemester = 0;
            $filteredDescription = "";
            $filteredFilename = "";
            $filteredCourses = array();
            $coursefiles_orderstring = "";
            $coursefiles_orderDirstring = "";
            $this->setTemplateVars($main, array('filteredCategory' => $filteredCategory, 'filteredSemester'=>$filteredSemester,
                'filteredDescription' => $filteredDescription, 'filteredFilename'=>$filteredFilename, 'filteredCourses' => $filteredCourses,
                'coursefiles_orderstring'=>$coursefiles_orderstring, 'coursefiles_orderDirstring'=>$coursefiles_orderDirstring,
                'coursefilescategories'=>$session_data['coursefilescategories'], 'coursefilessemesters'=>$session_data['coursefilessemesters'],
                'coursefileratingcategories'=>$session_data['coursefileratingcategories'], 'id'=>$id, 'courses'=>$session_data['courses'] ));
        }   
        $this->setCentralView($main, false);
        parent::view();
    }
    
    protected function coursesMerge() {
        $cUser = Session::getInstance()->getVisitor();
        
        if (!$cUser->hasRight('COURSE_ADMIN')){
            $this->rightsMissingView('COURSE_ADMIN');
        }
        
        $success = false;
        
        if (array_key_exists('merge', $_REQUEST)) {
            $this->addLog('merge courses');
            
            $course1 = CourseModel::getCourseById(InputValidator::getRequestData('course1', 0));
            $course2 = CourseModel::getCourseById(InputValidator::getRequestData('course2', 0));
            
            $DB = Database::getHandle();
            
            $DB->StartTrans();
            
            $forum1 = $course1->getForum();
            $forum2 = $course2->getForum();
            
            ForumModel::replaceForum($forum2, $forum1);
            ThreadModel::replaceForum($forum2, $forum1);
            
            CourseFileModel::replaceCourse($course2, $course1);
            
            $course1->absorb($course2);
            
            $course2->delete();
            
            $DB->CompleteTrans();
            
            $success = true;
       }
       
       $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'courses_merge.tpl');
       
       $courses = CourseModel::getAllCourses();
       $main->assign('courses', $courses);
       $main->assign('success', $success);
       
       $this->setCentralView($main, false);
       $this->view();
    }
    
    protected function editBanner(){    	
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'banner.tpl'); 
    	
        /* get the current user */
        $cUser = Session::getInstance()->getVisitor();
        
        if(!$cUser->hasRight('BANNER_ADMIN')) {
            $this->rightsMissingView('BANNER_ADMIN');
        }
        
        if(array_key_exists('save',$_POST)){
        	
            /* is a entry text given? */
            if( !array_key_exists('name', $_REQUEST) && !array_key_exists('banner_url', $_REQUEST)) {
                $this->errorView(ERR_BANNER_INSUFFICIENT_INFORMATION);
            }
            
            $data = array();
            
            /* checks */
            
             $formFields = array(
            'banner_url'   => array('required' => true, 'check' => 'isValidURL', 'escape' => false),
            'banner_path'   => array('required' => false, 'check' => 'isValidURL', 'escape' => false),
            'banner_name'  => array('required' => true, 'check' => 'isValidByLength', 'params' => array('lengthLo' => 5, 'lengthHi' => 100), 'escape' => true),
            'banner_rot'   => array('required' => true, 'check' => 'isValidInteger', 'params' => array('lo' => 0 , 'hi' => 100) )                    
             );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);  
            
            if(count($this->errors) > 0){
                $this->errorView($this->errors['missingFields']);
            }                                  
            
            $data['name'] = $_POST['banner_name'];
            $data['dest_url'] = $_POST['banner_url'];
            
            $extern = false;
            if(!empty($_POST['banner_path'])){
            	$data['banner_url'] = $_POST['banner_path'];
                $extern = true;
            }
            
            /* bulid the target time */
            $startTime = parent::getSmartyDate($_REQUEST, 'start');
            $endTime = parent::getSmartyDate($_REQUEST, 'end');
            if (empty($startTime)) {
                $this->errorView(ERR_BANNER_ADMIN_STARTDATE);
            }
            if (empty($endTime)) {
                $this->errorView(ERR_BANNER_ADMIN_ENDDATE);
            }
            
            if(!InputValidator::isValidFutureDate($startTime)){
            	$this->errorView(ERR_BANNER_ADMIN_STARTDATE);
            }
            if(!InputValidator::isValidFutureDate($endTime)){
                $this->errorView(ERR_BANNER_ADMIN_ENDDATE);
            }
            
            $data['start_date'] = $startTime;
            $data['end_date'] = $endTime;
            $data['is_visible'] = array_key_exists('isVisible', $_REQUEST);
            
            $data['post_ip'] = ClientInfos::getClientIP();            
            $data['random_rate'] = $_POST['banner_rot'];
            //var_dump($data);
            
            /* edit changes */
            if(array_key_exists('bannerId',$_REQUEST)){
            	$banner = BannerModel::getBannerById($_REQUEST['bannerId']);
                $banner->setValues($data);
                //var_dump($banner);
            /* add data to model */    
            }else{
                $banner = new BannerModel($data);
                $banner->setAuthor($cUser);

                $maxAttachmentSize = V_BANNER_FILE_SIZE;

                if ($_FILES['file_attachment1']['size'] && !$extern) {
    
                    // username of session object should not induce security risk
                    // this username is contrainted by the database to digits and chars
                    $atm = AttachmentHandler :: handleAttachment($_FILES['file_attachment1'], 
                        AttachmentHandler::getAdjointPath(Session :: getInstance()->getVisitor()), true, $maxAttachmentSize);
                    
                    if($atm == null){
                    	$this->errorView(ERR_BANNER_ADMIN_FILE);
                    }
                    
                    //TODO: test filesize of banner and if it is a image
                    
                    // add attachment to object
                    $banner->setBannerFile($atm);
                }
                elseif(!$extern){
                	$this->errorView(ERR_BANNER_ADMIN_NO_FILE);
                }  
                    
            }            
            
            /* save model */
            $banner->save();
        }
        /* end save */
        
        /* show values */
        if(array_key_exists('showValue',$_REQUEST) && array_key_exists('bannerId',$_REQUEST)){
        	$bannerToEdit = BannerModel::getBannerById($_REQUEST['bannerId']);
            $main->assign('bannerToEdit',$bannerToEdit);
            //var_dump($bannerToEdit);
        }
        
        $bannerList = BannerModel::getAllBanners();
        //var_dump($bannerList[0]->bannerFile);
        
        $main->assign('banners',$bannerList);        
        
        $this->setCentralView($main, false);
        $this->view();
    }
    
    protected function editTag(){
    	
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'tag.tpl');
        
        /* get the current user */
        $cUser = Session::getInstance()->getVisitor();

        if(!$cUser->hasRight('TAG_ADMIN')) {
            $this->rightsMissingView('TAG_ADMIN');
        }
        
        if(array_key_exists('showValue',$_REQUEST) && array_key_exists('tagId',$_REQUEST)){
        	$tagToEdit = TagModel::getTagById($_REQUEST['tagId']);
            if($tagToEdit == null)
                $this->errorView('Tag doesnt exist');
            $main->assign('tagToEdit', $tagToEdit);
        }
        
        if(array_key_exists('save',$_POST)){
        	if(array_key_exists('tagId',$_REQUEST)){
        		$newTag = TagModel::getTagById($_REQUEST['tagId']);
        	}
            else{
                $newTag = new TagModel;
            }
            
            if(empty($_REQUEST['showEntryValues'])){
            $formFields = array(        
            'tagName' => array('required' => true, 'check' => 'isValidName', 'escape' => false),            
             );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);
            }
            
            if(count($this->errors) > 0){
            	$this->errorView(ERR_TAG_ADMIN);
            }

            $newTag->setName($_POST['tagName']);
            $newTag->save();
        }
        
        $tags = TagModel::getAllTags();
        $main->assign('tags', $tags);
        
        $this->setCentralView($main, false);
        $this->view();
    }
    
    protected function editFeatures(){
    	$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'edit_features.tpl');
        
        /* get the current user */
        $cUser = Session::getInstance()->getVisitor();
        if(!$cUser->hasRight('FEATURE_ADMIN')){
            $this->rightsMissingView('FEATURE_ADMIN');
        }
        
        if(array_key_exists('featId',$_REQUEST)){
        	 $feat = FeatureModel::getFeatureById($_REQUEST['featId']);
             if($feat == null){
             	throw ArgumentException('featId', $_REQUEST['featId']);
             }
             
             if(array_key_exists('save',$_POST)){
                $feat->setDescription($_REQUEST['desc']);
                $feat->setDescriptionEnglish($_REQUEST['desc_eng']);
                $feat->setPointLevel($_REQUEST['pointLevel']);
                $feat->setPictureURL($_REQUEST['pic_url']);
                $feat->save();	
             }
             else{
                $main->assign('editFeat',$feat);
             }
        }
        if(array_key_exists('newFeatId',$_REQUEST)){

            $newFeatR = RightModel::getRightById($_REQUEST['newFeatId']);

            if($newFeatR == null){
            	throw new ArgumentException('newFeatId', $_REQUEST['newFeatId']);
            }

            if(array_key_exists('save', $_POST)){
            	$newFeat = new FeatureModel( null, $newFeatR->getName() , $_REQUEST['pointLevel']);
                $newFeat->setRightId($newFeatR->id);
                $newFeat->setDescription($_REQUEST['desc']);
                $newFeat->setDescriptionEnglish($_REQUEST['desc_eng']);
                $newFeat->setPictureURL($_REQUEST['pic_url']);
                //var_dump($newFeat);
                $newFeat->save();
            }
            else{            	
                //var_dump($newFeat);            
                $main->assign('newFeat',$newFeatR);
            }            
        }
        
        $features = FeatureModel::getAllFeatures();                     
        $main->assign('features', $features);
        
        if(array_key_exists('addFeat',$_REQUEST)){
            $nonFeatures = FeatureModel::getAllNonFeatures();
            $main->assign('nonFeatures', $nonFeatures);
        }
        
        $this->setCentralView($main, false);
        $this->view();
    }
    
    protected function editPointSources(){
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'edit_point_sources.tpl');
        
        /* get the current user */
        $cUser = Session::getInstance()->getVisitor();
        if (!$cUser->hasRight('POINT_SOURCE_ADMIN')){
            $this->rightsMissingView('POINT_SOURCE_ADMIN');
        }
        
        $pointSources = PointSourceModel::getAllPointSources();
        
        if (array_key_exists('changePointSource',$_REQUEST)) {
            foreach ($_POST as $k => $val) {
                if (substr($k,0,5) == 'pssum' and array_key_exists(substr($k,5), $pointSources)) {
             		$pointSources[substr($k,5)]->setPointsSum($val);
             	} else if (substr($k,0,6) == 'psflow' and array_key_exists(substr($k,6), $pointSources)) {
                    $pointSources[substr($k,6)]->setPointsFlow($val);
                }
            }
            foreach ($pointSources as $ps) {
            	$ps->save();
            } 
        }
                            
        $main->assign('pointsources', $pointSources);        
        
        $this->setCentralView($main, false);
        $this->view();
    }
    
    protected function generateSmileyArray() {
    	/* get the current user */
        $cUser = Session::getInstance()->getVisitor();
        if (!$cUser->hasRight('SMILEY_ADMIN')) {
            $this->rightsMissingView('SMILEY_ADMIN');
        }
        
        $f = fopen(SMILEY_INCLUDE_FILE, 'w');
        if (!$f or !flock($f, LOCK_EX)) {
        	throw new CoreException(Logging::getErrorMessage(FILE_FILE_NOT_FOUND, SMILEY_INCLUDE_FILE));
        }
        
        $DB = Database::getHandle();
        
        $q = '  (SELECT text AS t, length(text) AS l, url
                   FROM ' . DB_SCHEMA . '.smileys) 
             UNION 
                (SELECT text_alternative AS t, length(text_alternative) as l, url
                  FROM ' . DB_SCHEMA . '.smileys) 
          ORDER BY l ASC';
        $res = $DB->Execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        fwrite($f, "<?php\n");
        fwrite($f, '$smileys = array();' . "\n");
        fwrite($f, '$tags = array();' . "\n");
        foreach ($res as $row) {
        	fprintf($f, '$smileys[] = \'%s\';' . "\n", $row['t']);
            // avoid substitution of smileys in alt-text
            $altText = str_replace(array('(', ')', ':'), array('&#40;', '&#41;', '&#58;'), $row['t']); 
            $smileyTag = '<img alt="' . $altText . '" alt="' . $altText . '" src="' . SMILEY_URL . '/' . $row['url'] . '" />';
            fprintf($f, '$tags[] = \'%s\';' . "\n", $smileyTag);
        }        
        fwrite($f, "?>\n");
        
        flock($f, LOCK_UN);
        fclose($f);
    }
    
    protected function generateGlobalSettingsArray() {
        /* get the current user */
        $cUser = Session::getInstance()->getVisitor();
        if (!$cUser->hasRight('GLOBAL_SETTINGS_ADMIN')) {
            $this->rightsMissingView('GLOBAL_SETTINGS_ADMIN');
        }
        
        $f = fopen(GLOBAL_SETTINGS_INCLUDE_FILE, 'w');
        if (!$f or !flock($f, LOCK_EX)) {
            throw new CoreException(Logging::getErrorMessage(FILE_FILE_NOT_FOUND, GLOBAL_SETTINGS_INCLUDE_FILE));
        }
        
        $DB = Database::getHandle();
        
        $q = 'SELECT config_name, config_value
                FROM ' . DB_SCHEMA . '.global_config';
        $res = $DB->Execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        fwrite($f, "<?php\n");
        fwrite($f, '$globalSettings = array();' . "\n");
        foreach ($res as $row) {
            fprintf($f, '$globalSettings[\'%s\'] = \'%s\';' . "\n", $row['config_name'], $row['config_value']);
        }        
        fwrite($f, "?>\n");
        
        flock($f, LOCK_UN);
        fclose($f);
    }
    
    
    protected function showEmailLog(){

        /* get the current user */
        $cUser = Session::getInstance()->getVisitor();
        if (!$cUser->hasRight('PROFILE_ADMIN')) {
            $this->rightsMissingView('PROFILE_ADMIN');
        }

        $page = array_key_exists('page',$_REQUEST) ? $_REQUEST['page'] : 1;
        
        if(array_key_exists('search',$_POST)){
        	$mails = UserMailModel::getLogsBySubstring($_POST['emailsearch'],31, ($page-1)*30);
        }
        else{
            $mails = UserMailModel::getAllLogs(31, ($page-1)*30);
        }
        
        
        if (count($mails) > 30) {
            $mails = array_splice($mails, 0, -1);
            $nextPage = true;
        } else {
        	$nextPage = false;
        }
        
        //var_dump($mails);
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'email_log.tpl');
        
        $main->assign('mails', $mails);
        $main->assign('page', $page);
        $main->assign('nextPage', $nextPage);
        
        if(array_key_exists('mailId',$_REQUEST)){
        	$showMail = UserMailModel::getById($_REQUEST['mailId']);
            $main->assign('showMail',$showMail);
        }
        if(array_key_exists('sendId',$_REQUEST)){
        	$sentMail = UserMailModel::getById($_REQUEST['sendId']);
            $email = $sentMail->getMailTo();
            if(array_key_exists('newEmail',$_POST)){
                if(InputValidator::isValidMail($_POST['newEmail'])){
                	$email = $_POST['newEmail'];
                }
            }
            
            Mailer::sendAgainMail($email , $sentMail->getMailSubject(), $sentMail->getMailBody());
        }
        
        $this->setCentralView($main, false);
        $this->view();
    }
    
    protected function showStats(){
        $cUser = Session::getInstance()->getVisitor();
        
         /* get the current user */
         $cUser = Session::getInstance()->getVisitor();
         if (!$cUser->hasRight('ACCESS_STATS')) {
            $this->rightsMissingView('ACCESS_STATS');
         }
         
         $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), ADMIN_TEMPLATE_DIR . 'show_stats.tpl');
         $main->assign('study_paths', StudyPathModel::getAllStudyPaths());
         $main->assign('unis',UniversityModel::getAllUniversities());
         
         if (array_key_exists('username', $_REQUEST)) {
            $usernameString = $_REQUEST['username'];
            $studyPath = (isset($_POST['study_path'])) ? $_POST['study_path'] : '0';
            $gender = (isset($_POST['gender'])) ? $_POST['gender'] : '0';
            $flirtStatus = (isset($_POST['flirt_status'])) ? $_POST['flirt_status'] : '0';
            $picture = (isset($_POST['picture'])) ? $_POST['picture'] : '0';
            $uni = (isset($_POST['study_place'])) ? $_POST['study_place'] : '0';
            
            $searchResults = UserMainDataModel::CountSearchUserAdvanced($usernameString, $studyPath, $gender, $flirtStatus, $picture, $uni);
            if($searchResults == 0){
            	$searchResults = 'keinen';
            }       
                   
                   
            $searchValues['username'] = $usernameString;
            $searchValues['study_path'] = $studyPath;
            $searchValues['gender'] = $gender;
            $searchValues['flirt_status'] = $flirtStatus;
            $searchValues['picture'] = $picture;
            $searchValues['study_place'] = $uni;
            $main->assign('searchValues',$searchValues);
            $main->assign('searchCount', $searchResults);
         }
         
         $main->assign('latest_user_number',UserProtectedModel::countUser());
         $main->assign('user_number_female',UserProtectedModel::countUser(array('gender'=>'f')));
         $main->assign('user_number_male',UserProtectedModel::countUser(array('gender'=>'m')));
         
         $this->setCentralView($main, false);
         $this->view();
    }
    
    
}
