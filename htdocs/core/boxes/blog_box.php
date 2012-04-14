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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/boxes/blog_box.php $

require_once CORE_DIR . '/boxes/box_controller.php';
require_once CORE_DIR . '/views/view_factory.php';
require_once CORE_DIR . '/utils/global_ipc.php';
require_once MODEL_DIR . '/blog/blog_advanced_model.php';

/**
 * @class BlogBox
 * @brief representing the blog box
 *
 * @author linap
 * @version $Id: blog_box.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * @package Boxes
 */
class BlogBox extends BoxController {
    protected $cacheKey = 'boxes|blog';
    /**
     * constructor
     */
    public function __construct($instance) {
        parent::__construct('blog', $instance);
    }

    public function getView($ajax = false) {
        $view = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(),
                'boxes/blog.tpl');
        $cacheKey = $this->cacheKey;
        
        if (defined('CACHETEST')) {
            $view->enableCaching();
        }
        $this->setCanonicalParameters($view, 86400, $cacheKey, $ajax);
        
        self::observeIPC(
            new GlobalIPC, 
            array('BLOG_CHANGED'),
            $view, $cacheKey);
        
        $view->assign('box_blog_minimized', $this->minimized);
        $view->assign('box_blog_ajax', $ajax);
        
        if (!$view->isCached() && !$this->minimized) {
            $entries = array();
            foreach (BlogAdvancedModel::getAllParsedEntriesFromAllUsers() as $date => $_entries) {
                $entries = array_merge($entries, $_entries);
            }
            $view->assign('box_blog_entries', $entries);
        }
        
        return $view;
    }

}

?>
