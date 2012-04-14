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

// $Id: user_model.php 6210 2008-07-25 17:29:44Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/user_model.php $

// validate that we have the config available
if (!defined('HAVE_CONFIG')) {
  die("please include config first<br />\n");
}

require_once MODEL_DIR . '/base/base_model.php';
require_once MODEL_DIR . '/base/base_entry_model.php';
require_once MODEL_DIR . '/base/right_model.php';
require_once MODEL_DIR . '/base/role_model.php';
require_once MODEL_DIR . '/base/group_model.php';
require_once MODEL_DIR . '/base/university_model.php';
require_once MODEL_DIR . '/base/country_model.php';
require_once MODEL_DIR . '/base/study_path_model.php';
require_once MODEL_DIR . '/base/point_source_model.php';
require_once MODEL_DIR . '/base/feature_model.php';
require_once MODEL_DIR . '/user/user_data_model.php';
require_once MODEL_DIR . '/user/user_central_data_model.php';
require_once MODEL_DIR . '/user/user_main_data_model.php';
require_once MODEL_DIR . '/user/user_warning_model.php';

require_once CORE_DIR . '/constants/value_constants.php';
require_once CORE_DIR . '/utils/user_ipc.php';
require_once CORE_DIR . '/utils/attachment_handler.php';
require_once CORE_DIR . '/parser/parser_factory.php';
require_once CORE_DIR . '/interfaces/addressable_entity.php';

/**
 * @class UserModel
 * @brief model of an unihelp user
 * 
 * @author schnueptus, kyle, linap
 * @version $Id: user_model.php 6210 2008-07-25 17:29:44Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * The following properties are available:
 * 
 * <ul>
 * <li><var>id</var>                <b>int</b></li>
 * <li><var>username</var>          <b>string</b>   username</li>
 * <li><var>gender</var>            <b>string</b>   gender of user: 'm' or 'f' or ''</li>
 * <li><var>flirtStatus</var>       <b>string</b>   flirt status of user: 'red' or 'yellow' or 'green'</li>
 * <li><var>birthdate</var>         <b>date</b>     birthdate of user</li>
 * <li><var>signature</var>         <b>string</b>   parsed signature of user</li>
 * <li><var>signatureRaw</var>      <b>string</b>   raw signature of user</li>
 * <li><var>signatureParsed</var>   <b>string</b>   parsed signature of user</li>
 * <li><var>age</var>               <b>int</b>      age of user</li>
 * <li><var>uni</var>               <b>UniversityModel</b>   model of user's university</li>
 * <li><var>uniId</var>             <b>int</b>      id of user's university</li>
 * <li><var>userpicFile</var>       <b>string</b>   relative path to user's (big) user picture</li>
 * <li><var>smallUserpicFile</var>  <b>string</b>   relative path to user's small user picture variant</li>
 * <li><var>fancyUserpicFile</var>  <b>string</b>   relative path to user's fancy user picture variant</li>
 * <li><var>tinyUserpicFile</var>  <b>string</b>   relative path to user's tiny user picture variant</li>
 *  
 * <li><var>hasImJabber</var>       <b>boolean</b>  true iff user has a jabber account</li>
 * <li><var>imJabber</var>          <b>string</b>   jabber account; triggers notice, if not available</li>
 * <li><var>hasImICQ</var>          <b>boolean</b>  true iff user has a ICQ account</li>
 * <li><var>imICQ</var>             <b>string</b>   ICQ account; triggers notice, if not available</li>
 * <li><var>homepage</var>          <b>string</b>   user's homepage</li>
 * 
 * <li><var>publicPGPKey</var>      <b>boolean</b>  true iff pgp key fingerprint is available</li>
 * <li><var>hasPublicPGPKey</var>   <b>string</b>   pgp key fingerprint; triggers notice, if not available</li>
 *
 * <li><var>pms</var>               <b>int</b>      number of received private messages</li>
 * <li><var>pmsUnread</var>         <b>int</b>      number of received and unread private messages</li>
 * 
 * <li><var>forumLastVisit</var>    <b>string</b>   date/time string of user's last visit in a forum</li>
 *
 * <li><var>configFeatureSlots</var><b>int</b>      number of level feature slots that are available for this user
 * 
 * <li><var>points</var>            <b>int</b>      users level points</li>
 * <li><var>pointsEconomic</var>    <b>int</b>      users economic points 
 * </ul>
 * 
 * @package Models/Base
 */
class UserModel extends BaseModel implements AddressableEntity {
    /**
     * assocative array 
     * containing the user details according to main DB-table 'users'
     */
    protected $userDetails;
    /**
     * associative array
     * further user data (e.g. points) according to DB-table 'user_data'
     */
    protected $userData;
    /**
     * associative array
     * further user contact data (e.g. homepage) according to DB-table 'user_contact_data'
     */
    protected $userContactData;
    /**
     * associative array
     * further user configuration settings according to DB-table 'user_config'
     */
    protected $userConfig;
    /**
     * associative array
     * the keys are unihelp rights;
     * if and only if the value associated to a key is true, the right is granted
     */
    protected $userRights;
   
   /**
     * associative 2 dim array
     * the firt key is the group->id
     * the second keys are unihelp rights;
     * if and only if the value associated to a key is true, the right is granted
     */
    protected $userGroupRights;
        
    protected $userExtraData;
    
    protected $userPrivacyData;
    
    /**
     * @var array
     * ids of study path of this user; first entry is primary one
     */
    protected $studyPaths;
    /**
     * @var array
     * study path of this user; first entry is primary one
     */
    protected $studyPathsObj;
    
    /**
     * @var array
     * the courses this user has subscribed to
     */
    protected $courses;
    
    /**
     * @var boolean
     * flag, whether user is logged in
     */
    protected $isLoggedIn;
    /**
     * @var boolean
     * states, whether user belongs to this city
     * if true, user comes from a remote city
     */
    protected $isExternal;
    /**
     * @var string
     * activation string generated for this user
     */
    protected $activationString;
    
    /**
     * @var array
     * attributes of UserModel to save of ->save() operation
     */
    protected $valuesToSave;
    
    protected $warningCard;
    
    /**
     * @var int (timestamp)
     * time, when internal data has been refreshed last
     */
    protected $lastDataCacheReload = 0;
    /**
     * @var int
     * last time points cache was reloaded
     */
    protected $lastDataPointsReload = 0;
    /**
     * @var int (timestamp)
     * last set_user_online call
     */
    protected $lastMarkOnline = 0;
    
    /**
     * @var int
     * last time groups cache was reloaded
     */
    protected $lastGroupsReload = 0;
    
    protected $oldUsernames;
    
    /**
     * @var boolean
     * true, if user is regular local user (not anonymous, not external, not extraterrestrial ...)
     */
    protected $isRegularLocalUser;
    
    /**
     * @var array
     * user's groups
     */
    protected $groups;
    
    /**
     * cache, whether user has a blog
     * @var boolean
     */
    protected $hasBlog;
    
    /**
     * @var array
     * cache for those users models
     * that are fetched via getById method
     */
    protected static $_userCache = array();
    
    /**
     * @param $user UserModel|UserMainDataModel model to create this object from
     */
    public function __construct($user = null) {
        parent::__construct(0, 'users');
        // we don't know, if user is logged in
        $this->isLoggedIn = null;

        // user is regular by default
        $this->isRegularLocalUser = true;

        // if given user exists, copy
        if ($user!==null and $user instanceof UserModel) {
            $this->userDetails = $user->userDetails;
            $this->userData = $user->userData;
            $this->userContactData = $user->userContactData;
            $this->userExtraData = $user->userExtraData;
            $this->userConfig = $user->userConfig;
            $this->userRights = $user->userRights;
            $this->userPrivacyData = $user->userPrivacyData;
            $this->isLoggedIn = $user->isLoggedIn;
            $this->studyPaths = $user->studyPaths;
            $this->studyPathsObj = $user->studyPathsObj;
            $this->courses = $user->courses;
        	$this->isExternal = $user->isExternal;
            $this->id = $user->id;
            $this->isRegularLocalUser = $user->isRegularLocalUser;
            $this->groups = $user->groups;
        } 
        // when we have a data model, use it
        elseif ($user!==null and $user instanceof UserMainDataModel) {
        	$this->buildFromModel($user);
        }
        
        $this->valuesToSave = array();
        
        // user is local by default
        $this->isExternal = false;
    }
    
    /**
     * creates a new user from basic data
     * @param string username
     * @param string <b>unencrypted</b> password
     * @param int id of university
     * @param int id of person type (optional)
     */
    public static function createFromRegisterData($username, $password, $uniId, $personType = 1) {
        $user = new UserModel;
        $user->buildFromModel(UserMainDataModel::createFromRegisterData($username, self::encryptPassword($password), $uniId, $personType));
        return $user;
    }    
    
    /**
     * @param $model UserMainDataModel
     */
    protected function buildFromModel($model) {
        $this->userDetails      = $model;
        $this->id               = $model->id;
        $this->userData         = new UserDataModel($this->id, 'user_data');
        $this->userConfig       = new UserDataModel($this->id, 'user_config');
        $this->userContactData  = new UserDataModel($this->id, 'user_contact_data');
        $this->userPrivacyData  = new UserDataModel($this->id, 'user_privacy');
        $this->userExtraData    = new UserCentralDataModel($this->id, 'user_extra_data');
    }
    
    
    /**
     * @param int $id id of user to fetch
     * @param boolean $showValidOnly if true, invisible, inactive and inactivated user are hidden
     * @return UserModel or null
     * 
     * <b>NOTICE:</b> if $showValidOnly if true it can bring null back
     */    
    public static function getUserById($id, $showValidOnly = true) {    	
        if (!array_key_exists($id, self::$_userCache)) {
            $model = UserMainDataModel::getUserById($id, $showValidOnly);
            // if user main data model is not valid,
            // user model can neither be valid
            if ($model == null) {
            	return null;
            }
            $user = new UserModel;
        	$user->buildFromModel($model);
            
            self::$_userCache[$id] = $user;
        }
        return self::$_userCache[$id];
    }
    
    /**
     * gets user by username (case-<b>insensitive</b>)
     * @param string $username
     * @param boolean $showValidOnly if true, invisible, inactive and inactivated user are hidden
     * @return UserModel
     */    
    public static function getUserByUsername($username, $showValidOnly = true) {
        $model = UserMainDataModel::getUserByUsername($username, $showValidOnly);
        // if user main data model is not valid,
        // user model can neither be valid
        if ($model == null) {
            return null;
        }
        $user = new UserModel;
        $user->buildFromModel($model);
        return $user;
    }
    
    /**
     * checks, if given email address is already used by a user
     * @param string $emailAddress mail address to check
     * @return boolean
     */
    public static function doesUniEmailAddressExist($emailAddress) {
        $DB = Database::getHandle();
        $q = 'SELECT COUNT(id) AS nr
                FROM '.DB_SCHEMA.'.user_extra_data
               WHERE LOWER(uni_email) = ' . $DB->Quote(strtolower($emailAddress));
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // count should always return a valid result ... 
        if ($res->EOF) {
            throw new DBException(DB_COUNT_FAILED);
        }
        
        return $res->fields['nr'] > 0;
    }
    
    /**
     * count the active, activated and vibible users
     * @return int
     */
    public static function countUser($options = array()) {
        $DB = Database::getHandle();
        $q = 'SELECT COUNT(id) AS nr
                FROM '.DB_SCHEMA.'.users
               WHERE flag_active = true
                 AND flag_activated = true
                 AND flag_invisible = false';
        
        $optionsWhitelist = array('gender' => 1);
        
        if (!empty($options)) {
            foreach($options as $key => $op) {
                if (!array_key_exists($key, $optionsWhitelist)) {
                    continue;
                }
        		$q .= ' AND '. $key . ' = ' . $DB->quote($op);
        	}           
        }
        //var_dump($q);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // count should always return a valid result ... 
        if ($res->EOF) {
            throw new DBException(DB_COUNT_FAILED);
        }
        
        return $res->fields['nr'];
    }
    
    /**
     * returns user model for user with given username and password,
     * if existing and user is activated
     * <b>note:</b>username is case-insensitive, whereas password is case-sensitive
     * @param string username
     * @param string <b>unencrypted</b> password
     */
    public static function getUserByUsernamePassword($username, $password) {
        $model = UserMainDataModel::getUserByUsernamePassword($username, self::encryptPassword($password));
        // if user main data model is not valid,
        // user model can neither be valid
        if ($model == null) {
            return null;
        }
        $user = new UserModel;
        $user->buildFromModel($model);
        return $user;
    }
    
    /**
     * returns user model for user with given (cookie) identifier,
     * if existing and user is activated
     * <b>note:</b>username is case-sensitive
     */
    public static function getUserByUsernameCookie($identifier) {
        $model = UserMainDataModel::getUserByUsernameCookie($identifier);
        // if user main data model is not valid,
        // user model can neither be valid
        if ($model == null) {
            return null;
        }
        $user = new UserModel;
        $user->buildFromModel($model);
        return $user;
    }
    
    /**
     * returns user model of randomly chosen user who has got
     * a non empty user picture
     *
     * @return UserModel
     * @throws DBException on DB error
     */
    public static function getUserByRandom() {
        $user = new UserModel;
        $data = UserMainDataModel::getUserByRandom();
        if($data == null){
        	return null;
        }
        $user->buildFromModel($data);
        return $user;
    }
    
    /**
     * returns user model of the newest user     
     *
     * @return UserModel
     * @throws DBException on DB error
     */
    public static function getUserByNewest() {
        $user = new UserModel;
        $data = UserMainDataModel::getUserByNewest();
        if($data == null){
            return null;
        }
        $user->buildFromModel($data);
        return $user;
    }    
    
    public static function getUserByActivationString($activationString) {
    	$model = UserMainDataModel::getUserByActivationString($activationString);
        // if user main data model is not valid,
        // user model can neither be valid 
    	if ($model == null) {
    		return null;
    	}
        
    	$user = new UserModel;
        $user->buildFromModel($model);
        $user->activationString = $activationString;
        return $user;
    }
    
    public static function getUserByCanvass($canvassCode) {
        $model = UserMainDataModel::getUserByCanvass($canvassCode);
        // if user main data model is not valid,
        // user model can neither be valid 
        if ($model == null) {
            return null;
        }
        
        $user = new UserModel;
        $user->buildFromModel($model);
        return $user;
    }
    
    public static function getUsersByBirthday($showInvisible = false, $day = false, $month = false, $year = false) {
    	$users = array();
        $userMainModels = UserMainDataModel::getUsersByBirthday($showInvisible, $day, $month, $year);
        foreach ($userMainModels as $um) {
        	$user = new UserModel;
            $user->buildFromModel($um);
            array_push($users, $user);
        }
        return $users;
    }
    
    /**
     * gives a list of currently online users
     * 
     * @param boolean $showInvisible if false, only users marked as visible will be returned
     * @param string $orderBy may be 'gender', 'birthdate', 'username', 'status'
     */
    public static function getUsersByOnline($showInvisible = false,
                 $orderBy = '') {
        $users = array();
        $userMainModels = UserMainDataModel::getUsersByOnline($showInvisible, $orderBy);
        foreach ($userMainModels as $key => $um) {
            $user = new UserModel;
            $user->buildFromModel($um);
            array_push($users, $user);
        }
        return $users;
    }
    
    /**
     * $crit may be one of
     *  - forumEntries
     *  - courseUploads
     *  - courseDownloads
     *  - onlineActivity
     */
    public static function getTopUsers($crit) {
        $users = array();
        $userMainModels = UserMainDataModel::getTopUsers($crit);
        foreach ($userMainModels as $key => $um) {
            $user = new UserModel;
            $user->buildFromModel($um);
            array_push($users, $user);
        }
        return $users;
    }
    
    /**
     * returns all users who have marked themselfes for deletion
     * 
     * @return array of array('user' => UserModel, 'reason' => string)
     */
    public static function getUsersToDelete() {
        $DB = Database::getHandle();
        $q = 'SELECT user_id, comment, extract(epoch from insert_at) as insert_at
                FROM ' . DB_SCHEMA . '.user_recycle
            ORDER BY id ASC';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $tempUserIds = array();
        $usersToDelete = array();
        foreach ($res as $row) {
            array_push($usersToDelete, array('user' => $row['user_id'], 'reason' => $row['comment'], 'insertAt' => $row['insert_at']));
            array_push($tempUserIds, $row['user_id']);
        }
        $tempUsers = UserModel::getUsersByIds($tempUserIds, '', false);
        foreach ($usersToDelete as &$u) {
            $u['user'] = $tempUsers[$u['user']];
        }
        
        return $usersToDelete;
    }
    
    public static function getSystemUser() {
        return self::getUserById(SYSTEM_USER_ID, false);
    } 
    
    public static function getAnonymousUserOnlineNumber() {
        $DB = Database::getHandle();
        $q = 'SELECT COUNT(id) AS nr
                FROM ' . DB_SCHEMA . '.user_online
               WHERE user_id IS NULL
                 AND online_since > NOW() - \'' . (int) V_USER_ONLINE_DISPLAY_TIMEOUT . ' minutes\'::interval';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // count should always return a valid result ... 
        if ($res->EOF) {
            throw new DBException(DB_COUNT_FAILED);
        }
        
        return $res->fields['nr'];
    }
    
    public static function getTotalUserOnlineNumber() {
        $DB = Database::getHandle();
        $q = 'SELECT COUNT(DISTINCT(session_id)) AS nr
                FROM ' . DB_SCHEMA . '.user_online
               WHERE online_since > NOW() - \'' . (int) V_USER_ONLINE_DISPLAY_TIMEOUT . ' minutes\'::interval';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // count should always return a valid result ... 
        if ($res->EOF) {
            throw new DBException(DB_COUNT_FAILED);
        }
        
        return $res->fields['nr'] + self::getAnonymousUserOnlineNumber();
    }
    
    /**
     * returns an associative array of users with specified ids
     * <b>note:</b> the array does <b>not</b> preserve the order of the given ids
     *
     * @param array $ids array of int: the ids of users to retrieve
     * @param string criterion to order results by; default no order; may be 'username' (lower-case usernames)
     * @return array associative array of UserModel, the hash keys are the user ids
     * @throws DBException on database error
     */
    public static function getUsersByIds($ids, $order='', $showValidOnly = true) {
        $users = array();
        $userMainModels = UserMainDataModel::getUsersByIds($ids, $order, $showValidOnly);
        foreach ($userMainModels as $key => $um) {
            $user = new UserModel;
            $user->buildFromModel($um);
            $users[$key] = $user;
        }
        return $users;
    }
    
    /**
     * returns an associative array of users with specified usernames
     * <b>note:</b> the array does <b>not</b> preserve the order of the given ids
     *
     * @param array $usernames array of string: the usernames of users to retrieve
     * @param string criterion to order results by; default no order; may be 'username'
     * @return array associative array of UserModel, the hash keys are the user ids
     * @throws DBException on database error
     */
    public static function getUsersByUsernames($usernames, $order='') {
        $users = array();
        $userMainModels = UserMainDataModel::getUsersByUsernames($usernames, $order);
        foreach ($userMainModels as $um) {
            $user = new UserModel;
            $user->buildFromModel($um);
            array_push($users, $user);
        }
        return $users;
    }
    
    /**
     * searches for a user whose username contains the given string
     *
     * @param string $subString string that username must contain
     * @param int $limit maximal number of results
     * @param boolean $showValidOnly if true, only valid (activated, visible) users
     *   are returned (defaults to true)
     * @return array array of UserModel, ordered by username ascending
     * @throws DBException on DB error
     */
    public static function searchUser($subString, $limit=30, $showValidOnly=true, $searchOldNicks = false) {
        $users = array();
        $userMainModels = UserMainDataModel::searchUser($subString, $limit, $showValidOnly, $searchOldNicks);
        foreach ($userMainModels as $um) {
            $user = new UserModel;
            $user->buildFromModel($um);
            array_push($users, $user);
        }
        return $users;
    }

    public static function searchByEmail($email, $limit=30) {
        $users = array();
        $userMainModels = UserMainDataModel::searchByEmail($email, $limit);
        foreach ($userMainModels as $um) {
            $user = new UserModel;
            $user->buildFromModel($um);
            array_push($users, $user);
        }
        return $users;
    }
    
    /**
     * searches for a user by various options
     *
     * @param string $subString string that username must contain
     * @param int $studyPathId id of study path; may be 0 for "don't care"
     * @param string $flirtStatus '0' for "don't care", otherwise (red|yellow|green)
     * @param string $orderBy sort criterion: 'pointsDesc', 'pointsAsc'
     * @param string $orderBy sort criterion: 'pointsDesc', 'pointsAsc'
     * @param boolean $showValidOnly if true, only valid (activated, visible) users
     *   are returned (defaults to true)
     * @return array array of UserModel, ordered by given criterion
     * @throws DBException on DB error
     */
    public static function searchUserAdvanced($subString, $studyPathId, $gender, $flirtStatus, $picture, $uni, $order_by, $order, $limit, $offset = 0,$showValidOnly=true) {
        $users = array();
        $userMainModels = UserMainDataModel::searchUserAdvanced($subString, $studyPathId, $gender, $flirtStatus, $picture, $uni, $order_by, $order, $limit, $offset, $showValidOnly);
        foreach ($userMainModels as $um) {
            $user = new UserModel;
            $user->buildFromModel($um);
            array_push($users, $user);
        }
        return $users;
    }
    
    
    /**
     * is this user logged in?
     *
     * @return boolean
     */
    public function isLoggedIn() {
        // if we don't know anything about online status, look into db first
        if ($this->isLoggedIn === null) {
            $this->loadOnlineData();
        }
        return $this->isLoggedIn;
    }
    
    /**
     * flag, if user is anonymous; defaults to false for <b>all</b> normal users
     * @return boolean true, if user is anonymous
     */
    public function isAnonymous() {
    	return false;
    }
    
    /**
     * flag, if user is regular local user (not anonymous, not external, not extraterrestrial ...)
     * @return boolean
     */
    public function isRegularLocalUser() {
        return $this->isRegularLocalUser;
    }
    
    /**
     * true, if parameter represents the same object as $this
     * @param $user UserModel or int
     * @return boolean
     */
    public function equals($user) {
    	if ($user instanceof UserModel) {
            return ($user->id == $this->id and $this->getUniId() == $user->getUniId());
        } else if (!($this instanceof UserExternalModel)) {
            return ($this->id == $user);
        }
        return false;
    }
    
    /**
     * mark user as logged in
     *
     * @return boolean
     */
    public function login() {
        $this->isLoggedIn = true;
        
        $this->setLastLogin(time());
        
        $this->markAsOnline();        
    }
    
    public function copyLoginStateFromUser($user) {
        $this->isLoggedIn = $user->isLoggedIn;
    }
    
    /**
     * marks this user as online, if he is logged in and is not external
     * 
     * @note if user has not been online (user_online-table) before,
     *       reload of user's rights is enforced
     */
    public function markAsOnline() {
    	// if we are not logged in or are external, we need no action
        /*if (!$this->isLoggedIn or $this->isExternal) {
    		return;
    	}*/
        
        if ($this->id == 0) {
            return;
        }
        
        // we need not to call set_user_online every 23 seconds ...
        $now = time();
        if ($this->lastMarkOnline + V_USER_MODEL_ONLINE_LIFETIME > $now) {
            return;
        }
        $this->lastMarkOnline = $now;
        
        $DB = Database::getHandle();
    	// mark user as online
        if ($this->isExternal) {
            $q = 'SELECT ' . DB_SCHEMA . '.set_user_online(
                         0, ' . $DB->Quote($this->id) . ',
                         ' . $DB->Quote(Session::getInstance()->getSessionId()) . ') AS status';
        } else {
            $q = 'SELECT ' . DB_SCHEMA . '.set_user_online(
                         ' . $DB->Quote($this->id) . ', 0,
                         ' . $DB->Quote(Session::getInstance()->getSessionId()) . ') AS status';
        }
        $res = $DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // DB-function returns 1 on new entry
        // and 2 on existing entry

        // if we have not been online before, enforce rights reload
        if ($res->fields['status'] == 1) {
            $this->userRights = null;
        }
    }
    
    /**
     * removes this user from user online list
     * @throws DBException
     */
    public function removeFromUserOnlineList() {
        // delete from user online list 
        $DB = Database::getHandle();
        if ($this->isExternal) {
            $q = 'DELETE FROM ' . DB_SCHEMA . '.user_online
                        WHERE user_external_id=' . $DB->Quote($this->id);
        } else {
            $q = 'DELETE FROM ' . DB_SCHEMA . '.user_online
                        WHERE user_id=' . $DB->Quote($this->id);
        }
        $res = $DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // reset online status cache
        $this->lastMarkOnline = time() - 2 * V_USER_MODEL_ONLINE_LIFETIME;
        
        // reset rights cache
        $this->userRights = null;
    }
    
    /**
     * mark user as logged out
     * @throws DBException
     */
    public function logout() {
        $this->isLoggedIn = false;        
        $this->removeFromUserOnlineList();
    }
    /**
     * test if the User have enough points
     */
    public function hasEnoughPoints($pointValue, $multiplicator = 1){
    	$ps = PointSourceModel::getPointSourceByName($pointValue);

        // it is important to call 'getPointsEconomic' of UserModel, because UserProtectedModel
        // protects getPointsEconomic by privacy level
        if (self::getPointsEconomic() * GlobalSettings::getGlobalSetting('POINT_SOURCES_FLOW_MULTIPLICATOR')
                < -1 * $multiplicator * $ps->getPointsFlow()) { 
            return false;
        }
        
        return true;
    }
          
    /**
     * @return DetailsVisibleModel
     */
    public function detailVisible($categoryName) {
        $visibleId = $this->userPrivacyData->getValue($categoryName);
        return DetailsVisibleModel::getDetailsVisibleById($visibleId);
    }
    
    /**
     * returns name of corresponding DetailsVisibleModel; wrapper for detailVisible
     * @return string
     */
    public function detailVisibleName($categoryName) {
        $model = $this->detailVisible($categoryName);
        if ($model) {
            return $model->name;
        }
        return '';
    }
    /**
     * returns, if this user has named right
     *
     * @return boolean
     */
    public function hasRight($rightname) {
        // if we have no rights information, load them now
        if ($this->userRights === null) {
            $this->reloadRights();
        }
        
        //var_dump($rightname); var_dump($this->userRights);
        
        // existence of corresponding entry in right array
        // suffices to prove that right is granted
        return array_key_exists($rightname, $this->userRights);
    }
    
    public function hasGroupRight($rightname, $groupId = null){
        // if we have no rights information, load them now
        if ($this->userGroupRights === null) {
            $this->reloadRights();
        }
        //echo $groupId; var_dump($this->userGroupRights);
        /* if no group rights exist return false */
        if ($this->userGroupRights == null) {
            return false;
        }
        
        /* if some group has this right */
        if ($groupId == null){
        	foreach($this->userGroupRights as $group){
        		if(array_key_exists($rightname, $group)){
        			return true;
        		}
        	}
            return false;
        }
        
        /* if user have no rights in this group */
        if (!array_key_exists($groupId, $this->userGroupRights)) {
        	return false;
        }
        /* check the right */
        return array_key_exists($rightname, $this->userGroupRights[$groupId]);
    }
    /**
     * get the groupMembership filtert by a rightname
     * 
     * @param string $rightname
     * 
     * @return array GroupModel description
     */
    public function getGroupsByRight($rightname){
    	// if we have no rights information, load them now
        if ($this->userGroupRights === null) {
            $this->reloadRights();
        }
        /* if no group rights exist return empty array */
        if($this->userGroupRights == null){
            return array();
        }
        $groupList = $this->getGroupMembership();
        
        $groups = array();
        foreach($this->userGroupRights as $k => $group){
            if(array_key_exists($rightname, $group)){
                $groups[] = $groupList[$k];
            }
        }
        //var_dump($groups);
        return $groups;   
    }
    
    /**
     * returns a list of the role ids this user is member of
     *
     * @return array array of int
     */
    public function getRoleIds() {
        $DB = Database::getHandle();
        
        $q = 'SELECT role_id
                FROM '.DB_SCHEMA.'.user_role_membership
               WHERE user_id=' . $DB->Quote($this->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $roleIds = array();
        foreach ($res as $row) {
            array_push($roleIds, $row['role_id']);
        }
        
        return $roleIds;
    }
    
    /**
     * (re)loads all user/role-rights and group-rights of this user
     */
    protected function reloadRights() {
        $userRights = RightModel::getGrantedUserRightsByUserId($this);
        //var_dump($userRights);
        $userFeatures = FeatureModel::getAllFeatureIdsByUser($this->id);
        $allFeatures = FeatureModel::getAllFeatureIds();
        // collect user rights
        $this->userRights = array();
        foreach ($userRights as $name => $right) {
            if (!array_key_exists($right->id, $allFeatures) or
                    array_key_exists($right->id, $userFeatures)) {
                $this->userRights[$name] = $right;
            }
        }
        // get rights with right names as keys
        $this->userGroupRights = RightModel::getAllExplicitUserGroupRightsByUser($this, 'name');
    }
    
    protected function parseTextElements() {
        // settings: always format with BBCode and smileys
    	$ps_array = ParserFactory::createParserFromSettings(
            array(BaseEntryModel::PARSE_AS_FORMATCODE => true,
                  BaseEntryModel::PARSE_AS_SMILEYS => true));

        $signature = $this->userData['signature_text']->value;
        $description = $this->userData['description']->value;
        // parse iteratively
        // apply parsers from ParseStrategy-Array
        foreach ($ps_array as $parseStrategy) {
            $signature = $parseStrategy->parse($signature);
            $description = $parseStrategy->parse($description);
        }
        
        $this->userDetails['description'] = $description;
        $this->userDetails['signature']   = $signature;
    }
    
    /**
     * sets $number of own guestbook entries as read
     * @note save method has to be called separately
     * @param int
     */
    // no longer used (linap, 17.03.2007)
    /*public function setReadGBEntries($number) {
        // if we are not logged in, we have nothing to do
        if (!$this->isLoggedIn) {
            return;	
        }
        
    	$this->userData->increaseValue('gb_entries_unread', -$number);
        //var_dump($this->userData);
    }*/
    
    protected function loadOnlineData() {
        if ($this->isExternal) {
            // we don't know anything about external users
            $this->isLoggedIn = false;
            return;
        }
        
        $DB = Database::getHandle();
        
        $q = 'SELECT COUNT(id) AS c
                FROM ' . DB_SCHEMA . '.user_online
               WHERE user_id = ' . $DB->Quote($this->id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        if ($res->fields['c']>0) {
        	$this->isLoggedIn = true;
        } else {
        	$this->isLoggedIn = false;
        }
    }
    
    protected function loadStudyData() {
        if ($this->isExternal) {
            // we don't know anything about external users
            $this->studyPaths = array();
            return;
        }
        
        $DB = Database::getHandle();
        // get id of study path
        $q = 'SELECT study_path_id
                FROM ' . DB_SCHEMA . '.study_path_per_student
               WHERE user_id = ' . $DB->Quote($this->id) . '
            ORDER BY primary_course DESC';
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $studyPaths = array();
        foreach ($res as $row) { 
            array_push($studyPaths, $row['study_path_id']);
        }

        $this->studyPaths = $studyPaths;
    }    
    
    protected function __set($name, $value) {
    	switch ($name) {
        /*case 'signature':
                   // need to clear parsed signature here
                   $this->userDetails->setValue('signature_parsed', ''); 
            return $this->userDetails->setValue('signature_raw', $value);
        case 'description':
                   // need to clear parsed description here
                   $this->userData->setValue('description_parsed', ''); 
            return $this->userData->setValue('description_raw', $value);
        
        case 'nationalityId':
                   // need to clear prefetched nationality object
                   // without marking it for database-save-operation
                   $this->userDetails->setValue('nationality_obj', null, false); 
            return $this->userDetails->setValue('nationality_id', $value);
        case 'countryId':
                   // need to clear prefetched country object
                   // without marking it for database-save-operation
                   $this->userDetails->setValue('country_obj', null, false); 
            return $this->userDetails->setValue('country_id', $value);
        case 'uniId':
                   // need to clear prefetched uni object
                   // without marking it for database-save-operation
                   $this->userDetails->setValue('uni_obj', null, false); 
            return $this->userDetails->setValue('uni_id', $value);*/
        /*case 'studyPathsId':
            // need to clear prefetched uni object
            // without marking it for database-save-operation
            $this->studyPathsObj = null;
            //var_dump($this->studyPaths);
            $this->studyPaths = $value;
            //var_dump($this->studyPaths);
            $this->valuesToSave['studyPaths'] = true; 
            return true;*/
        
        // special treatment of booleans; they are internally represented as 't', 'f' strings 
        /*case 'flagActivated':
            return $this->__set('_flagActivated', Database::makeBooleanString($value));
        case 'flagActive':
            return $this->__set('_flagActive', Database::makeBooleanString($value));
        case 'flagInvisible':
            return $this->__set('_flagInvisible', Database::makeBooleanString($value));*/
        /*case 'isGBpublic': 
            return $this->__set('_gb_public', Database::makeBooleanString($value));*/
        default:
            if (array_key_exists($name, self::$props)) {
                $p = self::$props[$name][0]; 
                $q = self::$props[$name][1];
                //echo "set: $p - $q - $name - $value"; 
                return $this->$p->setValue($q, $value);
            }
    	}
    }
    
    protected function __get($v) {
    	// TODO: replace this automagic property
        //       with a normal class property
        if ($v == 'username') {
    	    return $this->userDetails->getValue('username');
    	}    	
    }
    
    /**
     * method executed after unserialize()
     */
    public function __wakeup() {
    	// check, if some stats have been changed
        // and have to be reloaded

        // create IPC model
        $userIPC = new UserIPC($this->id);
        
        if ($userIPC->isSetFlag('GB_ENTRIES_CHANGED')) {
            $this->userData->reload('gb_entries_unread');
            $this->userData->reload('gb_entries');
            // also reload points
            $this->reloadPoints();

            // reset flag after reload
            $userIPC->unsetFlag('GB_ENTRIES_CHANGED');
        }
        
        if ($userIPC->getTime('POINTS_CHANGED') > $this->lastDataPointsReload) {
            $this->reloadPoints();
            $this->lastDataPointsReload = time();
        }
        
        if ($userIPC->getTime('GROUPS_CHANGED') > $this->lastGroupsReload) {
            $this->groups = null;
            $this->lastGroupsReload = time();
        }
        
        if ($userIPC->isSetFlag('PMS_CHANGED')) {
            $this->userData->reload('pms_unread');
            $this->userData->reload('pms');
            
            // reset flag after reload
            $userIPC->unsetFlag('PMS_CHANGED');
        }
        
        if ($userIPC->isSetFlag('PMS_SENT_CHANGED')) {
            $this->userData->reload('pms_sent');
            
            // reset flag after reload
            $userIPC->unsetFlag('PMS_SENT_CHANGED');
        }
        
        // check if data cache lifetime has expired
        if (time() - V_USER_MODEL_DATA_CACHE_LIFETIME > $this->lastDataCacheReload) {
            // reload some data
            $this->userData->reload('pms_unread');
            $this->userData->reload('gb_entries_unread');

            $this->reloadPoints();
            
            // TODO: per-property reload flag?
            $this->lastDataCacheReload = time();
        }
        
    }

    /**
     * increase unihelp level points by $lPoints and
     * economic points by $wPoints
     * @note economic points are taken "as is" and are not scaled!
     * 
     * @param int $lPoints
     * @param int $wPoints
     */
    public function increaseUnihelpPoints($lPoints, $wPoints) {
        if ($lPoints == 0 and $wPoints == 0) {
            // don't cause not neccessary DB action
            return;
        }
        $this->userDetails->increaseValue('points_sum', $lPoints);
        $this->userDetails->increaseValue('points_flow', $wPoints);
    }
    
    /**
     * increment login error counter
     */
    /*public function incrementLoginErrors() {
        $this->userDetails->increaseValue('login_errors', 1);
    }*/
    
    /**
     * increment profile views
     */
    public function incrementProfileViews() {
        $this->userData->increaseValue('profile_views', 1);
    }
    
    /**
     * increase feature update slots
     */
    public function incrementFeatureSlots($delta) {
        $this->userConfig->increaseValue('feature_free_update_slots', $delta);
    }
    
    public function reloadPoints(){
        $this->userDetails->reload('points_sum, points_flow');
    }
    
    /*protected function reloadProperty($name){
    	$p = self::$props[$name];
        $this->$p[0]->reload();
    }*/
    
    public function getWarningCard() {
        if ($this->warningCard !== null) {
        	if ($this->warningCard == 0) {
                return null;
            }
            // check, if warning card has expired
            if ($this->warningCard->hasExpired()) {
        		$this->warningCard = null;
                return null;
        	}
            
            return $this->warningCard;
        }
        
        $this->warningCard = UserWarningModel::getLatestWarningByUser($this);
        $retWarningCard = $this->warningCard;
        
        if ($this->warningCard == null) {
        	// mark via 0 that we have no warning
            $this->warningCard = 0;
        }
        
        return $retWarningCard;
    }
    
    /**
     * checks, if user is member of the specified group
     * @param int $groupId id of group
     * @return boolean
     */
    public function isMemberOfGroup($groupId) {
        $DB = Database::getHandle();
        $q = 'SELECT COUNT(g.id) AS nr
                FROM '.DB_SCHEMA.'.user_group_membership AS m, '
                      .DB_SCHEMA.'.groups AS g   
               WHERE m.user_id = ' . $DB->Quote($this->id) . ' 
                 AND g.id = m.group_id
                 AND g.id = ' . $DB->Quote($groupId);
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
  
        return $res->fields['nr'] == 1;
    }
    
    /**
     * generates an activation string for this user
     * @return generated activation string
     */
    public function setActivationString() {
        $this->activationString = md5(uniqid(rand(),1));
      
        $DB = Database::getHandle();
        $q = 'INSERT INTO '.DB_SCHEMA.'.user_activation (user_id,activation_string) 
                   VALUES (' . $DB->Quote($this->id) . ','
                             . $DB->Quote($this->activationString) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        return $this->activationString;
    }
    
    public function activate() {
    	$DB = Database::getHandle();
        $DB->StartTrans();
        
    	// delete activation string
        $q = 'DELETE FROM ' . DB_SCHEMA . '.user_activation 
                    WHERE activation_string = ' . $DB->Quote($this->activationString);
        if (!$DB->execute($q)) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return false;
        }
        
        // mark user as activated
        $this->setActivated(true);
        $this->setFirstLogin(date('Y-m-d H:i:s'));
        $this->save();
        
        $DB->CompleteTrans();
    }
    
    /**
     * All user configuration settings, details and user data stored in this instance
     * are written to the database.
     *
     * @throws DBException when commiting data failed for DB reasons
     */
    public function save() {
    	$DB = Database::getHandle();
        // start transaction block
        $DB->StartTrans();

        $this->userDetails->save();
        if ($this->id == null) {
        	$this->id = Database::getCurrentSequenceId($DB, 'users','id');
            // create directory for user related files
            $userDir = AttachmentHandler::getAdjointPath($this);
            if (!file_exists($userDir)) {
                if (!mkdir($userDir)) {
                    throw new CoreException( Logging::getErrorMessage(FILE_MKDIR_FAILED, $userDir) );
                }
            }
            $generalUserDir = AttachmentHandler::getAdjointGeneralPath($this);
            if (!file_exists($generalUserDir)) {
                if (!mkdir($generalUserDir)) {
                    throw new CoreException( Logging::getErrorMessage(FILE_MKDIR_FAILED, $generalUserDir) );
                }
            }
            $this->userDetails->setUserId($this->id);
            $this->userExtraData->setUserId($this->id);
            $this->userData->setUserId($this->id);
            $this->userContactData->setUserId($this->id);
            $this->userPrivacyData->setUserId($this->id);
            $this->userConfig->setUserId($this->id);
        }
         
        $this->userExtraData->save();
        $this->userData->save();
	    $this->userContactData->save();
        $this->userPrivacyData->save();
        $this->userConfig->save();

        if (array_key_exists('studyPaths', $this->valuesToSave)) {
        	$this->saveStudyPaths();
        }

        // end transaction block
        $DB->CompleteTrans();
        
        $this->valuesToSave = array();
        
        return;
    }
    
    private function saveStudyPaths() {
    	$DB = Database::getHandle();

        $q = 'DELETE FROM ' . DB_SCHEMA . '.study_path_per_student
                    WHERE user_id = ' . $DB->Quote($this->id);
        if (!$DB->execute($q)) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return false;
        }
        
        $primaryCourse = true;
        foreach ($this->studyPaths as $id) {
            $q = 'INSERT INTO ' . DB_SCHEMA . '.study_path_per_student
                        (user_id, study_path_id, primary_course)
                    VALUES
                        (' . $DB->Quote($this->id) . ',
                         ' . $DB->Quote($id) . ',
                         ' . $DB->Quote(Database::makeBooleanString($primaryCourse)) . ')';
            if (!$DB->execute($q)) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
                return false;
            }
            
            // only the first one is a primary course
            $primaryCourse = false;
        }
        
    }
    
   /**
    * check, whether user with current name already exists
    * or existed some time ago
    *
    * @return boolean whether username exist(s|ed)
    */
    public static function userAlreadyExists($username) {        
        $DB = Database::getHandle();
        
        $q = 'SELECT COUNT(*) AS nr
                FROM ((SELECT id 
                         FROM ' . DB_SCHEMA . '.users
                        WHERE LOWER(username) = LOWER('.$DB->quote($username).'))
                      UNION 
                      (SELECT id 
                         FROM ' . DB_SCHEMA . '.user_old_nicks
                        WHERE LOWER(old_username) = LOWER('.$DB->quote($username).'))
                     ) existing';
        $res = $DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        return $res->fields['nr'] > 0;
    }
    
    /**
     * log a login error
     */
    public static function logLoginError($username, $password, $postIp) {
        $DB = Database::getHandle();
        
        $q = 'INSERT INTO ' . DB_SCHEMA . '.user_login_errors
                    (username, password, post_ip)
                 VALUES (' . $DB->quote($username) . ', ' . $DB->quote(self::encryptPassword($password)) . ',
                         ' . $DB->quote($postIp) . ')';
        $res = $DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    /**
     * log a login error
     */
    public static function isLoginBlacklisted($username, $postIp) {
        $DB = Database::getHandle();
        
        // look for all login errors in the
        // last three hours
        // find relation between ip address and number of login errors 
        // originating from this address
        $q = 'SELECT post_ip, count(*) AS nr
                FROM ' . DB_SCHEMA . '.user_login_errors
               WHERE lower(username) = ' . $DB->quote(strtolower($username)) . '
                 AND insert_at > now() - interval \'3 hours\' 
            GROUP BY post_ip';
        $res = $DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $totalLoginErrors = 0;
        $ips = array();
        foreach ($res as $row) {
            $totalLoginErrors += $row['nr'];
            $ips[$row['post_ip']] = true;
        }
        
        // check, if we are in blacklist mode
        if ($totalLoginErrors > GlobalSettings::getGlobalSetting('MAX_LOGIN_ERRORS')) {
            if (array_key_exists($postIp, $ips)) {
                return true;
            }
        }
        return false;
    }

/************************************************************************/    

    /**
    *
    * @throws DBException on DB error
    */
    public function listAllRights() {
        return Rights::userListAllRights($this->id);
    }
    
    /**
    * give ids of all roles this user is member of
    *
    * @return array associative array whose keys are the ids of roles the user is member of
    * @throws DBException on DB error
    */
    public function getRoleMembership() {
        $DB = Database::getHandle();
        
        // retrieve all user roles
        $q = "SELECT role_id 
                FROM ".DB_SCHEMA.".user_role_membership 
                WHERE user_id=".$DB->Quote($this->id);
        $res = $DB->execute( $q );
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $roleArray =array();
        foreach ($res as $row) {
            $roleArray[$row['role_id']] = true;
        }
        
        return $roleArray;
    }
	
    public static function isOnline($username) {
        $DB = Database::getHandle();
        
        $q = 'SELECT COUNT(uc.id) AS c
                FROM ' . DB_SCHEMA . '.user_online uc
		   LEFT JOIN ' . DB_SCHEMA . '.users u
		          ON uc.user_id = u.id
               WHERE u.username = ' . $DB->Quote($username);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        if ($res->fields['c']>0) {
        	return true;
        } else {
        	return false;
        }
    }
          
    public static function isUsedUniMailAddress($mail) {
        $DB = Database::getHandle();
        
        # first validate just this email address
        $q = 'SELECT COUNT(id) AS c
                FROM ' . DB_SCHEMA . '.user_old_email_addresses
               WHERE LOWER(email_address) = ' . $DB->Quote(strtolower($mail));
        $res = &$DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        if ($res->fields['c'] > 0) {
            # already found this address
            return true;
        }

        # first get all regexp from the public.email_regexp table which mach
        # the current schema name
        $q = 'SELECT DISTINCT(er.validate_regexp) AS validate_regexp
                FROM public.email_regexp er,
                     public.uni uni,
                     public.cities cities
               WHERE er.uni = uni.id
                 AND uni.city = cities.id
                 AND cities.schema_name = ' . $DB->Quote(DB_SCHEMA) . '
                 AND LENGTH(er.validate_regexp) > 0';
        $res = &$DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        # split email in parts
        $split = explode('@', strtolower(preg_quote($mail)));
        foreach ($res as $row) {
            # replace strings in regexp, if possible
            # remove leading and trailing quotes from name
            $regexp = str_replace('__NAME__', substr($DB->Quote($split[0]), 1, -1), $row['validate_regexp']);
            $regexp = str_replace('__DOMAIN__', $DB->Quote($split[1]), $regexp);
            # validate all email addresses with this regexp
            $q2 = 'SELECT COUNT(id) AS c
                     FROM ' . DB_SCHEMA . '.user_old_email_addresses
                    WHERE LOWER(email_address) ~ \'' . $regexp . '\'';
            $res2 = &$DB->execute($q2);
            if (!$res2) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }

            if ($res2->fields['c'] > 0) {
                # found a match, this email address seems already in use
                return true;
            }
        }

        # no match found
        return false;
    }
    
    public function getName() {
    	return $this->username;
    }
    
    public function getHash() {
        return 'U' . $this->id;
    }
    
    //
    // real getters
    //
    //
    
    public function getUsername() {
        return $this->userDetails->getValue('username');
    }
    
    public function setUsername($username) {
        $this->userDetails->setValue('username', $username);
    }
    
    public function getOldUsernames(){
    	if(empty($this->oldUsernames)){
    		$this->oldUsernames = array();
            $DB = Database::getHandle();
            $q = 'SELECT old_username FROM ' . DB_SCHEMA . '.user_old_nicks '
                 .' WHERE user_id = ' . $DB->quote($this->id)
                 .' ORDER BY insert_at ASC';
            $res = $DB->execute( $q );
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
            foreach( $res as $row ){
            	$this->oldUsernames[] = $row['old_username'];
            }
    	}
        return $this->oldUsernames;
    }
    
    public function getCity() {
        return CityModel::getLocalCity();
    }
    
    public function getCityName() {
        return $this->getCity()->getName();
    }
    
    public function getSignature() {
        // unlike the description we can be sure to have an existing db record of a parsed signature
        // - although it might be empty
        $signatureParsed = $this->userDetails->getValue('signature_parsed');
        // if we have no parsed signature, try to parse the raw one
        if ($signatureParsed == '') {
            $signatureRaw = $this->userDetails->getValue('signature_raw');
            $signatureParsed = ParserFactory::parseWithDefaultSettings($signatureRaw);
            $modelUnchanged = !$this->userDetails->hasChanges();
            $this->userDetails->setValue('signature_parsed', $signatureParsed);
            if ($modelUnchanged) {
                // save model only, if it has no other changes
                $this->userDetails->save();
            }
            // TODO: ensure save-method call?
        }
        return $signatureParsed;
    }
    public function getSignatureRaw() {
        return $this->userDetails->getValue('signature_raw');
    }
    public function setSignature($value) {
        $this->userDetails->setValue('signature_raw', $value);
        $this->userDetails->setValue('signature_parsed', ParserFactory::parseWithDefaultSettings($value));         
    }
    
    /**
     * sets birthdate
     * @param string
     * @param string
     * @param string year (with century!)
     */
    public function setBirthdate($day, $month, $year) {
        $this->userDetails->setValue('birthdate', $year . '-' . $month . '-' . $day);
    }
    
    public function getBirthdate($mode = 'german') {
        // TODO: mode not implemented yet
        if ($mode == 'german') {
            $date = explode('-', $this->userDetails->getValue('birthdate'));
            // insert leading zeros for day and month if neccessary
            if ($date[2][0] != '0' and strlen($date[2]) == 1) {
                $date[2] = '0' . $date[2];
            }
            if ($date[1][0] != '0' and strlen($date[1]) == 1) {
                $date[1] = '0' . $date[1];
            }
            return $date[2] . '.' . $date[1] . '.' . $date[0];
        } else {
            return $this->userDetails->getValue('birthdate');
        }
    }
    
    /**
     * sets password
     * @param string <b>unencrypted</b> password
     */
    public function setPassword($password) {
        $this->userDetails->setValue('password', self::encryptPassword($password));
    }
    public function getPassword() {
        return $this->userDetails->getValue('password');
    }
    
    /**
     * sets original password property
     * @param string <b>unencrypted</b> password
     */
    public function setOriginalPassword($password) {
        $this->userDetails->setValue('original_password', self::encryptPassword($password));
    }
    public function getOriginalPassword() {
        return $this->userDetails->getValue('original_password');
    }
    
    private static $salt = PASSWORD_SALT;
    public static function encryptPassword($password) {
        return sha1(self::$salt . $password);
    }
    
    public function getGender() {
        return $this->userDetails->getValue('gender');
    }
    public function setGender($gender) {
        $this->userDetails->setValue('gender', $gender);
    }
    
    public function getFlirtStatus() {
        // remove first character (status is stored as <nr><status text>)
        return substr($this->userDetails->getValue('flirt_status'), 1);
    }
    public function setFlirtStatus($flirtStatus) {
        if ($flirtStatus == 'red') {
            $flirtStatus = '2red';
        } else if ($flirtStatus == 'yellow') {
            $flirtStatus = '1yellow';
        } else if ($flirtStatus == 'green') {
            $flirtStatus = '0green';
        } else if ($flirtStatus == 'none') {
            $flirtStatus = '3none';
        } 
        $this->userDetails->setValue('flirt_status', $flirtStatus);
    }
    
    public function getUserpicFile($variant = '') {
        if ($variant) {
            $variant .= '_';
        }
        return $this->userDetails->getValue($variant . 'userpic_file');
    }
    public function setUserpicFile($file) {
        $this->userDetails->setValue('userpic_file', $file);
    }
    /**
     * sets the user picture file
     * 
     * due to interface AddressableEntitiy
     * 
     * @param string
     */
    public function setPictureFile($filename) {
        $this->setUserpicFile($filename);
    }
    public function getPictureFile($variant = '') {
        return $this->getUserpicFile($variant);
    }
    
    public function getUni() {
        return $this->userDetails->getValue('uni_obj');
    }
    public function getUniNameShort() {
        return $this->getUni()->getNameShort();
    }
    public function setUniId($id) {
        // need to clear prefetched uni object
        // without marking it for database-save-operation
        $this->userDetails->setValue('uni_obj', null, false);
        $this->userDetails->setValue('uni_id', $id);
    }
    public function getUniId() {
        return $this->userDetails->getValue('uni_id');
    }
    
    public function getNationality() {
        return $this->userDetails->getValue('nationality_obj');
    }
    public function setNationalityId($id) {
        // need to clear prefetched nationality object
        // without marking it for database-save-operation
        $this->userDetails->setValue('nationality_obj', null, false);
        $this->userDetails->setValue('nationality_id', $id);
    }
    public function getNationalityId() {
        return $this->userDetails->getValue('nationality_id');
    }
    
    /*public function getCountry() {
        return $this->userDetails->getValue('country_obj');
    }
    public function setCountryId($id) {
        // need to clear prefetched country object
        // without marking it for database-save-operation
        $this->userDetails->setValue('country_obj', null, false);
        $this->userDetails->setValue('country_id', $id);
    }
    public function getCountryId() {
        return $this->userDetails->getValue('country_id');
    }*/
    
    public function getPersonType() {
        return $this->userDetails->getValue('person_type_obj');
    }
    public function setPersonTypeId($id) {
        // need to clear prefetched person type object
        // without marking it for database-save-operation
        $this->userDetails->setValue('person_type_obj', null, false);
        $this->userDetails->setValue('person_type', $id);
    }
    public function getPersonTypeId() {
        return $this->userDetails->getValue('person_type');
    }
    
    public function getPoints() {
        return $this->userDetails->getValue('points_sum');
    }
    public function getPointsEconomic() {
        return $this->userDetails->getValue('points_flow') / GlobalSettings::getGlobalSetting('POINT_SOURCES_FLOW_MULTIPLICATOR');
    }
    
    public function getAge() {
        return $this->userDetails->getValue('age');
    }
    
    /*public function getLoginErrors() {
        return $this->userDetails->getValue('login_errors');
    }
    public function setLoginErrors($val) {
        $this->userDetails->setValue('login_errors', $val);
    }*/
    
    public function getFirstLogin() {
        return $this->userDetails->getValue('first_login');
    }
    public function setFirstLogin($val) {
        $this->userDetails->setValue('first_login', $val);
    }
    
    public function getLastLogin() {
        return $this->userDetails->getValue('last_login');
    }
    public function setLastLogin($val) {
        $this->userDetails->setValue('last_login', $val);
    }
    
    public function getLastChange() {
        return $this->userDetails->getValue('last_change');
    }
    
    //
    // user extra data
    //
    
    public function getFirstName() {
        return $this->userExtraData->getValue('first_name');
    }
    public function setFirstName($name) {
        $this->userExtraData->setValue('first_name', $name);
    }
    
    public function getLastName() {
        return $this->userExtraData->getValue('last_name');
    }
    public function setLastName($name) {
        $this->userExtraData->setValue('last_name', $name);
    }

    public function getZipCode() {
        $zip = $this->userExtraData->getValue('zip_code');
        // empty zip code must be 0, because it is an INTEGER
        if ((int)$zip == 0) {
            return '';
        }
        return $zip;
    }
    public function setZipCode($name) {
        // empty zip code must be 0, because it is an INTEGER
        if ($name == '') {
            $name = '0';
        }
        $this->userExtraData->setValue('zip_code', $name);
    }
    
    public function getLocation() {
        return $this->userExtraData->getValue('location');
    }
    public function setLocation($name) {
        $this->userExtraData->setValue('location', $name);
    }
    
    public function getStreet() {
        return $this->userExtraData->getValue('street');
    }
    public function setStreet($name) {
        $this->userExtraData->setValue('street', $name);
    }
    
    public function getPrivateEmail() {
        return $this->userExtraData->getValue('private_email');
    }
    public function setPrivateEmail($name) {
        $this->userExtraData->setValue('private_email', $name);
    }
    
    public function getPublicEmail() {
        return $this->userExtraData->getValue('public_email');
    }
    public function setPublicEmail($name) {
        $this->userExtraData->setValue('public_email', $name);
    }
    
    public function getUniEmail() {
        return $this->userExtraData->getValue('uni_email');
    }
    public function setUniEmail($name) {
        $this->userExtraData->setValue('uni_email', $name);
    }
    
    public function getTelephoneMobil() {
        return $this->userExtraData->getValue('telephone_mobil');
    }
    public function setTelephoneMobil($name) {
        $this->userExtraData->setValue('telephone_mobil', $name);
    }
    
    public function hasPublicPGPKey() {
        return $this->userData->getValue('has_public_pgp_key');
    }
    public function getPublicPGPKey() {
        return $this->userData->getValue('public_pgp_key');
    }
    public function setPublicPGPKey($name) {
        $this->userData->setValue('public_pgp_key', $name);
    }
    
    public function getDiaryEntries() {
        return $this->userData->getValue('blog_entries');
    }
    public function getGBEntries() {
        return $this->userData->getValue('gb_entries');
    }
    public function getGBEntriesUnread() {
        return $this->userData->getValue('gb_entries_unread');
    }
    public function getPMs() {
        return $this->userData->getValue('pms');
    }
    public function getPMsSent() {
        return $this->userData->getValue('pms_sent');
    }
    public function getPMsUnread() {
        return $this->userData->getValue('pms_unread');
    }
    public function getForumEntries() {
        return $this->userData->getValue('forum_entries');
    }
    public function getProfileViews() {
        return $this->userData->getValue('profile_views');
    }
    public function getCourseFilesUploads() {
        return $this->userData->getValue('course_file_uploads');
    }
    public function getCourseFilesDownloads() {
        return $this->userData->getValue('course_file_downloads');
    }
    public function getCourseFilesDownloadsByOthers() {
        return $this->userData->getValue('course_file_downloads_other');
    }
    
    public function hasForumRating() {
        return $this->userData->getValue('has_forum_rating');
    }
    public function getForumRating() {
        return $this->userData->getValue('forum_rating');
    }
    public function getForumRatingCount() {
        return $this->userData->getValue('forum_rating_count');
    }
    /*public function increaseForumRating($delta) {
        $this->userDetails->increaseValue('forum_rating', $delta);
    }
    public function incrementForumRatingCount($delta) {
        $this->userDetails->increaseValue('forum_rating_count', 1);
    }*/
    
    public function getActivityIndex() {
        return $this->userDetails->getValue('activity_index');
    }
    
    
    public function hasDescription() {
        return $this->userData->getValue('has_description_raw');
    }
    public function getDescription() {
        // if we have no parsed description, try to parse the raw one
        if (!$this->hasDescriptionParsed()) {
            $descriptionParsed = ParserFactory::parseWithDefaultSettings($this->getDescriptionRaw());
            $this->userData->setValue('description_parsed', $descriptionParsed);
        } else {
            $descriptionParsed = $this->userData->getValue('description_parsed');
        }
        return $descriptionParsed;
    }
    public function setDescription($value) {
        $value_raw = $value;
        $this->userData->setValue('description_parsed', ParserFactory::parseWithDefaultSettings($value)); 
        $this->userData->setValue('description_raw', $value_raw);
    }
    public function hasDescriptionParsed() {
        return $this->userData->getValue('has_description_parsed');
    }
    public function getDescriptionRaw() {
        return $this->userData->getValue('description_raw');
    }
    
    
    public function setPrivacyBirthdate($val) {
        $this->userPrivacyData->setValue('birthdate', $val);
    }
    public function setPrivacyAddress($val) {
        $this->userPrivacyData->setValue('address', $val);
    }
    public function setPrivacyEmailAddress($val) {
        $this->userPrivacyData->setValue('mail_address', $val);
    }
    public function setPrivacyInstantMessanger($val) {
        $this->userPrivacyData->setValue('instant_messanger', $val);
    }
    public function setPrivacyRealname($val) {
        $this->userPrivacyData->setValue('real_name', $val);
    }
    public function setPrivacyTelephone($val) {
        $this->userPrivacyData->setValue('telephone', $val);
    }
    public function isGBpublic() {
        return Database::convertPostgresBoolean($this->userConfig->getValue('gb_public'));
    }
    public function setGBpublic($val) {
        $this->userConfig->setValue('gb_public', Database::makeBooleanString($val));
    }
    public function isFriendListpublic() {
        return Database::convertPostgresBoolean($this->userConfig->getValue('friendlist_public'));
    }
    public function setFriendListpublic($val) {
        $this->userConfig->setValue('friendlist_public', Database::makeBooleanString($val));
    }
     public function isDiarypublic() {
        return Database::convertPostgresBoolean($this->userConfig->getValue('diary_public'));
    }
    public function setDiarypublic($val) {
        $this->userConfig->setValue('diary_public', Database::makeBooleanString($val));
    }
    
    
    public function isGBFilterShow() {
        return !$this->userConfig->getValue('has_guestbook_filter_show') or 
            Database::convertPostgresBoolean($this->userConfig->getValue('guestbook_filter_show'));
    }
    public function setGBFilterShow($val) {
        $this->userConfig->setValue('guestbook_filter_show', Database::makeBooleanString($val));
    }
    
    public function isDiaryFilterShow() {
        return !$this->userConfig->getValue('has_blog_filter_show') or 
            Database::convertPostgresBoolean($this->userConfig->getValue('blog_filter_show'));
    }
    public function setDiaryFilterShow($val) {
        $this->userConfig->setValue('blog_filter_show', Database::makeBooleanString($val));
    }


    public function isActivated() {
        return Database::convertPostgresBoolean($this->userDetails->getValue('flag_activated'));
    }
    public function setActivated($val) {
        $this->userDetails->setValue('flag_activated', Database::makeBooleanString($val));
    }
    public function isActive() {
        return Database::convertPostgresBoolean($this->userDetails->getValue('flag_active'));
    }
    public function setActive($val) {
        $this->userDetails->setValue('flag_active', Database::makeBooleanString($val));
    }
    public function isInvisible() {
        return Database::convertPostgresBoolean($this->userDetails->getValue('flag_invisible'));
    }
    public function setInvisible($val) {
        $this->userDetails->setValue('flag_invisible', Database::makeBooleanString($val));
    }

    public function isExternal() {
        return $this->isExternal;
    }
    
    public function hasImICQ() {
        return $this->userContactData->getValue('has_im_icq');
    }
    public function getImICQ() {
        return $this->userContactData->getValue('im_icq');
    }
    public function setImICQ($name) {
        $this->userContactData->setValue('im_icq', $name);
    }
    
    public function hasImJabber() {
        return $this->userContactData->getValue('has_im_jabber');
    }
    public function getImJabber() {
        return $this->userContactData->getValue('im_jabber');
    }
    public function setImJabber($name) {
        $this->userContactData->setValue('im_jabber', $name);
    }
    
    public function hasImYahoo() {
        return $this->userContactData->getValue('has_im_yahoo');
    }
    public function getImYahoo() {
        return $this->userContactData->getValue('im_yahoo');
    }
    public function setImYahoo($name) {
        $this->userContactData->setValue('im_yahoo', $name);
    }
    
    public function hasImMSN() {
        return $this->userContactData->getValue('has_im_msn');
    }
    public function getImMSN() {
        return $this->userContactData->getValue('im_msn');
    }
    public function setImMSN($name) {
        $this->userContactData->setValue('im_msn', $name);
    }
    
    public function hasImAIM() {
        return $this->userContactData->getValue('has_im_aim');
    }
    public function getImAIM() {
        return $this->userContactData->getValue('im_aim');
    }
    public function setImAIM($name) {
        $this->userContactData->setValue('im_aim', $name);
    }
    
    public function hasSkype() {
        return $this->userContactData->getValue('has_skype');
    }
    public function getSkype() {
        return $this->userContactData->getValue('skype');
    }
    public function setSkype($name) {
        $this->userContactData->setValue('skype', $name);
    }
    
    public function getHomepage() {
        return $this->userContactData->getValue('homepage');
    }
    public function setHomepage($name) {
        $this->userContactData->setValue('homepage', $name);
    }


    public function hasConfigBoxesMinimized() {
        return $this->userConfig->getValue('has_boxes_minimized');
    }
    public function getConfigBoxesMinimized() {
        return $this->userConfig->getValue('boxes_minimized');
    }
    public function setConfigBoxesMinimized($name) {
        $this->userConfig->setValue('boxes_minimized', $name);
    }

    public function hasConfigBoxesLeft() {
        return $this->userConfig->getValue('has_boxes_left');
    }
    public function getConfigBoxesLeft() {
        return $this->userConfig->getValue('boxes_left');
    }
    public function setConfigBoxesLeft($name) {
        // in order to get an empty box layout wirh one click
        // even if the user currently has no config string,
        // we first have to set some data, so that
        // our data really gets written to the model/database
        if ($name == '') {
            $this->userConfig->setValue('boxes_left', '3.141592653');
        }
        $this->userConfig->setValue('boxes_left', $name);
    }
    
    public function hasConfigBoxesRight() {
        return $this->userConfig->getValue('has_boxes_right');
    }
    public function getConfigBoxesRight() {
        return $this->userConfig->getValue('boxes_right');
    }
    public function setConfigBoxesRight($name) {
        // in order to get an empty box layout wirh one click
        // even if the user currently has no config string,
        // we first have to set some data, so that
        // our data really gets written to the model/database
        if ($name == '') {
            $this->userConfig->setValue('boxes_right', '3.141592653');
        }
        $this->userConfig->setValue('boxes_right', $name);
    }
    
    public function getConfigFeatureSlots() {
        return $this->userConfig->getValue('feature_free_update_slots');
    }
    public function setConfigFeatureSlots($name) {
        $this->userConfig->setValue('feature_free_update_slots', $name);
    }
    
    public function getNextFeaturePointLimit() {
        return $this->userConfig->getValue('feature_next_point_limit');
    }
    
    public function hasNoBasicStudies() {
        return $this->userConfig->getValue('has_no_basic_studies');
    }
    public function getNoBasicStudies() {
        return $this->userConfig->getValue('no_basic_studies');
    }
    public function setNoBasicStudies($name) {
        $this->userConfig->setValue('no_basic_studies', $name);
    }
    
    // remove persistent login DB-property,
    // we want a cookie based solution (linap, 19.05.2007)
    /*
    public function isPersistentLogin() {
        return $this->hasPersistentLogin() && Database::convertPostgresBoolean($this->getPersistentLogin());
    }
    protected function hasPersistentLogin() {
        return $this->userConfig->getValue('has_persistent_login');
    }
    protected function getPersistentLogin() {
        return $this->userConfig->getValue('persistent_login');
    }
    public function setPersistentLogin($name) {        
        $this->userConfig->setValue('persistent_login', Database::makeBooleanString($name));
    }
    */
    
    public function getCourses() {
        if ($this->courses === null) {
            $this->courses = CourseModel::getCoursesByUser($this);
        }
        return $this->courses;
    }
    /**
     * sets course models
     * they will <b>not</b> be saved in any relation with the user here
     */
    public function setCourses($courses) {
        $this->courses = $courses;
    }
    
    public function setStudyPathsId($value) {
        // need to clear prefetched uni object
        // without marking it for database-save-operation
        $this->studyPathsObj = null;
        //var_dump($this->studyPaths);
        $this->studyPaths = $value;
        //var_dump($this->studyPaths);
        $this->valuesToSave['studyPaths'] = true; 
    }
    public function getStudyPathsId() {
        return $this->safeReturn('studyPaths', 'loadStudyData');
    }
    public function getStudyPathsObj() {
        if (!$this->studyPathsObj) {
            $studyPathsId = $this->getStudyPathsId();
            $this->studyPathsObj = StudyPathModel::getStudyPathsByIds($studyPathsId);
        }
        return $this->studyPathsObj;
    }
    
    public function getGroupMembership() {
        if ($this->groups === null) {
            $this->groups = GroupModel::getGroupsByUser($this);
        }
        return $this->groups;
    }
    
    public function getCachekey() {
        return (int) ($this->id / 100) . '|' . $this->id;
    }
    
    public function moveToRecycleBin($reason = '') {
        $DB = Database::getHandle();
        $DB->StartTrans();
        
        $this->setInvisible(true);
        $this->setActive(false);
        $this->save();
        
        $q = 'INSERT INTO ' . DB_SCHEMA . '.user_recycle (user_id, comment) 
                VALUES (' . $DB->Quote($this->id) . ', ' . $DB->Quote($reason) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $DB->CompleteTrans();
    }
    
     public function removeFromRecycleBin() {
        $DB = Database::getHandle();
        $DB->StartTrans();
        
        $this->setInvisible(false);
        $this->setActive(true);
        $this->save();
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.user_recycle 
              WHERE user_id = '. $DB->Quote($this->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $DB->CompleteTrans();
    }
    
    public function setExpirationDate($expDate) {
        $DB = Database::getHandle();
        $q = 'INSERT INTO ' . DB_SCHEMA . '.user_expiration
                (user_id, expires)
               VALUES
                 (' . $DB->Quote($this->id) . ', ' . Database::getPostgresTimestamp($expDate). ')';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    public function hasBlog() {
        if ($this->hasBlog === null) {
            include_once MODEL_DIR . '/blog/user_blog_advanced_model.php';
            $this->hasBlog = UserBlogAdvancedModel::getBlog($this) != null;
        }
        return $this->hasBlog;
    }
    public function clearHasBlogCache() {
        $this->hasBlog = null;
    }
    
    public function isSystemUser() {
        return $this->id == SYSTEM_USER_ID;
    }
    
    public function delete($freeUsername = false) {
        $DB = Database::getHandle();
               
        $q = 'DELETE FROM ' . DB_SCHEMA . '.users 
                WHERE id = ' . $DB->Quote($this->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        if ($freeUsername) {
            $q = 'DELETE FROM ' . DB_SCHEMA . '.user_old_nicks 
                   WHERE old_username = ' . $DB->Quote($this->getUsername());
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
        }
    }
}

?>
