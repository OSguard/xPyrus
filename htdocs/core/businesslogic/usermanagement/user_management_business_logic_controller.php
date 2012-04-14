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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/businesslogic/usermanagement/user_management_business_logic_controller.php $

require_once MODEL_DIR . '/base/university_model.php';
require_once MODEL_DIR . '/base/user_protected_model.php';
require_once MODEL_DIR . '/base/friend_model.php';
require_once MODEL_DIR . '/base/foe_model.php';
require_once MODEL_DIR . '/base/person_type_model.php';
require_once MODEL_DIR . '/base/email_regexp_model.php';
require_once MODEL_DIR . '/user/user_canvass.php';
require_once CORE_DIR . '/utils/input_validator.php';
require_once CORE_DIR . '/utils/mailer.php';
require_once CORE_DIR . '/utils/global_ipc.php';

require_once CORE_DIR . '/businesslogic/business_logic_controller.php';

define('USER_MANAGEMENT_TEMPLATE_DIR', 'modules/usermanagement/');

/**
 * @package Controller
 * @author kyle, linap
 * @version $Id: user_management_business_logic_controller.php 5873 2008-05-03 10:42:23Z schnueptus $
 */
class UserManagementBusinessLogicController extends BusinessLogicController {
    
    public function __construct($ajaxView = false) {
    	parent::__construct($ajaxView);
    }
        
    /**
     * set methods that are allowed for calling by the controller
     */
    protected function getAllowedMethods() {
        // allow viewing and some data manipulation
        $methods = array_merge(parent::getAllowedMethods(),
            array (
                'canvassUser',
                'registerUser',
                'activateUser',
                'activateProfile',
                'passwordLost',
                
                'addUser',
                'createGuestUser',
                
                'changeProfile',
                'featureManagement',
                'contactData',
                'privacy',
                'courses',
                'friendlist',
                'configBoxes',
                
                'deleteAccount',
                
                'searchUser',
                'searchUserAdvanced',
                
                'ajaxSearchUser',
                'ajaxCheckUsername',
                'ajaxGetMaildomains',
                'ajaxFriendlist',
                'ajaxConfigBoxes',
                'ajaxBoxMinimize',
                'ajaxBoxMaximize',
                'ajaxBoxClose',
                'ajaxCourse'
            )
        );
        
        if (defined('LEGACY_RELEASE_DATE')) {
            array_push($methods, 'legacyTermsOfUse');
        }
        
        return $methods;
    }
    
    protected function getDefaultMethod() {
        return 'changeProfile';
    }
    
    private static function getUserinfoMethodObject() {
        return ControllerFactory::createControllerByName('userinfo')->getMethodObject('showUserInfo');
    }
    
    public function getMethodObject($method) {
        $parameters = $this->getParameters($method);
        if ('canvassUser' == $method) {
            return new BLCMethod(NAME_USER_CANVASS,
                '/canvassuser',
                BLCMethod::getDefaultMethod());
        } else if ('searchUserAdvanced' == $method) {
            return new BLCMethod(NAME_USER_SEARCH,
                rewrite_usermanagement(array('usersearch' => true)),
                BLCMethod::getDefaultMethod());
        } else if ('registerUser' == $method) {
            return new BLCMethod(NAME_USER_NEW,
                rewrite_usermanagement(array('newuser' => true)),
                BLCMethod::getDefaultMethod());
        } else if ('activateUser' == $method) {
            return new BLCMethod(NAME_USER_ACTIVATE,
                rewrite_usermanagement(array('activate' => true)),
                BLCMethod::getDefaultMethod());
        } else if ('passwordLost' == $method) {
            return new BLCMethod(NAME_USER_PASSWORD_LOST,
                '/passwordlost',
                BLCMethod::getDefaultMethod());
        } else if ('changeProfile' == $method) {
            return new BLCMethod(NAME_USER_PROFILE,
                rewrite_usermanagement(array('profile' => $parameters['user'], 'edit' => $parameters['adminMode'])),
                self::getUserinfoMethodObject());
        } else if ('contactData' == $method) {
            return new BLCMethod(NAME_USER_PROFILE,
                rewrite_usermanagement(array('contactData' => $parameters['user'], 'edit' => $parameters['adminMode'])),
                self::getUserinfoMethodObject());
        } else if ('privacy' == $method) {
            return new BLCMethod(NAME_USER_PROFILE,
                rewrite_usermanagement(array('privacy' => $parameters['user'], 'edit' => $parameters['adminMode'])),
                self::getUserinfoMethodObject());
        } else if ('courses' == $method) {
            return new BLCMethod(NAME_USER_PROFILE,
                rewrite_usermanagement(array('courses' => $parameters['user'], 'edit' => $parameters['adminMode'])),
                self::getUserinfoMethodObject());
        } else if ('configBoxes' == $method) {
            return new BLCMethod(NAME_USER_PROFILE,
                rewrite_usermanagement(array('boxes' => $parameters['user'], 'edit' => $parameters['adminMode'])),
                self::getUserinfoMethodObject());
        } else if ('friendlist' == $method) {
            return new BLCMethod(NAME_USER_PROFILE,
                rewrite_usermanagement(array('friendlist' => $parameters['user'], 'edit' => $parameters['adminMode'])),
                self::getUserinfoMethodObject());
        } else if ('featureManagement' == $method) {
            return new BLCMethod(NAME_USER_PROFILE,
                rewrite_usermanagement(array('features' => $parameters['user'], 'edit' => $parameters['adminMode'])),
                self::getUserinfoMethodObject());
        } else if ('deleteAccount' == $method){
        	$parent = $this->getMethodObject('changeProfile');
            return new BLCMethod(NAME_USER_DELETE,
                rewrite_usermanagement(array('delete' => $parameters['user'])),
                $parent);
        }
        
        return parent::getMethodObject($method);
    }
    
    protected static function getSpecificRight($method) {
        $specificRight = '';
        
        if ('changeProfile' == $method or
            'contactData' == $method or
            'privacy' == $method or
            'courses' == $method or
            'deleteAccount' == $method) 
        {
            $specificRight = 'PROFILE_MODIFY';
        }
        else if ('friendlist' == $method) {
            $specificRight = 'FRIENDLIST_MODIFY';
        } else if ('featureManagement' == $method) {
            $specificRight = 'FEATURE_SELECT';
        } else if ('configBoxes' == $method) {
            $specificRight = 'FEATURE_BOX_REARRANGEMENT';
        }
        
        return $specificRight;
    }
    
    protected function collectParameters($method) {
        $parameters = array();
        $specificRight = self::getSpecificRight($method);
        
        $parameters['adminMode'] = false;
        $user = null;
        if ($specificRight) {
            $cUser = Session::getInstance()->getVisitor();
    
            if (array_key_exists('edit', $_REQUEST) and array_key_exists('username', $_REQUEST) and 
                    $cUser->hasRight('PROFILE_ADMIN')) {
                $username = $_REQUEST['username'];
                // check in session for user to edit
                // if not found, load user by username and store model in session
                if (!($user = Session::getInstance()->getUserData('userToEdit')) or
                        $user->username != $username) {
                    $user = UserProtectedModel::getUserByUsername($username, false);
                    if ($user != null) {
                        Session::getInstance()->storeUserData('userToEdit', $user);
                    }
                }
                $parameters['adminMode'] = true;
            } else if ($cUser->hasRight($specificRight)) {
                // reload user model
                $user = UserProtectedModel::getUserById($cUser->id, false);
            }
        }
        $parameters['user'] = $user;
        
        $this->_parameters[$method] = $parameters;

        parent::collectParameters($method);
    }
    
    
    /**
     * method to canvass a User not register in the System
     */
    protected function canvassUser(){
    	/* get the current user */
        $cUser = Session::getInstance()->getVisitor();
        
        if(!$cUser->isLoggedIn()){
        	$this->errorView(ERR_USER_CANVASS_LOGOFF);
        }
        if($cUser->isExternal()){
        	$this->errorView(ERR_NO_EXTERN);
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'user_canvass.tpl');
        
        $main->assign('points',PointSourceModel::getPointSourceByName('USER_CANVASS'));
        
        $this->setCentralView($main);
        if(array_key_exists('save', $_POST)){
        	
            $canvassName = $_POST['canvassname'];
            $canvassEmail = $_POST['canvassemail'];
            
            if (!InputValidator::isValidName($canvassName) ) {
                $this->errors['canvassname'] = ERR_USER_CANVASS_NAME;
            }
            if (!InputValidator::isValidMail($canvassEmail, true) ) {
                $this->errors['canvassemail'] = ERR_USER_CANVASS_EMAIL;
            }
            if(count($this->errors) == 0){
            	
                $data = array('user_id' => $cUser->id,
                              'email' => $canvassEmail);
                $UserCanvass = new UserCanvass($data);
                $UserCanvass->save();
                
                $mail = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'mail/user_canvass.tpl');

                $mail->assign('canvassName', $canvassName);
                $mail->assign('username', $cUser->getUsername());
                $mail->assign('canvassText',$_POST['canvasstext']);
                $mail->assign('registrationURL', 
                    'http://' . CityModel::getLocalCity()->getSchemaName() . '.unihelp.de' . 
                     rewrite_usermanagement(array('newuser' => 1)) . '?ccode=' . $UserCanvass->getHash());

                $mailBody = &$mail->fetch();
                Mailer::send($cUser->getUsername(), 'register@unihelp.de', $canvassEmail, 'UniHelp.de jetzt Anmelden', $mailBody, false, null);
                
                $main->assign('success', true);
            }
            
        }
        
        
        $this->view();
    }
    
    protected function createGuestUser() {
        if (!Session::getInstance()->getVisitor()->hasRight('USER_CREATE')) {
            $this->rightsMissingView('USER_CREATE');
            exit;
        }
        
        $username = '';
        $password = '';
        
        if (InputValidator::getRequestData('createGuest-submit', false)) {
            $username = 'Guest' . time();
            $password = substr(base64_encode( pack( "H*", sha1( uniqid(rand())) ) ), 0, 8);
            $uni = current(UniversityModel::getAllUniversities());
            $email = ADMIN_MAIL;
            $user = UserProtectedModel::createFromRegisterData($username, $password, $uni->id, PersonTypeModel::getPersonTypeByName('unbekannt'));
    
            $user->setUniEmail('dummy_' . $username . '@localhost');
            $user->setPrivateEmail($email);
            $user->setPublicEmail($email);
            
            $user->setActivated(true);
            $user->setInvisible(true);
            $user->setFirstLogin(date('Y-m-d H:i:s'));
            
            $user->setPersonTypeId(PersonTypeModel::getPersonTypeByName('Gast-Zugang')->id);
            
            $DB = Database::getHandle();
            $DB->StartTrans();
            
            $user->save();
            
            $user->setExpirationDate(time() + 86400 * InputValidator::getRequestData('validity-period', 1));
            
            $role = RoleModel::getRoleByName('guests');
            $role->addUsers(array($user));
            
            $DB->CompleteTrans();
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'create_guest.tpl');
        $main->assign('guest_username', $username);
        $main->assign('guest_password', $password);
        $this->setCentralView($main);
        
        $this->view();
    }
    
    protected function addUser() {
    	if (!Session::getInstance()->getVisitor()->hasRight('USER_CREATE')) {
    	    $this->rightsMissingView('USER_CREATE');
            exit;
    	}
        
        $this->registerUser(true);
        self::notifyIPC(new GlobalIPC, 'NEW_USER');
    }
    
    /**
     * Present a Form where the user enter his own data.
     */
    protected function registerUser($lazyCheck = null) {
        if ($lazyCheck === null) {
            $lazyCheck = array_key_exists('lazyCheck', $_REQUEST);
        }
        $emailRegexps = EmailRegexpModel::getDistinctByDomainPartAndCity();
        
        /* we only show the form */;
        if(!array_key_exists('save', $_REQUEST)) {
            $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'user_new.tpl');
            $main->assign('universities', UniversityModel::getAllUniversities());
            $main->assign('emailRegexps', $emailRegexps);
            $main->assign('lazyCheck', $lazyCheck);
            if (array_key_exists('ccode', $_REQUEST)) {
                $main->assign('canvassCode', $_REQUEST['ccode']);
            }
            $this->setCentralView($main, false);

            $this->view();
            return;
        }
        
        $username = trim($_REQUEST['username']);
        $password = $_REQUEST['password'];
        $passwordCheck = $_REQUEST['passwordCheck'];
        $privateEmail = $_REQUEST['privateEmail'];
        $uniEmail = $_REQUEST['uniEmail'];
        $uniId = trim($_REQUEST['uniId']);
        $uniEmailDomain = $_REQUEST['uniEmailDomain'];
        
        if(!array_key_exists('accept_terms_of_use',$_REQUEST) || $_REQUEST['accept_terms_of_use'] != 'accepted' ){
        	$this->errors['terms_of_use'] = ERR_INVALID_TERMS_OF_USE;
        }
        
        /* test that the username only contain valid chars and is not registered */
        if (!InputValidator::isValidUsername($username)) {
            $this->errors['username'] = ERR_USER_REGISTRATION_INVALID_USERNAME;
        } else if (UserProtectedModel::userAlreadyExists($username)) {
            $this->errors['username'] = ERR_USER_REGISTRATION_EXISTING_USERNAME;
        }
            
        /* test that the password is valid */
        if (!InputValidator::isValidPassword($password)) {
            $this->errors['password'] = ERR_USER_REGISTRATION_INVALID_PASSWORD;
        }
            
        /* and that booth are identicall */
        if ($password != $passwordCheck) {
            $this->errors['password'] = ERR_USER_REGISTRATION_NONMATCHING_PASSWORD;
        }
        
        // check if mail address makes sense
        if ($privateEmail != '' and !InputValidator::isValidMail($privateEmail)) {
            $this->errors['email'] = ERR_USER_REGISTRATION_INVALID_PRIVATE_EMAIL;
        }
        
        if ($uniId == null) {
            $this->errorView('registerUser: keine Uni!');
        }
        
        $uni = UniversityModel::getUniversityById($uniId);

        if($uni == null)
            $this->errorView('registerUser: keine Uni mit der id gefunden!');
        
        $uEmail = $uniEmail . '@' . EmailRegexpModel::getEmailRegexpById($uniEmailDomain)->displayedDomainPart;
        
        if (!$lazyCheck) {   
            /* test that the email address matches the uni */
            $validEmailRegexp = $uni->isValidEmailAddress($uEmail);
            if ($validEmailRegexp === false) {
                $this->errors['uniEmailDomain'] = ERR_USER_REGISTRATION_NONMATCHING_UNIMAIL;
            } else if (UserProtectedModel::isUsedUniMailAddress($uEmail)) {
                $this->errors['uniEmail'] = ERR_USER_REGISTRATION_EXISTING_UNIMAIL;
            }
        }
        
        /* errors where found so represent the form */    
        if(count($this->errors) != 0) {
            $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'user_new.tpl');
            $main->assign('universities', UniversityModel::getAllUniversities());
            $main->assign('emailRegexps', $emailRegexps);

            $main->assign('username', $username);
            $main->assign('privateEmail', $privateEmail);
            $main->assign('uniEmail', $uniEmail);
            $main->assign('uniId', $uniId);
            $main->assign('uniEmailDomain', $uniEmailDomain);
            
            
            $this->setCentralView($main, false);
            $this->view();
            return;
        }
        
        // we can safely proceed the registration process
        
        // look for canvass code
        if (array_key_exists('ccode', $_REQUEST)) {
            // try to find canvassing user
            $canvassUser = UserProtectedModel::getUserByCanvass($_REQUEST['ccode']);
            if ($canvassUser != null) {
                $ps = PointSourceModel::getPointSourceByName('USER_CANVASS');
                
                $DB = Database::getHandle();
                $DB->StartTrans();
                
                $canvassUser->increaseUnihelpPoints($ps->getPointsSum(), 
                                                    $ps->getPointsFlow());
                $canvassUser->save();
                
                // delete used canvass code
                UserCanvass::deleteCanvassByHash($_REQUEST['ccode']);
                
                $DB->CompleteTrans();
                
                $userIPC = new UserIPC($canvassUser->id);
                $userIPC->setTime('POINTS_CHANGED');
            }
        }
        
        $user = UserProtectedModel::createFromRegisterData($username, $password, $uniId,
            (isset($validEmailRegexp)) 
                ? $validEmailRegexp->personTypeId 
                : PersonTypeModel::getPersonTypeByName('unbekannt') );
        // set uni email as default if user doesn't give private mail address
        if ($privateEmail == '') {
            $privateEmail = $uEmail;
        }
        $user->setUniEmail($uEmail);
        $user->setPrivateEmail($privateEmail);
        $user->setPublicEmail($privateEmail);
        $user->save();
        
        if (!$lazyCheck) {
            $actString = $user->setActivationString();
          
            
            $mail = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'mail/register_mail.tpl');
            $mail->assign('newUser', $user);
            $mail->assign('actString',$actString);
            $mail->assign('local_city', CityModel::getLocalCity());
            
            $mailBody = &$mail->fetch();
            Mailer::send('UniHelp.de','noreply@unihelp.de', $user->getUniEmail(), 'Aktivierungs-E-Mail UniHelp.de', $mailBody, true, 'registration');
	        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'user_new_success.tpl');
                                      
            $this->setCentralView($main, false);
            $this->view();
            
            if (NO_REGISTER_VALIDATION) {
                echo 'Congratulations, you are registered! Your activation string is ' . $actString . '<br />' .
                    '<a href="' . rewrite_usermanagement(array('activate' => $actString)) . '">Click here for activation</a>.'
                    ;
                $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'user_new_success.tpl');
                                          
                $this->setCentralView($main);
                $this->view();
            }

        } else {
            $user->setActivated(true);
            $user->save();
            
        	header('Location: ' . rewrite_usermanagement(array('extern' => 1, 'profile' => $user)));
        }
    }
    
    /**
     * Presents a form where user is notified about his activation
     */
    protected function activateUser() {
        if (!array_key_exists('activation', $_GET)) {
        	Logging::getInstance()->logSecurity('wrong parameter');
        }
        
        // set privacy context
        // grant access to all details which are classified up to no one
        PrivacyContext::getContext()->setLevelByName('no one');
        
        $actString = $_GET['activation'];
        $user = UserProtectedModel::getUserByActivationString($actString);
        
        // activation has failed by default
        $activationStatus = 'fail';
        if ($user != null) {
            Session::getInstance()->storeUserData('activateUser', $user);
            
            // mark successful activation progress
            $activationStatus = 'success';
        }

        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            USER_MANAGEMENT_TEMPLATE_DIR . 'activate_' . $activationStatus . '.tpl');
        
        $main->assign('user', $user);
        $main->assign('study_paths', StudyPathModel::getAllStudyPaths(($user != null) 
                                                                          ? $user->getUniId()
                                                                          : 0 ));
        
        $this->setCentralView($main, false);
        $this->view();
    }
    
    protected function passwordLost() {
        $wrongData = null;
        if (array_key_exists('lost_submit', $_REQUEST)) {
        	// form fields and their requirements
            $formFields = array(
                'lost_username'    => array('required' => true,  'check' => 'isValidAlmostAlways', 'escape' => false),
                'lost_email'       => array('required' => true,  'check' => 'isValidAlmostAlways'),
                               );
            $this->validateInput($formFields);
            $wrongData = count($this->errors) != 0;
            
            // set privacy context
            // grant access to all details which are classified up to no one
            PrivacyContext::getContext()->setLevelByName('no one');
            
            if (!$wrongData) {
            	$user = UserProtectedModel::getUserByUsername($_POST['lost_username']);
                if ($user != null) {
                    $lostEMail = strtolower($_POST['lost_email']); 
                	if (!(strtolower($user->getPrivateEmail()) == $lostEMail or
                          strtolower($user->getPublicEmail()) == $lostEMail or
                          strtolower($user->getUniEmail()) == $lostEMail)) {
                        $wrongData = true;
                    }
                } else {
                	$wrongData = true;
                }
            }
            if (!$wrongData) {
                $newPassword = substr(base64_encode( pack( "H*", sha1( uniqid(rand())) ) ), 0, 8);
                
                // keep originalPassword property
                // change only main password
                $user->setPassword($newPassword);
                
                $user->save();
                                
                $mail = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'mail/password_lost.tpl');

                $mail->assign('username', $user->getUsername());
                $mail->assign('password',$newPassword);
                $mail->assign('URL', 
                    'http://' . CityModel::getLocalCity()->getSchemaName() . '.unihelp.de/home');

                $mailBody = &$mail->fetch();
                Mailer::send($user->getUsername(), 'noreply@unihelp.de', $_POST['lost_email'], 'UniHelp.de - Neues Passwort', $mailBody, true, 'password_lost');

                $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'password_lost_success.tpl');
                $this->setCentralView($main);
                $this->view();
                return;
            }
        }
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            USER_MANAGEMENT_TEMPLATE_DIR . 'password_lost.tpl');
        
        $main->assign('wrong_data', $wrongData);
        $this->setCentralView($main);
        $this->view();
    }
    
    protected function activateProfile() {
        $user = Session::getInstance()->getUserData('activateUser');
        
        // in addMode no save is performed, but a new
        // form for study path selection will be added
        $addMode = array_key_exists('add_studypath_form', $_POST);
       
        // check, if changes of profile are POSTed
        if (array_key_exists('change_profile', $_POST)) {
            // form fields and there requirements
            $formFields = array(
                'first_name'       => array('required' => true, 'check' => 'isValidName'),
                'last_name'        => array('required' => true, 'check' => 'isValidName'),
                'gender'           => array('required' => true, 'check' => 'isValidGender'),
                'study_path0'      => array('required' => true, 'check' => 'isValidStudyPath'),
                               );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);
            
            $user->setGender($_POST['gender']);
            $user->setFirstName($_POST['first_name']);
            $user->setLastName($_POST['last_name']);
            
            $user->setBirthdate($_POST['Date_Day'], $_POST['Date_Month'], $_POST['Date_Year']);
            if (!checkdate($_POST['Date_Month'], $_POST['Date_Day'], $_POST['Date_Year'])) {
                self::addIncompleteField($this->errors, 'birthdate');
            }
            
            $studyPathsId = array();
            $s = 0;
            while (array_key_exists('study_path' . $s, $_POST) and $_POST['study_path' . $s]) {
                array_push($studyPathsId, $_POST['study_path' . $s]);
                ++$s;
            }
            // make the ids unique
            $studyPathsId = array_unique($studyPathsId);
            if ($s == 0) {
                self::addIncompleteField($this->errors, 'study_path0');
            }
            
            // if study paths have changed, update value
            if ($user->getStudyPathsId() != $studyPathsId) {
                $user->setStudyPathsId($studyPathsId);
            }
            
            // if no errors have occured and "save"-button has been clicked,
            // save changes
            if (count($this->errors) == 0) {
                $user->save();
                $user->activate();
                
                Session::getInstance()->setVisitor($user);
                
                self::notifyIPC(new GlobalIPC, 'NEW_USER');
                
                //add new Welcome PM
                require_once CORE_DIR . '/utils/notifier_factory.php';
                $text = ViewFactory::getSmartyView(USER_TEMPLATE_DIR, 'mail/user_welcome.tpl');
                $text->assign('user', $user);
                $notifyer = NotifierFactory::createNotifierByName('pm');
                $success = $notifyer->notify($user, CAPTION_WELCOME_NEW, $text->fetch());
                
                // handle automatic login
                require_once CORE_DIR . '/utils/login_handler.php';
                LoginHandler::triggerForcedLogin($user);
                
                // forward user to start page
                header('Location: ' . rewrite_index(array('extern' => true)));
                return;
            }
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            USER_MANAGEMENT_TEMPLATE_DIR . 'activate_success.tpl');
        
        $main->assign('user', $user);
        $main->assign('add_mode', $addMode);
        $main->assign('study_paths', StudyPathModel::getAllStudyPaths($user->getUniId()));
        
        $this->setCentralView($main);
        $this->view();
    }
    
    protected function changeProfile() {
        // display "change profile"-page by default at once 
        $display = true;
        $main = null;
        $cUser = Session::getInstance()->getVisitor();

        $parameters = $this->getParameters('changeProfile');
        if ($parameters['user'] == null and !$parameters['adminMode']) {
            $this->rightsMissingView(self::getSpecificRight('changeProfile'));
        } else if ($parameters['user'] == null) {
            $this->errorView(ERR_NO_USER);
        }
        
        if ($parameters['adminMode']) {
            $this->addLog('view and/or change of a user profile');
        }
        
        // set privacy context
        // grant access to all details which are classified up to no one
        PrivacyContext::getContext()->setLevelByName('no one');
        
        
        $main = $this->_changeProfile($parameters['adminMode'], $parameters['user'], $parameters['adminMode'], $display);
    }
    
    /**
     * @param boolean $display if set to false, smarty-view is returned and not displayed
     */
    protected function _changeProfile($adminMode, $user, $showAllOptions = true, $display = true) {

        // in addMode no save is performed, but a new
        // form for study path selection will be added        
        $addMode = array_key_exists('add_studypath_form', $_POST);
        $setNewPassword = false;

        // check, if changes of profile are POSTed
        if (array_key_exists('changeprofile_form', $_POST)) {
            // form fields and their requirements
            $formFields = array(
                'gender'           => array('required' => true,  'check' => 'isValidGender'),
                'flirt_status'     => array('required' => true,  'check' => 'isValidFlirtStatus'),
                'homepage'         => array('required' => false, 'check' => 'isValidURL'),   
                                );
                 
            if($adminMode !== true){
            	// do not list signature and description here, because they will automatically be parsed (and escaped)
                $formFields['study_path0'] = array('required' => true,  'check' => 'isValidStudyPath');
            }                    
                                
            // hack for homepages
            // they must start with http(s?)
            if (strlen($_REQUEST['homepage']) > 0 and strpos($_REQUEST['homepage'], 'http') !== 0) {
                $_REQUEST['homepage'] = 'http://' . $_REQUEST['homepage'];
            }
            
            $formFields['first_name'] = array('required' => false,  'check' => 'isValidName');
            $formFields['last_name']  = array('required' => false,  'check' => 'isValidName');
            
            // some validity checks and HTML-escape
            $this->validateInput($formFields);
            
            if ($showAllOptions) {
                // change username if required
                if($user->username != $_REQUEST['username_new']){
                    if (InputValidator::isValidUsername($_REQUEST['username_new'])) {                    
                        if(!UserProtectedModel::userAlreadyExists($_REQUEST['username_new'])){
                        	$user->setUsername($_REQUEST['username_new']);
                        }else{
                        	self::addIncompleteField($this->errors, 'username_new',ERR_USER_REGISTRATION_EXISTING_USERNAME);
                        }                        
                        
                    } else {
                        self::addIncompleteField($this->errors, 'username_new',ERR_USER_REGISTRATION_INVALID_USERNAME);
                    }
                }
                
                $user->setActivated(array_key_exists('flag_activated', $_REQUEST));
                $user->setActive(array_key_exists('flag_active', $_REQUEST));
                $user->setInvisible(array_key_exists('flag_invisible', $_REQUEST));
                
                $user->setUniId($_REQUEST['uni_id']);
                
                if (!checkdate($_REQUEST['Date_Month'], $_REQUEST['Date_Day'], $_REQUEST['Date_Year'])) {
                    self::addIncompleteField($this->errors, 'birthdate');
                }
				else{
					$user->setBirthdate($_REQUEST['Date_Day'], $_REQUEST['Date_Month'], $_REQUEST['Date_Year']);
				}
                
                // TODO: person type
            }
            
            $user->setFirstName($_REQUEST['first_name']);
            $user->setLastName($_REQUEST['last_name']);
            
            // IMPORTANT: validate input to eliminate XSS attack vectors!
            $user->setGender($_REQUEST['gender']);
            $user->setFlirtStatus($_REQUEST['flirt_status']);
            
            // remove trailing whitespace to handle redundant
            // new-line-feeds at the end of the string correctly
            // in line counting
            $signature = rtrim($_REQUEST['signature']);
            // count new lines in signature
            $newLineCount = 0;
            str_replace("\n", "\n", $signature, $newLineCount);
            // don't ignore mac users ...
            if (0 == $newLineCount) {
                str_replace("\r", "\r", $signature, $newLineCount);
            }
            if (strlen($signature) > V_SIGNATURE_MAX_CHARS or $newLineCount >= V_SIGNATURE_MAX_LINES) {
                self::addIncompleteField($this->errors, 'signature', ERR_ENTRY_TOO_LONG);
            }
            $user->setSignature($signature);
            
            if (strlen($_REQUEST['description']) > V_ENTRY_MAX_CHARS) {
                self::addIncompleteField($this->errors, 'description', ERR_ENTRY_TOO_LONG);
            }
            $user->setDescription($_REQUEST['description']);
            
            $user->setHomepage($_REQUEST['homepage']);
            
            $user->setNationalityId($_REQUEST['nationality_id']);
            //$user->setCountryId($_POST['country_id']);
            
            // additional checks for password issues
            if (!empty($_REQUEST['password_new']) and
                !empty($_REQUEST['password_check'])) {
            	
                // the following conditions are not formulated
                // as explicit conditions to determine 
                // point of failure, if it exists
                if (    empty($_REQUEST['password_old']) or
                        UserProtectedModel::encryptPassword($_REQUEST['password_old']) != $user->getPassword()) {
                    self::addIncompleteField($this->errors, 'password_old');
                } else if ($_REQUEST['password_new'] != $_REQUEST['password_check'] or
                           !InputValidator::isValidPassword($_REQUEST['password_new']) or
                           UserProtectedModel::encryptPassword($_REQUEST['password_old']) != $user->getPassword()) {
                    if (!InputValidator::isValidPassword($_REQUEST['password_new'])) {
                    	$this->errors['invalidPassword'] = ERR_USER_INVALID_PASSWORD; 
                    }
                    self::addIncompleteField($this->errors, 'password_new');
                    self::addIncompleteField($this->errors, 'password_check');
                } else {
                    // all conditions for password change have been met
                    $user->setPassword($_REQUEST['password_new']);
                    $user->setOriginalPassword($_REQUEST['password_new']);
                    $setNewPassword = true;
                }
            }

            $studyPaths = StudyPathModel::getAllStudyPaths($user->getUniId());
            $studyPathsId = array();
            $s = 0;
            while (array_key_exists('study_path' . $s, $_REQUEST) and $_REQUEST['study_path' . $s]) {
                array_push($studyPathsId, $_REQUEST['study_path' . $s]);
            	++$s;
            }
            // make the ids unique
            $studyPathsId = array_unique($studyPathsId);
            if ($s == 0 && $adminMode !== true) {
                self::addIncompleteField($this->errors, 'study_path0');
            }
            
            // if study paths have changed, update value
            if ($user->getStudyPathsId() != $studyPathsId) {
                $user->setStudyPathsId($studyPathsId);
            }
            
            // if no errors have occured and "save"-button has been clicked,
            // save changes
            if (count($this->errors) == 0) {
            	$upload = AttachmentHandler::SUCCESS;
                // is picture to be deleted?
                if (array_key_exists('user_picture_delete', $_REQUEST)) {
                    AttachmentHandler::removeUserPicture($user);
                }
                // is a new picture uploaded?
                else if ($_FILES['user_picture']['size']) {
                    $upload = AttachmentHandler::handleUserPicture($user, $_FILES['user_picture'],
                                AttachmentHandler::getAdjointPath($user), true, 102400,
                                array('big_maxwidth'=>250,'big_maxheight'=>500,
                                    'small_maxwidth'=>140,'small_maxheight'=>170,
                                    'tiny_maxwidth'=>50,'tiny_maxheight'=>60));
                }
                
                if (AttachmentHandler::SUCCESS == $upload) {
                    $user->save();
                    
                    // notify about changes
                    self::notifyIPC(new UserIPC($user->id), 'PROFILE_CHANGED');
                
                    // if not editing own profile,
                    // delete model stored in session
                    if (!$user->equals(Session::getInstance()->getVisitor())) {
                        Session::getInstance()->deleteUserData('userToEdit');
                        // and load fresh model from DB
                        // using the model from the SESSION seems not to work in PHP
                        //$user = UserProtectedModel::getUserByUsername($username);
                    } else {
                    	//var_dump($user);
                        $user->copyLoginStateFromUser(Session::getInstance()->getVisitor());
                        Session::getInstance()->setVisitor($user);
                    }
                } else if (AttachmentHandler::ERROR_SIZE == $upload) {
                    self::addIncompleteField($this->errors, 'user_picture', ERR_ATTACHMENT);
                } else if (AttachmentHandler::ERROR_MIME == $upload) {
                    self::addIncompleteField($this->errors, 'user_picture', ERR_PICTURE_MIMETYPE);
                }
            }
            
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'user_change.tpl');
        // CACHEME: how could be cache this page??
        $main->setCacheParameter(900, 'user_change');

        $main->assign('user', $user);
        $main->assign('show_all_options', $showAllOptions);
        $main->assign('study_paths', StudyPathModel::getAllStudyPaths($user->getUniId()));
        $main->assign('countries', CountryModel::getAllCountries());
        $main->assign('details_visible', DetailsVisibleModel::getAllDetailsVisible('user'));
        $main->assign('add_mode', $addMode);
        $main->assign('admin_mode', $adminMode);
        $main->assign('setNewPassword',$setNewPassword);
        if ($showAllOptions) {
            $main->assign('person_types', PersonTypeModel::getAllPersonTypes());
            $main->assign('universities', UniversityModel::getAllUniversities());
        }
        $this->setCentralView($main, false);
        
        if (!$display) {
            return $main;
        }
        
        $this->view();
    }
    
    protected function contactData() {
        $cUser = Session::getInstance()->getVisitor();
        
        $parameters = $this->getParameters('contactData');
        if ($parameters['user'] == null and !$parameters['adminMode']) {
            $this->rightsMissingView(self::getSpecificRight('contactData'));
        } else if ($parameters['user'] == null) {
            $this->errorView('Dieser User existiert nicht.');
        }
        
        if ($parameters['adminMode']) {
            $this->addLog('view and/or change of user contact data');
        }
        $adminMode = $parameters['adminMode'];
        $user = $parameters['user'];
        
        // set privacy context
        // grant access to all details which are classified up to no one
        PrivacyContext::getContext()->setLevelByName('no one');
        
        // check, if changes of profile are POSTed
        if (array_key_exists('changeprofile_form', $_POST)) {
            // form fields and their requirements
            $formFields = array(
                'telephone_mobil'  => array('required' => false, 'check' => 'isValidPhone'),
                'im_icq'           => array('required' => false, 'check' => 'isValidInteger'),
                'im_yahoo'         => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'im_msn'           => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'skype'            => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'im_aim'           => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'im_jabber'        => array('required' => false, 'check' => 'isValidMail'), // mail should be the right pattern for jabber
                'public_email'     => array('required' => false, 'check' => 'isValidMail'),
                'private_email'    => array('required' => false, 'check' => 'isValidMail'),
                'pgp_key'          => array('required' => false, 'check' => 'isValidPGPKey'),
                'zip_code'         => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'location'         => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'street'           => array('required' => false, 'check' => 'isValidAlmostAlways'),
                               );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);
            
            $user->setZipCode($_POST['zip_code']);
            $user->setLocation($_POST['location']);
            $user->setStreet($_POST['street']);
            
            $user->setTelephoneMobil($_POST['telephone_mobil']);
            $user->setImICQ($_POST['im_icq']);
            $user->setImYahoo($_POST['im_yahoo']);
            $user->setImMSN($_POST['im_msn']);
            $user->setImAIM($_POST['im_aim']);
            $user->setImJabber($_POST['im_jabber']);
            $user->setSkype($_POST['skype']);
            
            $user->setPublicEmail($_POST['public_email']);
            $user->setPublicPGPKey($_POST['pgp_key']);
            $user->setPrivateEmail($_POST['private_email']);
            
            // if no errors have occured and "save"-button has been clicked,
            // save changes
            if (count($this->errors) == 0) {
                $user->save();
                
                // notify about changes
                self::notifyIPC(new UserIPC($user->id), 'CONTACT_CHANGED');
                
                
                if (!$user->equals(Session::getInstance()->getVisitor())) {
                    // if not editing own profile,
                    // delete model stored in session
                    Session::getInstance()->deleteUserData('userToEdit');
                    // and load fresh model from DB
                    // using the model from the SESSION seems not to work in PHP
                    //$user = UserProtectedModel::getUserByUsername($username);
                } else {
                    //var_dump($user);
                    $user->copyLoginStateFromUser(Session::getInstance()->getVisitor());
                    Session::getInstance()->setVisitor($user);
                }
            }
            
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'contact_data.tpl');
        // CACHEME: how could be cache this page??
        $main->setCacheParameter(900, 'contact_data');
        
        $main->assign('user', $user);
        $main->assign('admin_mode', $adminMode);
        
        $this->setCentralView($main, false);
        $this->view();
    }
    
    protected function privacy() {
        $cUser = Session::getInstance()->getVisitor();
        
        $parameters = $this->getParameters('privacy');
        if ($parameters['user'] == null and !$parameters['adminMode']) {
            $this->rightsMissingView(self::getSpecificRight('privacy'));
        } else if ($parameters['user'] == null) {
            $this->errorView('Dieser User existiert nicht.');
        }
        
        if ($parameters['adminMode']) {
            $this->addLog('view and/or change of user privacy settings');
        }
        $adminMode = $parameters['adminMode'];
        $user = $parameters['user'];

        // pre-fetch all models, because we need them for input validation
        $detailsVisible = DetailsVisibleModel::getAllDetailsVisible('user');

        // check, if changes of profile are POSTed
        if (array_key_exists('changeprofile_form', $_POST)) {
            if (array_key_exists($_POST['birthdate'], $detailsVisible) and 
                    ($detailsVisible[$_POST['birthdate']]->name == 'no one' or
                     $detailsVisible[$_POST['birthdate']]->name == 'all' or
                     $detailsVisible[$_POST['birthdate']]->name == 'on friendlist')) {
                $user->setPrivacyBirthdate($_POST['birthdate']);
            }
            $user->setPrivacyInstantMessanger($_POST['instant_messanger']);
            $user->setPrivacyTelephone($_POST['telephone']);
            $user->setPrivacyRealName($_POST['real_name']);
            $user->setPrivacyEmailAddress($_POST['mail_address']);
            $user->setPrivacyAddress($_POST['address']);
            $user->setGBpublic(array_key_exists('guestbook_public', $_POST));
            $user->setFriendListpublic(array_key_exists('friendlist_public', $_POST));
            $user->setDiarypublic(array_key_exists('diary_public', $_POST));
            
            // we want a cookie based solution (linap, 19.05.2007)
            //$user->setPersistentLogin(array_key_exists('persistent_login', $_POST));
            
            // if no errors have occured and "save"-button has been clicked,
            // save changes
            if (count($this->errors) == 0) {
                $user->save();
                
                // notify about changes
                self::notifyIPC(new UserIPC($user->id), 'PRIVACY_CHANGED');
                
                if (!$user->equals(Session::getInstance()->getVisitor())) {
                    // if not editing own profile,
                    // delete model stored in session
                    Session::getInstance()->deleteUserData('userToEdit');
                    // and load fresh model from DB
                    // using the model from the SESSION seems not to work in PHP
                    //$user = UserProtectedModel::getUserByUsername($username);
                } else {
                    //var_dump($user);
                    $user->copyLoginStateFromUser(Session::getInstance()->getVisitor());
                    Session::getInstance()->setVisitor($user);
                }
            }
            
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'privacy.tpl');
       
        $main->assign('user', $user);
        $main->assign('admin_mode', $adminMode);
        $main->assign('details_visible', $detailsVisible);
        
        $this->setCentralView($main, false);
        $this->view();
    }
    
    protected function courses() {
        $cUser = Session::getInstance()->getVisitor();
        
        $parameters = $this->getParameters('courses');
        if ($parameters['user'] == null and !$parameters['adminMode']) {
            $this->rightsMissingView(self::getSpecificRight('courses'));
        } else if ($parameters['user'] == null) {
            $this->errorView('Dieser User existiert nicht.');
        }
        
        if ($parameters['adminMode']) {
            $this->addLog('view and/or change of a user courses');
        }
        $adminMode = $parameters['adminMode'];
        $user = $parameters['user'];

        $searchCourse = false;
        $coursesChanged = false;
        // check for action not concerning the whole profile
        // if found, we don't display the main form now
        // but wait for the specified sub-action to complete
        // case: search courses
        if (array_key_exists('searchcourses_form', $_POST) || !empty($_POST['coursename'])) {
            $searchCourse = true;
        } else if (array_key_exists('addcourses_form', $_POST)) {
            $this->addCourse($user);
            $coursesChanged = true;
        } else if (array_key_exists('delcourses_form', $_POST)) {
            $this->delCourse($user);
            $coursesChanged = true;
        } else if (array_key_exists('showcoursepage_form', $_POST)) {
            $a = null;
            if (array_key_exists('courses', $_POST) and is_array($_POST['courses'])) {
                $a = $_POST['courses'][0];
            } else if (array_key_exists('findcourses', $_POST) and is_array($_POST['findcourses'])) {
                $a = $_POST['findcourses'][0];
            }
            $this->showCoursePage($a);
        } else if (array_key_exists('no_basic_studies_form', $_POST)) {
            $user->setNoBasicStudies('t');
            $user->save();
        } else if (array_key_exists('basic_studies_form', $_POST)) {
            if ($user->hasNoBasicStudies()) {
                $user->setNoBasicStudies('f');
                $user->save();
            }
        }
        
        if (!BASIC_STUDIES_AVAILABLE || ($user->hasNoBasicStudies() && $user->getNoBasicStudies() == 't')) {
            $noBasicStudies = true;
            $suggestedCourses = null;
        } else {
            $noBasicStudies = false;
            $suggestedCourses = CourseModel::getCoursesByStudyPathAndUser($user->getStudyPathsId(), $user, 1, 4);
        }
        
        if (array_key_exists('changestudies_form', $_POST)) {
            foreach ($suggestedCourses as &$courseArray) {
                if ($courseArray[1] and !array_key_exists('c' . $courseArray[0]->id, $_POST)) {
                    $courseArray[0]->removeSubscribent($user);
                    $courseArray[1] = false;
                    $coursesChanged = true;
                } else if (array_key_exists('c' . $courseArray[0]->id, $_POST)) {
                    $courseArray[0]->addSubscribent($user);
                    $courseArray[1] = true;
                    $coursesChanged = true;
                }
            }
        }
        
        if ($coursesChanged) {
            if (!$user->equals(Session::getInstance()->getVisitor())) {
                // if not editing own profile,
                // delete model stored in session
                Session::getInstance()->deleteUserData('userToEdit');
                // and load fresh model from DB
                // using the model from the SESSION seems not to work in PHP
                //$user = UserProtectedModel::getUserByUsername($username);
            } else {
                $user->copyLoginStateFromUser(Session::getInstance()->getVisitor());
                Session::getInstance()->setVisitor($user);
            }
            
         
            $u = new UserIPC($user->id);
            $u->setTime('COURSES_CHANGED');
            $u->release();
        }
        

        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'courses.tpl');
        
        $main->assign('user', $user);
        $main->assign('admin_mode', $adminMode);
        $main->assign('no_basic_studies', $noBasicStudies);
        $main->assign('suggested_courses', $suggestedCourses);
        
        $courses = CourseModel::getCoursesByUser($user);
        $user->setCourses($courses);
        $main->assign('courses', $courses);
        
        
        if ($searchCourse and array_key_exists('coursename', $_REQUEST)) {
            $main->assign('new_courses', CourseModel::searchCourse($_REQUEST['coursename']));
        }
        
        $this->setCentralView($main, false);
        $this->view();
    }
    
    protected  function featureManagement(){
        $cUser = Session::getInstance()->getVisitor();
                
        $parameters = $this->getParameters('featureManagement');
        if ($parameters['user'] == null and !$parameters['adminMode']) {
            $this->rightsMissingView(self::getSpecificRight('featureManagement'));
        } else if ($parameters['user'] == null) {
            $this->errorView(ERR_NO_USER);
        }
        
        if ($parameters['adminMode']) {
            $this->addLog('view and/or change of user contact data');
        }
        $adminMode = $parameters['adminMode'];
        $user = $parameters['user'];
        
        $features = FeatureModel::getAllFeaturesWithUser($user);
        
        if (array_key_exists('save', $_REQUEST)) {
            $featuresToAdd = array();
            $featuresToDelete = array();
            foreach ($features as $id => $f) {
                if ($f->isEnabled()) {
                    $featuresToDelete[$id] = true;
                }
            }
            foreach ($_POST as $key => $val) {
                if (substr($key,0,4) == 'feat') {
                    $featureId = substr($key,4);
                    if (!array_key_exists($featureId,$features)) {
                        Logging::getInstance()->logSecurity('wrong feature id submitted');
                    } else if(!$features[$featureId]->isEnabled()) {
                        array_push($featuresToAdd, $featureId);
                        $features[$featureId]->setEnabled(true);
                        $features[$featureId]->setSaved(false);
                    } else {
                        unset($featuresToDelete[$featureId]);
                    }
                }
            }
            // allow feature removal for admins only
            if ($adminMode) {
                $featuresToDelete = array_keys($featuresToDelete);
            } else {
                $featuresToDelete = array();
                if (count($featuresToAdd) > $user->getConfigFeatureSlots()) {
                    array_push($this->errors, ERR_FEATURES_TOOMANY);
                }
            }
            
            // start transaction because we operate on two different models
            $DB = Database::getHandle();
            $DB->StartTrans();
            
            $changed = false;
            if (count($this->errors) == 0) {
                $changed = FeatureModel::changeFeatures($featuresToAdd, $featuresToDelete, $user);
                
                if (!$user->equals(Session::getInstance()->getVisitor())) {
                    // if not editing own profile,
                    // delete model stored in session
                    Session::getInstance()->deleteUserData('userToEdit');
                    // and load fresh model from DB
                    // using the model from the SESSION seems not to work in PHP
                    //$user = UserProtectedModel::getUserByUsername($username);
                } else {
                    //var_dump($user);
                    $user->copyLoginStateFromUser($cUser);
                    Session::getInstance()->setVisitor($user);
                }
            }
                        
            if ($changed) {
                // re-fetch features
                $features = FeatureModel::getAllFeaturesWithUser($user);
                // enforce rights reload for user just worked at
                $user->removeFromUserOnlineList();
                // correct number of available update slots
                $user->incrementFeatureSlots(-count($featuresToAdd));
                $user->save();
                // finish transaction
                $DB->CompleteTrans();

                header('Location: ' . rewrite_usermanagement(array('features' => $user, 'extern' => true)));
                return;
            }
            
            // finish transaction
            $DB->CompleteTrans();
        }
        
        $countEnableFeatures = 0;
        $countAvailableFeatures = 0;
        foreach($features as $feature){
            if($feature->isEnabled()){
                $countEnableFeatures++;
            }
            if($feature->isAvailable() && !$feature->isEnabled()){
            	$countAvailableFeatures++;
            }
        }

        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'feature_manage.tpl');

        $main->assign('user', $user);
        $main->assign('features', $features);
        $main->assign('countEnableFeatures',$countEnableFeatures);
        $main->assign('countFeatures', sizeof($features));
        $main->assign('countAvailableFeatures',$countAvailableFeatures);
        
        $main->assign('admin_mode', $adminMode);
        
        $this->setCentralView($main, false);
        $this->view();
        
    }
    
    protected function configBoxes() {
        $cUser = Session::getInstance()->getVisitor();
        $parameters = $this->getParameters('configBoxes');
        if ($parameters['user'] == null and !$parameters['adminMode']) {
            $this->rightsMissingView(self::getSpecificRight('configBoxes'));
        } else if ($parameters['user'] == null) {
            $this->errorView('Dieser User existiert nicht.');
        }
        
        if ($parameters['adminMode']) {
            $this->addLog('view and/or change of user contact data');
        }
        $adminMode = $parameters['adminMode'];
        $user = $parameters['user'];
        
        $availableBoxes = explode(',', self::getAvailableBoxes($user->isLoggedIn()));
        $countBoxes = array();
        
        if(!empty($_REQUEST['restoreBoxes'])){
            $user->setConfigBoxesLeft(BusinessLogicController::getDefaultBoxesLeft(true));
            $user->setConfigBoxesRight(BusinessLogicController::getDefaultBoxesRight(true));
            $user->save();
            
            if (!$user->equals(Session::getInstance()->getVisitor())) {
                // if not editing own profile,
                // delete model stored in session
                Session::getInstance()->deleteUserData('userToEdit');
                // and load fresh model from DB
                // using the model from the SESSION seems not to work in PHP
                //$user = UserProtectedModel::getUserByUsername($username);
            } else {
                $user->copyLoginStateFromUser(Session::getInstance()->getVisitor());
                Session::getInstance()->setVisitor($user);
            }
            
            header('Location: ' . rewrite_usermanagement(array('boxes' => $user, 'extern' => true)));
            return true;
        }
        
        
        /* search for all box with muli instance allowed */
        $multiInstance = BoxController::getAllMultiInstanceBoxNames();
        //var_dump($multiInstance);
        // existence of boxes_left array is
        // indicator for save action 
        if (array_key_exists('boxes_left', $_REQUEST)) {
    		$newBoxesLeft = 'user_login,';
            $addedBoxes = array();
                        
            // add selected boxes to the left
            // ensure that no box occurs twice
            // and that box name is valid
            foreach ($_REQUEST['boxes_left'] as $box) {
            	if(!array_key_exists($box, $countBoxes)){
            		$countBoxes[$box] = 1;
            	}
                else{
                	$countBoxes[$box] += 1;
                }
                if (in_array($box, $availableBoxes)) {
                    /* the first box allowed and more need to test if multi Instance are allowed */
                    if($countBoxes[$box] == 1 || ( $countBoxes[$box] > 1 && !empty($multiInstance[$box]))){
                        $newBoxesLeft .= $box . ':' . $countBoxes[$box] .',';
                        $addedBoxes[$box] = true;
                    }
                }
            }
            // remove trailing comma
            $newBoxesLeft = substr($newBoxesLeft, 0, -1);
            
            $newBoxesRight = '';
            
            // add selected boxes to the right
            // ensure that no box occurs twice
            // and that box name is valid
            foreach ($_REQUEST['boxes_right'] as $box) {
                if(!array_key_exists($box, $countBoxes)){
                    $countBoxes[$box] = 1;
                }
                else{
                    $countBoxes[$box] += 1;
                }
                
                if(in_array($box, $availableBoxes)) {
                    /* the first box allowed and more need to test if multi Instance are allowed */
                    if($countBoxes[$box] == 1 || ( $countBoxes[$box] > 1 && !empty($multiInstance[$box]))){
                        $newBoxesRight .= $box . ':' . $countBoxes[$box] .',';
                        $addedBoxes[$box] = true;
                    }
                }
            }
            // remove trailing comma
            if ($newBoxesRight != '') {
                $newBoxesRight = substr($newBoxesRight, 0, -1);
            }
            
            // save new configuration
            $user->setConfigBoxesLeft($newBoxesLeft);
            $user->setConfigBoxesRight($newBoxesRight);
            $user->save();
            
            if (!$user->equals(Session::getInstance()->getVisitor())) {
                // if not editing own profile,
                // delete model stored in session
                Session::getInstance()->deleteUserData('userToEdit');
                // and load fresh model from DB
                // using the model from the SESSION seems not to work in PHP
                //$user = UserProtectedModel::getUserByUsername($username);
            } else {
                $user->copyLoginStateFromUser(Session::getInstance()->getVisitor());
                Session::getInstance()->setVisitor($user);
            }
            
            header('Location: ' . rewrite_usermanagement(array('boxes' => $user, 'edit' => $adminMode, 'extern' => true)));
            return true;
        }
        
        
        if (array_key_exists('nojs', $_REQUEST) or $adminMode) {
        	$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'config_boxes_nojs.tpl');
        } else {
        	$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'config_boxes.tpl');
        }
        
        // prepare box configuration array for left and right side
        
        // ------------- left side
        
        if ($user->hasConfigBoxesLeft()) {
        	$boxesLeft = explode(',', $user->getConfigBoxesLeft());
        } else {
        	$boxesLeft = explode(',', self::getDefaultBoxesLeft($user->isLoggedIn()));
        }
        
        // remove "login-box" at first position
        // it will not be selectable
        array_shift($boxesLeft);
        
        // divite Name an instance number
        $editBoxesLeft = array();
        foreach($boxesLeft as $k => $box){
        	$editBoxesLeft[$k] = explode(':',$box);
            if(empty($editBoxesLeft[$k][1])){
            	$editBoxesLeft[$k][1] = 1;
            }
        }
        
        // fill with empty string up to 9 entries (possible boxes)
        // 9 = 10 -  login box
        for ($b=count($editBoxesLeft); $b<9; $b++) {
            $editBoxesLeft[] = array('',0);
        }
        
        // ------------- right side
        
        if ($user->hasConfigBoxesRight()) {
            $boxesRight = explode(',', $user->getConfigBoxesRight());
        } else {
            $boxesRight = explode(',', self::getDefaultBoxesRight($user->isLoggedIn()));
        }
        // divite Name an instance number
        $editBoxesRight = array();
        foreach($boxesRight as $k => $box){
            $editBoxesRight[$k] = explode(':',$box);
            if(empty($editBoxesRight[$k][1])){
                $editBoxesRight[$k][1] = 1;
            }
        }
                
        // fill with empty string up to 10 entries (possible boxes)
        for ($b=count($editBoxesRight); $b<10; $b++) {
            $editBoxesRight[] = array('',0);
        }

        // determine free boxes
        $freeBoxes = array();
        foreach ($availableBoxes as $a) {
            // FIXME: multi-instance stuff
            $found = false;
            $boxName = $a . ":1";
            if (in_array($boxName, $boxesLeft)) {
                $found = true;
            }
            if (!$found && in_array($boxName, $boxesRight)) {
                $found = true;
            }
            if (!$found) {
                $freeBoxes[] = $a;
            }
        }
        
        $main->assign('user', $user);
        $main->assign('admin_mode', $adminMode);
        $main->assign('user_boxes_left', $editBoxesLeft);
        $main->assign('user_boxes_right', $editBoxesRight);
        $main->assign('user_boxes_free', $freeBoxes);
        $main->assign('all_boxes', $availableBoxes);
        $this->setCentralView($main, false);
        
        $this->view();
        
    }
    
    /**
     * display friendlist management
     */
    protected function friendlist() {
        $cUser = Session::getInstance()->getVisitor();
        
        $parameters = $this->getParameters('friendlist');
        if ($parameters['user'] == null and !$parameters['adminMode']) {
            $this->rightsMissingView(self::getSpecificRight('friendlist'));
        } else if ($parameters['user'] == null) {
            $this->errorView('Dieser User existiert nicht.');
        }
        
        if ($parameters['adminMode']) {
            $this->addLog('view and/or change of user contact data');
        }
        $adminMode = $parameters['adminMode'];
        $user = $parameters['user'];
        
        $somethingHasChanged = false;
        
        if (array_key_exists('addFriend', $_REQUEST) and array_key_exists('friendId',$_REQUEST)) {
            $friendUserModel= UserProtectedModel::getUserById($_REQUEST['friendId']);
            
            if (!$user->equals($friendUserModel) && !FriendModel::hasOnIgnoreList($friendUserModel, $user)) {
                // instanciate new friend
                $friend = new FriendModel($user, $friendUserModel);
                // add friend to friendlist
                $friend->addToFriendlist();
                $somethingHasChanged = true;
            }
        } else if (array_key_exists('delFriend', $_REQUEST) and array_key_exists('friendId',$_REQUEST)) {
            $friendUserModel= UserProtectedModel::getUserById($_REQUEST['friendId']);
            
            if (!$user->equals($friendUserModel)) {
                // instanciate new friend
                $friend = new FriendModel($user, $friendUserModel);
                // remove friend to friendlist
                $friend->removeFromFriendlist();
                $somethingHasChanged = true;
            }
        } else if (array_key_exists('addFoe', $_REQUEST) and array_key_exists('foeId',$_REQUEST)) {
        	$foeUserModel= UserProtectedModel::getUserById($_REQUEST['foeId']);
        	if (!$user->equals($foeUserModel)) {
                // instanciate new friend
                $foe = new FoeModel($user, $foeUserModel);
                // add friend to friendlist
                $foe->addToIgnorelist();
                $somethingHasChanged = true;
            }
        	
        	
        } else if (array_key_exists('delFoe', $_REQUEST) and array_key_exists('foeId',$_REQUEST)) {
        	$foeUserModel= UserProtectedModel::getUserById($_REQUEST['foeId']);
        	if (!$user->equals($foeUserModel)) {
                // instanciate new friend
                $friend = new FoeModel($user, $foeUserModel);
                // remove friend to friendlist
                $friend->removeFromIgnorelist();
                $somethingHasChanged = true;
            }
        }

        // show non-JS variant, if user wants to or has no rights for friend categories
        if (array_key_exists('nojs', $_REQUEST) or $adminMode or !$user->hasRight('FRIENDLIST_EXTENDED_CATEGORIES')) {
    	    $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'friendlist_nojs.tpl');
        } else {
            $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'friendlist.tpl');
        }
        // CACHEME: how could be cache this page??
        $main->setCacheParameter(900, 'friendlist');
        
        $main->assign('user', $user);
        $friends = FriendModel::getFriendsByUser($user, '', 'dontcare', true, true);
    	$main->assign('user_friends', $friends);
        $main->assign('admin_mode', $adminMode);
        $main->assign('extended_categories', $user->hasRight('FRIENDLIST_EXTENDED_CATEGORIES') || $adminMode);
        
        $usernameSearch = Session::getInstance()->getUserData('searchFriend');
        if($usernameSearch and !array_key_exists('username_search', $_REQUEST)){
        	$_POST['searchFriend'] = true;
            $_REQUEST['username_search'] = $usernameSearch;
        }
        
        /* search for friendlist */
        if (array_key_exists('searchFriend', $_POST) and array_key_exists('username_search', $_REQUEST)) {
            $username = $_REQUEST['username_search'];
            
            $newFriends = UserProtectedModel::searchUser($username);
            $foes = FoeModel::getFoesByUser($user);
            // combine search results with information, whether
            // user already is on friendlist
            $newFriendsWithStatus = array();
            foreach ($newFriends as $nf) {
                $status = 0;
                if (array_key_exists($nf->id,$foes)) {
                    $status = 3;
                } else if (FriendModel::hasOnIgnorelist($nf, $user)) {
                    $status = 2;
                } else if (array_key_exists($nf->id,$friends)) {
                    $status = 1;
                }    
                array_push($newFriendsWithStatus, array($nf, $status));
            }
            $main->assign('newFriendList', $newFriendsWithStatus);
            $main->assign('newSearchFriend', $username);
            Session::getInstance()->storeUserData('searchFriend',$username);
        }
		
		/* search for Ignorelist */
		if (array_key_exists('searchFoe', $_POST) and array_key_exists('username_search_foe', $_REQUEST)) {
            $username = $_REQUEST['username_search_foe'];
            
            $newFriends = UserProtectedModel::searchUser($username);
            $foes = FoeModel::getFoesByUser($user);
            // combine search results with information, whether
            // user already is on friendlist
            $newFriendsWithStatus = array();
            foreach ($newFriends as $nf) {
                $status = 0;
                if (array_key_exists($nf->id,$foes)) {
                    $status = 3;
                } else if (FriendModel::hasOnIgnorelist($nf, $user)) {
                    $status = 2;
                }
                array_push($newFriendsWithStatus, array($nf, $status));
            }
            $main->assign('newFoeList', $newFriendsWithStatus);
            $main->assign('newSearchFoe', $username);            
        }
		
		
        if (array_key_exists('saveFriend', $_POST) and ($user->hasRight('FRIENDLIST_EXTENDED_CATEGORIES') or $adminMode)) {
            $DB = Database::getHandle();
            $DB->StartTrans();
            foreach ($_POST as $key => $friendType) {
                if (substr($key,0,5) == 'user_') {
                    $userId = substr($key, 5);
                    if (!array_key_exists($userId, $friends)) {
                        $DB->FailTrans();
                    }
                    
                    if (0 != ($typeId = FriendModel::isValidType($friendType))) {
                        $friends[$userId]->modifyTypeAtFriendlist($friendType);
                        $somethingHasChanged = true;
                    } else {
                        $DB->FailTrans();
                    }
                    
                    // need no save call; modifyTypeAtFriendlist causes instant 
                    // DB-commit 
                }
            }
            $DB->CompleteTrans();
        }
        
        if (!$user->equals(Session::getInstance()->getVisitor())) {
            // if not editing own profile,
            // delete model stored in session
            Session::getInstance()->deleteUserData('userToEdit');
            // and load fresh model from DB
            // using the model from the SESSION seems not to work in PHP
            //$user = UserProtectedModel::getUserByUsername($username);
        } else {
            //var_dump($user);
            $user->copyLoginStateFromUser(Session::getInstance()->getVisitor());
            Session::getInstance()->setVisitor($user);
        }
        
        if ($somethingHasChanged) {
            self::notifyIPC(new UserIPC($user->id), 'FRIENDLIST_CHANGED');
        }

        $this->setCentralView($main, false);
        $this->view();
    }
    
    protected function deleteAccount() {
        /* get the current user */
        $cUser = Session::getInstance()->getVisitor();
        
        if (!$cUser->isLoggedIn()) {
            $this->homeView();
        }
        
        $parameters = $this->getParameters('deleteAccount');
        
        if ($parameters['user'] == null and !$parameters['adminMode']) {
            $this->rightsMissingView(self::getSpecificRight('deleteAccount'));
        } else if ($parameters['user'] == null) {
            $this->errorView('Dieser User existiert nicht.');
        }
        $userToDelete = $parameters['user'];
        
                
        // did the user click on delete?
        if (array_key_exists('delete_submit', $_REQUEST)) {
            $uid = $userToDelete->id;
            
            $formFields = array(
                'reason'           => array('required' => false, 'check' => 'isValidAlmostAlways'),
                               );
            // some validity checks and HTML-escape
            $this->validateInput($formFields);
            
            $password = InputValidator::getRequestData('password');
            
            // then throw him into the recycle bin
            if(!$parameters['adminMode']){
                $user = UserModel::getUserByUsernamePassword($cUser->getUsername(), $password);
                
                if($user == null){
                	$this->errorView(ERR_INVALID_PASSWORD);
                    return;
                }
                
                // logout user first
                LoginHandler::triggerLogout();
                
            }else{
            	$user = $parameters['user'];
            }
            
                        
            
            if (array_key_exists('reason', $_REQUEST)) {
                $reason = $_REQUEST['reason'];
            } else {
                $reason = '';
            }
            $user->moveToRecycleBin($reason);
            
            // log deletion request for backup purposes
            Logging::getInstance()->logUserDelete(Logging::getErrorMessage(USER_REQUESTED_DELETION, time(), $uid));
            
            if(!$parameters['adminMode']){
                $this->homeView();
            }else{
                header('Location: '.rewrite_admin(array('purgeusers'=>true)));
                return;
            }
            
        } else if (array_key_exists('cancel_submit', $_REQUEST)) {
            if(!$parameters['adminMode']){
            	$this->homeView();
            }else{
            	header('Location: '.rewrite_usermanagement(array('profile'=>$userToDelete, 'edit'=>true)));
                return;
            }
        } else if (array_key_exists('permanent_delete_submit', $_REQUEST)) {            
        }

        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'delete_account.tpl');
        $main->setCacheParameter(7200, 'usermgmt');
        
        $main->assign('userToDelete', $userToDelete);
        $main->assign('admin_mode',$parameters['adminMode']);
        
        $this->setCentralView($main);
        $this->view();
    }
    
    protected function addCourse($user){
        if(!array_key_exists('findcourses',$_REQUEST)){
            $this->errorView(ERR_NO_COURSE);
            exit;
        }
        
        $courses = CourseModel::getCoursesByIds($_REQUEST['findcourses']);
        foreach ($courses as $c) {
        	$c->addSubscribent($user);
        }

        // we don't need a view()-call here, because we rely on the changeProfile-view 
    }
    
    protected function delCourse($user){
        if(!array_key_exists('courses',$_REQUEST)){
            $this->errorView(ERR_NO_COURSE);
            exit;
        }
        
        $courses = CourseModel::getCoursesByIds($_REQUEST['courses']);
        foreach ($courses as $c) {
            $c->removeSubscribent($user);
        }
        
        // we don't need a view()-call here, because we rely on the changeProfile-view 
    }
    
    protected function showCoursePage($courseId){
        if ($courseId == null) {
            $this->errorView(ERR_NO_COURSE);
            exit;
        }
        header('Location: ' . rewrite_course(array('courseId' => $courseId, 'extern' => true)));
    }
    
    
    protected function searchUser() {
        if (!array_key_exists('username_search', $_REQUEST)) {
            $this->errorView(ERR_SEARCH);
            return;
        }
        
        if(array_key_exists('search_in',$_REQUEST)){
        	$searchIn = $_REQUEST['search_in'];
        }else{
        	$searchIn = 'user';
        }
        
        $username = $_REQUEST['username_search'];

        if($searchIn == 'user'){
            // check, if user exists with given username
            if ($user = UserProtectedModel::getUserByUsername($username)) {
                header('Location: ' . rewrite_userinfo(array('user' => $user, 'extern' => true)));
            } else {
                // check, which usernames have to be considered
                $similarUsers = UserProtectedModel::searchUser($username);
                if (count($similarUsers)>1) {
                    // open advanced search page with search by given username
                    header('Location: ' . rewrite_usermanagement(array('usersearch'=>1, 'extern' => true)) . '?username=' . rawurlencode($username));
                } elseif (count($similarUsers)==1) {
                    // only one username is found, jump to him/her!
                    header('Location: ' . rewrite_userinfo(array('user' => $similarUsers[0], 'extern' => true)));
                } else {
                    // open advanced search page with "no result"-statement
                    header('Location: ' . rewrite_usermanagement(array('usersearch'=>1, 'extern' => true)) . '?noresult=' . rawurlencode($username));
                }
            }
        }else if($searchIn == 'forum'){
        	header('Location: ' . rewrite_forum(array('search' => true, 'extern' => true)) . '?'.F_SEARCH_SUBMIT.'=1&'.F_SEARCH_QUERY.'='.$username );
        }else if($searchIn == 'files'){
        	header('Location: ' . rewrite_course(array('extern' => true)) .'?'.F_SEARCH_SUBMIT.'=1&'.F_SEARCH_QUERY.'='.$username );
        }
    }
    
    protected function searchUserAdvanced() {
        /* get the current user */
        $cUser = Session::getInstance()->getVisitor();

        // do a rights check
        if (!$cUser->hasRight('USER_SEARCH_ADVANCED')) {
        	 $this->rightsMissingView('USER_SEARCH_ADVANCED');
        }
        
        $searchResults = null;
        if(!empty($_REQUEST['page'])){
        	$page = $_REQUEST['page'];	
        	$searchAdvanedOptions = Session::getInstance()->getUserData('searchAdvanedOptions');
        	//var_dump($searchAdvanedOptions);
        	if(sizeof($searchAdvanedOptions) >= 0 && array_key_exists('username',$searchAdvanedOptions)){
        		$_REQUEST['username'] = $searchAdvanedOptions['username'];
        		$_POST = $_POST + $searchAdvanedOptions;
        	}
        }else{
        	$page = 1;
        }
        //var_dump($_REQUEST);var_dump($_POST);
        $nextPage = false;
         /* rember search options */
        $searchValues = array();
        
        // array for warnings strings
        $warnings = array();
                
    	// did we get a search query?
        if (array_key_exists('username', $_REQUEST)) {
            $usernameString = $_REQUEST['username'];
            $studyPath = (isset($_POST['study_path'])) ? $_POST['study_path'] : '0';
            $gender = (isset($_POST['gender'])) ? $_POST['gender'] : '0';
            $flirtStatus = (isset($_POST['flirt_status'])) ? $_POST['flirt_status'] : '0';
            $picture = (isset($_POST['picture'])) ? $_POST['picture'] : '0';
            $uni = (isset($_POST['study_place'])) ? $_POST['study_place'] : '0';
            $order_by = (isset($_POST['order_by'])) ? $_POST['order_by'] : '0';
            $order = (isset($_POST['order'])) ? $_POST['order'] : 'ASC';
       		$limit = (isset($_POST['limit'])) ? $_POST['limit'] : 20;
       		if($limit > 64){ $limit = 64; }
       		
       		$display = (isset($_POST['display'])) ? $_POST['display'] : 'compact';
       		
       		if(!empty($_REQUEST['page'])){
       			$page = $_REQUEST['page'];
       			$offset = ($page-1) * $limit;
       		}
       		else{
       			$offset = 0;
       		}
       		
       		
            // we need at least three characters for username search
            // NOTE: when changing the integer constant change the following
            // string constant, too
            if (strlen($usernameString) < 3 and strlen($usernameString) > 0) {
                $usernameString = '';
                array_push($warnings, ERR_USERNAME_TOO_SHORT);
            }
            
            // nobody has been found yet
            $searchResults = UserProtectedModel::searchUserAdvanced($usernameString, $studyPath, $gender, $flirtStatus, $picture, $uni, $order_by, $order, ($limit+1), $offset);
                
            $searchValues['username'] = $usernameString;
            $searchValues['study_path'] = $studyPath;
            $searchValues['gender'] = $gender;
            $searchValues['flirt_status'] = $flirtStatus;
            $searchValues['picture'] = $picture;
            $searchValues['study_place'] = $uni;
            $searchValues['order_by'] = $order_by;
            $searchValues['order'] = $order;
            $searchValues['limit'] = $limit;
            $searchValues['display'] = $display;
            
            if(!empty($searchResults) && sizeof($searchResults) > $limit){
                Session::getInstance()->storeUserData('searchAdvanedOptions',$searchValues);
                $searchResults = array_splice($searchResults, 0, -1);
                $nextPage = true;
            }
    	}

        // check, if we have been forwarded from a search that didn't yield a result
        // e.g. from user search box
        if (array_key_exists('noresult', $_REQUEST)) {
            // no search results
            $searchResults = array();
            $searchValues['username'] = $_REQUEST['noresult'];
        }
                
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'search_user_advanced.tpl');
        // CACHEME (linap, 10.05.2007): search is naturally not to be cached
        $main->setCacheParameter(900, 'search_user_advanced');
        
        // TODO: connect study paths with university correctly here
        $main->assign('study_paths', StudyPathModel::getAllStudyPaths());
        $main->assign('unis',UniversityModel::getAllUniversities());
        $main->assign('search_results', $searchResults);
        $main->assign('searchValues',$searchValues);
        $main->assign('warnings', $warnings);

		$main->assign('page', $page);
		$main->assign('nextPage', $nextPage);

		if (!isset($display)){
            $display = 'compact';
        }
		$main->assign('display',$display);

        $this->setCentralView($main);
        $this->view();   
    }
    
    /**
     * call back for ajax functionaity
     */
    protected function ajaxSearchUser() {
    	$username = $_REQUEST['username'];
        $similarUsers = UserProtectedModel::searchUser($username);
        
        $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), USER_MANAGEMENT_TEMPLATE_DIR . 'ajax_search_user.tpl');
        $view->assign('similarUsers', $similarUsers);
        $view->display();
        exit;        
    }
    
    /**
     * checks if GET-parameter username is valid for a new user
     * @return JSON[int] (0|1|2) 0 = valid; 1 = username exists; 2 = invalid characters
     */
    protected function ajaxCheckUsername() {
    	$username = $_GET['username'];
        $validity = 0;
        
        if (!InputValidator::isValidUsername($username)) {
        	$validity = 2;
        } 
        if (UserProtectedModel::userAlreadyExists($username)) {
            $validity = 1;
        }
        
        echo json_encode($validity);
    }
    
    /**
     * get the ids of all Maildomains of a University 
     * @return JSON
     */
    protected function ajaxGetMaildomains() {
    	if (empty($_REQUEST['uni_id'])) {
            return;
        }
    	$uniId = $_REQUEST['uni_id'];
    	
        $regexp = EmailRegexpModel::getEmailRegexpByUniId($uniId);
        if ($regexp == null) {
        	echo "null";
            return;
        }
        
        $mailIds = array();
        foreach ($regexp as $r) {
        	$mailIds[] = $r->id;
        }
        echo json_encode($mailIds);
    }
    
    protected function ajaxFriendlist() {
        if (empty($_REQUEST['friends'])) {
            return;
        }
        
        $user = Session::getInstance()->getVisitor();
        
        // FIMXE: implement AJAX errornous feedback
        if (!$user->hasRight('FRIENDLIST_MODIFY') or
            !$user->hasRight('FRIENDLIST_EXTENDED_CATEGORIES')) {
            echo "1";
            return;
        }
        
        $friends = FriendModel::getFriendsByUser($user, '', 'dontcare', true, true);
        $friendsNew = json_decode($_REQUEST['friends']);
        $somethingHasChanged = false;
        
        $DB = Database::getHandle();
        $DB->StartTrans();
        foreach ($friendsNew as $friendType => $friendIds) {
            // friend deletion is a special case
            // handle adding and type modification first
            if ($friendType != 'Delete') {
                foreach ($friendIds as $userId) { 
                    // add friend, if he is not already in the list
                    if (!array_key_exists($userId, $friends)) {
                        $friendUserModel = UserProtectedModel::getUserById($userId);
            
                        if (!$user->equals($friendUserModel) && !FriendModel::hasOnIgnoreList($friendUserModel, $user)) {
                            // instanciate new friend
                            $friend = new FriendModel($user, $friendUserModel);
                    
                            // add friend to friendlist
                            $friend->addToFriendlist();
                            $friends[$userId] = $friend;
                            $somethingHasChanged = true;
                        }
                    }
                    
                    if (0 != ($typeId = FriendModel::isValidType($friendType))) {
                        $friendUserModel = UserProtectedModel::getUserById($userId);
                        if(!FriendModel::hasOnIgnoreList($friendUserModel, $user)){
	                        $friends[$userId]->modifyTypeAtFriendlist($friendType);
	                        $somethingHasChanged = true;
                        }
                    } else {
                        $DB->FailTrans();
                        die();
                    }
                }
            } else {
                foreach ($friendIds as $userId) {
                    $friendUserModel= UserProtectedModel::getUserById($userId);
                     // instanciate old friend
                    $friend = new FriendModel($user, $friendUserModel);
                    // remove friend to friendlist
                    $friend->removeFromFriendlist();
                    $somethingHasChanged = true;
                }
            }
        }
        
        if ($somethingHasChanged) {
            self::notifyIPC(new UserIPC($user->id), 'FRIENDLIST_CHANGED');
        }
        
        $DB->CompleteTrans();
        echo "0";
    }
    
    protected function ajaxConfigBoxes() {
        if (!array_key_exists('boxesLeft',$_REQUEST) or !array_key_exists('boxesRight',$_REQUEST)) {
            echo "1";
            return;
        }
        
        $user = Session::getInstance()->getVisitor();
        
        // FIMXE: implement AJAX errornous feedback
        if (!$user->hasRight('FEATURE_BOX_REARRANGEMENT')) {
            echo "1";
            return;
        }
        
        $boxesNewLeft = explode(',', $_REQUEST['boxesLeft']);
        $boxesNewRight = explode(',', $_REQUEST['boxesRight']);

        $DB = Database::getHandle();
        $DB->StartTrans();

        // FIXME: multiinstance stuff
        $availableBoxes = explode(',', self::getAvailableBoxes(true));
        $boxesLeft = 'user_login,';
        $boxesRight = '';
        foreach ($boxesNewLeft as $b) {
            if (in_array($b, $availableBoxes)) {
                $boxesLeft .= $b . ':1,';
            }
        }
        if (strlen($boxesLeft) > 0) {
            $boxesLeft = substr($boxesLeft, 0, -1);
        }
        
        foreach ($boxesNewRight as $b) {
            if (in_array($b, $availableBoxes)) {
                $boxesRight .= $b . ':1,';
            }
        }
        if (strlen($boxesRight) > 0) {
            $boxesRight = substr($boxesRight, 0, -1);
        }

        $user->setConfigBoxesLeft($boxesLeft);
        $user->setConfigBoxesRight($boxesRight);
        $user->save();
        
        $DB->CompleteTrans();

        echo "0";
    }

    protected function ajaxBoxMinimize() {
        $user = Session::getInstance()->getVisitor();
        if (!$user->hasRight('FEATURE_BOX_REARRANGEMENT')) {
            return;
        }
        if (empty($_REQUEST['boxname'])) {
            return false;
        }
        
        $box = BoxControllerFactory::getBox(explode(':', $_REQUEST['boxname']));
        if ($box == null) {
            return false;
        }
        
        $box->minimize();
        return true;
    }
    
    protected function ajaxBoxMaximize() {
        $user = Session::getInstance()->getVisitor();
        if (!$user->hasRight('FEATURE_BOX_REARRANGEMENT')) {
            return;
        }
        if (empty($_REQUEST['boxname'])) {
            return false;
        }
        
        $box = BoxControllerFactory::getBox(explode(':', $_REQUEST['boxname']));
        if ($box == null) {
            return false;
        }
        
        $box->maximize();
        // get ajax ready view
        $box->getView(true)->display();
    }
    
    protected function ajaxBoxClose() {
        $user = Session::getInstance()->getVisitor();
        if (!$user->hasRight('FEATURE_BOX_REARRANGEMENT')) {
            return;
        }
        if (empty($_REQUEST['boxname'])) {
            return false;
        }
        
        $box = BoxControllerFactory::getBox(explode(':', $_REQUEST['boxname']));
        if ($box == null) {
            return false;
        }
        
        $box->close();
        return true;
    }

    protected function ajaxCourse(){
    	$user = Session::getInstance()->getVisitor();
        
        if(array_key_exists('coursename', $_REQUEST)) {
            $new_courses = CourseModel::searchCourse($_REQUEST['coursename']);
            $courses = array();
            foreach($new_courses as $course){
            	$courses[] = $course;
            }
            print json_encode($courses);
            return true;
        }
        
    }
    
    /**
     * method to present legacy users out terms of use
     */
    protected function legacyTermsOfUse() {
        if (InputValidator::getRequestData('accept', false)) {
            $user = Session::getInstance()->getUserData('old_user');
            // mark user as logged in and online
            $user->login();
           
            // call save for "last login"
            $user->save();

            // update global session-stored user model of visitor            
            Session::getInstance()->setVisitor($user);
            
            Session::getInstance()->deleteUserData('old_user');
            
            header('Location: ' . rewrite_index(array('extern' => true)));
            return;
        } else if (InputValidator::getRequestData('decline', false)) {
            include_once BASE . '/contrib/legacy/legacy_termsofuse_delete.html';
            exit;
        } else if (InputValidator::getRequestData('delete', false)) {
            $user = Session::getInstance()->getUserData('old_user');
            $uid = $user->id;
            $user->moveToRecycleBin('declined new terms of use after login');
            
            // log deletion request for backup purposes
            Logging::getInstance()->logUserDelete(Logging::getErrorMessage(USER_REQUESTED_DELETION, time(), $uid));
            
            Session::getInstance()->deleteUserData('old_user');
            
            header('Location: ' . rewrite_index(array('extern' => true)));
        } else {
            include_once BASE . '/contrib/legacy/legacy_termsofuse.html';
            exit;
        }
    }

}

?>
