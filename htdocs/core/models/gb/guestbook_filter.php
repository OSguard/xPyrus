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

// $Id: guestbook_filter.php 5743 2008-03-25 19:48:14Z ads $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/gb/guestbook_filter.php $

require_once MODEL_DIR . '/base/advanced_filter.php';

/**
 * the class provides (SQL) filter statements
 * for guestbook entries
 *  
 * @package Models
 * @subpackage GB
 */
class GuestbookFilter extends AdvancedFilter {
    public function __construct($options) {
        parent::__construct($options);
        // no tsearch for guestbook
        $this->tsearchAvailable = false;
    } 
}

?>
