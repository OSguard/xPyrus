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
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/user/user_central_data_model.php $

require_once CORE_DIR.'/interfaces/data_container.php';
require_once MODEL_DIR.'/base/base_model.php';
require_once MODEL_DIR.'/user/user_data_item_model.php';

/**
 * @class UserCentralDataModel
 * @brief class that encapsulates user data, that is stored 
 *        in a single row per-user in a DB-table 
 * 
 * @author linap
 * @version $Id: user_central_data_model.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Models/User
 */
class UserCentralDataModel extends BaseModel implements DataContainer {
    protected $userId;
    protected $tableName;
    protected $data;
    protected $valuesToSave;
    
    public function __construct($userId, $tableName) {
        parent::__construct();
        
        $this->userId = $userId;
        $this->tableName = $tableName;
        $this->data = array(); 
        $this->valuesToSave = array();
        $this->valuesToIncrease = array();
    }
    
    public function setUserId($uid) {
        $this->userId = $uid;
    }
    
    protected function loadAllData() {
    	$DB = Database::getHandle();
        
        $q = 'SELECT *
                FROM ' . DB_SCHEMA . '.' . $this->tableName . '
               WHERE id = ' . $DB->Quote( $this->userId );
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        // store all user data into user_data array
        foreach ($res->fields as $key => $val) {
            $this->data[ $key ] = $val;
        }
    }
    
    public function hasChanges() {
        return count($this->valuesToSave) > 0 || count($this->valuesToIncrease) > 0;
    }
    
    /** 
     * Save the current model to the DB
     */
    public function save() {
        $DB = Database::getHandle();

        $q = null;
        if (!$this->userId) {
           throw new ArgumentNullException('userId');
        }
        
        // check, if we have values to save
        if (count($this->valuesToSave) > 0) {
            $keyValue = array();
            foreach ($this->valuesToSave as $val) {
            	$keyValue[$val] = $DB->Quote($this->data[$val]);
                
                // special treatment for timestamp values
                if ($val == 'last_login') {
                    $keyValue['last_login'] = Database::getPostgresTimestamp($this->data[$val]);
                }
            }
            
            $q = $this->buildSqlStatement($this->tableName, $keyValue, false, 'id=' . $DB->quote($this->userId));
            //echo $q;
            if (!$DB->execute($q)) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
            
            // empty the array after save operation
            $this->valuesToSave = array();
        }
        
        // check, if we have values to increase
        if (count($this->valuesToIncrease) > 0) {
            $updates = '';
            foreach ($this->valuesToIncrease as $val => $inc) {
            	$updates .= "$val = $val + $inc,";
            }
          	// remove trailing comma
           	$updates = substr($updates,0,-1);
            
            $q = 'UPDATE ' . DB_SCHEMA . '.' . $this->tableName . '
                     SET ' . $updates. '
                   WHERE id=' . $DB->quote($this->userId);
            
            //echo $q;
            if (!$DB->execute($q)) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
            
            // empty the array after save operation
            $this->valuesToIncrease = array();
        }
    }
        
    protected function __get($name) {
    	if ($name == 'id') {
    		return $this->userId;
    	}
        return $this->safeReturn3($name, $this->data, 'loadAllData');
    }
    
    public function getValue($name) {
        return $this->safeReturn3($name, $this->data, 'loadAllData');
    }
    
    /**
     * increases value of named property
     * @param string $name name of property to increase
     * @param int $delta
     */
    public function increaseValue($name, $delta) {
    	$this->valuesToIncrease[$name] = $delta;
        $this->data[$name] += $delta;
    }
    
    /**
     * set value of named property
     * @param string $name name of property to increase
     * @param string $value
     */
    public function setValue($name, $value, $markForSave = true) {
        //echo "setting $name to $value<br />";
        $this->data[$name] = $value;

        if ($markForSave) {
            $this->markForSave($name);
        }
        
        return true;
    }
    
    /**
     * marks named property as to be saved
     * @param string $name
     */
    protected function markForSave($name) {
    	if (!array_key_exists($name, $this->data)) {
    	   Logging::getInstance()->logWarning('saving a nonexistent value' . $name . ' in UserCentralDataModel');
        }
    	array_push($this->valuesToSave, $name);
    }
    
    /**
     * reloads every property
     * @note method signature is as is it to give possibilty
     *       of interface extraction (cf. UserDataModel)
     * @param string $name
     */
    public function reload($name = '*') {
        $DB = Database::getHandle();
        
        $q = 'SELECT ' . $name . '
                FROM ' . DB_SCHEMA . '.' . $this->tableName . '
               WHERE id = ' . $DB->Quote( $this->userId );
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // store all new user data into user_data array
        foreach ($res->fields as $key => $val) {
            $this->data[ $key ] = $val;
        }
    }
}

?>
