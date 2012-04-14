<?php

require_once MODEL_DIR . '/base/base_model.php';

/**
 * 
 * @version $Id$
 */
class SoccerStadiumsModel extends BaseModel {
    protected $city;
    protected $stadiumName;
    protected $tournament;
    
    public function __construct() {
        parent::__construct();
        
        // set default values
        $this->city = null;
        $this->stadiumName = '';
        $this->tournament = null;
    }
    
    public function getCity() { return $this->city; }
    public function getStadiumName() { return $this->stadiumName; }
    public function getTournament() { return $this->tournament instanceof SoccerTournamentsModel ? $this->tournament : ($this->tournament = SoccerTournamentsModel::getById($this->tournament)); }
    
    public function setCity($val) { $this->city = $val; }
    public function setStadiumName($val) { $this->stadiumName = $val; }
    public function setTournament($val) { $this->tournament = $val; }
    
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();
        
        $keyValue['city'] = $DB->quote($this->city);
        $keyValue['stadium_name'] = $DB->quote($this->stadiumName);
        $keyValue['tournament_id'] = $this->tournament instanceof SoccerTournamentsModel ? $this->tournament->id : $this->tournament;
        
        $q = '';
        // in UPDATE case we need a WHERE clause
        if ($this->id != null) {
            $q = $this->buildSqlStatement('soccer_stadiums', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('soccer_stadiums', $keyValue);
        }
    
        if (!$DB->execute($q)) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'soccer_stadiums','id');
        }
    }
    
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->city = $row['city'];
        $this->stadiumName = $row['stadium_name'];
        $this->tournament = $row['tournament_id'];
    }
    
    public static function getByIds($ids) {
        $DB = Database::getHandle();
            
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, city, stadium_name, tournament_id
                FROM ' . DB_SCHEMA . '.soccer_stadiums
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerStadiumsModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }
        
        return $models;
    }
    
    public static function getByTournament($tournament) {
        $DB = Database::getHandle();
            
        $q = 'SELECT id, city, stadium_name, tournament_id
                FROM ' . DB_SCHEMA . '.soccer_stadiums
               WHERE tournament_id = ' . $DB->Quote($tournament->id) . '
            ORDER BY city';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerStadiumsModel;
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
