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

# $Id: role_model.php 5743 2008-03-25 19:48:14Z ads $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/role_model.php $
#
# group management

require_once MODEL_DIR.'/base/base_model.php';
require_once MODEL_DIR.'/base/role_model.php';


/**
 * Class representing (right) role for users
 * @author linap, kyle
 * @package Core
 */
class RoleModel extends BaseModel {
    public $name;
    public $description;

    protected $rights = null;

    public function __construct($dbRow = null) {
        parent::__construct();
        
        if($dbRow != null) {
            $this->name = $dbRow['name'];
            $this->description = $dbRow['description'];
            $this->id = $dbRow['id'];
        }
    }
    
    /**
     * returns an array of roles with specified ids
     * <b>note:</b> the array does <b>not</b> preserve the order of the given ids
     *
     * @param array $ids array of int: the ids of role entries to retrieve
     * @return array array of RoleModel
     * @throws DBException on database error
     */
    public static function getRolesByIds($ids) {
        if (count($ids) == 0) {
            return array();
        }
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name
                FROM '.DB_SCHEMA.'.user_roles
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $roles = array();
        foreach ($res as $k => $row) {
            $role = new RoleModel;
            
            $role->id            = $row['id'];
            $role->name          = $row['name'];
            
            array_push($roles, $role);
        }
        
        return $roles;
    }

    /**
     * returns a role with the specified id
     *
     * @param integer $id the id of the role to retrieve
     * @return RoleModel
     * @throws DBException on database error
     */
    public static function getRoleById($id) {

        $DB = Database::getHandle();
        
        $q = 'SELECT * FROM '.DB_SCHEMA.'.user_roles
               WHERE id = ' . $DB->quote($id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach ($res as $k => $row) {
            return new RoleModel($row);
        }
    }
    
    /**
     * returns a role specified by name
     *
     * @param string $name name of role
     * @return RoleModel
     * @throws DBException on database error
     */
    public static function getRoleByName($name) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.user_roles
               WHERE name = ' . $DB->Quote($name);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $role = new RoleModel;
        
        $role->id            = $res->fields['id'];
        $role->name          = $res->fields['name'];
        
        return $role;
    }

    /**
     *
     * @throws DBException on DB error
     */
    public static function getAllRoles() {
        $DB = Database::getHandle();
        
        $groupArray = array();
        $q = 'SELECT id, name, description
                FROM '.DB_SCHEMA.'.user_roles
            ORDER BY name';
        $res = &$DB->execute( $q );
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,
                                    $DB->ErrorMsg() ) );
        }
        
        foreach( $res as $row ) {
            array_push($groupArray, new RoleModel($row));
        }
        
        return $groupArray;
    }

    /**
     * adds user to a role
     *
     * @param array $users array of user to add
     * @throws DBException on DB error
     */
    public function addUsers($users) {
        $DB = Database::getHandle();

        /* collect ids */
        $userIds = Database::makeCommaSeparatedString($users, 'id');

        /* add the new users */
        $stmt = 'INSERT INTO ' . DB_SCHEMA . '.user_role_membership (user_id, role_id)  
                    SELECT ' . DB_SCHEMA . '.users.id, ' . $DB->quote($this->id) . 
                        ' FROM ' . DB_SCHEMA . '.users
                          WHERE ' . DB_SCHEMA . '.users.id IN ('. $userIds . ') AND ' . DB_SCHEMA . '.users.id NOT IN  
                            (SELECT user_id FROM ' . DB_SCHEMA . '.user_role_membership 
                                WHERE role_id = ' . $DB->quote($this->id) . ' AND user_id IN ('. $userIds . '))';

        if (!$DB->execute($stmt)) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
/**
     * adds external user to a role
     *
     * @param array $users array of user to add
     * @throws DBException on DB error
     */
    public function addExternalUser($externalUser) {
        $DB = Database::getHandle();

        /* add the new users */
        $stmt = 'INSERT INTO ' . DB_SCHEMA . '.user_role_membership (user_external_id, role_id)  
                    SELECT ' . DB_SCHEMA . '.external_users.id, ' . $DB->quote($this->id) . 
                        ' FROM ' . DB_SCHEMA . '.external_users
                          WHERE ' . DB_SCHEMA . '.external_users.id = ' . $DB->Quote($externalUser->id) . ' AND ' . DB_SCHEMA . '.external_users.id NOT IN  
                            (SELECT user_external_id FROM ' . DB_SCHEMA . '.user_role_membership 
                                WHERE role_id = ' . $DB->quote($this->id) . ' AND user_external_id = ' . $DB->Quote($externalUser->id) . ')';

        if (!$DB->execute($stmt)) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    /**
     * dels user to a role
     *
     * @param array $users array of user to add
     * @throws DBException on DB error
     */
    public function delUsers($users) {
        $DB = Database::getHandle();

        /* collect ids */
        $userIds = Database::makeCommaSeparatedString($users, 'id');

        $stmt = 'DELETE FROM '. DB_SCHEMA . '.user_role_membership 
                       WHERE role_id=' . $DB->quote($this->id) . ' 
                         AND user_id IN (' . $userIds . ')'; 

        if (!$DB->execute($stmt)) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    /**
     * removes a user from a role
     *
     * @param int $role_id id of role user to add to
     * @param int $user_id id of user to add
     * @throws DBException on DB error
     */
    public static function userRemove($role_id, $user_id) {
        global $DB;
        
        $q = 'DELETE FROM '.DB_SCHEMA.'.user_role_membership
                    WHERE user_id='.$DB->Quote( $user_id ).'
                    AND role_id='.$DB->Quote( $role_id );
        $res = &$DB->execute( $q );
        if (!$res) {
        throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    public function listAllRights() {
        return Rights::roleListAllRights($this->id);
    }

    protected function __get($name) {
        throw new CoreException('dont use get with ' .  $name);
        
        if($name == 'rights') {
            //die ('use a method of the RightModel');
            return $this->safeReturn('rights', 'getRoleRights');
        }
    }
    
    protected function __set($name, $value) {
        throw new CoreException('dont use set with ' .  $name);
        if($name == 'rights' && is_array($value)) {
            //die ('use RightModel::setRoleRights');
            $this->rightsLoaded = true;
            return $this->rights = $value;
        }
    }
    
    public function delete() {
        $DB = Database::getHandle();
        $DB->execute('DELETE FROM user_roles WHERE id=' . $DB->quote($this->id));        
    }
    
    public function save() {
        $DB = Database::getHandle();
        
         $keyValue = array(
            'name' => $DB->quote($this->name), 
            'description' => $DB->quote($this->description));
            
         $DB->StartTrans();
         
         $res = null;
         
         /* insert or update? */
         if($this->id == null) {
            $stmt = $this->buildSqlStatement('user_roles', $keyValue);
            $res = $DB->execute($stmt);

            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
            
            $this->id = Database::getCurrentSequenceId($DB, 'user_roles', 'id');
         } else {
            $stmt = $this->buildSqlStatement('user_roles', $keyValue, false, 'id=' . $DB->quote($this->id));
            $res = $DB->execute($stmt);

            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
         }

        /* when no rights loaded we are ready */
        if($this->rights === null) {
            if(!$DB->CompleteTrans()) {
                throw new DBException(DB_TRANSACTION_FAILED);
            }
        }   
    }
    

    /**
     * returns an array of right ids the specified roles have
     * <b>note:</b> the array does <b>not</b> preserve the order of the given ids
     *
     * @param array $ids array of int: the ids of the roles
     * @return array array of int: ids of roles' rights
     * @throws DBException on database error
     */
    public function getRoleRights() {
        if ($this->rights === null) {
          $this->rights = RightModel::getAllRightIdsByRoleId($this->id, false);
        }
        return $this->rights;
    }
    
}


?>
