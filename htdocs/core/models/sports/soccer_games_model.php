<?php

require_once MODEL_DIR . '/base/base_model.php';

/**
 * 
 * @version $Id$
 */
class SoccerGamesModel extends BaseModel {
    protected $team1;
    protected $team2;
    protected $goalsTeam1;
    protected $goalsTeam2;
    protected $additionalInfo;
    protected $startTime;
    protected $stadium;
    protected $gameType;
    protected $tournament;
    
    public function __construct() {
        parent::__construct();
        
        // set default values
        $this->team1 = null;
        $this->team2 = null;
        $this->goalsTeam1 = 0;
        $this->goalsTeam2 = 0;
        $this->additionalInfo = '';
        $this->startTime = null;
        $this->stadium = null;
        $this->gameType = null;
        $this->tournament = null;
    }
    
    public function getTeam1() { return $this->team1 instanceof SoccerTeamsModel ? $this->team1 : ($this->team1 = SoccerTeamsModel::getById($this->team1)); }
    public function getTeam2() { return $this->team2 instanceof SoccerTeamsModel ? $this->team2 : ($this->team2 = SoccerTeamsModel::getById($this->team2)); }
    public function getGoalsTeam1() { return $this->goalsTeam1; }
    public function getGoalsTeam2() { return $this->goalsTeam2; }
    public function getAdditionalInfo() { return $this->additionalInfo; }
    public function getStartTime() { return $this->startTime; }
    public function getStadium() { return $this->stadium instanceof SoccerStadiumsModel ? $this->stadium : ($this->stadium = SoccerStadiumsModel::getById($this->stadium)); }
    public function getGameType() { return $this->gameType instanceof SoccerGameTypesModel ? $this->gameType : ($this->gameType = SoccerGameTypesModel::getById($this->gameType)); }
    public function getTournament() { return $this->tournament instanceof SoccerTournamentsModel ? $this->tournament : ($this->tournament = SoccerTournamentsModel::getById($this->tournament)); }
    
    public function setTeam1($val) { $this->team1 = $val; }
    public function setTeam2($val) { $this->team2 = $val; }
    public function setGoalsTeam1($val) { $this->goalsTeam1 = $val; }
    public function setGoalsTeam2($val) { $this->goalsTeam2 = $val; }
    public function setAdditionalInfo($val) { $this->additionalInfo = $val; }
    public function setStartTime($val) { $this->startTime = $val; }
    public function setStadium($val) { $this->stadium = $val; }
    public function setGameType($val) { $this->gameType = $val; }
    public function setTournament($val) { $this->tournament = $val; }
    
    public function isNearFuture() {
        $timeDiff = $this->getStartTime() - time(); 
        return 0 < $timeDiff && $timeDiff < 2 * 86400; 
    }
    public function isBetOpen() { return $this->getStartTime() - time() > 3600; }
	
	public function isFinished() { return time() - $this->getStartTime() > 6300; }
    
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();
        
        $keyValue['team_1'] = $this->team1 instanceof SoccerTeamsModel ? $this->team1->id : $this->team1;
        $keyValue['team_2'] = $this->team2 instanceof SoccerTeamsModel ? $this->team2->id : $this->team2;
        if ($this->goalsTeam1 !== null) {
            $keyValue['goals_team_1'] = $DB->quote($this->goalsTeam1);
        } else {
            $keyValue['goals_team_1'] = 'NULL';
        }
        if ($this->goalsTeam2 !== null) {
            $keyValue['goals_team_2'] = $DB->quote($this->goalsTeam2);
        } else {
            $keyValue['goals_team_2'] = 'NULL';
        }
        $keyValue['additional_info'] = $DB->quote($this->additionalInfo);
        if ($this->startTime !== null) {
            $keyValue['start_time'] = Database::getPostgresTimestamp($this->startTime);
        } else {
            $keyValue['start_time'] = 'NULL';
        }
        $keyValue['stadium'] = $this->stadium instanceof SoccerStadiumsModel ? $this->stadium->id : $this->stadium;
        $keyValue['game_type'] = $this->gameType instanceof SoccerGameTypesModel ? $this->gameType->id : $this->gameType;
        $keyValue['tournament_id'] = $this->tournament instanceof SoccerTournamentsModel ? $this->tournament->id : $this->tournament;
        
        $q = '';
        // in UPDATE case we need a WHERE clause
        if ($this->id != null) {
            $q = $this->buildSqlStatement('soccer_games', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('soccer_games', $keyValue);
        }
    
        if (!$DB->execute($q)) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'soccer_games','id');
        }
    }
    
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->team1 = $row['team_1'];
        $this->team2 = $row['team_2'];
        $this->goalsTeam1 = $row['goals_team_1'];
        $this->goalsTeam2 = $row['goals_team_2'];
        $this->additionalInfo = $row['additional_info'];
        $this->startTime = $row['start_time'];
        $this->stadium = $row['stadium'];
        $this->gameType = $row['game_type'];
        $this->tournament = $row['tournament_id'];
    }
    
    public static function getByIds($ids) {
        $DB = Database::getHandle();
            
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, team_1, team_2, goals_team_1, goals_team_2, additional_info, EXTRACT(epoch FROM start_time)
                     AS start_time, stadium, game_type, tournament_id
                FROM ' . DB_SCHEMA . '.soccer_games
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerGamesModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }
        
        return $models;
    }
    
    public static function getByToday() {
        $DB = Database::getHandle();
            
        $q = 'SELECT id, team_1, team_2, goals_team_1, goals_team_2, additional_info, EXTRACT(epoch FROM start_time)
                     AS start_time, stadium, game_type, tournament_id
                FROM ' . DB_SCHEMA . '.soccer_games
               WHERE date_trunc(\'day\', start_time) = CURRENT_DATE
            ORDER BY start_time ASC';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerGamesModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }
        
        return $models;
    }
    
    public static function getGroupGames($tournament) {
        $DB = Database::getHandle();
            
        $q = 'SELECT g.id, team_1, team_2, goals_team_1, goals_team_2, additional_info, EXTRACT(epoch FROM start_time)
                     AS start_time, stadium, game_type, g.tournament_id, t1.group_name AS group
                FROM ' . DB_SCHEMA . '.soccer_games g,
                     ' . DB_SCHEMA . '.soccer_teams t1,
                     ' . DB_SCHEMA . '.soccer_teams t2
               WHERE g.tournament_id = ' . $DB->Quote($tournament->id) . '
                 AND team_1 = t1.id
                 AND team_2 = t2.id
                 AND game_type = ' . $DB->Quote(self::GAME_TYPE_GROUP) . '
            ORDER BY t1.group_name,
                     start_time'
        ;
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $lastGroup = null;
        $models = array();
        $group = array();
        foreach ($res as $k => $row) {
            if ($lastGroup != $row['group'] && $lastGroup != null) {
                $models[] = array('group' => $lastGroup, 'data' => $group);
                $group = array();
            }
            $lastGroup = $row['group'];
            
            $model = new SoccerGamesModel;
            $model->buildFromRow($row);
            array_push($group, $model);
        }
        if ($lastGroup)
            $models[] = array('group' => $lastGroup, 'data' => $group);
        
        return $models;
    }
    
    const GAME_TYPE_GROUP = 1;
    
    public static function getKOGames($tournament) {
        $DB = Database::getHandle();
            
        $q = 'SELECT id, team_1, team_2, goals_team_1, goals_team_2, additional_info, EXTRACT(epoch FROM start_time)
                     AS start_time, stadium, game_type, tournament_id
                FROM ' . DB_SCHEMA . '.soccer_games g
               WHERE tournament_id = ' . $DB->Quote($tournament->id) . '
                 AND game_type <> ' . $DB->Quote(self::GAME_TYPE_GROUP) . '
            ORDER BY game_type,
                     start_time';
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $lastGameType = null;
        $models = array();
        $round = array();
        foreach ($res as $k => $row) {
            if ($lastGameType != $row['game_type'] && $lastGameType != null) {
                $models[] = array('game_type' => $lastGameType, 'data' => $round);
                $round = array();
            }
            $lastGameType = $row['game_type'];
            
            $model = new SoccerGamesModel;
            $model->buildFromRow($row);
            array_push($round, $model);
        }
        if ($lastGameType)
            $models[] = array('game_type' => $lastGameType, 'data' => $round);

        return $models;
    }
    
    public static function getById($id) {
        $model = self::getByIds(array($id));
        if (count($model) > 0) {
            return $model[0];
        }
        return null;
    }
    
    public static function getUpcomingGameType($tournament) {
        $DB = Database::getHandle();
            
        $q = 'SELECT MIN(game_type) AS game_type
                FROM ' . DB_SCHEMA . '.soccer_games
               WHERE tournament_id = ' . $DB->Quote($tournament->id) . '
                 AND start_time + \'7 hours\' > NOW()';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $gid = $res->fields['game_type'];
        if (!$gid) {
            return null;
        }
        
        return SoccerGameTypesModel::getById($gid);
    }
    
    public static function isTournamentStarted($tournament) {
        $DB = Database::getHandle();
            
        $q = 'SELECT MIN(start_time) < NOW() AS started
                FROM ' . DB_SCHEMA . '.soccer_games
               WHERE tournament_id = ' . $DB->Quote($tournament->id);

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        return Database::convertPostgresBoolean($res->fields['started']);
    }
    
    public function getBetQuote(){
    	$values = array();
        $values[] = SoccerTippsModel::countBettsByGame($this->id,0);
        $values[] = SoccerTippsModel::countBettsByGame($this->id,1);
        $values[] = SoccerTippsModel::countBettsByGame($this->id,2);
        return $values;
    }
}
?>
