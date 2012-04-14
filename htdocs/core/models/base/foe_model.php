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
/*
 * Created on 18.03.2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
require_once MODEL_DIR.'/base/friend_model.php';

class FoeModel extends FriendModel {
	
	protected $friendIsFriend = false;
	
	/**
	 * creates a new FoeModel 
     * if parameters are given, it will be based on given user and friendship type
     * @param $friendOf UserModel user who is origin of the friendship
     * @param $user UserModel user the friend model is based on     
     */
    public function __construct($friendOf, $user = null) {
        parent::__construct($friendOf, $user, 'Ignore');
        
    }
    
    public function addToIgnorelist(){
    	
    	if (FriendModel::isFriendOf($this, $this->friendOf)){
			$foe = new FriendModel($this, $this->friendOf);
    		$foe->removeFromFriendlist();
    	}
    	
    	if (FriendModel::isFriendOf($this->friendOf, $this)){
    		$this->removeFromFriendlist();
    	}
    	
    	$this->addToFriendlist();
    }
    
    public function removeFromIgnorelist() {
    	$this->removeFromFriendlist();
    }
    public static function getFoesByUser($originUser, $orderBy = '', $onlineStatus = 'dontcare', $showFriends = true, $showFoes = false) {
    	return parent::getFriendsByUser($originUser, $orderBy, $onlineStatus, false, true); 
    }
	
}
?>
