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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/boxes/courses_box.php $

require_once CORE_DIR . '/boxes/box_controller.php';
require_once CORE_DIR . '/views/view_factory.php';
require_once MODEL_DIR . '/course/course_model.php';

/**
 * @class CoursesBox
 * @brief representing the user's courses box
 * 
 * @author linap
 * @version $Id: courses_box.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Boxes
 */
class CoursesBox extends BoxController {
    protected $cacheKey = 'boxes|courses';
    /**
     * constructor
     */
    public function __construct($instance) {
        parent::__construct('courses', $instance);
    }
    
    public function getView($ajax = false) {
        $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),
                'boxes/courses.tpl');
        // extend cacheKey by session user's id 
        $uid = Session::getInstance()->getVisitorCachekey();
        $cacheKey = $this->cacheKey . '|' . $uid;
        
        if (defined('CACHETEST')) {
            $view->enableCaching();
        }
        
        // as we observe changes to courses, we can cache for a long time here
        $this->setCanonicalParameters($view, -1, $cacheKey, $ajax);
        
        // observe course list changes
        self::observeIPC(new UserIPC(Session::getInstance()->getVisitor()->id), array('COURSES_CHANGED'), $view);
        
        if (!$view->isCached() and !$this->minimized) {
            $courses = Session::getInstance()->getVisitor()->getCourses();
            $view->assign('box_courses_courses', $courses);
        }
        
        return $view;
    }

}

?>
