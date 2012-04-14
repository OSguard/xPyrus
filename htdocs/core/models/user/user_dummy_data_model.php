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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/user/user_dummy_data_model.php $

require_once CORE_DIR.'/interfaces/data_container.php';

/**
 * @class UserDummyDataModel
 * @brief class that implements the DataContainer in a trivial way (means: do nothing at all) 
 * 
 * @author linap
 * @version $Id: user_dummy_data_model.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @package Models/User
 */
class UserDummyDataModel implements DataContainer { 
    public function getValue($name) { return ''; }
    public function setValue($name, $value) { }
    
    public function reload() { }
    public function save() { }
    
    public function hasChanges() { return false; }

}

?>
