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

require_once './conf/config.php';
// do session data handling
// initialize session object once
require_once CORE_DIR . "/session.php";
Session::getInstance();

require_once MODEL_DIR . '/base/user_model.php';

// is a right check needed here?
// TODO: make this setting user configurable?
if (!Session::getInstance()->getVisitor()->isLoggedIn()) {
    return;
}

if (!array_key_exists('u', $_GET)) {
	die();
}

header('Content-Type: image/png');

// FIXME / OPTIMIZEME:
// use some sort of caching here for external users

$status = UserModel::isOnline($_GET['u']);
if ($status) {
	readfile(BASE . '/images/symbols/online.gif');
} else {
	readfile(BASE . '/images/symbols/offline.gif');
}

?>
