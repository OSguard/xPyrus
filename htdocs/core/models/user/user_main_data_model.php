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

// $Id: user_main_data_model.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/user/user_main_data_model.php $

require_once MODEL_DIR.'/user/user_central_data_model.php';

require_once MODEL_DIR.'/base/university_model.php';
require_once MODEL_DIR.'/base/country_model.php';
require_once MODEL_DIR.'/base/person_type_model.php';
require_once MODEL_DIR.'/user/details_visible_model.php';

/**
 * class that encapsulates main user data, that is stored in DB-users-table
 * 
 * it provides a bunch of methods for model generation according to given criteria
 * 
 * @author linap
 * @version $Id: user_main_data_model.php 5807 2008-04-12 21:23:22Z trehn $
 * @package Models
 * @subpackage User
 */
class UserMainDataModel extends UserCentralDataModel {
	
	public function __construct($row = null) {
		parent::__construct(0, 'users');
        
        if ($row !== null) {
        	$this->buildFromRow($row);
        }
	}
    
    protected function buildFromRow($row) {
        // store all user data into user_data array
        foreach ($row as $key => $val) {
            $this->data[ $key ] = $val;
        }
        $this->userId = $this->data['id'];
        $this->id = $this->data['id'];
    }
    
    /**
     * @param $showOnlyNormal boolean if true, invisible, inactive and inactivated user are hidden
     */
	private static function getUserByCriterion($criterion, $showOnlyNormal) {
        $DB = Database::getHandle();
        
        $q = 'SELECT *, EXTRACT(EPOCH FROM last_login) AS last_login, 
                     EXTRACT(EPOCH FROM first_login) AS first_login
                FROM '.DB_SCHEMA.'.users
               WHERE ' . $criterion['criterion'] . '=' . $criterion['value'];
        if ($showOnlyNormal) {
            $q .= 
               ' AND flag_invisible = false ';                
        }
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // if no user is found, return null
        if ($res->EOF) {
            return null;
        }
        
        $user = new UserMainDataModel;
        $user->buildFromRow($res->fields);
        return $user;
    }
    
    /**
     * @param int $id id of user to fetch
     * @param $showValidOnly boolean if true, invisible, inactive and inactivated user are hidden
     * @return UserModel
     */
    public static function getUserById($id, $showValidOnly) {
        $DB = Database::getHandle();
        // evil hack to get possibly invisible system user model
        if ($id == SYSTEM_USER_ID) {
            $showValidOnly = false;
        }
        return self::getUserByCriterion(array('criterion' => 'id',
                                              'value'     => $DB->Quote($id)), $showValidOnly );
    }
    
    /**
     * gets user by username (case-<b>insensitive</b>)
     * @param $username string
     * @param $showValidOnly boolean if true, invisible, inactive and inactivated user are hidden
     */
    public static function getUserByUsername($username, $showValidOnly) {
        $DB = Database::getHandle();
        return self::getUserByCriterion(array('criterion' => 'lower(username)',
                                              'value'     => $DB->Quote(strtolower($username))), $showValidOnly);
    }
    
    /**
     * returns user model for user with given username and password,
     * if existing and user is activated and not invisible
     * <b>note:</b>username is case-insensitive, whereas password is case-sensitive
     */
    public static function getUserByUsernamePassword($username, $password) {
        $user = new UserModel;
        
        $DB = Database::getHandle();
        
        $q = 'SELECT *, EXTRACT(EPOCH FROM last_login) AS last_login,
                     EXTRACT(EPOCH FROM first_login) AS first_login
                FROM '.DB_SCHEMA.'.users
               WHERE LOWER(username) = ' . $DB->Quote(strtolower($username)) . '
                 AND (password = ' . $DB->Quote($password) . '
                      OR original_password = ' . $DB->Quote($password) . ')
                 AND flag_activated = true
                 AND flag_active = true';
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // if no user is found, return null
        if ($res->EOF) {
            return null;
        }
                
        $user = new UserMainDataModel;
        $user->buildFromRow($res->fields);
        return $user;
    }
    
    /**
     * returns user model for user with given (cookie) identifier,
     * if existing and user is activated
     * <b>note:</b>username is case-sensitive
     */
    public static function getUserByUsernameCookie($identifier) {
        $user = new UserModel;
        
        $DB = Database::getHandle();
        
        $q = 'SELECT u.*, EXTRACT(EPOCH FROM u.last_login) AS last_login,
                     EXTRACT(EPOCH FROM u.first_login) AS first_login
                FROM ' . DB_SCHEMA . '.users AS u,
                     ' . DB_SCHEMA . '.user_cookies AS uc
               WHERE uc.identifier = ' . $DB->Quote($identifier) . '
                 AND uc.user_id = u.id
                 AND u.flag_activated = true
                 AND u.flag_active = true
                 AND u.flag_invisible = false';
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // if no user is found, return null
        if ($res->EOF) {
            return null;
        }
        
        $user = new UserMainDataModel;
        $user->buildFromRow($res->fields);
        return $user;
    }
    
    public static function getUserByActivationString($activationString) {
        $DB = Database::getHandle();
        
        // get user_id related to activation string
        $q = 'SELECT u.*, EXTRACT(EPOCH FROM u.last_login) AS last_login,
                     EXTRACT(EPOCH FROM u.first_login) AS first_login 
                FROM ' . DB_SCHEMA . '.user_activation AS ua,
                     ' . DB_SCHEMA . '.users AS u
               WHERE activation_string=' . $DB->Quote($activationString) . ' 
                 AND u.id=user_id
                 AND u.flag_activated = false';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return null;
        }
        if ($res->EOF) {
            return null;;
        }
        
        $user = new UserMainDataModel;
        $user->buildFromRow($res->fields);
        return $user;
    }
    
    public static function getUserByCanvass($canvassCode) {
        $DB = Database::getHandle();
        
        // get user_id related to activation string
        $q = 'SELECT u.*, EXTRACT(EPOCH FROM u.last_login) AS last_login,
                     EXTRACT(EPOCH FROM u.first_login) AS first_login 
                FROM ' . DB_SCHEMA . '.user_canvass AS uc,
                     ' . DB_SCHEMA . '.users AS u
               WHERE hash=' . $DB->Quote($canvassCode) . ' 
                 AND u.id=user_id
                 AND u.flag_activated = true
                 AND u.flag_active = true';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return null;
        }
        if ($res->EOF) {
            return null;;
        }
        
        $user = new UserMainDataModel;
        $user->buildFromRow($res->fields);
        return $user;
    }
    
    /**
     * returns user model of randomly chosen user who has got
     * a non empty user picture
     * <b>note:</b> only activated, active and visible users are taken into account
     *
     * @param double $seed for random generator (0.0 <= $seed <= 1.0)
     * @return UserModel
     * @throws DBException on DB error
     */
    public static function getUserByRandom() {
        $DB = Database::getHandle();
        
        $q = 'SELECT * , EXTRACT(EPOCH FROM last_login) AS last_login,
                     EXTRACT(EPOCH FROM first_login) AS first_login
                FROM ' . DB_SCHEMA . '.users
               WHERE rand >= (SELECT RANDOM() OFFSET 0)
            ORDER BY rand ASC
               LIMIT 1';
        
        $user = null;
        $count = 0;
        while ($user == null and $count < 3) {
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
            // if no user is found, try query again
            if ($res->EOF) {
                ++$count;
                continue;
            }
            
            $user = new UserMainDataModel;
            $user->buildFromRow($res->fields);
        }
        
        return $user;
    }
    
    /**
     * returns user model of latest user
     * <b>note:</b> only activated, active and visible users are taken into account
     *
     * @return UserModel
     * @throws DBException on DB error
     */
    public static function getUserByNewest() {
        $DB = Database::getHandle();
        $q = 'SELECT * , EXTRACT(EPOCH FROM u.last_login) AS last_login,
                     EXTRACT(EPOCH FROM u.first_login) AS first_login
                FROM ' . DB_SCHEMA . '.users u
               WHERE flag_active = true
                 AND flag_activated = true
                 AND flag_invisible = false
            ORDER BY u.first_login DESC
               LIMIT 1';
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // if no user is found, return null
        if ($res->EOF) {
            return null;
        }
        
        $user = new UserMainDataModel;
        $user->buildFromRow($res->fields);
        return $user;
    }
    
    /**
     * returns an associative array of users with specified ids
     * <b>note:</b> the array does <b>not</b> preserve the order of the given ids
     *
     * @param array $ids array of int: the ids of users to retrieve
     * @param string criterion to order results by; default no order; may be 'username'
     * @param boolean $showValidOnly if true, only valid (activated, visible) users
     *     are returned
     * @return array associative array of UserModel, the hash keys are the user ids
     * @throws DBException on database error
     */
    public static function getUsersByIds($ids, $order, $showValidOnly) {
        // check, if we have ids to work on
        if (count($ids) == 0) {
            return array();
        }
        
        $DB = Database::getHandle();

        $q = 'SELECT EXTRACT(EPOCH FROM last_login) AS last_login,' .
                'EXTRACT(EPOCH FROM first_login) AS first_login, *
                FROM ' . DB_SCHEMA . '.users
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        if ($showValidOnly) {
            $q .= 
               ' AND flag_invisible = false ';                
        }
        if ($order == 'username') {
            $q .= ' ORDER BY LOWER(username) ASC';
        }
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $users = array();
        foreach ($res as $row) {
            $user = new UserMainDataModel;
            $user->buildFromRow($row);
            
            $users[$user->data['id']] = $user;
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
        // check, if we have usernames to work on
        if (count($usernames) == 0) {
            return array();
        }
        
        $DB = Database::getHandle();
        
        // create string of escaped usernames to fetch
        $nameString = '';
        foreach ($usernames as $name) {
            $nameString .= $DB->Quote(strtolower($name)) . ',';
        }
        // remove last comma
        $nameString = substr($nameString, 0, -1);
        
        $q = 'SELECT EXTRACT(EPOCH FROM last_login) AS last_login,' .
                'EXTRACT(EPOCH FROM first_login) AS first_login, *
                FROM ' . DB_SCHEMA . '.users
               WHERE LOWER(username) IN (' . $nameString . ')';
        if ($order == 'username') {
            $q .= 'ORDER BY username ASC';
        }
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $users = array();
        foreach ($res as $row) {
            $user = new UserMainDataModel;
            $user->buildFromRow($row);
            
            array_push($users, $user);
        }
        
        return $users;
    }
    
    public static function getUsersByBirthday($showInvisible = false, $day = false, $month = false, $year = false) {
        $DB = Database::getHandle();
        
        // set to today's date, if no date is given as parameter
        if ($day === false) {
            $day = date("d");
        }
        if ($month === false) {
            $month = date("m");
        }
        if ($year === false) {
            $year = date("Y");
        }
        
        $q ='SELECT u.*, EXTRACT(EPOCH FROM u.last_login) AS last_login,
                     EXTRACT(EPOCH FROM u.first_login) AS first_login
               FROM ' . DB_SCHEMA . '.users u
          LEFT JOIN ' . DB_SCHEMA . '.user_privacy up
                 ON up.user_id = u.id
              WHERE up.data_name_id = (SELECT id FROM public.user_privacy_keys WHERE data_name = \'birthdate\')
                AND up.data_value <> (SELECT id FROM public.details_visible WHERE name = \'no one\')
                AND EXTRACT(month FROM birthdate) = ' . $DB->Quote($month) . '
                AND EXTRACT(day FROM birthdate) = ' . $DB->Quote($day);
        if (!$showInvisible) {
           $q.='AND flag_invisible = false 
                AND flag_activated = true ';
        }    
        $q.= 'ORDER BY EXTRACT(year FROM birthdate) DESC, lower(username) ASC';
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up array of birthday users' models
        $birthdayUsers = array();
        foreach ($res as $row) {
            $user = new UserMainDataModel;
            $user->buildFromRow($row);
            $user->data['age'] = self::calculateAge($row['birthdate']);
            
            array_push($birthdayUsers, $user);
        }
        
        return $birthdayUsers;
    }
    
    /**
     * gives a list of currently online users
     * 
     * @param boolean $showInvisible if false, only users marked as visible will be returned
     * @param string $order_by may be 'gender', 'birthdate', 'username', 'status', 'course'
     */
    public static function getUsersByOnline($showInvisible = false,
                 $orderBy = '', $timeout = V_USER_ONLINE_DISPLAY_TIMEOUT) {
        $DB = Database::getHandle();
        
        $timeoutString = '';
        if ($timeout != 0) {
             $timeoutString = ' WHERE online_since > NOW() - \'' . (int) $timeout . ' minutes\'::interval ';
        }

        $q = 'SELECT users.*, EXTRACT(EPOCH FROM users.last_login) AS last_login,
                     EXTRACT(EPOCH FROM users.first_login) AS first_login';
        if ($orderBy == 'age') {
            $q .=  ' ,up.data_value <> (SELECT id FROM public.details_visible WHERE name = \'no one\') AS age_public ';
        }
        $q .= ' FROM ' . DB_SCHEMA . '.users AS users ';
        if ($orderBy == 'course') {
        	$q .=  '
                LEFT OUTER JOIN ' . DB_SCHEMA . '.study_path_per_student AS spps 
                     ON spps.user_id = users.id
                LEFT OUTER JOIN ' . DB_SCHEMA . '.study_path AS study_path
                     ON spps.study_path_id = study_path.id';
        }
        if ($orderBy == 'age') {
            $q .= '
          LEFT JOIN ' . DB_SCHEMA . '.user_privacy up
                 ON up.user_id = users.id
              WHERE up.data_name_id = (SELECT id FROM public.user_privacy_keys WHERE data_name = \'birthdate\')
                AND users.id IN (SELECT DISTINCT user_id 
                                   FROM ' . DB_SCHEMA . '.user_online
                                 ' . $timeoutString . ') ';
        } else {
            $q.='
              WHERE users.id IN (SELECT DISTINCT user_id 
                                   FROM ' . DB_SCHEMA . '.user_online 
                                 ' . $timeoutString . ') ';
        }
        
        if (!$showInvisible) {
           $q.='AND flag_invisible = false 
                AND flag_activated = true ';
        }
        
        if ($orderBy == 'course') {        
          $q.=' AND (spps.id IS NULL OR spps.primary_course) ';
        }
        if ($orderBy != '') {
            if ($orderBy == 'gender') {
                $q .=  ' ORDER BY users.gender ASC';
            } elseif ($orderBy == 'age') {
                $q .=  ' ORDER BY age_public DESC, users.birthdate DESC';
            } elseif ($orderBy == 'username') {
                $q .=  ' ORDER BY lower(users.username) ASC';
            } elseif ($orderBy == 'status') {
                $q .=  ' ORDER BY users.flirt_status ASC';
            } elseif ($orderBy == 'course') {
                $q .=  ' ORDER BY study_path.name_short ASC';
            }
        }/* else {
            // if no criterion given, sort by login time
            $q .=  ' ORDER BY user_online.id ASC';
        }*/
        //var_dump($q);
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up array of online users' models
        $onlineUsers = array();
        foreach ($res as $row) {
            $user = new UserMainDataModel;
            $user->buildFromRow($row);
            
            array_push($onlineUsers, $user);
        }
        
        return $onlineUsers;
    }
    
    public static function getTopUsers($crit, $limit = 3) {
        $DB = Database::getHandle();
        //
        switch ($crit) {
        case 'forumEntries':
            $dbFields = array('user_data', 'forum_entries');
            break;
        case 'courseUploads':
            $dbFields = array('user_data', 'course_file_uploads');
            break;
        case 'courseDownloads':
            $dbFields = array('user_data', 'course_file_downloads_other');
            break;
        case 'onlineActivity':
            $dbFields = array('activity_index');
            break;
        default:
            return null;
        }
        
        if (count($dbFields) > 1) {
            $q = 'SELECT u.*, EXTRACT(EPOCH FROM u.last_login) AS last_login,
                         EXTRACT(EPOCH FROM u.first_login) AS first_login
                    FROM ' . DB_SCHEMA . '.' . $dbFields[0] . ',
                         ' . DB_SCHEMA . '.users AS u
                   WHERE u.id=user_id
                     AND data_name_id = (SELECT id 
                                           FROM public.' . $dbFields[0] . '_keys 
                                          WHERE data_name = ' . $DB->Quote($dbFields[1]) . ')
                     AND u.flag_activated = true
                     AND u.flag_active = true
                ORDER BY data_value DESC,
                         username ASC
                   LIMIT ' . (int) $limit;
        } else {
            $q = 'SELECT u.*, EXTRACT(EPOCH FROM u.last_login) AS last_login,
                         EXTRACT(EPOCH FROM u.first_login) AS first_login
                    FROM ' . DB_SCHEMA . '.users AS u
                   WHERE u.flag_activated = true
                     AND u.flag_active = true
                ORDER BY ' . $dbFields[0] . ' DESC,
                         username ASC
                   LIMIT ' . (int) $limit;
        }

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return null;
        }
        if ($res->EOF) {
            return null;
        }
        
        $topUsers = array();
        foreach ($res as $row) {
            $user = new UserMainDataModel;
            $user->buildFromRow($row);
            
            array_push($topUsers, $user);
        }
        return $topUsers;
    }
    
    /**
     * searches for a user whose username contains the given string
     *
     * @param string $subString string that username must contain
     * @param int $limit maximal number of results
     * @param boolean $showValidOnly if true, only valid (activated, visible) users
     *   are returned
     * @return array array of UserModel, ordered by username ascending
     * @throws DBException on DB error
     */
    public static function searchUser($subString, $limit, $showValidOnly, $searchOldNicks) {
        $DB = Database::getHandle();
        $q = 'SELECT *,
                     EXTRACT(EPOCH FROM last_login) AS last_login,
                     EXTRACT(EPOCH FROM first_login) AS first_login,
                     LENGTH(username) - LENGTH(' . $DB->Quote($subString) . ') AS orderusername
                FROM ' . DB_SCHEMA . '.users
               WHERE username ILIKE ' . $DB->Quote('%'.$subString.'%');
        if ($showValidOnly) {
            $q .= 
               ' AND flag_invisible = false
                 AND flag_active = true
                 AND flag_activated = true ';
        }
        if ($searchOldNicks){
        	 $q .= "\n".' UNION SELECT u.*,
                                           EXTRACT(EPOCH FROM last_login) AS last_login,
                                           EXTRACT(EPOCH FROM first_login) AS first_login,
                                           LENGTH(username) - LENGTH(' . $DB->Quote($subString) . ') AS orderusername
                FROM ' . DB_SCHEMA . '.users AS u,
                     ' . DB_SCHEMA . '.user_old_nicks AS uon
               WHERE uon.old_username ILIKE ' . $DB->Quote('%'.$subString.'%')
                    . ' AND uon.user_id = u.id';
                if ($showValidOnly) {
                    $q .= 
                       ' AND flag_invisible = false
                         AND flag_active = true
                         AND flag_activated = true ';
                }
        }
        $q .= 
          ' ORDER BY orderusername ASC
              LIMIT ' . $limit;
        //var_dump($q);
        $res = $DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $userArray = array();
        foreach ($res as $row) {
            $user = new UserMainDataModel;
            $user->buildFromRow($row);
            array_push($userArray, $user);
        }
        
        return $userArray;
    }
    
    public static function searchByEmail($email, $limit) {
        $DB = Database::getHandle();
        $q = 'SELECT *,
                     EXTRACT(EPOCH FROM last_login) AS last_login,
                     EXTRACT(EPOCH FROM first_login) AS first_login
                FROM ' . DB_SCHEMA . '.users u,
                     ' . DB_SCHEMA . '.user_extra_data ue
               WHERE u.id = ue.id
                 AND (uni_email ILIKE ' . $DB->Quote('%'.$email.'%') . '
                  OR private_email ILIKE ' . $DB->Quote('%'.$email.'%') . '
                  OR public_email ILIKE ' . $DB->Quote('%'.$email.'%') . ')
            ORDER BY username ASC
               LIMIT ' . $limit;
        //var_dump($q);
        $res = $DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $userArray = array();
        foreach ($res as $row) {
            $user = new UserMainDataModel;
            $user->buildFromRow($row);
            array_push($userArray, $user);
        }
        
        return $userArray;
    }
    
    
    /**
     * searches for a user by various options
     *
     * @param string $subString string that username must contain
     * @param int $studyPathId id of study path; may be 0 for "don't care"
     * @param string $gender '0' for "don't care", otherwise (m|f|\eps)
     * @param string $flirtStatus '0' for "don't care", otherwise (red|yellow|green)
     * @param string $orderBy sort criterion: 'pointsDesc', 'pointsAsc'
     * @param boolean $showValidOnly if true, only valid (activated, visible) users
     *   are returned
     * @return array array of UserModel, ordered by given criterion
     * @throws DBException on DB error
     */
    public static function searchUserAdvanced($subString, $studyPathId, $gender, $flirtStatus, $picture, $uni, $order_by, $order = 'ASC', $limit = 20, $offset=0, $showValidOnly = true) {
        
        if(!($order == 'ASC' || $order == 'DESC')) {
            $order = 'ASC';
        }
        
        $DB = Database::getHandle();
        $q = 'SELECT u.*, EXTRACT(EPOCH FROM u.last_login) AS last_login,
                     EXTRACT(EPOCH FROM u.first_login) AS first_login ';
        
        $q .= self::buildSearchClouse($subString, $studyPathId, $gender, $flirtStatus, $picture, $uni, $order_by, $order, $limit, $offset, $showValidOnly = true);
        //var_dump( $q );
        
        $res = $DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $userArray = array();
        foreach ($res as $row) {
            $user = new UserMainDataModel;
            $user->buildFromRow($row);
            array_push($userArray, $user);
        }
        
        return $userArray;
    }
    
    protected static function buildSearchClouse($subString, $studyPathId, $gender, $flirtStatus, $picture, $uni, $order_by, $order = 'ASC', $limit = 20, $offset=0, $showValidOnly = true){
    	
        $DB = Database::getHandle();
        $q = '';
        
        if ($order_by == 'age') {
            $q .=  ' ,up.data_value <> (SELECT id FROM public.details_visible WHERE name = \'no one\') AS age_public ';
        }
        // if we search by a username and have no order criterion
        // use the string length differences
        if ($subString != '' and $order_by ==  '0') {
            $q .= ' ,LENGTH(u.username) - LENGTH(' . $DB->Quote($subString) . ') AS orderusername ';
        }
        $q .= ' FROM ';
        if ($studyPathId != 0) {
            $q.=
                    ' ' . DB_SCHEMA . '.study_path_per_student AS stud, ';
        }
        $q .= ' ' . DB_SCHEMA . '.users AS u';
        
        if ($order_by == 'age') {
            $q .= '
          LEFT JOIN ' . DB_SCHEMA . '.user_privacy up
                 ON up.user_id = u.id
              WHERE up.data_name_id = (SELECT id FROM public.user_privacy_keys WHERE data_name = \'birthdate\') ';
        } else {
            $q.= ' 
              WHERE true ';
        }
        if ($subString != '') {
            $q .= 
               ' AND username ILIKE ' . $DB->Quote('%'.$subString.'%');
        }
        if ($studyPathId != 0) {
            $q .=
               ' AND stud.study_path_id = ' . $DB->Quote($studyPathId) . ' 
                 AND stud.user_id = u.id';
        }
        if ($gender !== '0' and
                ($gender == 'm' or $gender == 'f' or $gender === '')) {
            $q .=
               ' AND u.gender = ' . $DB->Quote($gender);
        }
        if ($flirtStatus !== '0' and
                ($flirtStatus == 'red' or $flirtStatus == 'yellow' or $flirtStatus == 'green' or $flirtStatus == 'none')) {
            $q .=
               ' AND u.flirt_status ILIKE ' . $DB->Quote('%'.$flirtStatus);
        }        
        if ($picture !== '0' and $picture == 'no') {
            $q .=
               ' AND u.userpic_file = \'\' ';
        }
        elseif ($picture !== '0' and $picture == 'yes') {
            $q .=
               ' AND u.userpic_file != \'\' ';
        }
        if ($showValidOnly) {
            $q .= 
               ' AND u.flag_invisible = false
                 AND u.flag_active = true
                 AND u.flag_activated = true ';
        }
        if ($uni != 0){
            $q .=
                ' AND uni_id = ' . $DB->quote($uni);
        }
        if($order_by !== '0'){
            if($order_by == 'points'){
                $q .=  'ORDER BY points_sum '.$order;
                $q .=       ',lower(username) '.$order;
            }
            elseif($order_by == 'username'){
                $q .=  'ORDER BY lower(username) '.$order;
            }
            elseif($order_by == 'age'){
                $q .=  'ORDER BY age_public DESC, birthdate '.$order;
                $q .=       ',lower(username) '.$order;
            }
            elseif($order_by == 'random'){
                $q .=  'ORDER BY random()';
            }
            elseif($order_by == 'activity_index'){
                $q .=  'ORDER BY activity_index '.$order;
                $q .=       ',points_sum '.$order;
                $q .=       ',lower(username) '.$order;
            }            
        } 
        // if we search by a username and have no order criterion
        // use the string length differences
        else if ($subString != '') {
            $q .= ' ORDER BY orderusername ASC';
        } // end if $order_by ...
        
        if($limit !== null){
            $q .=' LIMIT '. $DB->quote($limit);
            $q .=' OFFSET '.$DB->quote($offset);
        }
        
        return $q;
    }
    
     public static function countSearchUserAdvanced($subString, $studyPathId, $gender, $flirtStatus, $picture, $uni) {
        
        $DB = Database::getHandle();
        $q = 'SELECT count(u.id) AS nr ';
        
        $q .= self::buildSearchClouse($subString, $studyPathId, $gender, $flirtStatus, $picture, $uni, '0', 'ASC', null);
        //var_dump( $q );
        
        $res = $DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        return $res->fields['nr'];
    }
    
    public static function createFromRegisterData($username, $password, $uniId, $personType = 1) {
        $user = new UserMainDataModel;
        $user->data['password'] = $password;
        $user->data['uni_id'] = $uniId;
        $user->data['person_type'] = $personType;
        $user->data['username'] = $username;
        $user->userId = null;
        return $user;
    }
    
    
    protected static function calculateAge($dateString) {
    	$date = explode('-', $dateString);
        $today = explode('-', date('Y-m-d'));
        $age = $today[0] - $date[0];
        if (31*$today[1]+$today[2] < 31*$date[1]+$date[2]) {
            return $age-1;
        } else {
        	return $age;
        }
    }
    
    public function save() {
    	// if we have no user id here, perform an INSERT
        if (!$this->userId) {
            $DB = Database::getHandle();
            $keyValue = array();        
            foreach ($this->data as $key => $val) {
                // skip cached objects
                if (substr($key, -4, 4) == '_obj') {
                    continue;
                }
                
                if ($val instanceof BaseModel){
                	$keyValue[$key] = $DB->Quote($val->id);
                } else {
                    $keyValue[$key] = $DB->Quote($val);
                }
                
                // special treatment for timestamp values
                if ($key == 'last_login') {
                    $keyValue['last_login'] = Database::getPostgresTimestamp($val);
                }
            }
        
            // check, if we have no query to execute
            if (count($keyValue) == 0) {
                return;
            }
            
            $q = $this->buildSqlStatement($this->tableName, $keyValue, true);
            //var_dump($q);
            if (!$DB->execute($q)) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
        } else {
        	// for UPDATEs we can rely on parent method
        	parent::save();
        }
    }
    
    public function getValue($name) {
        if ($name == 'age' and !array_key_exists('age', $this->data)) {
            $birthdate = parent::getValue('birthdate');
            $this->data['age'] = self::calculateAge($birthdate);
            return $this->data['age'];
        }
        if ($name == 'uni_obj' and (!array_key_exists('uni_obj', $this->data) or
                                     $this->data['uni_obj'] == null)) {
            $uniId = parent::getValue('uni_id');
            $this->data['uni_obj'] = UniversityModel::getUniversityById($uniId);
            return $this->data['uni_obj'];
        }
        if ($name == 'country_obj' and (!array_key_exists('country_obj', $this->data) or
                                        $this->data['country_obj'] == null)) {
            $countryId = parent::getValue('country_id');
            $this->data['country_obj'] = CountryModel::getCountryById($countryId);
            return $this->data['country_obj'];
        }
        if ($name == 'nationality_obj' and (!array_key_exists('nationality_obj', $this->data) or
                                            $this->data['nationality_obj'] == null)) {
            $natId = parent::getValue('nationality_id');
            $this->data['nationality_obj'] = CountryModel::getCountryById($natId);
            return $this->data['nationality_obj'];
        }
        if ($name == 'person_type_obj' and (!array_key_exists('person_type_obj', $this->data) or
                                            $this->data['person_type_obj'] == null)) {
            $natId = parent::getValue('person_type');
            $this->data['person_type_obj'] = PersonTypeModel::getPersonTypeById($natId);
            return $this->data['person_type_obj'];
        }
        if ($name == 'details_visible_for_obj' and (!array_key_exists('details_visible_for_obj', $this->data) or
                                            $this->data['details_visible_for_obj'] == null)) {
            $visId = parent::getValue('details_visible_for');
            $this->data['details_visible_for_obj'] = DetailsVisibleModel::getDetailsVisibleById($visId);
            return $this->data['details_visible_for_obj'];
        }
        if ($name == 'small_userpic_file') {
            $pic = parent::getValue('userpic_file');
            $this->data['small_userpic_file']= AttachmentHandler::getSmallVariantPath($pic);
        }
        if ($name == 'tiny_userpic_file') {
            $pic = parent::getValue('userpic_file');
            $this->data['tiny_userpic_file']= AttachmentHandler::getTinyVariantPath($pic);
        }
        if ($name == 'fancy_userpic_file') {
            $pic = parent::getValue('userpic_file');
            $this->data['fancy_userpic_file']= AttachmentHandler::getFancyVariantPath($pic);
        }
        return parent::getValue($name);
    }
    
    protected function __get($name) {
    	if ($name == 'age' and !array_key_exists('age', $this->data)) {
    		$birthdate = parent::getValue('birthdate');
            $this->data['age'] = self::calculateAge($birthdate);
            return $this->data['age'];
    	}
        if ($name == 'uni_obj' and (!array_key_exists('uni_obj', $this->data) or
                                     $this->data['uni_obj'] == null)) {
            $uniId = parent::getValue('uni_id');
            $this->data['uni_obj'] = UniversityModel::getUniversityById($uniId);
            return $this->data['uni_obj'];
        }
        if ($name == 'country_obj' and (!array_key_exists('country_obj', $this->data) or
                                        $this->data['country_obj'] == null)) {
            $countryId = parent::getValue('country_id');
            $this->data['country_obj'] = CountryModel::getCountryById($countryId);
            return $this->data['country_obj'];
        }
        if ($name == 'nationality_obj' and (!array_key_exists('nationality_obj', $this->data) or
                                            $this->data['nationality_obj'] == null)) {
            $natId = parent::getValue('nationality_id');
            $this->data['nationality_obj'] = CountryModel::getCountryById($natId);;
            return $this->data['nationality_obj'];
        }
        if ($name == 'person_type_obj' and (!array_key_exists('person_type_obj', $this->data) or
                                            $this->data['person_type_obj'] == null)) {
            $natId = parent::getValue('person_type');
            $this->data['person_type_obj'] = PersonTypeModel::getPersonTypeById($natId);;
            return $this->data['person_type_obj'];
        }
        if ($name == 'details_visible_for_obj' and (!array_key_exists('details_visible_for_obj', $this->data) or
                                            $this->data['details_visible_for_obj'] == null)) {
            $visId = parent::getValue('details_visible_for');
            $this->data['details_visible_for_obj'] = DetailsVisibleModel::getDetailsVisibleById($visId);;
            return $this->data['details_visible_for_obj'];
        }
        if ($name == 'small_userpic_file') {
        	$pic = parent::getValue('userpic_file');
            $this->data['small_userpic_file']= AttachmentHandler::getSmallVariantPath($pic);
        }
        if ($name == 'tiny_userpic_file') {
            $pic = parent::getValue('userpic_file');
            $this->data['tiny_userpic_file']= AttachmentHandler::getTinyVariantPath($pic);
        }
        if ($name == 'fancy_userpic_file') {
            $pic = parent::getValue('userpic_file');
            $this->data['fancy_userpic_file']= AttachmentHandler::getFancyVariantPath($pic);
        }
    	return parent::getValue($name);
    }
}

?>
