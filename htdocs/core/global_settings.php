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

require_once CORE_DIR . '/database.php';

/**
 * This class provides access to global configuration settings
 * @package Core
 */
class GlobalSettings {
    private static $settings = null;
    
    private static function initialize() {
        require_once GLOBAL_SETTINGS_INCLUDE_FILE;
        // $globalSettings comes from the included file
        self::$settings = $globalSettings;
    }
    
    /**
     * reads a global configuration setting
     *
     * @param string $configName name/identifier of configuration setting
     * @return string setting value
     * @throws DBException on DB error
     */
    public static function getGlobalSetting($configName) {
        // on first access initialize settings-cache array
        if (self::$settings == null) {
            self::initialize();
        }
        // moved from pure database based version
        // to file cached version of config settings
        //      (linap, 21.05.2007)
        /*
        // check, if config setting is already fetched
        if (isset(self::$settings[$configName])) {
            return self::$settings[$configName];
        }
        
        $DB = Database::getHandle();
        $q = 'SELECT config_value 
                FROM '.DB_SCHEMA.'.global_config 
            WHERE config_name='.$DB->Quote($configName);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // save fetched value in settings array
        self::$settings[$configName] = $res->fields['config_value'];
        */
        
        return self::$settings[$configName];
    }
}

?>
