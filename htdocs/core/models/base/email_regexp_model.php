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
require_once MODEL_DIR.'/base/person_type_model.php';

require_once CORE_DIR.'/database.php';


/**
 * Email Regexp for validating
 * 
 * By __get
 * <ul>
 * <li><var>uni</var><b>{@link UniversityModel} </b>the uni of this entry</li>
 * <li><var>personType</var><b>{@link PersonTypeModel} </b>the person type of this entry</li>
 * </ul>
 * 
 * @author kyle
 * @package Base
 * @subpackage Models
 */
class EmailRegexpModel extends BaseModel {
    /** 
     * person type if this model. 
     * A PersonTypeModel object can be gotten via ->personType
     */
    public $personTypeId;
    
    /**
     * Id of the uni of this entry. A UniversityModel 
     * can be gotten via ->university.
     */
    public $uniId;
    
    /**
     * Name of this regexp type
     */
    public $name;
    
    /**
     * don't now
     */
    public $needsValidating;
    
    /**
     * the regexp to test
     */
    public $emailRegexp;
    
    /**
     * the domain part that should be displayed
     */
    public $displayedDomainPart;

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
    
    /** 
     * Build a model by an assicotive array given for example from the db.
     * 
     * @param array $row assiciativ array with the values
     */
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->personTypeId = $row['person_type'];
        $this->uniId = $row['uni'];
        $this->name = $row['name'];
        $this->needsValidating = Database::convertPostgresBoolean($row['needs_validating']);
        $this->emailRegexp = $row['email_regexp'];
        $this->displayedDomainPart = $row['displayed_domain_part'];
    }
    
    /**
     * save the current model to the DB 
     */
    public function save() {
        $keyValue = array();
        $DB = Database::getHandle();

        $keyValue['person_type'] = $DB->quote($this->personTypeId);
        $keyValue['uni'] = $DB->quote($this->uniId);
        $keyValue['name'] = $DB->quote($this->name);
        $keyValue['needs_validating'] = $DB->quote($this->needsValidating);
        $keyValue['email_regexp'] = $DB->quote($this->emailRegexp);
        $keyValue['displayed_domain_part'] = $DB->quote(Database::makeBooleanString($this->displayedDomainPart));

        $q = null;
        
        /* update or insert? */
        if($this->id != null)
            $q = $this->buildSqlStatement('email_regexp', $keyValue, false, 'id=' . $DB->quote($this->id), 'public');
        else
            $q = $this->buildSqlStatement('email_regexp', $keyValue, true, null, 'public');
                
        if(!$DB->execute($q)) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }

    protected function __get($name) {
        
        if($name == 'uni')
            return $this->safeReturn('uni', 'uniSafeReturnCallback');

        if($name == 'personType')
            return $this->safeReturn('personType', 'personTypeCallback');
    }

    /**
     * callback for safe return
     */    
    protected function uniCallback() {
        $this->uni = UniversityModel::getUniversityById($this->uniId); 
    }
        
    /**
     * callback for safe return
     */    
    protected function personTypeCallback() {
        $this->personType = PersonTypeModel::getPersonTypeById($this->personTypeId); 
    }
    
    /**
     * @param integer $id the id of the row set
     * @return the email regexp by the id
     */
    public function getEmailRegexpById($id) {
        $DB = Database::getHandle();
        
        $q = 'SELECT * FROM public.email_regexp WHERE id =' . $DB->quote($id);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $rarray = array();
        
        foreach($res as $row) {
            return new EmailRegexpModel($row);
        }
    }
    
    /**
     * @param integer $uniId the id of the uni
     * @return all email regexp by the uni id
     */
    public function getEmailRegexpByUniId($uniId) {
        $DB = Database::getHandle();
        
        $q = 'SELECT * FROM public.email_regexp WHERE uni =' . $DB->quote($uniId);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $rarray = array();
        
        foreach($res as $row) {
            $rarray[] = new EmailRegexpModel($row);
        }
        
        return $rarray;
    }
    
    /**
     * @return all email regexp distinct by the domain part
     */
    public function getDistinctByDomainPart() {
        $DB = Database::getHandle();
        
        $q = 'SELECT DISTINCT ON (displayed_domain_part) *
                FROM public.email_regexp';
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $rarray = array();
        
        foreach($res as $row) {
            $rarray[] = new EmailRegexpModel($row);
        }
        
        return $rarray;
    }
    
    /**
     * @return all email regexp distinct by the domain part and only for the current city
     */
    public function getDistinctByDomainPartAndCity() {
        $DB = Database::getHandle();
        
        $q = 'SELECT DISTINCT ON (position, displayed_domain_part) *
                FROM public.email_regexp
               WHERE uni IN (SELECT id
                               FROM public.uni
                              WHERE city = (SELECT id
                                              FROM public.cities
                                             WHERE schema_name = ' . $DB->Quote(DB_SCHEMA) . '))
            ORDER BY position ASC,
                     displayed_domain_part ASC';
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $rarray = array();
        
        foreach($res as $row) {
            $rarray[] = new EmailRegexpModel($row);
        }
        
        return $rarray;
    }
    
}

?>
