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

// $Id: study_path_model.php 5760 2008-03-29 16:45:37Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/study_path_model.php $

require_once MODEL_DIR.'/base/base_model.php';
require_once MODEL_DIR.'/base/tag_model.php';

/**
 * class representing a study path
 *
 * @package Models
 * @subpackage Base
 */
class StudyPathModel extends BaseModel {
    protected $name;
    protected $nameEnglish;
    protected $nameShort;
    protected $description;
    protected $isAvailable;
    protected $uniId;
    
    protected $tags;
    
    protected static $allStudyPaths = array();
    
    public function __construct($id = null, $name = null, $nameShort = null, $nameEnglish = null, $description = null, $isAvailable = null) {
    	$this->id = $id;
        $this->name = $name;
        $this->nameShort = $nameShort;
        $this->nameEnglish = $nameEnglish;
        $this->description = $description;
        $this->isAvailable = $isAvailable;
        
        $this->tags = null;
    }
    
    public static function getStudyPathById($id) {
    	$DB = Database::getHandle();
        
        $q = 'SELECT id, name, name_short, is_available
                FROM ' . DB_SCHEMA . '.study_path
               WHERE id = ' . $DB->Quote($id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $studyPath = new StudyPathModel($res->fields['id'], $res->fields['name'], $res->fields['name_short']);
        $studyPath->isAvailable = Database::convertPostgresBoolean($res->fields['is_available']);
        
        return $studyPath;
    }
    public static function getStudyPathByNameShort($name) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, name_short, is_available
                FROM ' . DB_SCHEMA . '.study_path
               WHERE name_short = ' . $DB->Quote($name);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // if no study path has been found, return
        if ($res->EOF) {
        	return null;
        }
        
        $studyPath = new StudyPathModel($res->fields['id'], $res->fields['name'], $res->fields['name_short']);
        $studyPath->isAvailable = Database::convertPostgresBoolean($res->fields['is_available']);
        
        return $studyPath;
    }
    
    public static function getStudyPathsByIds($ids) {
    	$DB = Database::getHandle();
        
        // if no entries are to fetch, return empty array
        if (count($ids) == 0) {
            return array();
        }
        
        $q = 'SELECT id, name, name_short, is_available
                FROM ' . DB_SCHEMA . '.study_path
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up array to keep order of entries to retrieve
        // orderArray is hash with key [id] and value [position of id in $ids]
        $orderArray = array();
        for ($i=0;$i<count($ids);$i++) {
            $orderArray[$ids[$i]] = $i;
        }

        // build up temporary array out of which return array will be created;
        // pre-fill array with null, so that insertion order of entries does
        // not matter
        $paths = array_fill(0,count($ids),null);
        foreach ($res as $k => $row) {
            $studyPath = new StudyPathModel($row['id'], $row['name'], $row['name_short']);
            $studyPath->isAvailable = Database::convertPostgresBoolean($row['is_available']);
            
            // put in right position in return array
            $paths[$orderArray[$studyPath->id]] = $studyPath;
        }
        
        return $paths;
    }
    
    /**
     * returns an array of all available study paths
     * @return array associative array [id] => [StudyPathModel]
     */
    public static function getAllStudyPaths($uniId = 0) {
        if (!array_key_exists($uniId, self::$allStudyPaths)) {
            $DB = Database::getHandle();
            
            $q = 'SELECT id, name, name_short, is_available
                    FROM ' . DB_SCHEMA . '.study_path';
            if ($uniId != 0) {
                $q .= 
                 ' WHERE uni_id = ' . $DB->Quote($uniId); 
            }
            $q .= 
              ' ORDER BY name';
              
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
            
            // build up return array
            self::$allStudyPaths[$uniId] = array();
            foreach ($res as $k => $row) {
                $studyPath = new StudyPathModel($row['id'], $row['name'], $row['name_short']);
                $studyPath->isAvailable = Database::convertPostgresBoolean($row['is_available']);
                
                self::$allStudyPaths[$uniId][$row['id']] = $studyPath;
            }
        }
        
        return self::$allStudyPaths[$uniId];
    }
    
    protected function loadTags(){
    	$this->tags = TagModel::getTagByStudyPath($this->id);
    }
    
    protected function loadExtendedData() {
        $DB = Database::getHandle();
        $q = 'SELECT name_english, description, uni_id
                FROM ' . DB_SCHEMA . '.study_path
               WHERE id = ' . $DB->Quote($this->id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $this->description = $res->fields['description'];
        $this->nameEnglish = $res->fields['name_english'];
        $this->uniId = $res->fields['uni_id'];
    }

    public function getName() {
        return $this->name;
    }
    public function getNameShort() {
        return $this->nameShort;
    }
    public function isAvailable() {
        return $this->isAvailable;
    }
    public function getNameEnglish() {
        return $this->safeReturn('nameEnglish', 'loadExtendedData');
    }
    public function getDescription() {
        return $this->safeReturn('description', 'loadExtendedData');
    }
    public function getTags() {
        return $this->safeReturn('tags', 'loadTags');
    }
    
    public function setUniId($newId){
    	$this->uniId = $newId;
    }
       
    public function save() {
        $keyValue = array ();

        $DB = Database :: getHandle();
        /** used in all operations */
        $keyValue['name'] = $DB->quote($this->name); 
        $keyValue['name_english'] = $DB->quote($this->nameEnglish);
        $keyValue['name_short'] = $DB->quote($this->nameShort);
        $keyValue['description'] = $DB->quote($this->description);
        $keyValue['is_available'] = ($this->isAvailable) ? 'true' : 'false';
        $keyValue['uni_id'] = $DB->quote($this->uniId);        
                
        /** is update? we need the a where clausel */
        if ($this->id != null)
            $q = $this->buildSqlStatement('study_path', $keyValue, false, 'id = ' . $DB->quote($this->id));
        else
            $q = $this->buildSqlStatement('study_path', $keyValue);
            
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }     

    }
}
?>
