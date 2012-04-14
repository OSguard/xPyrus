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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/boxes/user_login_box.php $

require_once CORE_DIR . '/boxes/box_controller.php';
require_once CORE_DIR . '/utils/login_handler.php';
require_once CORE_DIR . '/views/view_factory.php';
require_once MODEL_DIR . '/base/user_protected_model.php';

require_once MODEL_DIR . '/pm/pm_model.php';

/**
 * @class UserLoginBox
 * @brief representing the user login/logout box
 * 
 * @author linap
 * @version $Id: user_login_box.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Boxes
 */
class UserLoginBox extends BoxController {
    protected $cacheKey = 'boxes|user_login';
    
    const STATE_SHOW_LOGIN  = 0;
    const STATE_SHOW_LOGOUT = 1;
    private $currentState;

    public function __construct($instance) {
        parent::__construct('user_login',$instance);
        $this->determineState();
        
        // force logout if user has no right to be logged in
        $cUser = Session::getInstance()->getVisitor();
        if ($cUser->isLoggedIn() and !$cUser->hasRight('LOGIN')) {
            LoginHandler::triggerLogout();
            header('Location: /home');
        }
        
        if ($this->currentState == self::STATE_SHOW_LOGIN and
                   array_key_exists('user_city', $_COOKIE) and 
                   array_key_exists('user_data', $_COOKIE)) {
            $remoteCity = CityModel::getCityByName($_COOKIE['user_city']);
            // test, if city exists
            if ($remoteCity != null) {
                $success = $this->loginRemote($remoteCity, $_COOKIE['user_data']);
                if ($success != LoginHandler::SUCCESS) {
                    $this->currentState = self::STATE_SHOW_LOGIN;
                    Session::getInstance()->storeUserData('failedLogin', $success);
                }
            }
        }
    }
    
    protected function determineState() {
    	//addDebugOutput( "login box:" . ( Session::getInstance()->getVisitor()->isLoggedIn() ? 'logged in' : 'not logged in') );
        if (Session::getInstance()->getVisitor()->isLoggedIn()) {
            $this->currentState = self::STATE_SHOW_LOGOUT;
        } else {
            $this->currentState = self::STATE_SHOW_LOGIN;
        }
    }
    
    public function getView() {
    	// extend cacheKey by username (and hence implicitly by state) 
        $cacheKey = $this->cacheKey . '|' . Session::getInstance()->getVisitor()->getUsername();

        // login state could have been changed by activation process
        $this->determineState();

        switch ($this->currentState) {
            case self::STATE_SHOW_LOGOUT:
                $templateFile = 'boxes/user_logout.tpl'; break;
            case self::STATE_SHOW_LOGIN:
            default:
                $templateFile = 'boxes/user_login.tpl'; break;
        }

        $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),
                $templateFile);
        $view->setCacheParameter(900, $cacheKey);
      	$view->assign('boxes_user_login_failed_login', Session::getInstance()->getUserData('failedLogin'));

        if (!$view->isCached() && $this->currentState == self::STATE_SHOW_LOGOUT) {
            $view->assign('boxes_user_login_user', Session::getInstance()->getVisitor());
        }
        
        return $view;
    }
    
    /**
     * set methods that are allowed for calling by the controller
     */
    protected function getAllowedMethods() {
        return array( 'login', 'logout' );
    }
    
    public function doesNeedReload() {
        // we always want reload of box structure after this box has been called
        return true;
    }
    
    public function login() {
        // check, if we are in a valid state for login
        if ($this->currentState != self::STATE_SHOW_LOGIN) {
        	return;
        }
        
        // check if action is required
        if (empty($_REQUEST['loginname']) or empty($_REQUEST['password'])) {
        	return;
        }

        $success = LoginHandler::triggerLogin($_REQUEST['loginname'], $_REQUEST['password']);
        switch ($success) {
        case LoginHandler::SUCCESS:
            // update box state
        	$this->currentState = self::STATE_SHOW_LOGOUT;
            Session::getInstance()->deleteUserData('failedLogin');
            break;
        default:
            $this->currentState = self::STATE_SHOW_LOGIN;
            Session::getInstance()->storeUserData('failedLogin', $success);
        }
        
        if ($success == LoginHandler::FAILED_TERMSOFUSE) {
            include_once BASE . '/contrib/legacy/legacy_termsofuse.html';
            exit;
        }
    }
    
    public function logout() {
        // check, if we are in a valid state for logout
        if ($this->currentState != self::STATE_SHOW_LOGOUT) {
            return;
        }

        LoginHandler::triggerLogout();
        // update box state
        $this->currentState = self::STATE_SHOW_LOGIN;
        
        header('Location: ' . rewrite_index(array('extern' => true)));
    }
    
    public function loginCookie($cookieMagic) {
        return LoginHandler::triggerLoginCookie($cookieMagic);
    }
    
    public function loginRemote($city, $remoteData) {
        return LoginHandler::triggerLoginRemote($city, $remoteData);
    }
    
    public function setLoginCookie($user) {
        $cookieMagic = sha1(uniqid(time(),rand()));
        $success = LoginHandler::registerCookie($user->id, $cookieMagic);
    }

}

?>
