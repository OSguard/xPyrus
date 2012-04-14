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

// defines
define('BROWSER','browsername');
define('USERIP','userip');
define('USEROS','useros');

/**
 * provides a function which provides back a hash
 * with a couple of technical user-data-information
 * just like IP, Browser, ...
 * @package Utils
 */
class ClientInfos {
    /**
     * associative array containing the main user
     * infos, like which browser is used and so on
     */
    private static $clientInfos = null;
    
    private static function initialize() {
        self::$clientInfos = array();
    
        // fetch data
        $httpUserAgent = &$_SERVER['HTTP_USER_AGENT'];
    
        if (stripos($httpUserAgent,'opera')!==false) self::$clientInfos[BROWSER] = 'OPERA';
        elseif (stripos($httpUserAgent,'mozilla')!==false) self::$clientInfos[BROWSER] = 'MOZILLA';
        elseif (stripos($httpUserAgent,'msie')!==false) self::$clientInfos[BROWSER] = 'EXPLORER';
        else self::$clientInfos[BROWSER] = 'UNKNOWN';
    
        if (stripos($httpUserAgent,'linux')!==false) self::$clientInfos[USEROS] = 'LINUX';
        elseif (stripos($httpUserAgent,'windows')!==false) self::$clientInfos[USEROS] = 'WINDOWS';
        else self::$clientInfos[USEROS] = 'UNKNOWN';

        if (defined('BEHIND_PROXY')) {
            self::$clientInfos[USERIP] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {    
            self::$clientInfos[USERIP] = $_SERVER['REMOTE_ADDR'];
        }    
    }	
    
    /**
    * @return string client's ip-address
    */
    public static function getClientIP() {
        if (!self::$clientInfos) self::initialize();
        
        return self::$clientInfos[USERIP];
    }
}
