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
require_once CORE_DIR . '/boxes/box_controller.php';
require_once CORE_DIR . '/views/view_factory.php';
require_once CORE_DIR . '/parser/parser_factory.php';
require_once CORE_DIR . '/utils/global_ipc.php';
//require_once BASE . '/lib/lib-rssreader/index.php';

class ShoutItem {
	public $entryTime;
    public $text;
    public $isMeMessage;
    public $username;
    public $tinyuserpic;
    
    public function setUser($user) {
        $this->username = $user->getUsername();
        
        $url = $user->getUserpicFile('tiny');
        if (!$url) {
            $genderChar = ($user->getGender() != '') ? $user->getGender() : 'u';
            $url = '/images/kegel-' . $genderChar . '_tiny.png';
        }
        
        $this->tinyuserpic = $url;
        
        $this->user = $user;
    }
    
    public function __construct($entryTime, $user, $text, $isAjax = false) {
		$this->entryTime = $entryTime;
        
        // some hack to get a polymorphic constructor
        // for optimization of model loading it is better to set
        // the id first and store the related model later
        if ($user instanceof UserModel) {
            $this->setUser($user);
        } else {
            $this->user = $user;
        }
        
		$this->text = $text;
        
        /* the message is a me message */
        if(strlen($text) > 5 && substr($text, 0, 3) == '/me') {
        	$this->isMeMessage = true;
            $this->text = substr($text, 4, strlen($text));
        } else {
            $this->isMeMessage = false;        
        }
	}
}

class Shoutbox extends BoxController {
    protected $cacheKey = 'boxes|shoutbox';
    
	public function __construct() {
		parent :: __construct('shoutbox');
	}

	public function getView($ajax = false) {
		$view = ViewFactory :: getSmartyView(Session :: getInstance()->getTemplateDirectory(), 'boxes/shoutbox.tpl');

        if (defined('CACHETEST')) {
            $view->enableCaching();
        }

		// as we observe changes to shoutbox, we can cache for a long time here
        $this->setCanonicalParameters($view, -1, $this->cacheKey, $ajax);

        /*$ipc = new GlobalIPC();
        $view->clearCache($ipc->getTime('SHOUTBOX_CHANGED'));
        $ipc->release();*/
        self::observeIPC(new GlobalIPC(), array('SHOUTBOX_CHANGED'), $view);
        /*if ($ipc->isSetFlag('SHOUTBOX_CHANGED')) {
            $ipc->unsetFlag('SHOUTBOX_CHANGED');
            // call release here and in else-branch
            // in order to release shmem as soon as possible
            $ipc->release();
            
            $view->clearCache();
        } else {
            $ipc->release();
        }*/
        
		if (!$view->isCached() and !$this->minimized) {
			$DB = Database :: getHandle();
			$query = 'SELECT id, user_id, entry_raw, entry_parsed, EXTRACT(EPOCH FROM entry_time) AS etime 
                        FROM ' . DB_SCHEMA .'.box_shoutbox 
                    ORDER BY entry_time DESC 
                       LIMIT ' . V_SHOUTBOX_ENTRIES;

			$res = $DB->execute($query);

			if (!$res) {
				throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
			}

			/* need an array for smarty no object! */
			$a = array ();
            // collect user ids, so we can fetch later all models at once
            $uids = array();
			foreach ($res as $row) {
       		    $a[] = new ShoutItem($row['etime'], 
			                         $row['user_id'], 
			                         $this->parseText($row['id'], $row['entry_raw'], $row['entry_parsed']), true);
                $uids[] = $row['user_id'];
			}
            $userModels = UserProtectedModel::getUsersByIds($uids);
            foreach ($a as $shout) {
                if (array_key_exists($shout->user, $userModels)) {
                    $shout->setUser($userModels[$shout->user]);
                } else {
                    $shout->setUser(new UserAnonymousModel);
                }
            }
            $view->assign('shout_items', $a);
            $view->assign('visitor', Session :: getInstance()->getVisitor());            
		}

		return $view;
	}

	public function addToShoutbox() {
		if (empty ($_REQUEST['shout_text']))
			return;

		$text = $_REQUEST['shout_text'];
		$visitor = Session :: getInstance()->getVisitor();

		if ($visitor->isExternal() || !$visitor->isLoggedIn()) {
			return;
		}
    
        if (!$visitor->hasRight('POST_SHOUTBOX')){
        	return;
        }
    
		if (strlen($text) > 160)
			$text = substr($text, 0, 160);

		$DB = Database :: getHandle();
		$query = 'SELECT user_id, entry_raw 
                    FROM ' . DB_SCHEMA . '.box_shoutbox 
                ORDER BY entry_time DESC
                   LIMIT 2';
		$res = $DB->execute($query);

		//test for flooding
		$userIdCounter = 1;
		$entryCounter = 1;
		
		foreach ($res as $row) {
		    if($visitor->id == $row['user_id'])
		        $userIdCounter++;
		        
	        if($text == $row['entry_raw'])
	            $entryCounter++;
		}
		
		/* it's flooding ignore the request */
        if ($userIdCounter == 3 || $entryCounter == 3) {
            /* it's an ajax request so stop processing here */
	        if (array_key_exists('view', $_REQUEST) && $_REQUEST['view'] == 'ajax') {
                exit;
            }

            return;
		}
		

		if (!$res) {
			throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
		}

		$query = 'INSERT INTO ' . DB_SCHEMA . '.box_shoutbox(user_id, entry_raw, entry_parsed) VALUES (' .
		$DB->quote($visitor->id) . ', ' . $DB->quote($text) . ', ' . $DB->quote($this->parseText(0, $text, '')) . ')';

		$res = $DB->execute($query);

        // signal changes to shoutbox
        $ipc = new GlobalIPC();
        $ipc->setTime('SHOUTBOX_CHANGED');
        $ipc->release();     

		if (!$res) {
			throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
		}
        
        /* it's an ajax request so stop processing here */
        if (array_key_exists('view', $_REQUEST) && $_REQUEST['view'] == 'ajax') {
            exit;
        }
	}

	protected function getAllowedMethods() {
		return array_merge(parent :: getAllowedMethods(), array (
			'addToShoutbox',
			'ajaxGetShoutEntries'
		));
	}

	protected function ajaxGetShoutEntries() {
        $lastUpdate = null;
        $session = Session::getInstance();
        
        /* invalid format -> last update time was 10s ago */
        if(!array_key_exists('lastUpdate', $_REQUEST) || !is_numeric($_REQUEST['lastUpdate']))
            $lastUpdate = 0;
        else
            $lastUpdate = $_REQUEST['lastUpdate'];
        
		$DB = Database :: getHandle();
		$query = 'SELECT id, user_id, entry_raw, entry_parsed, EXTRACT(EPOCH FROM entry_time) AS etime 
                    FROM ' . DB_SCHEMA . '.box_shoutbox';
        
        if ($lastUpdate > 0)
            $query .= ' WHERE entry_time > (SELECT entry_time 
                                              FROM '. DB_SCHEMA . '.box_shoutbox 
                                             WHERE id=' . $DB->quote($lastUpdate) . ' 
                                             LIMIT 1) 
                     ORDER BY entry_time ASC 
                        LIMIT ' . V_SHOUTBOX_ENTRIES;
        else
            $query .= ' ORDER BY entry_time DESC 
                           LIMIT 1';
            
		$res = $DB->execute($query);

		if (!$res) {
			throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
		}


		/* need an array for smarty no object! */
		$a = array ();
        $lastId = 0;
        
        /*
		foreach ($res as $row) {
		    $a[] = new ShoutItem($row['etime'], 
		                         UserModel::getUserById($row['user_id']), 
		                         $this->parseText($row['id'], $row['entry_raw'], $row['entry_parsed']), true);
            $lastId = $row['id'];
		}*/
        // collect user ids, so we can fetch later all models at once
        $uids = array();
        foreach ($res as $row) {
            $a[] = new ShoutItem($row['etime'], 
                                 $row['user_id'], 
                                 $this->parseText($row['id'], $row['entry_raw'], $row['entry_parsed']), true);
            $lastId = $row['id'];
            $uids[] = $row['user_id'];
        }
        $userModels = UserProtectedModel::getUsersByIds($uids);
        foreach ($a as $shout) {
            $shout->setUser($userModels[$shout->user]);
        }

        if($lastUpdate != 0)
            print json_encode(array($lastId, $a));
        else
            print json_encode(array($lastId, array()));
        
        exit;
	}
	
	protected function parseText($id, &$rawText, $parsedText) {
	    
	    /* text is alreally parsed */
	    if($parsedText != '')
	        return $parsedText;
	        
        //echo 'parse text!';
	    
	    /* lazy initialisiation of the parsers */
	    if(!isset($this->parsers)) {
			$this->parsers = ParserFactory::createParserFromSettings(array (
				BaseEntryModel :: PARSE_AS_SMILEYS => 'true',
	            BaseEntryModel :: PARSE_AS_HTML => 'true',
                BaseEntryModel :: PARSE_MAX_SMILEYS => 5                
			));
	    }
	    
	    $parsedText = $rawText;
	    
	    /* apply parser */
		foreach ($this->parsers as $parser)
    		$parsedText = $parser->parse($parsedText);
		
		//parse URLs
		//$parsedText = preg_replace('#(?<=^|[^"])((?:https?|ftp)://[\w\-\~,/%\.&\?=\#:@]+[\w\-\~/%&\?=\#:@])(?=$|[^"])#i', ' <a href="\\1">link</a> ', $parsedText);
		
		//split to long words
		$parsedText = preg_replace_callback("#(?<=^|\s)(\w{15,})(?>=$|\s|)#", create_function('$treffer', 'return wordwrap($treffer[0], 15, " ", true);'), $parsedText);
		
		if($id != 0) {
			$DB = Database :: getHandle();
			$query = 'UPDATE ' . DB_SCHEMA . '.box_shoutbox SET entry_parsed=' . $DB->quote($parsedText) . ' WHERE id = ' . $DB->quote($id);
    		
			if (!$DB->execute($query)) {
			    throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
		    }
		}
		

		return $parsedText;
	}

}
?>
