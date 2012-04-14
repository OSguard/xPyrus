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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/user_ipc.php $

require_once CORE_DIR . '/utils/ipc.php';

/**
 * @class UserIPC
 * @brief allows exchange of certain user flags among processes 
 * 
 * @author linap
 * @version $Id: user_ipc.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Utils
 */
class UserIPC extends IPC {    
    /**
     * creates new UserIPC instance from username
     * @param string $username
     */    
    public function __construct($uid) {
        // we group 10 users for one IPC package
        $bundle = 10;
        // bytes per user
        $size = 90;
        
        parent::__construct(SHARED_MEMORY_OFFSET + 0x100000 - 1 - ((int) ($uid / $bundle)), $size * $bundle);
        
        $this->flags = array(
            // flags used by user model
            'GB_ENTRIES_CHANGED' => 0,
            'PMS_CHANGED' =>  1,
            'PMS_SENT_CHANGED' => 3,
            
            // flags used by templates/caches
            // 4 bytes wide for timestamp
            'FRIENDLIST_CHANGED' => 20,
            'COURSES_CHANGED' => 24,
            'DIARY_CHANGED' => 28,
            'GUESTBOOK_CHANGED' => 32,
            'GROUPS_CHANGED' => 36,
            'POINTS_CHANGED' => 40,
            'PROFILE_CHANGED' => 44,
            'CONTACT_CHANGED' => 48,
            'PRIVACY_CHANGED' => 52,
            'AWARD_CHANGED' => 56,
        );
        
        foreach ($this->flags as $key => &$val) {
            $val += $size * ($uid % $bundle);
        }
    }
}

?>
