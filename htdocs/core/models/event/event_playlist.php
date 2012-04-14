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

require_once MODEL_DIR . '/base/base_model.php';

/**
 * 
 * @version $Id$
 */
class EventPlaylistModel extends BaseModel {
    protected $artist;
    protected $song;
    protected $userId;
    protected $insertAt;
    
    public function __construct() {
        parent::__construct();
        
        // set default values
        $this->artist = null;
        $this->song = null;
        $this->userId = null;
    }
    
    public function getArtist() { return $this->artist; }
    public function getSong() { return $this->song; }
    public function getUserId() { return $this->userId; }
    public function getInsertAt() { return $this->insertAt; }
    
    public function setArtist($val) { $this->artist = $val; }
    public function setSong($val) { $this->song = $val; }
    public function setUserId($val) { $this->userId = $val; }
    
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();
        
        $keyValue['artist'] = $DB->quote($this->artist);
        $keyValue['song'] = $DB->quote($this->song);
        $keyValue['user_id'] = $DB->quote($this->userId);
        
        $q = '';
        // in UPDATE case we need a WHERE clause
        if ($this->id != null) {
            $q = $this->buildSqlStatement('event_playlist', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('event_playlist', $keyValue);
        }

        if (!$DB->execute($q)) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'event_playlist','id');
        }
    }
    
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->artist = $row['artist'];
        $this->song = $row['song'];
        $this->userId = $row['user_id'];
        $this->insertAt = $row['insert_at'];
    }
    
    public static function getByIds($ids) {
        $DB = Database::getHandle();
            
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, artist, song, user_id, EXTRACT(epoch FROM insert_at) AS insert_at
                FROM ' . DB_SCHEMA . '.event_playlist
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new EventPlaylistModel;
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
    
    public static function countSongs($userId = null){
    	if($userId === null){
    		return 0;
    	}
        
        $DB = Database::getHandle();
        
        $q = 'SELECT count(id) AS nr
                FROM ' . DB_SCHEMA . '.event_playlist
               WHERE user_id = ' . $userId;
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        return $res->fields['nr'];
    }
    
}
?>
