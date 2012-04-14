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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/ipc.php $

require_once CORE_DIR . '/utils/shared_memory.php';

/**
 * @class UserIPC
 * @brief allows exchange of certain flags among processes 
 * 
 * @author linap
 * @version $Id: ipc.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Models/User
 */
class IPC extends SharedMemory {
    /**
     * @var array
     * contains flags to be saved;
     * values of the array are the offsets in shared
     * memory to save at
     */
    protected $flags;
    
    /**
     * creates new IPC instance based on key
     * @param long $key
     */    
    public function __construct($key, $size = 100) {
        parent::__construct($key, $size);
        $this->flags = array();
    }
    
    /**
     * sets flag with given name (see UserIPC::$flags)
     * @param string $flagName
     */
    public function setFlag($flagName) {
        if (!array_key_exists($flagName, $this->flags)) {
            return false;
        }
        $offset = $this->flags[$flagName];
        $this->store(1, $offset);
    }
    
    /**
     * unsets flag with given name (see UserIPC::$flags)
     * @param string $flagName
     */
    public function unsetFlag($flagName) {
        if (!array_key_exists($flagName, $this->flags)) {
            return false;
        }
        $offset = $this->flags[$flagName];
        $this->store(0, $offset);
    }
    
    public function setTime($flagName) {
        if (!array_key_exists($flagName, $this->flags)) {
            return false;
        }
        $offset = $this->flags[$flagName];
        // store time() minus 1 in order to avoid "zero differences" later 
        $this->store(pack('L', time()-1), $offset);
    }
    
    public function getTime($flagName) {
        if (!array_key_exists($flagName, $this->flags)) {
            return false;
        }
        $offset = $this->flags[$flagName];
        $array = unpack('L', $this->get($offset, 4));
        return $array[1];
    }
    
    /**
     * checks, if flag with given name (see UserIPC::$flags) is set
     * @param string $flagName
     * @return boolean
     */
    public function isSetFlag($flagName) {
        if (!array_key_exists($flagName, $this->flags)) {
            return false;
        }
        $offset = $this->flags[$flagName];
        return $this->get($offset,1) == 1;
    }
    
    public function release() {
        parent::close();
    }
}

?>
