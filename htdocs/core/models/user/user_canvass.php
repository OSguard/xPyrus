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
 * Created on 29.01.2007 by schnueptus
 * sunburner Unihelp.de
 */
require_once MODEL_DIR . '/base/base_model.php';
 
 class UserCanvass extends BaseModel {
 	
    protected $hash;
    protected $email;
    protected $userId;
    
    public function __construct($dbRow = null) {
        parent::__construct();
        
        if($dbRow != null) {
            $this->buildFromRow($dbRow);
        }
    }
    
    protected function buildFromRow($row){
        if(array_key_exists('id', $row)) $this->id = $row['id'];
        if(array_key_exists('user_id', $row)) $this->userId = $row['user_id'];
        if(array_key_exists('hash', $row)) $this->hash = $row['hash'];
        if(array_key_exists('email', $row)) $this->email = $row['email'];
    }
    
    public static function deleteCanvassByHash($hash) {
        $DB = Database::getHandle();
        $q = 'DELETE FROM ' . DB_SCHEMA . '.user_canvass
                    WHERE hash = ' . $DB->Quote($hash);
        
        if (!$DB->execute($q)) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }
    
    public function getHash(){
        if ($this->hash == ''){
            $this->hash = sha1(uniqid('Das, wobei unsere Berechnungen versagen, nennen wir Zufall. (Albert Einstein)'));
        }
        return $this->hash;
    }
    
    public function save(){
        $DB = Database::getHandle();
        
        $keyValue = array();
        $keyValue['user_id'] = $DB->quote($this->userId);
        $keyValue['email'] = $DB->quote($this->email);
        $keyValue['hash'] = $DB->quote($this->getHash());
        
        /* insert or update? */        
        if ($this->id != null) {
            $q = $this->buildSqlStatement('user_canvass', $keyValue, false, 'id=' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('user_canvass', $keyValue, true, null);
        }
        
        //var_dump($q);
        
        if (!$DB->execute($q)) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }
    
 }
 
?>
