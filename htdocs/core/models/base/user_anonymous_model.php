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

// $Id: user_anonymous_model.php 5760 2008-03-29 16:45:37Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/user_anonymous_model.php $

// validate that we have the config available
if (!defined('HAVE_CONFIG')) {
  die("please include config first<br />\n");
}

require_once MODEL_DIR.'/base/user_model.php';
require_once MODEL_DIR.'/user/user_dummy_data_model.php';

/**
 * model of an anonymous unihelp user (i.e. a guest, who is not logged in)
 * @package Models
 * @subpackage Base
 */
class UserAnonymousModel extends UserModel {
    const ANONYMOUS_USERNAME = 'JOHN DOE';
    
    /* only public to override old __get magic */    
    public $username;
    
    public function __construct() {
        parent::__construct();
        
        // anonymous user is _never_ logged in
        $this->isLoggedIn = false;
        
        // an anonymous user is no regular user
        $this->isRegularLocalUser = false;
        
        // our user_id will be 'false', so that other scripts will hopefully
        // work in a default mode ...
        $this->id = 0;
        $this->username = self::ANONYMOUS_USERNAME;
        
        // initialize all data collections with dummies
        // parent method could access some properties defined in these models
        $this->userDetails = new UserDummyDataModel;
        $this->userData = $this->userDetails;
        $this->userContactData = $this->userDetails;
        $this->userExtraData = $this->userDetails;
        $this->userConfig = $this->userDetails;
        $this->userRights = $this->userDetails;
        $this->userPrivacyData = $this->userDetails;
    }
    
    /**
     * is this user logged in?
     *
     * @return boolean
     */
    public function isLoggedIn() {
        // anonymous user is _never_ logged in
        return false;
    }
    
    /**
     * flag, if user is anonymous; defaults to true for <b>anonymous</b> users
     * @return boolean true, if user is anonymous
     */
    public function isAnonymous() {
        return true;
    }
    
    /**
     * mark user as logged in
     *
     * @return boolean
     */
    public function login() {
        // anonymous user is _never_ logged in
        $this->markAsOnline();
        return false;
    }
    
    /**
     * mark user as logged out
     */
    public function logout() {
        // anonymous user is _never_ logged in
        return;
    }
    
    /**
     * returns, if this user has named right
     *
     * @return boolean
     */
    public function hasRight($rightname) {
        // because our user_id is set to 'false',
        // rights will be correctly filled with default rights only
        
        // therefore it is enough here, to use function of parent class
        return parent::hasRight($rightname);
    }
        
    public function save() {
        // do nothing
    }
    
    public function getGroupMembership() {
        return array();
    }

	public function hasFirstName() {
		return false;
	}
	
	public function hasLastName() {
		return false;
	}
	
	public function hasPublicEmail() {
		return false;
	}
	
	public function getPoints() {
		return 0;
	}
	
	public function getForumEntries() {
		return 0;
	}
    
    public function isSystemUser() {
        return false;
    }
}

?>
