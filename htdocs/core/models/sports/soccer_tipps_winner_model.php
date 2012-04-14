<?php

require_once MODEL_DIR . '/base/base_model.php';

/**
 * 
 * @version $Id$
 */
class SoccerTippsWinnerModel extends BaseModel {
    protected $user;
    protected $winnerIs;
    protected $points;
    protected $tournament;
    
    public function __construct() {
        parent::__construct();
        
        // set default values
        $this->user = null;
        $this->winnerIs = null;
        $this->points = 0;
        $this->tournament = null;
    }
    
    public function getUser() { return $this->user instanceof UserModel ? $this->user : ($this->user = UserModel::getById($this->user)); }
    public function getWinnerIs() { return $this->winnerIs instanceof SoccerTeamsModel  ? $this->winnerIs : ($this->winnerIs = SoccerTeamsModel::getById($this->winnerIs)); }
    public function getPoints() { return $this->points; }
    public function getTournament() { return $this->tournament instanceof SoccerTournamentsModel ? $this->tournament : ($this->tournament = SoccerTournamentsModel::getById($this->tournament)); }
    
    public function setUser($val) { $this->user = $val; }
    public function setWinnerIs($val) { $this->winnerIs = $val; }
    public function setPoints($val) { $this->points = $val; }
    public function setTournament($val) { $this->tournament = $val; }
    
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();
        
        $keyValue['user_id'] = $DB->quote($this->user instanceof UserModel ? $this->user->id : $this->user);
        $keyValue['winner_is'] = $DB->quote($this->winnerIs instanceof SoccerTeamsModel ? $this->winnerIs->id : $this->winnerIs);
        $keyValue['points'] = $DB->quote($this->points);
        $keyValue['tournament_id'] = $this->tournament instanceof SoccerTournamentsModel ? $this->tournament->id : $this->tournament;
        
        $q = '';
        // in UPDATE case we need a WHERE clause
        if ($this->id != null) {
            $q = $this->buildSqlStatement('soccer_tipps_winner', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('soccer_tipps_winner', $keyValue);
        }
    
        if (!$DB->execute($q)) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'soccer_tipps_winner','id');
        }
    }
    
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->user = $row['user_id'];
        $this->winnerIs = $row['winner_is'];
        $this->points = $row['points'];
        $this->tournament = $row['tournament_id'];
    }
    
    public static function getByIds($ids) {
        $DB = Database::getHandle();
            
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, user_id, winner_is, points, tournament_id
                FROM ' . DB_SCHEMA . '.soccer_tipps_winner
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerTippsWinnerModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }
        
        return $models;
    }
    
    public static function getByUserAndTournament($user, $tournament) {
        $DB = Database::getHandle();
            
        $q = 'SELECT id, user_id, winner_is, points, tournament_id
                FROM ' . DB_SCHEMA . '.soccer_tipps_winner
               WHERE user_id = ' . $DB->Quote($user->id) . '
                 AND tournament_id = ' . $DB->Quote($tournament->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach ($res as $k => $row) {
            $model = new SoccerTippsWinnerModel;
            $model->buildFromRow($row);
            return $model;
        }
        
        return null;
    }
    
     public static function countByTournament($tournament) {
        $DB = Database::getHandle();
            
        $q = 'SELECT count(id) as number, winner_is, max(tournament_id) as tournament_id
                FROM ' . DB_SCHEMA . '.soccer_tipps_winner
               WHERE tournament_id = ' . $DB->Quote($tournament->id)
                 . ' GROUP BY winner_is ORDER BY number DESC';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $teams = array();
        foreach ($res as $k => $row) {
            $team = array();
            $model = SoccerTeamsModel::getById($row['winner_is']);
            $team[0] = $model;
            $team[1] = $row['number'];
            $teams[] = $team;
        }
        
        return $teams;
    }
    
    public static function getById($id) {
        $model = self::getByIds(array($id));
        if (count($model) > 0) {
            return $model[0];
        }
        return null;
    }

    public function delete() {
        $DB = Database::getHandle();

        $q = 'DELETE FROM ' . DB_SCHEMA . '.soccer_tipps_winner
                WHERE id = ' . (int) $this->id;
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
}
?>
