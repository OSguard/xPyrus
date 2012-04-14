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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/user/user_data_model.php $

require_once CORE_DIR.'/interfaces/data_container.php';
require_once MODEL_DIR.'/base/base_model.php';
require_once MODEL_DIR.'/user/user_data_item_model.php';

/**
 * @class UserDataModel
 * @brief class that encapsulates user data, that is stored in several, perhaps optional
 *        rows per-user in a DB-table 
 * 
 * @author linap
 * @version $Id: user_data_model.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Models/User
 */
class UserDataModel extends BaseModel implements DataContainer {
    protected $userId;
    protected $tableName;
    protected $data;
    protected $valuesToSave;
    
    /**
     * @var boolean
     * true iff all variables have been reloaded recently 
     */
    private $reloadedAll;
    
    public function __construct($userId, $tableName) {
        parent::__construct();
        
        $this->userId = $userId;
        $this->tableName = $tableName;
        $this->data = array(); 
        $this->valuesToSave = array();
    }
    
    public function setUserId($uid) {
        $this->userId = $uid;
    }
    
    protected function loadAllData() {
    	$DB = Database::getHandle();
        
        $q = 'SELECT ud.id AS id, udk.data_name AS data_name,
                     ud.data_value AS data_value,
                     udk.id AS data_id
                FROM ' . DB_SCHEMA . '.' . $this->tableName . ' ud, 
                     public.' . $this->tableName . '_keys udk
               WHERE ud.data_name_id = udk.id
                 AND ud.user_id = ' . $DB->Quote( $this->userId );
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        // store all user data into user_data array
        foreach ($res as $row) {
            $this->data[ $row['data_name'] ] = new UserDataItemModel($row['data_value'], $row['id']);
        }
    }
    
    public function hasChanges() {
        return count($this->valuesToSave) > 0;
    }
    
    /** 
     * Save the current model to the DB.
     * @note relies on already opened transaction 
     */
    public function save() {
        // check, if we have something to do
        if (count($this->valuesToSave) == 0) {
            return;
        }
        
        $DB = Database::getHandle();
        $DB->StartTrans();
        
        $q = null;
        if (!$this->userId) {
           throw new ArgumentNullException('userId');
        }
        
        foreach ($this->valuesToSave as $val) {
            if ($val->id != 0) {
            	if ($val->delta != 0) {
                	$q = 'UPDATE ' . DB_SCHEMA . '.' . $this->tableName. '
                             SET data_value = CAST(data_value AS INTEGER)+(' . $DB->Quote($val->delta) . ')
                           WHERE id = ' . $DB->Quote($val->id);
                } else {
                	$q = 'UPDATE ' . DB_SCHEMA . '.' . $this->tableName. '
                             SET data_value = ' . $DB->Quote($val->value) . '
                           WHERE id = ' . $DB->Quote($val->id);
                }
            } else {
                if ($val->name == '') {
                	throw new ArgumentNullException('val');
                }
            	$q = 'INSERT INTO ' . DB_SCHEMA . '.' . $this->tableName. '
                                (user_id, data_name_id, data_value)
                        VALUES (' . $DB->Quote($this->userId) . ',
                                (SELECT id::integer AS id FROM public.' . $this->tableName . '_keys WHERE data_name=' . $DB->Quote($val->name) . '),
                                ' . $DB->Quote($val->value) . ')';
            }
            if (!$DB->execute($q)) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
            if($val->id == 0){
            	$val->id = Database::getCurrentSequenceId($DB, $this->tableName ,'id');
            }
        }
        $DB->CompleteTrans();
        
        // reset all deltas after successful transaction
        // this has to be done, because they are references
        // to the $data array and the delta would be kept 
        // forever otherwise
        foreach ($this->valuesToSave as &$val) {
            if ($val->id != 0) {
                $val->delta = 0;
            }
        }
        // empty the array after save operation
        $this->valuesToSave = array();
    }

    public function getValue($name) {
        // OPTIMIZEME:
        // once loadAllData was executed, the else branch doesn't need to loadAllData
        // again; the code is written like this for generality
        // for performance improvements, one can change that part
        //addDebugOutput($name);
        if (substr($name,0,4) != 'has_') {
            // return value-to-save here first, if it exists
            // because PHP session handling seems buggy
            if (array_key_exists($name, $this->valuesToSave)) {
                return $this->valuesToSave[$name]->value;
            }
            return $this->safeReturn2($name, $this->data, 'loadAllData');
        } else {
            return $this->safeReturn2Check(substr($name,4), $this->data, 'loadAllData');
        }
    }
    
    public function __wakeup() {
    	// ignore all reloads of previous session acts
        $this->reloadedAll = false;
    }
    
    /**
     * increases value of named property
     * @param string $name name of property to increase
     * @param int $delta
     */
    public function increaseValue($name, $delta) {
    	// if delta is zero, nothing is to do
        if ($delta == 0) {
    		return;
    	}
        
        // ensure that value is loaded
    	$prop = $this->getValue($name);
        if ($prop === null) {
        	throw new CoreException('increasing a null value ' . $name);
        }

        $this->data[$name]->delta += $delta;
        $this->data[$name]->value += $delta;
        $this->markForSave($name);
    }
    
    /**
     * set value of named property
     * @param string $name name of property to increase
     * @param string $value
     */
    public function setValue($name, $value) {    
        // ensure that value is loaded and,
        // if value has not existed before, create a new one
        if (!$this->getValue('has_' . $name) and $value == '') {
        	return true;
        } else if (!$this->getValue('has_' . $name) and $value != '') {
            $this->data[$name] = new UserDataItemModel($value);
            $this->data[$name]->name = $name;
        } else {
        	//echo "setting <b>$name</b>";
        	$this->data[$name]->value = $value;
            $this->data[$name]->delta = 0;
        }
        $this->markForSave($name);
        
        return true;
    }
    
    /**
     * marks named property as to be saved
     * @param string $name
     */
    protected function markForSave($name) {
    	if (!array_key_exists($name, $this->data)) {
    	   Logging::getInstance()->logWarning('saving a nonexistent value' . $name . ' in UserDataModel');
        }
    	$this->valuesToSave[$name] = $this->data[$name];
    }
    
    /**
     * reloads (at least) property with given name
     * @param string $name
     */
    public function reload($name = null) {
    	// don't reload if data has recently been reloaded
        if ($this->reloadedAll) {
    		return;
    	}
        
        // clear all data cache
        $this->data = array();
        
        // mark recent reload process
        $this->reloadedAll = true;
    }
}

?>
