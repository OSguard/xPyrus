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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/course/course_file_semester_model.php $

require_once MODEL_DIR . '/base/base_model.php';

/**
 * @class CourseFileSemesterModel
 * @brief model of a semester for a course file
 * 
 * @author linap
 * @version $Id: course_file_semester_model.php 5743 2008-03-25 19:48:14Z ads $
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
class CourseFileSemesterModel extends BaseModel {
    protected $name;
    
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
    
    /**
     * holds all semesters stored in database 
     */
    protected static $semesters = null;
    
    public function __construct($id = null, $name = null) {
        $this->id = $id;
    	$this->name = $name;
    }
    
    public static function getAllSemesters() {
        // if we have pre-fetched semesters, use them
        if (self::$semesters != null) return self::$semesters;
        
    	$DB = Database::getHandle();
        
        // fetch semesters ordered by id descendingly
        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.courses_files_semesters
            ORDER BY id DESC';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $semesters = array();
        foreach ($res as $k => $row) {
            $sem = new CourseFileSemesterModel($row['id'], $row['name']);
            $semesters[$sem->id] = $sem;
        }

        // store semesters in static array
        self::$semesters = $semesters;

        
        return $semesters;
    }
	
	public static function getAllSemesterNames(){
		return array_map(create_function('$e', 'return $e->getName();'), self::getAllSemesters());	
	}
	
	public static function getAllSemesterIDs(){
		return array_map(create_function('$e', 'return $e->id;'), self::getAllSemesters());
	}
	
    
    public static function getSemestersByIds($ids) {
    	$DB = Database::getHandle();
        
        // if no entries are to fetch, return empty array
        if (count($ids)==0) {
            return array();
        }
        
        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.courses_files_semesters
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $semesters = array();
        foreach ($res as $k => $row) {
            $sem = new CourseFileSemesterModel($row['id'], $row['name']);
            
            // insert into semester hash
            $semesters[$sem->id] = $sem;
        }
        
        return $semesters;
    }
    
    public static function getSemesterById($id) {
        // if we have pre-fetched semester, use it
        if (self::$semesters[$id]) {
        	return self::$semesters[$id];
        }
        
        $DB = Database::getHandle();        
        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.courses_files_semesters
               WHERE id = ' . $DB->Quote($id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        if ($res->EOF) {
        	return null;
        }
        $sem = new CourseFileSemesterModel($res->fields['id'], $res->fields['name']);
            
        return $sem;
    }
}

?>
