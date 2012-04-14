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


// $Id: base_model.php 5743 2008-03-25 19:48:14Z ads $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/base_model.php $

require_once CORE_DIR . '/database.php';

/**
 * basic class for a model
 */
abstract class BaseModel {
    /**
     * @var int
     * id of this model
     */
    public $id;
    
    // dummy string to signal not-existence of certain properties
    const PROPERTY_DOESNT_EXIST = '5(%7%"!32%3)§(=!)';
    
    function __construct() {
    }
    
    /**
     * @return boolean
     */
    public function isEmpty() {
        return $this->id == null;
    }
    
    /**
     * @note the function does <b>not</b> compare the class types, only ids
     * @return boolean
     */
    public function equals($obj) {
        return $this->id == $obj->id;
    }

    /**
     * Easy way to create simple insert or update statements, So you can save the headache
     * with creating statements with the .= operator
     * 
     * @param string $table    the table in that should a line inserted or updated
     * @param array  $keyValue assiociative array column name => quoted value
     * @param boolean $isInsert indicates that this should be a insert statement
     * @param string $whereClausel the where clausel for the update
     */
    public static function buildSqlStatement($table, $keyValue, $isInsert = true, $whereClausel = "", $dbSchema = DB_SCHEMA) {
    	// check, if we have something to do
    	if ($keyValue == null) return '';

        /* insert */
        if ($isInsert === true) {
            $columns = 'INSERT INTO ' . $dbSchema . '.' . $table . ' (';
            $values = ' VALUES (';

            foreach ($keyValue as $key => $value) {
                $columns .= $key . ', ';
                $values .= $value . ', ';
            }

            $q = substr($columns, 0, strlen($columns) - 2) . ') ' .
            substr($values, 0, strlen($values) - 2) . ') ';
        } else {
            /* update statement */
            $columns = 'UPDATE ' . $dbSchema . '.' . $table . ' SET ';

            foreach ($keyValue as $key => $value) {
                $columns .= $key . ' = ' . $value . ', ';
            }

            $q = substr($columns, 0, strlen($columns) - 2);

            if ($whereClausel != null)
                $q .= ' WHERE ' . $whereClausel;
        }

        return $q;
    }
    
    /**
     * utility for lazy loading
     * if $val is null, method $function of this object is executed
     * in the hope value will not be null afterwards ...
     * method triggers an error in the latter case
     * 
     * @param string $val property of this object to examine
     * @param string $function name of method to execute
     * 
     * @return mixed desired value of $this->$val
     */
    protected function safeReturn($val, $function) {        
        
        /* suppress NOTICEs when var isn't there */
        $oldErrorReporting = error_reporting(E_WARNING);
        
        // first try to load from the given function when not available
        if ($this->$val === null) {
            
            /* restore old error level */
            error_reporting($oldErrorReporting);    
            $this->$function();
        }
        
        /* restore old error level */
        error_reporting($oldErrorReporting);    
        
        // if can\'t be loaded, trigger error, 
        // === is ok here because we don't want warn on empty arrays
        if ($this->$val === null) {
        	throw new CoreException('Property ' . $val . ' couldn\'t be initialized');
        }
        return $this->$val;
    }
    
    /**
     * utility for lazy loading
     * if given array has no key $val, method $function of this object is executed
     * in the hope value will not be null afterwards ...
     * method triggers an error in the latter case
     * 
     * @param string $val key in array to examine
     * @param array $array array
     * @param string $function name of method to execute
     * 
     * @return mixed desired value of $this->$val
     */
    protected function safeReturn2($val, &$array, $function) {
        // first try to load from DB if not available
        if (!isset($array[$val])) {
            $this->$function();
        }
        // if not in DB, trigger error
        if (!isset($array[$val]) or !($array[$val] instanceof UserDataItemModel)) {
            throw new CoreException("value $val not found in DB");
        }
        return $array[$val]->value;
    }
    
    /**
     * works like safeReturn2, but doesn't trigger error
     * on access failure but returns boolean information
     * about value existence
     * @return boolean
     */
    protected function safeReturn2Check($val, &$array, $function) {
        // first try to load from DB if not available
        if (!isset($array[$val])) {
            $this->$function();
        }
        // if not in DB, return false
        if (!isset($array[$val]) or !($array[$val] instanceof UserDataItemModel)) {
            $array[$val] = self::PROPERTY_DOESNT_EXIST;
            return false;
        }
        return true;
    }
    
    protected function safeReturn3($val, &$array, $function) {
        // first try to load from DB if not available
        if (!isset($array[$val])) {
                $this->$function();
        }
        // if not in DB, trigger error
        if (!isset($array[$val])) {
            throw new CoreException("value $val not found in DB");
        }
        return $array[$val];
    }
    
    /**
     * takes an array of elements and encapsulates
     * each entry in a proxy class
     * @param array of models
     * @param Proxy
     */
    public static function applyProxy($array, $proxy) {
        $proxiedModels = array();
        foreach ($array as $m) {
            $proxiedModels[] = $proxy->proxy($m);
        }
        return $proxiedModels;
    }
}

?>
