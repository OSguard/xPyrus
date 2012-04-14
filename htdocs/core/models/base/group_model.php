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
 * Created on 01.07.2006 by schnueptus
 * Sunburner Unihelp.de
 */
require_once MODEL_DIR.'/base/base_model.php';
require_once MODEL_DIR.'/base/user_model.php';

require_once MODEL_DIR.'/group/group_infopage_model.php';

require_once CORE_DIR . '/interfaces/addressable_entity.php';
 
class GroupModel extends BaseModel implements AddressableEntity {
    public $title;   
    public $name;
    public $description;
    public $logoUrl;
    public $isVisible;
    
    /**
     * @var array
     * all Members as UserModel of the Group
     */
    protected $members;
    
    protected $infopage;
    
    protected $isVisitorMember;
    protected $forum;
    
    function __construct($dbRow = null){
    	parent :: __construct();
        
        $this->isVisitorMember = null;
        
        if($dbRow != null) {
            
            $this->id = $dbRow['id'];
            $this->name = $dbRow['name'];
            $this->title = $dbRow['title'];
            
            if(array_key_exists('description', $dbRow))
                $this->description = $dbRow['description']; 

            if(array_key_exists('is_visible', $dbRow))
                $this->isVisible = Database::convertPostgresBoolean($dbRow['is_visible']); 
                
            if(array_key_exists('infopage_raw', $dbRow)){
            	$infopage_raw = $dbRow['infopage_raw'];
                if(array_key_exists('infopage_parsed', $dbRow)){
                    $infopage_parsed = $dbRow['infopage_parsed'];
                }else{
                	$infopage_parsed = null;
                }
                $this->infopage = new GroupInfopageModel($this, $infopage_parsed, $infopage_raw);
            }            
            if(array_key_exists('grouppic_file', $dbRow)){
            	$this->logoUrl = $dbRow['grouppic_file'];
            }    
        }
    }
    
    /**
     * Return all Groups in the Database
     */    
    public static function getAllGroups() {
        $DB = Database::getHandle();
        
        $res = $DB->execute('SELECT * FROM groups ORDER BY name');
        
        if(!$res)
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            
        $groups = array();
        
        foreach($res as $row) {
            $groups[] = new GroupModel($row);
        }
        
        return $groups;
    }
    
    public static function getGroupsByUser($user , $showInvisible = false){
    
        $DB = Database::getHandle();
        $q = 'SELECT g.id AS id, g.name AS name, g.title AS title, g.grouppic_file AS grouppic_file                    
                FROM '.DB_SCHEMA.'.user_group_membership AS m, '
                      .DB_SCHEMA.'.groups AS g   
               WHERE m.user_id = '.$DB->Quote($user->id)
             . ' AND g.id = m.group_id ';
        
        if(!$showInvisible){
        	$q .= 'AND g.is_visible = true';
        }
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $groups = array();
        
        foreach($res as $row){	
            $groups[$row['id']] = new GroupModel($row);
        }    
            
         return $groups;   
    }
    
    public static function getGroupsByIds($ids , $showInvisible = false){
        if (count($ids) == 0) {
        	return array();
        }
        
        $DB = Database::getHandle();
        $q = 'SELECT g.id AS id, g.name AS name, g.title AS title, g.grouppic_file AS grouppic_file                    
                FROM ' . DB_SCHEMA . '.groups AS g   
               WHERE g.id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        
        if(!$showInvisible){
            $q .= 'AND g.is_visible = true';
        }
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $groups = array();
        
        foreach($res as $row){  
            $groups[$row['id']] = new GroupModel($row);
        }    
            
        return $groups;   
    }
    
   
    
    public static function getGroupById($id){
    
        $DB = Database::getHandle();
        $q = 'SELECT id, title, name, description, is_visible, infopage_raw, infopage_parsed, grouppic_file
                FROM ' . DB_SCHEMA . '.groups   
               WHERE id = '.$DB->Quote($id);
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach($res as $row) {
            return new GroupModel($row);
        }
    }
    
    public static function getGroupByName($name){
    
        $DB = Database::getHandle();
        $q = 'SELECT id, title, name, description, is_visible, infopage_raw, infopage_parsed, grouppic_file
                FROM ' . DB_SCHEMA . '.groups   
               WHERE name = '.$DB->Quote($name);
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach($res as $row) {
            return new GroupModel($row);
        }
    }
    
    public static function getGroupsByNames($groupnames, $order=''){
    	// check, if we have groupnames to work on
        if (count($groupnames) == 0) {
            return array();
        }
        
        $DB = Database::getHandle();
        
        // create string of escaped usernames to fetch
        $groupString = '';
        foreach ($groupnames as $name) {
        	
      			//if "{...}" in string cut, this
      			$matchs = strpos($name,'['); //cut everything left from "["
      			$matche = strrpos($name, ']'); //cut everything right from "]" 
      			
      			if(((int)$matchs>-1)&&((int)$matche>-1))
      			{
      			$name = trim(substr($name, $matchs + 1, $matche -1)); //cut "[","]" and spaces on begin and end
                $groupString .= $DB->Quote(strtolower($name)) . ',';
      			}
        }
        // remove last comma
        $groupString = substr($groupString,0,-1);
        
        // TODO: restrict to active/actvivated/visible users on default
        $q = 'SELECT * FROM ' . DB_SCHEMA . '.groups
               WHERE LOWER(name) IN (' . $groupString . ')';
        if ($order == 'name') {
            $q .= ' ORDER BY name ASC';
        }
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $groups = array();
        foreach ($res as $row) {                    
            array_push($groups, new GroupModel($row));
        }
        
        return $groups;
    }
    
     public function getMembers($showInvisible = false){
        
        if($this->members){
            return $this->members;
        }
        
        $DB = Database::getHandle();
        $q = 'SELECT m.user_id AS user_id                   
                FROM '.DB_SCHEMA.'.user_group_membership AS m   
               WHERE m.group_id = '.$DB->Quote($this->id);        
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $user_ids = array();
        
        foreach($res as $row){  
            $user_ids[] = $row['user_id'];
        }    
            
         return $this->members = UserProtectedModel::getUsersByIds($user_ids, 'username');   
    }
    
    public function getAdmins(){
        $DB = Database::getHandle();
        $q = 'SELECT r.user_id AS user_id                   
                FROM '.DB_SCHEMA.'.rights_user_group AS r   
               WHERE r.group_id = '.$DB->Quote($this->id) .
                'AND r.right_id = ( SELECT id FROM ' . DB_SCHEMA.' . rights WHERE name = \'GROUP_OWN_ADMIN\' )';        
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $user_ids = array();
        
        foreach($res as $row){  
            $user_ids[] = $row['user_id'];
        }    
            
        return UserProtectedModel::getUsersByIds($user_ids);
    }
    
    public function hasMember($user){
        /* not loggin and external cannt members of a group */
        if(!$user->isLoggedIn() || $user->isExternal()){
        	return false;
        }
        
        $members = $this->getMembers();
        
        return array_key_exists($user->id, $members);   
    }
       
   protected function loadForum(){
   	
        $DB = Database::getHandle();
        
        // get subscriptors
        $q = 'SELECT forum_id
                FROM ' . DB_SCHEMA . '.groups_forums
               WHERE group_id = ' . $DB->Quote($this->id) . '
                 AND is_default = TRUE';
    
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach($res as $row){
            $this->forum = ForumModel::getForumById($row['forum_id']);
            return;
        }
        $this->forum = false;
    
   }
   
   public function getForum(){
   	    if(!$this->forum){
   	    	$this->loadForum();
   	    }
        return $this->forum;
   }
    
    /**
     * test if visitor is in this group
     */
    protected function isVisitorInGroup(){
    	$cUser = Session::getInstance()->getVisitor();
        return $this->isVisitorMember = $this->hasMember($cUser);
    }
    
    /**
     * adds user to a group
     *
     * @param array $users array of user to add
     * @throws DBException on DB error
     */
    public function addUsers($users) {
        $DB = Database::getHandle();

        if (count($users) == 0) {
            return;
        }

        /* collect ids */
        $userIds = Database::makeCommaSeparatedString($users, 'id');

        /* add the new users */
        $stmt = 'INSERT INTO ' . DB_SCHEMA . '.user_group_membership (user_id, group_id)  
                    SELECT ' . DB_SCHEMA . '.users.id, ' . $DB->quote($this->id) . 
                    ' FROM ' . DB_SCHEMA . '.users 
                     WHERE ' . DB_SCHEMA . '.users.id IN ('. $userIds . ') 
                       AND ' . DB_SCHEMA . '.users.id NOT IN  
                             (SELECT user_id 
                                FROM ' . DB_SCHEMA . '.user_group_membership 
                               WHERE group_id = ' . $DB->quote($this->id) . ' 
                                 AND user_id IN ('. $userIds . '))';

        //var_dump($stmt);

        if (!$DB->execute($stmt)) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    /**
     * dels user to a group
     *
     * @param array $users array of user to add
     * @throws DBException on DB error
     */
    public function delUsers($users) {
        $DB = Database::getHandle();

        $stmt = 'DELETE FROM '. DB_SCHEMA . '.user_group_membership 
                       WHERE group_id=' . $DB->quote($this->id) . ' 
                         AND user_id IN (' . Database::makeCommaSeparatedString($users, 'id') . ')'; 

        if (!$DB->execute($stmt)) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    /**
     * returns the Infopage of the Group
     */
    public function getInfopage(){
    	if($this->infopage == null){
    		$this->infopage == new GroupInfopageModel($this);
    	}
        return $this->infopage;
    }
    
    /**
     * sets the group picture file
     * 
     * due to interface AddressableEntitiy
     * 
     * @param string
     */
    public function setPictureFile($filename) {
        $this->logoUrl = $filename;
    }
    public function getPictureFile($variant = '') {
        if ($variant == '') {
            return $this->logoUrl;
        } else if ($variant == 'tiny') {
            return AttachmentHandler::getTinyVariantPath($this->logoUrl);
        }
        return null;
    }
    
    /** save the current model db */    
    public function save() {
        $DB = Database::getHandle();
        $DB->StartTrans();
        
        $keyValue = array(
           'name' => $DB->quote($this->name), 
           'title' => $DB->quote($this->title),
           'description' => $DB->quote($this->description),
           'is_visible' => $DB->quote(Database::makeBooleanString($this->isVisible)),
           'grouppic_file' => $DB->quote($this->logoUrl)
           );
            
        $stmt = null;
        
        if($this->id != null) {
            $stmt = $this->buildSqlStatement('groups', $keyValue, false, 'id=' . $DB->quote($this->id));
        } else {
            $stmt = $this->buildSqlStatement('groups', $keyValue);                        
        }
        
        if(!$DB->execute($stmt)){
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        if($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'groups','id');
            $groupDir = AttachmentHandler::getAdjointPath2($this);
            if (!file_exists($groupDir)) {
                if (!mkdir($groupDir)) {
                    throw new DBException( Logging::getErrorMessage(FILE_MKDIR_FAILED, $groupDir) );
                }
            }
            $generalGroupDir = AttachmentHandler::getAdjointPath2($this);
            if (!file_exists($generalGroupDir)) {
                if (!mkdir($generalGroupDir)) {
                    throw new DBException( Logging::getErrorMessage(FILE_MKDIR_FAILED, $generalGroupDir) );
                }
            }
        }
        
        $DB->CompleteTrans();
    }
    
    public function delete() {
        $DB = Database::getHandle();
        
        $DB->execute('DELETE FROM groups WHERE id=' . $DB->quote($this->id));
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getTitle() {
        if ($this->title != 'group') {
            return $this->title;
        }
        return DEFAULT_GROUP;
    }
    
    public function getHash() {
    	return 'G' . $this->id;
    }
 }
?>
