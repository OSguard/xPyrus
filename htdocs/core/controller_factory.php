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

// $Id: controller_factory.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/controller_factory.php $

class ControllerFactory {
    // private constructor
    private function __construct() {}
    
    public static function createControllerByName($name, $ajaxView = false) {
        require BASE . "/conf/enabled_modules.php";

        /* cut things like "http://hostname/?cssswitch=alternative */
        $tmp = strpos($name, "?");
        if($tmp !== FALSE) {
            $name = str_split($name, $tmp);
            $name = $name[0];
        }
        
        // search the file to include
        // $enabledModules comes from upper include
        if (!array_key_exists($name, $enabledModules)) {
            return null;
        }
        $controllerClass = $enabledModules[$name];

        if ($controllerClass == ""){
            return null;
        }
        
        //include required controller
        $fileName = preg_replace( '/(.*?[A-Za-z])([A-Z][a-z].*?)/m','\1_\2', $controllerClass);
        $fileName = strtolower($fileName);
        
        // TODO: here is a bug (with __autoload), but I don't know where it comes from
        //            (linap, 23.06.07)
        include_once CORE_DIR . '/businesslogic' . "/" . $name . "/" . $fileName . ".php";

        //instanciate it
        return new $controllerClass($ajaxView);
    }
    
    public static function createSupportHandlingController() {
        return self::createControllerByName(self::createSupportHandlingControllerName());
    }
    
    public static function createSupportHandlingControllerName() {
        return 'mantis';
    }
}

?>
