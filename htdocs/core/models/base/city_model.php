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

// $Id: city_model.php 5743 2008-03-25 19:48:14Z ads $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/city_model.php $

require_once MODEL_DIR.'/base/base_model.php';

/**
 * class representing a city
 *
 * @package Models
 * @subpackage Base
 */
class CityModel extends BaseModel {
    protected $name;
    protected $contact;
    protected $schemaName;
    protected $isPublic;
    protected $privateKey;
    protected $publicKey;
    
    protected static $localCity = null;
    
    public function __construct($id = null, $name = null) {
    	$this->id = $id;
        $this->name = $name;
        $this->contact = null;
        $this->schemaName = null;
        $this->isPublic = null;
        $this->privateKey = null;
        $this->publicKey = null;
    }
    
    public static function getCityById($id) {
    	$DB = Database::getHandle();
        
        $q = 'SELECT id, name, schema_name, public, private_key, public_key
                FROM public.cities
               WHERE id = ' . $DB->Quote($id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        if ($res->EOF) {
            return null;
        }
        
        $city = new CityModel($res->fields['id'], $res->fields['name']);
        $city->isPublic = Database::convertPostgresBoolean($res->fields['public']);
        $city->schemaName = $res->fields['schema_name'];
        $city->privateKey = $res->fields['private_key'];
        $city->publicKey  = $res->fields['public_key'];
        
        return $city;
    }
    
    public static function getCityByName($name) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, schema_name, public, private_key, public_key
                FROM public.cities
               WHERE lower(name) = ' . $DB->Quote(strtolower($name));
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        if ($res->EOF) {
        	return null;
        }
        
        $city = new CityModel($res->fields['id'], $res->fields['name']);
        $city->isPublic = Database::convertPostgresBoolean($res->fields['public']);
        $city->schemaName = $res->fields['schema_name'];
        $city->privateKey = $res->fields['private_key'];
        $city->publicKey  = $res->fields['public_key'];
        
        return $city;
    }
    
    public static function getCityBySchema($schema) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, schema_name, public, private_key, public_key
                FROM public.cities
               WHERE schema_name = ' . $DB->Quote(strtolower($schema));
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        if ($res->EOF) {
            return null;
        }
        
        $city = new CityModel($res->fields['id'], $res->fields['name']);
        $city->isPublic = Database::convertPostgresBoolean($res->fields['public']);
        $city->schemaName = $res->fields['schema_name'];
        $city->privateKey = $res->fields['private_key'];
        $city->publicKey  = $res->fields['public_key'];
        
        return $city;
    }
    
    protected function loadExtendedData() {
        $DB = Database::getHandle();
        $q = 'SELECT contact
                FROM public.cities
               WHERE id = ' . $DB->Quote($this->id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $this->contact = $res->fields['contact'];
        
    }
    
    public function isPublic() {
    	return $this->isPublic;
    }
    
    private function __get($var) {
        throw new CoreException("don't use __get($var)");
        switch ($var) {
        case 'name': return $this->name;
        case 'public': return $this->isPublic;
        case 'contact': return $this->safeReturn('contact', 'loadExtendedData');
        case 'schemaName': return $this->schemaName;
        case 'privateKey': return $this->privateKey;
        case 'publicKey': return $this->publicKey;
        }
    }
    
    public function getName() {
        return $this->name;
    }
    public function getContact() {
        return $this->safeReturn('contact', 'loadExtendedData');
    }
    public function getSchemaName() {
        return $this->schemaName;
    }
    public function getPrivateKey() {
        return $this->privateKey;
    }
    public function getPublicKey() {
        return $this->publicKey;
    }
    
    public static function getLocalCity() {
    	if (self::$localCity == null) {
    	   self::$localCity = self::getCityBySchema(DB_SCHEMA);
    	}
        
        return self::$localCity;
    }
}
?>
