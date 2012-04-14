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
 * Created on 16.06.2006 by schnueptus
 * Sunburner Unihelp.de
 */
 
require_once CORE_DIR . '/exceptions/core_exception.php';  
require_once CORE_DIR . '/models/base/interactive_user_element_model.php';
require_once CORE_DIR . '/parser/parser_factory.php';
/**
 * @class CategoryModel
 * @brief represants a category of the forum
 *
 * @author schnueptus, kyle
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * @package Models/Forum
 *
 * from CategoryModel
 * <ul>
 * <li><var>id</var><b>int </b></li>
 * <li><var>desc</var><b> string </b> parsed description</li>
 * <li><var>descriptionRaw</var><b> string </b> unparsed description</li>  
 * <li><var>numberOfThreads</var><b> int</b> counter the threads</li>
 * <li><var>numberOfForums</var><b> int</b> counter the forums</li>
 * <li><var>defaultTemplate</var><b> string</b> Template name of Forum </li>
 * <li><var>moderators</var><b> array of UserModel</b> all moderator for this category</li>
 * <li><var>defaultForumModerators</var><b> array of UserModel</b> all defaultForumModerators</li>
 * </u1>
 */
class CategoryModel extends InteractiveUserElementModel {

    protected $name;
    protected $descRaw;
    protected $description;
    
    protected $numberOfThreads;
    protected $numberOfForums;
    
    
    
    
    
    protected $defaultTemplate;
    
    protected $type;
    
    protected $moderators_cache;
    protected $moderatorsLoaded = false;
    protected $defaultForumModerators;
    protected $defaultForumModeratorsLoaded = false;
    
    
    /* is lazy loaded on demand */
    /* public $moderators; */ 

    public function __construct($name = null, $id = null) {
    	$this->id = $id;
    	$this->name = $name;
    }
    
    /**
     * builds the data from array into the objekt
		 *@return CategoryModel
     */
    private function buildFromRow($row) {
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->description = $row['description_parsed'];
        $this->descRaw = $row['description_raw'];
        $this->numberOfThreads = $row['number_of_threads'];
        $this->numberOfForums = $row['number_of_forums'];
        $this->defaultTemplate = $row['default_template'];
        $this->type = $row['category_type'];
        
        return $this;
    }
    
    /**
     * save a category
     */
    public function save() {
        $keyValue = array();
        
        $DB = Database::getHandle();
        
        $keyValue['name'] = $DB->quote($this->name);
        $keyValue['description_parsed'] = $DB->quote($this->getDescriptionParsed());
        $keyValue['description_raw'] = $DB->quote($this->descRaw);
        $keyValue['default_template'] = $DB->quote($this->defaultTemplate);

        $q = null;

        /* is update? add extra fields */
        if($this->id != null) {
            $q = $this->buildSqlStatement('forum_categories', $keyValue, false, 'id=' . $DB->quote($this->id));
        } else {        
            $q = $this->buildSqlStatement('forum_categories', $keyValue);
        }
        
        $DB->StartTrans();

        /* save the category */
        if (!$DB->execute($q)) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        /* set the current id so we can add moderators */
        if($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'forum_categories', 'id');
        }
        
        /* save moderators */
        if($this->id != null) {
            
            /* delete the users that are no more moderators */
            $stmt = 'DELETE FROM '. DB_SCHEMA . '.forum_category_moderator WHERE category_id=' . 
                $DB->quote($this->id) . ' AND user_id NOT IN (';
            
            $moderators = $this->getModerators();
            if ($moderators == null){
                $userIds = '0';
            } else {
                $userIds = Database::makeCommaSeparatedString($moderators, 'id');
            }
            
            $stmt .= $userIds . ') ';
            
            if (!$DB->execute($stmt)) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
            
            /* add the new users */
            $stmt = 'INSERT INTO ' . DB_SCHEMA . '.forum_category_moderator (user_id, category_id)  
                        SELECT ' . DB_SCHEMA . '.users.id, ' . $DB->quote($this->id) .' 
                          FROM ' . DB_SCHEMA . '.users 
                         WHERE ' . DB_SCHEMA . '.users.id IN ('. $userIds . ') 
                           AND ' . DB_SCHEMA . '.users.id NOT IN 
                                (SELECT user_id 
                                   FROM ' . DB_SCHEMA . '.forum_category_moderator 
                                  WHERE category_id = ' . $DB->quote($this->id) . ' 
                                    AND user_id IN ('. $userIds . '))';
                
            if (!$DB->execute($stmt)) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
        }

        /* save default forum moderators */        
        if($this->id != null) {
            
            /* delete the users that are no more moderators */
            $stmt = 'DELETE FROM '. DB_SCHEMA . '.forum_default_moderator WHERE category_id=' . 
                $DB->quote($this->id) . ' AND user_id NOT IN (';
            
            $moderators = $this->getDefaultForumModerators();
            if ($moderators == null){
                $userIds = '0';
            } else {
                $userIds = Database::makeCommaSeparatedString($moderators, 'id');
            }
            
            $stmt .= $userIds . ') ';
            
            if (!$DB->execute($stmt)) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
            
            /* add the new users */
            $stmt = 'INSERT INTO ' . DB_SCHEMA . '.forum_default_moderator (user_id, category_id)  
                        SELECT ' . DB_SCHEMA . '.users.id, ' . $DB->quote($this->id) . 
                        ' FROM ' . DB_SCHEMA . '.users 
                         WHERE ' . DB_SCHEMA . '.users.id IN ('. $userIds . ') 
                           AND ' . DB_SCHEMA . '.users.id NOT IN  
                                (SELECT user_id 
                                   FROM ' . DB_SCHEMA . '.forum_default_moderator 
                                  WHERE category_id = ' . $DB->quote($this->id) . ' 
                                    AND user_id IN ('. $userIds . '))';
                                    
            if (!$DB->execute($stmt)) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
        }
        
        if(!$DB->CompleteTrans()) {
            throw new DBException(DB_TRANSACTION_FAILED);
        }        
    }
    
     /**
    * Get a CategoryModels from a id
    *
    * @param int $id the id of the category
    * @throws DBException on DB error
    *
    * @return CategoryModels
    */
    public static function getCategoryById($id) {
        
        if($id == null)
            throw new ArgumentNullException('id');

        $DB = Database::getHandle();
        
        $q = 'SELECT * FROM '.DB_SCHEMA.'.forum_categories WHERE id=' . $DB->quote($id);
               
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        /** iterate over result set */
        foreach ($res as $k => $row) {
            $category = new CategoryModel();
            return $category->buildFromRow($row);
        }
    }
    
    protected static function loadAllModerators($categories) {
        $DB = Database::getHandle();
        
        // determine moderator ids for all categories
        $categoryModIds = array();
        $q = 'SELECT category_id, user_id 
                FROM ' . DB_SCHEMA . '.forum_category_moderator
               WHERE category_id IN (' . Database::makeCommaSeparatedString($categories, 'id') . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // save user ids and category-user relationship
        $userIds = array();
        foreach ($res as $row) {
            $userIds[] = $row['user_id'];
            if (!array_key_exists($row['category_id'], $categoryModIds)) {
                $categoryModIds[$row['category_id']][] = $row['user_id'];
            }
        }
        
        // load all neccessary user models
        $users = UserProtectedModel::getUsersByIds($userIds);
        
        // add moderator information to all models
        foreach ($categories as $cat) {
            $cat->moderatorsLoaded = true;
            $cat->moderators_cache = array();
            
            if (array_key_exists($cat->id, $categoryModIds)) {                
                foreach ($categoryModIds[$cat->id] as $uid) {
                    $cat->moderators_cache[] = &$users[$uid];
                }
            }
        }
    }
    
    /**
     * get All Categories are in the Database
		 *
		 * @throws DBException on DB error
		 *
		 *@return array of CategorieModel
     */
    public static function getAllCategories() {
    	$DB = Database::getHandle();
        
        $q = 'SELECT * 
                FROM '.DB_SCHEMA.'.forum_categories 
               WHERE category_type = \'default\' or category_type = \'market\'
              ORDER BY position, name ASC';
               
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $categories = array();
        
        /** iterate over result set */
        foreach ($res as $k => $row) {
            
            $category = new CategoryModel();
            $category->buildFromRow($row);
            
            $categories[] = $category;               
        }
        
        // preload all moderators of all categories
        self::loadAllModerators($categories);
        
        return $categories;
        
    }
    
    
    /**
     * get the right category to the course
     */
    public static function getCourseCategory() {

        $DB = Database::getHandle();
        
        $q = 'SELECT * FROM '.DB_SCHEMA.'.forum_categories WHERE category_type = \'course\'';
               
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        /** iterate over result set */
        foreach ($res as $k => $row) {
            $category = new CategoryModel();
            return $category->buildFromRow($row);
        }
    }
    
     /**
     * get the right category to the course
     */
    public static function getOldCourseCategory() {

        $DB = Database::getHandle();
        
        $q = 'SELECT * FROM '.DB_SCHEMA.'.forum_categories WHERE category_type = \'course_old\'';
               
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        /** iterate over result set */
        foreach ($res as $k => $row) {
            $category = new CategoryModel();
            return $category->buildFromRow($row);
        }
    }
    
    /**
     * get the right category to the course
     */
    public static function getGroupCategory() {
        
        $DB = Database::getHandle();
        
        $q = 'SELECT * FROM '.DB_SCHEMA.'.forum_categories WHERE category_type = \'group\'';
               
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        /** iterate over result set */
        foreach ($res as $k => $row) {
            $category = new CategoryModel();
            return $category->buildFromRow($row);
        }
    }
    
 		/**
		 * the magic funktions ;)
		 */
    protected function __get($name) {
        throw new CoreException("don't use __get($name)");
        /* parse the desc on demand */
        if($name == 'desc') {
            if($this->xdesc == null || $this->xdesc == '') {
                $this->xdescription = ParserFactory::parseWithDefaultSettings($this->descRaw);
            } 
            
            return $this->xdescription;
        }
        
        /*if($name == 'moderators')
            return $this->safeReturn('moderators', 'getModerators');
            
        if($name == 'defaultForumModerators')
            return $this->safeReturn('defaultForumModerators', 'getDefaultForumModerators');*/
        
    }
    
    public function getModerators() {
       if(!$this->moderatorsLoaded){
       	    $this->_getModerators();
            $this->moderatorsLoaded = true;
       }
       return $this->moderators_cache;
    }
    public function setModerators($mods) {
        $this->moderators_cache = $mods;
        $this->moderatorsLoaded = true;
    }
    public function getDefaultForumModerators() {
        if(!$this->defaultForumModeratorsLoaded){
        	$this->_getDefaultForumModerators();
            $this->defaultForumModeratorsLoaded = true;
        }
        return $this->defaultForumModerators;
    }
    public function setDefaultForumModerators($mods) {
        $this->defaultForumModerators = $mods;
        $this->defaultForumModeratorsLoaded = true;
    }
    
    public function getName() {
      return $this->name;
    }
    public function setName($name) {
      $this->name = $name;
    }
    public function getDescriptionParsed() {
        if ($this->description == null || $this->description == '') {
            $this->description = $this->descRaw;
            $this->description = ParserFactory::parseWithDefaultSettings($this->description);
        }
        return $this->description;
    }
    public function getDescriptionRaw() {
      return $this->descRaw;
    }
    public function setDescriptionRaw($desc) {
      $this->descRaw = $desc;
      $this->description = '';
    }
    
    public function getNumberOfThreads() {
        return $this->numberOfThreads;
    }
    public function getNumberOfForums() {
        return $this->numberOfForums;
    }
    
    public function getDefaultTemplate() {
        return $this->defaultTemplate;
    }
    public function setDefaultTemplate($tpl) {
        $this->defaultTemplate = $tpl;
    }
    
    public function getType() {
        return $this->type;
    }
    
    
    /**
     * IMPORTENT: if you want test if a user is a moderator of 
     *  a forum use <code>isUserModerator($userModel)</code>
     * 
     * @return array a array of UserModel objects that contains all moderators of the forum
     */
    protected function _getModerators() {
        /* without a id is no way to load our moderators */
        if($this->id == null)
            throw new ArgumentNullException('id');            
        $DB = Database::getHandle();            
            
        $q = 'SELECT user_id 
                FROM ' . DB_SCHEMA . '.forum_category_moderator 
               WHERE category_id =' . $DB->quote($this->id);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        /* get user ids */
        $ids = array();
        foreach ($res as $row) {
            $ids[] = $row['user_id'];
        }
        
        $this->moderators_cache = UserProtectedModel::getUsersByIds($ids);
    }

    /**
     * @return array a array of UserModel objects that contains all default moderators of the forum
     */
    protected function _getDefaultForumModerators() {
        
        /* without a id is no way to load our moderators */
        if($this->id == null)
            throw new ArgumentNullException('id');
            
        $DB = Database::getHandle();            
            
        $q = 'SELECT user_id 
                FROM ' . DB_SCHEMA . '.forum_default_moderator 
               WHERE category_id =' . $DB->quote($this->id);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        /* get user ids */
        $ids = array();        
        foreach($res as $row) {
            $ids[] = $row['user_id'];
        }
        
        $this->defaultForumModerators = UserProtectedModel::getUsersByIds($ids);
    }

    /**
     * Test if a given user is a moderator of the category.
     * 
		 * @param UserMode $userModel the Model of the User wich we want to test
		 *
     * @return true when the user is a moderator
     */
    public function isModerator($userModel) {
        
        if($userModel->hasRight('FORUM_CATEGORY_ADMIN')){
            return true;
        }
        
        /* test the moderator of the forum */
        foreach ($this->getModerators() as $user) {
            if ($userModel->equals($user)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * deletes the current model form the DB
     */
    public function delete() {
        /* without a id is no way to load our moderators */
        if($this->id == null)
            throw new ArgumentNullException('id');
            
        $DB = Database::getHandle();            
            
        $q = 'DELETE FROM ' . DB_SCHEMA . '.forum_categories 
              WHERE id =' . $DB->quote($this->id);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }
   
   /**
     * rePosition the Category
     */
    public function rePosition($value){
        
        if($this->id == null)
            throw new ArgumentNullException('id');
        
        $DB = Database::getHandle();            
        
        if ($value == 'down'){    
            $delta = 1;
        }
        elseif ($value == 'up'){    
            $delta = -1;      
        }
        else{
        	throw new ArgumentException('value', $value);
        }
        
        $q = 'SELECT public.reposition_category(\'' . DB_SCHEMA . '\', ' . $DB->Quote($this->id) . '::bigint, ' . $delta . '::smallint);';        
            
        
        $DB->StartTrans();
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        if(!$DB->CompleteTrans())
            throw new DBException(DB_TRANSACTION_FAILED);  
    }
    
}
?>
