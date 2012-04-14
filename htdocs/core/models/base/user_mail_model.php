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

require_once MODEL_DIR . '/base/mail_model.php';
require_once MODEL_DIR . '/base/mail_types_model.php';

class UserMailModel extends MailModel {
     protected $user;
     protected $userId;
     protected $mailId;
     protected $mailType;
     
     
     public function getMailType() { return $this->mailType; }
     public function setMailType($t) { $this->mailType = $t; }
     
     public function setUserId($u) { $this->userId = $u; }
     
     public function getUser(){
        if ($this->user == null){
            return $this->user;
        }
        if ($this->userId == null){
            return $this->user = UserProtectedModel::getUserById($this->userId);
        }
        return $this->user = new UserAnonymousModel();
    }
     
     public function __construct($mailModel = null) {
         parent::__construct();
         
         if ($mailModel !== null) {
             $this->buildFromModel($mailModel);
             // correct id
             $this->mailId = $this->id;
             $this->id = null;
         }
         
         $this->userId = null;
         $this->user = null;
     }
     
     public static function getAllLogs($limit = 10, $offset = 0, $order = 'desc'){
     	
         // set default sort order; also ensure that no sql injection is possible
        if ($order != 'desc' && $order != 'asc')
            $order = 'desc';
        
        $DB = Database::getHandle();
        
           
        $q = 'SELECT m.*, m.id AS mid, um.* 
                FROM ' . DB_SCHEMA . '.user_mails um
           LEFT JOIN ' . DB_SCHEMA . '.mail m
                  ON um.mail_id = m.id
            ORDER BY m.insert_at ' . $order . ', m.id ' . $order . '
               LIMIT ' . $DB->Quote($limit) . '
              OFFSET ' . $DB->Quote($offset);
        
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        $mails = array();
        foreach($res as $row){
        	$mail = new UserMailModel();
            $mail->buildFromRow($row);
            $mails[] = $mail;
        }
        
        return $mails;
     } 
     
     public static function getLogsBySubstring($substring, $limit = 10, $offset = 0, $order = 'desc'){
        
         // set default sort order; also ensure that no sql injection is possible
        if ($order != 'desc' && $order != 'asc')
            $order = 'desc';
        
        $DB = Database::getHandle();
        
           
        $q = 'SELECT m.*, m.id AS mid, um.* 
                FROM ' . DB_SCHEMA . '.user_mails um
           LEFT JOIN ' . DB_SCHEMA . '.mail m
                  ON um.mail_id = m.id
               WHERE lower(m.mail_to) ILIKE ' . $DB->quote('%'.$substring.'%') . '
            ORDER BY m.insert_at ' . $order . ', m.id ' . $order . '
               LIMIT ' . $DB->Quote($limit) . '
              OFFSET ' . $DB->Quote($offset);
        
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        $mails = array();
        foreach($res as $row){
            $mail = new UserMailModel();
            $mail->buildFromRow($row);
            $mails[] = $mail;
        }
        
        return $mails;
     }
     
     public static function getById($id){
        
        $DB = Database::getHandle();
        
        $q = 'SELECT m.*, m.id AS mid, um.* 
                FROM ' . DB_SCHEMA . '.user_mails um
           LEFT JOIN ' . DB_SCHEMA . '.mail m
                  ON um.mail_id = m.id
               WHERE um.id = ' . $DB->quote($id);
        
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        foreach($res as $row){
            $mail = new UserMailModel();
            $mail->buildFromRow($row);
            return $mail;
        }

     } 
     
    protected function buildFromRow($row){
        parent::buildFromRow($row);
        $this->mailId = $row['mid'];
        $this->mailType = $row['mail_type'];
        $this->sent = Database::convertPostgresBoolean($row['sent']);
        
        if(!empty($row['user_id'])){
            $this->userId = $row['user_id'];
        }
    }
     
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();
        
        if ($this->userId !== null) {
            $keyValue['user_id'] = $DB->quote($this->userId);
        }
        $keyValue['mail_type'] = $DB->quote($this->mailType);
        $keyValue['mail_id'] = $DB->quote($this->mailId);
        
        $q = '';
        // in UPDATE case we need a WHERE clause
        if ($this->id != null) {
            $q = $this->buildSqlStatement('user_mails', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('user_mails', $keyValue);
        }

        if (!$DB->execute($q)) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'user_mails','id');
        }
     }
}
 
?>
