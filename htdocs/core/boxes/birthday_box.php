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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/boxes/birthday_box.php $

require_once CORE_DIR . '/boxes/box_controller.php';
require_once CORE_DIR . '/views/view_factory.php';
require_once MODEL_DIR . '/base/user_protected_model.php';

/**
 * @class BirthdayBox
 * @brief representing the birthday box
 *
 * @author linap
 * @version $Id: birthday_box.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * @package Boxes
 */
class BirthdayBox extends BoxController {
    protected $cacheKey = 'boxes|birthday';
    /**
     * constructor
     */
    public function __construct($instance) {
        parent::__construct('birthday', $instance);
    }

    public function getView($ajax = false) {
        $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),
                'boxes/birthday.tpl');
        // cache by day 
        $cacheKey = $this->cacheKey . '|' . date('d');
        
        if (defined('CACHETEST')) {
            $view->enableCaching();
        }
        $this->setCanonicalParameters($view, 86400, $cacheKey, $ajax);
        
        $view->assign('box_birthday_minimized', $this->minimized);
        $view->assign('box_birthday_ajax', $ajax);
        
        if (!$view->isCached() && !$this->minimized) {
            // don't show invisible users
            // date(j) -> day
            // date(n) -> month 
            $view->assign('box_birthday_users', UserProtectedModel::getUsersByBirthday(false, date('j'), date('n')));
        }
        
        return $view;
    }

}

?>
