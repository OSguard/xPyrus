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
require_once MODEL_DIR . '/forum/virtuell_forum_model.php';
require_once CORE_DIR . '/utils/cookie_manager.php';

class ForumRead {
	
    protected $user;
    protected $information;
    
    protected static $instance = array();
    
    protected function __construct($user){
        $this->user = $user;
        $this->information = array();
        
        if (!$user->isRegularLocalUser())
            $this->user = null;
    }
    
    public static function getInstance($user) {
        if (!array_key_exists($user->id, self::$instance)) {
            self::$instance[$user->id] = new ForumRead($user);
        }
        return self::$instance[$user->id];
    }
    
    
    public function setRead($forumId, $threadId) {
		if ($this->user == null)
			return;
		
		$DB = Database::getHandle();
		
		$DB->StartTrans();
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.forum_thread_read
                    WHERE thread_id = ' . $DB->Quote($threadId) . '  
                      AND user_id = ' . $DB->Quote($this->user->id);
                            
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

		$q = 'INSERT INTO ' . DB_SCHEMA . '.forum_thread_read
                   (thread_id, user_id)
                  VALUES (' . $DB->Quote($threadId) . ',' . $DB->Quote($this->user->id) . ')';
                            
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
		
		$q = 'DELETE FROM ' . DB_SCHEMA . '.forum_forum_read
                    WHERE forum_id = ' . $DB->Quote($forumId) . '  
                      AND user_id = ' . $DB->Quote($this->user->id);
                            
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

		$q = 'INSERT INTO ' . DB_SCHEMA . '.forum_forum_read
                   (forum_id, user_id)
                  VALUES (' . $DB->Quote($forumId) . ',' . $DB->Quote($this->user->id) . ')';
                            
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
		
		$DB->CompleteTrans();
    }
    
	public function firstNewEntryId($thread){
		if ($this->user == null)
			return 0;
		
		$DB = Database::getHandle();
        
        $q = 'SELECT id
                FROM ' . DB_SCHEMA . '.forum_thread_entries
               WHERE thread_id = ' . $DB->Quote($thread->id) . '  
                 AND entry_time > COALESCE(
                      (SELECT read
                         FROM ' . DB_SCHEMA . '.forum_thread_read
                        WHERE user_id = ' . $DB->Quote($this->user->id) . '
                          AND thread_id = ' . $DB->Quote($thread->id) . '), \'0001-01-01\')
            ORDER BY id ASC
               LIMIT 1';
                            
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
		
		if ($res->fields['id']) {
			return $res->fields['id'];
		}
		
		return 0;
    }
	
	public function hasNewEntry($forum) {
		if ($this->user == null)
			return 0;
		
		$DB = Database::getHandle();
        
        $q = 'SELECT t.entry_time > COALESCE(fr.read, \'0001-01-01\') AS new
                FROM ' . DB_SCHEMA . '.forum_thread_entries t,
                     ' . DB_SCHEMA . '.forum_fora f
           LEFT JOIN ' . DB_SCHEMA . '.forum_forum_read fr
                  ON fr.forum_id = f.id AND fr.user_id = ' . $DB->Quote($this->user->id) . '
               WHERE t.id = f.last_entry 
                 AND f.id = ' . $DB->Quote($forum->id);

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
		
		return Database::convertPostgresBoolean($res->fields['new']);
    }
    
	public function countThreads() {
		if ($this->user == null)
			return 0;
		
		$DB = Database::getHandle();
        
        $q = 'SELECT count(*) AS nr
                FROM ' . DB_SCHEMA . '.forum_thread_entries
               WHERE entry_time > 
                      (SELECT MAX(read)
                         FROM ' . DB_SCHEMA . '.forum_forum_read
                        WHERE user_id = ' . $DB->Quote($this->user->id) . ')';
                            
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
		
		return $res->fields['nr'];
	}
    
}

?>
