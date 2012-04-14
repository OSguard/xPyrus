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

/**
 *  @todo your includes here 
 */

require_once CORE_DIR . '/utils/input_validator.php';
require_once CORE_DIR . '/businesslogic/business_logic_controller.php';
require_once CORE_DIR . '/utils/notifier_factory.php';

require_once MODEL_DIR . '/sports/soccer_tournaments_model.php';
require_once MODEL_DIR . '/sports/soccer_stadiums_model.php';
require_once MODEL_DIR . '/sports/soccer_teams_model.php';
require_once MODEL_DIR . '/sports/soccer_game_types_model.php';
require_once MODEL_DIR . '/sports/soccer_games_model.php';
require_once MODEL_DIR . '/sports/soccer_tipps_model.php';
require_once MODEL_DIR . '/sports/soccer_tipps_winner_model.php';

// the directory, where your templates are placed 
define('SPORTS_TEMPLATE_DIR', 'modules/sports/');

/**
 * @package controller
 * @author YOU
 * @version $Id: skeleton_business_logic_controller.php 5743 2008-03-25 19:48:14Z ads $
 */
class SportsBusinessLogicController extends BusinessLogicController {
    
    public function __construct($ajaxView = false) {
        parent::__construct($ajaxView);
    }
    
    // ----------------------------------------------------------------------
    // NECCESSARY OVERRIDDEN METHODS
    //
    
    /**
     * set methods that are allowed for calling by the controller
     */
    protected function getAllowedMethods() {
        return array_merge(parent::getAllowedMethods(),array(
				'soccerBet',
				'soccerBetRanking',
				'soccerBetUser',
				'soccerBetStats',
				'soccerBetAdmin',
				'soccerBetNotifyWinner',
				
				'home',
                )
            );
    }
	
    /**
     * method, that is executed if no one is explicity called
     * @return string
     */
    protected function getDefaultMethod() {
        return 'home';
    }
    
    /**
     * collects and preprocesses REQUEST parameters for the named method
     * @param string method name
     */
    protected function collectParameters($method) {
        $parameters = array();
        if ($method == "soccerBetUser") {
			$parameters['tournament'] = SoccerTournamentsModel::getById(InputValidator::getRequestData('tournament'));
            $parameters['user'] = UserProtectedModel::getUserByUsername(InputValidator::getRequestData('username'));
        } else if ($method == "soccerBetRanking") {
			$parameters['tournament'] = SoccerTournamentsModel::getById(InputValidator::getRequestData('tournament'));
            $parameters['page'] = InputValidator::getRequestData('page', 1);
        } else if ($method == "soccerBetStats") {
            $parameters['game'] = SoccerGamesModel::getById(InputValidator::getRequestData('game', 0));
        } else if ($method == "soccerBet") {
			$parameters['tournament'] = SoccerTournamentsModel::getById(InputValidator::getRequestData('tournament'));
		}
        $this->_parameters[$method] = $parameters;
        
        // give our parent the possibility to collect some parameters on its own
        parent::collectParameters($method);
    }
    
    public function getMethodObject($method) {
        $parameters = $this->getParameters($method);
        if ('soccerBet' == $method) {
			$tournament = $this->getParameter($method, 'tournament');
            return new BLCMethod($tournament->getName() . NAME_SOCCER_BET,
                rewrite_sports(array('soccerBet' => $tournament)),
                $this->getMethodObject('home'));
        } else if ('soccerBetRanking' == $method) {
			$tournament = $this->getParameter($method, 'tournament');
            return new BLCMethod(NAME_SOCCER_BET_RANKING,
                rewrite_sports(array('soccerBetRanking' => $tournament, 'page' => $this->getParameter($method, 'page'))),
                $this->getMethodObject('soccerBet'));
        } else if ('soccerBetUser' == $method) {
            $user = $this->getParameter($method, 'user');
			$tournament = $this->getParameter($method, 'tournament');
            return new BLCMethod($tournament->getName() . "-Tipp von " . $user->getUsername(),
                rewrite_sports(array('soccerBetUser' => $user, 'tournament' => $tournament)),
                $this->getMethodObject('soccerBet'));
        } else if ('home' == $method) {
            return new BLCMethod(NAME_SPORTS_HOME,
                rewrite_sports(array('home' => true)),
                BLCMethod::getDefaultMethod());
        }
        
        return parent::getMethodObject($method);
    }
    
	protected function home() {
		$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), SPORTS_TEMPLATE_DIR . 'home.tpl');
		$tournaments = SoccerTournamentsModel::getAll();
		
		$this->setCentralView($main);
        $main->assign('tournaments', $tournaments);
        $this->view();
	}
	
    protected function soccerBet() {
        if (!Session::getInstance()->getVisitor()->isRegularLocalUser()) {
            $this->simpleView(SPORTS_TEMPLATE_DIR . 'soccer_bet_unregistered.tpl');
            exit;
        }
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), SPORTS_TEMPLATE_DIR . 'soccer_bet.tpl');
        
        $cUser = Session::getInstance()->getVisitor();
		
		$tournament = $this->getParameter('soccerBet', 'tournament');
        
        $teams = SoccerTeamsModel::getByTournament($tournament);
        $games = SoccerGamesModel::getKOGames($tournament);
        $gamesForGet = array();
        foreach ($games as $g) {
            $gamesForGet = array_merge($gamesForGet, $g['data']);
        }
        if ($tournament->hasGroupStage()) {
            $gamesGroupStage = SoccerGamesModel::getGroupGames($tournament);
            foreach ($gamesGroupStage as $g) {
                $gamesForGet = array_merge($gamesForGet, $g['data']);
            }
        }
        $tipps = SoccerTippsModel::getByGamesAndUser($gamesForGet, $cUser);
        
        $gameTypes = array();
        foreach (SoccerGameTypesModel::getByTournament($tournament) as $gameType) {
            $gameTypes[$gameType->id] = $gameType;
        }
        
        $justBet = array();
        $errorBet = array();
        $justBetWinner = false;
        $errorBetWinner = false;
        
        $tippWinner = SoccerTippsWinnerModel::getByUserAndTournament($cUser, $tournament);
        $tournamentStarted = SoccerGamesModel::isTournamentStarted($tournament);
        $betGameType = InputValidator::getRequestData('bet', 0);
        
        if ($betGameType) {
            $betGameType = SoccerGameTypesModel::getById($betGameType);
            Database::getHandle()->StartTrans();
            foreach ($gamesForGet as $g) {
                if (!$g->isBetOpen())
                    continue;
                
                $bet1 = InputValidator::getRequestData('game' . $g->id . '_team1');
                $bet2 = InputValidator::getRequestData('game' . $g->id . '_team2');
                
                if (ctype_digit($bet1) && ctype_digit($bet2)) {
                    if (!array_key_exists($g->id, $tipps)) {
                        $tip = new SoccerTippsModel();
                        $tip->setGame($g);
                        $tip->setUser($cUser);
                    } else {
                        $tip = $tipps[$g->id];
                    }
                    $tip->setGoalsTeam1($bet1);
                    $tip->setGoalsTeam2($bet2);
                    $tip->save();
                    $justBet[$g->id] = 1;
                    $tipps[$g->id] = $tip;
                } else if ($bet1 || $bet2) {
                    $errorBet[$g->id] = 1;
                }
            }
            Database::getHandle()->CompleteTrans();
        }
        
        if (!$tournamentStarted && InputValidator::getRequestData('bet_winner')) {
            $bet = InputValidator::getRequestData('tipp-winner');
            if ($bet) {
                if (!$tippWinner) {
                    $tippWinner = new SoccerTippsWinnerModel();
                    $tippWinner->setTournament($tournament);
                    $tippWinner->setUser($cUser);
                }
                $found = false;
                foreach ($teams as $t) {
                    if ($t->id == $bet) {
                        $tippWinner->setWinnerIs($t);
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    $tippWinner->save();
                    $justBetWinner = true;
                } else {
                    $errorBetWinner = true;
                }
            } else if ($tippWinner) {
                $tippWinner->delete();
            }
        }

        $this->setCentralView($main);
        $main->assign('tournament', $tournament);
        $main->assign('tournament_started', $tournamentStarted);
        $main->assign('teams', $teams);
        $main->assign('tipp_winner', $tippWinner);
        $main->assign('game_types', $gameTypes);
        if ($betGameType == null) {
            $betGameType = SoccerGamesModel::getUpcomingGameType($tournament);
        }
        $main->assign('upcoming_game_type', $betGameType);
        $main->assign('games', $games);
        if ($tournament->hasGroupStage()) {
            $main->assign('games_group_stage', $gamesGroupStage);
        }
        $main->assign('tipps', $tipps);
        $main->assign('error_bet', $errorBet);
        $main->assign('just_bet', $justBet);
        $main->assign('error_bet_winner', $errorBetWinner);
        $main->assign('just_bet_winner', $justBetWinner);
        $main->assign('ranking', SoccerTippsModel::getRankingByUser($tournament, $cUser));
        $this->view();
    }
    
    protected function soccerBetUser() {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), SPORTS_TEMPLATE_DIR . 'soccer_bet_user.tpl');
        
        $cUser = Session::getInstance()->getVisitor();
        $userToShow = $this->getParameter('soccerBetUser', 'user');
        if (!$userToShow) {
            return;
        }
		
		$tournament = $this->getParameter('soccerBet', 'tournament');
        
        $teams = SoccerTeamsModel::getByTournament($tournament);
		$games = SoccerGamesModel::getKOGames($tournament);
        $gamesForGet = array();
        foreach ($games as $g) {
            $gamesForGet = array_merge($gamesForGet, $g['data']);
        }
        if ($tournament->hasGroupStage()) {
            $gamesGroupStage = SoccerGamesModel::getGroupGames($tournament);
            foreach ($gamesGroupStage as $g) {
                $gamesForGet = array_merge($gamesForGet, $g['data']);
            }
        }
        $tipps = SoccerTippsModel::getByGamesAndUser($gamesForGet, $userToShow);
        
        $gameTypes = array();
        foreach (SoccerGameTypesModel::getByTournament($tournament) as $gameType) {
            $gameTypes[$gameType->id] = $gameType;
        }
                
        $tippWinner = SoccerTippsWinnerModel::getByUserAndTournament($userToShow, $tournament);
        
        $this->setCentralView($main);
		$main->assign('tournament', $tournament);
        $main->assign('user', $userToShow);
        $main->assign('teams', $teams);
        $main->assign('tipp_winner', $tippWinner);
        $main->assign('game_types', $gameTypes);
        $main->assign('games', $games);
        if ($tournament->hasGroupStage()) {
            $main->assign('games_group_stage', $gamesGroupStage);
        }
        $main->assign('tipps', $tipps);
        $main->assign('ranking', SoccerTippsModel::getRankingByUser($tournament, $userToShow));
        $this->view();
    }
    
    protected function soccerBetAdmin() {
        $cUser = Session::getInstance()->getVisitor();
        if (!$cUser->hasRight('SOCCER_BET_ADMIN')) {
            $this->rightsMissingView('SOCCER_BET_ADMIN');
            exit;
        }
        
		$tournament = $this->getParameter('soccerBet', 'tournament');
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), SPORTS_TEMPLATE_DIR . 'soccer_bet_admin.tpl');
        
        $teams = array();
        $teamsReal = array();
        foreach (SoccerTeamsModel::getByTournament($tournament) as $team) {
            $teams[$team->id] = $team->getName();
            $teamsReal[$team->id] = $team;
        }
        $stadiums = array();
        $stadiumsReal = array();
        foreach (SoccerStadiumsModel::getByTournament($tournament) as $stadium) {
            $stadiums[$stadium->id] = $stadium->getCity();
            $stadiumsReal[$stadium->id] = $stadium;
        }
        $gameTypes = array();
        $gameTypesReal = array();
        foreach (SoccerGameTypesModel::getAll() as $gameType) {
            $gameTypes[$gameType->id] = $gameType->getName();
            $gameTypesReal[$gameType->id] = $gameType;
        }
        
        $gamesGroup = SoccerGamesModel::getGroupGames($tournament);
        $gamesKO    = SoccerGamesModel::getKOGames($tournament); 
        
        if (InputValidator::getRequestData('add-game')) {
            $game = new SoccerGamesModel();
            $game->setGameType($gameTypesReal[InputValidator::getRequestData('game_type')]);
            $game->setTeam1($teamsReal[InputValidator::getRequestData('team_1')]);
            $game->setTeam2($teamsReal[InputValidator::getRequestData('team_2')]);
            $game->setStadium($stadiumsReal[InputValidator::getRequestData('stadion')]);
            $game->setStartTime(self::getSmartyDate($_REQUEST, 'startTime', true));
            $game->setTournament($tournament);
            $game->save();
        } 
        if (InputValidator::getRequestData('save-result')) {
            $tempGames = array();
            foreach ($gamesGroup as $g) {
                $tempGames = array_merge($tempGames, $g['data']);
            }
            foreach ($gamesKO as $g) {
                $tempGames = array_merge($tempGames, $g['data']);
            }
            Database::getHandle()->StartTrans();
            foreach ($tempGames as $g) {
                $bet1 = InputValidator::getRequestData('game' . $g->id . '_team1');
                $bet2 = InputValidator::getRequestData('game' . $g->id . '_team2');
                
                if ($bet1 !== null && $bet1 !== '' && $bet2 !== null && $bet2 !== '') {
                    $g->setGoalsTeam1($bet1);
                    $g->setGoalsTeam2($bet2);
                    $g->setAdditionalInfo(InputValidator::getRequestData('game' . $g->id . '_result_type'));
                    $g->save();
                }
            }
            Database::getHandle()->CompleteTrans();
            
            SoccerTippsModel::calculatePoints($tournament);
        }
        if (InputValidator::getRequestData('set_winner')) {
            $t = InputValidator::getRequestData('winner_team');
            SoccerTippsModel::calculatePointsWinner($tournament, $teamsReal[$t]);
        }
        if (InputValidator::getRequestData('day-winners')) {
            $date = InputValidator::getRequestData('day-winners-day');
            $date = explode('.', $date);
            $winners = SoccerTippsModel::calculateDayRanking($tournament, $date[0], $date[1], $date[2]);
            $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), SPORTS_TEMPLATE_DIR . 'soccer_bet_day.tpl');
            $main->assign('winners', $winners);
            $this->setCentralView($main);
            $this->view();
            return;
        }
        
        $main->assign('wm_games', $gamesGroup);
        $main->assign('wm_games2', $gamesKO);
        $main->assign('teams', $teams);
        $main->assign('game_types', $gameTypes);
        $main->assign('stadiums', $stadiums);
        
        $this->setCentralView($main);
        $this->view();
    }
    
    protected function soccerBetRanking() {
		$tournament = $this->getParameter('soccerBet', 'tournament');
        $page = $this->getParameter('soccerBetRanking', 'page');
        $pageSize = 30;
        $totalPages = ceil(SoccerTippsModel::getRankingTotal($tournament) / $pageSize);
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), SPORTS_TEMPLATE_DIR . 'soccer_bet_ranking.tpl');
		$main->assign('tournament', $tournament);
        $main->assign('ranking', SoccerTippsModel::getRanking($tournament, ($page - 1) * $pageSize, $pageSize));
        $main->assign('rank_page', $page);
        $main->assign('max_page', $totalPages);
        $main->assign('page_numbers', range(1, $totalPages));
        
        $main->assign('rankingTotal',SoccerTippsModel::getRankingTotal($tournament));
        $ranking = SoccerTippsWinnerModel::countByTournament($tournament);

        $sum = 0;
        foreach($ranking as $row){
        	$sum += $row[1];
        }
        foreach($ranking as $key => $row){
        	$ranking[$key][2] = ($ranking[$key][1]*100/$sum);
        }
        $main->assign('countUserVoted',$sum);
        $main->assign('rankingList',$ranking);
        
        $this->setCentralView($main);
        $this->view();
    }
	
	protected static function resultToPoint($width, $height, $goalsPerAxis, $g1, $g2) {
		return array(($g1 + 1) * ($width / ($goalsPerAxis + 2)),
		             $height - ($g2 + 1) * ($height / ($goalsPerAxis + 2)));
	}
	
	protected function soccerBetStats() {
		$live = InputValidator::getRequestData('live', false);
		$game = $this->getParameter('soccerBetStats', 'game');
		$stats = SoccerTippsModel::getGameBetStatistics($game);
		
		$adHeight = 100;
		$width = 300;
		$height = 300;
		$goalsPerAxis = 6;
		header("Content-type: image/svg+xml");
		echo '<?xml version="1.0" encoding="utf-8"?>
   <!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" 
  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
   <svg width="' . $width . '" height="' . (($live ? $adHeight : 0) + $height) . '" 
     version="1.1"
     xmlns="http://www.w3.org/2000/svg"
	 xmlns:xlink="http://www.w3.org/1999/xlink">';
	 
	 $total = 0;
	 $max = 0;
	 foreach ($stats as $count) {
		$total += $count[1];
		$max = max($max, $count[1]);
	 }
	 
	 // draw grid;
	 
	 echo '<g stroke="grey" stroke-width="1">';
	 for ($i = 0; $i <= $goalsPerAxis; ++$i) {
		list($x1, $y1) = self::resultToPoint($width, $height, $goalsPerAxis, $i, 0);
		list($x2, $y2) = self::resultToPoint($width, $height, $goalsPerAxis, $i, $goalsPerAxis);
		echo '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2. '" y2="' . $y2 . '" />' . "\n";
		
		list($x1, $y1) = self::resultToPoint($width, $height, $goalsPerAxis, 0, $i);
		list($x2, $y2) = self::resultToPoint($width, $height, $goalsPerAxis, $goalsPerAxis, $i);
		echo '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2. '" y2="' . $y2 . '" />' . "\n";
	 }
	 list($x1, $y1) = self::resultToPoint($width, $height, $goalsPerAxis, 0, 0);
	list($x2, $y2) = self::resultToPoint($width, $height, $goalsPerAxis, $goalsPerAxis, $goalsPerAxis);
	echo '<line x1="' . $x1 . '" y1="' . $y1 . '" x2="' . $x2. '" y2="' . $y2 . '" />' . "\n";
	
	 echo '</g>';
	 
	echo '<g font-family="Verdana" font-size="10" fill="grey" text-anchor="left">';
	 for ($i = 0; $i <= $goalsPerAxis; ++$i) {
		list($x1, $y1) = self::resultToPoint($width, $height, $goalsPerAxis, $i, 0);
		list($x2, $y2) = self::resultToPoint($width, $height, $goalsPerAxis, $i, $goalsPerAxis);
		echo '<text x="' . $x1 . '" y="' . $y1 . '">';
		echo $i;
		echo '</text>';
		
		list($x1, $y1) = self::resultToPoint($width, $height, $goalsPerAxis, 0, $i);
		list($x2, $y2) = self::resultToPoint($width, $height, $goalsPerAxis, $goalsPerAxis, $i);
		echo '<text x="' . $x1 . '" y="' . $y1 . '">';
		echo $i;
		echo '</text>';
	}
	 echo '</g>';
	
	$userBet = null;
	$cUser = Session::getInstance()->getVisitor();
	if ($cUser->isRegularLocalUser()) {
		$userBet = SoccerTippsModel::getByGamesAndUser(array($game), $cUser);
		if (array_key_exists($game->id, $userBet)) {
			$userBet = $userBet[$game->id];
		} else {
			$userBet = null;
		}
	}
	 
	 foreach ($stats as $obj) {
		$count = $obj[1];
		$result = $obj[0];
		 
		if ($result->g1 < 0 || $result->g1 > $goalsPerAxis || 
			$result->g2 < 0 || $result->g2 > $goalsPerAxis)
		{
			continue;
		}
		
		$minRadius = 2.5;
		$maxRadius = 0.5 * $width / ($goalsPerAxis + 2);
		$minOpacity = 0.7;
		$maxOpacity = 1.0;
		
		list($x, $y) = self::resultToPoint($width, $height, $goalsPerAxis, $result->g1, $result->g2);
		$r = $minRadius + ($maxRadius-$minRadius) * $count / $max;
		$o = $minOpacity + ($maxOpacity - $minOpacity) * $count / $max;
		$colorG = floor(200 * (1.0 - $count / $max));
		if ($live) {
			# get XML file from http://ticker.rp-online.de/em2008/xml/index.xml
			$ticker = file('/tmp/index.xml');
			if ($ticker !== false) {
				$result1 = -1;
				$result2 = -1;
				foreach ($ticker as $l) {
					if (preg_match('!<match.*score1="(\d+)".*score2="(\d+)".*status="(\w+)"!', $l, $matches)) {
						$result1 = $matches[1];
						$result2 = $matches[2];
					}
				}
				if ($result->g1 == $result1 && $result->g2 == $result2) {
					$stroke = ' stroke-width="5" stroke="black" stroke-opacity="0.4" ';
				} else {
					$stroke = '';
				}
			}
        } else {
            if ($game->isFinished() && $result->g1 == $game->getGoalsTeam1() && 
                $result->g2 == $game->getGoalsTeam2()) {
                $stroke = ' stroke-width="5" stroke="black" stroke-opacity="0.4" ';
            } else if ($userBet && $result->g1 == $userBet->getGoalsTeam1() && 
                $result->g2 == $userBet->getGoalsTeam2()) {
                $stroke = ' stroke-width="5" stroke="blue" stroke-opacity="0.5" ';
            } else {
                $stroke = '';
            }
        }

		echo '<circle cx="' . $x . '" cy="' . $y . '" r="' . $r . '"
        opacity="' . $o . '"  fill="rgb(255,' . $colorG . ',0)" ' . $stroke . '/>';
	 }
	 
	 echo '<text x="20" y="' . ($height / 2) . '" font-family="Verdana" font-size="12" fill="blue" transform="rotate(-90,20,' . ($height / 2) . ')" text-anchor="middle" >';
	 echo $game->getTeam2()->getName();
	 echo '</text>';
	 echo '<text x="' . ($width / 2) . '" y="' . ($height - 10) . '" font-family="Verdana" font-size="12" fill="blue" text-anchor="middle">';
	 echo $game->getTeam1()->getName();
	 echo '</text>';
	 
	 echo '<image x="' . ($width - 50) . '" y="' . ($height - 30) . '" width="25px" height="25px"
         xlink:href="/images/tippspiel/' . strtolower($game->getTeam1()->getNameShort()) . '2.png" />';
	echo '<image x="0" y="20" width="25px" height="25px"
         xlink:href="/images/tippspiel/' . strtolower($game->getTeam2()->getNameShort()) . '2.png" />';

     if ($live) {
	    echo '<image x="' . (($width - 250) / 2) . '" y="' . $height . '" width="250px" height="' . $adHeight . 'px"
         xlink:href="/images/tippspiel/logo-uni-theke.jpg" />';
     }
	 echo '</svg>';
	}

    protected function soccerBetNotifyWinner() {
        $user = UserProtectedModel::getUserById(InputValidator::getRequestData('uid', 0));

        $randomNumber = rand(1000,9999);

        $text = ViewFactory::getSmartyView(USER_TEMPLATE_DIR, 'mail/soccer_bet_win_day.tpl');
        $text->assign('user', $user);
        $text->assign('number', $randomNumber);
        $text->assign('rank', InputValidator::getRequestData('rank', 0));
        $success = NotifierFactory::createNotifierByName('pm')->notify($user, CAPTION_SOCCER_BET, $text->fetch());

        if ($success) {
            print "successfully notified " . $user->getUsername() . " @ " . $randomNumber;
        } else {
            print "notification for " . $user->getUsername() . " failed";
        }
        exit;
    }
}

?>
