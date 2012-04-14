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

// $Id: cookie_manager.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/cookie_manager.php $
class CookieManager {
    public static function setCookie($name, $value, $expiresAt = 0, $rootDomainLevel = false) {
       $domain = null;
       if ($rootDomainLevel) {
            $domain = '.' . DOMAIN_BASE;
       }
       return setcookie($name, $value, $expiresAt, '/', $domain);
    }
    
    public static function setCookiePermanently($name, $value, $rootDomainLevel = false) {
       // ten years can be defined to be eternity here
       return self::setCookie($name, $value, time() + 10 * 365 * 86400, $rootDomainLevel);
    }
    
    public static function removeCookie($name, $rootDomainLevel = false) {
        $domain = null;
        if ($rootDomainLevel) {
            $domain = '.' . DOMAIN_BASE;
        }
        
        if (setcookie($name, null, time()-86400, '/', $domain)) {
            unset($_COOKIE[$name]);
            return true;
        }
        
        return false;
    }
    
    public static function removeAllCookies($rootDomainLevel = false) {
        $domain = null;
        if ($rootDomainLevel) {
            $domain = '.' . DOMAIN_BASE;
        }
        
        $success = true;
        foreach ($_COOKIE as $name => $val) {
            if (setcookie($name, null, time()-86400, '/', $domain)) {
                unset($_COOKIE[$name]);
                $success &= true;
            } else {
                $success &= false;
            }
        }
        
        return $success;
    }
 
}

?>
