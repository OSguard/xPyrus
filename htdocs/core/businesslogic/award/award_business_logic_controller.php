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

/**
 *  @todo your includes here
 */
 
require_once CORE_DIR . '/utils/input_validator.php';
require_once CORE_DIR . '/utils/user_ipc.php';
require_once CORE_DIR . '/businesslogic/business_logic_controller.php';

// models for the sql handlings
require_once MODEL_DIR . '/award/award_model.php';
require_once MODEL_DIR . '/award/user_award_model.php';
require_once MODEL_DIR . '/base/user_model.php';


 // directory, where the templates are placed 
define('AWARD_TEMPLATE_DIR', 'modules/award/');

/**
 * @package controller
 * @author Matthias Fansa
 * @version $Id: award_business_logic_controller.php 5807 2008-04-12 21:23:22Z trehn $
 */
class AwardBusinessLogicController extends BusinessLogicController {

    public function __construct($ajaxView = false) {
        parent::__construct($ajaxView);
    }
/**
    * default method, that is executed if no one is explicity called;
    * show the Hall of Fame with Award overview
    * @return string
    */
    protected function getDefaultMethod() {
        return 'main';
    }

/**
    * list of all methods that are allowed
    * @return string
    */  
    public function getAllowedMethods() {
		$array = parent::getAllowedMethods();
        array_push($array,
                   'main',
                   'delete',
                   'deleteRank',
				   'addAward',
				   'modAward',
				   'addUserAward',
				   'editUserAward',
				   'onlineAwards'
				  );				   
        return $array;
    }
	
	/* delete Awards 
	 * like WM, Skat and so on...
	 */
	public function delete(){
		
        $cUser = Session::getInstance()->getVisitor();
        if(!$cUser->hasRight('PROFILE_ADMIN')){
             $this->rightsMissingView('PROFILE_ADMIN');
        }
        
        // get the expected request parameters
		$parameters = $this->getParameters('delete');
		$award = $parameters['award'];
		//shows a delete Confirmation 
		// yes -> delete()
		// no -> turn back to award_template		
         /* is the delete confirmend? */
        if (!array_key_exists('deleteConfirmation', $_REQUEST)) {
            return $this->confirmationView(NAME_AWARD_DEL.': ' . $award->getName(),
                                           DO_ACTION_AWARD_DEL,
                                           '/index.php?mod=award&method=delete&zahl='.$award->id.'&deleteConfirmation=yes',
                                           '/index.php?mod=award');            
        }
            
		$award->delAward();
		header('location:  index.php?mod=award');
	}
	
	/* delete UserAwards
	 * means rankings
	 */
	public function deleteRank(){
		
        $cUser = Session::getInstance()->getVisitor();
        if(!$cUser->hasRight('PROFILE_ADMIN')){
             $this->rightsMissingView('PROFILE_ADMIN');
        }
        
        $parameters = $this->getParameters('deleteRank');
		$UserAward = $parameters['UserAward'];
		$usermodel=UserModel::getUserById($UserAward->getUserId());
		
         /* is the delete confirmend? */
        if (!array_key_exists('deleteConfirmation', $_REQUEST)) {
            return $this->confirmationView(NAME_AWARD_RANG_DEL.': ' . $usermodel->getName(),
                                           DO_ACTION_AWARD_RANG_DEL,
                                           '/index.php?mod=award&method=deleteRank&id='.$UserAward->id.'&deleteConfirmation=yes',
                                           '/index.php?mod=award');            
        }

        self::notifyIPC(new UserIPC($usermodel->id), 'AWARD_CHANGED');
		$UserAward->delUserAward();
		header('location:  /index.php?mod=award');
	}
	
	/*add Awards */
	protected function addAward() {
		
        $cUser = Session::getInstance()->getVisitor();
        if(!$cUser->hasRight('PROFILE_ADMIN')){
             $this->rightsMissingView('PROFILE_ADMIN');
        }
        
        $parameters = $this->getParameters('addAward');
		// instanciate view to operate on
		$main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, AWARD_TEMPLATE_DIR . 'award_add_template.tpl');
		// assign variable to template
		$main->assign('name', $parameters['name']);
		$main->assign('icon', $parameters['icon']);
		$awards=AwardModel::getAll();
		$error=false;
			foreach ($awards as $foo) {
				if ($parameters['name'] == $foo->getName()) {
					$error=true;
				}
			}
				
		// form fields -> parameters and their requirements, found in InputValidator class
        // name is required; if it doesn't exist, an error is detected
        // input field is also checked, if it seems to be a valid human name (i.e. do not contain fancy characters)
		$formFields = array(
                'name'    	=> array('required' => true, 'check' => 'isValidName', 'escape' => true),
				'icon'    	=> array('required' => false, 'check' => 'isValidURL', 'escape' => true),
				'link'    	=> array('required' => false, 'check' => 'isValidURL', 'escape' => true)
									);    
		
		if (array_key_exists('name', $_REQUEST)){
			// validate input according to specification
			// and store ESCAPEd values in our $parameters array 
			$this->validateInput($formFields, $parameters);
			// check for errors
			if (count($this->errors) == 0 and $error==false) {
				// if no error occured, 
				// save input data in a escaped model
				$model = new AwardModel();
				$model->setName($parameters['name']);
				$model->setIcon($parameters['icon']);
				$model->setLink($parameters['link']);
				$model->save(); 
				header('location:  index.php?mod=award');
			}	
			else if ($error==true) {
				$this->errors['name'] = 'Awardname schon vergeben!';
			}
				
		}
		// set central content and do not show an ad banner or breadcrumbs navigation
		$this->setCentralView($main, false, false);
		// display the template
		$this->view();
	}
	
	/*modify Awards 
	 * -> change Name
	 */
	protected function modAward() {
		
        $cUser = Session::getInstance()->getVisitor();
        if(!$cUser->hasRight('PROFILE_ADMIN')){
             $this->rightsMissingView('PROFILE_ADMIN');
        }
        
        $parameters = $this->getParameters('modAward');
		$award = $parameters['award'];
		$main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, AWARD_TEMPLATE_DIR . 'award_edit_template.tpl');
		$main->assign('award', $award);
		$awards=AwardModel::getAll();
		$error=false;
			foreach ($awards as $foo) {
				if ($parameters['name'] == $foo->getName() && $award->id != $foo->id) {
					$error=true;
				}
			}
	
		if ($award == null) {
			$this->errors['award'] = 'Award nicht vorhanden!';
		}
		$formFields = array(
                'name'    	=> array('required' => true, 'check' => 'isValidName', 'escape' => true),
				'icon'    	=> array('required' => false, 'check' => 'isValidURL', 'escape' => true),
				'link'    	=> array('required' => false, 'check' => 'isValidURL', 'escape' => true)
									);              
		// after name was typed in
		if (array_key_exists('name', $_REQUEST)) {
			$this->validateInput($formFields, $parameters);
			$main->assign('name', $parameters['name']); 	
			$main->assign('icon', $parameters['icon']);
			$award->setName($parameters['name']);
			$award->setIcon($parameters['icon']);
			$award->setLink($parameters['link']);
			// in a only name edit case
			if(empty($parameters['icon'])) {
				$award->setIcon(null);
			}
			if(empty($parameters['link'])) {
				$award->setLink(null);
			}
			if (count($this->errors) == 0 and $error==false) {
				$award->save();
				header('location:  index.php?mod=award');
			}
			else if ($error==true) {
				$this->errors['name'] = 'Awardname schon vergeben!';
			}
				
		}
		$this->setCentralView($main, false, false);
		$this->view();
	}
	
	/* adds a Rank to an Award 
	 * -> ranking and username
	 */
	protected function addUserAward() {
		
        $cUser = Session::getInstance()->getVisitor();
        if(!$cUser->hasRight('PROFILE_ADMIN')){
             $this->rightsMissingView('PROFILE_ADMIN');
        }
        
        $parameters = $this->getParameters('addUserAward');
		$main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, AWARD_TEMPLATE_DIR . 'award_addUserAward_template.tpl');
		$award = $parameters['award'];
		$main->assign('award', $award);
		$main->assign('rank', $parameters['rank']);
		$main->assign('person', $parameters['person']);
		
		if ($award == null) {
			$this->errors['award'] = 'Award nicht vorhanden!';
		}
		
		$formFields = array(
                'rank'    	=> array('required' => true, 'check' => 'isValidInteger', 'escape' => true)
									);    
		
		if (array_key_exists('rank', $_REQUEST)) {
			$this->validateInput($formFields, $parameters);
			$main->assign('rank', $parameters['rank']);
			$main->assign('person', $parameters['person']);
			// model to compare with existing Usermodels
			$UserAwards = UserAwardModel::getByAwardId($award->id);
			// new Rank
			$UserAward = new UserAwardModel();
			$UserAward->setAwardId($award->id);
			$UserAward->setRank($parameters['rank']);
			// get Usermodel by Username
			$usermodel = UserModel::getUserByUsername($parameters['person'], true);
			// simply error varible 
			$error = false;
			foreach ($UserAwards as $foo) {
				//get Users for comparing 
				$model = UserModel::getUserById($foo->getUserId());
				// check that user already exists by comparing models
				if($model->equals($usermodel)){
					$error = true;
				}
			}
			// also checked that User exists in database
			if ($usermodel != null and $error == false) {
				$UserAward->setUserId($usermodel -> id);		
			}
			else $this->errors['person'] = 'User nicht vorhanden oder schon belegt!';
			
			if (count($this->errors) == 0) {
                self::notifyIPC(new UserIPC($usermodel->id), 'AWARD_CHANGED');
				$UserAward->save();
				header('location:  index.php?mod=award');
			}
		} 
		$this->setCentralView($main, false, false);
		$this->view();	
	}
	/* for modifying the Ranks*/
	protected function editUserAward() {
		
        $cUser = Session::getInstance()->getVisitor();
        if(!$cUser->hasRight('PROFILE_ADMIN')){
             $this->rightsMissingView('PROFILE_ADMIN');
        }
        
        $parameters = $this->getParameters('editUserAward');
		$main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, AWARD_TEMPLATE_DIR . 'award_editUserAward_template.tpl');
		$UserAward = $parameters['UserAward'];
		$main->assign('UserAward', $UserAward);
		$name=AwardModel::getById($UserAward->getAwardId())->getName();
		$main->assign('name', $name);
		
		if ($UserAward == null) {
			$this->errors['UserAward'] = 'Rang nicht vorhanden!';
		}
		$formFields = array(
                'rank'    	=> array('required' => true, 'check' => 'isValidInteger', 'escape' => true),
				'person'    	=> array('required' => true, 'check' => 'isValidName', 'escape' => true)
									);  
		
		if (array_key_exists('rank', $_REQUEST) and array_key_exists('person', $_REQUEST)) {
			$this->validateInput($formFields, $parameters);
			$main->assign('rank', $parameters['rank']);
			$main->assign('person', $parameters['person']);; 
			$UserAwards = UserAwardModel::getByAwardId($UserAward->getAwardId());			
			$usermodel=UserModel::getUserByUsername($parameters['person'], true);
			$error = false;
			foreach ($UserAwards as $foo) {
				$model = UserModel::getUserById($foo->getUserId());
				if($model->equals($usermodel)){
					$error = true;
				}
			}
			// on RankEdit
			if ($usermodel != null and $parameters['rank'] != $UserAward->getRank() and $error == true) {
				$UserAward->setRank($parameters['rank']);
			}
			// on NameEdit
			else if ($usermodel != null and $error == false and $parameters['person'] != $UserAward->getUsername()) {
				$UserAward->setUserId($usermodel -> id);
			}
			else if ($error == true and $parameters['rank'] == $UserAward->getRank()){
				$this->errors['person'] = 'User schon belegt!';
			}
			
			if (count($this->errors) == 0) {
                self::notifyIPC(new UserIPC($usermodel->id), 'AWARD_CHANGED');
				$UserAward->save();
				header('location:  index.php?mod=award');
			}
		}				
		$this->setCentralView($main, false, false);
		$this->view();
	}
	
	/* main function, shows the Hall of Fame */
    protected function main(){
		$cUser = Session::getInstance()->getVisitor();
        if(!$cUser->hasRight('PROFILE_ADMIN')){
        	 $this->rightsMissingView('PROFILE_ADMIN');
        }
        
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, AWARD_TEMPLATE_DIR . 'award_template.tpl');
		
		$arr=AwardModel::getAll();
		$main->assign('awards', $arr);
	
		
		$this->setCentralView($main, false, false);
        $this->view();
    }
	/*shows a selected OnlineAward Ranking*/
	protected function onlineAwards(){
		
        $cUser = Session::getInstance()->getVisitor();
        if(!$cUser->hasRight('PROFILE_ADMIN')){
             $this->rightsMissingView('PROFILE_ADMIN');
        }
        
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, AWARD_TEMPLATE_DIR . 'award_online_template.tpl');
		$parameters = $this->getParameters('onlineAwards');
		// need to describe onlineAward case
		$var = $parameters['var'];
		$main->assign('var', $var);
		
		if ($var == 'a') {
			// to propose award name
			$titel='OnlineAward: Hoechste Download-Score';
			$oward=UserModel::getTopUsers('courseDownloads');
			
		}
		else if ($var == 'b'){
			$titel='OnlineAward: Die meisten Forenbeitraege';
			$oward=UserModel::getTopUsers('forumEntries');
		}
		else if ($var == 'c'){
			$titel='OnlineAward: Hoechste Upload-Score';
			$oward=UserModel::getTopUsers('courseUploads');
		}
		else if ($var == 'd') {
			$titel='OnlineAward: Hoechste Aktivitaet';
			$oward=UserModel::getTopUsers('onlineActivity');
		}
		$main->assign('titel', $titel);
		$main->assign('name', $parameters['name']);
		
		$formFields = array(
                'name'    	=> array('required' => true, 'check' => 'isValidName', 'escape' => true)
							);   
		
		if ($parameters['name'] != null) {
			$this->validateInput($formFields, $parameters);
			if (count($this->errors) == 0) {
				$i = 1;
				$arr=AwardModel::getAll();
				$names=array();
				$j=0;
				$error=false;
				foreach ($arr as $foo) {
					array_push($names, $foo->getName());
					if ($parameters['name'] == $names[$j]) {
						$error=true;
						if ($error==true){
							$awardid=$foo->id;	
							break;
						}
					}
					else {
						$j=$j+1;
					}
				}
				if ($error == false) {
					$award = new AwardModel();
					$award->setName($parameters['name']);		
					$award->save();
					foreach ($oward as $too) {
						$userAward = new UserAwardModel();
						$userAward->setAwardId($award->id);
						$userAward->setRank($i);
						$userAward->setUserId($too->id);
						$userAward->save();
						$i= $i+1;
					}		
					header('location:  index.php?mod=award');
				}
				else if ($error == true) {
					$z=0;
					$userAward=UserAwardModel::getByAwardId($awardid);
					foreach ($oward as $too) {
							$userAward[$z]->setRank($i);
							$userAward[$z]->setUserId($too->id);
							$userAward[$z]->save();
							$i = $i+1;
							$z=$z+1;
						header('location:  index.php?mod=award');
					}	
				}
			}
		}

		$this->setCentralView($main, false, false);
        $this->view();
	}
			
/**
     * collects and preprocesses REQUEST parameters for the named method
     * @param string method name
     */
	protected function collectParameters($method) {
       // array to store  parameters in
		$parameters = array();
		// collect parameters for the 'modAward' method
		if ('modAward' == $method) {	
			// safely access $_REQUEST['name']
			$parameters['name'] = InputValidator::getRequestData('name', Null);	
			$parameters['award'] = AwardModel::getById(InputValidator::getRequestData('zahl', 0));
			$parameters['icon'] = InputValidator::getRequestData('icon', Null);	
			$parameters['link'] = InputValidator::getRequestData('link', Null);
		}
		
		else if ('onlineAwards' == $method) {
			$parameters['var'] = InputValidator::getRequestData('var', null);	
			$parameters['name'] = InputValidator::getRequestData('name', null);
		}
		
		else if ('addAward' == $method) {	
			$parameters['name'] = InputValidator::getRequestData('name', null);	
			$parameters['icon'] = InputValidator::getRequestData('icon', null);	
			$parameters['link'] = InputValidator::getRequestData('link', null);
		}
		
		else if ('addUserAward' == $method) {	
			$parameters['person'] = InputValidator::getRequestData('person', null);
			$parameters['rank'] = InputValidator::getRequestData('rank', null);
			$parameters['award'] = AwardModel::getById(InputValidator::getRequestData('zahl', 0));
		}
	  
		else if ('editUserAward' == $method) {	
			$parameters['person'] = InputValidator::getRequestData('person', null); 
			$parameters['rank'] = InputValidator::getRequestData('rank', null);
			$parameters['UserAward'] = UserAwardModel::getById(InputValidator::getRequestData('id', 0));
		}
		
		else if ('delete' == $method) {	
			$parameters['award'] = AwardModel::getById(InputValidator::getRequestData('zahl', 0));
		}
		
		else if ('deleteRank' == $method) {	
			$parameters['UserAward'] = UserAwardModel::getById(InputValidator::getRequestData('id', 0));
		}
		
		// save collection for further use
		$this->_parameters[$method] = $parameters;
		// give the parent the possibility to collect some parameters on its own
        parent::collectParameters($method);
    }
}

?>
