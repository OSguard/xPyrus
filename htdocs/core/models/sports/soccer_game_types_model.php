<?php

require_once MODEL_DIR . '/base/base_model.php';

/**
 * 
 * @version $Id$
 */
class SoccerGameTypesModel extends BaseModel {
    protected $name;
    
    public function __construct() {
        parent::__construct();
        
        $this->name = '';
    }
    
    public function getName() { return $this->name; }
    
    public function setName($val) { $this->name = $val; }
       
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->name = $row['name'];
    }
    
    public static function getByIds($ids) {
        $DB = Database::getHandle();
            
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.soccer_game_types
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerGameTypesModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }
        
        return $models;
    }
    
    public static function getByTournament($tournament) {
        $DB = Database::getHandle();
            
        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.soccer_game_types
               WHERE id IN (SELECT game_type FROM ' . DB_SCHEMA . '.soccer_games WHERE tournament_id = ' . $DB->Quote($tournament->id) . ')
                 AND id <> ' . (int) SoccerGamesModel::GAME_TYPE_GROUP;
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerGameTypesModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }
        
        return $models;
    }
    
    public static function getAll() {
        $DB = Database::getHandle();
            
        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.soccer_game_types';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerGameTypesModel;
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
