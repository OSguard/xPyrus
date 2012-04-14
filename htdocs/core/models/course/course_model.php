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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/course/course_model.php $

require_once MODEL_DIR . '/base/base_model.php';
require_once MODEL_DIR . '/base/user_protected_model.php';
require_once MODEL_DIR . '/course/course_file_model.php';
require_once MODEL_DIR . '/forum/forum_model.php';

/**
 * @class CourseModel
 * @brief model of course
 * 
 * @author linap
 * @version $Id: course_model.php 5746 2008-03-28 17:43:58Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * The following properties are available:
 * <ul>
 * <li><var>id</var>                <b>int</b></li>
 * <li><var>name</var>              <b>string</b></li>
 * <li><var>nameShort</var>         <b>string</b></li>
 * <li><var>nameEnglish</var>       <b>string</b></li>
 * <li><var>subscriptors</var>      <b>array</b>    array of UserModel; subscriptors of this course</li>
 * <li><var>subscriptorsNumber</var><b>int</b>      number of subscriptors of this course</li>
 * <li><var>courseFiles</var>       <b>array</b>    array of CourseFileModel</li>
 * <li><var>courseFilesNumber</var> <b>int</b>      number of uploaded course files in this course</li>
 * </ul>
 * 
 * @package Models/Course
 */
class CourseModel extends BaseModel {
    public $name;
    protected $nameEnglish;
    protected $nameShort;
    
    protected $subscriptors;
    protected $subscriptorsNumber;
    protected $courseFilesNumber;
    
    protected $studyPaths;
    
    protected $forum;
    
    /**
     * @param int $id id of the course
     * @param string $name name of the course
     * @param string $nameShort short name of the course
     * @param string $nameEnglish english name of the course
     */
    public function __construct($id = null, $name = null, $nameShort = null, $nameEnglish = null) {
    	$this->id = $id;
        $this->name = $name;
        $this->nameShort = $nameShort;
        $this->nameEnglish = $nameEnglish;
        
        $this->subscriptors = null;
        
        $this->subsidiesEnabled = null;
        
        $this->studyPaths = array();
    }
	
    
    /**
     * returns a course model of the course specified by id
     * @param int
     * @return CourseModel
     */
    public static function getCourseById($id) {
    	$DB = Database::getHandle();
        
        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.courses
               WHERE id = ' . $DB->Quote($id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // if no course is found, return null
        if ($res->EOF) {
            return null;
        }
        
        $course = new CourseModel($res->fields['id'], $res->fields['name']);
        
        return $course;
    }
    
    /**
     * returns an array of all courses the given user has subscribed to
     * @param UserModel
     * @return array array of CourseModel
     */
    public static function getCoursesByUser($user) {
        // if we have no user id, return empty array
        if (!$user or !$user->id) return array();
        
    	$DB = Database::getHandle();
        
        $q = 'SELECT courses.id, courses.name
                FROM ' . DB_SCHEMA . '.courses,
                     ' . DB_SCHEMA . '.courses_per_student
               WHERE course_id = courses.id
                 AND user_id = ' . $DB->Quote($user->id) . '
            ORDER BY name';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array with selected courses
        $courses = array();
        foreach ($res as $k => $row) {
            array_push($courses, new CourseModel($row['id'], $row['name']));
        }
        
        return $courses;
    }
    
    /**
     * returns an array of all courses
     * @return array array of CourseModel
     */
    public static function getAllCourses() {
        
        $DB = Database::getHandle();
        
        $q = 'SELECT courses.id AS id, courses.name AS name
                FROM ' . DB_SCHEMA . '.courses
            ORDER BY name';
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array with selected courses
        $courses = array();
        foreach ($res as $k => $row) {
            array_push($courses, new CourseModel($row['id'], $row['name']));
        }
        
        return $courses;
    }
	
    /**
     * returns an array of all courses' ids
     * @param array optional, array of CourseModels; if given method will return courses' ids from array instead of retrieving them from data base
     * @return array array of int
     */
    public static function getAllCoursesIds($courses = null) {
		return array_map(create_function('$c', 'return $c->id;'), 
				($courses == null ? self::getAllCourses() : $courses));
    }

    /**
     * returns an array of all courses' names
    * @param array optional, array of CourseModels, if given method will return courses' names from array instead of retrieving them from data base
     * @return array array of string
     */
    public static function getAllCoursesNames($courses = null) {
		return array_map(create_function('$c', 'return $c->getName();'), 
				($courses == null ? self::getAllCourses() : $courses));
    }	
    
    /**
     * returns an array of all courses
     * @return array array of CourseModel
     */
    public static function getCoursesByStudyPathAndUser($studyPathsIds, $user, $semesterMin = 1, $semesterMax = 16) {
        if (count($studyPathsIds) == 0) {
            return array();
        }
        
        $DB = Database::getHandle();
        
        $q = 'SELECT DISTINCT ON(name) c.id AS id, c.name AS name, (SELECT COUNT(*) 
                                                    FROM ' . DB_SCHEMA . '.courses_per_student cs 
                                                   WHERE cs.user_id = ' . $DB->Quote($user->id) . '
                                                     AND cs.course_id = c.id) AS selected
                FROM ' . DB_SCHEMA . '.courses c
           LEFT JOIN ' . DB_SCHEMA . '.courses_per_study_path csp
                  ON c.id=csp.course_id
               WHERE csp.study_path_id IN (' . Database::makeCommaSeparatedString($studyPathsIds) . ')
                 AND csp.semester_max >= ' . $DB->Quote($semesterMin) . '
                 AND csp.semester_min <= ' . $DB->Quote($semesterMax) . '
            ORDER BY name';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        // build up return array with selected courses
        $courses = array();
        foreach ($res as $k => $row) {
            array_push($courses, array(new CourseModel($row['id'], $row['name']), (int)$row['selected'] > 0));
        }
        
        return $courses;
    }
    
    /**
     * returns an array of all courses; courses that user has subscribed to are marked
     * @param UserModel $user courses selected by given user are marked 
     * @return array of array([0] => CourseModel, [1] => boolean (marked?))
     */
    public static function getAllCoursesWithUser($user) {
        $DB = Database::getHandle();
        
        $q = 'SELECT c.id AS id, c.name AS name, cs.user_id AS uid
                FROM ' . DB_SCHEMA . '.courses AS c
           LEFT JOIN ' . DB_SCHEMA . '.courses_per_student AS cs
                  ON cs.course_id=c.id AND cs.user_id=' . $DB->Quote($user->id) . '
            ORDER BY name';
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array with selected courses
        $courses = array();
        foreach ($res as $k => $row) {
            array_push($courses, array(new CourseModel($row['id'], $row['name']), 
                  // subscribed courses have a not null uid column 
                  ($row['uid']!=null) ));
        }
        
        return $courses;
    }
    
    /**
     * return an array of all courses specified by ids
     * <b>note:</b> ids are _not_ escaped
     * @param array array of int, course ids
     * @return array array of CourseModel
     */
    public static function getCoursesByIds($ids) {
        // check, if we have ids to work on
        if (count($ids) == 0) {
            return array();
        }
        
        $DB = Database::getHandle();
        
        $q = 'SELECT courses.id, courses.name
                FROM ' . DB_SCHEMA . '.courses
               WHERE courses.id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array with selected courses
        $courses = array();
        foreach ($res as $k => $row) {
            $courses[$row['id']] = new CourseModel($row['id'], $row['name']);
        }
        
        return $courses;
    }
    
    /**
     * searches for a user whose username contains the given string
     *
     * @param string $subString string that course name must contain
     * @param boolean $showValidOnly if true, only valid courses are returned
     * @return array array of CourseModel, ordered by course name ascending
     * @throws DBException on DB error
     */
    public static function searchCourse($subString, $startWith = false, $showValidOnly = true) {
        $DB = Database::getHandle();
        
        if($startWith){
        	$search = $DB->Quote($subString.'%');
        }else{
            $search = $DB->Quote('%'.$subString.'%');
        }
        
        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.courses
               WHERE name ILIKE ' . $search;
        if ($showValidOnly) {
            /* TODO: condition, if valid flag will have been added */
        }
        $q .= 
          ' ORDER BY name ASC';
        
        $res = $DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $courses = array();
        foreach ($res as $k => $row) {
            $courses[$row['id']] = new CourseModel($row['id'], $row['name']);
        }
        
        return $courses;
    }
    
    public function addStudyPath($studyPath, $semesterMin, $semesterMax) {
    	$this->studyPaths[$studyPath->id] = array($studyPath, $semesterMin, $semesterMax);
    }
    
    protected function loadExtendedData() {
        $DB = Database::getHandle();
        $q = 'SELECT name_english, name_short
                FROM ' . DB_SCHEMA . '.courses
               WHERE id = ' . $DB->Quote($this->id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $this->nameShort = $res->fields['name_short'];
        $this->nameEnglish = $res->fields['name_english'];
    }
    
    protected function loadSubscriptors() {
        $DB = Database::getHandle();
        
        // get subscriptors
        $q = 'SELECT user_id
                FROM ' . DB_SCHEMA . '.courses_per_student
               WHERE course_id=' . $DB->Quote($this->id);
    
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $this->subscriptors = array();
        
        foreach ($res as $k => $row) {
            array_push($this->subscriptors, $row['user_id']);
        }
        
        // retrieve subscriptors, ordered by usernamed
        $this->subscriptors = UserProtectedModel::getUsersByIds($this->subscriptors, 'username');
        
        // can't use count to determine subscriptors' number
        // due to possible invisible users
        $this->subscriptorsNumber = 0;
        foreach ($this->subscriptors as $s) {
            if (!$s->isAnonymous()) {
                ++$this->subscriptorsNumber;
            }
        }
        
    }
    
    protected function loadCourseFileData() {
        $this->courseFilesNumber = CourseFileModel::getCourseFilesNumberByCourse($this);	
    }
    

	protected function loadForum(){
        $DB = Database::getHandle();

        // get subscriptors
        $q = 'SELECT forum_id
                FROM ' . DB_SCHEMA . '.courses_data
               WHERE course_id=' . $DB->Quote($this->id);

        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach($res as $row){
        	$this->forum = ForumModel::getForumById($row['forum_id']);
        	return;
        }
        $this->forum = false;
	}

	public function setName($name){
		$this->name = $name;
	}
	
	public function setNameEnglish($name){
		$this->nameEnglish = $name;
	}
	
	public function setNameShort($name){
		$this->nameShort = $name;
	}
    
    public function getName() {
        return $this->name;
    }
    public function getNameShort() {
        return $this->safeReturn('nameShort', 'loadExtendedData');
    }
    public function getNameShortSafe() {
        // if nameShort exists, use it 
        if (($nameShort = trim($this->getNameShort())) != '') {
            return $nameShort;
        } else {
            return $this->getName();
        }
    }
    
    public function getNameEnglish() {
        return $this->safeReturn('nameEnglish', 'loadExtendedData');
    }
    public function getSubscriptors() {
        return $this->safeReturn('subscriptors', 'loadSubscriptors');
    }
    public function getSubscriptorsNumber() {
        return $this->safeReturn('subscriptorsNumber', 'loadSubscriptors');
    }
    public function getCourseFilesNumber() {
        return $this->safeReturn('courseFilesNumber', 'loadCourseFileData');
    }
    public function getForum() {
        return $this->safeReturn('forum', 'loadForum');
    }
    
    /**
     * adds a subscribent (user) to this course
     * @param UserModel user to add
     */
    public function addSubscribent($user) {
        // avoid double insertion
        if ($this->isSubscribent($user)) {
            return;
        }
        
        $DB = Database::getHandle();
        
        $q = 'INSERT INTO ' . DB_SCHEMA . '.courses_per_student
                        (course_id, user_id)
                    VALUES
                        (' . $DB->Quote($this->id) . ','
                           . $DB->Quote($user->id) . ')';
    
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    /**
     * removes a subscribent (user) from this course
     * @param UserModel user to remove
     */
    public function removeSubscribent($user) {
        $DB = Database::getHandle();
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.courses_per_student
                    WHERE course_id = ' . $DB->Quote($this->id) . '
                      AND user_id = ' . $DB->Quote($user->id);
    
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    /**
     * check if user is subscribed to this course
     * @param UserModel user to check
     * @return boolean
     */
    public function isSubscribent($user) {
        $DB = Database::getHandle();
        
        $q = 'SELECT COUNT(id) AS nr
                FROM ' . DB_SCHEMA . '.courses_per_student
               WHERE course_id = ' . $DB->Quote($this->id) . '
                 AND user_id = ' . $DB->Quote($user->id);
    
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        return $res->fields['nr'] > 0;
    }
    
    
    public function absorb($course) {
        $DB = Database::getHandle();
        
        $DB->StartTrans();
        
        /*
         *        course   <-->   student
         */
        
        $q = 'INSERT INTO ' . DB_SCHEMA . '.courses_per_student
                    (course_id, user_id)
                SELECT ' . $DB->Quote($this->id) . ', user_id
                  FROM ' . DB_SCHEMA . '.courses_per_student cs
                 WHERE course_id = ' . $DB->Quote($course->id) . '
                   AND NOT EXISTS (SELECT id 
                                     FROM ' . DB_SCHEMA . '.courses_per_student cs2
                                    WHERE cs2.user_id = cs.user_id
                                      AND cs2.course_id = ' . $DB->Quote($this->id) . ')';
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.courses_per_student
                    WHERE course_id = ' . $DB->Quote($course->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        /*
         *        course   <-->   study_path
         */
        
        $q = 'INSERT INTO ' . DB_SCHEMA . '.courses_per_study_path
                    (course_id, study_path_id, semester_min, semester_max)
                SELECT ' . $DB->Quote($this->id) . ', study_path_id, semester_min, semester_max
                  FROM ' . DB_SCHEMA . '.courses_per_study_path cs
                 WHERE course_id = ' . $DB->Quote($course->id) . '
                   AND NOT EXISTS (SELECT id 
                                     FROM ' . DB_SCHEMA . '.courses_per_study_path cs2
                                    WHERE cs2.study_path_id = cs.study_path_id
                                      AND cs2.course_id = ' . $DB->Quote($this->id) . ')';
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.courses_per_study_path
                    WHERE course_id = ' . $DB->Quote($course->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $DB->CompleteTrans();
        
    }
    
    public function delete() {
        $DB = Database::getHandle();
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.courses_data
                    WHERE course_id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.courses_per_student
                    WHERE course_id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.courses_per_study_path
                    WHERE course_id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.courses
                    WHERE id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    public function save() {

        $keyValue = array ();

        $DB = Database :: getHandle();

        /* values not in db */
        //$keyValue['last_update_time'] = 'now()';
        //$keyValue['post_ip'] = $DB->quote($this->postIp);

        /** used in all operations */
        $keyValue['name'] = $DB->quote($this->name); 
        $keyValue['name_english'] = $DB->quote($this->nameEnglish); 
        $keyValue['name_short'] = $DB->quote($this->nameShort);        
                
        /** is update? we need the a where clausel */
        if ($this->id != null)
            $q = $this->buildSqlStatement('courses', $keyValue, false, 'id = ' . $DB->quote($this->id));
        else
            $q = $this->buildSqlStatement('courses', $keyValue);
            
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        if (!$this->id) {
            $this->id = Database::getCurrentSequenceId($DB, 'courses','id');
        }
        
        $keyValue = array();
        $keyValue['course_id'] = $this->id;
        
        foreach ($this->studyPaths as $studyPath) {
        	$keyValue['study_path_id'] = $DB->quote($studyPath[0]->id);
            $keyValue['semester_min'] = $DB->quote($studyPath[1]);
            $keyValue['semester_max'] = $DB->quote($studyPath[2]);
            // TODO: handle update
            $q = $this->buildSqlStatement('courses_per_study_path', $keyValue);
            //echo $q;
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
        }

    }
}
?>
