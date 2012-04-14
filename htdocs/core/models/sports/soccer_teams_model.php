<?php

require_once MODEL_DIR . '/base/base_model.php';

/**
 * 
 * @version $Id$
 */
class SoccerTeamsModel extends BaseModel {
    protected $name;
    protected $nameShort;
    protected $groupName;
    protected $tournament;
    
    public function __construct() {
        parent::__construct();
        
        // set default values
        $this->name = null;
        $this->nameShort = null;
        $this->groupName = null;
        $this->tournament = null;
    }
    
    public function getName() { return $this->name; }
    public function getNameShort() { return $this->nameShort; }
    public function getGroupName() { return $this->groupName; }
    public function getTournament() { return $this->tournament instanceof SoccerTournamentsModel ? $this->tournament : ($this->tournament = SoccerTournamentsModel::getById($this->tournament)); }
    
    public function setName($val) { $this->name = $val; }
    public function setNameShort($val) { $this->nameShort = $val; }
    public function setGroupName($val) { $this->groupName = $val; }
    public function setTournament($val) { $this->tournament = $val; }
    
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();
        
        $keyValue['name'] = $DB->quote($this->name);
        $keyValue['name_short'] = $DB->quote($this->nameShort);
        $keyValue['group_name'] = $DB->quote($this->groupName);
        $keyValue['tournament_id'] = $this->tournament instanceof SoccerTournamentsModel ? $this->tournament->id : $this->tournament;
        
        $q = '';
        // in UPDATE case we need a WHERE clause
        if ($this->id != null) {
            $q = $this->buildSqlStatement('soccer_teams', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('soccer_teams', $keyValue);
        }
    
        if (!$DB->execute($q)) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'soccer_teams','id');
        }
    }
    
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->nameShort = $row['name_short'];
        $this->groupName = $row['group_name'];
        $this->tournament = $row['tournament_id'];
    }
    
    public static function getByIds($ids) {
        $DB = Database::getHandle();
            
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, name, name_short, group_name, tournament_id
                FROM ' . DB_SCHEMA . '.soccer_teams
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerTeamsModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }
        
        return $models;
    }
    
    public static function getByTournament($tournament) {
        $DB = Database::getHandle();
            
        $q = 'SELECT id, name, name_short, group_name, tournament_id
                FROM ' . DB_SCHEMA . '.soccer_teams
               WHERE tournament_id = ' . $DB->Quote($tournament->id) . '
            ORDER BY name';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerTeamsModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }
        
        return $models;
    }
    
    public static function getById($id) {
        $model = self::getByIds(array($id));
        if (count($model) > 0) {
            return $model[0];
        }
        return null;
    }
}
?>
