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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/course/course_file_category_model.php $

require_once MODEL_DIR . '/base/base_model.php';

/**
 * @class CourseFileCategoryModel
 * @brief model of a category for a course file
 * 
 * @author linap
 * @version $Id: course_file_category_model.php 5743 2008-03-25 19:48:14Z ads $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * The following properties are available:
 * <ul>
 * <li><var>id</var>                <b>int</b></li>
 * <li><var>name</var>              <b>string</b></li>
 * </ul>
 * 
 * @package Models/Course
 */
class CourseFileCategoryModel extends BaseModel {
    protected $name;
    
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
    
    /**
     * holds all categories stored in database 
     */
    protected static $categories = null;
    
    public function __construct($id = null, $name = null) {
        $this->id = $id;
    	$this->name = $name;
    }
    
    public static function getAllCategories() {
        // if we have pre-fetched categories, use them
        if (self::$categories != null) return self::$categories;
        
    	$DB = Database::getHandle();

        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.courses_files_categories';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $categories = array();
        foreach ($res as $k => $row) {
            $cat = new CourseFileCategoryModel($row['id'], $row['name']);
            $categories[$cat->id] = $cat;
        }
        
        // store categories in static array
        self::$categories = $categories;
        
        return $categories;
    }
	
	public static function getAllCategoryNames(){
		return array_map(create_function('$e', 'return $e->getName();'), self::getAllCategories());	
	}
	
	public static function getAllCategoryIds(){
		return array_map(create_function('$e', 'return $e->id;'), self::getAllCategories());	
	}
    
    public static function getCategoriesByIds($ids) {
    	$DB = Database::getHandle();
        
        // if no entries are to fetch, return empty array
        if (count($ids)==0) {
            return array();
        }

        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.courses_files_categories
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $categories = array();
        foreach ($res as $k => $row) {
            $cat = new CourseFileCategoryModel($row['id'], $row['name']);
            
            // insert into category hash
            $categories[$cat->id] = $cat;
        }
        
        return $categories;
    }
    
    public static function getCategoryById($id) {
        // if we have pre-fetched category, use it
        if (self::$categories[$id]) {
            return self::$categories[$id];
        }
        
        $DB = Database::getHandle();        
        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.courses_files_categories
               WHERE id = ' . $DB->Quote($id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        if ($res->EOF) {
            return null;
        }
        $cat = new CourseFileCategoryModel($res->fields['id'], $res->fields['name']);
            
        return $cat;
    }
    
}

?>
