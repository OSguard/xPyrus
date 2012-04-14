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
 * Created on 10.07.2006 by schnueptus
 * sunburner Unihelp.de
 */
require_once CORE_DIR . '/businesslogic/business_logic_controller.php';

require_once CORE_DIR.'/models/base/group_model.php';

require_once CORE_DIR . '/utils/client_infos.php';
require_once CORE_DIR . '/utils/attachment_handler.php';

require_once MODEL_DIR . '/forum/forum_read.php'; 

// load some useful constants for working on <form>-input
require_once CORE_DIR . '/constants/form_constants.php';
require_once CORE_DIR . '/constants/value_constants.php';

require_once CORE_DIR . '/utils/global_ipc.php';

class GroupsBusinessLogicController extends BusinessLogicController {
    public function __construct($ajaxView = false) {
        parent::__construct($ajaxView); 
    }

   /** 
     * Defoult Methode: we want to see the Overview of all Forums 
     */
    protected function getDefaultMethod() {
        return 'viewAllGroups';
    }
    
    /**
      * List of al methods who ar allowed
      */
    public function getAllowedMethods() {
        $array = parent::getAllowedMethods();
        array_push($array, 
                   'viewAllGroups',
                   'viewGroupInfoPage',
                   
                   'editGroup',
                   'editGroupInfopage',
                   'groupApplication',
                   'leaveGroup');
        return $array;
      }
    
    public function getMethodObject($method) {
        $parameters = $this->getParameters($method);
        if ('viewAllGroups' == $method) {
            return new BLCMethod(NAME_GROUPS, rewrite_group(array()), BLCMethod::getDefaultMethod());
        } else if ('viewGroupInfoPage' == $method) {
            return new BLCMethod(htmlspecialchars($parameters['group']->getTitle() . ' ' . $parameters['group']->getName()),
                rewrite_group(array('group' => $parameters['group'])),
                $this->getMethodObject('viewAllGroups'));
        } else if ('editGroup' == $method){
        	$this->copyParameters('editGroup', 'viewGroupInfoPage');
            $parent = $this->getMethodObject('viewGroupInfoPage');
            return new BLCMethod(htmlspecialchars(NAME_EDIT.' '.$parameters['group']->getTitle() . ' ' . $parameters['group']->getName()),
                rewrite_group(array('groupToEdit' => $parameters['group']->id)),
                $parent);
        } else if ('editGroupInfopage' == $method){
        	$this->copyParameters('editGroupInfopage', 'viewGroupInfoPage');
            $parent = $this->getMethodObject('viewGroupInfoPage');
            return new BLCMethod(htmlspecialchars(NAME_EDIT.' '. $parameters['group']->getName()),
                rewrite_group(array('editInfo' => $parameters['group']->id)),
                $parent);
        } else if ('groupApplication' == $method){
            $this->copyParameters('groupApplication', 'viewGroupInfoPage');
            $parent = $this->getMethodObject('viewGroupInfoPage');
            return new BLCMethod(NAME_APPLICATION,
                rewrite_group(array('applicationId' => $parameters['group']->id)),
                $parent);
        } else if ('leaveGroup' == $method){
            $this->copyParameters('leaveGroup', 'viewGroupInfoPage');
            $parent = $this->getMethodObject('viewGroupInfoPage');
            return new BLCMethod(NAME_LEAVE,
                rewrite_group(array('leaveId' => $parameters['group']->id)),
                $parent);
        }
        
        return parent::getMethodObject($method);
    }
    
    protected function collectParameters($method) {
        $parameters = array();
        if ('viewGroupInfoPage' == $method) {
            $parameters['group'] = GroupModel::getGroupById(InputValidator::getRequestData('groupId', 0));
        }elseif ('editGroup' == $method) {
            $parameters['group'] = GroupModel::getGroupById(InputValidator::getRequestData('groupId', 0));
        }elseif ('editGroupInfopage' == $method) {
            $parameters['group'] = GroupModel::getGroupById(InputValidator::getRequestData('groupId', 0));
        }elseif ('groupApplication' == $method) {
            $parameters['group'] = GroupModel::getGroupById(InputValidator::getRequestData('groupId', 0));
        }elseif ('leaveGroup' == $method) {
            $parameters['group'] = GroupModel::getGroupById(InputValidator::getRequestData('groupId', 0));
        }
        $this->_parameters[$method] = $parameters;
        
        parent::collectParameters($method);
    }
    
    protected function viewAllGroups(){
        
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/groups/overview.tpl');
        
        $cacheKey = 'groups';
        $main->setCacheParameter(-1, $cacheKey);
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        //$cUser = Session::getInstance()->getVisitor();
        self::observeIPC(
            new GlobalIPC, array('GROUP_CHANGED'), $main, $cacheKey);
        
        if (!$main->isCached()) {
            $groupList = GroupModel::getAllGroups();
            //var_dump($groupList[1]->members);
            $main->assign('groups', $groupList);
        }
        
        $this->setCentralView($main, true, false);
        $this->view();
    }
    
    protected function viewGroupInfoPage($show = true, $viewGroup = null){
        $parameters = $this->getParameters('viewGroupInfoPage');
        
        if (!$parameters['group']) {
            $this->errorView(ERR_NO_GROUP);
        }
        
        $groupId = $_REQUEST['groupId'];
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/groups/infopage.tpl');
         /* get the current user */
        $cUser = Session::getInstance()->getVisitor();
        
        $cacheKey = 'groups';
        $main->setCacheParameter(-1, $cacheKey . '|' . $groupId);
        
        // don't cache, if we have a group model given
        if (defined('CACHETEST') and $viewGroup == null) {
            $main->enableCaching();
        }
        
        self::observeIPC(
            new GlobalIPC, array('GROUP_CHANGED'), $main, $cacheKey);
        
        if ($viewGroup != null) {
            $group = $viewGroup;
        } else {
            $group = $parameters['group'];
        }
        
        // always fetch and assign group model 
        $main->assign('group', $group);
        $main->assign('threads',ThreadModel::getAllThreadsByForumId($group->getForum()->id, 5));
        
        $read = ForumRead::getInstance($cUser);
        $main->assign('forumRead', $read);
        
        $this->setCentralView($main);
        // no output return smarty instance 
        if (!$show) {
            return $main;
        }
        $this->view();
    }
    
     protected function editGroup($show = true, $viewGroup = null){
     	$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/groups/edit_group.tpl');
        
        if(!array_key_exists('groupId', $_REQUEST)){
            $this->errorView(ERR_NO_GROUP);
        }
        
        $groupId = $_REQUEST['groupId'];
        
        if($viewGroup == null){
            $group = GroupModel::getGroupById($groupId);
        }
        else{
            $group = $viewGroup;
        }
        
        if($group == null){
            $this->errorView(ERR_NO_GROUP);
        }
        
        $cUser = Session::getInstance()->getVisitor();
        if( !( $cUser->hasGroupRight('GROUP_OWN_ADMIN', $group->id) || $cUser->hasRight('GROUP_ADMIN') ) ){
            $this->rightsMissingView('GROUP_OWN_ADMIN');
        }
        
    	if(!$cUser->hasGroupRight('GROUP_OWN_ADMIN', $group->id) ){
    		$this->addLog('change on Group');
    	}
        
        /*search new member */
        if (array_key_exists('username', $_REQUEST)){
            $username = $_REQUEST['username'];
            
            $newmembers = UserProtectedModel::searchUser($username);
                      
            $main->assign('newMemberList', $newmembers);
            $main->assign('newSearchMember', $username);
        }
        /* add user to group */
        if(array_key_exists('userToAdd', $_REQUEST)) {
            $user = UserProtectedModel::getUserById($_REQUEST['userToAdd']);
            $group->addUsers(array($user));
            
            // announce change via IPC
            self::notifyIPC(new UserIPC($user->id), 'GROUPS_CHANGED');
            self::notifyIPC(new GlobalIPC, 'GROUP_CHANGED');
            
            /*
             * notive the user that he was added
             */
            require_once CORE_DIR . '/models/pm/pm_entry_model.php';
            $text = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'mail/group_add.tpl');
            $text->assign('user', $user);
            $text->assign('group', $group);
                   
            // send admin PM, 
            $sentTo = array($user);
            if(count($sentTo) > 0){
                $success = PmEntryModel::sendSystemPm($text->fetch(), CAPTION_GROUP_APPLICATION,
                    'undisclosed recipients', $sentTo, true);
            } 
            
        }
        /* User remove form Group */
        if(array_key_exists('userToDel', $_REQUEST)) {
            $user = UserProtectedModel::getUserById($_REQUEST['userToDel']);
            $group->delUsers(array($user));
            $user->removeFromUserOnlineList();
            
            // announce change via IPC
            self::notifyIPC(new UserIPC($user->id), 'GROUPS_CHANGED');
            self::notifyIPC(new GlobalIPC, 'GROUP_CHANGED');
        }
        /* set UserGroupRights of a user */
        if(array_key_exists('setRights', $_REQUEST)){
        	$user = UserProtectedModel::getUserById($_REQUEST['setRights']);
            $groupUserRights = RightModel::getAllExplicitUserGroupRightsByUser($user);
            $groupRights = RightModel::getAllExplicitRightsByGroup($group);

            $main->assign('member',$user);
            $main->assign('groupUserRights',$groupUserRights);
            $main->assign('groupRights', $groupRights);
        }
        
        /* group Right should save */
        if(array_key_exists('saveGroup', $_REQUEST)) {
            $user = UserProtectedModel::getUserById($_REQUEST['userId'], false);
            $group = GroupModel::getGroupById($_REQUEST['groupId']);
            
            if($user == null){
                $this->errorView(ERR_NO_USER);
            }
            
            if($group == null){
                $this->errorView(ERR_NO_GROUP);
            }
            
            $groupRightsGranted = array();
            if(array_key_exists('granted', $_REQUEST)){
                $groupRightsGranted = $_REQUEST['granted'];
            }
                
            //var_dump($groupRightsGranted);   
            
            RightModel::setUserGroupRights($user->id, $group->id, $groupRightsGranted);
            $main->assign('rightsHaveBeenSet', true);
            
            // enforce rights reload for user just worked at
            $user->removeFromUserOnlineList();
        }
        /* remove logo of group */
        if (array_key_exists('logo_delete', $_POST)) {
            AttachmentHandler::removeGroupPicture($group);
        }
        /* upload a new logo */
        else if(array_key_exists('logo_picture', $_FILES) && $_FILES['logo_picture']['size']) {
            $upload = AttachmentHandler::handleGroupPicture($group, $_FILES['logo_picture'],
                        AttachmentHandler::getAdjointPath2($group), true, 204800,
                        array('big_maxwidth'=>640,'big_maxheight'=>480,
                              'tiny_maxwidth'=>50,'tiny_maxheight'=>60));
            if ($upload == AttachmentHandler::SUCCESS) {
                self::notifyIPC(new GlobalIPC, 'GROUP_CHANGED');
                $group->save();
            }
        }
        
        $main->assign('group',$group);
        
        $this->setCentralView($main);
        /* no output return smarty instance */
        if(!$show){
            return $main;
        }
        $this->view();
     }
    
    /**
     * edit the infopage
     */
    protected function editGroupInfopage(){
    	/* infopage need to save */
        $toSave = array_key_exists('save',$_REQUEST);
        /* remove the changes (preview) */
        //$noSave = array_key_exists('nosave',$_REQUEST);
        $noSave = false; // by linap, see template
        /* just preview */
        $toPreview = array_key_exists('preview',$_REQUEST);
        
        $parameters = $this->getParameters('editGroupInfopage');
        
        if (null == $group = $parameters['group']) {
            $this->errorView(ERR_NO_GROUP);
        }
        
        $cUser = Session::getInstance()->getVisitor();
        
        if( !( $cUser->hasGroupRight('GROUP_INFOPAGE_EDIT', $group->id) || $cUser->hasRight('GROUP_INFOPAGE_ADMIN') ) ){
        	$this->rightsMissingView('GROUP_INFOPAGE_EDIT');
        }
        
    	if(!$cUser->hasGroupRight('GROUP_INFOPAGE_EDIT', $group->id) ){
    		$this->addLog('change Infopage of Group');
    	}
	
        $infopage = $group->getInfopage();
        
        if (!$noSave and ($toSave or $toPreview)) {
        	$infopage->setParseSettings(self::getParseSettings());
            
            /* get the content and add */
            $infopage->setContentRaw($_REQUEST['entryText']);
            
            if (strlen($_POST['entryText']) > V_ENTRY_MAX_CHARS) {
                self::addIncompleteField($this->errors, 'entryText', ERR_ENTRY_TOO_LONG);
            }
            
            if ($toSave and count($this->errors) == 0){
                self::notifyIPC(new GlobalIPC, 'GROUP_CHANGED');
                
                $infopage->save();
                header('Location: '.rewrite_group(array('group'=>$group)));
                return;
            }
        }
        
        $main = $this->viewGroupInfoPage(false , $group);
        $main->assign('editMode', (!$toSave || $noSave) || count($this->errors) > 0);
        
        $this->view();
        
    }   
    
    protected function groupApplication(){
    	$cUser = Session::getInstance()->getVisitor();
        
        if(!$cUser->isLoggedIn()){
        	$this->errorView(ERR_NO_LOGGIN);
        }
        
        if($cUser->isExternal()){
            $this->errorView(ERR_NO_EXTERN);
        }
        
        $parameters = $this->getParameters('groupApplication');
        
        if (null == $group = $parameters['group']) {
            $this->errorView(ERR_NO_GROUP);
        }
        
        require_once CORE_DIR . '/models/pm/pm_entry_model.php';
        
        $text = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'mail/group_application.tpl');
        $text->assign('user', $cUser);
        $text->assign('group', $group);
               
        // send admin PM, 
        $success = false;
        $sentTo = $group->getAdmins();
        if(count($sentTo) > 0){
            $success = PmEntryModel::sendSystemPm($text->fetch(), CAPTION_GROUP_APPLICATION,
                'undisclosed recipients', $sentTo, true);
        }        
            
        $main = $this->viewGroupInfoPage(false, $group);
        $main->assign('application', true);
        $main->assign('successApplication', $success);
        
        $this->view();     
    }
    
    
    protected function leaveGroup(){
    	$cUser = Session::getInstance()->getVisitor();
        
        if(!$cUser->isLoggedIn()){
            $this->errorView(ERR_NO_LOGGIN);
        }
        
        if($cUser->isExternal()){
            $this->errorView(ERR_NO_EXTERN);
        }
        
        $parameters = $this->getParameters('leaveGroup');
        
        if (null == $group = $parameters['group']) {
            $this->errorView(ERR_NO_GROUP);
        }
        
        if(!$group->hasMember($cUser)){
        	$this->errorView(ERR_GROUP_MEMBERSHIP);
        }
        
        /* is the delete confirmend? */
        if (!array_key_exists('deleteConfirmation', $_REQUEST)) {
            $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/forum/confirmation.tpl');
            $main->assign('confirmationCause', 'Austreten aus der Organisation: ' . $group->getName());
            $main->assign('confirmationConsequences', 'Du bist nicht mehr Mitglied der Organisation und verlierst alle Rechte die damit verknüpft sind!');
            $main->assign('confirmationOkLink', '/index.php?mod=groups&amp;dest=modul&amp;method=leaveGroup&amp;groupId=' . $group->id . '&amp;deleteConfirmation=yes');
            $main->assign('confirmationCancelLink', rewrite_group(array('group' => $group)));
            $this->setCentralView($main);
            $this->view();
            return;        
        }
        
        $group->delUsers(array($cUser));
        $cUser->removeFromUserOnlineList();
        
        header('Location: '.rewrite_group(array('group' => $group, 'extern' => true)));
        return;
    }
 }
?>
