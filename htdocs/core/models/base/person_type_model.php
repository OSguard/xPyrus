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

require_once MODEL_DIR.'/base/base_model.php';
require_once MODEL_DIR.'/base/university_model.php';

require_once CORE_DIR.'/database.php';

/**
 * Person Types in the database.
 * 
 * @author kyle
 * @package Base
 * @subpackage Models
 */
class PersonTypeModel extends BaseModel {
    /** name of this entry */
    protected $name;
    
    /**
     * constructor
     * 
     * @param $dbRow array with key => values to build an object
     */
    public function __construct($dbRow = null) {
        parent::__construct();
        
        if($dbRow != null)
            $this->buildFromRow($dbRow);
    }
    
    public function getName() {
        return $this->name;
    }
    
    /** 
     * Build a model by an assicotive array given for example from the db.
     * 
     * @param array $row assiciativ array with the values
     */
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->name = $row['name'];
    }

    /**
     * Get a person by the id.
     * 
     * @param integer $id id of the person
     * @return the person or null when no one was found
     */    
    public static function getPersonTypeById($id) {
        $DB = Database::getHandle();
        
        $q = 'SELECT * FROM public.person_types WHERE id = ' . $DB->quote($id);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach($res as $row) {
            return new PersonTypeModel($row);
        }
    }
    
    /**
     * Get a person by its name.
     * 
     * @param string $name name of person type
     * @return the person or null when no one was found
     */    
    public static function getPersonTypeByName($name) {
        $DB = Database::getHandle();
        
        $q = 'SELECT * FROM public.person_types WHERE name = ' . $DB->quote($name);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach($res as $row) {
            return new PersonTypeModel($row);
        }
    }
    
    /**
     * Get a person by the id.
     * 
     * @param arrays $ids ids of the persons
     * @return array a array of persons
     */    
    public static function getPersonTypesByIds($ids) {
        $DB = Database::getHandle();
        
        $q = 'SELECT * FROM public.person_types WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $persons = array();
        
        foreach($res as $row) {
            $persons[] = new PersonTypeModel($row);
        }
        
        return $persons;
    }
    
    /**
     * Get all person types.
     * 
     * @return array a array of persons
     */    
    public static function getAllPersonTypes() {
        $DB = Database::getHandle();
        $q = 'SELECT * FROM public.person_types';
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $persons = array();
        
        foreach($res as $row) {
            $persons[] = new PersonTypeModel($row);
        }
        
        return $persons;
    }
    
    /**
     * save the current model to the DB 
     */
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();

        $keyValue['name'] = $DB->quote($this->name);

        $q = null;
        
        /* update or insert? */
        if($this->id != null)
            $q = $this->buildSqlStatement('person_types', $keyValue, false, 'id=' . $DB->quote($this->id), 'public');
        else
            $q = $this->buildSqlStatement('person_types', $keyValue, true, null, 'public');
                
        if(!$DB->execute($q)) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }
    
}

?>
