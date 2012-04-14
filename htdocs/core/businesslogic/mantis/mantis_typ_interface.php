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
 * Created on 12.02.2008 by schnueptus
 * sunburner Unihelp.de
 */
abstract class mantisCategory{
    public $source_cat;
    public $needLoggin = false;
    public $data = array();
    public abstract function getTemplateVars($isLoggedIn);
    public abstract function getCheckedFilds();
    public function extendetCheck(){
    	return array();
    }
    public abstract function getMantisBug();
    
    public function buildBug($cUser, $directlink, $entry, $source){
    	 $city = CityModel::getLocalCity();
         $data = array();
         $data['mail'] = $this->getUserData('mail', "", $cUser);
         $data['username'] = $this->getUserData('username', "", $cUser);
         $data['company'] = $this->getUserData('company');
         $data['phone'] = $this->getUserData('phone');
         $data['adress'] = $this->getUserData('adress');
         $data['directlink'] = trim((is_null($directlink) ? WORD_UNKOWN : $directlink));
         $data['source'] = trim((is_null($source) ? WORD_UNKOWN : MantisGetTypeName($source)));
         $data['reason'] = $this->getUserData('reason');
         $data['entry'] = (is_null($entry) ? WORD_UNKOWN : $entry);
         $data['query'] = $this->getUserData('query');
         $data['response'] = trim((isset($_REQUEST['response']) ? WORD_YES : WORD_NO));
         $data['description'] = $this->getUserData('description');
         $data['technicalData'] = $this->getUserData('technicalData');
         $data['browser'] = $this->getUserData('browser');
         $data['newUsername'] = InputValidator::getRequestData('newUsername', WORD_UNKOWN);

         if(array_key_exists('newUni',$_REQUEST)){
             $uni = UniversityModel::getUniversityById(InputValidator::getRequestData('newUni', 0));
             if($uni != null){
                $data['newUni'] = $uni->getName();
             }
         }else{
            $data['newUni'] = null;
         }

         $data['newBirthday'] = InputValidator::getRequestData('yearOfBirthday', WORD_UNKOWN)."-".
                            InputValidator::getRequestData('monthOfBirthday', WORD_UNKOWN)."-".
                            InputValidator::getRequestData('dayOfBirthday', WORD_UNKOWN);

         $data['groupName'] = trim(InputValidator::getRequestData('groupName', ''));
         //$data['pmToUser'] = "Dem User direkt schreiben (PM): http://".$city->getName().".unihelp.de/pm/new/to_".$data['username']."/caption_Meldung auf UniHelp.de\"> oder Mail an ".$data['mail']." oder ins Gaestebuch: http://".$city->getName().".unihelp.de/user/".$data['username'];
         if(!$cUser->isLoggedIn()){
            $data['pmToUser'] = WORD_EMAIL.": ". $data['mail'];
         }else{
            $data['pmToUser'] = WORD_EMAIL.": ". $data['mail'] . '( '.MANTIS_EMAIL_USE_NOTICE . ' )';
         }
         $tmp = GroupModel::getGroupByName($data['groupName']);
         if ($tmp){
            $data['groupId'] = $tmp->id;
         }else{
            $data['groupId'] = false;
         }
         
         $this->data = $data;
    }
    
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
    
} 

class mantisCategoryFactory{
	function getMantisCategory($mantisCategoryName){
		if(F_SOURCE_REPORT_ENTRY == $mantisCategoryName){
			return new mantisReportEntry;
		}
        if(F_SOURCE_ERROR_REPORT == $mantisCategoryName){
            return new mantisErrorReport;
        }
        if(F_SOURCE_DELETE_ENTRY == $mantisCategoryName){
            return new mantisDeleteEntry;
        }
        if(F_SOURCE_GENERAL_QUERY == $mantisCategoryName){
            return new mantisGenerlQuery;
        }
        if(F_SOURCE_FEATURE_REQUEST == $mantisCategoryName){
            return new mantisFeatureRequest;
        }
        if(F_SOURCE_CHANGE_USERNAME == $mantisCategoryName){
            return new mantisChangeUsername;
        }
        if(F_SOURCE_CHANGE_UNI == $mantisCategoryName){
            return new mantisChangeUni;
        }
        if(F_SOURCE_CHANGE_BIRTHDAY == $mantisCategoryName){
            return new mantisChangeBirthday;
        }
        if(F_SOURCE_DELETE_ACCOUNT == $mantisCategoryName){
            return new mantisDeleteAccount;
        }
        if(F_SOURCE_ADD_ME_TO_GROUP == $mantisCategoryName){
            return new mantisAddMeToGroup;
        }
        if(F_SOURCE_DELETE_ME_FROM_GROUP == $mantisCategoryName){
            return new mantisDeleteMeFromGroup;
        }
        if(F_SOURCE_FOUND_GROUP == $mantisCategoryName){
            return new mantisFoundGroup;
        }
        if(F_SOURCE_MISSING_COURSE == $mantisCategoryName){
            return new mantisMissingCourse;
        }
        if(F_SOURCE_MISC == $mantisCategoryName){
            return new mantisMisc;
        }
        if(F_SOURCE_UNKNOWN == $mantisCategoryName){
            return new mantisUnkown;
        }
        if(F_SOURCE_CALENDER == $mantisCategoryName){
        	return new mantisCalender;
        }
      return null;
	}
}

class mantisReportEntry extends mantisCategory{
	
    public $source_cat = F_SOURCE_REPORT_ENTRY;
    public $needLoggin = true;
    
    public function getTemplateVars($isLoggedIn){
    	
        return array(
                "entryNeeded"=>"true",
                "responseNeeded"=>"",
                "reasonNeeded"=>"true",
                "mailNeeded"=>"",
                "titleNeeded"=>"true",
                "queryNeeded"=>"",
                "descriptionNeeded"=>"",
                "source_cat"=>$this->source_cat,
                "_entry"=>InputValidator::getRequestData(F_ENTRY_TEXT, ''),
                "_reason"=>InputValidator::getRequestData('reason', ''),
                "_title"=>InputValidator::getRequestData('title', '')
                );  
    }
    
    public function getCheckedFilds (){
        return array ( "reason" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed" ) );
    }
   
    public function getMantisBug(){
   	    $bug = new BugData(); 
        $data = $this->data;
        $bug->summary = "Eintrag melden (User '".$data['username']."' meldet)";
        $bug->description = "Begruendung: ".$data['reason'];
        $bug->additional_information = 
                        "User: ".$data['username']." (Mail: ".$data['mail'].")\n".
                        "Eintrag: ".$data['entry']."\n".
                        "Link zum Beitrag: ".$data['directlink']."\n".
                        "Quelle: ".$data['source'];
        
        return $bug;
    }
}


class mantisDeleteEntry extends mantisCategory{
    
    public $source_cat = F_SOURCE_DELETE_ENTRY;
    public $needLoggin = true;
    
    public function getTemplateVars($isLoggedIn){
        
        return array(
                "entryNeeded"=>"true",
                "responseNeeded"=>"",
                "reasonNeeded"=>"true",
                "mailNeeded"=>"",
                "titleNeeded"=>"true",
                "queryNeeded"=>"",
                "descriptionNeeded"=>"",
                "source_cat"=>$this->source_cat,
                "_entry"=>InputValidator::getRequestData(F_ENTRY_TEXT, ''),
                "_reason"=>InputValidator::getRequestData('reason', ''),
                "_title"=>InputValidator::getRequestData('title', '')
                );  
    }
    
    public function getCheckedFilds (){
    	return array ( "reason" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed" ) );
    }
    public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $bug->summary = "Eintrag loeschen (User '".$data['username']."' meldet)";
        $bug->description = "Begruendung: ".$data['reason'];
        $bug->additional_information = 
                            "User: ".$data['username']." (Mail: ".$data['mail'].")\n".
                            "Eintrag: ".$data['entry']."\n".
                            "Link zum Beitrag: ".$data['directlink']."\n".
                            "Quelle: ".$data['source'];
       return $bug;
    }    
} 

class mantisErrorReport extends mantisCategory{
	public $source_cat = F_SOURCE_ERROR_REPORT;
    public function getTemplateVars($isLoggedIn){
    	
        return array(
            "source_cat"=>F_SOURCE_ERROR_REPORT,
            "entryNeeded"=>"",
            "responseNeeded"=>"true",
            "reasonNeeded"=>"",
            "mailNeeded"=>"true",
            "titleNeeded"=>"true",
            "queryNeeded"=>"",
            "descriptionNeeded"=>"true",
            "browserNeeded"=>"true",
            "technicalDataNeeded"=>"true",
            "fileNeeded"=>"true",
            "_response"=>InputValidator::getRequestData('response', ''),
            "_mail"=>trim(InputValidator::getRequestData('mail', '')),
            "_title"=>InputValidator::getRequestData('title', ''),
            "_description"=>InputValidator::getRequestData('description', ''),
            "_browser"=>trim(InputValidator::getRequestData('browser', '')),
            "_technicalData"=>trim(InputValidator::getRequestData('technicalData', ''))
            );
    }
    public function getCheckedFilds(){
    	return  array (
                        "mail" => array( "required" => true, "check" => "isValidMail" ),
                        "description" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                        "browser" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                        "title" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed")
                    );
    }
    public function extendetCheck() {
		if ($_FILES['file']['size'] > 307200){
                        return array('file'=> ERR_ATTACHMENT);
                    }
        return array();            
	}
    public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $bug->summary .= " (User '".$data['username']."' meldet)";
        $bug->description = "Fehlerbeschreibung: ".$data['description'];
        $bug->additional_information = 
                            "User: ".$data['username']." (Mail: ".$data['mail'].")\n".
                            "Rueckmeldung erwuenscht: ".$data['response'].
                                (InputValidator::getRequestData('response', false) ? "(".$data['pmToUser'].")" : "")."\n".
                            "Technische Daten: ".$data['technicalData']."\n".
                            "Browser: ".$data['browser']."\n";        
        return $bug;
    }
}

class mantisGenerlQuery extends mantisCategory{
    public $source_cat = F_SOURCE_GENERAL_QUERY;
    public function getTemplateVars($isLoggedIn){
        
            return array(
            "source_cat"=>F_SOURCE_GENERAL_QUERY,
            "entryNeeded"=>"",
            "responseNeeded"=>"true",
            "reasonNeeded"=>"",
            "mailNeeded"=>"true",
            "titleNeeded"=>"true",
            "queryNeeded"=>"true",
            "descriptionNeeded"=>"",
            "phoneNeeded"=>"true",
            "companyNeeded"=>"true",
            "adressNeeded"=>"true",
            "_title"=>trim(InputValidator::getRequestData('title', '')),
            "_mail"=>trim(InputValidator::getRequestData('mail', '')),
            "_response"=>InputValidator::getRequestData('response', ''),
            "_query"=>trim(InputValidator::getRequestData('query', '')),
            "_phone"=>trim(InputValidator::getRequestData('phone', '')),
            "_company"=>trim(InputValidator::getRequestData('company', '')),
            "_adress"=>trim(InputValidator::getRequestData('adress', ''))
            );
    }
    public function getCheckedFilds(){
    	return array (
                        "mail" => array( "required" => true, "check" => "isValidMail" ),
                        "company" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                        "title" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                        "query" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed")
                    );
    }
    public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $bug->summary .= " (User '".$data['username']."' meldet)";
        $bug->description = "Anliegen: ".$data['query'];
        $bug->additional_information = 
                            "User: ".$data['username']." (Mail: ".$data['mail'].")\n".
                            "Firma/Name: ".$data['company']."\n".
                            "Phone: ".$data['phone']."\n".
                            "Adress: ".$data['adress']."\n".
                            "Rueckmeldung erwuenscht: ".$data['response'].(InputValidator::getRequestData('response', false) ? "(".$data['pmToUser'].")" : "");        
        return $bug;
    }
}

class mantisFeatureRequest extends mantisCategory{
    public $source_cat = F_SOURCE_FEATURE_REQUEST;
    public function getTemplateVars($isLoggedIn){
        
        return array(
            "source_cat"=>F_SOURCE_FEATURE_REQUEST,
            "entryNeeded"=>"",
            "responseNeeded"=>"true",
            "reasonNeeded"=>"",
            "mailNeeded"=>"true",
            "titleNeeded"=>"true",
            "queryNeeded"=>"",
            "descriptionNeeded"=>"true",
            "fileNeeded"=>"true",
            "_title"=>trim(InputValidator::getRequestData('title', '')),
            "_mail"=>trim(InputValidator::getRequestData('mail', '')),
            "_response"=>InputValidator::getRequestData('response', ''),
            "_description"=>InputValidator::getRequestData('description', '')
            );
    }
   public function getCheckedFilds(){
   	 return array (
                        "mail" => array( "required" => true, "check" => "isValidMail" ),
                        "description" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                        "title" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed")
                    );
   }
   public function extendetCheck() {
        if ($_FILES['file']['size'] > 307200){
                        return array('file'=> ERR_ATTACHMENT);
                    }
        return array();            
   }
   public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $bug->summary .= " (User '".$data['username']."' meldet)";
        $bug->description = "Funktionsbeschreibung: ".$data['description'];
        $bug->additional_information = 
                    "User: ".$data['username']." (Mail: ".$data['mail'].")\n".
                    "Rueckmeldung erwuenscht: ".$data['response'].(InputValidator::getRequestData('response', false) ? "(".$data['pmToUser'].")" : "");        
        return $bug;
    } 
}

class mantisChangeUsername extends mantisCategory{
    public $source_cat = F_SOURCE_CHANGE_USERNAME;
    public $needLoggin = true;
    public function getTemplateVars($isLoggedIn){
        return array(
                "entryNeeded"=>"",
                "responseNeeded"=>"",
                "reasonNeeded"=>"true",
                "newUsernameNeeded"=>"true",
                "mailNeeded"=>"",
                "titleNeeded"=>"",
                "queryNeeded"=>"",
                "descriptionNeeded"=>"",
                "source_cat"=>$this->source_cat,
                "_reason"=>InputValidator::getRequestData('reason', ''),
                "_newUsername"=>InputValidator::getRequestData('newUsername', '')
                );
        
    }
    public function getCheckedFilds(){
    	return $formFields = array( "reason" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                            "newUsername" => array("required" => true, "check" => "isValidUsername") );
    }
    public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $bug->summary = "Username aendern (User '".$data['username']."' => '".$data['newUsername']."')";
        $bug->description = "Begruendung: ".$data['reason'];
        $bug->additional_information = "User (alt): ".$data['username'].
                    "\nUser (neu): ".$data['newUsername'].
                    "\n(Mail: ".$data['mail'].")".
                    "\nDirektlink zum Aendern: http://".$_SERVER['SERVER_NAME']."/user/".urlencode($data['username'])."/profile/edit";
        return $bug;
    }
}

class mantisChangeUni extends mantisCategory{
    public $source_cat = F_SOURCE_CHANGE_UNI;
    public $needLoggin = true;
    public function getTemplateVars($isLoggedIn){
        
        return array(
                "entryNeeded"=>"",
                "responseNeeded"=>"",
                "reasonNeeded"=>"true",
                "newUniNeeded"=>"true",
                "mailNeeded"=>"",
                "titleNeeded"=>"",
                "queryNeeded"=>"",
                "descriptionNeeded"=>"",
                "source_cat"=>$this->source_cat,
                "_reason"=>InputValidator::getRequestData('reason', ''),
                "_newUni"=>InputValidator::getRequestData('newUni', '')
                ); 
    }
    public function getCheckedFilds(){
    	array( "reason" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"));
    }
    public function extendetCheck(){
    	$uni = UniversityModel::getUniversityById(InputValidator::getRequestData('newUni', 0));
        if($uni == null){
             return array('newUni' => ERR_ERROR_OCCURED);
        }
        return array();
    }
    public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $bug->summary = "Uni ändern (User '".$data['username']."')";
        $bug->description = "Begruendung: ".$data['reason'];
        $bug->additional_information = "User : ".$data['username'].
                    "\nUni (neu): ".$data['newUni'].
                    "\n(Mail: ".$data['mail'].")".
                    "\nDirektlink zum Aendern: http://".$_SERVER['SERVER_NAME']."/user/".urlencode($data['username'])."/profile/edit";        
        return $bug;
    }
}

class mantisChangeBirthday extends mantisCategory{
    public $source_cat = F_SOURCE_CHANGE_BIRTHDAY;
    public $needLoggin = true;
    public function getTemplateVars($isLoggedIn){
        
                $days = range(1,31);
                $days = array_map(create_function('$e', 'return ($e<=9 ? "0".$e : strval($e) );'), $days);
                $months = range(1,12);
                $months = array_map(create_function('$e', 'return ($e<=9 ? "0".$e : strval($e) );'), $months);
                $vars = array(
                "entryNeeded"=>"",
                "responseNeeded"=>"",
                "reasonNeeded"=>"true",
                "newBirthdayNeeded"=>"true",
                "mailNeeded"=>"",
                "titleNeeded"=>"",
                "queryNeeded"=>"",
                "descriptionNeeded"=>"",
                "source_cat"=>$this->source_cat,
                "_reason"=>InputValidator::getRequestData('reason', ''),
                "_newBirthday_Day"=>InputValidator::getRequestData('dayOfBirthday', ''),
                "_newBirthday_Month"=>InputValidator::getRequestData('monthOfBirthday', ''),
                "_newBirthday_Year"=>InputValidator::getRequestData('yearOfBirthday', ''),
                "months"=>$months,"days"=>$days,"years"=>range(1900,2100), "yearMin"=>date('Y')-50, "yearMax"=>date('Y')-16
                );
                return $vars;    
    }
   public function getCheckedFilds(){
   	return array( "reason" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"));
   } 
   public function extendetCheck(){
   	    $day = InputValidator::getRequestData('dayOfBirthday', '');
        $month = InputValidator::getRequestData('monthOfBirthday', '');
        $year = InputValidator::getRequestData('yearOfBirthday', '');
        $birthday = $year.'-'.$month.'-'.$day;
        if (!InputValidator::isValidPastDate($birthday)){
            return array('birthdate'=> ERR_INVALID_BIRTHDATE);
        }

        return array();
   }
   public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $city = CityModel::getLocalCity();
        $bug->summary = "Geburtstag aendern (User '".$data['username']."' meldet)";
        $bug->description = "Begruendung: ".$data['reason'];
        $bug->additional_information = "Neuer Geburtstag (JJJJ-MM-TT): ".$data['newBirthday'].
                    "\n(Mail: ".$data['mail'].")".
                    "\nDirektlink zum Aendern: http://".$_SERVER['SERVER_NAME']."/user/".urlencode($data['username'])."/profile/edit";        
        return $bug;
   }
}

class mantisDeleteAccount extends mantisCategory{
    public $source_cat = F_SOURCE_DELETE_ACCOUNT;
    public $needLoggin = true;
    public function getTemplateVars($isLoggedIn){
        
        return array(
                "source_cat"=>F_SOURCE_DELETE_ACCOUNT,
                "entryNeeded"=>"",
                "responseNeeded"=>"true",
                "reasonNeeded"=>"true",
                "mailNeeded"=>"",
                "titleNeeded"=>"",
                "queryNeeded"=>"",
                "descriptionNeeded"=>"",
                "_reason"=>InputValidator::getRequestData('reason', ''),
                "_reponse"=>InputValidator::getRequestData('response', false)
                );  
    }
   public function getCheckedFilds(){
   	 return array( "reason" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"));
   }
   public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $city = CityModel::getLocalCity();
        $bug->summary = F_SOURCE_DELETE_ACCOUNT." (User '".$data['username']."' meldet)";
        $bug->description = "Begruendung: ".$data['reason'];
        $bug->additional_information = "\n(Mail: ".$data['mail'].")".
                    "\nDirektlink zum Aendern: http://".$_SERVER['SERVER_NAME']."/user/".urlencode($data['username'])."/delete
                    \nRueckmeldung erwuenscht: ".$data['response'].(InputValidator::getRequestData('response', false) ? "(".$data['pmToUser'].")" : "");        
        return $bug;
   } 
}

class mantisAddMeToGroup extends mantisCategory{
    public $source_cat = F_SOURCE_ADD_ME_TO_GROUP;
    public $needLoggin = true;
    public function getTemplateVars($isLoggedIn){
        
        return array(
                "entryNeeded"=>"",
                "responseNeeded"=>"",
                "reasonNeeded"=>"true",
                "mailNeeded"=>"",
                "titleNeeded"=>"true",
                "titleReadonly"=>"true",
                "queryNeeded"=>"",
                "descriptionNeeded"=>"",
                "source_cat"=>$this->source_cat,
                "responseNeeded"=>"true",
                "_response"=>true,
                "groupNameNeeded"=>"true",
                "_reason"=>InputValidator::getRequestData('reason', ''),
                "_title"=>InputValidator::getRequestData('title', translate_mantis(F_SOURCE_ADD_ME_TO_GROUP)),
                "_groupName"=>InputValidator::getRequestData('groupName', ''),
                "_response"=>InputValidator::getRequestData('response', '')
                ); 
        
    }
    public function getCheckedFilds(){
    	return array( "reason" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                "title"=> array("required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                "groupName" => array("required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"));
    }
    public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $city = CityModel::getLocalCity();
        $bug->summary = "User zu Gruppe hinzufügen (User '".$data['username']."' meldet)";
        $bug->description = "Gruppe: '".$data['groupName']."'\nBegruendung: ".$data['reason'];
        $bug->additional_information = "\n(Mail: ".$data['mail'].")".
                    "\nDirektlink zum Aendern: ".($data['groupId'] ? 
                        "http://".$_SERVER['SERVER_NAME']."/orgas/".$data['groupId']."/edit" : 
                        "http://".$_SERVER['SERVER_NAME']."/i_am_god/groups" ).
                    "\nRueckmeldung erwuenscht: ".$data['response'].(InputValidator::getRequestData('response', false) ? "(".$data['pmToUser'].")" : "");        
        return $bug;
    }
}

class mantisDeleteMeFromGroup extends mantisCategory{
    public $source_cat = F_SOURCE_DELETE_ME_FROM_GROUP;
    public $needLoggin = true;
    public function getTemplateVars($isLoggedIn){
        
        return array(
                "entryNeeded"=>"",
                "responseNeeded"=>"",
                "reasonNeeded"=>"true",
                "mailNeeded"=>"",
                "titleNeeded"=>"true",
                "titleReadonly"=>"true",
                "queryNeeded"=>"",
                "descriptionNeeded"=>"",
                "source_cat"=>$this->source_cat,
                "groupNameNeeded"=>"true",
                "responseNeeded"=>"true",
                "_response"=>true,              
                "_reason"=>InputValidator::getRequestData('reason', ''),
                "_title"=>InputValidator::getRequestData('title', translate_mantis(F_SOURCE_DELETE_ME_FROM_GROUP)),
                "_groupName"=>InputValidator::getRequestData('groupName', ''),
                "_response"=>InputValidator::getRequestData('response', '')
                );            
        
    }
   public function getCheckedFilds(){
   	  return  array( "reason" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                "title"=> array("required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                "groupName" => array("required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"));
   }
   public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $city = CityModel::getLocalCity();
        $bug->summary = "User aus Gruppe entfernen (User '".$data['username']."' meldet)";
        $bug->description = "Gruppe: '".$data['groupName']."'\nBegruendung: ".$data['reason'];
        $bug->additional_information = "\n(Mail: ".$data['mail'].")".
                    "\nDirektlink zum Aendern: ".($data['groupId'] ? 
                        "http://".$_SERVER['SERVER_NAME']."/orgas/".$data['groupId']."/edit" : 
                        "http://".$_SERVER['SERVER_NAME']."/i_am_god/groups" ).
                    "\nRueckmeldung erwuenscht: ".$data['response'].(InputValidator::getRequestData('response', false) ? "(".$data['pmToUser'].")" : "");        
        return $bug;
    } 
}

class mantisFoundGroup extends mantisCategory{
    public $source_cat = F_SOURCE_FOUND_GROUP;
    public $needLoggin = true;
    public function getTemplateVars($isLoggedIn){
        
      return $vars = array(
                "entryNeeded"=>"",
                "responseNeeded"=>"",
                "reasonNeeded"=>"",
                "mailNeeded"=>"",
                "titleNeeded"=>"true",
                "titleReadonly"=>"true",
                "queryNeeded"=>"",
                "descriptionNeeded"=>"true",
                "source_cat"=>$this->source_cat,
                "responseNeeded"=>"true",
                "_response"=>true,              
                "groupNameNeeded"=>"true",
                "_description"=>InputValidator::getRequestData('description', ''),
                "_title"=>InputValidator::getRequestData('title', translate_mantis(F_SOURCE_FOUND_GROUP)),
                "_groupName"=>InputValidator::getRequestData('groupName', ''),
                "_response"=>InputValidator::getRequestData('response', '')
                );  
    }
    public function getCheckedFilds(){
    	return  array( "description" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                "title"=> array("required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                "groupName" => array("required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"));
    }
    public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $city = CityModel::getLocalCity();
        $bug->summary = "Gruppe gründen (User '".$data['username']."' meldet)";
        $bug->description = "Gruppe: '".$data['groupName']."'\nBeschreibung: ".$data['description'];
        $bug->additional_information = "\n(Mail: ".$data['mail'].")".
                    "\nDirektlink zum Aendern: http://".$_SERVER['SERVER_NAME']."/i_am_god/groups".
                    "\nRueckmeldung erwuenscht: ".$data['response'].(InputValidator::getRequestData('response', false) ? "(".$data['pmToUser'].")" : "");        
        return $bug;
    }
}

class mantisMissingCourse extends mantisCategory{
    public $source_cat = F_SOURCE_MISSING_COURSE;
    public function getTemplateVars($isLoggedIn){
        
        return array(
            "source_cat"=>F_SOURCE_MISSING_COURSE,
            "entryNeeded"=>"",
            "newCourseNameNeeded"=>"true",
            "responseNeeded"=>"",
            "reasonNeeded"=>"true",
            "mailNeeded"=>"",
            "titleNeeded"=>"",
            "queryNeeded"=>"",
            "descriptionNeeded"=>"",
            "fileNeeded"=>"",
            "_title"=>trim(InputValidator::getRequestData('title', '')),
            "_newCourseName"=>trim(InputValidator::getRequestData('newCourseName','')),
            "_response"=>InputValidator::getRequestData('response', '')
            );
    }
    public function getCheckedFilds(){
    	return array (
                        "newCourseName" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                        "reason" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed")
                   );
    }
    public function getMantisBug(){
        $bug = new BugData(); $data = $this->data;
        $bug->summary = "fehlendes Fach (User '".$data['username']."' meldet)";
        $bug->description = "Name des fehlenden Faches: ".trim(InputValidator::getRequestData('newCourseName','')) .
                             "\n" . $data['reason'];
        $bug->additional_information = 
                            "User: ".$data['username'];        
        return $bug;
    }
}

class mantisMisc extends mantisCategory{
    public $source_cat = F_SOURCE_MISC;
    public function getTemplateVars($isLoggedIn){
        return array();
        
    }
    public function getCheckedFilds(){
    	return array();
    }
    public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $bug->summary .= " (User '".$data['username']."' meldet)";
        $bug->description = "Anliegen: ".$data['query']."\n";
        $bug->additional_information =
                            "User: ".$data['username']." (Mail: ".$data['mail'].")\n".
                            "Rueckmeldung erwuenscht: ".$data['response'].(InputValidator::getRequestData('response', false) ? "(".$data['pmToUser'].")" : "");
        
        return $bug;
    }
}

class mantisUnkown extends mantisCategory{
    public $source_cat = F_SOURCE_UNKNOWN;
    public function getTemplateVars($isLoggedIn){
        
        $contentArray = array(
                    F_SOURCE_ERROR_REPORT=>F_SOURCE_ERROR_REPORT, F_SOURCE_GENERAL_QUERY=>F_SOURCE_GENERAL_QUERY,
                    F_SOURCE_FEATURE_REQUEST=>F_SOURCE_FEATURE_REQUEST, F_SOURCE_MISC => F_SOURCE_MISC,
                    F_SOURCE_ADD_ME_TO_GROUP=>F_SOURCE_ADD_ME_TO_GROUP, F_SOURCE_DELETE_ME_FROM_GROUP=>F_SOURCE_DELETE_ME_FROM_GROUP,
                    F_SOURCE_MISSING_COURSE => F_SOURCE_MISSING_COURSE
                    );
        asort($contentArray);
        
        return array(
                "source_cat"=>F_SOURCE_UNKNOWN,
                "responseNeeded"=>"true",
                "reasonNeeded"=>"",
                "mailNeeded"=>"true",
                "titleNeeded"=>"",
                "queryNeeded"=>"true",
                "descriptionNeeded"=>"",
                "_title"=>trim(InputValidator::getRequestData('title', '')),
                "_mail"=>trim(InputValidator::getRequestData('mail', '')),
                "_response"=>InputValidator::getRequestData('response', ''),
                "_query"=>trim(InputValidator::getRequestData('query', '')),
                "showTitleSelect"=>"true",
                "selectContent"=>$contentArray,
                "showFAQNote"=>true);
    }
    public function getCheckedFilds(){
        return array();
    }
    public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $bug->summary .= " (User '".$data['username']."' meldet)";
        $bug->description = "Anliegen: ".$data['query']."\n";
        $bug->additional_information =
                            "User: ".$data['username']." (Mail: ".$data['mail'].")\n".
                            "Rueckmeldung erwuenscht: ".$data['response'].(InputValidator::getRequestData('response', false) ? "(".$data['pmToUser'].")" : "");
        
        return $bug;
    }
}

class mantisCalender extends mantisCategory{
    public $source_cat = F_SOURCE_UNKNOWN;
    public function getTemplateVars($isLoggedIn){
        
        return array(
            "source_cat"=>F_SOURCE_CALENDER,
            "entryNeeded"=>"",
            "responseNeeded"=>"true",
            "reasonNeeded"=>"",
            "mailNeeded"=>"true",
            "titleNeeded"=>"true",
            "queryNeeded"=>"",
            "descriptionNeeded"=>"true",
            "fileNeeded"=>"",
            "_title"=>trim(InputValidator::getRequestData('title', '')),
            "_mail"=>trim(InputValidator::getRequestData('mail', '')),
            "_response"=>InputValidator::getRequestData('response', ''),
            "_description"=>InputValidator::getRequestData('description', '')
            );
    }
    public function getCheckedFilds(){
    	return array (
                        "mail" => array( "required" => true, "check" => "isValidMail" ),
                        "description" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed"),
                        "title" => array( "required" => true, "check" => "isValidAlmostAlways_NoWhitespaceAllowed")
                    );
    }
    public function getMantisBug(){
        $bug = new BugData(); 
        $data = $this->data;
        $bug->summary .= " (User '".$data['username']."' meldet)";
        $bug->description = "Anmerkung zum Kalender: ".$data['description'];
        $bug->additional_information = 
            "User: ".$data['username']." (Mail: ".$data['mail'].")\n".
            "Rueckmeldung erwuenscht: ".$data['response'].(InputValidator::getRequestData('response', false) ? "(".$data['pmToUser'].")" : "");        
        return $bug;
    }
}
?>
