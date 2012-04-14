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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/boxes/friendslist_box.php $

require_once CORE_DIR . '/boxes/box_controller.php';
require_once CORE_DIR . '/views/view_factory.php';
require_once MODEL_DIR . '/base/friend_model.php';

/**
 * @class FriendlistBox
 * @brief representing the user's friendlist box
 * 
 * @author linap
 * @version $Id: friendslist_box.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Boxes
 */
class FriendslistBox extends BoxController {
    protected $cacheKey = 'boxes|friendlist';
	/**
	 * constructor
	 */
	public function __construct($instance) {
		parent::__construct('friendslist',$instance);
		
		if (!Session::getInstance()->getVisitor()->isLoggedIn()) {
    		throw new ArgumentException('display friendlist of not logged in user is not possible');
		}
	}
    
    public function getView($ajax = false) {
        $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),
                'boxes/friendlist.tpl');
                
        // extend cacheKey by session user's id 
        $uid = Session::getInstance()->getVisitorCachekey();
        $cacheKey = $this->cacheKey . '|' . $uid;
        
        if (defined('CACHETEST')) {
            $view->enableCaching();
        }
        
        // because we have the users' online status in the list, we
        // should cache at most 15 minutes (linap, 23.05.2007)
        // reduced cache lifetime to 5 minutes (linap, 31.08.2007) 
        $this->setCanonicalParameters($view, 300, $cacheKey, $ajax);
        

        // observe friendlist changes
        self::observeIPC(new UserIPC(Session::getInstance()->getVisitor()->id), array('FRIENDLIST_CHANGED'), $view);
        //var_dump($view->isCached(), $cacheKey);
        if (!$view->isCached() and !$this->minimized) {
            $user = Session::getInstance()->getVisitor();
            $friends = FriendModel::getFriendsByUser($user, 'username', '');
            
            if($user->hasRight('FRIENDLIST_EXTENDED_CATEGORIES')){
            	$friendFiled = array('Love' => array(),
                                     'Friend' => array(),
                                     'Family' => array(),
                                     'Normal' => array());
                foreach($friends as $friend){
                	$friendFiled[$friend->getFriendType()][] = $friend;
                }
                $view->assign('box_friendlist_users_adv',true);
                foreach($friendFiled as $key => $fgroup){
                    $view->assign('box_friendlist_users_'.$key, $fgroup);
                }
            }
            
            $view->assign('box_friendlist_users', $friends);
        }
        
        return $view;
    }
    
    public function setShow(){
    	if (empty($_REQUEST['onlyOnline'])) {
            return;
        }
            
        $this->setConfigValue('onlyOnline', ($_REQUEST['onlyOnline'] == 'true' ));
    }
    
    protected function getAllowedMethods() {
        return array_merge(parent::getAllowedMethods(),
            array ('setShow'));
    }
}

?>
