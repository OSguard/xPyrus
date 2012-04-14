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

// $Id: login_handler.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/login_handler.php $

require_once MODEL_DIR . '/base/user_anonymous_model.php';
require_once MODEL_DIR . '/base/user_guest_model.php';
require_once CORE_DIR . '/utils/cookie_manager.php';
require_once CORE_DIR . '/utils/client_infos.php';

/**
 * abstraction of our login cookie
 */
class LoginCookie {
    const USERID = 0;
    const USERNAME = 1;
    const USER_PASSWORD = 2;
    const EXPIRE_LOCAL = 3;
    const EXPIRE_REMOTE = 4;
    
    public static function password($password) {
        return sha1($password);
    }
}

/**
 * this class provides methods for handling user login/logout
 * @package Utils
 */
class LoginHandler {
	const SUCCESS = 0;
	const FAILED_USERNAME_PASSWORD = 1;
    const FAILED_MAX_LOGIN_ERROR = 2;
    const FAILED_TIMEOUT = 3;
    const FAILED_BLOCKED = 4;
    
    const FAILED_TERMSOFUSE = 100;
    
    private function __construct() {
    }
    
    /**
     * Trigger a user login. Creates user session data, if attempt is successful.
     *
     * @param string $username
     * @param string $password <b>unencrypted</b> password
     *
     * @return int see class constants
     */
    public static function triggerLogin($username, $password) {
        $user = UserProtectedModel::getUserByUsernamePassword($username, $password);
        
        if (UserModel::isLoginBlacklisted($username, ClientInfos::getClientIP())) {
            return self::FAILED_MAX_LOGIN_ERROR;
        }
        
        // if user login attempt was successful
        if ($user != null and $user->hasRight('LOGIN')) {
            
            if (defined('LEGACY_RELEASE_DATE') and $user->getLastLogin() < LEGACY_RELEASE_DATE) {
                Session::getInstance()->storeUserData('old_user', $user);
                return self::FAILED_TERMSOFUSE;
            }
            
            if ($user->getPersonType()->getName() == 'Gast-Zugang') {
                $user = new UserGuestModel($user);
            }
            
            // mark user as logged in and online
            $user->login();
            
            // if original password differs from password, we make
            // them equal again
            if ($user->getPassword() != $user->getOriginalPassword()) {
                $user->setPassword($password);
                $user->setOriginalPassword($password);
            }
            
            // call save for "last login"
            $user->save();

            // update global session-stored user model of visitor            
            Session::getInstance()->setVisitor($user);
            
            self::registerCityCookie($user, InputValidator::getRequestData('persistentLogin', false));
            
            return self::SUCCESS;
        }
        // login was not successful
         else {
            // log error only, if login doesn't fail because of missing right
            if ($user == null) {
                UserModel::logLoginError($username, $password, ClientInfos::getClientIP());
                return self::FAILED_USERNAME_PASSWORD;
            } else {
                return self::FAILED_BLOCKED;
            }
        }
    }
    
    public static function triggerLoginRemote($city, $remoteData) {
        
        if (!defined('OPENSSL_USERNAME')) {
            define('OPENSSL_USERNAME', '___OPENSSL___');
        }
        
        if (UserModel::isLoginBlacklisted(OPENSSL_USERNAME, ClientInfos::getClientIP())) {
            return self::FAILED_MAX_LOGIN_ERROR;
        }
        
        $data = explode('##', $remoteData);

        // verify authenticity of user data
        $pkeyid = openssl_get_publickey($city->getPublicKey());
        if (!$pkeyid) {
            throw new CoreException(Logging::getErrorMessage(CORE_PRIVATE_KEY_FAILED));
        }
        $success = openssl_verify($data[0], base64_decode($data[1]), $pkeyid);
        if ($success == -1) {
            throw new CoreException(Logging::getErrorMessage(CORE_PRIVATE_KEY_FAILED));
        }        
        openssl_free_key($pkeyid);
        if (!$success) {
            UserModel::logLoginError(OPENSSL_USERNAME, OPENSSL_USERNAME, ClientInfos::getClientIP());
            return self::FAILED_USERNAME_PASSWORD;
        }
        
        // we may now assume that user data is valid
        $userData = explode('#', $data[0]);
        // userData is { $externalId, $username, $issuedAtTime, $validTime }
                
        // everything is fine
        // so try to fetch appropiate user model
        if ($city->equals(CityModel::getLocalCity())) {
            $user = UserProtectedModel::getUserByUsername($userData[1]);
            if (time() > $userData[LoginCookie::EXPIRE_LOCAL]) {
                return self::FAILED_TIMEOUT;
            }
            if ($user != null and (LoginCookie::password($user->getPassword()) != $userData[LoginCookie::USER_PASSWORD]
                                   or !$user->isActive())) {
                return self::FAILED_USERNAME_PASSWORD;
            }
        } else {
            // check, if cookie has expired
            if (time() > $userData[LoginCookie::EXPIRE_REMOTE]) {
                return self::FAILED_TIMEOUT;
            }
            
            $user = UserExternalModel::getUserByUsername($userData[LoginCookie::USERNAME], $city);
            if ($user == null) {
                $user = UserExternalModel::getNewUser($userData[LoginCookie::USERNAME], $city, $userData[LoginCookie::USERID]);
            }
        }
        
        if (!$user->hasRight('LOGIN')) {
            return self::FAILED_BLOCKED;
        }
        
        $user->login();
        // call save for "last login"
        $user->save();
        
        // update global session-stored user model of visitor            
        Session::getInstance()->setVisitor($user);
            
        return self::SUCCESS;
    }
    
    /**
     * Trigger a user login. No check of password is done.
     *
     * @param string $id
     *
     * @return boolean
     */
    public static function triggerForcedLogin($user) {
        if ($user!=null) {
            // mark user as logged in and online
            $user->login();

            // update global session-stored user model of visitor
            Session::getInstance()->setVisitor($user);

            self::registerCityCookie($user);
            
            return true;
        }
        
        // login was not successful
        return false;
    }
    
    /**
     * Trigger a user logout. Deletes user session data.
     */
    public static function triggerLogout() {
        // logout user
        $user = Session::getInstance()->getVisitor();
        self::unregisterCityCookie();
        
        // try to remove remaining cookies
        CookieManager::removeAllCookies(false);
        
        $user->logout();
        
        // delete complete session content
        // and begin with anonymous user again
        Session::restart();
    }
    
    protected static function registerCityCookie($user, $persistent = false) {
        $city = CityModel::getLocalCity();
        $expiresAtLocal = time();
        if ($persistent) {
            $expiresAtLocal +=  10*365* 60*60*24;
        }
        $expiresAtRemote = time() + 60*60*24;
        
        $dataString = array($user->id,
                            $user->getUsername(),
                            LoginCookie::password($user->getPassword()),
                            $expiresAtLocal,
                            $expiresAtRemote);
        $dataString = implode('#', $dataString);
        $signature = '';

        $pkeyid = openssl_get_privatekey($city->getPrivateKey(), PK_PASSPHRASE);
        if (!$pkeyid) {
        	throw new CoreException(Logging::getErrorMessage(CORE_PRIVATE_KEY_FAILED));
        }        
        $success = openssl_sign($dataString, $signature, $pkeyid);
        if (!$success) {
            throw new CoreException(Logging::getErrorMessage(CORE_PRIVATE_KEY_FAILED));
        }        
        openssl_free_key($pkeyid);
        

        if ($persistent) {
            // set (globally available) city cookie
            CookieManager::setCookiePermanently('user_city', $city->getName(), true);
            // set (globally available) cookie with signed user data
            CookieManager::setCookiePermanently('user_data', $dataString . '##' . base64_encode($signature), true);
        } else {
            // set (globally available) city cookie for session only
            CookieManager::setCookie('user_city', $city->getName(), 0, true);
            // set (globally available) cookie with signed user data for session only
            CookieManager::setCookie('user_data', $dataString . '##' . base64_encode($signature), 0, true);
        }
    }
    
    protected static function unregisterCityCookie() {
        CookieManager::removeCookie('user_city', true);
        CookieManager::removeCookie('user_data', true);
    }
}

?>
