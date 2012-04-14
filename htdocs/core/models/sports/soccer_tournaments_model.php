<?php

require_once MODEL_DIR . '/base/base_model.php';

/**
 * 
 * @version $Id$
 */
class SoccerTournamentsModel extends BaseModel {
    protected $name;
    protected $description;
	protected $groupStage;
	protected $pointsWinner;
    
    public function __construct() {
        parent::__construct();
        
        // set default values
        $this->name = null;
        $this->description = '';
		$this->groupStage = null;
		$this->pointsWinner = null;
    }
    
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
	public function hasGroupStage() { return $this->groupStage; }
	public function getPointsWinner() { return $this->pointsWinner; }
    
    public function setName($val) { $this->name = $val; }
    public function setDescription($val) { $this->description = $val; }
    
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();
        
        $keyValue['name'] = $DB->quote($this->name);
        $keyValue['description'] = $DB->quote($this->description);
        
        $q = '';
        // in UPDATE case we need a WHERE clause
        if ($this->id != null) {
            $q = $this->buildSqlStatement('soccer_tournaments', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('soccer_tournaments', $keyValue);
        }
    
        if (!$DB->execute($q)) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'soccer_tournaments','id');
        }
    }
    
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->description = $row['description'];
		$this->groupStage = Database::convertPostgresBoolean($row['group_stage']);
		$this->pointsWinner = $row['points_winner'];
    }
    
    public static function getByIds($ids) {
        $DB = Database::getHandle();
            
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, name, description, group_stage, points_winner
                FROM ' . DB_SCHEMA . '.soccer_tournaments
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerTournamentsModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }
        
        return $models;
    }
	
	public static function getAll() {
        $DB = Database::getHandle();
            
        $q = 'SELECT id, name, description, group_stage, points_winner
                FROM ' . DB_SCHEMA . '.soccer_tournaments
            ORDER BY id DESC';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerTournamentsModel;
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

    public static function getByName($name) {
        $DB = Database::getHandle();

        $q = 'SELECT id, name, description, group_stage, points_winner
                FROM ' . DB_SCHEMA . '.soccer_tournaments
               WHERE name = ' . $DB->Quote($name);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach ($res as $k => $row) {
            $model = new SoccerTournamentsModel;
            $model->buildFromRow($row);
            return $model;
        }
        
        return null;
    }
}
?>
