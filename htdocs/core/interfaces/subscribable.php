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

// $Id: subscribable.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/interfaces/subscribable.php $

interface Subscribable {
    /**
     * @param UserModel
     * @param string
     */
    public function addSubscriptor($user, $type);
    
    /**
     * @param UserModel
     */
    public function removeSubscriptor($user);
    
    /**
     * @return array of hashes ('user' => UserModel, 'type' => string) 
     */
    public function getSubscriptors();
    
    /**
     * @return string
     */
    public function getSubscription($user); 
}

?>
