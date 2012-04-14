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

// $Id: notifier_factory.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/notifier_factory.php $

class NotifierFactory {
    // private constructor
    private function __construct() {}
    
    public static function createNotifierByName($name) {
        switch ($name) {
        case 'pm':
            include_once MODEL_DIR . '/pm/pm_entry_model.php';
            return new PmEntryModel;
        case 'email':
            include_once CORE_DIR . '/utils/mailer.php';
            return new Mailer;
        default:
            return null;
        }
    }    
}

?>
