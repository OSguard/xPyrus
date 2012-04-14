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

// $Id: university_model.php 5743 2008-03-25 19:48:14Z ads $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/university_model.php $

require_once MODEL_DIR.'/base/base_model.php';
require_once MODEL_DIR.'/base/city_model.php';
require_once MODEL_DIR.'/base/email_regexp_model.php';

/**
 * class representing a university
 *
 * @package Models
 * @author linap, kyle
 * @subpackage Base
 */
class UniversityModel extends BaseModel {
    /** The name of the university. */
    protected $name;
    protected $nameShort;
    protected $emailRegexp;
    
    /** The id of the city. When you want the CityModel use $uni->city */
    protected $cityId;
    
    public function __construct($dbRow = null) {
        parent::__construct();
        
        if ($dbRow != null) {
            $this->buildFromRow($dbRow);
        }
    }

    /** 
     * Save the current model to the DB
     */
    public function save() {
        $DB = Database::getHandle();
        $keyValue = array('name' =>         $DB->Quote($this->name),
                          'name_short' =>   $DB->Quote($this->nameShort),
                          'city' =>         $DB->Quote($this->cityId));
        
        $q = null;

        /* insert or update? */
        if ($this->id != null) {
            $q = $this->buildSqlStatement('uni', $keyValue, false, 'id=' . $DB->quote($this->id), 'public');
        } else {
            $q = $this->buildSqlStatement('uni', $keyValue, true, null, 'public');
        }
                
        if (!$DB->execute($q)) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }

    /**
     * Return an UniversityModel by a given id.
     * 
     * @param int $id the id of the university to return
     * @return UniversityModel a university
     */    
    public static function getUniversityById($id) {
    	$DB = Database::getHandle();
        
        $q = 'SELECT id, name, name_short, city
                FROM public.uni
               WHERE id=' . $DB->Quote($id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $uni = new UniversityModel($res->fields);
        
        return $uni;
    }
    
    /**
     * Collect all universities
     * 
     * @return array array of UniversityModel
     */
    public static function getAllUniversities($cityOnly = true) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, name_short, city FROM public.uni';
        
        if($cityOnly){
            $q .= ' WHERE city = (SELECT id
                                    FROM public.cities
                                   WHERE schema_name = ' . $DB->Quote(DB_SCHEMA) . ')';
        }
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $unis = array();
        
        foreach($res as $row) {
            $unis[$row['id']] = new UniversityModel($row);
        }
        
        return $unis;
    }
    
    /** 
     * Build a model by an assicotive array given for example from the db.
     * 
     * @param array $row assiciativ array with the values
     */
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->nameShort = $row['name_short'];
        $this->cityId = $row['city'];
    }
    
    public function getCity() {
        return $this->safeReturn('city', '_getCity');
    }
    
    public function getName() {
        return $this->name;
    }
    public function getNameShort() {
        return $this->nameShort;
    }
    public function getEmailRegexp() {
        return $this->safeReturn('emailRegexp', 'getMailRegexps');
    }
    
    protected function __get($name) {
        throw new CoreException("don't use __get($name)");
        if ($name == 'city') {
            return $this->safeReturn('city', 'getCity');
        }
            
        if ($name == 'emailRegexp') {
            return $this->safeReturn('emailRegexp', 'getMailRegexps');
        }
    }
    
    /** 
     * Callback for safeReturn to get the city. 
     * When no cityId was set nothing is done.
     */
    private function _getCity() {
        if ($this->cityId == null) {
            return;
        }
        
        $this->city = CityModel::getCityById($this->cityId);
    }
    
    /**
     * Test that the email address is valid for this university
     * 
     * @return an {@link EmailRegexpModel} is returned when it's valid otherwhise false
     */
    public function isValidEmailAddress($emailAdress) {
        
        foreach ($this->getEmailRegexp() as $regex) {
        //var_dump($regex->emailRegexp, $emailAdress);
            if (preg_match($regex->emailRegexp, $emailAdress)) {
                return $regex;
            }
        }        
        
        return false;
    }
    
    /**
     * Callback for safereturn to get all email 
     * address regexp for that uni
     *
     * @return array array with email address regexp
     * @throws DBException on error
     */
    protected function getMailRegexps() {
        $this->emailRegexp = EmailRegexpModel::getEmailRegexpByUniId($this->id);
    }
    
}

?>
