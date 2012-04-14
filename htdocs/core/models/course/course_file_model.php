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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/course/course_file_model.php $

require_once MODEL_DIR . '/base/base_filter.php';
require_once MODEL_DIR . '/base/file_model.php';
require_once MODEL_DIR . '/course/course_model.php';
require_once MODEL_DIR . '/course/course_file_revision_model.php';
require_once MODEL_DIR . '/course/course_file_category_model.php';
require_once MODEL_DIR . '/course/course_file_semester_model.php';
require_once MODEL_DIR . '/course/course_file_rating_category_model.php';
require_once MODEL_DIR . '/course/course_file_rating_model.php';
require_once MODEL_DIR . '/course/course_file_filter.php';

/**
 * @class CourseFileModel
 * @brief model of an (uploaded) course file
 * 
 * @author linap
 * @version $Id: course_file_model.php 5760 2008-03-29 16:45:37Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * The following properties are available from BaseModel
 * <ul>
 * <li><var>id</var>                <b>int</b></li>
 * </ul>
 * 
 * The following properties are available directly from this class:
 * <ul>
 * <li><var>category</var>          <b>CourseFileCategoryModel</b>  upload category</li>
 * <li><var>categoryId</var>        <b>int</b>                      id of upload category</li>
 * <li><var>semester</var>          <b>CourseFileSemesterModel</b>  semester</li>
 * <li><var>semesterId</var>        <b>int</b>                      id of semester</li>
 * <li><var>course</var>            <b>CourseModel</b>              associated course</li>
 * <li><var>author</var>            <b>UserModel</b>                uploading user</li> 
 * <li><var>costs</var>             <b>int</b></li>
 * <li><var>description</var>       <b>string</b>                   author's description of file content</li>
 * <li><var>downloadNumber</var>    <b>int</b>                      number of downloads</li>
 * 
 * <li><var>ratingsMedians</var>    <b>array</b>                    associative array: keys are the rating category names; values are (non-associative) arrays (int $rating, int $numberOfRatings)</li>
 * <li><var>ratings</var>           <b>array</b>                    associative array: keys are the rating category names; values are (associative) arrays ('rating' => string $parsedRating, 'category' => CourseFileRatingCategoryModel $ratingCategory)</li>
 * 
 * <li><var>annotations</var>       <b>string</b>                   comma separated string of annotations
 * </ul>
 * 
 * @package Models/Course
 */
class CourseFileModel extends BaseModel {
    protected $category;
    protected $semester;

    protected $author;
    
    /**
     * @var CourseModel
     * the course this file belongs to 
     */
    protected $course;
       
    protected $costs;
    protected $description;
    
    protected $downloadNumber;
    
    /**
     * @var array
     * associative array
     */
    protected $ratings;
    /**
     * @var array
     * associative array containing median values
     * of file ratings; keys of the array are category names 
     */
    protected $ratingsMedians;   
    
    /**
     * @var string
     */
    protected $annotations;
    
    /**
     * @var boolean
     * true, iff file has been downloaded by a user given by context
     */
    public $downloaded;
    
    /**
     * @var array
     * subsidy criteria
     */
    protected static $subsidies;
    
    /**
     * @var array of FileModel
     */
    protected $revisions;
    
    protected $revisionToAdd;
    
    public function addRevision($rev) {
    	$this->revisionToAdd = $rev;
    } 
    
    /**
     * @var int
     * unix time when the file was uploaded 
     */
    protected $insertAt;
    
    protected $fileName;
    protected $fileSize;
    protected $fileType;
    
    // substring of error message
    const DUPLICATE_FILE = 'duplicate key violates unique constraint "courses_files_revisions_hash_key"';
    
    /**
     * @param int $id id
     * @param string $path absolute path of file
     */
    public function __construct($id = null, $author = null, $course = null) {
        parent::__construct();
        $this->id = $id;
        $this->downloaded = null;
        $this->revisions = null;
        $this->revisionToAdd = null;
        
        $this->author = $author;
        $this->course = $course;
    }
	
    public function buildFromRow($row, $course = null, $overwrite = true) {
    	$this->id = $row['id'];
        if ($overwrite or $this->author == null) {
            $this->author = $row['author_id'];
        }
        if ($overwrite or $this->course == null) {
            $this->course = $course;
        }
        if ($overwrite or $this->semester == null) {
            $this->semester = $row['semester_id'];
        }
        if ($overwrite or $this->category == null) {
            $this->category = $row['category_id'];
        }
        if ($overwrite or $this->costs == null) {
            $this->costs = $row['costs'];
        }
        if ($overwrite or $this->description == null) {
            $this->description = $row['description'];
        }
        if ($overwrite or $this->downloadNumber == null) {
            $this->downloadNumber = $row['download_number'];
        }
        if ($overwrite or $this->insertAt == null) {
            $this->insertAt = $row['unix_time'];
        }
        if ($overwrite or $this->fileName == null) {
            $this->fileName = $row['file_name'];
        }
        if ($overwrite or $this->fileSize == null) {
            $this->fileSize = $row['file_size'];
        }
        if ($overwrite or $this->fileType == null) {
            $this->fileType = $row['file_type'];
        }
        if (($overwrite or $this->downloaded == null) and array_key_exists('downloaded', $row)) {
            $this->downloaded = Database::convertPostgresBoolean($row['downloaded']);
        }
    }

    /**
     * collects all files that belong to the given course
     * various sorting settings can be applied
     * almost identical to 'getCourseFilesByCourse' but allows for multiple courses to search for; 
	 main reason why this method is used is performance since 'SELECT ... FROM ... WHERE course_id IN (.course_ids)...' is probably much fast than traversing all available 
	 courses in php and retrieving the files for each course separately by using 'getCourseFilesByCourse'!
     * @param array $courses array of CourseModels of which the files should be retrieved
     * @param UserModel $user if set only files from the respective user will be retrieved
     * @param BaseFilter $filter object describing the filtering criteria
     * @param int limits the retrieval from the database to the given number of rows; if $limit is less than zero ($limit<0) 'ALL' is assumed and all available rows will be returned
     * @param int offset from where the $limit rows in the result set will be returned (see Postgres documentation for details)
     * @return array array { [0] => numberOfFiles, [1] => 
     *      array of CourseFileModel
     * @throws DBException on DB error 
     */
    public static function getCourseFilesByCourses($courses, $user = null, $filter = null, $limit = 30, $offset = 0) {
    	$DB = Database::getHandle();
        $fileNumber = 0;
		
		//if no courses given, return empty result array
		if (!is_array($courses) || count($courses)==0){
			return array(0, array());
		}
		//create string with id numbers looking like '1,2,3,4,6'
		$courseIDs = Database::makeCommaSeparatedString($courses, 'id');		
        
        if ($filter !== null and !($filter instanceof BaseFilter)) {
            $filter = null;
        }
        
        $q = 'SELECT cf.id, category_id, semester_id, author_id, 
                     file_name, file_size, file_type,
                     costs, description, download_number,
                     extract(EPOCH FROM cf.insert_at) AS unix_time,
					 course_id';
        if ($user != null) {
            $q .=  ', NOT (cfd.file_id IS NULL) AS downloaded';
        }
        $q .= ' FROM ' . DB_SCHEMA . '.courses_files AS cf';
        if ($user != null) {
        	$q .= 
         ' LEFT JOIN ' . DB_SCHEMA . '.courses_files_downloads AS cfd 
                  ON cfd.file_id = cf.id AND cfd.user_id = ' . $DB->Quote($user->id);
        }
        if ($filter != null and $filter->getOption(CourseFileFilter::FILTER_RATING) !== null) {
            $q .= ' ,' . DB_SCHEMA . '.courses_files_ratings_median AS cfr ';
        }
        $q .=' WHERE course_id IN (' . $courseIDs . ')';//<- main difference to 'getCourseFilesByCourse'!!!
        if ($filter != null and $filter->getOption(CourseFileFilter::FILTER_RATING) !== null) {
            $q .= 
               'AND cfr.file_id = cf.id ';
        }
        // apply filter if available
        if ($filter != null) {
            $q .= $filter->getSQLFilterString();
        }else{
        	$q .= ' ORDER BY cf.insert_at desc';
        }        
        $q .= ' LIMIT ' . ($limit >=0 ? $DB->Quote($limit) : ' ALL '). ' OFFSET ' . $DB->Quote($offset);
//echo "Query: ".$q."<br class=\"clear\" />";
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build return array
        $files = array();
        // temporarily store the category ids in array to fetch them later at once
        $tempCategories = array();
        // temporarily store the semester ids in array to fetch them later at once
        $tempSemesters = array();
        // temporarily store the user ids in array to fetch them later at once
        $tempUsers = array();
        // store files in category sub-array
        foreach ($res as $k => $row) {
            $file = new CourseFileModel($row['id']);
            $file->buildFromRow($row, CourseModel::getCourseById($row['course_id']));
            array_push($tempCategories, $row['category_id']);
            array_push($tempSemesters, $row['semester_id']);
            array_push($tempUsers, $row['author_id']);
            // push file model                    
            $files[] = $file;
        }
    
        $categories = CourseFileCategoryModel::getCategoriesByIds($tempCategories);
        $semesters = CourseFileSemesterModel::getSemestersByIds($tempSemesters);
        $users     = UserProtectedModel::getUsersByIds($tempUsers);
        // apply semester and user models to file models
        foreach ($files as $file) {
            $file->category = $categories[$file->category];
            $file->semester = $semesters[$file->semester];
            $file->author   = $users[$file->author];
        }
        
        // determine file count
        $q = 'SELECT COUNT(cf.id) AS nr
                FROM ' . DB_SCHEMA . '.courses_files cf ';
        if ($filter != null and $filter->getOption(CourseFileFilter::FILTER_RATING) !== null) {
            $q .= ' ,' . DB_SCHEMA . '.courses_files_ratings_median AS cfr ';
        }
        $q .='  WHERE course_id IN (' . $courseIDs . ')';//<- main difference to 'getCourseFilesByCourse'!!!
        if ($filter != null and $filter->getOption(CourseFileFilter::FILTER_RATING) !== null) {
            $q .= 
               'AND cfr.file_id = cf.id ';
        }
        // apply filter if available
        if ($filter != null) {
            $q.=$filter->getSQLWhereString();
        }

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $fileNumber = $res->fields['nr'];
        
        return array($fileNumber, $files);
    }

    
    /**
     * collects all files that belong to the given course
     * various sorting settings can be applied
     * @param Course $course the course of which files should be retrieved
     * @param UserModel $user if set only files from the respective user will be retrieved
     * @param BaseFilter $filter object describing the filtering criteria
     * @param int limits the retrieval from the database to the given number of rows; if $limit is less than zero ($limit<0) 'ALL' is assumed and all available rows will be returned
     * @param int offset from where the $limit rows in the result set will be returned (see Postgres documentation for details)
     * @return array array { [0] => numberOfFiles, [1] => 
     *      array of CourseFileModel
     * @throws DBException on DB error 
     */
    public static function getCourseFilesByCourse($course, $user = null, $filter = null, $limit = 30, $offset = 0) {
    	$DB = Database::getHandle();
        $fileNumber = 0;
                   
        if ($filter !== null and !($filter instanceof BaseFilter)) {
            $filter = null;
        }
        
        $q = 'SELECT cf.id, category_id, semester_id, author_id, 
                     file_name, file_size, file_type,
                     costs, description, download_number,
                     extract(EPOCH FROM cf.insert_at) AS unix_time';
        if ($user != null) {
            $q .=  ', NOT (cfd.file_id IS NULL) AS downloaded';
        }
        $q .= ' FROM ' . DB_SCHEMA . '.courses_files AS cf';
        if ($user != null) {
        	$q .= 
         ' LEFT JOIN ' . DB_SCHEMA . '.courses_files_downloads AS cfd 
                  ON cfd.file_id = cf.id AND cfd.user_id = ' . $DB->Quote($user->id);
        }
        if ($filter != null and $filter->getOption(CourseFileFilter::FILTER_RATING) !== null) {
            $q .= ' ,' . DB_SCHEMA . '.courses_files_ratings_median AS cfr ';
        }
        $q .=' WHERE course_id = ' . $DB->Quote($course->id);
        if ($filter != null and $filter->getOption(CourseFileFilter::FILTER_RATING) !== null) {
            $q .= 
               'AND cfr.file_id = cf.id ';
        }
        // apply filter if available
        if ($filter != null) {
            $q .= $filter->getSQLFilterString();
        }else{
        	$q .= ' ORDER BY cf.insert_at desc';
        }        
        $q .= ' LIMIT ' . ($limit >=0 ? $DB->Quote($limit) : ' ALL '). ' OFFSET ' . $DB->Quote($offset);
//echo "Query: ".$q."<br class=\"clear\" />";
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $files = array();
        
        // temporarily store the category ids in array to fetch them later at once
        $tempCategories = array();
        // temporarily store the semester ids in array to fetch them later at once
        $tempSemesters = array();
        // temporarily store the user ids in array to fetch them later at once
        $tempUsers = array();
        // store files in category sub-array
        foreach ($res as $k => $row) {
            $file = new CourseFileModel($row['id']);
            $file->buildFromRow($row, $course);
            array_push($tempCategories, $row['category_id']);
            array_push($tempSemesters, $row['semester_id']);
            array_push($tempUsers, $row['author_id']);
            // push file model                    
            $files[] = $file;
        }
    
        $categories = CourseFileCategoryModel::getCategoriesByIds($tempCategories);
        $semesters = CourseFileSemesterModel::getSemestersByIds($tempSemesters);
        $users     = UserProtectedModel::getUsersByIds($tempUsers);
        // apply semester and user models to file models
        foreach ($files as $file) {
            $file->category = $categories[$file->category];
            $file->semester = $semesters[$file->semester];
            $file->author   = $users[$file->author];
        }
        
        // determine file count
        $q = 'SELECT COUNT(cf.id) AS nr
                FROM ' . DB_SCHEMA . '.courses_files cf ';
        if ($filter != null and $filter->getOption(CourseFileFilter::FILTER_RATING) !== null) {
            $q .= ' ,' . DB_SCHEMA . '.courses_files_ratings_median AS cfr ';
        }
        $q .='  WHERE course_id = ' . $DB->Quote($course->id);
        if ($filter != null and $filter->getOption(CourseFileFilter::FILTER_RATING) !== null) {
            $q .= 
               'AND cfr.file_id = cf.id ';
        }
        // apply filter if available
        if ($filter != null) {
            $q.=$filter->getSQLWhereString();
        }

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $fileNumber = $res->fields['nr'];
        
        return array($fileNumber, $files);
    }
	
    public static function getCourseFilesByFilter($user = null, $filter = null, $limit = 30, $offset = 0) {
        $DB = Database::getHandle();
        $fileNumber = 0;
                   
        if ($filter !== null and !($filter instanceof BaseFilter)) {
            $filter = null;
        }
        
        $q = 'SELECT cf.id, category_id, semester_id, author_id, 
                     file_name, file_size, file_type,
                     costs, description, download_number,
                     extract(EPOCH FROM cf.insert_at) AS unix_time,
                     course_id';
        if ($user != null) {
            $q .=  ', NOT (cfd.file_id IS NULL) AS downloaded';
        }
        $q .= ' FROM ' . DB_SCHEMA . '.courses_files AS cf';
        if ($user != null) {
            $q .= 
         ' LEFT JOIN ' . DB_SCHEMA . '.courses_files_downloads AS cfd 
                  ON cfd.file_id = cf.id AND cfd.user_id = ' . $DB->Quote($user->id);
        }
        if ($filter != null and $filter->getOption(CourseFileFilter::FILTER_RATING) !== null) {
            $q .= ' ,' . DB_SCHEMA . '.courses_files_ratings_median AS cfr ';
        }
        //$q .=' WHERE course_id = ' . $DB->Quote($course->id);
        $q .= 'WHERE true ';
        if ($filter != null and $filter->getOption(CourseFileFilter::FILTER_RATING) !== null) {
            $q .= 
               'AND cfr.file_id = cf.id ';
        }
        // apply filter if available
        if ($filter != null) {
            $q .= $filter->getSQLFilterString();
        }else{
            $q .= ' ORDER BY cf.insert_at desc';
        }        
        $q .= ' LIMIT ' . ($limit >=0 ? $DB->Quote($limit) : ' ALL '). ' OFFSET ' . $DB->Quote($offset);
//echo "Query: ".$q."<br class=\"clear\" />";
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $files = array();
        
        // temporarily store the category ids in array to fetch them later at once
        $tempCategories = array();
        // temporarily store the semester ids in array to fetch them later at once
        $tempSemesters = array();
        // temporarily store the user ids in array to fetch them later at once
        $tempUsers = array();
        // store files in category sub-array
        foreach ($res as $k => $row) {
            $file = new CourseFileModel($row['id']);
            $file->buildFromRow($row, CourseModel::getCourseById($row['course_id']));
            array_push($tempCategories, $row['category_id']);
            array_push($tempSemesters, $row['semester_id']);
            array_push($tempUsers, $row['author_id']);
            // push file model                    
            $files[] = $file;
        }
    
        $categories = CourseFileCategoryModel::getCategoriesByIds($tempCategories);
        $semesters = CourseFileSemesterModel::getSemestersByIds($tempSemesters);
        $users     = UserProtectedModel::getUsersByIds($tempUsers);
        // apply semester and user models to file models
        foreach ($files as $file) {
            $file->category = $categories[$file->category];
            $file->semester = $semesters[$file->semester];
            $file->author   = $users[$file->author];
        }
        
        // determine file count
        $q = 'SELECT COUNT(cf.id) AS nr
                FROM ' . DB_SCHEMA . '.courses_files cf ';
        if ($filter != null and $filter->getOption(CourseFileFilter::FILTER_RATING) !== null) {
            $q .= ' ,' . DB_SCHEMA . '.courses_files_ratings_median AS cfr ';
        }
        //$q .='  WHERE course_id = ' . $DB->Quote($course->id);
        $q .= 'WHERE true ';
        if ($filter != null and $filter->getOption(CourseFileFilter::FILTER_RATING) !== null) {
            $q .= 
               'AND cfr.file_id = cf.id ';
        }
        // apply filter if available
        if ($filter != null) {
            $q.=$filter->getSQLWhereString();
        }

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $fileNumber = $res->fields['nr'];
        
        return array($fileNumber, $files);
    }
    
	public static function getAllCourseFilesIds(){
        $DB = Database::getHandle();
        
        $q = 'SELECT id
                FROM ' . DB_SCHEMA . '.courses_files LIMIT ALL';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // return null, if no file with given id has been found
        if ($res->EOF) return null;
		
		$ids = array();
        foreach ($res as $k => $row) {
            $ids[] = $row['id'];
        }
        return $ids;		
	}
    
    /**
     * returns a model of the course file specified by id
     * @param int $id id
     * @return CourseFileModel
     * @throws DBException on DB error
     */
    public static function getCourseFileById($id) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id
                FROM ' . DB_SCHEMA . '.courses_files
               WHERE id = ' . $DB->Quote($id);

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // return null, if no file with given id has been found
        if ($res->EOF) return null;
        
        $courseFile = new CourseFileModel($res->fields['id']);
        
        return $courseFile;
    }
    
    /**
     * returns models of the latest added course files
     * @param array $courses array of CourseModels the files are selected by (may be null for no filtering)
     * @param int $num number of latest files (defaults to 5)
     * @return array array of CourseFileModel
     * @throws DBException on DB error
     */
    public static function getLatestCourseFiles($courses = null, $num = 5) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, file_name, author_id, course_id,
                     extract(EPOCH FROM insert_at) AS unix_time
                FROM ' . DB_SCHEMA . '.courses_files cf
               WHERE true';
        if (is_array($courses) and count($courses) > 0) {
            $q .= 
               ' AND course_id IN (' . Database::makeCommaSeparatedString($courses, 'id') . ')'; 
        }
        $q .=
          ' ORDER BY insert_at DESC
               LIMIT ' . $DB->Quote($num);

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $files = array();
        
        // temporarily store user and course ids to fetch them later at once
        $tempUsers = array();
        $tempCourses = array();
        foreach ($res as $k => $row) {
        	$file = new CourseFileModel($row['id']);
            $file->author = $row['author_id'];
            $file->course = $row['course_id'];
            $file->insertAt = $row['unix_time'];
            $file->fileName = $row['file_name'];
                
            array_push($tempUsers, $file->author);
            array_push($tempCourses, $file->course);
            
            array_push($files, $file);
        }

        $users = UserProtectedModel::getUsersByIds($tempUsers);
        $courses = CourseModel::getCoursesByIds($tempCourses);
        
        // apply fetched user and course models
        foreach ($files as $f) {
        	if(array_key_exists($f->author, $users)){
                $f->author = $users[$f->author];
            }else{
            	$f->author = new UserAnonymousModel();
            }
            $f->course = $courses[$f->course];
        }
        
        return $files;
    }
    
    /**
     * returns models of course files the given user has uploaded
     * @param UserModel $author author of files
     * @return array array of CourseFileModel
     * @throws DBException on DB error
     */
    public static function getCourseFilesByAuthor($author) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, file_name, course_id,
                     extract(EPOCH FROM insert_at) AS unix_time
                FROM ' . DB_SCHEMA . '.courses_files
               WHERE author_id = ' . $DB->Quote($author->id);

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $files = array();
        
        // temporarily store course ids to fetch them later at once
        $tempCourses = array();
        foreach ($res as $row) {
            $courseFile = new CourseFileModel($row['id']);
            $courseFile->insertAt = $row['unix_time'];
            $courseFile->fileName = $row['file_name'];
            $courseFile->author = $author;
            $courseFile->course = $row['course_id'];
            
            array_push($tempCourses, $courseFile->course);
            array_push($files, $courseFile);
        }
        
        $courses =  CourseModel::getCoursesByIds($tempCourses);
        
        // apply course models
        foreach ($files as $f) {
            $f->course = $courses[$f->course];
        }
        
        return $files;
    }

    /**
     * returns array of all last $limit unrated files
     */    
    public static function getCourseFilesUnrated($user, $limit=20) {
    	$DB = Database::getHandle();
        $q = 'SELECT f.id, file_name, file_size,
                     author_id, course_id,
                     extract(epoch from f.insert_at) AS unix_time
                FROM ' . DB_SCHEMA . '.courses_files_downloads AS d,
                     ' . DB_SCHEMA . '.courses_files AS f
               WHERE f.id=d.file_id
                 AND d.user_id = ' . $DB->Quote($user->id). '
                 AND f.author_id <> ' . $DB->Quote($user->id). '
                 AND NOT d.already_rated
            ORDER BY f.insert_at DESC
               LIMIT ' . $DB->Quote($limit);

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $files = array();
        
        // temporarily store user and course ids to fetch them later at once
        $tempUsers = array();
        $tempCourses = array();
        foreach ($res as $k => $row) {
            $courseFile = new CourseFileModel($row['id']);
            $courseFile->fileName = $row['file_name'];
            $courseFile->fileSize = $row['file_size'];
            $courseFile->insertAt = $row['unix_time'];
            $courseFile->author = $row['author_id'];
            $courseFile->course = $row['course_id'];
            
            array_push($tempUsers, $courseFile->author);
            array_push($tempCourses, $courseFile->course);
            array_push($files, $courseFile);
        }
        
        $users = UserProtectedModel::getUsersByIds($tempUsers);
        $courses =  CourseModel::getCoursesByIds($tempCourses);
        
        // apply fetched user and course models
        foreach ($files as $f) {
            if(!empty($users[$f->author])){
                $f->author = $users[$f->author];
            }else{
            	$f->author = new UserAnonymousModel();
            }
            $f->course = $courses[$f->course];
        }
        
        return $files;
    }
    
    
    /**
     * searches for course files that meet given criteria
     * 
     * @param CourseModel $course course to operate on
     * @param array $categories array of int, ids of categories; can be null
     * @param array $semesters array of int, ids of semesters; can be null 
     * @param UserModel $author author of file; can be null
     * @param int $maxCosts maximal cost; can be null
     * @param array $minRating associative array of float; keys are rating-category-ids
     *        values are lower bounds for average file rating in the related rating-category
     * @param array $annotations array of annotations that sought file must have 
     * @return array array of CourseFileModel
     * @throws DBException on DB error
     */
    public static function searchCourseFiles($course, $categories, $semesters, $author, $maxCosts, 
                $minRating = null, $annotations = null) {
                	
        // eliminate rating categories without bound
        if ($minRating != null) {
            foreach ($minRating as $k => $v) {
            	if ($v == 0) unset($minRating[$k]);
            }
        }

        $DB = Database::getHandle();
        $q = 'SELECT c.id, path, category_id, semester_id, author_id,
                     to_char(upload_time, \'DD.MM.YYYY, HH24:MI\') AS upload_time
                FROM ' . DB_SCHEMA . '.courses_files AS c ';
        // join tables for subcategory ratings
        if ($minRating != null) {
        	for ($m=1; $m<=count($minRating); $m++) {
                $q .= ',' . DB_SCHEMA . '.courses_files_ratings_median AS m' . $m;
            }
        }
        // join tables for costs information
        if ($maxCosts != null) {
            $q .= ',' . DB_SCHEMA . '.courses_files_information AS i';
        }
        // join tables for annotations
        if ($annotations != null) {
        	for ($a=1; $a<=count($annotations); $a++) {
                $q .= ',' . DB_SCHEMA . '.courses_files_annotations AS a' . $a;
            }
        }
                             
        $q .= ' WHERE course_id = ' . $DB->Quote($course->id);
        if ($minRating != null) {
        	for ($m=1; $m<=count($minRating); $m++) {
                $q.=
                ' AND c.id = m' . $m . '.file_id';
            }
        }
        if ($maxCosts != null) {
            $q .= 
                ' AND i.file_id = c.id';
        }
        if ($annotations != null) {
        	for ($a=1; $a<=count($annotations); $a++) {
                $q.=
                ' AND c.id = a' . $a . '.file_id';
            }
        }
        if ($categories != null) {
            $q.=' AND category_id IN (' . Database::makeCommaSeparatedString($categories) . ') ';
        }
        if ($semesters != null) {
            $q.=' AND semester_id IN (' . Database::makeCommaSeparatedString($semesters) . ') ';
        }
        if ($author != null) {
            $q.=' AND author_id = ' . $DB->Quote($author->id) . ' ';
        }
        if ($maxCosts != null) {
            $q.=' AND costs <= ' . $DB->Quote($maxCosts) . ' ';
        }
        if ($minRating != null) {
            $m = 1;
            foreach ($minRating as $catId => $val) {
            	$q .=
                ' AND m' . $m . '.rating_category_id=' . $DB->Quote($catId) . '
                  AND m' . $m . '.rating<=' . $DB->Quote($val) . '
                  AND m' . $m . '.rating>0';
                $m++;
            }
        }
        if ($annotations != null) {
        	$a = 1;
        	foreach ($annotations as $annot) {
        	   $q .=
                ' AND a' . $a . '.annotation=' . $DB->Quote($annot) . ' ';
               $a++;
            }
        }       
         
        //var_dump($q);
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $files = array();
                        
        // temporarily store the category ids in array to fetch them later at once
        $tempCategories = array();
        // temporarily store the semester ids in array to fetch them later at once
        $tempSemesters = array();
        // temporarily store the user ids in array to fetch them later at once
        $tempUsers = array();
        // store files in category sub-array
        foreach ($res as $k => $row) {
            $courseFile = new CourseFileModel($row['id'], $row['path']);
            $courseFile->category = $row['category_id'];
            $courseFile->semester = $row['semester_id'];
            $courseFile->author = $row['author_id'];
            $courseFile->course = $course;
            $courseFile->uploadTime = $row['upload_time'];

            array_push($tempCategories, $row['category_id']);
            array_push($tempSemesters, $row['semester_id']);
            array_push($tempUsers, $row['author_id']);
            
            // push file model
            array_push($files, $courseFile);
        }
        
        $categories = CourseFileCategoryModel::getCategoriesByIds($tempCategories);
        $semesters  = CourseFileSemesterModel::getSemestersByIds($tempSemesters);
        $users      = UserProtectedModel::getUsersByIds($tempUsers);
        // apply category, semester and user models to file models
        foreach ($files as $k => $file) {
            $file->category = $categories[$file->category];
            $file->semester = $semesters[$file->semester];
            $file->author   = $users[$file->author];
        }
        
        return $files;
    }
    
    /**
     * @return int total number of uploaded course files
     * @throws DBException on DB error
     */
    public static function getTotalFileNumber() {
        $DB = Database::getHandle();
        
        $q = 'SELECT COUNT(id) AS nr
                FROM ' . DB_SCHEMA . '.courses_files';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        return $res->fields['nr'];
    }
    
    /**
     * @param CourseModel $course
     * @return int number of uploaded course files for given course
     * @throws DBException on DB error
     */
    public static function getCourseFilesNumberByCourse($course) {
        $DB = Database::getHandle();
        
        $q = 'SELECT COUNT(id) AS nr
                FROM ' . DB_SCHEMA . '.courses_files
               WHERE course_id = ' . $DB->Quote($course->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        return $res->fields['nr'];
    }
    
    protected function loadBasicData() {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, author_id, semester_id, course_id, category_id,
                     extract(epoch from insert_at) AS unix_time,
                     costs, description, download_number,
                     file_name, file_size, file_type
                FROM ' . DB_SCHEMA . '.courses_files
               WHERE id = ' . $DB->Quote($this->id);

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // we only change data entries, if they are null

        $this->buildFromRow($res->fields, null, false);
        
        if ($this->course == null){
            $this->course = CourseModel::getCourseById($res->fields['course_id']);
        }

        if (!($this->author instanceof UserModel)) {
            $this->author = UserProtectedModel::getUserById($res->fields['author_id']);
            if($this->author == null){
                $this->author = new UserAnonymousModel();
            }
        }
    }
    
    /*
    protected function loadAnnotationData() {
        $DB = Database::getHandle();
        
        $q = "SELECT annotation
                FROM " . DB_SCHEMA . ".courses_files_annotations
               WHERE file_id = " . $DB->Quote($this->id);

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $this->annotations = '';
        foreach ($res as $row) {
        	$this->annotations .= $row['annotation'] . ',';
        }
        // remove last comma
        if ($this->annotations != '') {
            $this->annotations = substr($this->annotations,0,-1);
        }
        
    }*/
    
    protected function loadRevisionData() {
        $DB = Database::getHandle();
        $q = 'SELECT id, path, file_size, file_type, hash,
                     EXTRACT(EPOCH FROM upload_time) AS unix_time
                FROM ' . DB_SCHEMA . '.courses_files_revisions
               WHERE file_id = ' . $DB->Quote($this->id) . '
            ORDER BY upload_time DESC';
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $this->revisions = array();
        foreach ($res as $row) {
        	$rev = new CourseFileRevisionModel( $row['id'],
                $row['path'],
                $row['file_size'],
                $row['file_type'],
                $row['hash']
                );
            $rev->uploadTime = $row['unix_time'];
            
            array_push($this->revisions, $rev);
        }
    }
    
    protected function loadRatingMedians() {
        $ratingCatgeories = CourseFileRatingCategoryModel::getAllCategories();
        
        $DB = Database::getHandle();
        $q = "SELECT rating_category_id AS id, rating, rating_number
                FROM " . DB_SCHEMA . ".courses_files_ratings_median
               WHERE file_id = " . $DB->Quote($this->id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // initialize and fill array
        $this->ratingsMedians = array();
        foreach ($res as $k => $row) {
        	 $this->ratingsMedians[$ratingCatgeories[$row['id']]->name] = array($row['rating'], $row['rating_number']);
        }        
    }
    
    protected function loadRatings() {
        $this->ratings = CourseFileRatingModel::getRatingsByFile($this);        
    }
    
    /**
     * get current download subsidy settings
     * 
     * @return array associative array with the keys 
     *  - 'enabled'
     *  - 'maxDownloadNumber'
     *  - 'maxFileNumber'
     *  - 'maxUserNumber'
     *  - 'subsidy'
     * @throws DBException on DB error
     */
    public static function getSubsidies() {
    	if (self::$subsidies === null) {
    		self::$subsidies = array();
	    	self::$subsidies['enabled'] = Database::convertPostgresBoolean(
	    		GlobalSettings::getGlobalSetting('COURSE_SUBSIDIES_ENABLED'));
			//if (self::$subsidies['enabled']) {
				self::$subsidies['maxDownloadNumber'] = GlobalSettings::getGlobalSetting('COURSE_SUBSIDIES_MAX_DOWNLOAD');
				self::$subsidies['maxFileNumber'] = GlobalSettings::getGlobalSetting('COURSE_SUBSIDIES_MAX_FILE');
				self::$subsidies['maxUserNumber'] = GlobalSettings::getGlobalSetting('COURSE_SUBSIDIES_MAX_USER');
				self::$subsidies['subsidy'] = GlobalSettings::getGlobalSetting('COURSE_SUBSIDIES_SUBVENTION');
			//}
    	}
    	return self::$subsidies;
    }
    
    public static function setSubsidies($subsidies) {
    	var_dump($subsidies);
    	die("to be implemented");
    }
    
    /**
     * adds information about this file to database
     */
    public function save() {
        $DB = Database::getHandle();
        
        // start transaction because we could have to insert into a file revision
        $DB->StartTrans();

        // used on insert
        if ($this->id == null) {
            $keyValue['author_id'] = $DB->quote($this->getAuthor()->id);
        }
               
        /** used in all operations */
        $keyValue['course_id'] = $DB->quote($this->getCourseId()); 
        $keyValue['category_id'] = $DB->quote($this->getCategoryId());
        $keyValue['semester_id'] = $DB->quote($this->getSemesterId());
        $keyValue['costs'] = $DB->quote($this->costs);        
        $keyValue['description'] = $DB->quote($this->description);
        
        if ($this->revisionToAdd != null) {
            $keyValue['file_name'] = $DB->quote($this->revisionToAdd->getFileName());
            $keyValue['file_size'] = $DB->quote($this->revisionToAdd->getFileSize());
            $keyValue['file_type'] = $DB->quote($this->revisionToAdd->getType());
        }
                
        /** is update? we need the a where clausel */
        if ($this->id != null) {
            $q = $this->buildSqlStatement('courses_files', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('courses_files', $keyValue);
        }

        $res = &$DB->execute($q);
        if (!$res) {
            $DB->CompleteTrans();
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return;
        }
        
        if ($this->id == null) {
        	$this->id = Database::getCurrentSequenceId($DB, 'courses_files','id');
        }
        
        if ($this->revisionToAdd != null) {
            $this->revisionToAdd->courseFileId = $this->id;
            $this->revisionToAdd->save();
        }
        
        $DB->CompleteTrans();
    }
    
    /**
     * removes this file from this course
     */
    public function deleteFile() {
        $DB = Database::getHandle();
		
        $q = 'DELETE FROM ' . DB_SCHEMA . '.courses_files
                    WHERE id = ' . $DB->Quote($this->id);
    
        $res = &$DB->execute($q);
        if (!$res) {            
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return;
        }
    }
    
    public function getInsertAt() {
        return $this->insertAt;
    }
    public function getCategory() {
        if ($this->category == null) {
            $this->category = $this->safeReturn('category', 'loadBasicData');
        }
        if (!($this->category instanceof CourseFileCategoryModel)) {
            // force reload of model by explicitly setting to NULL
            $this->category = CourseFileCategoryModel::getCategoryById($this->category);;
        }
        return $this->category;
    }
    public function getCategoryId() {
        // it could be that category is given by id rather than by concrete model
        if ($this->category == null or 
            $this->category instanceof CourseFileCategoryModel) { 
            return $this->safeReturn('category', 'loadBasicData')->id;
        } else {
            return (integer)$this->category;
        }
    }
    public function getSemester(){
            if ($this->semester == null) {
                $this->semester = $this->safeReturn('semester', 'loadBasicData');
            }
            if (!($this->semester instanceof CourseFileSemesterModel)) {
                // force reload of model by explicitly setting to NULL
                $this->semester = CourseFileSemesterModel::getSemesterById($this->semester);;
            }
            return $this->semester;
    }

    public function getCourseId() {
        // it could be that category is given by id rather than by concrete model
        if ($this->course == null or 
            $this->course instanceof CourseModel) { 
            return $this->safeReturn('course', 'loadBasicData')->id;
        } else {
            return (integer)$this->course;
        }
    }
  

    public function getRevisions(){
        return $this->safeReturn('revisions', 'loadRevisionData');
    }
    public function getRevisionsNumber(){
        if ($this->id == null) {
            return 0;
        }
        $rev = $this->getRevisions();
        if (!is_array($rev)) {
            return 0;
        }
        return count($rev);
    }
        
        // array of arrays [0] rating average [1] rating number
    public function getRatingsMedians(){ return $this->safeReturn('ratingsMedians', 'loadRatingMedians'); }
    public function getRatingQuickvote() {
        $this->safeReturn('ratingsMedians', 'loadRatingMedians');
        return $this->ratingsMedians[CourseFileRatingCategoryModel::getQuickvoteCategoryName()];
    }
    public function getRatingQuickvoteInt() {
        $qv = $this->getRatingQuickvote();
        return (int) round($qv[0]);
    }
        
    public function getRatings(){ return $this->safeReturn('ratings', 'loadRatings');}

    public function getSemesterId(){
            // it could be that semester is given by id rather than by concrete model
            if ($this->semester == null or 
                $this->semester instanceof CourseFileSemesterModel) { 
                return $this->safeReturn('semester', 'loadBasicData')->id;
            } else {
                return (integer)$this->semester;
            }
    }
    //public function getAnnotations(){ return $this->safeReturn('annotations', 'loadAnnotationData'); }
    
    public function getCourse() {
        return $this->safeReturn('course', 'loadBasicData');
    }
    public function getAuthor() {
        return $this->safeReturn('author', 'loadBasicData');
    }
    public function getCosts() {
        return $this->safeReturn('costs', 'loadBasicData');
    }
    public function getDescription() {
        $desc = $this->safeReturn('description', 'loadBasicData');
        $ps = ParserFactory::createParserFromSettings(array());
        foreach ($ps as $p) {
            $desc = $p->parse($desc);   
        }
        return $desc;
    }
    public function getDownloadNumber() {
        return $this->safeReturn('downloadNumber', 'loadBasicData');
    }
    public function getFileName() {
        return $this->safeReturn('fileName', 'loadBasicData');
    }
    public function getFileSize($kb = false) {
        $size = $this->safeReturn('fileSize', 'loadBasicData');
        if ($kb) {
            return ceil($size / 1024);
        }
        return $size;
    }
    public function getFileType() {
        return $this->safeReturn('fileType', 'loadBasicData');
    }
    
    /**
     * @param string $desc
     */
    public function setDescription($desc) {
        $this->description = $desc;
    }
    
    /**
     * @param int $costs costs of file download
     */
    public function  setCosts($costs) {
        $this->costs = $costs;
    }
    
    /**
     * @param int $cat <b>id</b> of category
     */
    public function setCategory($cat) {
        $this->category = $cat;
    }
    
    /**
     * @param int $sem <b>id</b> of semester
     */
    public function setSemester($sem) {
        $this->semester = $sem;
    }

    /**
     * @param int $course <b>id</b> of course
     */
    public function setCourse($course) {
        $this->course = $course;
    }

    /**
     * set new list of annotations this file is going to have
     * @param string $annotations comma separated list of annotations
     * @throws DBException on DB error
     */
    /*public function updateAnnotations($annotations) {
    	// create array of old annotations
        $oldAnnotations = split(',', $this->safeReturn('annotations', 'loadAnnotationData'));
        // create array of new annotations
        $newAnnotations = split(',', $annotations);
        
        // determine changes
        $annotationsToAdd = array();
        $annotationsToKeep = array();
        foreach ($newAnnotations as &$annot) {
        	$annot = strtolower(trim($annot));
            $found = false;
            if (!in_array($annot, $oldAnnotations)) {
            	array_push($annotationsToAdd, $annot);
            } else {
            	array_push($annotationsToKeep, $annot);
            }
        }
        $annotationsToDelete = array_diff($oldAnnotations, $annotationsToKeep);
        //var_dump($annotationsToAdd);
        //var_dump($annotationsToKeep);
        //var_dump($annotationsToDelete);        
        
        $DB = Database::getHandle();
        $DB->StartTrans();
        // add new annotations
        foreach ($annotationsToAdd as $annot) {
            $q = 'INSERT INTO ' . DB_SCHEMA . '.courses_files_annotations
                        (file_id, annotation)
                    VALUES
                        (' . $DB->Quote($this->id) . ',
                         ' . $DB->Quote($annot) . ')';
            $res = &$DB->execute($q);
            if (!$res) {
                $DB->FailTrans();
                $DB->CompleteTrans();
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
                return;
            }
        }
        // delete old annotations
        foreach ($annotationsToDelete as $annot) {
            $q = 'DELETE FROM ' . DB_SCHEMA . '.courses_files_annotations
                        WHERE file_id=' . $DB->Quote($this->id) . '
                          AND annotation =' . $DB->Quote($annot);
            $res = &$DB->execute($q);
            if (!$res) {
                $DB->FailTrans();
                $DB->CompleteTrans();
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
                return;
            }
        }
        
        // finish transaction, if all changes are done
        $DB->CompleteTrans();
    }*/
    
    /**
     * check, if user has already downloaded this file
     * @param UserModel $user
     * @return boolean
     * @throws DBException on DB error
     */
    public function hasAlreadyDownloaded($user) {
        // if user has no id, he cannot have downloaded
        if (!$user->id) {
        	return false;
        }
        
        $DB = Database::getHandle();

        // check, if file has been downloaded
        $q = 'SELECT COUNT(id) AS nr
                FROM ' . DB_SCHEMA . '.courses_files_downloads
               WHERE file_id=' . $DB->Quote($this->id) . '
                 AND user_id=' . $DB->Quote($user->id);

        $res = &$DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        return $res->fields['nr']==1;
    }
    
    /**
     * check, if user has already rated this file
     * @param UserModel $user
     * @return boolean
     * @throws DBException on DB error
     */
    public function hasAlreadyRated($user) {
        // if user has no id, he cannot have rated
        if (!$user->id) {
            return false;
        }
        
        $DB = Database::getHandle();

        // check, if file has been downloaded
        $q = 'SELECT COUNT(id) AS nr
                FROM ' . DB_SCHEMA . '.courses_files_ratings
               WHERE file_id=' . $DB->Quote($this->id) . '
                 AND user_id=' . $DB->Quote($user->id);

        $res = &$DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        return $res->fields['nr']==1;
    }
    
    /**
     * handles all neccessary DB operations for registering a file download
     * @param UserModel $user downloading user
     * @throws DBException on DB error
     */
    public function registerDownload($user) {        
        $DB = Database::getHandle();
        
        // insert download information
        $q = 'INSERT INTO ' . DB_SCHEMA . '.courses_files_downloads
                    (file_id, user_id)
                VALUES
                    (' . $DB->Quote($this->id) . ',
                     ' . $DB->Quote($user->id) . ')';

        $res = &$DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    /**
     * replaces authorship of author by newAuthor
     * @param UserModel
     * @param UserModel
     * 
     * @note the id param is not working now; it could be extended to restrict the replacement to entries given by $id-param
     */
    public static function replaceAuthor($author, $newAuthor, $id = null) {
        // if author is not valid
        if ($author->id == 0 or $newAuthor->id == 0) {
            return false;
        }

        $DB = Database::getHandle();
       
        // replace author
        $q =  'UPDATE ' . DB_SCHEMA . '.courses_files
                  SET author_id = ' . $DB->Quote($newAuthor->id) . '
                WHERE author_id = ' . $DB->Quote($author->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        return true;
    }
    
    /**
     * replaces course by newCourse
     * @param CourseModel
     * @param CourseModel
     */
    public static function replaceCourse($course, $newCourse) {
        // if course is not valid
        if ($course->id == 0 or $newCourse->id == 0) {
            return false;
        }

        $DB = Database::getHandle();
       
        // replace author
        $q =  'UPDATE ' . DB_SCHEMA . '.courses_files
                  SET course_id = ' . $DB->Quote($newCourse->id) . '
                WHERE course_id = ' . $DB->Quote($course->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        return true;
    }
}

?>
