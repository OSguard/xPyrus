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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/boxes/random_userpic_box.php $

require_once CORE_DIR . '/boxes/box_controller.php';
require_once CORE_DIR . '/views/view_factory.php';
require_once MODEL_DIR . '/base/user_protected_model.php';

/**
 * @class RandomUserpicBox
 * @brief representing the random users' picture box
 * 
 * @author linap
 * @version $Id: random_userpic_box.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Boxes
 */
class RandomUserpicBox extends BoxController {
    protected $cacheKey = 'boxes|random_userpic';
    /**
     * constructor
     */
    public function __construct($instance) {
        parent::__construct('random_userpic',$instance);
    }
    
    public function getView() {
    	$view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),
                'boxes/random_userpic.tpl');
        $view->setCacheParameter(60, $this->cacheKey);
        
        if (!$view->isCached()) {
            // use only minutes precision for seed
            $seed = (date('h')*60+date('i'))*(date('N'))/525600.0;
            $view->assign('box_random_userpic_user', UserProtectedModel::getUserByRandom($seed));
        }
        
        return $view;
    }
}

?>
