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

# $Id: privacy_context.php 2378 2007-02-02 12:29:08Z trehn $
# $HeadURL: svn://unihelp.de/unihelp_dev/v2/trunk/htdocs/core/privacy_context.php $
#

require_once CORE_DIR . '/controller_factory.php';

/**
 * @package Core
 */
class BLCMethod {
    protected $pattern;
    protected $parameters;
    protected $parentMethod;
    
    protected $name;
    protected $url;
    
    protected $feedURL;
    protected $feedTitle;
    
    /**
     * constructs an blc method object
     * @param string 
     * @param string may be null, if no url is available
     * @param BLCMethod
     * @param string
     */
    public function __construct($name, $url, $parentMethod, $feedURL = null, $feedTitle = null) {
        $this->name = $name;
        $this->url = $url;
        $this->parentMethod = $parentMethod;
        $this->feedURL = $feedURL;
        $this->feedTitle = $feedTitle;
    }
    
    public function getURL() {
        return $this->url;
    }
    
    public function getFeedURL() {
        return $this->feedURL;
    }
    
    public function getFeedTitle() {
        return $this->feedTitle;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getParentMethod() {
        return $this->parentMethod;
    }
    
    protected static $defaultMethod = null;
    public static function getDefaultMethod() {
        if (self::$defaultMethod === null) {
            self::$defaultMethod = ControllerFactory::createControllerByName('index', true)->getMethodObject('home');
        }
        return self::$defaultMethod;
    }
    
    public static function getQuotedName($name, $prefix = '') {
        return $prefix . ' ' . QUOTE_LEFT . $name . QUOTE_RIGHT;
    }
}

?>
