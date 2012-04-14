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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/shared_memory.php $

/**
 * @class SharedMemory
 * @brief abstraction of shared memory
 * 
 * @author linap
 * @version $Id: shared_memory.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Utils
 */
class SharedMemory {
	/**
     * @var long
     * key which shared memory is referenced by
	 */
    protected $key;
    
    /**
     * @var int
     * handle of shared memory region
     */
    protected $handle;
    
    /**
     * @param long $key key to reference shm region by; if null, a random key is created
     * @param int $size size of shm region in bytes
     */
    public function __construct($key = null, $size = 100) {
    	// if key is null, create random handle
        if ($key == null) {
    		$key = unpack('V',substr(md5(uniqid(rand()),0,8)));
            $key = $key[1];
    	}
        $this->key = $key;
        
        $this->handle = shmop_open($key, 'c', 0644, $size);
        
        if (!$this->handle) {
            throw new CoreException(Logging::getErrorMessage(CORE_SHARED_MEMORY_ACQUIRE_FAILED, $key, $size));
        }
    }
    
    /**
     * store data at specified offset
     * @param mixed $data
     * @param int $offset offset in bytes
     * @throws CoreException if handle to shm is not available
     */
    protected function store($data, $offset) {
        shmop_write($this->handle, $data, $offset);
    }
    
    /**
     * reads length bytes from offset from shm
     * @param int $offset offset in bytes
     * @param int $length length in bytes
     * @return mixed
     * @throws CoreException if handle to shm is not available
     */
    protected function get($offset, $length) {
        return shmop_read($this->handle, $offset, $length);
    }
    
    /**
     * release shared memory access
     */
    protected function close() {
        shmop_close($this->handle);
    }
    
}

?>
