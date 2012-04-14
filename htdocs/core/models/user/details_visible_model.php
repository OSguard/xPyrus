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

// $Id: details_visible_model.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/user/details_visible_model.php $

require_once MODEL_DIR.'/base/base_model.php';

/**
 * class representing a "details visible for"-configuration setting
 *
 * @author linap
 * @package Models
 * @subpackage Base
 */
class DetailsVisibleModel extends BaseModel {
    public $name;
    
    public function __construct($id = null, $name = null) {
        $this->id = $id;
        $this->name = $name;
    }
    
    public static function getDetailsVisibleById($id) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name
                FROM public.details_visible
               WHERE id = ' . $DB->Quote($id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $details = new DetailsVisibleModel($res->fields['id'], $res->fields['name']);
        
        return $details;
    }
    
    public static function getDetailsVisibleByName($name) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name
                FROM public.details_visible
               WHERE name = ' . $DB->Quote($name);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $details = new DetailsVisibleModel($res->fields['id'], $res->fields['name']);
        
        return $details;
    }
    
    /**
     * Collect "details visible for"-configuration settings matching given ids
     * 
     * @param array array of id
     * @return array associative array of DetailsVisible; keys are the model ids
     */
    public static function getDetailsVisibleByIds($ids) {
        if (count($ids) == 0) {
            return array();
        }
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name
                FROM public.details_visible
               WHERE id IN (\'' . Database::makeCommaSeparatedString($ids) . '\')';
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $details = array();
        
        foreach ($res as $row) {
            $details[$row['id']] = new DetailsVisibleModel($row['id'], $row['name']);
        }
        
        return $details;
    }
    
    /**
     * Collect all possible "details visible for"-configuration settings
     * 
     * @return array associative array of DetailsVisible; keys are the model ids
     */
   public static function getAllDetailsVisible($accessType = null, $onlyNormal = true) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name
                FROM public.details_visible';
        if ($onlyNormal) {
            $q .=
             ' WHERE is_normal = true';
        }
        if (!empty($accessType)){
        	if($onlyNormal){
        		$q .= ' AND ';                
        	}else{
        		$q .= ' WHERE ';
        	}
            $q .= '( access_type = ' . $DB->quote($accessType);
            $q .= ' OR access_type = \'general\' )';
        }
        //var_dump($q);
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $details = array();
        
        foreach ($res as $row) {
            $details[$row['id']] = new DetailsVisibleModel($row['id'], $row['name']);
        }

        return $details;
    }
    
    public function allowsAccess($level) {        
        if ($this->name != 'all' and $level == null) {
            return false;
        } else if ($level == null) {
            return true;
        }
        return $level->id >= $this->id;
    }
}
?>
