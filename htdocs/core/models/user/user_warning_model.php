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

// $Id: user_warning_model.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/user/user_warning_model.php $

require_once MODEL_DIR . '/base/base_model.php';

/**
 * class representing a warning card shown to users
 *
 * @package Models
 * @author linap
 * @subpackage Base
 */
class UserWarningModel extends BaseModel {
    /** The type of a warning. */
    public $type;
    /** user the warning is assigned to */
    public $user;
       
    protected $insertAt;
    public $declaredUntil;
    
    public function getDeclaredUntil() { return $this->declaredUntil; }
    public function getType() { return $this->type; }
    
    protected $reason;
    
    public function __construct($dbRow = null) {
        parent::__construct();
        
        if($dbRow != null) {
            $this->buildFromRow($dbRow);
        }
    }
    
    // can't make the variables const or static due to smarty
    public $TYPE_YELLOW = 'y';
    public $TYPE_YELLOWRED = 'ry';
    public $TYPE_RED = 'r';
    public $TYPE_GREEN = 'g';

    /** 
     * Save the current model to the DB
     */
    public function save() {
        $DB = Database::getHandle();
        $keyValue = array('user_id' => $DB->Quote($this->user->id),
                          'warning_type' => $DB->Quote($this->type),
                          'declared_until' => "TIMESTAMPTZ 'epoch' + " . (int) $this->declaredUntil . " * '1 second'::interval");
        if ($this->reason !== null) {
        	$keyValue['reason'] = $DB->Quote($this->reason);
        }
        
        $q = null;

        /* insert or update? */        
        if ($this->id != null) {
            $q = $this->buildSqlStatement('user_warnings', $keyValue, false, 'id=' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('user_warnings', $keyValue, true, null);
        }
        //var_dump($q);
        if (!$DB->execute($q)) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }

    /**
     * Return an UserWarningModel of lastest warning, 
     * if a warning is currently declared.
     * 
     * @param UserModel $user
     * @return UserWarningModel or null
     */    
    public static function getLatestWarningByUser($user) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, warning_type, 
                     EXTRACT(epoch FROM declared_until) AS _declared_until
                FROM ' . DB_SCHEMA . '.user_warnings
               WHERE user_id = ' . $DB->Quote($user->id) . '
                 AND declared_until > NOW()
            ORDER BY warning_type
               LIMIT 1';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        if ($res->EOF) {
        	return null;
        }
        
        $warning = new UserWarningModel($res->fields);
        
        return $warning;
    }
    
    /**
     * Collect all warnings regarding one user
     * 
     * @return array array of UserWarningModel
     */
    public static function getAllWarningsByUser($user) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, warning_type, reason,
                     EXTRACT(epoch FROM insert_at) AS _insert_at,
                     EXTRACT(epoch FROM declared_until) AS _declared_until
                FROM ' . DB_SCHEMA . '.user_warnings
               WHERE user_id = ' . $DB->Quote($user->id) . '
            ORDER BY insert_at ASC';
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $warnings = array();
        
        foreach ($res as $row) {
            $warnings[$row['id']] = new UserWarningModel($row);
            $warnings[$row['id']]->user = $user;
        }
        
        return $warnings;
    }
    
    /**
     * Get latest warnings of all users
     * 
     * @return array array of UserWarningModel
     */
    public static function getAllWarningsByLatest($offset = 0, $limit = 100) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, warning_type, reason, user_id,
                     EXTRACT(epoch FROM insert_at) AS _insert_at,
                     EXTRACT(epoch FROM declared_until) AS _declared_until
                FROM ' . DB_SCHEMA . '.user_warnings
            ORDER BY insert_at DESC
               LIMIT ' . $DB->Quote($limit) . '
              OFFSET ' . $DB->Quote($offset);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $warnings = array();
        $users = array();
        foreach ($res as $row) {
            $warnings[$row['id']] = new UserWarningModel($row);
            $warnings[$row['id']]->user = $row['user_id'];
            $users[] = $row['user_id'];
        }
        $users = UserProtectedModel::getUsersByIds($users);
        foreach ($warnings as $k => $warn) {
            $warn->user = $users[$warn->user];
        }
        
        return $warnings;
    }
    
    /** 
     * Build a model by an assicotive array given for example from the db.
     * 
     * @param array $row assiciativ array with the values
     */
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->type = $row['warning_type'];
        
        if (array_key_exists('_insert_at', $row)) {
            $this->insertAt = $row['_insert_at'];
        }
        
        $this->declaredUntil = $row['_declared_until'];
        
        if (array_key_exists('reason', $row)) {
            $this->reason = $row['reason'];
        }
    }
    
    protected function loadComplete() {
    	$DB = Database::getHandle();
        
        $q = 'SELECT id, warning_type, reason,
                     EXTRACT(epoch FROM insert_at) AS _insert_at,
                     EXTRACT(epoch FROM declared_until) AS _declared_until
                FROM ' . DB_SCHEMA . '.user_warnings
               WHERE id = ' . $DB->Quote($this->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        if ($res->EOF) {
            return false;
        }
        
        $this->buildFromRow($res->fields);
        
        return true;
    }
    
    public function hasExpired() {
    	return $this->declaredUntil < time();
    }
    
    public function expire() {
        $this->declaredUntil = time() - 1;
    }
     
    public function getReason() {
    	return $this->safeReturn('reason', 'loadComplete');
    }
    
    public function getInsertAt() {
    	return $this->safeReturn('insertAt', 'loadComplete');
    }

    public function setReason($reason) {
    	$this->reason = $reason;
    }
}

?>
