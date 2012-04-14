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

# $Id: right_model.php 5743 2008-03-25 19:48:14Z ads $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/right_model.php $

require_once MODEL_DIR.'/base/base_model.php';

/**
 * class representing one right in using the application
 *
 * @package Models
 * @subpackage Base
 */
class RightModel extends BaseModel {
    protected $name;
    protected $description;
    protected $isGroupSpecific;
    
    public function __construct($id = null, $name = null, $description = null, $isGroupSpecific = null) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->isGroupSpecific = $isGroupSpecific;
    }
    
    public function getName() {
        return $this->name;
    }
    public function getDescription() {
        return $this->description;
    }
    public function isGroupSpecific() {
        return $this->isGroupSpecific;
    }
    
    /**
     * returns an associative array of rights with specified ids
     * <b>note:</b> the array does <b>not</b> preserve the order of the given ids
     *
     * @param array $ids array of int: the ids of right entries to retrieve
     * @return array associative array of RightModel, the hash keys are the rights' names
     * @throws DBException on database error
     */
    public static function getRightsByIds($ids) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.rights
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $rights = array();
        foreach ($res as $k => $row) {
            $right = new RightModel($row['id'], $row['name']);
            $rights[$right->name] = $right;
        }
        
        return $rights;
    }
    
    /**
     * returns the right of a specified id
     * 
     * @param int $id  int: the id of right entries to retrieve
     * @return RightModel
     * @throws DBException on database error
     */
    public static function getRightById($id) {
        $DB = Database::getHandle();        
        
        $q = 'SELECT id, name, description
                FROM ' . DB_SCHEMA . '.rights
               WHERE id = ' . $DB->Quote($id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach ($res as $k => $row) {
            return new RightModel($row['id'], $row['name'], $row['description']);            
        }
    }
        
    /**
     * returns an array of all UserRights
     *
     * @param boolean $showGroupSpecific including the rights spezial for groups
     *
     * @return array array of RightModel
     * @throws DBException on database error
     */
    public static function getAllUserRights() {
        //die('use getAllUserRights or getAllGroupRights instead');
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, description
                FROM ' . DB_SCHEMA . '.rights
               WHERE is_group_specific=' . $DB->quote(Database::makeBooleanString(false)) . '
            ORDER BY name';
                
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $rights = array();
        foreach ($res as $k => $row) {
            $right = new RightModel($row['id'], $row['name'], $row['description']);
            $rights[] = $right;
        }
        
        return $rights;
    }
    
    /**
     * returns an array of GroupRights
     *
     * @return array array of RightModel
     * @throws DBException on database error
     */
    public static function getAllGroupRights() {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, description, is_group_specific
                FROM ' . DB_SCHEMA . '.rights
               WHERE is_group_specific=' . $DB->quote(Database::makeBooleanString(true)) . '
            ORDER BY name';
                
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $rights = array();
        foreach ($res as $k => $row) {
            $right = new RightModel($row['id'], $row['name'], $row['description'], $row['is_group_specific']);
            $rights[] = $right;
        }
        
        return $rights;
    }
    
    public static function getAllExplicitRightsByUser($user, $asModel = true) {
    	$DB = Database::getHandle();
        $q = 'SELECT r.id, r.name, r.description, r.is_group_specific, ru.right_granted AS right_granted 
                FROM ' . DB_SCHEMA .'.rights_user AS ru, 
                     ' . DB_SCHEMA .'.rights AS r
                 WHERE user_id=' . $DB->quote($user->id) . '
                      AND r.id = ru.right_id ';
            
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        if($asModel){
        	$rights = array();
            foreach ($res as $k => $row) {
                $right = new RightModel($row['id'], $row['name'], $row['description'], $row['is_group_specific']);
                $rights[] = $right;
            }
            
            return $rights;
        }
        
        $rights = array();
        foreach ($res as $k => $row) {
            $rights[$row['id']] = Database::convertPostgresBoolean( $row['right_granted'] );
        }
        
        return $rights;
    }
    
    public static function getAllExplicitRightsByRole($role, $asModel = true) {
        
        $DB = Database::getHandle();
        
         $q = 'SELECT r.id, r.name, r.description, r.is_group_specific, rr.right_granted AS right_granted 
                FROM ' . DB_SCHEMA .'.rights_role AS rr, 
                     ' . DB_SCHEMA .'.rights AS r
                 WHERE rr.role_id=' . $DB->quote($role->id) . '
                      AND r.id = rr.right_id 
                      AND rr.right_granted=true';
            
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        if($asModel){
            $rights = array();
            foreach ($res as $k => $row) {
                $right = new RightModel($row['id'], $row['name'], $row['description'], $row['is_group_specific']);
                $rights[] = $right;
            }
            
            return $rights;
        }
        
        $rightIds = array();
        foreach ($res as $row) {
            $rightIds[$row['id']] = $row['id'];
        }
        return $rightIds;
        
    }
    
    public static function getAllRightIdsByRoleId($roleId) {
        
        $DB = Database::getHandle();
        
        $q = 'SELECT r.id, rr.right_granted AS right_granted 
                FROM ' . DB_SCHEMA .'.rights_role AS rr, 
                     ' . DB_SCHEMA .'.rights AS r
                 WHERE rr.role_id = ' . $DB->Quote($roleId) . '
                   AND r.id = rr.right_id 
              ORDER BY right_granted DESC';
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
                
        $rightIds = array();
        foreach ($res as $row) {
            if (Database::convertPostgresBoolean($row['right_granted'])) {
              $rightIds[ $row['id'] ] = $row['id'];
            } else {
              $rightIds[ -$row['id'] ] = false;
            }
        }
        return $rightIds;
        
    }
    
    public static function getAllExplicitRightsByRoleIds($roleIds, $asModel = true) {
        
        $DB = Database::getHandle();
        
        $idString = Database::makeCommaSeparatedString($roleIds);
        
        /*$q = 'SELECT r.id, r.name, r.description, r.is_group_specific, rr.right_granted AS right_granted 
                FROM ' . DB_SCHEMA .'.rights_role AS rr, 
                     ' . DB_SCHEMA .'.rights AS r
                 WHERE rr.role_id IN (' . $idString . ') 
                   AND r.id = rr.right_id 
                   ORDER BY right_granted DESC';
                   #AND rr.right_granted=true';
        */
        
        $q = 'SELECT r.id, r.name, r.description, r.is_group_specific, rr.right_granted AS right_granted 
                FROM ' . DB_SCHEMA .'.rights_role AS rr, 
                     ' . DB_SCHEMA .'.rights AS r
                 WHERE rr.right_id IN (SELECT right_id 
                                         FROM ' . DB_SCHEMA .'.rights_role 
                                        WHERE role_id IN (' . $idString . ')
                                          AND right_granted = true
                                    EXCEPT
                                       SELECT right_id 
                                         FROM ' . DB_SCHEMA .'.rights_role 
                                        WHERE role_id IN (' . $idString . ')
                                          AND right_granted = false) 
                   AND r.id = rr.right_id';
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        if($asModel){
            $rights = array();
            foreach ($res as $k => $row) {
                $right = new RightModel($row['id'], $row['name'], $row['description'], $row['is_group_specific']);
                $rights[] = $right;
            }
            
            return $rights;
        }
        
        $rightIds = array();
        foreach ($res as $row) {
            $rightIds[$row['id']] = $row['id'];
        }
        return $rightIds;
        
    }
    
    public static function getAllExplicitRightsByGroup($group, $asModel = true) {
        
        $DB = Database::getHandle();
        $q = 'SELECT gr.right_id AS id,  r.name AS right_name, r.description AS description, r.is_group_specific AS is_group_specific
                FROM ' . DB_SCHEMA . '.rights_group AS gr,' 
                       . DB_SCHEMA .'.rights AS r
                WHERE r.id = gr.right_id   
                  AND group_id=' . $DB->quote($group->id);

        //var_dump($q);
            
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        if($asModel){
        	$rights = array();
            foreach ($res as $k => $row) {
                $right = new RightModel($row['id'], $row['right_name'], $row['description'], $row['is_group_specific']);
                $rights[] = $right;
            }
            
            return $rights;
        }
        
        $rights = array();
        foreach ($res as $k => $row) {
            $rights[$row['id']] = true;
        }
        
        return $rights;
    }
    
     /**
     * collects all rights that specified user have in all his group
     *
     * @param UserModel $user
     * @param string    property may be id or name
     * @return array of boolean, has key are groupId and right names
     * @throws DBException on DB error
     * 
     */
    public static function getAllExplicitUserGroupRightsByUser($user, $property = 'id') {
        $groups = $user->getGroupMembership();
        
        if (count($groups) == 0) {
            return array();
        }
        
        $DB = Database::getHandle();
        $q = 'SELECT gr.right_id AS right_id, gr.group_id AS group_id, r.name AS right_name
                FROM ' . DB_SCHEMA . '.rights_user_group AS gr,' 
                       . DB_SCHEMA .'.rights AS r
                WHERE group_id in (' . Database::makeCommaSeparatedString($groups, 'id') . ') 
                  AND r.id = gr.right_id   
                  AND user_id=' . $DB->quote($user->id);

        //var_dump($q);
            
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        if ($property != 'id' and $property != 'name') {
            $property = 'id';
        }
        
        $rights = array();
        foreach ($res as $k => $row) {
            $rights[$row['group_id']][$row['right_' . $property]] = true;
        }
        
        return $rights;
        
    }
    
    /**
     * collects all rights that specified user has on behalf of his role memberships and
     * usership itsself
     *
     * @param int $user_id id of user to examine
     * @return array associative array of RightModel, has key are right names
     * @throws DBException on DB error
     */
    public static function getGrantedUserRightsByUserId($user) {
        $DB = Database::getHandle();
        
        // load all basic rights, that everyone has
        $q = 'SELECT id, name
                FROM '.DB_SCHEMA.'.rights
            WHERE default_allowed=true';
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // initialize return array
        $rightsArray = array();
        
        foreach ($res as $k => $row) {
            $right = new RightModel($row['id'], $row['name']);
            $rightsArray[$right->name] = $right;
        }
        
        // if no user id is given, return only the basic rights
        if (!$user) {
            return $rightsArray;
        }
        $userAttribute = 'user_id';
        if ($user->isExternal()) {
        	$userAttribute = 'user_external_id';
        }

        // load all role rights
        // note: order by right_granted, so that positive role rights override negative ones
        //       (false right_granted before true right_granted)
        $q = 'SELECT rights.id AS right_id, rights.name AS right_name,right_granted
                FROM '.DB_SCHEMA.'.rights_role AS rights_role,'.DB_SCHEMA.'.rights AS rights,'.DB_SCHEMA.'.user_roles AS roles
               WHERE role_id IN (SELECT role_id FROM '.DB_SCHEMA.'.user_role_membership WHERE ' . $userAttribute . '='.$DB->Quote($user->id).') 
                 AND rights_role.right_id=rights.id
                 AND rights_role.role_id=roles.id
            ORDER BY right_granted DESC'; // order DESC so that negative rights
            // will override positive ones
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        foreach ($res as $k => $row) {
            $granted = Database::convertPostgresBoolean($row['right_granted']);
            // if right is granted, set entry to true, overriding default settings
            if ($granted && !array_key_exists($row['right_name'],$rightsArray)) {
                $right = new RightModel($row['right_id'], $row['right_name']);
                $rightsArray[$right->name] = $right;
            }
            // if right is denied, unset related hash entry, overriding default settings
            else if (!$granted) {
                unset($rightsArray[$row['right_name']]);
            }
        }

        // load all user specific rights
        $q = 'SELECT rights.id AS right_id, rights.name AS right_name, right_granted
                FROM '.DB_SCHEMA.'.rights_user AS rights_user,'.DB_SCHEMA.'.rights AS rights
               WHERE rights_user.right_id=rights.id
                 AND rights_user.' . $userAttribute . '='.$DB->Quote($user->id);
                
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach ($res as $k => $row) {
            $granted = Database::convertPostgresBoolean($row['right_granted']);
            // if right is granted, set entry to true, overriding default settings and role rights
            if ($granted and (!array_key_exists($row['right_name'],$rightsArray) or
                              $rightsArray[$row['right_name']])) {
                $right = new RightModel($row['right_id'], $row['right_name']);
                $rightsArray[$right->name] = $right;
            }
            // if right is denied, unset related hash entry, overriding default settings and role rights
            else if (!$granted) {
                unset($rightsArray[$row['right_name']]);
            }
        }

        return $rightsArray;
    }
    
    /**
     * collects all rights that specified role has
     *
     * @param int $roleId id of role to examine
     * @return array associative array of RightModel, has key are right names
     * @throws DBException on DB error
     */
    public static function getGrantedRoleRightsByRoleId($roleId) {
        $DB = Database::getHandle();
       
        // TODO: merge code with upper method
         
        // load all basic rights, that everyone has
        $q = 'SELECT id, name
                FROM '.DB_SCHEMA.'.rights
            WHERE default_allowed=true';
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // initialize return array
        $rightsArray = array();
        
        foreach ($res as $k => $row) {
            $right = new RightModel($row['id'], $row['name']);
            $rightsArray[$right->name] = $right;
        }
        
        // if no user id is given, return only the basic rights
        if ($roleId == null) {
            return $rightsArray;
        }

        // load all role rights
        // note: order by right_granted, so that positive role rights override negative ones
        //       (false right_granted before true right_granted)
        $q = 'SELECT rights.id AS right_id, rights.name AS right_name,right_granted
                FROM '.DB_SCHEMA.'.rights_role AS rights_role,'.DB_SCHEMA.'.rights AS rights,'.DB_SCHEMA.'.user_roles AS roles
               WHERE role_id ='.$DB->Quote($roleId).' 
                 AND rights_role.right_id=rights.id
                 AND rights_role.role_id=roles.id
            ORDER BY right_granted DESC'; // order DESC so that negative rights
            // will override positive ones
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        foreach ($res as $k => $row) {
            $granted = Database::convertPostgresBoolean($row['right_granted']);
            // if right is granted, set entry to true, overriding default settings
            if ($granted && !array_key_exists($row['right_name'],$rightsArray)) {
                $right = new RightModel($row['right_id'], $row['right_name']);
                $rightsArray[$right->name] = $right;
            }
            // if right is denied, unset related hash entry, overriding default settings
            else if (!$granted) {
                unset($rightsArray[$row['right_name']]);
            }
        }

        return $rightsArray;
    }
    
        /**
     * set all granted rights by an array of rights
     * 
     * @param integer $userId the user to set
     * @param array $rights a array of rights
     */
    public static function setUserRights($userId, $rights, $granted) {
        $DB = Database::getHandle();
        
        $DB->StartTrans();

        $rightIds = Database::makeCommaSeparatedString($rights);
        /* delete the users that are no more moderators */
        $stmt = 'DELETE FROM '. DB_SCHEMA . '.rights_user WHERE user_id=' . $DB->quote($userId); 
            
        /* when nothing is there only delete */    
        if (count($rights) != 0) {
            $stmt .= ' AND right_id NOT IN (' . $rightIds . ')'; 
        }
        $stmt .= ' AND right_granted=' . $DB->quote(Database::makeBooleanString($granted));
        
        $res = $DB->execute($stmt);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        /* we have nothing to insert */
        if (count($rights) == 0) {
            if(!$DB->CompleteTrans()){
                throw new DBException(DB_TRANSACTION_FAILED);
            }        
            return;
        }
                
        /* add the new users */
        $stmt = 'INSERT INTO ' . DB_SCHEMA . '.rights_user (right_id, user_id, right_granted)  
                    SELECT ' . DB_SCHEMA . '.rights.id, ' . $DB->quote($userId) . ', '. $DB->quote(Database::makeBooleanString($granted)) .
                    ' FROM ' . DB_SCHEMA . '.rights 
                     WHERE ' . DB_SCHEMA . '.rights.id IN ('. $rightIds . ') 
                       AND ' . DB_SCHEMA . '.rights.id NOT IN  
                            (SELECT right_id 
                               FROM ' . DB_SCHEMA . '.rights_user 
                              WHERE user_id = ' . $DB->quote($userId) . ' 
                                AND right_id IN ('. $rightIds . ')  
                                AND right_granted=' . $DB->quote(Database::makeBooleanString($granted)) . ')';
        

        //var_dump($stmt);
                                
        $res = $DB->execute($stmt);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        if(!$DB->CompleteTrans())
            throw new DBException(DB_TRANSACTION_FAILED);        
    }
    
    public static function setRoleRights($roleId, $rights, $granted = true) {
        $DB = Database::getHandle();
        
        $DB->StartTrans();
        
        /* collect ids */
        $rightIds = Database::makeCommaSeparatedString($rights);

        $stmt = 'DELETE FROM '. DB_SCHEMA . '.rights_role WHERE role_id=' . 
            $DB->quote($roleId); 
            
        /* when nothing is there only delete */    
        if(count($rights) != 0){
            $stmt .= ' AND right_id NOT IN ('. $rightIds . ')'; 
        }
        
        $stmt .= ' AND right_granted=' . $DB->quote(Database::makeBooleanString($granted));
        
        #var_dump($stmt);
        $res = $DB->execute($stmt);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        /* we have nothing to insert */
        if(count($rights) == 0) {
            if(!$DB->CompleteTrans()) {
                throw new DBException(DB_TRANSACTION_FAILED);
            }        
        }
                
        /* add the new users */
        $stmt = 'INSERT INTO ' . DB_SCHEMA . '.rights_role (right_id, role_id, right_granted)  
                    SELECT ' . DB_SCHEMA . '.rights.id, ' . $DB->quote($roleId) . ', '.$DB->quote(Database::makeBooleanString($granted)) .
                    ' FROM ' . DB_SCHEMA . '.rights 
                     WHERE ' . DB_SCHEMA . '.rights.id IN ('. $rightIds . ')';
        #var_dump($stmt);                        
        $res = $DB->execute($stmt);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        if(!$DB->CompleteTrans()){
            throw new DBException(DB_TRANSACTION_FAILED);
        }    
    }
    
    public static function setGroupRights($groupId, $rights) {
        $DB = Database::getHandle();
        
        $DB->StartTrans();
        
        $rightIds = Database::makeCommaSeparatedString($rights);

        /* delete the users that are no more moderators */
        $stmt = 'DELETE FROM '. DB_SCHEMA . '.rights_group
                         WHERE group_id=' . $DB->quote($groupId)
                         .' AND right_id NOT IN ( '.$rightIds.' )'; 
            
        /* when nothing is there only delete */    
        /*if(count($rights) != 0)
            $stmt .= ' AND right_id NOT IN ('. $rightIds . ')';*/ 
        
        //var_dump($stmt);
        
        $res = $DB->execute($stmt);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        /* we have nothing to insert */
        if(count($rights) == 0) {       
            if(!$DB->CompleteTrans()) {
                throw new DBException(DB_TRANSACTION_FAILED);        
            }
            return;
        }
                
        /* add the new users */
        //TODO: eine bessere SQL statment bauen
        foreach($rights as $right){
            $stmt = 'INSERT INTO ' . DB_SCHEMA . '.rights_group
                                 (right_id, group_id)
                          VALUES (' . $DB->quote($right) . ',
                                  ' . $DB->quote($groupId) . ')';
            //var_dump($stmt);                        
            $res = $DB->execute($stmt);
        }
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        if(!$DB->CompleteTrans()) {
            throw new DBException(DB_TRANSACTION_FAILED);        
        }
    }
    
    /**
     * set all granted rights by an array of rights
     * 
     * @param integer $userId the user to set
     * @param integer $groupId the group to set
     * @param array $rights a array of rights
     * 
     * TODO: gezielt rechte geben und löschen
     * Momentan: alle GroupRechte werden gelöscht und nur die gegebenen werden wieder eingefügt
     * sorry das war mir zu komplex habe ich nicht auf die reihe bekommen, soll jemand anders bitte drauf schauen
     * 
     * @author schnueptus
     */
    public static function setUserGroupRights($userId, $groupId, $rights) {
        $DB = Database::getHandle();
        
        $DB->StartTrans();
        
        $rightIds = implode(',',$rights);

        /* delete the users that are no more moderators */
        $stmt = 'DELETE FROM '. DB_SCHEMA . '.rights_user_group
                       WHERE user_id=' . $DB->quote($userId) . '
                         AND group_id=' . $DB->quote($groupId); 
            
        /* when nothing is there only delete */    
        /*if(count($rights) != 0)
            $stmt .= ' AND right_id NOT IN ('. $rightIds . ')';*/ 
        
        //var_dump($stmt);
        
        $res = $DB->execute($stmt);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        /* we have nothing to insert */
        if(count($rights) == 0) {    
            if(!$DB->CompleteTrans()) {
                throw new DBException(DB_TRANSACTION_FAILED);        
            }
            return;
        }
                
        /* add the new users */
        //TODO: eine bessere SQL statment bauen
        foreach($rights as $right){
            $stmt = 'INSERT INTO ' . DB_SCHEMA . '.rights_user_group
                                 (right_id, user_id, group_id)
                          VALUES (' . $DB->quote($right) . ',
                                  ' . $DB->quote($userId) . ',
                                  ' . $DB->quote($groupId) . ')';
            //var_dump($stmt);                        
            $res = $DB->execute($stmt);
        }
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        if(!$DB->CompleteTrans()) {
            throw new DBException(DB_TRANSACTION_FAILED);        
        }
    }
    
    
    
    
}

?>
