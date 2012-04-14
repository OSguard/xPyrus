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

# $Id: smarty_view.php 6210 2008-07-25 17:29:44Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/views/smarty_view.php $
#
# class for smarty view
#

require_once CORE_DIR . '/smarty_instance.php';
require_once CORE_DIR . '/views/base_view.php';

/**
 * @package Views
 */
class SmartyView extends BaseView {
    protected $smarty;
    
    protected $templateFolder;
    protected $templateName;
    protected $cacheKey;
    protected $cacheTime;
    
    protected $assignedVariables;
    
    protected $hasCaching = false;
    
    protected $isStupidBrowser = false;
    
    public function enableCaching() { $this->hasCaching = true; }
    public function disableCaching() { $this->hasCaching = false; }
    
    public function __construct ($templateFolder, $templateName) {
        parent::__construct();
        $this->smarty = &SmartyInstance::getHandle();
	$this->smarty->setLanguage('de');
        
        if ($templateFolder === null) {
            $templateFolder = "unihelp_hp";
        }
        
        $this->templateFolder = $templateFolder;
        
        $this->switchTemplateFolder($this->templateFolder);
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $this->isStupidBrowser = strpos($userAgent, 'MSIE') !== false &&
                strpos($userAgent, 'MSIE 7') === false;
            
        // HACK for IE        
        if ($this->isStupidBrowser) {
            $templateNameIE = str_replace('.tpl', '_ie.tpl', $templateName);
            if (file_exists($this->smarty->template_dir. '/' . $templateNameIE)) {
                $this->templateName = $templateNameIE;
            } else {
                $this->templateName = $templateName;
            }
        } else {
            $this->templateName = $templateName;
        }
        
        $this->assignedVariables = array();
        
        $this->setCacheParameter(0, '');
    }
    
    /**
     * @return boolean
     */
    public function isCached() {
        //$this->switchTemplateFolder($this->templateFolder);
        $oldCache = $this->smarty->caching;
        $this->smarty->caching = ($this->hasCaching) ? 2 : 0;
        $isCached = $this->smarty->is_cached($this->templateName, $this->cacheKey);
        $this->smarty->caching = $oldCache;
        return $isCached;
        //return $this->smarty->is_cached($this->templateName, $this->cacheKey);
    }
    
    /**
     * sets cache parameter for this view
     * @param int       cache lifetime in seconds or -1 for infinite caching
     * @param string    key by which this template instance is cached 
     */
    public function setCacheParameter($lifetime, $cacheKey) {
        if ($this->isStupidBrowser) {
            if ($cacheKey != '') {
                $cacheKey .= '|ie6';
            } else {
                $cacheKey = 'ie6';
            }
        }
        $this->cacheKey = $cacheKey;
        $this->cacheTime = $lifetime;
    }
    
    /**
     * assigns a variable to this view
     * @param string
     * @param mixed
     */
    public function assign($variableName, $variableValue) {
        $this->assignedVariables[$variableName] = $variableValue;
    }
    
    public function display($xml=false) {
        $this->assignVariables();
        
        if ($xml){
        	 // send XML header
            header('Content-Type: text/xml; charset=utf-8');        
        } else {
            // send utf-8 in charset header, in case apache sends iso by default
            header('Content-Type: text/html; charset=utf-8');
        }
       
        // no browser cache
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // every time new
        if(DEVEL){
           addDebugOutput($this->templateName, true);
        }
        $oldCache = $this->smarty->caching;
        $this->smarty->caching = ($this->hasCaching) ? 2 : 0;
        $this->smarty->cache_lifetime = $this->cacheTime;
        $this->smarty->display($this->templateName, $this->cacheKey);
        $this->smarty->caching = $oldCache;
    }
    
    public function fetch(){
    	$this->assignVariables();
        $oldCache = $this->smarty->caching;
        $this->smarty->caching = ($this->hasCaching) ? 2 : 0;
        $this->smarty->cache_lifetime = $this->cacheTime;
        $content = $this->smarty->fetch($this->templateName, $this->cacheKey);
        $this->smarty->caching = $oldCache;
        return $content;
    }
    
    public function clearCache($cacheKey = null, $expireTime = null, $templateSpecific = false){
        $oldCache = $this->smarty->caching;
        $this->smarty->caching = ($this->hasCaching) ? 2 : 0;
        $this->smarty->cache_lifetime = $this->cacheTime;
        if ($expireTime === 0) {
            // if expire time equals zero, we don't want to clear the cache
            $expireTime = time();
        } else if ($expireTime !== null) {
            // calculate age of template due to 
            // smarty implementation of this parameter
            $expireTime = time() - $expireTime;
        }
        if ($cacheKey == null) {
            $cacheKey = $this->cacheKey;
        }
        //var_dump($this->templateName, $cacheKey, $expireTime);
        //$this->smarty->clear_cache($this->templateName, $cacheKey, null, $expireTime);
        //$this->smarty->clear_cache($this->templateName, $cacheKey, null, $expireTime);
        if ($cacheKey != null and !$templateSpecific) {
            $this->smarty->clear_cache(null, $cacheKey, null, $expireTime);
        } else if ($cacheKey != null) {
            $this->smarty->clear_cache($this->templateName, $cacheKey);
        } else {
            $this->smarty->clear_cache($this->templateName);
        }
        $this->smarty->caching = $oldCache;
        //var_dump($this->templateName, $cacheKey, $expireTime);
        addDebugOutput('cleaning cache: ' . $this->templateName . ' ~~ ' .  $cacheKey . ' ~~ ' . $expireTime, true);
    }
    
    public function switchTemplateFolder($templateFolder) {
        $this->smarty->template_dir = BASE . "/template/" . $templateFolder;
        $this->smarty->assign('TEMPLATE_DIR', "/template/" . $templateFolder);
        # set compile id (allow different templates in same cache dir)
        $this->smarty->compile_id = $templateFolder;
    }
    
    protected function assignVariables() {
    //echo "---------------------------------------------------------";
    //var_dump($this->templateName);
        foreach ($this->assignedVariables as $var => $val) {
            $this->smarty->assign($var, $val);
        }
        if (!array_key_exists('visitor', $this->assignedVariables)) {
            $this->smarty->assign('visitor', Session::getInstance()->getVisitor());
        }
        // HACK for IE
        $this->smarty->assign('ie6', $this->isStupidBrowser);
        /* Show a button to swicht Debug messages on/off */
        if(DEVEL){
        	if(array_key_exists('show_debug',$_COOKIE) && $_COOKIE['show_debug'] == 'true'){
        	   $this->smarty->assign('showDebugOff',true);
            }
            else{
            	$this->smarty->assign('showDebugOn',true);
            }
            
        }
    }
    
    public function getContent() {
        $this->assignVariables();
        if(DEVEL){
            addDebugOutput('<font size="1">['. $this->templateName . "]</font>", true);
        }
        $oldCache = $this->smarty->caching;
        $this->smarty->caching = ($this->hasCaching) ? 2 : 0;
        $this->smarty->cache_lifetime = $this->cacheTime;
        $content = &$this->smarty->fetch($this->templateName, $this->cacheKey);
        $this->smarty->caching = $oldCache;
        return $content;
    }
}

?>
