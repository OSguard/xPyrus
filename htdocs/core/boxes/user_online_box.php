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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/boxes/user_online_box.php $

require_once CORE_DIR . '/boxes/box_controller.php';
require_once CORE_DIR . '/views/view_factory.php';
require_once MODEL_DIR . '/base/user_protected_model.php';

/**
 * @class UserOnlineBox
 * @brief representing the user online box
 * 
 * @author linap
 * @version $Id: user_online_box.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Boxes
 */
class UserOnlineBox extends BoxController {
    protected $cacheKey = 'boxes|user_online';
	protected $sortList = false;
    
    /**
	 * constructor
	 */
	public function __construct($instance) {
		parent::__construct('user_online',$instance);
        
        $this->sortCriterion = Session::getInstance()->getViewData('user_online_sort');
	}
    
    // the criterion, the list is sorted by
    private $sortCriterion;
    
    public function getView($ajax = false) {
        // extend cacheKey by sort criterion (if set) 
        $cacheKey = $this->cacheKey;
        
        if (Session::getInstance()->getVisitor()->isLoggedIn()) {
            if ($this->sortCriterion) {
                $cacheKey .= '|' . $this->sortCriterion; 
            } else {
                $cacheKey .= '|none';
            }
            if($this->sortList){
            	$cacheKey .= '|ajax';
            }
        } else {
            $cacheKey .= '|off';
            $this->sortList = null;
        }
        
        if ($this->sortList){
        	$view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),
                'boxes/user_online_list.tpl');
        }else{
            $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),
                'boxes/user_online.tpl');
        } 
                
        if (defined('CACHETEST')) {
            $view->enableCaching();
        }
        // CACHEME: cache for 10 minutes, regardless of login/logoff actions
        $this->setCanonicalParameters($view, 600, $cacheKey, $ajax);
        
        if (!$view->isCached() and !$this->minimized) {
            $userOnline = UserProtectedModel::getUsersByOnline(false, $this->sortCriterion);
            $anonymousUsers = UserModel::getAnonymousUserOnlineNumber();
            $externalUsers = UserExternalModel::getUsersByOnline();
            $view->assign('box_user_online_users', array_merge($userOnline,$externalUsers));
            $view->assign('box_user_online_users_number', count($userOnline) + count($externalUsers) + $anonymousUsers);
            $view->assign('box_user_online_guests_number', $anonymousUsers);
            $view->assign('visitor_logged_in', Session::getInstance()->getVisitor()->isLoggedIn());
        }
        
        return $view;
    }
    
    /**
     * set methods that are allowed for calling by the controller
     */
    protected function getAllowedMethods() {
        // some sort criteria
        return array_merge(parent::getAllowedMethods(),
            array (
                'sortByStatus',
                'sortByUsername',
                'sortByGender',
                'sortByAge',
                'sortByCourse',
                
                'ajaxSort',
                
                // no longer used (linap, 30.06.07)
                //'ajaxOnlineNumber',
                  )
        );
    }
     
    protected function sortByStatus() {
        $this->setSortCriterion('status');
    }
        
    protected function sortByUsername() {
        $this->setSortCriterion('username');
    }
    
    protected function sortByGender() {
        $this->setSortCriterion('gender');
    }
    
    protected function sortByAge() {
        $this->setSortCriterion('age');
    }
    
    protected function sortByCourse() {
        $this->setSortCriterion('course');
    }
    
    protected function setSortCriterion($criterion) {
    	$this->sortCriterion = $criterion;
        Session::getInstance()->storeViewData('user_online_sort', $this->sortCriterion);
    }
    
    protected function ajaxSort(){
    	
        if(array_key_exists('sortBy',$_REQUEST)){
        	 $sortBy = $_REQUEST['sortBy'];
             if($sortBy == 'status' || $sortBy == 'username' 
                || $sortBy == 'gender' || $sortBy == 'age'
                || $sortBy = 'course'){
                 $this->setSortCriterion($sortBy);                 
                }
        }
        $this->sortList = true;
        $this->getView(true)->display();
    }
    
    /*protected function ajaxOnlineNumber() {
        $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),
                'boxes/user_online_number.tpl'); 
                
        if (defined('CACHETEST')) {
            $view->enableCaching();
        }
        
        $this->setCanonicalParameters($view, 60, $this->cacheKey . '|number');
        
        if (!$view->isCached()) {
            $view->assign('number', UserModel::getTotalUserOnlineNumber());
        }
        
        $view->display();
    }*/
    
}

?>
