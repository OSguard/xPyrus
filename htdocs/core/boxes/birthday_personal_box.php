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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/mauritius-1.1/htdocs/core/boxes/birthday_box_personal.php $

require_once CORE_DIR . '/boxes/box_controller.php';
require_once CORE_DIR . '/views/view_factory.php';
require_once MODEL_DIR . '/base/user_protected_model.php';
require_once MODEL_DIR . '/base/friend_model.php';

/**
 * @class BirthdayBoxPersonal
 * @brief representing the birthday box by User
 *
 * @author linap
 * @version $Id: birthday_box.php 3646 2007-03-06 23:21:42Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * @package Boxes
 */
class BirthdayPersonalBox extends BoxController {
    protected $cacheKey = 'boxes|birthday_personal';
    /**
     * constructor
     */
    public function __construct($instance) {
        parent::__construct('birthday_personal', $instance);
    }

    public function getAllowedMethods() {
        return array_merge(parent::getAllowedMethods(),
            array('setNotifyPMBefore'));
    }

    public function setNotifyPMBefore() {
        $days = InputValidator::getRequestData('reminder', 0);
        $this->setConfigValue(self::KEY_PM_BEFORE, $days);
        $this->mustReload = true;
    }

    const KEY_PM_BEFORE = 'reminder_before_days';
    protected $mustReload = false;

    public function notifyPMBefore($user) {
        $this->loadConfig($user->id);
        if (!isset($this->boxConfig[self::KEY_PM_BEFORE])) {
            // default is notify three days in advance
            return 3;
        }
        return (int) $this->boxConfig[self::KEY_PM_BEFORE];
    }

    public function getView($ajax = false) {
        $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),
                'boxes/birthday_personal.tpl');
        
        // extend cacheKey by session user's id 
        $uid = Session::getInstance()->getVisitorCachekey();
        $cacheKey = $this->cacheKey . '|' . $uid . '|' . date('d');
        
        if (defined('CACHETEST')) {
            $view->enableCaching();
        }
        $this->setCanonicalParameters($view, 86400, $cacheKey, $ajax);
        
        $view->assign('box_birthday_personal_minimized', $this->minimized);
        $view->assign('box_birthday_personal_ajax', $ajax);

        if ($this->mustReload) {
            $view->clearCache($cacheKey);
        }
        
        if (!$view->isCached() && !$this->minimized) {

            $visitor = Session::getInstance()->getVisitor();
            $users = array();
            
            for($i=0;$i<7;$i++){
                $inDays = time() + $i * 86400;
                // don't show invisible users
                // date(j) -> day
                // date(n) -> month 
                $u = UserModel::getUsersByBirthday(false, date('j', $inDays), date('n', $inDays));
                $toShow = array();
                foreach($u as $mayIsFriend){
                	if(FriendModel::isFriendOf($visitor,$mayIsFriend)){
                		$toShow[] = $mayIsFriend;
                	}
                }
                
                if(count($toShow) > 0 ){
                    $users[$inDays] = $toShow;
                    //$users[$inDays] = array_merge($users[$inDays], array(Session::getInstance()->getVisitor()));
                }                
            }
            
            $view->assign('box_birthday_personal_users', $users);
            $view->assign('box_birthday_personal_users_days', $this->notifyPMBefore(Session::getInstance()->getVisitor()));
        }
        
        return $view;
    }

}

?>
