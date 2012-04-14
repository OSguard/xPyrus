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
 * Created on 25.07.2006 by schnueptus
 * sunburner Unihelp.de
 */
require_once CORE_DIR . '/businesslogic/business_logic_controller.php';
 
require_once CORE_DIR . '/utils/client_infos.php';
require_once CORE_DIR . '/utils/attachment_handler.php';

require_once CORE_DIR . '/models/pm/pm_entry_model.php'; 
require_once CORE_DIR . '/models/base/entry_attachment_model.php';
require_once CORE_DIR . '/models/base/friend_model.php';
 
// load some useful constants for working on <form>-input
require_once CORE_DIR . '/constants/form_constants.php';
require_once CORE_DIR . '/constants/value_constants.php';
 
define('PM_TEMPLATE_DIR', 'modules/pm/');
/**
 * all for displaying and sending PM messages on unihelp
 */ 
class PmBusinessLogicController extends BusinessLogicController {
    
    public function __construct($ajaxView = false) {
        parent::__construct($ajaxView);
    }   


   /** 
     * default method: we want to see the overview of all Forums 
     */
    protected function getDefaultMethod() {
        return 'viewUserPm';
    }
    
    /**
      * List of al methods who ar allowed
      */
    public function getAllowedMethods() {
        $array = parent::getAllowedMethods();
        array_push($array, 
                   'viewUserPm',
                   'viewPmSent',
                   'viewPmEntrie',
                   
                   'newUserPm',
                   
                   'delPm',
                   'delPmSent',
                   
                   'ajaxPmPreview'
                   );
        return $array;
      }
    
    public function getMethodObject($method) {
        $parameters = $this->getParameters($method);
        if ('viewUserPm' == $method) {
            return new BLCMethod(NAME_PM_INBOX,
                rewrite_pm(array('page' => $parameters['page'])),
                BLCMethod::getDefaultMethod());
        } else if ('viewPmSent' == $method) {
            return new BLCMethod(NAME_PM_OUTBOX,
                rewrite_pm(array('out' => 1, 'page' => $parameters['page'])),
                BLCMethod::getDefaultMethod());
        } else if ('viewPmEntrie' == $method) {
            $parentMethod = null;
            if ($parameters['sent']) {
                $parentMethod = $this->getMethodObject('viewPmSent');
            } else {
                $parentMethod = $this->getMethodObject('viewUserPm');
            }
            $caption = $parameters['pm']->getCaption();
            if (!$caption) {
                $caption = '(' . ENTRY_NO_SUBJECT . ')';
            }
            return new BLCMethod($caption,
                rewrite_pm(array('sent' => $parameters['sent'], 'pm' => $parameters['pm'])),
                $parentMethod);
        } else if ('newUserPm' == $method) {
            return new BLCMethod(NAME_PM_COMPOSE,
                rewrite_pm(array('new' => 1)),
                $this->getMethodObject('viewUserPm'));
        } 
        
        return parent::getMethodObject($method);
    }
    
    protected function collectParameters($method) {
        $parameters = array();
        if ('viewUserPm' == $method) {
            $parameters['page'] = InputValidator::getRequestData('page');
        } else if ('viewPmSent' == $method) {
            $parameters['page'] = InputValidator::getRequestData('page');
        } else if ('viewPmEntrie' == $method) {
            $pmId = InputValidator::getRequestData('pmId');
            $parameters['sent'] = InputValidator::getRequestData('sent', false);
            if ($parameters['sent']){
                $pm = PmEntryModel::getPmByIdAuthor(Session::getInstance()->getVisitor(), $pmId);
            } else {
                $pm = PmEntryModel::getPmById(Session::getInstance()->getVisitor()->id, $pmId);
            }
            $parameters['pm'] = $pm;
        }
        $this->_parameters[$method] = $parameters;

        parent::collectParameters($method);
    }
    
    /**
     * displays all PM's the visitor get
     * 
     * @param $display - output the template or give it as return
     * 
     * @return SmatyInstance
     */
    protected function viewUserPm($display = true){
    	$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/pm/overview.tpl');
        
        $cUser = Session::getInstance()->getVisitor();        
        
        if(!$cUser->hasRight('PM_READ_MESSAGES')){
        	 $this->rightsMissingView('PM_READ_MESSAGES');
        }
        
        /* set PMs read */
        if(array_key_exists('pmSelected',$_REQUEST) && array_key_exists('read',$_REQUEST)){
        	PmModel::setRead($_REQUEST['pmSelected'], $cUser, true);
            // signal number change
            PmEntryModel::signalPmsChanged($cUser);
        }
        /* set PMs unread */
        elseif(array_key_exists('pmSelected',$_REQUEST) && array_key_exists('unread',$_REQUEST)){
            PmModel::setRead($_REQUEST['pmSelected'], $cUser, false);
            // signal number change
            PmEntryModel::signalPmsChanged($cUser);
        }
        /* delets PM */
        elseif(array_key_exists('pmSelected',$_REQUEST) && array_key_exists('del',$_REQUEST)){
            PmModel::delForUser($_REQUEST['pmSelected'], $cUser);
            // signal number change
            PmEntryModel::signalPmsChanged($cUser);
        }
        
       	$page = array_key_exists('page',$_REQUEST) ? $_REQUEST['page'] : 1;
        
        $pms = PmEntryModel::getPmByUserId($cUser->id, V_PM_ENTRIES_PER_PAGE, ($page-1)*V_PM_ENTRIES_PER_PAGE, 'desc' );
        $main->assign('pms',$pms);
        $counter = PmModel::nonLinearCounter(ceil($cUser->getPMs()/V_PM_ENTRIES_PER_PAGE), $page);
        $main->assign('pm_counter',$counter);
        $main->assign('page',$page);
        
        $this->setCentralView($main);
        
        if(!$display){
        	return $main;
        }
        
        $this->view();
    }
    /**
     * view all message the visitor has send
     * 
     * @param $display - output the template or give it as return
     * 
     * @return SmatyInstance
     */
    protected function viewPmSent($display = true){
    	$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/pm/overview_sent.tpl');
        
        // CACHEME: should not cache this page, is not worth it (linap)
        $cUser = Session::getInstance()->getVisitor();        
                
        if(!$cUser->hasRight('PM_READ_MESSAGES')){
             $this->rightsMissingView('PM_READ_MESSAGES');
        }
        $page = array_key_exists('page',$_REQUEST) ? $_REQUEST['page'] : 1;
        
        if(array_key_exists('pmSelected',$_REQUEST) && array_key_exists('del',$_REQUEST)){
            PmModel::delForAuthor($_REQUEST['pmSelected'], $cUser);
            // signal number change
            PmEntryModel::signalPmsChanged($cUser);
        }
        
        $pms = PmEntryModel::getPmByAuthor($cUser, V_PM_ENTRIES_PER_PAGE, ($page-1)*V_PM_ENTRIES_PER_PAGE, 'desc');
        
        //var_dump($pms);
        $main->assign('pms',$pms);
        
        $counter = PmModel::nonLinearCounter(ceil($cUser->getPMsSent()/V_PM_ENTRIES_PER_PAGE), $page);
        $main->assign('pm_counter',$counter);
        $main->assign('page',$page);    
        
        $this->setCentralView($main);
        
        if(!$display){
            return $main;
        }
        
        $this->view();
    }
    /**
     * view a single PM's
     * 
     * the visitor have to be a reciever 
     * or he must be the author but then we neet $_REQUEST['send'] (to sign as author view)
     * 
     * @param $display - output the template or give it as return
     * 
     * @return SmatyInstance
     */
    protected function viewPmEntrie($display = true){
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/pm/view_entry.tpl');
        
        // CACHEME: should not cache this page, is not worth it (linap)
        $cUser = Session::getInstance()->getVisitor();        
        
        if(!$cUser->hasRight('PM_READ_MESSAGES')){
             $this->rightsMissingView('PM_READ_MESSAGES');
        }
        
        $parameters = $this->getParameters('viewPmEntrie');

        //$pmId = $parameters['pmId'];                
        
        $pm = $parameters['pm'];
        
        //var_dump($pm);
        
        if ($pm == null){
            Logging::getInstance()->logSecurity('PM doesnt exist for this user');
        }
        
        if (!$parameters['sent'] and $pm->isUnread()){
            $pm->setUnread(false);
            
            // signal number change
            PmEntryModel::signalPmsChanged($cUser);
        }
        //var_dump($pm);
        
        $main->assign('pm',$pm);
        
        if($pm->getAuthor()->isSystemUser()){
        	$main->assign('systemPm', true);
        }
        
        $this->setCentralView($main);
        
        if (!$display) {
            return $main;
        }
        
        $this->view();
    }
    /**
     * display the form for sending a new PM
     * sending a new PM
     *
     * quote and forward an preview are supportet
     */
    protected function newUserPm(){
 	    $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/pm/new_user_pm.tpl');
        
        $cUser = Session::getInstance()->getVisitor();        
        
        if(!$cUser->hasRight('PM_ADD_USER_MESSAGES')){
             $this->rightsMissingView('PM_ADD_USER_MESSAGES');
        }    
        /* shows page that a massage was sussesfull send */   
        if(array_key_exists('sendsuccessful',$_REQUEST)){
            $main->assign('sendsuccessful',true);
            $this->setCentralView($main);
            $this->view();
            return;
        }
        
        /* try to send a message */
        if(array_key_exists('save',$_POST)){
        	
             $formFields = array(
            'receivers'   => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => true),
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
            
            $sendToFriends = false;
            $sendToAll = false;
            $sendToOnline = false;
            $sendToGroupAdmins = false;
            $sendToGroup = false;
            $sendToCourse = false;
                        
            $receiverList = explode(',',$_POST['receivers']);
            /* removes space near "," */
            $receiverList = array_map('trim',$receiverList);
            
            // limit number of receivers
            $receiverLimit = -1;
            
            if(array_key_exists('limit_receivers', $_REQUEST)){
            	$receiverLimit = $_REQUEST['limit_receivers'];
            }
            
            //var_dump($receiverList);
            
            if($cUser->hasRight('PM_SENDTO_FRIENDS') ){
                if(in_array('[friends]',$receiverList)){
                	$sendToFriends = true;
                }
            }
                        
            if($cUser->hasRight('PM_SENDTO_ALL')){
                if(in_array('[ALLUSER]',$receiverList)){
                    $sendToAll = true;
                }
            }
            
            if($cUser->hasRight('PM_SENDTO_ALL')){
                if(in_array('[ALLONLINE]',$receiverList)){
                    $sendToOnline = true;
                }
            }
            
            if($cUser->hasRight('PM_SENDTO_ALL')){
                if(in_array('[ALLGROUPADMINS]',$receiverList)){
                    $sendToGroupAdmins = true;
                }
            }
            
            if($cUser->hasRight('PM_SENDTO_GROUP')){
                $groupIds = array();
                
                //old Version
                foreach($receiverList as $res){
                    $res = str_replace(' ','',$res);           
                	if (substr($res, 0, 7) == "[group=") {
                		$id = substr($res, 7, strlen($res) - 8);
                        $groupIds[] = $id;
                	}
                }
                
                //here we can use the true groupnames to send PMs             
                $groups = GroupModel::getGroupsByNames($receiverList);
                
                foreach($groups as $group){
                	$groupIds[] = $group->id;
                }
                
                if(count($groupIds) > 0){
                	$sendToGroup = true;
                }
            }
            
            if($cUser->hasRight('PM_SENDTO_COURSE')){
                $courseIds = array();
                
                foreach($receiverList as $res){
                    $res = str_replace(' ','',$res);
                    if (preg_match('#\[course=(\d+)(?:_(\d+))?\]#', $res, $matches)) {
                        $courseIds[] = $matches[1];
                        if(array_key_exists(2, $matches))
                            $receiverLimit = $matches[2];
                    }
                }
                
                //var_dump($receiverLimit);
                if(count($courseIds) > 0){
                    $sendToCourse = true;
                }
            }
            
            $receiverString = '';
            $receivers = UserProtectedModel::getUsersByUsernames($receiverList);
            $realReceivers = array();
            foreach ($receivers as &$r) {
                // if user not active he dont get PNs
                if($r->isActive()){
                    // if visiting user is on ignore list, remove him from receiver list
                    if (FriendModel::hasOnIgnoreList($r, $cUser)) {
                        $_REQUEST['receivers'] = preg_replace('/' . $r->getUsername(). '/i', 'IGNORE', $_REQUEST['receivers']);
                    } else {
                        array_push($realReceivers, $r);
                    }
                }
            }
            $receivers = $realReceivers;
            //var_dump($sendToFriends);
            
            if($receivers == null && !$sendToFriends && !$sendToAll && !$sendToOnline && !$sendToGroupAdmins && !$sendToGroup && !$sendToCourse){
            	$this->errors['receivers'] = ERR_PM_NO_RESIVERS;
            }
            
            //$content_raw = null, $author = null, $parseSettings = array (), $caption = ''
            $pmToSend = Session::getInstance()->getUserData('pm_entry');
            if($pmToSend == null){
                $pmToSend = new PmEntryModel($_POST['entryText'], $cUser, self::getParseSettings(), $_POST['caption'], $_REQUEST['receivers']);
            }
            else{
            	$pmToSend->update($_POST['entryText'], self::getParseSettings(), $_POST['caption'], $_REQUEST['receivers']);
            }
            
            $pmToSend->setReceivers($receivers, $receiverLimit);
            $pmToSend->setReplyTo(empty($_REQUEST['replyId']) ? null : $_REQUEST['replyId']);
            
            // signal pm number change for recipients
            foreach ($receivers as $r) { 
                PmEntryModel::signalPmsChanged($r);
            }
            // signal number change
            PmEntryModel::signalPmsSentChanged($cUser);
            
            if($sendToFriends){
            	$pmToSend->setAsFriendlist(true);
            }
            if($sendToAll){
            	$pmToSend->setAsAll(true);
            }
            if($sendToOnline){
                $pmToSend->setAsOnline(true);
            }
            if($sendToGroupAdmins){
            	$pmToSend->setAsGroupAdmin(true);
            }
            if($sendToGroup){
            	$pmToSend->setGroupIds($groupIds);
            }
            if($sendToCourse){
            	$pmToSend->setCourseIds($courseIds);
            }
            
            // handle attachment(s) additions and removements
            if($cUser->hasRight('PM_ADD_ATTACHMENT')){
                if (!$this->handleAttachment($pmToSend, $cUser, GlobalSettings::getGlobalSetting('ENTRY_MAX_ATTACHMENT_SIZE') * 1024)) {
                    $this->errors['attachment'] = ERR_ATTACHMENT;
                }
            }
            
            /* if is a error before we switch in preview mode, no save*/
            if(!$this->previewMode and count($this->errors) == 0){
                /* fail if not enouth points */
                if($pmToSend->save()){
                	/* old style */
                    $main->assign('sendsuccessful',true);
                    Session::getInstance()->deleteUserData('pm_entry');
                    /* new one */
                    header('Location: ' . rewrite_pm(array('submit'=>true, 'extern' => true)));
                    return;
                }
                else{
                	$this->errors['points'] = ERR_PM_POINTS;
                }               
            }
            
        } /* end of saving */
        
        /* preview Mode */
        if ($this->previewMode || $this->uploadMode || count($this->errors) > 0){
            
            //$pmToPrevie = new PmEntryModel($_POST['entryText'], $cUser, self::getParseSettings(), $_POST['caption'], $_POST['receivers']);
            $pmToPreview = Session::getInstance()->getUserData('pm_entry');
            if($pmToPreview == null){
                $pmToPreview = new PmEntryModel($_POST['entryText'], $cUser, self::getParseSettings(), $_POST['caption'], $_REQUEST['receivers']);
            }
            else{
                if(empty($_REQUEST['addFileId'])){
                    $pmToPreview->update($_POST['entryText'], self::getParseSettings(), $_POST['caption'], $_REQUEST['receivers']);
                }               
            }

             // handle attachment(s) additions and removements
            if($cUser->hasRight('PM_ADD_ATTACHMENT')){ 
                if (!$this->handleAttachment($pmToPreview, $cUser, GlobalSettings::getGlobalSetting('ENTRY_MAX_ATTACHMENT_SIZE') * 1024)) {
                    $this->errors['attachment'] = ERR_ATTACHMENT;
                }
            }                                                   
           
            /*** save the model in the session ***/
            
            Session::getInstance()->storeUserData('pm_entry', $pmToPreview); 

            $main->assign('isPreview', $this->previewMode);
            if (array_key_exists('receivers',$_REQUEST)){
                $main->assign('receivers',$_REQUEST['receivers']);
            }
            else{
            	$main->assign('receivers', $pmToPreview->getRecipientString());
            }
            $main->assign('pmToPreview',$pmToPreview);
            
            if(array_key_exists('limit_receivers', $_REQUEST)){
                
                if(array_key_exists('limit_max', $_REQUEST)){
                    $limitMax = $_REQUEST['limit_max'];
                    $main->assign('limit_max', $limitMax);
                }
                if( $limitMax != null)
                    $limitReceivers = min($_REQUEST['limit_max'], $limitMax );
                $main->assign('limit_receivers', $limitReceivers);
            }
        } /* end of preview */
        
        if(array_key_exists('quoteId',$_GET)){
        	$pmToQuote = PmEntryModel::getPmById($cUser->id, $_GET['quoteId']);
            if(array_key_exists('toAll',$_REQUEST)){
            	$toAll = $pmToQuote->getRecipientString().','.$pmToQuote->getAuthor()->getUserName();
                $toAll = str_replace($cUser->username.',', '',$toAll);
                $main->assign('receivers',$toAll);
            }
            if($pmToQuote != null){
            	$main->assign('isQuote',true);            
                $pmToQuote->setCaption(preg_replace('/^(RE:|ANSW:|ANTW:|Antwort:|AW:|FWD:|FORWARD:|WG:|\s)+/i', '', $pmToQuote->getCaption()));
            	$main->assign('pmToQuote',$pmToQuote);
            }
        }
        
        if(array_key_exists('fwdId',$_GET)){
            $pmToQuote = PmEntryModel::getPmByIdAuthor($cUser,$_GET['fwdId']);
            if($pmToQuote == null){
                $pmToQuote = PmEntryModel::getPmById($cUser->id,$_GET['fwdId']);
            }
            if($pmToQuote != null){
                $main->assign('isFwd',true); 
                $pmToQuote->setCaption(preg_replace('/^(RE:|ANSW:|ANTW:|Antwort:|AW:|FWD:|FORWARD:|WG:|\s)+/i', '', $pmToQuote->getCaption()));
                $main->assign('pmToQuote',$pmToQuote);
            }
        }
        if(array_key_exists('receivers',$_REQUEST)){
            $main->assign('receivers',$_REQUEST['receivers']);
        }
        
        if(array_key_exists('caption',$_REQUEST) && !$this->previewMode && !$this->uploadMode){
            $main->assign('capitionValue', preg_replace('/^(RE:|ANSW:|ANTW:|Antwort:|AW:|FWD:|FORWARD:|WG:|\s)+/i', '', $_REQUEST['caption']));
        }
        if(array_key_exists('courseId',$_REQUEST)){
        	$main->assign('receivers','[course='.$_REQUEST['courseId'].']');            
            
            $course = CourseModel::getCourseById($_REQUEST['courseId']);
            $main->assign('courseName', $course->getName());
            $main->assign('courseId', $course->id);
            
            $limitMax = $course->getSubscriptorsNumber();
            $main->assign('limit_max', $limitMax);
            $limit = array_key_exists('limit_receivers', $_POST) ? $_POST['limit_receivers'] : 10;
            $main->assign('limit_receivers', min($limit, $limitMax));
            if(!array_key_exists('preview_submit', $_POST)){                
                $main->assign('capitionValue', '['.$course->getName().'] Anfrage von '.$cUser->getUsername());
            }
        }
        
        $this->setCentralView($main);                
        $this->view();        
    }
    
    /**
     * del a PM for the visitor as reciever
     */
    protected function delPm(){
    	
        if(!array_key_exists('pmId',$_GET)){
    		$this->errorView(ERR_NO_ENTRY);
    	}
        
        $cUser = Session::getInstance()->getVisitor();
        
        if(!$cUser->hasRight('PM_DEL_USER_MESSAGES')){
            $this->rightsMissingView('PM_DEL_USER_MESSAGES');
        }
        
        $pmId = $_GET['pmId'];
        $pm = PmEntryModel::getPmById($cUser->id, $pmId );
        
        if($pm == null){
            Logging::getInstance()->logSecurity('PM doesnt exist for this user');
        }
        $pm->delForUser($cUser);
        
        // signal number change
        PmEntryModel::signalPmsChanged($cUser);
        
        /* new style */
        $page = array_key_exists('page',$_REQUEST) ? $_REQUEST['page'] : 1;
        header('Location: ' . rewrite_pm(array('page'=>$page, 'extern' => true)) );
    }
 
 
    /**
     * del a PM for the author
     */
    protected function delPmSent(){
        
        if(!array_key_exists('pmId',$_GET)){
            $this->errorView(ERR_NO_ENTRY);
        }
        
        $cUser = Session::getInstance()->getVisitor();
        
        if(!$cUser->hasRight('PM_DEL_USER_MESSAGES')){
            $this->rightsMissingView('PM_DEL_USER_MESSAGES');
        }
        
        $pmId = $_GET['pmId'];
        $pm = PmEntryModel::getPmByIdAuthor($cUser, $pmId);
        
        if($pm == null){
            Logging::getInstance()->logSecurity('PM doesnt exist for this user');
        }
        $pm->delForAuthor();
        
        
        // signal number change
        PmEntryModel::signalPmsSentChanged($cUser);
        
        /* new style */
        $page = array_key_exists('page',$_REQUEST) ? $_REQUEST['page'] : 1;
        header('Location: ' . rewrite_pm(array('out'=>true,'page'=>$page, 'extern' => true)) );
    }
 
   /**
    * ajex preview Reqest
    */ 
   protected function ajaxPmPreview(){
   	    $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/pm/pm_preview.tpl');
        
        $cUser = Session::getInstance()->getVisitor();        
        
        if(!$cUser->hasRight('PM_ADD_USER_MESSAGES')){
             return;
        }
        
        $formFields = array(
            'receivers'   => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => true),
            'entryText' => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => false),
            'caption'=> array('required' => false, 'check' => 'isValidAlmostAlways', 'escape' => true)
             );
        // some validity checks and HTML-escape
        $this->validateInput($formFields);                         
            
        $pmToPreview = Session::getInstance()->getUserData('pm_entry');
        if($pmToPreview == null){
            $pmToPreview = new PmEntryModel($_POST['entryText'], $cUser, null, $_POST['caption'], $_POST['receivers']);
        }
        else{
            if(empty($_REQUEST['addFileId'])){
                $pmToPreview->update($_POST['entryText'], null, $_POST['caption'], $_POST['receivers']);
            }               
        }
        $parser = self::getAjaxParseSettings();
        $pmToPreview->setParseSettings($parser);
        
        if (array_key_exists('receivers',$_REQUEST)){
            $main->assign('receivers',$_REQUEST['receivers']);
        }
        else{
            $main->assign('receivers', $pmToPreview->getRecipientString());
        }
        
        $main->assign('pmToPreview',$pmToPreview);
        $main->display();
        
   }
}
?>
