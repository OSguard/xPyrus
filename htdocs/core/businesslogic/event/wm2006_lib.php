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

/*
 * this is the PHP backend for the unihelp WM-2006 bet game
 *
 */

include_once(realpath(dirname(__FILE__) . "/../functions_mysql.php"));
include_once(realpath(dirname(__FILE__) . "/../functions_php.php"));
include_once(realpath(dirname(__FILE__) . "/../smarty.php"));

//$group_game_type_id = get_sql_row_single("SELECT id FROM `wm2006_game_types` WHERE name = 'Vorrunde'");
$group_game_type_id = 1;

$now = time();

function wm2006_get_group_games ($interval = 3600) {
    global $group_game_type_id;
    global $now;

    $q = "SELECT games.id, games.goals_team_1, games.goals_team_1, games.goals_team_2,
                 DATE_FORMAT(games.start_time,'%d.%m., %H:%i') AS pretty_time,
                 UNIX_TIMESTAMP(games.start_time) AS unix_time,
                 games.additional_info,
                 teams1.name AS team1, LOWER(teams1.short) AS team1_short, teams1.group_name AS gruppe1,
                 teams2.name AS team2, LOWER(teams2.short) AS team2_short, stadiums.city AS stadium
            FROM `wm2006_games` AS games, `wm2006_teams` AS teams1, `wm2006_teams` AS teams2,
                 `wm2006_stadiums` AS stadiums
           WHERE games.game_type = " . $group_game_type_id . "
             AND games.team_1=teams1.id
             AND games.team_2=teams2.id
             AND games.stadium=stadiums.id
        ORDER BY gruppe1, games.start_time";

    $games = array();
    $temp_array = array();
    $last_group = '';
    $games_raw = get_sql_array($q);

    foreach ($games_raw as $g) {
        if ($g['gruppe1'] != $last_group && $last_group != '') {
            array_push($games, array('group' => $last_group, 'data' => $temp_array));
            $temp_array = array();
        }

        // check, if game takes place at $interval or more seconds in the future
        if ($g['unix_time'] - $now > $interval) {
            $g['bet_open'] = true;
        } else {
            $g['bet_open'] = false;
        }
        
        // check, if game takes place at $interval or more seconds in the future
        if ($g['bet_open'] and ($g['unix_time'] - $now <= 129600)) {
            $g['near_future'] = true;
        } else {
            $g['near_future'] = false;
        }

        array_push($temp_array, $g);
        $last_group = $g['gruppe1'];
    }
    array_push($games, array('group' => $last_group, 'data' => $temp_array));

    return $games;
}

function wm2006_get_ko_games ($interval = 3600) {
    global $group_game_type_id;
    global $now;

    $q = "SELECT games.id, games.goals_team_1, games.goals_team_1, games.goals_team_2,
                 DATE_FORMAT(games.start_time,'%d.%m., %H:%i') AS pretty_time,
                 UNIX_TIMESTAMP(games.start_time) AS unix_time,
                 games.additional_info,
                 teams1.name AS team1, LOWER(teams1.short) AS team1_short,
                 teams2.name AS team2, LOWER(teams2.short) AS team2_short, stadiums.city AS stadium,
                 gt.name AS gt_str, games.game_type
            FROM `wm2006_games` AS games, `wm2006_teams` AS teams1, `wm2006_teams` AS teams2,
                 `wm2006_stadiums` AS stadiums, `wm2006_game_types` AS gt
           WHERE games.game_type <> " . $group_game_type_id . "
             AND games.team_1=teams1.id
             AND games.team_2=teams2.id
             AND games.stadium=stadiums.id
             AND games.game_type=gt.id
        ORDER BY games.game_type, games.start_time";

    $games = array();
    $temp_array = array();
    $last_gt = '';
    $last_gt_str = '';
    $games_raw = get_sql_array($q);

    foreach ($games_raw as $g) {
        if ($g['game_type'] != $last_gt && $last_gt != '') {
            array_push($games, array('game_type' => $last_gt_str, 'data' => $temp_array));
            $temp_array = array();
        }

        // check, if game takes place at $interval or more seconds in the future
        if ($g['unix_time'] - $now > $interval) {
            $g['bet_open'] = true;
        } else {
            $g['bet_open'] = false;
        }

        array_push($temp_array, $g);
        $last_gt = $g['game_type'];
        $last_gt_str = $g['gt_str'];
    }
    array_push($games, array('game_type' => $last_gt_str, 'data' => $temp_array));

    return $games;
}

function wm2006_get_games () {
    global $now;

    $q = "SELECT games.id, games.goals_team_1, games.goals_team_1, games.goals_team_2,
                 DATE_FORMAT(games.start_time,'%d.%m., %H:%i') AS pretty_time,
                 UNIX_TIMESTAMP(games.start_time) AS unix_time,
                 games.additional_info,
                 teams1.name AS team1, LOWER(teams1.short) AS team1_short, teams1.group_name AS gruppe1,
                 teams2.name AS team2, LOWER(teams2.short) AS team2_short, stadiums.city AS stadium,
                 gt.name AS gt_str, games.game_type
            FROM `wm2006_games` AS games, `wm2006_teams` AS teams1, `wm2006_teams` AS teams2,
                 `wm2006_stadiums` AS stadiums, `wm2006_game_types` AS gt
           WHERE games.team_1=teams1.id
             AND games.team_2=teams2.id
             AND games.stadium=stadiums.id
             AND games.game_type=gt.id
        ORDER BY games.game_type, games.start_time";

    $games = array();
    $temp_array = array();
    $last_gt = '';
    $games_raw = get_sql_array($q);

    /*foreach ($games_raw as $g) {
        if ($g['gruppe1'] != $last_group && $last_group != '') {
            array_push($games, array('group' => $last_group, 'data' => $temp_array));
            $temp_array = array();
        }

        // check, if game result could be known
        if ($g['unix_time'] > ($now + 90*60)) {
            $g['game_finished'] = true;
        } else {
            $g['game_finished'] = false;
        }

        array_push($temp_array, $g);
        $last_group = $g['gruppe1'];
    }*/
    foreach ($games_raw as $g) {
        if ($g['game_type'] != $last_gt && $last_gt != '') {
            array_push($games, array('group' => $last_gt_str, 'data' => $temp_array));
            $temp_array = array();
        }

        // check, if game takes place at $interval or more seconds in the future
        if ($g['unix_time'] - $now > $interval) {
            $g['bet_open'] = true;
        } else {
            $g['bet_open'] = false;
        }

        array_push($temp_array, $g);
        $last_gt = $g['game_type'];
        $last_gt_str = $g['gt_str'];
    }
    array_push($games, array('group' => $last_gt_str, 'data' => $temp_array));
    //array_push($games, array('group' => $last_group, 'data' => $temp_array));

    return $games;
}

function wm2006_get_user_game_bet($username) {
    $q = "SELECT *
            FROM `wm2006_tipps`
           WHERE username = '" . mysql_real_escape_string($username) . "'";

    $bets_raw = get_sql_array($q);

    $bets = array();
    foreach ($bets_raw as $b) {
        $b['points'] /= 2;
        $bets[$b['game_id']] = $b;
    }
    return $bets;
}

function wm2006_is_numerical($str) {
    return $str===null || preg_match('/\d+/',$str);
}

function wm2006_set_user_game_bet($username, $game_id, $goals_team_1, $goals_team_2, $interval=3600) {
    global $now;
    // check for valid value range
    if (!wm2006_is_numerical($game_id) ||
        !wm2006_is_numerical($winner_is) ||
        !wm2006_is_numerical($goals_team_1) ||
        !wm2006_is_numerical($goals_team_2) ||
        !wm2006_is_numerical($id)) {
        // should not occur in reallife
        return false;
    }

    // if one goal field is empty, attempt to delete bet
    if ($goals_team_1===null || $goals_team_2===null) {
        $delete_bet = true;
    } else {
        $delete_bet = false;

        if ($goals_team_1 == $goals_team_2) {
            $winner_is = 1;
        } elseif ($goals_team_1 > $goals_team_2) {
            $winner_is = 0;
        } elseif ($goals_team_1 < $goals_team_2) {
            $winner_is = 2;
        }
    }

    $game_time = get_sql_row("SELECT UNIX_TIMESTAMP(start_time) AS unix_time
                                FROM `wm2006_games`
                               WHERE id='" . $game_id . "'");

    // check, if game takes place at $interval or more seconds in the future
    if ($game_time['unix_time'] - $now > $interval) {
        $bet_open = true;
    } else {
        $bet_open = false;
    }

    if (!$bet_open) {
        mail('toolbar@unihelp.de','wm2006','UPDATE after time by "'.mysql_real_escape_string($username).'" at '.date("d.m.Y H:i:s")." for game $game_id: [$$goals_team_1 : $goals_team_2]");
        return false;
    }

    $id_array = get_sql_array("SELECT id
                                 FROM `wm2006_tipps`
                                WHERE username = '" . mysql_real_escape_string($username) . "'
                                  AND game_id='" . $game_id . "'");
    $has_already_bet = (count($id_array) == 1);

    if (!$delete_bet) {
        if (!$has_already_bet) {
            $q = "INSERT INTO `wm2006_tipps` (username,game_id,winner_is,goals_team_1,goals_team_2)
                    VALUES ('" . mysql_real_escape_string($username) . "','" . $game_id . "','" . $winner_is . "',
                            " . $goals_team_1 . "," . $goals_team_2 . ")";
        } else {
            $q = "UPDATE `wm2006_tipps`
                    SET winner_is='" . $winner_is . "',
                        goals_team_1=" . $goals_team_1 . ",
                        goals_team_2=" . $goals_team_2 . "
                WHERE id=" . $id_array[0]['id'] . "
                    AND username = '" . mysql_real_escape_string($username) . "'
                    AND game_id='" . $game_id . "'";
        }
    } else {
        $q = "DELETE FROM `wm2006_tipps`
                    WHERE username = '" . mysql_real_escape_string($username) . "'
                      AND game_id='" . $game_id . "'";
    }
    //var_dump($q);
    send_sql($q);
    
    return true;
}

function wm2006_set_result($game_id, $goals_team_1, $goals_team_2, $additional_info) {
    global $now;
    //var_dump($game_id, $goals_team_1, $goals_team_2);
    // check for valid value range
    if (!wm2006_is_numerical($game_id) ||
        !wm2006_is_numerical($goals_team_1) ||
        !wm2006_is_numerical($goals_team_2)) {
        // should not occur in reallife
        return false;
    }

    // if one goal field is empty, attempt to delete bet
    if ($goals_team_1===null || $goals_team_2===null) {
        $goals_team_1 = "NULL";
        $goals_team_2 = "NULL";
    }
    
    $q = "UPDATE `wm2006_games`
            SET goals_team_1=" . $goals_team_1 . ",
                goals_team_2=" . $goals_team_2 . ",
                start_time=start_time,
                additional_info = '".mysql_real_escape_string($additional_info)."'
          WHERE id=" . $game_id;

    //var_dump($q);
    send_sql($q);
}

function wm2006_get_teams() {
    $q = "SELECT id,name,LOWER(short) AS short
            FROM `wm2006_teams`
        ORDER BY name ASC";
    $teams_raw = get_sql_array($q);

    $team_id = array();
    $team_name = array();
    $team_short = array();

    foreach ($teams_raw as $t) {
        array_push($team_id, $t['id']);
        array_push($team_name, $t['name']);
        array_push($team_short, $t['short']);
    }

    return array($team_id, $team_name, $team_short);
}

function wm2006_get_stadiums() {
    $q = "SELECT id,city
            FROM `wm2006_stadiums`
        ORDER BY city ASC";
    $stadium_raw = get_sql_array($q);

    $stadium_id = array();
    $stadium_city = array();

    foreach ($stadium_raw as $t) {
        array_push($stadium_id, $t['id']);
        array_push($stadium_city, $t['city']);
    }

    return array($stadium_id, $stadium_city);
}

function wm2006_get_game_types() {
    $q = "SELECT id,name
            FROM `wm2006_game_types`
        ORDER BY id ASC";
    $game_raw = get_sql_array($q);

    $game_id = array();
    $game_name = array();

    foreach ($game_raw as $t) {
        array_push($game_id, $t['id']);
        array_push($game_name, $t['name']);
    }

    return array($game_id, $game_name);
}

function wm2006_set_user_wm_winner($username, $winner_id) {
    if (!wm2006_is_numerical($winner_id)) {
        // should not occur in reallife
        return false;
    }
    $id_array = get_sql_array("SELECT id
                                 FROM `wm2006_tipps_winner`
                                WHERE username = '" . mysql_real_escape_string($username) . "'");
    $has_already_bet = (count($id_array) == 1);

    if ($winner_id) {
        if (!$has_already_bet) {
            $q = "INSERT INTO `wm2006_tipps_winner` (username,winner_is)
                    VALUES ('" . mysql_real_escape_string($username) . "','" . $winner_id . "')";
        } else {
            $q = "UPDATE `wm2006_tipps_winner`
                    SET winner_is='" . $winner_id . "'
                WHERE id=" . $id_array[0]['id'] . "
                    AND username = '" . mysql_real_escape_string($username) . "'";
        }
    } else {
        $q = "DELETE FROM `wm2006_tipps_winner`
                    WHERE username = '" . mysql_real_escape_string($username) . "'";
    }
    //var_dump($q);
    send_sql($q);
    
    return true;
}

function wm2006_get_user_wm_winner($username) {
    $winner_id = get_sql_row_single("SELECT winner_is
                                       FROM `wm2006_tipps_winner`
                                      WHERE username = '" . mysql_real_escape_string($username) . "'");
    return $winner_id;
}

function wm2006_calculate_points() {
    // calculate only the ko-round points
    $finished_games = get_sql_array("SELECT id,goals_team_1,goals_team_2
                                          FROM `wm2006_games`
                                         WHERE goals_team_1 IS NOT NULL
                                           AND goals_team_2 IS NOT NULL
					   AND id>57");
    //var_dump($finished_games);
    // double points for 1/8 final
    foreach ($finished_games as $game) {
        $q = 'UPDATE `wm2006_tipps`
                 SET points=6
               WHERE game_id=' . $game['id'] . '
                 AND SIGN(goals_team_1-goals_team_2)=SIGN(' . $game['goals_team_1'] . '-' . $game['goals_team_2'] . ')';
        //echo $q . "\n";
        send_sql($q);

        $q = 'UPDATE `wm2006_tipps`
                 SET points=9
               WHERE game_id=' . $game['id'] . '
                 AND goals_team_1-goals_team_2=' . $game['goals_team_1'] . '-' . $game['goals_team_2'];
        //echo $q . "\n";
        send_sql($q);

        $q = 'UPDATE `wm2006_tipps`
                 SET points=12
               WHERE game_id=' . $game['id'] . '
                 AND goals_team_1=' . $game['goals_team_1'] . '
                 AND goals_team_2=' . $game['goals_team_2'];
        //echo $q . "\n";
        send_sql($q);
    }
}

function wm2006_calculate_wm_winner_points($winner_id) {
    if (!wm2006_is_numerical($winner_id)) {
        // should not occur in reallife
        return false;
    }
    $q = 'UPDATE `wm2006_tipps_winner`
             SET points=10
           WHERE winner_is = ' . $winner_id;
    //echo $q . "\n";
    send_sql($q);
    
    $q = 'UPDATE `wm2006_tipps_winner`
             SET points=0
           WHERE winner_is <> ' . $winner_id;
    //echo $q . "\n";
    send_sql($q);
    
    return true;
}

function wm2006_get_user_points($username) {
    $q = "SELECT points AS p
            FROM `wm2006_ranking`
           WHERE username='" . mysql_real_escape_string($username) . "'";
    $p=get_sql_array($q);
    if (!count($p)) {
        return 0;
    }
    
    $p = $p[0]['p']/2;
    
/*    $q = "SELECT points AS p
            FROM `wm2006_tipps_winner`
           WHERE username='" . mysql_real_escape_string($username) . "'";
    $p2=get_sql_array($q);
    if (!count($p2)) {
        return $p;
    }
    
    return $p2[0]['p'] + $p;*/
    return $p;
}

function wm2006_recreate_rank_table() {
    // clear old ranking
    send_sql("TRUNCATE TABLE `wm2006_ranking`");
    // recreate with new points
    send_sql("INSERT INTO `wm2006_ranking` (username, points) 
                    SELECT username,sum(points) AS points 
                      FROM `wm2006_tipps` 
                     GROUP by username");
}

function wm2006_get_top_user($number=10,$offset=0) {
/*SELECT t.username,2*tw.points+SUM(t.points) AS points2
FROM `wm2006_tipps` AS t, `wm2006_tipps_winner` AS tw
WHERE t.username=tw.username
GROUP BY t.username
ORDER BY points2 DESC, username ASC*/
    /*$q = "SELECT t.username,2*tw.points+SUM(t.points) AS points_ges
            FROM `wm2006_tipps` AS t, `wm2006_tipps_winner` AS tw
           WHERE t.username=tw.username
        GROUP BY username
        ORDER BY points_ges DESC,
                 username ASC
           LIMIT " . $number . "
          OFFSET " . $offset;*/
    $q = "SELECT username,points AS points_ges
            FROM `wm2006_ranking`
        ORDER BY points_ges DESC,
                 username ASC
           LIMIT " . $number . "
          OFFSET " . $offset;
    $ranking = get_sql_array($q);
    
    // real user points are half of stored number
    $ranking[0]['points_ges'] /= 2;
    // determine rank of first user
    $ranking[0]['rank'] = wm2006_get_rank_by_points($ranking[0]['points_ges']);
    

    for ($i=1; $i<count($ranking); $i++) {
        // real user points are half of stored number
        $ranking[$i]['points_ges'] /= 2;
        
        // if points are the same, we know the rank (the previous)
        if ($ranking[$i]['points_ges'] == $ranking[$i-1]['points_ges']) {
            $ranking[$i]['rank'] = $ranking[$i-1]['rank'];
        } else {
            // otherwise we have to query again
            $ranking[$i]['rank'] = wm2006_get_rank_by_points($ranking[$i]['points_ges']);
        }
    }
    
    /*for ($i=0; $i<count($ranking); $i++) {
        $ranking[$i]['points_ges'] /= 2;
        $ranking[$i]['rank'] = wm2006_get_rank_by_points($ranking[$i]['points_ges'])+1;
    }*/
    return $ranking;
}

function wm2006_get_user_betting() {
    $q = "SELECT COUNT(id) AS nr FROM `wm2006_ranking`";
    return get_sql_row_single($q);
}

function wm2006_get_rank_by_points($points) {
    global $user;
    static $cache;

#  if ($user == "ads" || $user == "linap") {

    if (!defined($cache)) {
      # retrieve the data from database
      # this will only happen on the first call
      $q = "SELECT *
              FROM `wm2006_ranking`";
      $cache = get_sql_array($q);
    }

    $sum = 0;
    foreach ($cache as $value) {
      if ($value["points"] > (2*$points)) {
        $sum++;
      }
    }

    return ($sum + 1);
#  } else {
#    $q = "SELECT COUNT(id) AS nr
#            FROM `wm2006_ranking`
#           WHERE points >".(2*$points);
#
#    return get_sql_row_single($q)+1;
#  }
}

function wm2006_has_already_bet($username) {
    $has_already_bet = get_sql_row_single("SELECT COUNT(id)
                                      FROM `wm2006_tipps`
                                     WHERE username = '" . mysql_real_escape_string($username) . "'");
    return $has_already_bet > 0;
}

function wm2006_complete() {
    $q = "SELECT username AS u, points AS p
            FROM `wm2006_tipps_winner`";
    $p2=get_sql_array($q);

    //var_dump($p2);
    foreach ($p2 as $j) {
        if ($j["p"] > 0) {
                $q = "UPDATE `wm2006_ranking` SET points=ceil(10*points/9)+20 WHERE username = '" . mysql_real_escape_string($j["u"]) . "'";
                echo "$q <br />";
                #send_sql($q);
        }
    }
}

function wm2006_distribute_points() {
    
    $q = "SELECT username AS u, ceil(points/2) AS p
            FROM `wm2006_ranking`";
    $p2=get_sql_array($q);
    
    $aktuell = time();
    $comment = 'Hiermit schreiben wir Dir Deine gewonnenen Punkte für das WM-Tippspiel gut.';
    $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
    $bewertung = '1';
    
    global $user;
    
    foreach ($p2 as $u) {
        $punkte = $u['p'];
        $pluspunkt = $punkte - 1;
        $username = $u['u'];
        
        echo "working at $username <br />";
        
        $q1 = "INSERT INTO unihelp_gaestebuch (id, username, time, eintrag, autor, bewertung, postip) VALUES('','$username','$aktuell','$comment','linap','$bewertung','$REMOTE_ADDR')";
        
        //echo "$q1 <br />";
        if ($user == 'linap' and $_GET['doit'] == '1') {
            send_sql($q1);
        }
        
        $q2 = "INSERT INTO unihelp_gaestebuch_latest (id, username, time, eintrag, autor, bewertung, postip) VALUES(" . get_insert_id() . ",'$username','$aktuell','$comment','linap','$bewertung','$REMOTE_ADDR')";
        
        //echo "$q2 <br />";
        if ($user == 'linap' and $_GET['doit'] == '1') {
            send_sql($q2);
        }
        
        $q3 = "UPDATE unihelp_user SET new_gb_posts = new_gb_posts + 1, punkte = punkte + " . $punkte. " WHERE username='$username'";
        
        //echo "$q3 <br />";
        if ($user == 'linap' and $_GET['doit'] == '1') {
            send_sql($q3);
        }
        
        $q4 = "INSERT INTO unihelp_log_pluspunkte VALUES('$username','$pluspunkt','linap','$REMOTE_ADDR')";
        
        //echo "$q4 <br />";
        if ($user == 'linap' and $_GET['doit'] == '1') {
            send_sql($q4);
        }
        
    }
}

?>
