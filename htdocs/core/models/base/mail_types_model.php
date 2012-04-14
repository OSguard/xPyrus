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

// $Id: mail_types_model.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/mail_types_model.php $

class MailTypesModel extends BaseModel {
    protected $name;
    
    public function __construct() {
        parent::__construct();
        
        // set default values
        $this->name = null;
    }
    
    public function getName() { return $this->name; }
    
    public function setName($val) { $this->name = $val; }
    
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();
        
        $keyValue['name'] = $DB->quote($this->name);
        
        $q = '';
        // in UPDATE case we need a WHERE clause
        if ($this->id != null) {
            $q = $this->buildSqlStatement('mail_types', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('mail_types', $keyValue);
        }
    
        if (!$DB->execute($q)) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'mail_types','id');
        }
    }
    
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->name = $row['name'];
    }
    
    public static function getByName($name) {
        $DB = Database::getHandle();
                    
        $q = 'SELECT id, name
                FROM public.mail_types
               WHERE name = ' . $DB->quote($name);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach ($res as $k => $row) {
            $model = new MailTypesModel;
            $model->buildFromRow($row);
            return $model;
        }
        
        return null;
    }
    
    public static function getByIds($ids) {
        $DB = Database::getHandle();
            
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, name
                FROM public.mail_types
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $models = array();
        foreach ($res as $k => $row) {
            $model = new MailTypesModel;
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
}


?>
