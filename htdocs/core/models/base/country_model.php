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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/country_model.php $

require_once MODEL_DIR.'/base/base_model.php';

/**
  * @class CountryModel
  * @brief This is the class representing a country.
  * 
  * @author linap
  * @version $Id: country_model.php 5807 2008-04-12 21:23:22Z trehn $
  * @copyright Copyright &copy; 2006, Unihelp.de
  * 
  * The following properties are available via __get-magic:
  * 
  * <ul>
  * <li><var>id</var>           <b>int</b>          id of country</li>
  * <li><var>name</var>         <b>string</b>       country's name</li>
  * <li><var>nationality</var>  <b>string</b>       country's nationality</li>
  * <li><var>areaCode</var>     <b>string</b>       area code (telephone)</li>
  * <li><var>zipCode</var>      <b>string</b>       zip code</li>
  * <li><var>zipCodeLength</var>    <b>string</b>   length of zip code</li>
  * </ul>
  * 
  * @package Models/Base
  */
class CountryModel extends BaseModel {
    protected $name;
    protected $nationality;
    protected $isoCode;
    
    /*protected $areaCode;
    protected $zipCode;
    protected $zipCodeLength;*/
    
    public function __construct($id = null, $name = null, $nationality = null, $isoCode = null
                                /*$areaCode = null, $zipCode = null, $zipCodeLength = null*/) {
        $this->id = $id;
        $this->name = $name;
        $this->nationality = $nationality;
        $this->isoCode = $isoCode;
        /*$this->areaCode = $areaCode;
        $this->zipCode = $zipCode;
        $this->zipCodeLength = $zipCodeLength;*/
    }
    
    public static function getCountryById($id) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, nationality, iso_code
                FROM public.countries
               WHERE id = ' . $DB->Quote($id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $country = new CountryModel($res->fields['id'], $res->fields['name'],
                                 $res->fields['nationality'], $res->fields['iso_code']);
        
        return $country;
    }
    
    /**
     * Collect all countries
     * 
     * @return array array of CountryModel
     */
    public static function getAllCountries() {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, nationality, iso_code
                FROM public.countries 
            ORDER BY name ASC';
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $countries = array();
        
        foreach($res as $row) {
            $countries[] = new CountryModel($row['id'], $row['name'],
                    $row['nationality'], $row['iso_code']);
        }

        return $countries;
    }
    
    public function getName() {
        return $this->name;
    }
    public function getNationality() {
        return $this->nationality;
    }
    public function getIsoCode() {
        return $this->isoCode;
    }
}
?>
