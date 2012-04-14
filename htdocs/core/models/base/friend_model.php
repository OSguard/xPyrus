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

// $Id: friend_model.php 5743 2008-03-25 19:48:14Z ads $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/friend_model.php $

require_once MODEL_DIR.'/base/user_protected_model.php';

class FriendModel extends UserProtectedModel {
    /**
     * @var string
     * type of friendship
     */
    protected $friendTypeName;
    
    /**
     * @var boolean
     * special friendship
     */
    protected $friendIsNormal;
    
    /**
     * @var boolean
     * friend or foe?
     */
    protected $friendIsFriend;
    
    /**
     * @var UserModel
     * user this model is a friend of
     */
    protected $friendOf;
    
    /**
     * @var array
     * associatvie array: [friendType] => typeId
     */
    protected static $validFriendTypes = null;
    
    const HAS_NO_RELATION = 0;
    const IS_FRIEND = 1;
    const IS_FOE = 2;   
    
    /**
     * creates a new FriendModel 
     * if parameters are given, it will be based on given user and friendship type
     * @param $friendOf UserModel user who is origin of the friendship
     * @param $user UserModel user the friend model is based on
     * @param $friendType string
     */
    public function __construct($friendOf, $user = null, $friendType = 'Normal') {
        parent::__construct($user);
        $this->friendTypeName = $friendType;
        $this->friendOf = $friendOf;
    }
    
    /**
     * adds this user to friendlist
     */
    public function addToFriendlist() {
        
        $DB = Database::getHandle();
        
    	$q = 'INSERT INTO ' . DB_SCHEMA . '.user_friends
                (user_id, friend_id, friend_type)
              VALUES
                ( ' . $DB->Quote($this->friendOf->id).',
                  ' . $DB->Quote($this->id).',
                  (SELECT id FROM friend_types WHERE type_name=' . $DB->Quote($this->friendTypeName) . ')
                )';
        $res = &$DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    /**
     * removes this user from friendlist
     */
    public function removeFromFriendlist() {
        $DB = Database::getHandle();
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.user_friends
                    WHERE user_id=' . $DB->Quote($this->friendOf->id) . '
                      AND friend_id=' . $DB->Quote($this->id);
        $res = &$DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    /**
     * changes the type of friendship
     */
    public function modifyTypeAtFriendlist($newType) {
    	if ($this->friendTypeName == $newType) {
    		return;
    	} 
    	
        $this->friendTypeName = $newType;

        $DB = Database::getHandle();
        $q = 'UPDATE ' . DB_SCHEMA . '.user_friends
                 SET friend_type=(SELECT id FROM friend_types WHERE type_name=' . $DB->Quote($this->friendTypeName) . ')
               WHERE user_id=' . $DB->Quote($this->friendOf->id).'
                 AND friend_id=' . $DB->Quote($this->id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$this->DB->ErrorMsg() ) );
        }
    }
    
    /**
     * returns an array of friends of given user, sorted by given criterion
     *
     * @param $originUser UserModel user id whose friends are to be retrieved
     * @param $orderBy string sort criterion: 'username' or 'type' or 'status'
     * @param $onlineStatus string login status: 'dontcare', '', 'online' or 'offline'
     *               dontcare ignores the online status, '' returns both online and offline
     * @return array array of FriendModel, the hash keys are the user ids
     * @throws DBException on database error
     */
    public static function getFriendsByUser($originUser, $orderBy = '', $onlineStatus = 'dontcare', $showFriends = true, $showFoes = false) {
        // check that parameters are in valid range
        if ($orderBy != 'username' && $orderBy != 'type' && $orderBy != 'status') {
        	$orderBy = 'username';
        }
        if ($onlineStatus != '' and 
            $onlineStatus != 'dontcare' and
            $onlineStatus != 'online' and
            $onlineStatus != 'offline') 
        {
            $onlineStatus = 'dontcare';
        }
        
    	$DB = Database::getHandle();
        
        $q = 'SELECT type_name AS type, is_type_friend AS is_friend, is_normal, 
                     u.*';
        if ($onlineStatus != 'dontcare') {
            $q.=   ',(uo.id IS NOT NULL) AS online_status ';
        }
        $q.=  ' FROM ' . DB_SCHEMA . '.user_friends AS f,
                     friend_types, ' . DB_SCHEMA . '.users AS u';
        if ($onlineStatus != 'dontcare') {
            $q.=  
         ' LEFT JOIN ' . DB_SCHEMA . '.user_online AS uo
                  ON uo.user_id=u.id ';
        } 
           
        $q.= ' WHERE f.user_id=' . $DB->Quote($originUser->id) . ' ';
        if ($onlineStatus=='online') {
            $q.='AND online_status = true ';
        } else if ($onlineStatus=='offline') {
            $q.='AND online_status = false ';
        }
        $q.=    'AND f.friend_id=u.id
                 AND f.friend_type=friend_types.id ';
        if ($showFriends && !$showFoes) {
            $q.='AND friend_types.is_type_friend ';
        } else if ($showFoes && !$showFriends) {
            $q.='AND NOT friend_types.is_type_friend ';
        }
        $q.=    'AND u.flag_activated = true
                 AND u.flag_invisible = false ';

        if ($orderBy=='username') {
            $q.='ORDER BY lower(username) ASC ';
        } else if ($orderBy=='type') {
            $q.='ORDER BY f.friend_type, lower(username) ASC ';
        } else if ($orderBy=='status') {
            $q.='ORDER BY online_status DESC, lower(username) ASC ';
        }

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $users = array();
        foreach ($res as $row) {
        	// note: UserMainDataModel gets some columns, it does not need
            // such as type, is_normal, is_friend, online_status.
            // it would be disproportional effort to filter them out
            // as there are no sideeffects expected ...
        	$user = new FriendModel($originUser, new UserModel(new UserMainDataModel($row)));
            $user->friendTypeName                  = $row['type'];
            $user->friendIsNormal                  = Database::convertPostgresBoolean($row['is_normal']);
            $user->friendIsFriend                  = Database::convertPostgresBoolean($row['is_friend']);
            
            if ($onlineStatus!='dontcare') {
                $user->isLoggedIn                  = Database::convertPostgresBoolean($row['online_status']);
            }
            
            $users[$user->id] = $user;
        }
        
        return $users;
    }
    
    /**
     * returns an array of users who have given user as a friend
     *
     * @param $originUser UserModel user id who is to be examined
     * @return array array of FriendModel, the hash keys are the user ids
     * @throws DBException on database error
     */
    public static function getFriendsReverseByUser($destUser) {
        $DB = Database::getHandle();
        
        $q = 'SELECT type_name AS type, is_type_friend AS is_friend, is_normal, 
                     u.*
                FROM ' . DB_SCHEMA . '.user_friends AS f,
                     friend_types, ' . DB_SCHEMA . '.users AS u  
               WHERE f.friend_id=' . $DB->Quote($destUser->id) . '
                 AND f.user_id=u.id
                 AND f.friend_type=friend_types.id
                 AND u.flag_activated = true
                 AND u.flag_invisible = false 
            ORDER BY lower(username) ASC';
        //var_dump($q);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $users = array();
        foreach ($res as $row) {
            // note: UserMainDataModel gets some columns, it does not need
            // such as type, is_normal, is_friend, online_status.
            // it would be disproportional effort to filter them out
            // as there are no sideeffects expected ...
            $user = new FriendModel(null, new UserModel(new UserMainDataModel($row)));
            $user->friendTypeName                  = $row['type'];
            $user->friendIsNormal                  = Database::convertPostgresBoolean($row['is_normal']);
            $user->friendIsFriend                  = Database::convertPostgresBoolean($row['is_friend']);
            
            $users[$user->id] = $user;
        }
        
        return $users;
    }
    
    /**
     * checks if $friend is a friend of $user
     * @note by default, foes are ignored 
     * @param UserModel $user 
     * @param UserModel $friend
     * @param string $type optional condition to the type of friendship
     * @return boolean
     */
    public static function isFriendOf($user, $friend, $type = '') {
        // if one of given users has id 0, there can't be a relationship
        if ($user->id == 0 || $friend->id == 0) {
            return false;
        }
        if ($user->equals($friend)) {
            return false;
        }
        
        $DB = Database::getHandle();
        $q = 'SELECT COUNT(id) AS nr
                FROM ' . DB_SCHEMA . '.user_friends
               WHERE user_id = ' . $DB->Quote($user->id) . '
                 AND friend_id = ' . $DB->Quote($friend->id);
        if ($type != '') {
        	$q .= 
               ' AND friend_type = (SELECT id FROM public.friend_types
                                     WHERE type_name = ' . $DB->Quote($type). ')';
        } else {
        	$q .= 
               ' AND friend_type IN (SELECT id FROM public.friend_types
                                     WHERE is_type_friend = true)';
        }
        #var_dump($q);
        $res = &$DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        return $res->fields['nr'] > 0;
    }
    
    
    // TODO: combine with upper "isFriendOf"-method?
    /**
     * determines type of relationship between two users
     * 
     * @param UserModel
     * @param UserModel
     * @return int FriendModel-constant
     */
    public static function getRelationType($user, $otherUser) {
        // if one of given users has id 0, there can't be a relationship
        if ($user->id == 0 || $otherUser->id == 0) {
            return self::HAS_NO_RELATION;
        }
        if ($user->equals($otherUser)) {
            return self::HAS_NO_RELATION;
        }
        
        $DB = Database::getHandle();
        $q = 'SELECT is_type_friend
                FROM public.friend_types ft,
                     ' . DB_SCHEMA . '.user_friends uf
               WHERE user_id = ' . $DB->Quote($user->id) . '
                 AND friend_id = ' . $DB->Quote($otherUser->id) . '
                 AND ft.id = uf.friend_type';
        #var_dump($q);
        $res = &$DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        if ($res->EOF) {
            return self::HAS_NO_RELATION;
        }
        if (Database::convertPostgresBoolean($res->fields['is_type_friend'])) {
            return self::IS_FRIEND;
        } else {
            return self::IS_FOE;
        }
    }
    
    public static function hasOnIgnoreList($user, $foe) {
        return self::isFriendOf($user, $foe, 'Ignore');
    }
    
    protected function __get($val) {
    	if ($val == 'username') return parent::__get('username');
        
        throw new CoreException ("don't use __get-magic (" . $val . "), you have to use new getter functions instead");
        /*switch ($val) {
        case 'isNormal': return $this->friendIsNormal;
        case 'isFriend': return $this->friendIsFriend;
        case 'friendType': return $this->friendTypeName;
        default: return parent::__get($val);
    	}*/
    }
    
    public function getFriendType() {
        return $this->friendTypeName;
    }
	
    /**
     * gives a associative array of all valid friend types
     * @return array
     */
    public static function getValidFriendTypes() {
    	// if valid type cache is empty, fetch from DB
    	if (self::$validFriendTypes == null) {
    		self::$validFriendTypes = array();
            
            $DB = Database::getHandle();
            $q = 'SELECT id, type_name
                    FROM public.friend_types';
            $res = &$DB->execute($q);
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
            foreach ($res as $row) {
            	self::$validFriendTypes[$row['type_name']] = $row['id'];
            }
    	}
        return self::$validFriendTypes;
    }
    
    /**
     * checks if given type is a valid type
     * @param $type string
     * @return int id of friend type is valid; 0 otherwise 
     */
    public static function isValidType($type) {
    	if (array_key_exists($type, self::getValidFriendTypes())) {
    		return self::$validFriendTypes[$type];
    	}
        return 0;
    }
}

?>
