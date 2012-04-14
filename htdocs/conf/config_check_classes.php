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

abstract class Check {
    protected $name;
    protected $isSevere;
    
    public function __construct($name, $isSevere) {
        $this->name = $name;
        $this->isSevere = $isSevere;
    }
    
    public function isSevere() { return $this->isSevere; }
    public function getName() { return $this->name; }
    
    public function doSafeCheck() {
        return $this->doCheck(false);
    }
    
    public function doCheck($forceSuccess = true) {
        $ret = $this->_check();
        ///var_dump($this->isSevere, $forceSuccess, $ret);
        if ($this->isSevere && $forceSuccess && !$ret) {
            die ($this->name . " failed");
        } else if (!$ret) {
            $this->repair();
        }
        return $ret;
    }
    
    protected function repair() { }
    
    abstract protected function _check();
}

class ConstantCheck extends Check {
    protected $defaultValue;
    protected $constantName;
    public function __construct($constantName, $isSevere, $defaultValue) {
        parent::__construct("Checking for existence of constant $constantName", $isSevere);
        $this->defaultValue = $defaultValue;
        $this->constantName = $constantName;
    }
    
    protected function _check() {
        return defined($this->constantName);        
    }
    
    protected function repair() {
        if ($this->defaultValue !== null) {
            $lambda = create_function('', 'return ' . $this->defaultValue . ';');
            define($this->constantName, $lambda());
        }
    }
}

class NeccessaryConstantCheck extends ConstantCheck {
    protected $evilValue;
    public function __construct($constantName, $evilValue = null) {
        parent::__construct($constantName, true, null);
        $this->evilValue = $evilValue;
    }
    protected function _check() {
        return parent::_check() && (($this->evilValue !== null) 
                                            ? (constant($this->constantName) != $this->evilValue) 
                                            : true);
    }
}

class OptionalConstantCheck extends ConstantCheck {
    public function __construct($constantName, $defaultValue) {
        parent::__construct($constantName, false, $defaultValue);
    }
}

class PHPConfigCheck extends Check {
    protected $requiredValue;
    protected $configName;
    public function __construct($configName, $isSevere, $requiredValue) {
        $requiredValueString = $requiredValue;
        if (is_bool($requiredValueString)) {
            $requiredValueString = $requiredValueString ? 'On' : 'Off';
        }
        parent::__construct("Checking PHP config for $configName == $requiredValueString", $isSevere);
        $this->requiredValue = $requiredValue;
        $this->configName = $configName;
    }
    
    protected function _check() {
        return ini_get($this->configName) == $this->requiredValue;
    }
    
    protected function repair() {
        ini_set($this->configName, $this->requiredValue);
    }
}

class FatalPHPCheck extends PHPConfigCheck {
    public function __construct($configName, $requiredValue) {
        parent::__construct($configName, true, $requiredValue);
    }
}

class OptionalPHPCheck extends PHPConfigCheck {
    public function __construct($configName, $requiredValue) {
        parent::__construct($configName, false, $requiredValue);
    }
}

class PHPModuleCheck extends Check {
    protected $function;
    public function __construct($function) {
        parent::__construct("Checking existence of function $function", true);
        $this->function = $function;
    }
    
    protected function _check() {
        return function_exists($this->function);
    }
}

class ApacheModuleCheck extends Check {
    protected $module;
    public function __construct($module) {
        parent::__construct("Checking availability of Apache $module", true);
        $this->module = $module;
    }
    
    protected function _check() {
        return in_array($this->module, apache_get_modules());
    }
}

?>
