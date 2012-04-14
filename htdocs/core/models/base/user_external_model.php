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

// $Id: user_external_model.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/user_external_model.php $

require_once MODEL_DIR . '/base/user_model.php';

class UserExternalModel extends UserModel {
	public $localId;
    protected $city;
    protected $remoteId;
    protected $username;
    
    /**
     * creates a new UserExternalModel
     * representing a user who has no valid account on local system 
     * the parameters are the only known values
     * 
     * @param int $localId id of user in this local system
     * @param string $username username of user
     * @param CityModel $city origin city of remote user
     * @param int $remoteId id of user on remote system
     */
    public function __construct($localId, $username, $city, $remoteId) {
        parent::__construct();
        $this->localId = $localId;
        
        // protect code that assumes all users are local
        // and therefore uses the id without internal/external check
        
        // deactivate this protection, every proper model has to have an id
        //        (linap, 08.06.2007)
        //$this->id = 0;
        $this->id = $localId;
         
        
        $this->username = $username;
        $this->city = $city;
        $this->remoteId = $remoteId;
        
        // an external user is no regular user
        $this->isRegularLocalUser = false;
        
        // user is external by definition
        $this->isExternal = true;
        
        // we don't assume, that user is currenty logged in
        $this->isLoggedIn = false;
 
        // we don't assume, that user has a blog
        $this->hasBlog = false;
        
        // initialize all data collections with dummies
        // parent method could access some properties defined in these models
        $this->userDetails = new UserDummyDataModel;
        $this->userData = $this->userDetails;
        $this->userContactData = $this->userDetails;
        $this->userExtraData = $this->userDetails;
        $this->userConfig = $this->userDetails;
        $this->userPrivacyData = $this->userDetails;
    }
    
    public function login() {
    	$this->isLoggedIn = true;
    }
    
    private static function getUserByCriterion($criterion) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, username,
                     city_id, external_id
                FROM ' . DB_SCHEMA . '.external_users
               WHERE ' . $criterion['criterion'] . '=' . $criterion['value'];
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // if no user is found, return null
        if ($res->EOF) {
            return null;
        }
        
        $user = new UserExternalModel($res->fields['id'],
                                      $res->fields['username'],
                                      CityModel::getCityById($res->fields['city_id']),
                                      $res->fields['external_id']);
        return $user;
    }
    
    /**
     * @param int $id id of user to fetch
     * @return UserExternalModel
     */
    public static function getUserById($id) {
    	$DB = Database::getHandle();
        return self::getUserByCriterion(array('criterion' => 'id',
                                              'value'     => $DB->Quote($id)) );
    }
    
    /**
     * gets user by username (case-<b>insensitive</b>)
     * 
     * @param string $username name of user to fetch
     * @param CityModel $city city in which to look for
     * @return UserExternalModel
     */
    public static function getUserByUsername($username, $city) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, username,
                     city_id, external_id
                FROM ' . DB_SCHEMA . '.external_users
               WHERE lower(username) = ' . $DB->Quote(strtolower($username)) . '
                 AND city_id = ' . $DB->Quote($city->id);
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // if no user is found, return null
        if ($res->EOF) {
            return null;
        }
        
        $user = new UserExternalModel($res->fields['id'],
                                      $res->fields['username'],
                                      CityModel::getCityById($res->fields['city_id']),
                                      $res->fields['external_id']);
        return $user;
    }
    
    /**
     * creates a model for the user by username (case-<b>insensitive</b>)
     * and inserts a copy of the user in the local database
     * 
     * @param string $username name of user to fetch
     * @param CityModel $city city in which to look for
     * @return UserExternalModel
     */
    public static function getNewUser($username, $city, $externalId) {
        $DB = Database::getHandle();
        
        $keyValue = array('username' => $DB->Quote($username),
                          'external_id' => $DB->Quote($externalId),
                          'city_id' => $DB->Quote($city->id));
        
        $res = null;
         
        $stmt = self::buildSqlStatement('external_users', $keyValue);
        $res = $DB->execute($stmt);

        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $id = Database::getCurrentSequenceId($DB, 'external_users', 'id');
        
        $user = new UserExternalModel($id,
                                      $username,
                                      $city,
                                      $externalId);

		// add to appropriate role
		RoleModel::getRoleByName("external_users")->addExternalUser($user);
		
        return $user;
    }
    
    /**
     * returns an associative array of users with specified ids
     * <b>note:</b> the array does <b>not</b> preserve the order of the given ids
     *
     * @param array $ids array of int: the ids of blog entries to retrieve
     * @return array associative array of UserExternalModel, the hash keys are the user ids
     * @throws DBException on database error
     */
    public static function getUsersByIds($ids) {
        // check, if we have ids to work on
        if (count($ids) == 0) {
            return array();
        }
        
        $DB = Database::getHandle();
        
        $q = 'SELECT id, username,
                     city_id, external_id
                FROM ' . DB_SCHEMA . '.external_users
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $users = array();
        foreach ($res as $k => $row) {
            $user = new UserExternalModel($row['id'],
                                          $row['username'],
                                          CityModel::getCityById($row['city_id']),
                                          $row['external_id']);
            
            $users[$user->localId] = $user;
        }
        
        return $users;
    }
    /**
     * gets a (Extern)UserModel by Input of intern or extern Key
     */
    public static function getUserByIntOrExtId($intId = null, $extId = null){
    	if ($intId === null and $extId === null){
    		return new UserAnonymousModel();
    	}
    	
    	if ($intId != null){
    		$user = UserProtectedModel::getUserById($intId);
            if ($user == null){
            	$user = new UserAnonymousModel();
            }
    		return $user;
    	}
    	
    	if ($extId != null){
    		return self::getUserById($extId);
    	}
    	
    	return null;
    }
    
    public static function getUsersByOnline() {
        $DB = Database::getHandle();
        
        $q = 'SELECT eu.id, username,
                     city_id, external_id
                FROM ' . DB_SCHEMA . '.external_users eu,
                     ' . DB_SCHEMA . '.user_online uo
               WHERE eu.id = uo.user_external_id
            ORDER BY online_since DESC';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $users = array();
        foreach ($res as $k => $row) {
            $user = new UserExternalModel($row['id'],
                                          $row['username'],
                                          CityModel::getCityById($row['city_id']),
                                          $row['external_id']);
            
            $users[] = $user;
        }
        
        return $users;
    }
    
    protected function reloadRights() {
        //$this->userRights = RightModel::getGrantedRoleRightsByRoleId(RoleModel::getRoleByName('external_users')->id);
        $this->userRights = RightModel::getGrantedUserRightsByUserId($this);
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function getCity() {
        return $this->city;
    }
    
    public function getUserpicFile($variant = '') { return null; }
    public function getGender() { return null; }
    public function getFlirtStatus() { return null; }
    public function getAge() { return null; }
    
    public function isInvisible() { return false; }
    public function isActive() { return true; }
    public function isActivated() { return false; }
    
    public function save() {
        // we don't have to change anything here at the moment
    }
    
    public function __wakeup() {
    	// nothing to wake up for know
        // TODO: RPC instead of IPC?
        return;
    }
    
    public function isSystemUser() {
        return false;
    }
}

?>
