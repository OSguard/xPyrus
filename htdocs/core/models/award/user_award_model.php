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

require_once MODEL_DIR . '/base/base_model.php';
require_once MODEL_DIR . '/base/user_model.php';
require_once MODEL_DIR . '/award/award_model.php';

/**
 * @author Matthias Fansa
 * @version $Id: user_award_model.php 5807 2008-04-12 21:23:22Z trehn $
 */
class UserAwardModel extends BaseModel {
    
	// ID of user
	protected $userId;
	// ID of award
    protected $awardId;
	
	// creation date of the award 
    protected $entryTime;
    protected $lastUpdateTime;
	// users place in ranking
	protected $rank;
  
	
    public function __construct() {
        parent::__construct();
        
        // set default values
        $this->userId = null;
        $this->awardId = null;
        $this->rank = 0;
    }
    
    public function getUserId() { return $this->userId; }
    public function getAwardId() { return $this->awardId; }
    public function getEntryTime() { return $this->entryTime; }
    public function getLastUpdateTime() { return $this->lastUpdateTime; }
    public function getRank() { return $this->rank; }
	public function getId() { return $this->id; }
    
    public function getAward(){
    	if($this->award == null){
    		return $this->award = AwardModel::getById($this->awardId);
    	}
        return $this->award;
    }
    
    public function setUserId($val) { $this->userId = $val; }
    public function setAwardId($val) { $this->awardId = $val; }
    public function setEntryTime($val) { $this->entryTime = $val; }
    public function setLastUpdateTime($val) { $this->lastUpdateTime = $val; }
    public function setRank($val) { $this->rank = $val; }

	/**
	* All userAward data stored in this instance
	* are written to the database.
	* @throws DBException when commiting data failed for DB reasons
	*/    
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();
        
        $keyValue['user_id'] = $DB->quote($this->userId);
        $keyValue['award_id'] = $DB->quote($this->awardId);
        $keyValue['entry_time'] = Database::getPostgresTimestamp($this->entryTime);
        $keyValue['last_update_time'] = Database::getPostgresTimestamp($this->lastUpdateTime);
        $keyValue['rank'] = $DB->quote($this->rank);
        
        $q = '';
        // in UPDATE case we need a WHERE clause
        if ($this->id != null) {
            $q = $this->buildSqlStatement('user_award', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('user_award', $keyValue);
        }
    
        if (!$DB->execute($q)) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'user_award','id');
        }
    }
    
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->userId = $row['user_id'];
        $this->awardId = $row['award_id'];
        $this->entryTime = $row['entry_time'];
        $this->lastUpdateTime = $row['last_update_time'];
        $this->rank = $row['rank'];
    }
    
    public static function getByIds($ids) {
        $DB = Database::getHandle();
            
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, user_id, award_id, EXTRACT(epoch FROM entry_time) AS entry_time, EXTRACT(epoch
                     FROM last_update_time) AS last_update_time, rank
                FROM ' . DB_SCHEMA . '.user_award
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new UserAwardModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }
        
        return $models;
    }
    /* public static function getAll() {
        $DB = Database::getHandle();


        $q = 'SELECT id, EXTRACT(epoch FROM entry_time) AS entry_time, EXTRACT(epoch FROM last_update_time)
                     AS last_update_time, user_id, award_id, rank
                FROM ' . DB_SCHEMA . '.user_award';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $models = array();
        foreach ($res as $k => $row) {
            $model = new UserAwardModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }

        return $models;
    } */
	
	/** gets userAward by award_id
	     * @param int $award_id
	     * @param boolean $showValidOnly if true, invisible, inactive and inactivated user are hidden
	     * @return UserAwardModel
	     */    
	public static function getByAwardId($award_id) {
        $DB = Database::getHandle();


        $q = 'SELECT id, EXTRACT(epoch FROM entry_time) AS entry_time, EXTRACT(epoch FROM last_update_time)
                     AS last_update_time, user_id, award_id, rank
                FROM ' . DB_SCHEMA . '.user_award
				WHERE award_id = ' . $DB->quote($award_id) . '
				ORDER BY rank';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $models = array();
        foreach ($res as $k => $row) {
            $model = new UserAwardModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }

        return $models;
    }
	
    public static function getAwardByUser($user) {
        $DB = Database::getHandle();

        if($user->id == null || $user->isExternal()){
        	return array();
        }
    
    
        $q = 'SELECT id, EXTRACT(epoch FROM entry_time) AS entry_time, EXTRACT(epoch FROM last_update_time)
                     AS last_update_time, user_id, award_id, rank
                FROM ' . DB_SCHEMA . '.user_award
                WHERE user_id = ' . $DB->quote($user->id) . '
                ORDER BY award_id';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $models = array();
        foreach ($res as $k => $row) {
            $model = new UserAwardModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }

        return $models;
    }
    
		/** gets usernamel by UserAwardModel 
		* @return username
		*/ 
	public function getUserName() {
		$user=UserModel::getUserById($this -> userId, true);
		return	$this -> UserName = $this -> UserName = $user->username;
	}
	
	public function getUsers() {
		if ($this -> Users == null) {
			$this -> Users = $this -> Users= UserModel::getUserById($this -> userId, true);
		}
		return $this -> Users;
	}
	
	/**
	* deletes a UserAward
	*/
	public function delUserAward() {
		$DB = Database::getHandle();
		
		$q = 'DELETE FROM ' . DB_SCHEMA . '.user_award
			WHERE id = ' . $DB->quote($this->id) . '';
		$res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
		return null;
	}
	
	/* public function getByUserId($user_id) {
	$DB = Database::getHandle();


        $q = 'SELECT id, EXTRACT(epoch FROM entry_time) AS entry_time, EXTRACT(epoch FROM last_update_time)
                     AS last_update_time, user_id, award_id, rank
                FROM ' . DB_SCHEMA . '.user_award
				WHERE user_id = ' . $DB->quote($user_id) . '';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $models = array();
        foreach ($res as $k => $row) {
            $model = new UserAwardModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }

        return $models;
    } */
	
	/**
	   * @param int $id id of userAward to fetch
	   * @return UserAwardModel or null
	   */   
    public static function getById($id) {
        $model = self::getByIds(array($id));
        if (count($model) > 0) {
            return $model[0];
        }
        return null;
    }
}
?>
