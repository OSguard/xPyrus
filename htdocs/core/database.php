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

# $Id: database.php 5743 2008-03-25 19:48:14Z ads $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/database.php $
#
# db class
#

require_once CORE_DIR . '/exceptions/db_exception.php';

/**
 * @package Core
 */
class Database {
    /**
     * @var ADOConnection
     * ADOdb-handle
     */
    private static $DB;

    /**
     * constructor for Database class
     * constructor is protected due to Singleton pattern
     */
    protected function __construct () { }

    public static function getHandle() {
        if (self::$DB == null) {
            // initialize variable for adodb object
            $_DB = null;

            // following script will assign correct AdoDB class
            // to $_DB
            include_once LIB_DIR . "/lib-sql/index.php";
            
            if ($_DB === null) {
                die ('error on instanciating Adodb class');
            }
            self::$DB = $_DB;
        }

        return self::$DB;
    }
    
    /**
     * converts a postgresql-boolean ('t','f') into a php-boolean (true,false)
     *
     * @param string $var variable to interpret
     * @return boolean true or false
     * @throws CoreException for invalid argument
     */
    public static function convertPostgresBoolean($var) {
        if ($var == false or $var === 'f') {
            return false;
        } elseif ($var == true or $var === 't') {
            return true;
        }
    
        // throw error about invalid argument
        throw new CoreException($this->getErrorMessage(GENERAL_ARGUMENT_INVALID, $var), E_ERROR);
    }
    
    /**
     * converts a boolean php-value into a string
     *
     * @param boolean $var variable to interpret
     * @return string "t" or "f"-string
     * @throws CoreException for invalid argument
     */
    public static function makeBooleanString($var) {
        if (!$var or $var === "false" or $var === "FALSE" or $var == false) {
            return "f";
        } else if ($var === "true" or $var === "TRUE" or $var == true) {
            return "t";
        }
    
        // throw error about invalid argument
        throw new CoreException( $this->getErrorMessage(GENERAL_ARGUMENT_INVALID, $var), E_ERROR);
    }


    /**
     * Return the last inserted id in a table for the current database connection
     * 
     */
    public static function getCurrentSequenceId ($DB, $table, $column, $schema = DB_SCHEMA) {
        $name = $schema . "." . $table . "_" . $column . "_seq";

        $q = "SELECT CURRVAL('" . $name . "') AS value";
 
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach ($res as $row) {
            return $row['value'];
        }
    }
    
    /**
     * format date/time for postgresql
     * 
     * @param int unix timestamp
     * @return string date/time useable for postgres
     */
    public static function getPostgresTimestamp($epoch) {
        return 'TIMESTAMPTZ \'epoch\' + ' . ((int) $epoch) . ' * INTERVAL \'1 second\'';
    }
    
    /**
     * transforms an array of values into a comma separated, DB-quoted string
     * e.g. for use in "WHERE IN ($idString)"
     * 
     * if property is given the array elements are treated as objects and the 
     * named property is tried to be extracted
     * 
     * @param array
     * @param string property of object to extract from each object in the array
     * @param boolean if true, the function will return '0' (string) at
     *                an empty array
     * @return string
     */
    public static function makeCommaSeparatedString($array, $property = null, $nonEmptyReturn = true) {
        $DB = Database::getHandle();
        
        if (count($array) == 0) {
            if ($nonEmptyReturn) {
                return '0';
            } else {
                return '';
            }
        }
        
        $_array = array();
        
        foreach ($array as $key => $el) {
            if ($property == null) {
                $_array[$key] = $DB->Quote($el);
            } else {
                $_array[$key] = $DB->Quote($el->$property);
            }
        }
        
        return implode(',', $_array);
    }
}

?>
