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

// $Id: mantis_business_logic_controller.php 6210 2008-07-25 17:29:44Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/businesslogic/mantis/mantis_business_logic_controller.php $

/*
* How to add new categories:
* 1a) add category to form_constants.php
* 1b) add category to mantis - IMPORTANT: names of categories in mantis have to be identical to those defined in form_constants.php
* 2a) add category to '$this->setTemplateVars(...)'
* 2b) add fields to template mantis_interface.tpl if not yet existing
* 3) add category to '$this->checkFields(...)'
* 4) add category to '$this->createMantisBug(...)'
* 5) pray that it works ;-)
*/



require_once CORE_DIR . '/businesslogic/business_logic_controller.php';
require_once CORE_DIR . '/utils/captcha_computation.php';
require_once CORE_DIR . '/utils/mailer.php';

require_once CORE_DIR . '/utils/client_infos.php';
require_once BASE . '/lang/de.php.inc';

// load some useful constants for working on <form>-input
require_once CORE_DIR . '/constants/form_constants.php';
require_once CORE_DIR . '/constants/value_constants.php';

include_once(BASE . "/conf/mantis_config.php");

require_once CORE_DIR . '/businesslogic/mantis/mantis_soa_objects.php';
require_once CORE_DIR . '/businesslogic/mantis/mantis_typ_interface.php';
    
function MantisGetTypeName($conts){
		
    global $MantisNames;
    if(!empty($MantisNames [$conts])){
		return $MantisNames [$conts];
	}
}


/**
 * The index site with short forumoverview, the news and more
 * This is our first site
 * and our 'Spielwiese' for the new MVC concept.
 */
class MantisBusinessLogicController extends BusinessLogicController {

	var $mantis_id;

	function __construct() {
		parent::__construct();
	}

    /**
     * Default method, will be called upon submit and first call to Mantis controller
     */
    protected function getDefaultMethod() {
        return 'home';
    }

    /**
      * List of all methods that are allowed
      */
    public function getAllowedMethods() {
        $array = parent::getAllowedMethods();
        array_push($array,
            'home',
            'webservice',
            'userAnswer',
            'test'
        );
        return $array;
      }

    public function getMethodObject($method) {
        
        return new BLCMethod(NAME_SUPPORT,
                rewrite_mantis(array()),
                BLCMethod::getDefaultMethod());
                
    }
                

	/**
	* Set variables for templates.
	* @param object template
	* @param string Determines source category, i.e. guestbook, forum etc.
	* @param array array containing additional values to be set, see template for details
	*  ######ATTENTION: if $additionalValues contains template variables already set before they will be OVERWRITTEN!!!######
	* @return void
	*/
    private function setTemplateVars( $template, $source_cat, $additionalValues = array() ) {
		$vars = array();
		$source_cat = trim($source_cat);
		$session = Session::getInstance();
		$visitor = $session->getVisitor();
		$isLoggedIn = $visitor->isLoggedIn();
		$showLogInError = false;
		
        $CathegoryObjekt = mantisCategoryFactory::getMantisCategory($source_cat);
        
        /*
         * load from Obejekt
         */
        if($CathegoryObjekt !== null){
            $vars = $CathegoryObjekt->getTemplateVars($isLoggedIn);
            $showLogInError = ($CathegoryObjekt->needLoggin && !$isLoggedIn);
        }
		else {//source unknown
			
            $CathegoryObjekt = mantisCategoryFactory::getMantisCategory(F_SOURCE_UNKNOWN);
            $vars = $CathegoryObjekt->getTemplateVars($isLoggedIn);
            $showLogInError = ($CathegoryObjekt->needLoggin && !$isLoggedIn);
                      
			//get session data
			$session = Session::getInstance();
			$tmp = $session->getUserData($this->mantis_id);
			if ($tmp){//if data inside
				$tmp[F_SOURCE_CAT] = F_SOURCE_UNKNOWN;
				//write to session that source category could not be determined
				$session->storeUserData($this->mantis_id, $tmp);
			}
		}
		if ($showLogInError){
			$this->errorView(ERR_NO_LOGGIN);		
		} else if (!$visitor->isLoggedIn()) {
            $vars['captchaNeeded'] = true;
            $captcha = new CaptchaComputation();
            $captcha->generate();
            $vars['comment_captcha'] = $captcha;            
            Session::getInstance()->storeViewData('captcha', $captcha);
        }
		//set template variables according to values given above
		foreach ($vars as $key => $v){
			$template->assign($key, $v);
		}
		//set additional template variables ######ATTENTION: if $additionalValues contains template variables already set before they will be OVERWRITTEN######
		foreach ($additionalValues as $key => $v){
			$template->assign($key, $v);
		}
    }
	/**
	* Check all form fields for missing/wrong input.
	* @param string Determines source category, i.e. guestbook, forum etc. Necesary to determine which fields to check.
	* @return boolean True if all fields are ok, false otherwise.
	*/
    private function checkFields( $source_cat ) {
			$formFields = array();
			//if not all required values are being submited, return error messages

			//trim variable so that whitespaces do not cause faults/false evaluations
			$source_cat = trim($source_cat);
			$session = Session::getInstance();
			$visitor = $session->getVisitor();
			$isLoggedIn = $visitor->isLoggedIn();
			
            $CathegoryObjekt = mantisCategoryFactory::getMantisCategory($source_cat);
        
            /*
             * load from Obejekt
             */
            if($CathegoryObjekt !== null){
                if($CathegoryObjekt->needLoggin && !$isLoggedIn){
                	$this->errorView(ERR_NO_LOGGIN);
                }
                $formFields = $CathegoryObjekt->getCheckedFilds();
                $extendetCheack = $CathegoryObjekt->extendetCheck();
                foreach ( $extendetCheack as $missing => $message){
                	$this->addIncompleteField($this->errors, $missing, $message);
                }
            }
            else{//i.e. F_SOURCE_UNKNOWN
                    $formFields = array (
                        "mail" => array( "required" => true, "check" => "isValidMail" ),
                        "query" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                        "title" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed")
                    );
		    }
            if (!$visitor->isLoggedIn()) {
                $formFields['comment_captcha'] = array('required' => true,  
                                        'check' => 'isValidCaptcha', 
                                        'params' => array('captcha' => Session::getInstance()->getViewData('captcha', null)));
            }
		    $valid = $this->validateInput($formFields);
			//if errors occured they will be returned in array $this->errors...
			return ( count($this->errors) == 0);
    }
	
	/**
	* Create bug for mantis, i.e. write values to class variables of BugClass
	* @param object current user
	* @param string  Direct link to entry if any.
	* @param string Raw content of the entry if any.
	* @param string Source of the call, e.g. forum, guest book
	* @param string Source category of the call, e.g. report entry, delete entry !!!IMPORTANT NOTE!!!: $source_cat MUST comply (i.e. match the exact syntac!!) with the categories set in the respective mantis project, otherwise there will be a mantis bug and the bug won't be registered with mantis but a mail to the admin will be send instead!!!
	* @return object BugClass filled with respective values
	*/
    private function createMantisBug ( $cUser, $directlink, $entry, $source, $source_cat ) {
             $project = new ProjectData();
             $project->id= MANTIS_PROJEKT_ID;
             $project->name = MANTIS_PROJEKT;

             $CathegoryObjekt = mantisCategoryFactory::getMantisCategory($source_cat);
			 $CathegoryObjekt->buildBug($cUser, $directlink, $entry, $source);
             
             $bug = new BugData();
             $bug = $CathegoryObjekt->getMantisBug();
             $bug->id=0;
             $bug->category = MantisGetTypeName($source_cat);
             $bug->project = $project;
             $bug->summary = $this->getUserData('title') . ' ' . $bug->summary;
             return $bug;
    }

	/**
	*
	* Create unique ID for form. Used for identification of multiple postings/injections.
	*/
    private function createMantisID(){
    	return md5(uniqid (rand(), true));
    }
	
	/*
	* Return data from either user data stored in $user (for mail and username so far) or $_REQUEST.
	* @param string item to be retrieved from $_REQUEST
	* @param string default value that will be set if no or no senseful value could be retrieved for respective item
	* @param object current user
	* @return sring retrieved data from user object or $_REQUEST
	*/
	private function getUserData($item, $defaultValue = 'unbekannt', $user = null){
		$cUser = $user;
		if (strcmp($item, 'mail')==0){
	        if (!is_null($cUser) && $cUser->isLoggedIn()) {
	            if ($cUser->getPrivateEmail()){
	            	$_mail = $cUser->getPrivateEmail();
	            }
	            else {
	            	$_mail = $cUser->getUniEmail();
	            }
	        }
	        //not logged in
	        else {
				//try to get data from $_REQUEST
	            $_mail = (isset($_REQUEST['mail']) ? $_REQUEST['mail'] : $defaultValue);
	        }
			return trim($_mail);
		}
		elseif (strcmp($item, 'username')==0){
	        if (!is_null($cUser) && $cUser->isLoggedIn()) {
				$_username = $cUser->getUsername();
	        }
	        //not logged in
	        else {
	            $_username = $defaultValue;
	        }
			return trim($_username);
		}
		else {
			//$_item = (isset($_REQUEST[$item]) ? $_REQUEST[$item] : $defaultValue);
			return trim(InputValidator::getRequestData($item, $defaultValue));
		}
	}

	/**
	* Main method in class. Encapsulates business logic for handling support/bug report/entry report form
	* @param array array( F_DIRECTLINK=>..., F_SOURCE_CAT=>..., F_SOURCE=>..., F_ENTRY_TEXT=>... ) is used if home is called directly via php instead of calling via get/post "call"
	*/
    protected function home( $params = array() ) {

        $session_data = array();
    	$session = Session::getInstance();
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'modules/mantis/mantis_interface.tpl');
        $main->setCacheParameter(900, 'index');
		//put data from $params to $_REQUEST; if no data inside $params, no action will take place
		$_REQUEST = array_merge($params, $_REQUEST);

		$this->mantis_id = trim( InputValidator::getRequestData(F_MANTIS_ID, $this->createMantisID()) );

		/* get the current user */
		$visitor = $session->getVisitor();
        PrivacyContext::getContext()->setLevelByName('no one');
			$cUser = $visitor;

//echo "birthday: ".$cUser->getBirthdate();

        //get entry if any
        $tmp = $session->getUserData($this->mantis_id);
		$entry = (isset($tmp[F_ENTRY_TEXT]) ? $tmp[F_ENTRY_TEXT] : $this->getUserData("entry", ""));
		//get direct link to previous page (i.e. page from where mantis page was called
		$directlink = (isset($tmp[F_DIRECTLINK]) ? $tmp[F_DIRECTLINK] : $this->getUserData("directlink", ""));
		//get source category
		$source_cat = (isset($tmp[F_SOURCE_CAT]) ? $tmp[F_SOURCE_CAT] : $this->getUserData("source_cat", ""));
        //get source (GB, forum, wiki etc.)
		$source = (isset($tmp[F_SOURCE]) ? $tmp[F_SOURCE] : $this->getUserData("source", ""));

		//if form was not yet submited
		if (!isset($_REQUEST['save'])) {
            //save data into session
            $session_data[F_DIRECTLINK] = $directlink;
            $session_data[F_SOURCE_CAT] = $source_cat;
            $session_data[F_SOURCE] = $source;
            $session_data[F_ENTRY_TEXT] = $entry;
			//set ID and store data in session using ID as key
            $session->storeUserData($this->mantis_id, $session_data);
			$this->setTemplateVars( $main, $source_cat, array("_mantis_id"=>$this->mantis_id, "user"=>$cUser) );
            
            if($source_cat == F_SOURCE_CHANGE_UNI){
            	$main->assign('uni',$cUser->getUni()->getName());
                $main->assign('allUnis',UniversityModel::getAllUniversities());
            }
            
        }
        //if form was already submited
		if (isset($_REQUEST['save']) ) {
			//check all form fields
			$fieldsOK = $this->checkFields( $source_cat );
			//if errors occured during processing of form data...
			if (!$fieldsOK) {
				$this->setTemplateVars( $main, $source_cat,
                    array(//create array containing values from form to return to form
                        "_entry" => $this->getUserData("entry", ""),
                        "_phone"=>$this->getUserData("phone", ""),
                        "_company"=>$this->getUserData("company", ""),
                        "_adress"=>$this->getUserData("adress", ""),
                        "_title"=>$this->getUserData("title", ""),
                        "_reason"=>$this->getUserData("reason", ""),
                        "_browser"=>$this->getUserData("browser", ""),
                        "_technicalData"=>$this->getUserData("technicalData", ""),
                        "_query "=>$this->getUserData("query", ""),
                        "_response"=>$this->getUserData("response", ""),
                        "_mantis_id"=>$this->getUserData("mantis_id", $this->mantis_id),
						"user"=>$cUser
						)
				);
		    }//$fieldsOK?
		    //if no errors occured, start building mantis string and try to write to mantis system via webservice
		    elseif ($fieldsOK && $session->getUserData($this->mantis_id) 
						&& !array_key_exists(F_STATUS_SUBMITED, $session->getUserData($this->mantis_id)) ) {
		    	 $bug = $this->createMantisBug( $cUser, $directlink, $entry, $source, $source_cat );
                 
                 //send webservice to server
                 if(MANTIS_TYPE == 'webservice'){
                    $res = $this->webservice($bug, $cUser);
                 }
                 else{
                 	$res = false;
                 }

				 $datei = (array_key_exists('file', $_FILES) ? $_FILES['file'] : false);
				 
				 /**
                  * Send email if webservice fail or not defined as simple support
				  */
                 
                 if (!$res){
                 	//send mail to admin if web service didn't work
					$targetPath = RELATIVE_USERFILE_DIR . '/mantis/'.$this->mantis_id."_";
					if ($datei && move_uploaded_file($datei['tmp_name'], $targetPath.$datei['name'])){
						$pathToAttachment = realpath($targetPath.$datei['name']);
					}
					else{
						$pathToAttachment = "Could not be determined.";
					}
					
					//RELATIVE_USERFILE_DIR . '/mantis'
                 	Mailer::sendMailAdmin("Mantis bug form", $bug->summary, 
						$bug->description."\n".
						$bug->additional_information."\n".
                 		(isset($bug->platform) ? $bug->platform : "")."\n".
						($datei ? "Pfad zum Anhang: ".$pathToAttachment : "")."\n".
						"Mantis-ID (wichtig für Rückverfolgung!): ".$this->mantis_id."\n"
					);
                 }//!$res
                 if ($res){
                    if ($datei && $datei['error']==UPLOAD_ERR_OK) {
                        $f = fopen($datei['tmp_name'], 'r');
	    				if ($f){
							//read whole file content
		    				$content = fread ( $f, filesize($datei['tmp_name']));
							//encode to MIME base64 => not necessary!!!
			    			//$content = base64_encode($content);
				    	}
					    else{
						    $content = "";//base64_encode( "" );
    					}
	    				$attachment = new BugAttachment();
		    			$attachment->id = $res;
			    		$attachment->name = $datei['name'];
				    	$attachment->file_type = $datei['type'];
					    $attachment->content = $content;
    					$this->addAttachment( $attachment );
						//$path = ini_get('upload_tmp_dir');
						//delete file if path could be found
						if (file_exists($datei['tmp_name'])){
							unlink(/*realpath($path."/".*/$datei['tmp_name']);
						}
                    }
				}//$res
				$this->setTemplateVars($main, $source_cat, array("ackNeeded"=>true, "directlink"=>$directlink, "user"=>$cUser));
				//create key 'F_STATUS_SUBMITED' in session and assign unique mantis id
				$session_data = $session->getUserData($this->mantis_id);
				$session_data[F_STATUS_SUBMITED] = $this->mantis_id;
				$session->deleteUserData($this->mantis_id);
				$session->storeUserData($this->mantis_id, $session_data);
				
				//send mail to user if wanted...
				if ($this->getUserData('response', false)){
					$id = ($res ? $res : $this->mantis_id);
					$to = $this->getUserData('mail', "", $cUser);
					$city = CityModel::getLocalCity();
					$message = MANTIS_MAIL_MESSAGE_HEADER.$id.". (".MANTIS_MAIL_MESSAGE_REQUEST.")\n\n\n".
									MANTIS_MAIL_MESSAGE_FOOTER.
									rewrite_mantis(array('extern' => 1)) . "" ;
                    // TODO: find suitable mail address
					@Mailer::sendmailMantisToUser($to, MANTIS_MAIL_MESSAGE_CAPTION . 
                                                        " ('".$this->getUserData('title')."')", $message);
				}

		    }
			else{//all form fields are ok but unique mantis id already exists in session => don't store bug again but set template variables for acknowledgement page instead
				$this->setTemplateVars($main, $source_cat, array("ackNeeded"=>true, "directlink"=>$directlink, "user"=>$cUser));
			}
		}//form already submited
        /* set as central template */
        $this->setCentralView($main, false);
        /* output */
        $this->view();
	}
	/**
	* Add attachment to mantis bug report.
	* @param object Class with attachment data inside.
	* @return mixed Returns false if attaching failed, number of issue otherwise.
	*/
	protected function addAttachment( $attachment ){
		$client = new soapclient(MANTIS_URL);
		
		//webservice might throw exception...
		try{
			$result = $client->mc_issue_attachment_add(MANTIS_USERNAME,MANTIS_PASSWORD, $attachment->id, $attachment->name, 
				$attachment->file_type, $attachment->content); 
		}
		catch (Exception $e){
            Logging::getInstance()->logException($e);
			return false;
		}
		return $result;
	}

	/**
	* Add bug report to mantis using webservice.
	* @param object Class with bug report data inside.
	* @return mixed Returns false if attaching failed, number of issue otherwise.
	*/
    protected function webservice($bug, $user = false) {
        $client = new soapclient(MANTIS_URL);
		//webservice might throw exception...
		try{
			$result = $client->mc_issue_add(MANTIS_USERNAME,MANTIS_PASSWORD, $bug);
		}
		catch (Exception $e){
            Logging::getInstance()->logException($e);
            return false;
		}
        //$getBug = $client->mc_issue_get(MANTIS_USERNAME,MANTIS_PASSWORD, $result);
        /* update link to write User a PM */
        if($user != false && $user->isLoggedIn()){
            $bug->id = $result;
            $link = rewrite_admin(array('extern'=>1, 'systemPm'=>1,'targetuser'=> $user, 'bugId' => $result));
            $bug->additional_information .= "\n".'User eine PN schreiben: ' . $link;
            $client->mc_issue_update(MANTIS_USERNAME,MANTIS_PASSWORD, $result, $bug);
        }
        
        
		return $result;
    }
    
    public function addNote($bugId, $text){
    	$client = new soapclient(MANTIS_URL);
        
        $note = new Note();
        $note->text = $text;
        
        //webservice might throw exception...
        try{
            $result = $client->mc_issue_note_add(MANTIS_USERNAME,MANTIS_PASSWORD, $bugId, $note);
        }
        catch (Exception $e){
            Logging::getInstance()->logException($e);
            return false;
        }
        return $result;
    }
    
    public function userAnswer(){
    	 /* current user logged in user */
        $cUser = Session::getInstance()->getVisitor();
        
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, 'modules/mantis/mantis_interface.tpl');
        $main->setCacheParameter(900, 'index');
        
        if(!array_key_exists('mantisId',$_REQUEST)){
        	$this->errorView('no id found');
        }else{
        	$mantisId = $_REQUEST['mantisId'];
        }
        if(array_key_exists('save',$_REQUEST)){
        	 $formFields = array(
            'query'   => array('required' => true, 'check' => 'isValidAlmostAlways', 'escape' => true)
            );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);
            
            if(count($this->errors) > 0){
                $this->errorView('no text');
            }
            $text = $cUser->getUsername() . ' antwortet auf die PN:' . "\n\n";
            $text .= $_POST['query'];
            
            $this->addNote($mantisId, $text);
            
            $this->setStatusToReOpen($mantisId);
            
            //$this->setTemplateVars($main, $source_cat, array("ackNeeded"=>true, "directlink"=>$directlink, "user"=>$cUser));
            $main->assign('ackNeeded',true);
            $main->assign('user',$cUser);
            $main->assign('ackNeeded',rewrite_pm(array()));
            $this->setCentralView($main);
            $this->view();
            return;
        }
        
        
        $main->assign('mantisId',$mantisId);
        
        $vars = array(
                "source_cat"=>F_SOURCE_ANWSER,
                "entryNeeded"=>"",
                "responseNeeded"=>"",
                "reasonNeeded"=>"",
                "mailNeeded"=>"",
                "titleNeeded"=>"",
                "queryNeeded"=>"true",
                "descriptionNeeded"=>"",
                "_reason"=>InputValidator::getRequestData('reason', ''),
                "_reponse"=>InputValidator::getRequestData('response', false)
                );
        foreach ($vars as $key => $v){
            $main->assign($key, $v);
        }
        $main->assign('user',$cUser);
        $this->setCentralView($main);
        $this->view();        
    }
    
    protected function setStatusToReOpen($mantisId){
        $client = new soapclient(MANTIS_URL);
        $getBug = $client->mc_issue_get(MANTIS_USERNAME,MANTIS_PASSWORD, $mantisId);
        if($getBug->status->id <= 20){
            return;
        }
        $getBug->status->id = 20;
        $client->mc_issue_update(MANTIS_USERNAME,MANTIS_PASSWORD, $mantisId, $getBug);
    }
    
    public function test(){
    	//$result = $this->addNote(546, "Ich habe den User ne PM geschrieben: \n\n DU doof");
        $client = new soapclient(MANTIS_URL);
        $getBug = $client->mc_issue_get(MANTIS_USERNAME,MANTIS_PASSWORD, 563);
        if($getBug->status->id <= 20){
        	return;
        }
        
        $getBug->status->id = 20;
        $client->mc_issue_update(MANTIS_USERNAME,MANTIS_PASSWORD, 563, $getBug);
        
        var_dump($getBug);
    }
}
?>
