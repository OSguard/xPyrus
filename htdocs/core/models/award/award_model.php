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

/**
 *@author Matthias Fansa
 * @version $Id: award_model.php 5807 2008-04-12 21:23:22Z trehn $
 */
class AwardModel extends BaseModel {
	
	// creation date of the award 
    protected $entryTime;
    protected $lastUpdateTime;
	
	//name of the award
    protected $name;
	// link to a picture
    protected $icon;
	// link to event page
	protected $link;

    public function __construct() {
        parent::__construct();

        // set default values
        $this->name = '';
        $this->icon = null;
		$this->link = null;
    }

    public function getEntryTime() { return $this->entryTime; }
    public function getLastUpdateTime() { return $this->lastUpdateTime; }
    public function getName() { return $this->name; }
    public function getIcon() { return $this->icon; }
	public function getId() {return $this->id;}
	public function getLink() {return $this->link;}

    public function setEntryTime($val) { $this->entryTime = $val; }
    public function setLastUpdateTime($val) { $this->lastUpdateTime = $val; }
    public function setName($val) { $this->name = $val; }
    public function setIcon($val) { $this->icon = $val; }
	public function setLink($val) { $this->link = $val; }

	/**
	     * All award data stored in this instance
	     * are written to the database.
	     * @throws DBException when commiting data failed for DB reasons
	     */
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();

        $keyValue['entry_time'] = Database::getPostgresTimestamp($this->entryTime);
        $keyValue['last_update_time'] = Database::getPostgresTimestamp($this->lastUpdateTime);
        $keyValue['name'] = $DB->quote($this->name);
        if ($this->icon !== null) {
            $keyValue['icon'] = $DB->quote($this->icon);
        }
		if ($this->link !== null) {
            $keyValue['link'] = $DB->quote($this->link);
        }
		// need if we want to remove
		if ($this->link == null) {
            $keyValue['link'] = $DB->quote(null);
        }
		if ($this->icon == null) {
            $keyValue['icon'] = $DB->quote(null);
        }
		
        $q = '';
        // in UPDATE case we need a WHERE clause
        if ($this->id != null) {
            $q = $this->buildSqlStatement('award', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('award', $keyValue);
        }

        if (!$DB->execute($q)) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'award','id');
        }
    }

    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->entryTime = $row['entry_time'];
        $this->lastUpdateTime = $row['last_update_time'];
        $this->name = $row['name'];
        $this->icon = $row['icon'];
		$this->link = $row['link'];
    }

    public static function getByIds($ids) {
        $DB = Database::getHandle();

        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }

        $q = 'SELECT id, EXTRACT(epoch FROM entry_time) AS entry_time, EXTRACT(epoch FROM last_update_time)
                     AS last_update_time, name, icon, link
                FROM ' . DB_SCHEMA . '.award
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $models = array();
        foreach ($res as $k => $row) {
            $model = new AwardModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }

        return $models;
    }
	/**
	    * Get a array with all AwardModels   
	    * @throws DBException on DB error
	    * @return array with AwardModels 
	    */
    public static function getAll() {
        $DB = Database::getHandle();


        $q = 'SELECT id, EXTRACT(epoch FROM entry_time) AS entry_time, EXTRACT(epoch FROM last_update_time)
                     AS last_update_time, name, icon, link
                FROM ' . DB_SCHEMA . '.award';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $models = array();
        foreach ($res as $k => $row) {
            $model = new AwardModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }

        return $models;
    }
	
	/** gets UserAwardModel by AwardModel 
	     * @return UserAwardModel
	     */    
	public function getUserAward() {
		if ($this -> UserAward == null) {
			$this -> UserAward = $this -> UserAward= UserAwardModel::getByAwardId($this -> id);
		}
		return $this -> UserAward;
	}
	/**
	     * deletes a Award
	     */
	public function delAward() {
		$DB = Database::getHandle();
		
		$q = 'DELETE FROM ' . DB_SCHEMA . '.award
			WHERE id = ' . $DB->quote($this->id) . '';
		$res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
		return null;
	}
	/**
	   * @param int $id id of award to fetch
	   * @return AwardModel or null
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
