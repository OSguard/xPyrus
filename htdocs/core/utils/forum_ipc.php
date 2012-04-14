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

// $HeadURL: svn://unihelp.de/unihelp_dev/v2/trunk/htdocs/core/utils/user_ipc.php $

require_once CORE_DIR . '/utils/ipc.php';

/**
 * @class ForumIPC
 * @brief allows exchange of certain forum flags among processes 
 * 
 * @author linap
 * @version $Id: user_ipc.php 3977 2007-03-17 21:40:07Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Utils
 */
class ForumIPC extends IPC {    
    /**
     * creates new ForumIPC instance from forum id
     * @param int 
     */    
    public function __construct($forumId) {
        // we group 20 fora for one IPC package
        $bundle = 20;
        // bytes per forum
        $size = 20;
        
        parent::__construct(SHARED_MEMORY_OFFSET + 0x100 + ((int) ($forumId / $bundle)), $size * $bundle);
        
        $this->flags = array(
            // flags used by templates/caches
            // 4 bytes wide for timestamp
            'THREAD_CHANGED' => 0,
        );
        
        foreach ($this->flags as $key => &$val) {
            $val += $size * ($forumId % $bundle);
        }
    }
}

?>
