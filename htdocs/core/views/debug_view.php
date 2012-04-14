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

# $Id: debug_view.php 5807 2008-04-12 21:23:22Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/views/debug_view.php $
#
# class for smarty view
#

require_once CORE_DIR . '/views/base_view.php';

/**
 * @package Views
 */
class DebugView extends BaseView {
    protected $variables;
    
    public function __construct () {
        parent::__construct();
        header('Content-Type: text/plain');
        
        $this->variables = array();
    }
    
    public function isCached() {
        return false;
    }
    
    public function setCacheParameter($lifetime, $cacheKey) {}
    
    public function assign($variableName, $variableValue) {
        $this->variables[$variableName] = $variableValue;
    }
    
    public function display() {
        foreach ($this->variables as $key => $val) {
            echo "$key :\n";
            var_dump($val);
            echo "-------------------------------------------\n";
        }
    }

}

?>
