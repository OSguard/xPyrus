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

# $Id: smarty_blog_view.php 5807 2008-04-12 21:23:22Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/views/smarty_blog_view.php $
#
# class for smarty view
#

require_once CORE_DIR . '/smarty_instance.php';
require_once CORE_DIR . '/views/smarty_view.php';

/**
 * @package Views
 */
class SmartyBlogView extends SmartyView {
    protected $centralView;
    
    const TEMPLATE_FILE = 'modules/blogadvanced/blog.tpl';
    
    public function __construct ($templateFolder) {
        parent::__construct($templateFolder, self::TEMPLATE_FILE);
        
        // disable caching
        $this->setCacheParameter(0, '');
    }
    
    public function setCentralView($central) {
        $this->centralView = $central;
    }
    
    public function display() {
        $this->assign('central', $this->centralView);
        
        parent::display();
    }

}

?>
