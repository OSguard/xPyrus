<?php

require_once MODEL_DIR . '/base/base_model.php';

/**
 * 
 * @version $Id$
 */
class SoccerTippsModel extends BaseModel {
    protected $user;
    protected $game;
    protected $goalsTeam1;
    protected $goalsTeam2;
    protected $points;
    
    public function __construct() {
        parent::__construct();
        
        // set default values
        $this->user = null;
        $this->game = null;
        $this->winnerIs = 0;
        $this->goalsTeam1 = null;
        $this->goalsTeam2 = null;
        $this->points = 0;
    }
    
    public function getUser() { return $this->user instanceof UserModel ? $this->user : ($this->user = UserModel::getById($this->user)); }
    public function getGame() { return $this->game instanceof SoccerGamesModel ? $this->game : ($this->game = SoccerGamesModel::getById($this->game)); }
    public function getGoalsTeam1() { return $this->goalsTeam1; }
    public function getGoalsTeam2() { return $this->goalsTeam2; }
    public function getPoints() { return $this->points / 2; }
    
    public function setUser($val) { $this->user = $val; }
    public function setGame($val) { $this->game = $val; }
    public function setGoalsTeam1($val) { $this->goalsTeam1 = $val; }
    public function setGoalsTeam2($val) { $this->goalsTeam2 = $val; }
    public function setPoints($val) { $this->points = $val; }
    
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();
        
        $keyValue['user_id'] = $this->user instanceof UserModel ? $this->user->id : $this->user;
        $keyValue['game_id'] = $this->game instanceof SoccerGamesModel ? $this->game->id : $this->game;
        $keyValue['goals_team_1'] = $DB->quote($this->goalsTeam1);
        $keyValue['goals_team_2'] = $DB->quote($this->goalsTeam2);
        $keyValue['points'] = $DB->quote($this->points);
        $keyValue['last_change'] = Database::getPostgresTimestamp(time());
        
        $q = '';
        // in UPDATE case we need a WHERE clause
        if ($this->id != null) {
            $q = $this->buildSqlStatement('soccer_tipps', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('soccer_tipps', $keyValue);
        }
    
        if (!$DB->execute($q)) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'soccer_tipps','id');
        }
    }
    
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->user = $row['user_id'];
        $this->game = $row['game_id'];
        $this->goalsTeam1 = $row['goals_team_1'];
        $this->goalsTeam2 = $row['goals_team_2'];
        $this->points = $row['points'];
    }
    
    public static function getByIds($ids) {
        $DB = Database::getHandle();
            
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, user_id, game_id, goals_team_1, goals_team_2, points
                FROM ' . DB_SCHEMA . '.soccer_tipps
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerTippsModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }
        
        return $models;
    }
    
    public static function getByGamesAndUser($games, $user) {
        $DB = Database::getHandle();
            
        // if no entries are to fetch, return empty array
        if (count($games) == 0) {
            return array();
        }
        
        $q = 'SELECT id, user_id, game_id, goals_team_1, goals_team_2, points
                FROM ' . DB_SCHEMA . '.soccer_tipps
               WHERE game_id IN (' . Database::makeCommaSeparatedString($games, 'id') . ')
                 AND user_id = ' . $DB->Quote($user->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new SoccerTippsModel;
            $model->buildFromRow($row);
            $models[$row['game_id']] = $model;
        }
        
        return $models;
    }
	
	public static function getGameBetStatistics($game) {
        $DB = Database::getHandle();
            
        $q = 'SELECT count(*) AS c , goals_team_1 AS g1, goals_team_2 AS g2
                FROM ' . DB_SCHEMA . '.soccer_tipps 
               WHERE game_id = ' .  $DB->Quote($game->id) . '
            GROUP BY goals_team_1,
                     goals_team_2';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $stats = array();
        foreach ($res as $k => $row) {
            //$stats[] = $row;
			$g = new stdclass;
			$g->g1 = $row['g1'];
			$g->g2 = $row['g2'];
			$stats[] = array($g, $row['c']);
        }
        
        return $stats;
    }
    
    public static function getById($id) {
        $model = self::getByIds(array($id));
        if (count($model) > 0) {
            return $model[0];
        }
        return null;
    }
    
    public static function calculateDayRanking($tournament, $day, $month, $year) {
        $DB = Database::getHandle();
            
        $q = 'SELECT t.user_id, sum(t.points) AS points, avg(date_trunc(\'day\',g.start_time) - t.last_change) AS time 
                FROM ' . DB_SCHEMA . '.soccer_games g, 
                     ' . DB_SCHEMA . '.soccer_tipps t 
               WHERE t.game_id=g.id 
                 AND date_trunc(\'day\',g.start_time) = \'' . sprintf("%04d", (int) $year) . '-' . sprintf("%02d", (int) $month) . '-' . sprintf("%02d", (int) $day) . '\'
                 AND g.tournament_id = ' . $DB->Quote($tournament->id) . '
            GROUP BY date_trunc(\'day\',g.start_time), 
                     t.user_id 
            ORDER BY points DESC,
                     time DESC
               LIMIT 10';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $users = array();
        $winners = array();
        foreach ($res as $k => $row) {
            array_push($users, $row['user_id']);
            array_push($winners, array('user' => $row['user_id'], 
                             'points' => $row['points'] / 2,
                             'time' => $row['time']));
        }

        $users = UserProtectedModel::getUsersByIds($users);
        foreach ($winners as &$w) {
            $w['user'] = $users[$w['user']];
        }
        
        return $winners;
    }

    public function delete() {
        $DB = Database::getHandle();

        $q = 'DELETE FROM ' . DB_SCHEMA . '.soccer_tipps
               WHERE id = ' . (int) $this->id;
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    public static function calculatePointsWinner($tournament, $winner) {
        $DB = Database::getHandle();
        
        $DB->StartTrans();
        
        $q = 'LOCK TABLE ' . DB_SCHEMA . '.soccer_tipps_winner, ' . DB_SCHEMA . '.soccer_games IN EXCLUSIVE MODE';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $q = 'UPDATE ' . DB_SCHEMA . '.soccer_tipps_winner AS str
                 SET points = CASE WHEN winner_is = ' . $DB->Quote($winner->id) . ' 
                                    THEN 2 * (SELECT points_winner
                                                FROM ' . DB_SCHEMA . '.soccer_tournaments 
                                               WHERE id=' . $DB->Quote($tournament->id) . ')
                                   ELSE 0 
                               END
               WHERE tournament_id = ' . $DB->Quote($tournament->id) . '
                 AND winner_is = ' . $DB->Quote($winner->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $q = 'UPDATE ' . DB_SCHEMA . '.users AS u
                 SET points_sum = points_sum + (SELECT SUM(points-points_credited)/2 
                                                  FROM ' . DB_SCHEMA . '.soccer_tipps_winner
                                                 WHERE user_id = u.id
                                                   AND tournament_id = ' . $DB->Quote($tournament->id) . '),
                     points_flow = points_flow + (SELECT SUM(points-points_credited) * (SELECT config_value::INTEGER 
                                                                                         FROM ' . DB_SCHEMA . '.global_config
                                                                                        WHERE config_name = \'POINT_SOURCES_FLOW_MULTIPLICATOR\') / 2
                                                    FROM ' . DB_SCHEMA . '.soccer_tipps_winner
                                                   WHERE user_id = u.id
                                                     AND tournament_id = ' . $DB->Quote($tournament->id) . ')
              WHERE id IN (SELECT user_id 
                             FROM ' . DB_SCHEMA . '.soccer_tipps_winner
                            WHERE tournament_id = ' . $DB->Quote($tournament->id) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $q = 'UPDATE ' . DB_SCHEMA . '.soccer_tipps_winner
                 SET points_credited = points
               WHERE tournament_id = ' . $DB->Quote($tournament->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $q = 'UPDATE ' . DB_SCHEMA . '.soccer_tipps_ranking AS str
                 SET points = CASE
                   WHEN EXISTS (SELECT * FROM ' . DB_SCHEMA . '.soccer_tipps_winner stw
                                 WHERE tournament_id = ' . $DB->Quote($tournament->id) . '
                                   AND winner_is = ' . $DB->Quote($winner->id) . '
                                   AND stw.user_id = str.user_id)
                        THEN points + 2 * (SELECT points_winner 
                                             FROM ' . DB_SCHEMA . '.soccer_tournaments 
                                            WHERE id=' . $DB->Quote($tournament->id) . ')
                   ELSE ceil(points::real * 0.95)
                  END
               WHERE tournament_id = ' . $DB->Quote($tournament->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        self::resetRanking($DB, $tournament);
        
        $DB->CompleteTrans();
    }
    
    public static function calculatePoints($tournament) {
        $DB = Database::getHandle();

        $q = 'UPDATE ' . DB_SCHEMA . '.soccer_tipps AS t
        				 SET points = 
        				 			CASE
        				 				WHEN g.goals_team_1 = t.goals_team_1 AND g.goals_team_2 = t.goals_team_2 THEN 4
        				 				WHEN g.goals_team_1 - g.goals_team_2 = t.goals_team_1 - t.goals_team_2 THEN 3
        				 				WHEN sign(g.goals_team_1 - g.goals_team_2) = sign(t.goals_team_1 - t.goals_team_2) THEN 2
        				 				ELSE 0
        				 	   	END
        				 	  * 
        				 	  	CASE
        				 				WHEN g.game_type >= 4 AND g.game_type <= 6 THEN 3
        				 				WHEN g.game_type = 3 THEN 2
        				 				ELSE 1
        				 	   	END
        				FROM ' . DB_SCHEMA . '.soccer_games AS g
               WHERE t.game_id = g.id 
               	 AND g.start_time < NOW()
                 AND g.tournament_id = ' . $DB->Quote($tournament->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $DB->StartTrans();
        
        $q = 'LOCK TABLE ' . DB_SCHEMA . '.soccer_tipps, ' . DB_SCHEMA . '.soccer_games IN EXCLUSIVE MODE';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $q = 'UPDATE ' . DB_SCHEMA . '.users AS u
                 SET points_sum = points_sum + (SELECT SUM(points-points_credited)/2 
                                                  FROM ' . DB_SCHEMA . '.soccer_tipps
                                                 WHERE user_id = u.id
                                                   AND game_id IN (SELECT id 
                                                                     FROM ' . DB_SCHEMA . '.soccer_games
                                                                    WHERE tournament_id = ' . $DB->Quote($tournament->id) . ')),
                     points_flow = points_flow + (SELECT SUM(points-points_credited) * (SELECT config_value::INTEGER 
                                                                                         FROM ' . DB_SCHEMA . '.global_config
                                                                                        WHERE config_name = \'POINT_SOURCES_FLOW_MULTIPLICATOR\') / 2
                                                    FROM ' . DB_SCHEMA . '.soccer_tipps
                                                   WHERE user_id = u.id
                                                     AND game_id IN (SELECT id 
                                                                       FROM ' . DB_SCHEMA . '.soccer_games
                                                                      WHERE tournament_id = ' . $DB->Quote($tournament->id) . '))
              WHERE id IN (SELECT user_id 
                             FROM ' . DB_SCHEMA . '.soccer_tipps
                            WHERE game_id IN (SELECT id 
                                                FROM ' . DB_SCHEMA . '.soccer_games
                                               WHERE tournament_id = ' . $DB->Quote($tournament->id) . '))';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $q = 'UPDATE ' . DB_SCHEMA . '.soccer_tipps
                 SET points_credited = points
               WHERE game_id IN (SELECT id 
                                   FROM ' . DB_SCHEMA . '.soccer_games
                                  WHERE tournament_id = ' . $DB->Quote($tournament->id) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.soccer_tipps_ranking
        					  WHERE tournament_id = ' . $DB->Quote($tournament->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
                
        $q = 'INSERT INTO ' . DB_SCHEMA . '.soccer_tipps_ranking
        				(tournament_id, rank, user_id, points)
        			SELECT ' . $DB->Quote($tournament->id) . ', 0, user_id, sum(points) AS s 
        				FROM ' . DB_SCHEMA . '.soccer_tipps
        			 WHERE game_id IN (SELECT id FROM ' . DB_SCHEMA . '.soccer_games WHERE tournament_id = ' . $DB->Quote($tournament->id) . ') 
        		GROUP BY user_id';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        self::resetRanking($DB, $tournament);
        
        $DB->CompleteTrans();
    }

    private static function resetRanking($DB, $tournament) {
        $q = 'UPDATE ' . DB_SCHEMA . '.soccer_tipps_ranking r
        				 SET rank = (SELECT count(*) 
                                       FROM ' . DB_SCHEMA . '.soccer_tipps_ranking r2 
                                      WHERE r2.points > r.points
                                        AND tournament_id = ' . $DB->Quote($tournament->id) . ') + 1 
        			WHERE tournament_id = ' . $DB->Quote($tournament->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    public static function getRanking($tournament, $offset = 0, $limit = 30) {
        $DB = Database::getHandle();

        $q = 'SELECT rank, user_id, points 
        				FROM ' . DB_SCHEMA . '.soccer_tipps_ranking
        			 WHERE tournament_id = ' . $DB->Quote($tournament->id) . ' 
        		ORDER BY rank ASC, user_id ASC
        			 LIMIT ' . (int) $limit . '
        		  OFFSET ' . (int) $offset;
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $ranking = array();
        $userIds = array();
        $rank = $offset + 1;
        foreach ($res as $row) {
            $userIds[] = $row['user_id'];
            $ranking[] = array('user' => $row['user_id'], 'points' => $row['points'] / 2, 'rank' => $row['rank']);
            ++$rank;
        }
        $users = UserProtectedModel::getUsersByIds($userIds);
        foreach ($ranking as &$r) {
            $r['user'] = $users[$r['user']];
        }
        return $ranking;
    }
    
    public static function getRankingByUser($tournament, $user) {
        $DB = Database::getHandle();

        $q = 'SELECT rank, points 
        				FROM ' . DB_SCHEMA . '.soccer_tipps_ranking
        			 WHERE tournament_id = ' . $DB->Quote($tournament->id) . '
        			   AND user_id = ' . $DB->Quote($user->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach ($res as $row) {
            return array('points' => $row['points'] / 2, 'rank' => $row['rank']);
        }
        return null;
    }
    
    public static function getRankingTotal($tournament) {
        $DB = Database::getHandle();

        $q = 'SELECT COUNT(*) AS nr
        				FROM ' . DB_SCHEMA . '.soccer_tipps_ranking
        			 WHERE tournament_id = ' . $DB->Quote($tournament->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        return $res->fields['nr'];
    }
    
    public static function getBettingTotal($tournament) {
        $DB = Database::getHandle();

        $q = 'SELECT COUNT(DISTINCT(user_id)) AS nr
        				FROM ' . DB_SCHEMA . '.soccer_tipps
        			 WHERE game_id IN (SELECT id FROM ' . DB_SCHEMA . '.soccer_games WHERE tournament_id = ' . $DB->Quote($tournament->id) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        return $res->fields['nr'];
    }
    /*
     * @id GameId
     * @type 0 = first teams wins
     *       1 = no win
     *       2 = second team wins
     */
    public static function countBettsByGame($gameId, $type = 0){
    	$DB = Database::getHandle();

        $q = 'SELECT COUNT(DISTINCT(user_id)) AS nr
                        FROM ' . DB_SCHEMA . '.soccer_tipps
                     WHERE game_id ='.$DB->Quote($gameId) . '
                     AND ';
        if($type == 0){
        	$q .= 'goals_team_1 > goals_team_2';
        }
        if($type == 1){
            $q .= 'goals_team_1 = goals_team_2';
        }
        if($type == 2){
            $q .= 'goals_team_1 < goals_team_2';
        }             
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        return $res->fields['nr'];
    }
}
?>
