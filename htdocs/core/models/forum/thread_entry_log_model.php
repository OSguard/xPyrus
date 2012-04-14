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
 * Created on 16.02.2007 by schnueptus
 * sunburner Unihelp.de
 */
 require_once MODEL_DIR . '/forum/thread_entry_model.php';
 
 class ThreadEntryLogModel extends ThreadEntryModel{
 	
    protected $trigger_id;
    
     public function __construct($content_raw = null, $author = null, $parseSettings = array (), $threadId = null, $isAnonymous = false, $caption = '') {
        parent :: __construct($content_raw, $author, $parseSettings, $threadId, $isAnonymous, $caption);
        
    }
    
    protected function buildFromRow($row) {
    	parent :: buildFromRow($row);
        $this->trigger_id = $row['trigger_id'];
    }
    
    
    public static function getHistoryById($id){
        $DB = Database::getHandle();
        
        $q = 'SELECT trigger_id, id, caption, thread_id, post_ip, author_int, 
                  author_ext, entry_parsed, entry_raw, enable_anonymous, group_id,
                  extract(epoch FROM entry_time) AS entry_time,
                  extract(epoch FROM last_update_time) AS last_update_time, trigger_mode
                             FROM forum_thread_entries_log
                            WHERE id = '.$DB->quote($id).'
                              AND trigger_tuple = \'new\'
                            ORDER BY trigger_id DESC';
        
        //var_dump( $q );
        
        $res = $DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        //var_dump($res);
        
        $entrys = array();
        
        foreach($res as $row){
        	$entry = new ThreadEntryLogModel();
            $entry->buildFromRow($row);
            $entrys[] = $entry;
        }
        
        return $entrys;
    }
    
    /**
     * nothing to save
     */
    public function save(){
    	return;
    }
    
    /**
     * nothing to save
     */
    public function __destruct() {
    	return;
    }
 }
?>
