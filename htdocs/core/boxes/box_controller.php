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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/boxes/box_controller.php $

require_once CORE_DIR . '/base_controller.php';
require_once CORE_DIR . '/utils/cookie_manager.php';

/**
  * @class BoxController
  * @brief controller for the boxes on the left and right side
  * 
  * @author kyle, linap
  * @version $Id: box_controller.php 6210 2008-07-25 17:29:44Z trehn $
  * @copyright Copyright &copy; 2006, Unihelp.de
  * 
  * @package Boxes
  */
abstract class BoxController extends BaseController {
    protected $cacheKey = '';
    /**
     * @var boolean
     * true iff box is minimized
     */
    protected $minimized;
    /**
     * @var string
     * identifier of box
     */
    protected $boxName;
    
    /**
     * @var int identifier in case of multiple box instances
     */
    protected $instance;
    
    protected $boxConfig = array();
    
    protected $boxConfigIds = array();
    
	public function __construct($boxName, $instance = 1) {
        parent::__construct();
        $this->boxName = $boxName;
        
        if (Session::getInstance()->getVisitor()->hasRight('FEATURE_BOX_REARRANGEMENT')) {
            $this->minimized = self::isMinimized($this->boxName, $instance);
        }
        $this->instance = $instance;
	}

    protected function setCanonicalParameters($view, $lifetime, $cacheKey, $ajax = false) {
        // add ajax flag if needed
        if ($ajax) {
            $cacheKey = $cacheKey . '|ajax';
        }
        // set minimization flag if needed
        if ($this->minimized) {
            $cacheKey = $this->cacheKey . '|__MINIMIZED__';
        }

        $view->setCacheParameter($lifetime, $cacheKey);
        $view->assign('box_' . $this->boxName . '_minimized', $this->minimized);
        $view->assign('box_' . $this->boxName . '_ajax', $ajax);
        $view->assign('instance',$this->instance);
    }
    /**
     * load the given config keys from the DB. If config $configValues is not an array only 
     * this key is loaded. if $configValue is null ALL keys are loaded (attention thats heavy!)
     */
    protected function loadConfig($userId, $instance = 1, $configValues = null) {
    	
        addDebugOutput($this->boxName . ' - values from DB loaded', true);
        $DB = Database::getHandle();
        
        $toLoad = null;
                
        if(is_array($configValues)) {
            $toLoad = '(';
            
        	foreach($configValues as $c) {
        	   $toLoad .= $DB->quote($configValues) + ', ';
        	}
            
            $toLoad = substr($toLoad, 0, strlen($toLoad) - 2) . ') ';
        } else if(is_string($configValues)){
        	$toLoad = '(' . $DB->quote($configValues) . ')';
        }
        
        $query = 'SELECT id, data_key, data_value FROM ' . DB_SCHEMA . '.box_config WHERE ' .
                'box_id=' . $DB->quote($this->getBoxId()) . ' AND user_id=' . $DB->quote($userId) . 
                ' AND instance=' . $DB->quote($instance);
        if($toLoad != null) {
        	$query .= ' AND data_key IN ' . $toLoad; 
        }
        
        $res = $DB->execute($query);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach($res as $row) {
        	$this->boxConfig[$row['data_key']] = $row['data_value'];
            $this->boxConfigIds[$row['data_key']] = $row['id'];
        }
    }

    /**
     * cache the box id
     */    
    private function getBoxId() {
        /* cache box id */
        if(empty($this->boxId)) {
            $DB = Database::getHandle();
            $query = 'SELECT id FROM ' . DB_SCHEMA . '.box_type WHERE name=' . $DB->quote($this->boxName);
            $res = $DB->execute($query);
            
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }

            $this->boxId = $res->fields['id'];            
        }
        
        return $this->boxId;
    }
    /**
     * set a option for this box instance
     * 
     * @param string $key the value name
     * @param $value
     */
    protected function setConfigValue($key, $value) {        
        
        $sessionKey = 'box-' . $this->boxName . '-' . $this->instance . '-' . $key;
        Session::getInstance()->storeUserData($sessionKey, $value);
        
        /* we must look if the value in the db exist */
        if(empty($this->boxConfigIds[$key])){
        	$this->loadConfig(Session::getInstance()->getVisitor()->id, $this->instance, $key);
        }
        
        
        $stmt = '';
        $DB = Database::getHandle();
        
        $DB->StartTrans();
        
    	if(empty($this->boxConfigIds[$key])) {
    	   $stmt = 'INSERT INTO ' . DB_SCHEMA . '.box_config(box_id, user_id, instance, data_key, data_value) VALUES (' .
           $DB->quote($this->getBoxId()) .', ' . $DB->quote(Session::getInstance()->getVisitor()->id) 
           .', ' . $DB->quote($this->instance) .', ' . $DB->quote($key) . ', ' . $DB->quote($value) .')';
    	} else {
    		$stmt = 'UPDATE ' . DB_SCHEMA . '.box_config SET data_value=' . $DB->quote($value) . 
                    ' WHERE id=' . $DB->quote($this->boxConfigIds[$key]);
    	}        
        
        $res = $DB->execute($stmt);
            
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        if(empty($this->boxConfigIds[$key])) {
            $this->boxConfigIds[$key] = Database::getCurrentSequenceId($DB, 'box_config','id');
        }
        $this->boxConfig[$key] = $value;
        
        $DB->CompleteTrans();
    }
     /**
     * loads a option for this box instance
     * 
     * @param string $key the value name
     * @param $default - if no option find defind the default value
     */
    protected function getConfigValue($key, $default = '') {
        $sessionKey = 'box-' . $this->boxName . '-' . $this->instance . '-' . $key; 
        $data = Session::getInstance()->getUserData($sessionKey);
        if($data != null){
        	return $data;
        }
        
        if(!isset($this->boxConfig[$key])) {
            $visitor = Session::getInstance()->getVisitor();
            if($visitor->isExternal() || !$visitor->isLoggedIn())
                return $default;
            
            $this->loadConfig($visitor->id, $this->instance);
        }
            
        if(!isset($this->boxConfig[$key])) {
            $this->boxConfig[$key] = $default;
        }
        /* cache in Session */
        Session::getInstance()->storeUserData($sessionKey, $this->boxConfig[$key]);
           
        return $this->boxConfig[$key];
    }
    
    /**
     * returns true iff box with given name is to be displayed minimized
     * @param string $boxName box name to check for
     */
    private static function isMinimized($boxName, $instance = 1) {
        $user = Session::getInstance()->getVisitor();
        if (!$user->hasConfigBoxesMinimized()) {
            return false;
        }

        return strpos($user->getConfigBoxesMinimized(), $boxName . ':' . $instance) !== false;
    }
    
    /**
     * set methods that are allowed for calling by the controller
     */
    protected function getAllowedMethods() {
        return array_merge(parent::getAllowedMethods(),
            array (
                'maximize',
                'minimize',
                'close',
                  )
        );
    }
    
    /**
     * determines, whether page box arrangement must be reininitalized after this box
     * has been called
     * @return boolean
     */
    public function doesNeedReload() {
        return $_REQUEST['method'] == 'close';
    }
    
    public function maximize() {
        $user = Session::getInstance()->getVisitor();
        if (!$user->hasRight('FEATURE_BOX_REARRANGEMENT')) {
            return;
        }
        
        $this->minimized = false;
        
        // if no minimized entry exists, we have nothing to do
        if (!$user->hasConfigBoxesMinimized()) {
            return;
        }
        
        $user->setConfigBoxesMinimized(
            str_replace($this->boxName . ':' . $this->instance . ',',
            '',
            $user->getConfigBoxesMinimized()));
        $user->save();
    }
    
    public function minimize() {
        $user = Session::getInstance()->getVisitor();
        if (!$user->hasRight('FEATURE_BOX_REARRANGEMENT')) {
            return;
        }
        
        $this->minimized = true;
        
        // safely load existing "minimized" string
        if (!$user->hasConfigBoxesMinimized()) {
            $cookieString = '';
        } else {
            $cookieString = $user->getConfigBoxesMinimized();
        }
        
        $user->setConfigBoxesMinimized($cookieString . $this->boxName . ':' . $this->instance . ',');
        $user->save();
    }
    
    public function close() {
        $user = Session::getInstance()->getVisitor();
        if (!$user->hasRight('FEATURE_BOX_REARRANGEMENT')) {
            return;
        }
        
        $configLeft = null;
        $configRight = null;
        // if we have no user-specific setting, load default one first
        if (!$user->hasConfigBoxesLeft()) {
            $configLeft = BusinessLogicController::getDefaultBoxesLeft(true);
        } else {
            $configLeft = $user->getConfigBoxesLeft();
        }
        if (!$user->hasConfigBoxesRight()) {
            $configRight = BusinessLogicController::getDefaultBoxesRight(true);
        } else {
            $configRight = $user->getConfigBoxesRight();
        }
        
        // remove box from config
        $user->setConfigBoxesLeft(preg_replace('/' . $this->boxName . '\:' . $this->instance . ',?/', '', $configLeft));
        $user->setConfigBoxesRight(preg_replace('/' . $this->boxName . '\:' . $this->instance . ',?/', '', $configRight));
        $user->save();
    }
    
    abstract public function getView();
    /**
     * get all boxes wich allowed multi instance
     */
    public static function getAllMultiInstanceBoxNames(){
    	$DB = Database::getHandle();
        
        $stmt = 'SELECT name FROM ' . DB_SCHEMA . '.box_type 
                         WHERE multi_instance = true';
        
        $res = $DB->execute($stmt);
            
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $out = array();
        
        foreach($res as $row){
        	$out[$row['name']] = true;
        }
        
        return $out;
    }
}

?>
