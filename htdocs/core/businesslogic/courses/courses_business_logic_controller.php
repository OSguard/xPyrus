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

require_once CORE_DIR . '/businesslogic/business_logic_controller.php';
require_once CORE_DIR . '/utils/attachment_handler.php';
require_once CORE_DIR . '/utils/input_validator.php';
require_once CORE_DIR . '/constants/value_constants.php';

require_once MODEL_DIR . '/course/course_file_model_proxy.php';

require_once MODEL_DIR . '/forum/forum_read.php'; 

require_once MODEL_DIR . '/base/point_source_model.php';

// $Id: courses_business_logic_controller.php 6210 2008-07-25 17:29:44Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/businesslogic/courses/courses_business_logic_controller.php $

define('COURSE_TEMPLATE_DIR', 'modules/course/');

/**
 * @module Controller
 */
class CoursesBusinessLogicController extends BusinessLogicController {
    /**
     * @var CourseModel
     * central course of page, if needed
     */
    protected $course;
    /**
     * @var CourseFileModel
     * central course file of page, if needed
     */
    protected $courseFile;
        
    /**
     * @var int
     * the size in bytes an uploaded course file may have at most
     */
    protected $maxCourseFileSize;
    
    protected $errors;
    
    public function __construct($ajaxView = false) {
        parent::__construct($ajaxView);
        $this->course = null;
        $this->courseFile = null;
        
        $this->errors = array();
    }
    
    public function process() {
        parent::process();
    }
    
    /**
     * set methods that are allowed for calling by the controller
     */
    protected function getAllowedMethods() {
        // allow viewing and some data manipulation
        return array_merge(parent::getAllowedMethods(),
                array (
                    'studyHome',
                    'viewCourse',
                    'viewCourseFiles',
                    'viewUserCourseFiles',
                    'viewLatestCourseFiles',

                    'adminUnivisImport',
                    'adminSubsidies',
                    
                    'rateCourseFile',
                    'addCourseFile',
                    'downloadCourseFile',
                    'viewCourseFile',
                    'addCourseFileRating',
                    
                    'courseFileFeed',
                    
                    'ajaxShowRating'
                ));
    }
    
    protected function getDefaultMethod() {
        //return 'viewCourse';
        return 'studyHome';
    }
    
    public function getMethodObject($method) {
        $parameters = $this->getParameters($method);
        if ('studyHome' == $method) {
            return new BLCMethod(NAME_STUDIES,
                rewrite_course(array()),
                BLCMethod::getDefaultMethod());
        } else if ('viewCourse' == $method) {
            return new BLCMethod(NAME_COURSE . ': ' . $parameters['course']->getName(),
                rewrite_course(array('course' => $parameters['course'])),
                $this->getMethodObject('studyHome'));
        } else if ('viewLatestCourseFiles' == $method){
        	return new BLCMethod(NAME_COURSE_FILE_LATEST,
                rewrite_course(array('courseFileLatest' => true)),
                $this->getMethodObject('studyHome'));

        } else if ('viewCourseFiles' == $method or
                   'addCourseFile' == $method
                  ) {
            // we can reuse the parameters here for 'viewCourse'-method object
            $this->copyParameters($method, 'viewCourse');
            return new BLCMethod(NAME_COURSE_FILES,
                rewrite_course(array('course' => $parameters['course'], 'showFiles' => true)),
                $this->getMethodObject('viewCourse'),
                '/index.php?mod=courses&method=courseFileFeed&courseId='.$parameters['course']->id, NAME_COURSE_FILE_LATEST_RSS);
        } else if ('viewCourseFile' == $method) {
            // we can reuse the parameters here for 'viewCourseFiles'-method object
            $this->copyParameters($method, 'viewCourseFiles');
            return new BLCMethod($parameters['file']->getFileName(),
                rewrite_course(array('courseFile' => $parameters['file'])),
                $this->getMethodObject('viewCourseFiles'));
            
        } else if ('rateCourseFile' == $method or
                   'addCourseFileRating' == $method) {
            // we can reuse the parameters here for 'viewCourseFiles'-method object
            $this->copyParameters($method, 'viewCourseFiles');
            return new BLCMethod($parameters['file']->getFileName(),
                rewrite_course(array('rateFile' => $parameters['file'])),
                $this->getMethodObject('viewCourseFiles'));
        }else if ('viewUserCourseFiles' == $method) {
            
            $parentMethod = ControllerFactory::createControllerByName('userinfo')->getMethodObject('showUserInfo');
            return new BLCMethod('Unterlagen von '.$parameters['user']->getUsername(),
                rewrite_course(array('couseUserFiles'=>$parameters['user'])),
                $parentMethod);
            
        }
        
        return parent::getMethodObject($method);
    }
    
    protected function collectParameters($method) {
        $parameters = array();
        
        if ('viewCourse' == $method) {
            $parameters['course'] = CourseModel::getCourseById(InputValidator::getRequestData('courseId', 0));
        } else if ('viewCourseFiles' == $method or
                   'addCourseFile' == $method
                  ) {
            $parameters['course'] = CourseModel::getCourseById(InputValidator::getRequestData('courseId', 0));
        } else if ('viewCourseFile' == $method) {
            $file = CourseFileModel::getCourseFileById(InputValidator::getRequestData('fileId'));
            $parameters['file'] = $file;
            $parameters['course'] = ($file != null) ? $file->getCourse() : null;
            // we want a "true" boolean here ;)
            $parameters['edit'] = (InputValidator::getRequestData('edit', false) != false);
        } else if ('rateCourseFile' == $method or
                   'addCourseFileRating' == $method) {
            $file = CourseFileModel::getCourseFileById(InputValidator::getRequestData('id'));
            $parameters['file'] = $file;
            $parameters['course'] = ($file != null) ? $file->getCourse() : null;
        } else if('viewUserCourseFiles' == $method){
        	$user = UserProtectedModel::getUserByUsername(InputValidator::getRequestData('username'));
            $parameters['user'] = $user;
        }
        
        $this->_parameters[$method] = $parameters;

        parent::collectParameters($method);
    }
    
    protected function studyHome(){
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'modules/course/search_study.tpl');
        /* get the current user */
        $cUser = Session::getInstance()->getVisitor();
         
          /*  if (TSEARCH2_AVAILABLE != 'true') {
                throw new CoreException('tsearch not available');
                return;
            }*/
         
        // by default empty query string
        $query = '';
         
         if(array_key_exists('course_name',$_REQUEST)){
         	$startCourse = CourseModel::searchCourse($_REQUEST['course_name'], true, true);
            $main->assign('startCourse',$startCourse);
         }
         
         /*
          * copy & paste from ForumBLC::searchThreadEntries()
          */
        if (array_key_exists(F_SEARCH_SUBMIT, $_REQUEST) && !empty($_REQUEST[F_SEARCH_QUERY])) {
            /*
             * search in forum
             */
            $threads = array();
            if (TSEARCH2_AVAILABLE == 'true') {
                $threads = ThreadEntryModel::getThreadEntryByFulltext(
                        ParserFactory::getRawParser()->parseQuery($_REQUEST[F_SEARCH_QUERY]),
                        15, 'course', 
                        $cUser->isLoggedIn(), $cUser->getGroupMembership());
            } else {
                // nothing to do here
                // for development purposes only
                /*
                $bla = ThreadModel::getAllCourseThreadsByUser(5, 0 ,'desc', false, $cUser, true);
                $threads = array();
                foreach($bla as $blubb){
                    $threads[] = $blubb->getFirstEntry();
                }
                */
            }
            
            $main->assign('threadEntries', $threads);
            $query = $_REQUEST[F_SEARCH_QUERY];
            
            /*
             * search CourseFiles
             */
            $filterSettings = array();
            $filterSettings[CourseFileFilter::FILTER_SEARCH] = $_REQUEST[F_SEARCH_QUERY];
            $fileFilter = new CourseFileFilter($filterSettings);
            $files = CourseFileModel::getCourseFilesByFilter($cUser,$fileFilter);
            $main->assign('courseFiles',$files[1]);
            
            /*
             * search cours
             */
             $findCourse = CourseModel::searchCourse($_REQUEST[F_SEARCH_QUERY]);
             $main->assign('findCourse',$findCourse);
        }else{
        	$main->assign('courseThreads', ThreadModel::getAllCourseThreadsByUser(V_COURSE_HOME_THREADS_PER_PAGE, 0 ,'desc', false, $cUser, true));
            $courseFiles = CourseFileModel::getLatestCourseFiles($cUser->getCourses(), V_COURSE_HOME_FILES_PER_PAGE);
            $main->assign('courseFiles',$courseFiles);
            
            /*self::observeIPC(
                new GlobalIPC, 
                array('FORUM_THREAD_CHANGED'),
                $main, 'forum|latest');*/
            $forumRead = ForumRead::getInstance(Session::getInstance()->getVisitor(), true);    
        }
        $main->assign('query', $query);
         
         
         $this->setCentralView($main);
         $this->view();
    }
    
    protected function viewLatestCourseFiles(){
    	   
         $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), COURSE_TEMPLATE_DIR . 'course_file_latest.tpl');
         /* get the current user */
         $cUser = Session::getInstance()->getVisitor();  
        
         $courseFiles = CourseFileModel::getLatestCourseFiles($cUser->getCourses(), 20);
         $main->assign('courseFiles',$courseFiles);
         
         $this->setCentralView($main);
         $this->view();
    }
    
    protected function viewCourse(){
    	    
    	$main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), COURSE_TEMPLATE_DIR . 'course.tpl');
        /* get the current user */
        $cUser = Session::getInstance()->getVisitor();

        $parameters = $this->getParameters('viewCourse');
        $this->course = $parameters['course'];
        
        // check, if course is given
        if ($this->course == null) {
            $this->errorView(ERR_NO_COURSE);
            return;
        }
        
        $main->assign('course', $this->course);
        $main->assign('threads', ThreadModel::getAllThreadsByForumId($this->course->getForum()->id, 10, 0,'desc'));

        $read = ForumRead::getInstance($cUser);
        $main->assign('forumRead', $read);
        
        $temp = CourseFileModel::getCourseFilesByCourse($this->course, $cUser, new CourseFileFilter(array()), 5, 0);        
        $main->assign('coursefiles_files', $temp[1]);
        
        $main->setCacheParameter(900, 'course|'.$this->course->id);
        $this->setCentralView($main);
        $this->view();
    }
    
    protected function viewCourseFiles() {
        $visitor = Session::getInstance()->getVisitor();
        
        $parameters = $this->getParameters('viewCourseFiles');
        $this->course = $parameters['course'];
        
        // if we have no course file, something went wrong
        if ($this->course == null) {
            $this->errorView(ERR_NO_COURSE);
            return;
        }
        
        $this->maxCourseFileSize = GlobalSettings::getGlobalSetting('COURSE_MAX_FILE_SIZE') * 1024;
        
        $file = null;
        if (InputValidator::getRequestData('upload_submit')) {
            $file = $this->addCourseFile();
        }
        
        /**********************
         * check for filters
         **********************/
        $fileFilter = null;
        $filterSettings = array();
        if (!empty($_POST['file_filter_reset'])) {
        	Session::getInstance()->deleteUserData('course_file_filter');
        } else if (!empty($_POST['file_filter'])) {
            $categories = CourseFileCategoryModel::getAllCategories();
            $semesters = CourseFileSemesterModel::getAllSemesters();
            if (!empty($_POST['category_filter']) and 
                    array_key_exists($_POST['category_filter'], $categories)) {
        		$filterSettings[CourseFileFilter::FILTER_CATEGORY] = $categories[$_POST['category_filter']];
        	}
            if (!empty($_POST['semester_filter']) and 
                    array_key_exists($_POST['semester_filter'], $semesters)) {
                $filterSettings[CourseFileFilter::FILTER_SEMESTER] = $semesters[$_POST['semester_filter']];
            }  
            if (!empty($_POST['rating_filter'])) {
                $rating = $_POST['rating_filter'];
                if (!preg_match('/\d+(\.\d+)?/', $rating)) {
                    $rating = 0;
                }
                $filterSettings[CourseFileFilter::FILTER_RATING] = $rating;
            }
            if (!empty($_POST['order'])) {
                $filterSettings[CourseFileFilter::FILTER_ORDER] = $_POST['order'];
           
                if ($_POST['order'] != '0' and !empty($_POST['orderDir'])) {
                    $filterSettings[CourseFileFilter::FILTER_ORDERDIR] = $_POST['orderDir'];
                }
            }
        }
        
        // check session for filter options
        if (!$filterSettings and ($tmp = Session::getInstance()->getUserData('course_file_filter'))) {
            // if filter setting apply for this site, take them
            if ($tmp['for_course'] == $this->course->id) {
                $filterSettings = $tmp;
            } 
            // otherwise discard them
            else {
                Session::getInstance()->deleteUserData('course_file_filter');
            }
        } else if (count($filterSettings) > 0) {
            Session::getInstance()->storeUserData('course_file_filter', array_merge(array('for_course' => $this->course->id),$filterSettings));
        }
        
        if (count($filterSettings) > 0) {
            $fileFilter = new CourseFileFilter($filterSettings);
        }
        
        // sub-pages
        $page = null;
        if (!empty($_REQUEST['page'])) {
            $page = $_REQUEST['page'];
        }
        
        $this->viewMain($file, $fileFilter, $page);
    }
    
    protected function viewUserCourseFiles(){
    	
        $parameters = $this->getParameters('viewUserCourseFiles');
        
        $user = $parameters['user'];
        
        if($user == null){
        	$this->errorView(ERR_NO_USER);
        }
        
        $cursesFiles = CourseFileModel::getCourseFilesByAuthor($user);
        //var_dump($cursesFiles);
        
        // CACHEME
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), COURSE_TEMPLATE_DIR . 'course_user_files.tpl');
        $main->setCacheParameter(900, 'course_user_files|'.$user->getUsername());
        
        $main->assign('coursefiles', $cursesFiles);
        $main->assign('user', $user);
        
        $this->setCentralView($main);
        //var_dump($this);
        parent::view();
        
    }
    
    
    protected function adminUnivisImport() {
        require_once CORE_DIR . '/utils/uni_data_handler_magdeburg.php';
        require_once MODEL_DIR . '/course/course_model.php';
        
	    $cUser = Session::getInstance()->getVisitor();
        
        if(!$cUser->hasRight('COURSE_ADMIN')){
            $this->rightsMissingView('COURSE_ADMIN');
        }
	
	    $this->addLog('import form Univis');
	
        //define ('UNIVIS_DIR', BASE . '/testfiles/univis/');
        define ('UNIVIS_DIR', '/tmp/');
        $xmlFiles = scandir(UNIVIS_DIR);
        $xmlFiles = scandir('/tmp');
        $univisFiles = array();
        foreach ($xmlFiles as $xml) {
            if (preg_match('#\.xml$#',$xml)) {
                array_push($univisFiles, $xml);
            }
        }
        $lectures = array();
        foreach ($univisFiles as $f) {
            $univis = new UniDataReader_Magdeburg(UNIVIS_DIR . $f);
            $lectures = array_merge($lectures, $univis->getLectures());
        }
        // remove existing courses
        foreach (CourseModel::getAllCourses() as $c) {
            $key = UniDataReader_Magdeburg::getCourseKey($c);
            if (array_key_exists($key, $lectures)) {
                unset($lectures[$key]);
            }
        }
        
        if (!InputValidator::getRequestData('save', false)) {
            $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), COURSE_TEMPLATE_DIR . 'course_import.tpl');
            $main->assign('courses', $lectures);
            
            $this->setCentralView($main);
            $this->view();
        } else {
            $DB = Database::getHandle();
            $DB->StartTrans();

            try {
                foreach ($lectures as $key => $l) {
                    // ignore already existing or not selected courses
                    if ($l->id or !InputValidator::getRequestData('course-' . $key, false)) {
                        continue;
                    }
                    
                    //echo "try " . $l->name . " <br />";
                    $l->save();
                }
            } catch (DBException $e) {
                print "failed";
                $DB->CompleteTrans();
                return;
            }
            
            print "courses successfully imported";
            $DB->CompleteTrans();
        }
    }
    
    protected function adminSubsidies() {
        
	   $cUser = Session::getInstance()->getVisitor();
        
       if(!$cUser->hasRight('COURSE_ADMIN')){
            $this->rightsMissingView('COURSE_ADMIN');
       }
	
	   $this->addLog('edit Subsidies for Course');
	
	   if (!empty($_POST['subsidy_submit'])) {
            // TODO: input validation
            CourseFileModel::setSubsidies(
                array('enabled' => (array_key_exists('subvention_active',$_REQUEST)) ? true : false,
                      'maxDownloadNumber' => $_REQUEST['dlnumber'],
                      'maxFileNumber' => $_REQUEST['fnumber'],
                      'maxUserNumber' => $_REQUEST['unumber'],
                      'subsidies' => $_REQUEST['subvention'])
            );
        }
        
        $this->subsidiesView();
    }
    
    protected function addCourseFile() {
	   // TODO: move check to calling method
	    // check, if user's rights suffice for this action
        if (!Session::getInstance()->getVisitor()->hasRight('COURSES_FILE_UPLOAD')) {
        	$this->rightsMissingView('COURSES_FILE_UPLOAD');
        }
        
        $cUser = Session::getInstance()->getVisitor();
        
        // TODO: change from class property to method parameter?
        $this->maxCourseFileSize = GlobalSettings::getGlobalSetting('COURSE_MAX_FILE_SIZE') * 1024;
        
        $file = null;
        if (!empty($_REQUEST['file_id'])) {
        	$file = CourseFileModel::getCourseFileById($_REQUEST['file_id']);
        } else {
        	$file = new CourseFileModel(null, $cUser, $this->course);
        }
        $fileBasicOld = null;
        $fileBasic = null;
        // first try to aquire file from POST        
        // if file is not in POST, look in session
        // if not in session, fail
        $emptyFile = empty($_FILES['course_file']['size']);
        // simulate short circuit "or" here between the two clauses
        // the attachment must not be handled if $_FILES contains no valid file 
        $tooLargeFile = !$emptyFile && !($fileBasic = AttachmentHandler::handleAttachment($_FILES['course_file'],
                    AttachmentHandler::getAdjointGeneralPath($cUser), true, $this->maxCourseFileSize, true));
        if (($emptyFile or $tooLargeFile) and $file->getRevisionsNumber() == 0 and
                !($fileBasicOld = Session::getInstance()->getUserData('course_file'))) {
             self::addIncompleteField($this->errors, 'course_file');
             if ($emptyFile) {
             	$this->errors['noFile'] = ERR_COURSE_NO_FILE;
             } else if ($tooLargeFile) {
                $this->errors['largeFile'] = ERR_COURSE_LARGE_FILE;
             }
        }

        if (!array_key_exists('category', $_POST) or 
                !array_key_exists($_POST['category'], CourseFileCategoryModel::getAllCategories())) {
        	self::addIncompleteField($this->errors, 'category');
        }
        if (!array_key_exists('semester', $_POST) or 
                !array_key_exists($_POST['semester'], CourseFileSemesterModel::getAllSemesters())) {
            self::addIncompleteField($this->errors, 'semester');
        }
        if (!array_key_exists('costs', $_POST) or 
                $_POST['costs'] < 1 or $_POST['costs'] > 10) {
            self::addIncompleteField($this->errors, 'costs');
        }

        // set the given properties
        $file->setCategory($_POST['category']);
        $file->setSemester($_POST['semester']);
        $file->setDescription($_POST['description']);
        $file->setCosts($_POST['costs']);

        if ($fileBasic) {
        	// cast FileModel to CourseFileRevisionModel
            $rev = CourseFileRevisionModel::createFromFileModel($fileBasic, $cUser, $this->course);
            $file->addRevision($rev);
        }
        
        $somethingFailed = true;
        
        if (count($this->errors) == 0) {           
            // need DB object here, because upload may fail due to duplicate
            // key constraint and we want to catch this exception and rollback
            // transaction
            $DB = Database::getHandle();
            $DB->StartTrans();
            
            try {    
                $file->save();
                // delete session copy of file
                Session::getInstance()->deleteUserData('course_file');
                // refresh course file
                $this->courseFile = $file;
                // $file is no longer needed 
                $file = null;     
                
                $somethingFailed = false;           
            } catch (DBException $e) {
                // check for violation of unique constraint
                if (strpos($e->getMessage(),CourseFileModel::DUPLICATE_FILE)!==false) {
                    $this->errors['duplicateFile'] = ERR_COURSE_DUPLICATE_FILE;
                    // file is not useable
                    $file = null;
                } else {
                	// re-throw exception
                    throw $e;
                }
            }
            // force completion of transaction
            // assuming, we are not inside of another
            // transaction
            $oldErr = error_reporting(0);
            while ($DB->CompleteTrans()) {
                ;
            }
            error_reporting($oldErr);
        } 
        
        // TODO: store values in session, even if ERR_COURSE_DUPLICATE_FILE occurs
        //       that doesn't work at the moment
        if ($somethingFailed) {
            // mark file field as failed, if we have no revision or a file was tried to be uploaded
            if (($file != null and $file->getRevisionsNumber() == 0) or $_FILES['course_file']['size']) {
                self::addIncompleteField($this->errors, 'course_file');
            }
            // keep file in session
            if ($fileBasicOld and !$fileBasic) {
            	$fileBasic = $fileBasicOld;
            }
            Session::getInstance()->storeUserData('course_file', $fileBasic);
        }
        
        // TODO: find a better way than instanciating view object here
        $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), COURSE_TEMPLATE_DIR . 'course_files.tpl');
        $view->clearCache(self::getCourseFilesCachekey($this->course));
        
        return $file;
    }
    
    protected function downloadCourseFile() {
	
	    $cUser = Session::getInstance()->getVisitor();
        // check, if user's rights suffice
        if (!$cUser->hasRight('COURSES_FILE_DOWNLOAD')) {
        	$this->rightsMissingView('COURSES_FILE_DOWNLOAD');
        }
        // check, if id a valid file is specified
        if (empty($_REQUEST['fileId']) or null == ($file = CourseFileRevisionModel::getRevisionById($_REQUEST['fileId']))) {
            $this->errorView(ERR_NO_FILE);
        }
        $courseFile = CourseFileModel::getCourseFileById($file->courseFileId); 
        // if user has already downloaded the file,
        // a new download costs him nothing
        $effectiveCosts = 0;
        
        $hasDownloaded = $courseFile->hasAlreadyDownloaded($cUser);
        if (!$hasDownloaded and !$cUser->equals($courseFile->getAuthor())) {
            $ps = PointSourceModel::getPointSourceByName('COURSE_FILE_BOUGHT');
            $subsidies = CourseFileModel::getSubsidies();
            
            // calculate effective costs for user
            // with respect to subsidisation
            $effectiveCosts = $courseFile->getCosts();
            
            $costSubsidies = 1;
            
            if ($subsidies['enabled']) {
                if ($courseFile->getCourse()->getSubscriptorsNumber() < $subsidies['maxUserNumber'] and
                    $courseFile->getCourse()->getCourseFilesNumber() < $subsidies['maxFileNumber'] and
                    $courseFile->getDownloadNumber() < $subsidies['maxDownloadNumber']) {
                    $costSubsidies = $subsidies['subsidy'];
                }
            }
            
            // check, that user's point suffice
            // check negative costs here, because COURSE_FILE_BOUGHT is seen
            // from file author's perspective and he gets positive points/negative costs
            if (!$cUser->hasEnoughPoints('COURSE_FILE_BOUGHT', -$courseFile->getCosts())) {
                // TODO: perhaps construct a complete template for this case?
                $this->errorView(ERR_COURSE_FILE_TOO_EXPENSIVE);
            }
        
            /***********************
             * START TRANSACTION
             ***********************/
            $DB = Database::getHandle();
            $DB->StartTrans();

            // this methods marks that user has downloaded that file
            $courseFile->registerDownload($cUser);
            // increase points for author
            $author = $courseFile->getAuthor();
            
            if ($author != null and $author->isRegularLocalUser()) {
                // author gets the real costs
                $author->increaseUnihelpPoints(($courseFile->getCosts() * $costSubsidies) * $ps->getPointsSum(), 
                                               ($courseFile->getCosts() * $costSubsidies) * $ps->getPointsFlow());
                $author->save();
            }
                        
            // download user pays only effective costs
            $cUser->increaseUnihelpPoints(0, 
                                          -$courseFile->getCosts() * $ps->getPointsFlow());
            $cUser->save();
            $DB->CompleteTrans();
            
            self::notifyIPC(new UserIPC($author->id), 'POINTS_CHANGED');
            self::notifyIPC(new UserIPC($cUser->id), 'POINTS_CHANGED');
            
            /***********************
             * FINISH TRANSACTION
             ***********************/
        } else if (!$hasDownloaded) {
        	// just mark download, if author of file is downloader
            $courseFile->registerDownload($cUser);
        }
        

        // output via stream
        $this->fileView($file);
        //print_r('you have successfully downloaded this file and paid ' . $effectiveCosts . ' for download!');
    }
    
    protected function rateCourseFile() {
        // check, if file id is given
        $parameters = $this->getParameters('rateCourseFile');
        $this->courseFile = $parameters['file'];
        if ($this->courseFile == null){
            $this->errorView(ERR_NO_FILE);
        }
        $mayRate = true;
        // check, if user has already downloaded this file and may therefore rate it
        if (!$this->courseFile->hasAlreadyDownloaded(Session::getInstance()->getVisitor())) {
            $mayRate = false;
        }
        // check, if user has already rated this file
        if ($this->courseFile->hasAlreadyRated(Session::getInstance()->getVisitor())) {
            $mayRate = false;
        }
        // check if visitor is author of the file
        if ($this->courseFile->getAuthor()->equals(Session::getInstance()->getVisitor())) {
            $mayRate = false;
        }
        
        $this->ratingsViewAdd($mayRate);
    }
    
    protected function viewCourseFile() {
        $parameters = $this->getParameters('viewCourseFile');
        $this->courseFile = $parameters['file'];
        if ($this->courseFile == null){
            $this->errorView(ERR_NO_FILE);
        }
        
        $editFile = null;
        if ($parameters['edit']) {
            $visitor = Session::getInstance()->getVisitor();
            if (!$this->courseFile->getAuthor()->equals($visitor) and !$visitor->hasRight('COURSE_FILE_ADMIN')) {
                $this->rightsMissingView('COURSE_FILE_ADMIN');
            }
            
            $this->maxCourseFileSize = GlobalSettings::getGlobalSetting('COURSE_MAX_FILE_SIZE') * 1024;
            $editFile = $this->courseFile;
        } else if (InputValidator::getRequestData('upload_submit')) {
            //$this->maxCourseFileSize = GlobalSettings::getGlobalSetting('COURSE_MAX_FILE_SIZE') * 1024;
            $editFile = $this->addCourseFile();
        }
        
        $mayRate = true;
        $downloadedFile = $this->courseFile->hasAlreadyDownloaded(Session::getInstance()->getVisitor());
        
        // check, if user has already downloaded this file and may therefore rate it
        if (!$downloadedFile) {
            $mayRate = false;
        } 
        // check, if user has already rated this file
        else if ($this->courseFile->hasAlreadyRated(Session::getInstance()->getVisitor())) {
            $mayRate = false;
        }
        // check if visitor is author of the file
        else if ($this->courseFile->getAuthor()->equals(Session::getInstance()->getVisitor())) {
            $mayRate = false;
        }

        $this->fileDetailsView($mayRate, $editFile, $downloadedFile);
    }
    
    protected function addCourseFileRating() {
        $cUser = Session::getInstance()->getVisitor();
        
        // check, if user's rights suffice
        if (!$cUser->hasRight('COURSES_FILE_RATING')) {
            $this->rightsMissingView('COURSES_FILE_RATING');
        }
        // check, if file id is given
        if (array_key_exists('id', $_REQUEST)) {
            $this->courseFile = CourseFileModel::getCourseFileById($_REQUEST['id']);
            if($this->courseFile == null){
                $this->errorView(ERR_NO_FILE);
            }
        } else {
        	$this->errorView(ERR_COURSE_NO_FILE);
        }
        
        // check, if user has already downloaded this file and may therefore rate it
        if (!$this->courseFile->hasAlreadyDownloaded(Session::getInstance()->getVisitor())) {
        	$this->errorView(ERR_COURSE_FILE_NOT_DOWNLOADED);
        }
        // check, if user has already rated this file
        if ($this->courseFile->hasAlreadyRated(Session::getInstance()->getVisitor())) {
            #die('you may only rate once');
            $this->errorView(ERR_COURSE_DUAL_RATE);
        }
        
        $quickVote = false;
        
        // check for quickvote
        if (array_key_exists('quickvote', $_POST)) {
        	$cat = CourseFileRatingCategoryModel::getCategoryQuickvote();
            $ratingsSingle = array();
            
            $ratingsSingle[$cat->name]['rating'] = $_POST['quickvote'];
            $ratingsSingle[$cat->name]['category'] = $cat;

            $validRating = InputValidator::isValidForFormElement($_POST['quickvote'],true,$cat);
            
            $quickVote = true;
        } else {        
            // extract single ratings from POSTed data
            $ratingsSingle = array();
            $ratingCategories = CourseFileRatingCategoryModel::getAllCategories();
            // counter for ratings that can not to be classified
            $falseCategories = 0;
            // set flag to boolean, because we AND to it
            $validRating = true;
            foreach ($_POST as $key => $val) {
                if (substr($key,0,6) == 'rating') {
                    $category = $ratingCategories[substr($key,6)];
                    if (!$category) {
                    	++$falseCategories; 
                        break;
                    }
                    $ratingsSingle[$category->name]['rating'] = $val;
                    $ratingsSingle[$category->name]['category'] = $category;
                    // validate input; require input on range selects
                    $validRating &= InputValidator::isValidForFormElement($val,$category->getType() == FORM_ELEMENT_RANGE,$category);
                }
            }
            $validRating &= !$falseCategories && count($ratingsSingle) == count($ratingCategories);
        }
        
        // TODO: write general form input validator;
        // create new interface for input type; apply this to CourseFileRatingModel
        
        // ensure, that all categories are used
        if ($validRating) {
            $rating = new CourseFileRatingModel(null, Session::getInstance()->getVisitor(), null,
                $ratingsSingle);
            $rating->addRatingToFile($this->courseFile);
            
            // generate points, if not quickvote
            if (!$quickVote) {
            	$ps = PointSourceModel::getPointSourceByName('COURSE_FILE_RATED');
                $cUser->increaseUnihelpPoints($ps->getPointsSum(), $ps->getPointsFlow());
                $cUser->save();
            }
            
            // TODO: find a better way than instanciating view object here
            $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), COURSE_TEMPLATE_DIR . 'course_file_rating.tpl');
            $view->clearCache(self::getCourseFileCachekey($this->courseFile));
        } else {
        	$this->errorView('rating not complete');
        }

        // may not rate anymore
        $this->fileDetailsView(false, null, true);
    }
        
    protected function subsidiesView() {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), COURSE_TEMPLATE_DIR . 'course_subsidy.tpl');
        $main->assign('subsidies', CourseFileModel::getSubsidies());
        
        $this->setCentralView($main);

        parent::view();
    }
    
    protected static function getCourseFileCachekey($file, $downloadedFile = null) {
        $cacheKey = 'course|file|' . $file->id;
        if ($downloadedFile !== null) {
            $cacheKey .= '|' . ($downloadedFile ? '1' : '0');
        }
        return $cacheKey;
    }
    
    protected static function getCourseFilesCachekey($course, $filter = null, $page = null) {
        $cacheKey = 'course|' . $course->id . '|files';
        $cacheKey .= '|' . ($filter ? '1' : '0');
        if ($page !== null) {
            $cacheKey .= '|' . $page;
        }
        return $cacheKey;
    }
    
    protected function fileDetailsView($mayRate, $editFile = null, $downloadedFile = false) {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), COURSE_TEMPLATE_DIR . 'course_file_rating.tpl');
        
        $main->setCacheParameter(-1, self::getCourseFileCachekey($this->courseFile, $downloadedFile));

        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        
        $main->assign('coursefile', $this->courseFile);
        $main->assign('coursefile_downloaded', $downloadedFile);
        $main->assign('coursefile_may_rate', $mayRate);
               
        if ($mayRate) {
            $main->assign('coursefileratingcategories', CourseFileRatingCategoryModel::getAllCategories());
        }
        if ($editFile != null) {
            $main->assign('coursefile_edit', true);
            $main->assign('coursefiles_file', $editFile);
            
            $main->assign('coursefilescategories', CourseFileCategoryModel::getAllCategories());
            $main->assign('coursefilessemesters', CourseFileSemesterModel::getAllSemesters());
            $main->assign('coursefiles_maxattachmentsize', $this->maxCourseFileSize);
            $main->assign('coursefiles_maxattachmentsize_kb', $this->maxCourseFileSize/1024);
        }

        $this->setCentralView($main);

        parent::view();
    }
    
    protected function ratingsViewAdd($mayRate) {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), COURSE_TEMPLATE_DIR . 'course_file_rating_add.tpl');
        
        $main->assign('coursefile', $this->courseFile);
        $main->assign('coursefile_may_rate', $mayRate);
        $main->assign('coursefileratingcategories', CourseFileRatingCategoryModel::getAllCategories());
        $this->setCentralView($main);

        parent::view();
    }
    
    protected function viewMain($fileToDisplay = null, $fileFilter = null, $page = null) {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), COURSE_TEMPLATE_DIR . 'course_files.tpl');
        
        if ($page == null or $page < 1) {
            $page = V_COURSE_FILES_START_PAGE;
        }
        
        $main->setCacheParameter(-1, self::getCourseFilesCachekey($this->course, $fileFilter, $page));
        
        if (defined('CACHETEST') and $fileFilter == null) {
            $main->enableCaching();
        }
        
        $main->assign('errors', $this->errors);

        $visitor = Session::getInstance()->getVisitor();
        if (!$visitor->isLoggedIn()) {
        	$visitor = null;
        }        
        $main->assign('course', $this->course);

        if (!$main->isCached()) {
            $temp = CourseFileModel::getCourseFilesByCourse($this->course, $visitor, $fileFilter, V_COURSE_FILES_PER_PAGE, V_COURSE_FILES_PER_PAGE * ($page-1));        
            $main->assign('coursefiles_files', $temp[1]);
            if ($fileFilter) {
                $main->assign('coursefiles_categoryfilter', $fileFilter->getOption(CourseFileFilter::FILTER_CATEGORY));
                $main->assign('coursefiles_semesterfilter', $fileFilter->getOption(CourseFileFilter::FILTER_SEMESTER));
                $main->assign('coursefiles_ratingfilter', $fileFilter->getOption(CourseFileFilter::FILTER_RATING));
                $main->assign('coursefiles_orderstring', $fileFilter->getOption(CourseFileFilter::FILTER_ORDER));
                $main->assign('coursefiles_orderDirstring', $fileFilter->getOption(CourseFileFilter::FILTER_ORDERDIR));
            }
            
            $totalPages = ceil($temp[0] / V_COURSE_FILES_PER_PAGE);
            
            $main->assign('coursefiles_counter', InteractiveUserElementModel::nonLinearCounter($totalPages, $page));
            $main->assign('coursefiles_page', $page);
        }
        
        
        if (Session::getInstance()->getVisitor()->isLoggedIn()) {
            $main->assign('coursefiles_file', $fileToDisplay);
            $main->assign('coursefilescategories', CourseFileCategoryModel::getAllCategories());
            $main->assign('coursefilessemesters', CourseFileSemesterModel::getAllSemesters());
            $main->assign('coursefiles_maxattachmentsize', $this->maxCourseFileSize);
            $main->assign('coursefiles_maxattachmentsize_kb', $this->maxCourseFileSize/1024);
            $main->assign('coursefiles_unrated', CourseFileModel::getCourseFilesUnrated(Session::getInstance()->getVisitor()));
        }
        
        $this->setCentralView($main);

        parent::view();
    }
    
    protected function courseFileFeed() {
        $courseId = InputValidator::getRequestData('courseId', null);

        $entries = null;
        $feedMetadata = null;

        $course = CourseModel::getCourseById($courseId);
        
        $entries = CourseFileModel::getCourseFilesByCourse($course);
        $entries = $entries[1];
        
        $name = "";
        if (count($entries) > 0) {
            $name = $entries[0]->getCourse()->getName();
        }
        $entries = BaseModel::applyProxy($entries, new CourseFileFeedProxy);
        
        $feedMetadata = array(
            'title' => NAME_FORUM_LATEST_RSS . " / " . $name,
            'description' => NAME_FORUM_LATEST_RSS . " / " . $name,
            'url' => rewrite_course(array('courseFile' => $course, "extern" => true))
        );

        $this->showFeed($entries, $feedMetadata);

    }
    
    protected function ajaxShowRating(){
    	
        if(!empty($_REQUEST['id'])){
            $this->courseFile = CourseFileModel::getCourseFileById($_REQUEST['id']);
        }else{
        	return;
        }         
        
        $visitor = Session::getInstance()->getVisitor();
        
        if($this->courseFile->getAuthor()->equals($visitor)){
            return;
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), COURSE_TEMPLATE_DIR . 'course_file_rating_add_all.tpl');
        
        $main->assign('coursefile', $this->courseFile);
        $main->assign('coursefileratingcategories', CourseFileRatingCategoryModel::getAllCategories());
        
        
        $main->display();
    }
}

?>
