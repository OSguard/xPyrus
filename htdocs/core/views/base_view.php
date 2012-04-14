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

// $Id: base_view.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/views/base_view.php $

abstract class BaseView {
    function __construct() {}
    
    abstract public function assign($var, $value);
    abstract public function display();
    
    /**
     * @return boolean
     */
    abstract public function isCached();
    
    /**
     * sets cache parameter for this view
     * @param int       cache lifetime in seconds or -1 for infinite caching
     * @param string    key by which this template instance is cached 
     */
    abstract public function setCacheParameter($lifetime, $cacheKey);
    
    abstract public function clearCache($cacheKey = null, $expireTime = null);
}

?>
