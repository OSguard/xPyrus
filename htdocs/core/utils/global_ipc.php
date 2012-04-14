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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/global_ipc.php $

require_once CORE_DIR . '/utils/ipc.php';

/**
 * @class GlobalIPC
 * @brief allows exchange of certain flags among processes 
 * 
 * @author linap
 * @version $Id: global_ipc.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Utils
 */
class GlobalIPC extends IPC {
    /**
     * creates new GlobalIPC instance
     */
    public function __construct() {
        // create (unsigned long) key
        parent::__construct(SHARED_MEMORY_OFFSET);
        $this->flags = array(
            // flags used by templates/caches
            // 4 bytes wide for timestamp
            'SHOUTBOX_CHANGED' => 20,
            'NEW_USER' => 24,
            'FORUM_THREAD_CHANGED' => 28,
            'GROUP_CHANGED' => 32,
            'NEWS_CHANGED' => 36,
            'EVENT_CHANGED' => 40,
            'BLOG_CHANGED' => 44,
        );
    }
}

?>
