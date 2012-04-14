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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/boxes/courses_files_box.php $

require_once CORE_DIR . '/boxes/box_controller.php';
require_once CORE_DIR . '/views/view_factory.php';
require_once MODEL_DIR . '/course/course_file_model.php';

/**
 * @class CoursesFilesBox
 * @brief representing the lastest uploaded course files box
 * 
 * @author linap
 * @version $Id: courses_files_box.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Boxes
 */
class CoursesFilesBox extends BoxController {
    protected $cacheKey = 'boxes|courses_files';
	/**
	 * constructor
	 */
	public function __construct($instance) {
		parent::__construct('courses_files',$instance);
	}
    
    public function getView($ajax = false) {
        $visitor = Session::getInstance()->getVisitor(); 
        
        $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),
                'boxes/courses_files.tpl');
        
        if (defined('CACHETEST')) {
            $view->enableCaching();
        }
        
        // we need detailed cachekey only if we are not minimized
        if (!$this->minimized) {        
            if ($visitor->isLoggedIn() && !$visitor->isExternal()) {
                $courseSelect = $this->getConfigValue('course', 'all');
                $special = ($courseSelect !== 'all' and $courseSelect !== 'allOwn');
                if ($special){
                    $cacheKey = $this->cacheKey . '|course|' . $courseSelect;
                } else {
                    // extend cacheKey by session user's id 
                    $uid = Session::getInstance()->getVisitorCachekey();
                    $cacheKey = $this->cacheKey . '|user|' . $uid;
                }               
            } else {
                $cacheKey = $this->cacheKey . '|all';
            }
        } else {
            // for minimized boxes setCanonicalParameters
            // cares for the cache key
            $cacheKey = null;
        }
        
        // 15 minutes caching seems okay
        $this->setCanonicalParameters($view, 900, $cacheKey, $ajax);
        
        if (!$view->isCached() and !$this->minimized) {
            if ($visitor->isLoggedIn() && !$visitor->isExternal()) {
            	$courseSelect = $this->getConfigValue('course', 'all');
                //var_dump($courseSelect);
                $special = ($courseSelect !== 'all' and $courseSelect !== 'allOwn');
                if ($special){
                    $course = CourseModel::getCourseById($courseSelect);
                    // show 10 latest course files of given course
                    $courseFiles = CourseFileModel::getCourseFilesByCourse($course, null, null, 10);
                    //var_dump($courseFiles[1]);
                    $view->assign('spezialCourseFile', $courseFiles[1]);
                    $view->assign('spezialCourse', $course);
                } else if($courseSelect !== 'all'){
                    $courseFiles = CourseFileModel::getLatestCourseFiles($visitor->getCourses());
                    $view->assign('box_courses_files_latest_files', $courseFiles);
                    $view->assign('personal',true);
                    $view->assign('spezialCourse', false);
                } else {
                	$courseFiles = CourseFileModel::getLatestCourseFiles();
                    $view->assign('box_courses_files_latest_files', $courseFiles);
                    $view->assign('personal',false);
                    $view->assign('spezialCourse', false);
                    $view->assign('box_courses_files_total_number', CourseFileModel::getTotalFileNumber());
                }
               
            } else {
            	$courseFiles = CourseFileModel::getLatestCourseFiles();
                $view->assign('box_courses_files_latest_files', $courseFiles);
                $view->assign('box_courses_files_total_number', CourseFileModel::getTotalFileNumber());
            }
        }
        
        return $view;
    }
    
    public function setCourse() {

        if (empty($_REQUEST['course'])) {
            return;
        }
            
        $this->setConfigValue('course', $_REQUEST['course']);
    }
    
    public function ajaxSetCourse(){
		$this->setCourse();     
		
		/* display box that the ajax code can handle the html stuff */   
        $this->getView(true)->display();
    }
    
    protected function getAllowedMethods() {
        return array_merge(parent::getAllowedMethods(),
            array ('setCourse',
                   'ajaxSetCourse'
                   ));
    }
    
}

?>