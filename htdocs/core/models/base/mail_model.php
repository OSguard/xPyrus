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
 
class MailModel extends BaseModel {
    protected $insertAt;
    protected $sent;
    protected $sentAt;
    protected $mailFromName;
    protected $mailFrom;
    protected $mailTo;
    protected $mailSubject;
    protected $mailBody;
    
    public function __construct() {
        parent::__construct();
        
        // set default values
        $this->sent = false;
        $this->sentAt = null;
        $this->mailFromName = '';
        $this->mailFrom = '';
        $this->mailTo = '';
        $this->mailSubject = '';
        $this->mailBody = '';
    }
    
    public function getInsertAt() { return $this->insertAt; }
    public function isSent() { return $this->sent; }
    public function getSentAt() { return $this->sentAt; }
    public function getMailFromName() { return $this->mailFromName; }
    public function getMailFrom() { return $this->mailFrom; }
    public function getMailTo() { return $this->mailTo; }
    public function getMailSubject() { return $this->mailSubject; }
    public function getMailBody() { return $this->mailBody; }
    
    public function setSent($val) { $this->sent = $val; }
    public function setSentAt($val) { $this->sentAt = $val; }
    public function setMailFromName($val) { $this->mailFromName = $val; }
    public function setMailFrom($val) { $this->mailFrom = $val; }
    public function setMailTo($val) { $this->mailTo = $val; }
    public function setMailSubject($val) { $this->mailSubject = $val; }
    public function setMailBody(&$val) { $this->mailBody = $val; }
    
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();
        
        $keyValue['sent'] = $DB->quote(Database::makeBooleanString($this->sent));
        if ($this->sentAt !== null) {
            $keyValue['sent_at'] = Database::getPostgresTimestamp($this->sentAt);
        }
        $keyValue['mail_from_name'] = $DB->quote($this->mailFromName);
        $keyValue['mail_from'] = $DB->quote($this->mailFrom);
        $keyValue['mail_to'] = $DB->quote($this->mailTo);
        $keyValue['mail_subject'] = $DB->quote($this->mailSubject);
        $keyValue['mail_body'] = $DB->quote($this->mailBody);
        
        $q = '';
        // in UPDATE case we need a WHERE clause
        if ($this->id != null) {
            $q = $this->buildSqlStatement('mail', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('mail', $keyValue);
        }

        if (!$DB->execute($q)) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'mail','id');
        }
    }
    
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->insertAt = $row['insert_at'];
        $this->sent = Database::convertPostgresBoolean($row['sent']);
        $this->sentAt = $row['sent_at'];
        $this->mailFromName = $row['mail_from_name'];
        $this->mailFrom = $row['mail_from'];
        $this->mailTo = $row['mail_to'];
        $this->mailSubject = $row['mail_subject'];
        $this->mailBody = $row['mail_body'];
    }
    
    protected function buildFromModel($model) {
        $this->id = $model->id;
        $this->insertAt = $model->insertAt;
        $this->sent = $model->sent;
        $this->sentAt = $model->sentAt;
        $this->mailFromName = $model->mailFromName;
        $this->mailFrom = $model->mailFrom;
        $this->mailTo = $model->mailTo;
        $this->mailSubject = $model->mailSubject;
        $this->mailBody = $model->mailBody;
    }
    
    public static function getByIds($ids) {
        $DB = Database::getHandle();
            
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, EXTRACT(epoch FROM insert_at) AS insert_at, sent, EXTRACT(epoch FROM
                     sent_at) AS sent_at, mail_from_name, mail_from, mail_to, mail_subject,
                     mail_body
                FROM ' . DB_SCHEMA . '.mail
                WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new MailModel;
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
    
    public static function getUnsentMail() {
        $DB = Database::getHandle();
            
        $q = 'SELECT id, EXTRACT(epoch FROM insert_at) AS insert_at, sent, EXTRACT(epoch FROM
                     sent_at) AS sent_at, mail_from_name, mail_from, mail_to, mail_subject,
                     mail_body
                FROM ' . DB_SCHEMA . '.mail
               WHERE NOT sent';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new MailModel;
            $model->buildFromRow($row);
            array_push($models, $model);
        }
        
        return $models;
    }
}
 
?>
