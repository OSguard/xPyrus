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

// $Id: smarty_instance.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/smarty_instance.php $
//
// db class
//

/**
 * @package Core
 */
class SmartyInstance {
    /**
     * @var Smarty
     * Smarty-handle
     */
    private static $smarty;

    /**
     * constructor for SmartyInstance class
     * constructor is protected due to Singleton pattern
     */
    protected function __construct () { }

    public static function getHandle() {
        if (!isset(self::$smarty)) {
        	// initialize variable for smarty object
            $_SMARTY = null;
            
            // following script will assign correct Smarty class
            // to $_SMARTY
            include_once LIB_DIR . "/lib-template/index.php";

            if ($_SMARTY === null) {
            	die ('error on instanciating Smarty class');
            }
            
            self::$smarty = $_SMARTY;
        }

        return self::$smarty;
    }
}

?>
