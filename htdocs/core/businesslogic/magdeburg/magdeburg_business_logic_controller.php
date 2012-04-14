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
require_once CORE_DIR . '/businesslogic/business_logic_controller.php';

// the directory, where your templates are placed 
define('MAGDEBURG_TEMPLATE_DIR', 'modules/magdeburg/');

define('MR_MRSCAPUS_RATING_ENABLED', true);

/**
 * @package controller
 * @author YOU
 * @version $Id: skeleton_business_logic_controller.php 5743 2008-03-25 19:48:14Z ads $
 */
class MagdeburgBusinessLogicController extends BusinessLogicController {
    
    public function __construct($ajaxView = false) {
        parent::__construct($ajaxView);
    }
    
    // ----------------------------------------------------------------------
    // NECCESSARY OVERRIDDEN METHODS
    //
    
    /**
     * set methods that are allowed for calling by the controller
     */
    protected function getAllowedMethods() {
        return array_merge(parent::getAllowedMethods(),array(
                'mrMrsCampus',
        				'mrMrsCampusCandidates',
        				'mrMrsCampusScore',
        				'mrMrsCampusAdmin'
                )
            );
    }
    
    /**
     * method, that is executed if no one is explicity called
     * @return string
     */
    protected function getDefaultMethod() {
        return 'mrMrsCampus';
    }
    
    public function getMethodObject($method) {
        if ('mrMrsCampus' == $method || 
        	'mrMrsCampusCandidates' == $method ||
          'mrMrsCampusScore' == $method ||
        	'mrMrsCampusAdmin' == $method) {
            return new BLCMethod('Mister &amp; Miss Campus 2008', '', null);
        } 
    }
    
    /**
     * collects and preprocesses REQUEST parameters for the named method
     * @param string method name
     */
    protected function collectParameters($method) {
        // array to store our parameters in
        $parameters = array();
        
        // collect parameters for our 'magic' method below
        if ('magic' == $method) {
            // safely access $_REQUEST['number']
            // 'safely' refers to PHP errors only -- no escaping is done here!
            $parameters['number'] = InputValidator::getRequestData('number', 0);
        }
        else if ('advancedMagic' == $method) {
            // form fields/REQUEST parameters and their requirements
            //
            // firstName is not required, so no error is issued if this field is missing
            // lastName is required; if it doesn't exist, an error is detected
            // both input fields are also checked, if they seem to be valid human names
            //    (i.e. do not contain fancy characters)
            // further validation methods can be found in utils/InputValidator class
            $formFields = array(
                'firstName'      => array('required' => false, 'check' => 'isValidName'),
                'lastName'       => array('required' => true,  'check' => 'isValidName'),
                                );
            // validate input according to our specification
            // and store ESCAPEd values in our $parameters array 
            $this->validateInput($formFields, $parameters);
        }
        /** @todo your methods here */
        
        
        // save collection for further use
        $this->_parameters[$method] = $parameters;

        // give our parent the possibility to collect some parameters on its own
        parent::collectParameters($method);
    }
    
    // LEGACY HACK for sonnenblume
    private static function get_sql_row($q) {
        $DB = Database::getHandle();
        $res = $DB->execute($q);
        foreach ($res as $row) {
            $dummy = array();
            $c = 0;
            foreach ($row as $n => $val) {
                $dummy[$c++] = $val;
            }
            return $dummy;
        } 
    }
    
    protected function mrMrsCampus() {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), MAGDEBURG_TEMPLATE_DIR . 'mr_campus_2007.tpl');
        
        $cUser = Session::getInstance()->getVisitor();
        
        if(array_key_exists('save',$_POST) && $cUser->isRegularLocalUser() && MR_MRSCAPUS_RATING_ENABLED){
         $entertainer = $_REQUEST['entertainer'];
         $canidateId = $_REQUEST['canidateId'];
         if($entertainer > 10){
        		die('hack detected');
         }
         
         $DB = Database::getHandle();
         $q = "INSERT INTO magdeburg.mrs_campus_ratings (candidate, rating, user_id) VALUES (" . $DB->Quote($canidateId) . "," . $DB->Quote($entertainer) . "," . (int) $cUser->id . ")";
         try {
         $res = $DB->execute($q);
         } catch (DBException $e) {
             print "Du hast den Kandidaten schon bewertet";
         }         
        }
        
        $dummy = self::get_sql_row(
        "SELECT c.* 
           FROM magdeburg.mrs_campus_candidates c 
      left join magdeburg.mrs_campus_ratings r 
             on r.candidate=c.id and r.user_id=".(int)$cUser->id." 
          WHERE r.id is null 
       ORDER BY random() 
          LIMIT 1");
        
        $dummy[8] = "http://devel-static.unihelp.de/event/mrmrscampus2008/" . $dummy[1]. ".jpg";
        $main->assign('canidate', $dummy);       

        $votes = self::get_sql_row("SELECT count(*) AS nr FROM magdeburg.mrs_campus_ratings WHERE user_id=" . (int) $cUser->id);
        	$number = self::get_sql_row("SELECT count(*) AS nr FROM magdeburg.mrs_campus_candidates");			
        			
        	$main->assign('countCanidates',$number);
        	$main->assign('countVotes',$votes);
        
        $this->setCentralView($main);
        $this->view();
    }
    
    private static function get_sql_array($q) {
        $DB = Database::getHandle();
        $res = $DB->execute($q);
        $candidates = array();
        foreach ($res as $row) {
            $dummy = array();
            $c = 0;
            foreach ($row as $n => $val) {
                $dummy[$c++] = $val;
            }
		        $dummy[8] = "http://devel-static.unihelp.de/event/mrmrscampus2008/" . $dummy[1]. ".jpg";
            $candidates[] = $dummy;
        }
        return $candidates;
    }
    
    protected function mrMrsCampusCandidates() {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), MAGDEBURG_TEMPLATE_DIR . 'mr_campus_2007_teilnehmer.tpl');
        
        $cUser = Session::getInstance()->getVisitor();
        $q = "SELECT c.* FROM magdeburg.mrs_campus_candidates c ORDER BY c.cam_pic_id DESC";
        
        $main->assign('canidates', self::get_sql_array($q));
        
        $this->setCentralView($main);
        $this->view();
    }
    
    protected function mrMrsCampusScore() {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), MAGDEBURG_TEMPLATE_DIR . 'mr_campus_2007_score.tpl');
        
        $cUser = Session::getInstance()->getVisitor();
        
        $q = "SELECT avg(r.rating) AS score, c.id  FROM mrs_campus_candidates c, mrs_campus_ratings r WHERE r.candidate=c.id AND c.sex = 'f' GROUP BY c.id ORDER BY score DESC LIMIT 10";
        $c = self::get_sql_array($q);
        if ($c) {
            $candidates = array();
            foreach ($c as $row) {
                $DB = Database::getHandle();
                $q = 'SELECT * FROM mrs_campus_candidates WHERE id = ' . (int) $row[1];
                $res = $DB->execute($q);
                foreach ($res as $row2) {
                    $dummy = array();
                    $c = 0;
                    foreach ($row2 as $n => $val) {
                        $dummy[$c++] = $val;
                    }
        		        $dummy[8] = "http://devel-static.unihelp.de/event/mrmrscampus2008/" . $dummy[1]. ".jpg";
                }
                $dummy[count($dummy)] = $row[0];
                $candidates[] = $dummy;
            }
        } else {
            $candidates = $c;
        }
        $main->assign('canidates_w', $candidates);
        
        $q = "SELECT avg(r.rating) AS score, c.id FROM mrs_campus_candidates c, mrs_campus_ratings r WHERE r.candidate=c.id AND c.sex = 'm' GROUP BY c.id ORDER BY score DESC LIMIT 10";
        $c = self::get_sql_array($q);
        if ($c) {
            $candidates = array();
            foreach ($c as $row) {
                $DB = Database::getHandle();
                $q = 'SELECT * FROM mrs_campus_candidates WHERE id = ' . (int) $row[1];
                $res = $DB->execute($q);
                foreach ($res as $row2) {
                    $dummy = array();
                    $c = 0;
                    foreach ($row2 as $n => $val) {
                        $dummy[$c++] = $val;
                    }
        		        $dummy[8] = "http://devel-static.unihelp.de/event/mrmrscampus2008/" . $dummy[1]. ".jpg";
                }
                $dummy[count($dummy)] = $row[0];
                $candidates[] = $dummy;
            }
        } else {
            $candidates = $c;
        }
        $main->assign('canidates_m', $candidates);
        
        $this->setCentralView($main);
        $this->view();
    }
    
    protected function mrMrsCampusAdmin() {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), MAGDEBURG_TEMPLATE_DIR . 'mr_campus_2007_admin.tpl');
        
        $cUser = Session::getInstance()->getVisitor();
        if (!$cUser->isRegularLocalUser())
            return;
        
            $error = null;
        if(array_key_exists('save',$_POST)){
            $DB = Database::getHandle();
            $pic_id = $_REQUEST['pic_id'];
        	    $vorname = $_REQUEST['vorname'];
        	    $nachname = $_REQUEST['nachname'];
        	    $gender = $_REQUEST['gender'];
        	    $study_path = $_REQUEST['study_path'];
        	    $telefon = $_REQUEST['telefon'];
        	    $email = $_REQUEST['email'];
        	    $entertainer = $_REQUEST['entertainer'];
        	    $sports = $_REQUEST['sports'];
        	    $sexy = $_REQUEST['sexy'];
        	    $brain = $_REQUEST['brain'];
          
        	if(empty($vorname) || empty($nachname)){
        				$error = 'Namen nicht vollstaendig';
        	}
        	elseif(empty($pic_id) or !ctype_digit($pic_id)){
        	      $error = 'keine Pic Id';
        				}
        	elseif($gender != 'f' && $gender != 'm'){
        	      $error = 'falsches Geschlecht';
        				}
        	elseif( ($entertainer+$sports+$sexy+$brain)>10 ){
        	      $error = 'Summe der Werte zu hoch!!';
        				}
        	else{
        	    
        				 $q = "INSERT INTO mrs_campus_candidates 
        				  (cam_pic_id, first_name, last_name, sex
        				  ,  study_path, telephone, email, rating_entertainer,
        				  rating_sports, rating_sexy, rating_brain)
        				  VALUES (" . $DB->Quote($pic_id) . ", " . $DB->Quote($vorname) . ", " . $DB->Quote($nachname) . ", " . $DB->Quote($gender) . ", " . $DB->Quote($study_path) . ", " . $DB->Quote($telefon) . ",
        				   " . $DB->Quote($email) . ", " . $DB->Quote($entertainer) . ", " . $DB->Quote($sports) . ", " . $DB->Quote($sexy) . ", " . $DB->Quote($brain) . ")";
        				try{
                         $DB->execute($q);
                        }catch(DBException $e){
                        	echo "Schon eingetragen";
                        }
        	}
        
        }
        
        $main->assign('study_path', StudyPathModel::getAllStudyPaths());
        $main->assign('my_error', $error);
        
        $this->setCentralView($main);
        $this->view();
    }
}

?>
