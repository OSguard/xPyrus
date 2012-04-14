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

// $HeadURL: svn://unihelp.de/unihelp_dev/v2/trunk/htdocs/core/boxes/user_search_box.php $

require_once CORE_DIR . '/boxes/box_controller.php';
require_once CORE_DIR . '/views/view_factory.php';
require_once MODEL_DIR . '/base/user_protected_model.php';

/**
 * @class WetterComBox
 * @brief representing the user search box
 * 
 * @author linap
 * @version $Id: user_search_box.php 4176 2007-04-04 22:47:40Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Boxes
 */
class WetterComBox extends BoxController {
    protected $cacheKey = 'boxes|wetter_com';
    /**
     * constructor
     */
    public function __construct($instance) {    
        parent::__construct('wetter_com',$instance);
    }
    
    public function getView($ajax = false) {
        $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),
                'boxes/wetter_com.tpl');
        
        if (defined('CACHETEST')) {
            $view->enableCaching();
        }
        
        // we have no restrictions for cache key 
        $cacheKey = $this->cacheKey;
        
        // we can cache forever here
        $this->setCanonicalParameters($view, -1, $cacheKey, $ajax);
        
        /*
         * box never minimize
         */
        $this->minimized = false;
        
        $view->assign('box_wetter_com_minimized', $this->minimized);
        return $view;
    }
}

?>
