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


require_once MODEL_DIR . '/base/interactive_user_element_model.php';
require_once MODEL_DIR . '/base/tag_model.php';

require_once MODEL_DIR . '/forum/thread_entry_model.php';
require_once MODEL_DIR . '/forum/category_model.php';

require_once CORE_DIR . '/exceptions/core_exception.php';
require_once CORE_DIR . '/parser/parser_factory.php';
/**
 * @class ForumModel
 * @brief represants a forum of the forum
 * 
 * @author schnueptus, kyle, linap
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * @package Models/Forum
 * 
 * from ForumModel
 * <ul>
 * <li><var>id</var><b>int </b></li>
 * <li><var>name</var><b> string</b> the name/caption/tiltle of the forum</li>
 * <li><var>desc</var><b> string </b> parsed description</li>
 * <li><var>descriptionRaw</var><b> string </b> unparsed description</li> 
 * <li><var>lastEntry</var><b> ThreadEntryModel</b> of the last entry</li>
 * <li><var>numberOfEntries</var><b> int</b> counter the entries</li>
 * <li><var>numberOfThreads</var><b> int</b> counter the Threads</li>
 * <li><var>categoryId</var><b> int</b> the id of the Category</li>
 * <li><var>categoryName</var><b> string</b> the Name of the Category</li>
 * <li><var>category</var><b> CategoryModel</b> the Model of the Category</li> 
 * <li><var>counter</var><b> array of int</b> the non-linear counter</li>
 * <li><var>page</var><b> int</b> the current page number</li>
 * <li><var>threadsPerPage</var><b> int</b> who many threads per page are display</li>
 * <li><var>totalPages</var><b> int</b> </li>
 * <li><var>moderators</var><b> array of UserModel</b> all Moderator of this forum</li>
 * <li><var>parent</var><b> ForumModel</b> the Model of the Parent Forum</li>
 * <li><var>hasParent</var><b> booloean</b> if the Forum has a Parent Fourm</li>
 * <li><var>tags</var><b> array of TagModel</b> all tags linked to this forum</li>
 * </u1>
 */
class ForumModel extends InteractiveUserElementModel {

    protected $categoryId;
    protected $categoryName;
    
    /* is lazy loaded on demand */
    /* public $moderators; */ 

    protected $categoryLoaded = false;
    protected $category_cache;  //is lazy loaded on demand
    
    protected $parentId;
    protected $parent_cache;
    
    /* public $desc */ //dynamicly added
    
    /* public $tags; */ //dynamiacly added
    protected $tagsLoaded = false; /*** indicates that the tags were loaded */
    protected $tags;
    
    protected $name;
    protected $descRaw;
    
    protected $visibleId;
    
    protected $enableFormatCode;
    protected $enableHtml;
    protected $enableSmileys;
    protected $enablePoints;
    protected $enablePostings;
    
    protected $mayContainNews;
    
    protected $isImportant;
    
    protected $numberOfEntries;
    protected $numberOfThreads;
    
    protected $lastEntry;
    protected $lastEntryId;
    protected $forumTemplate;
    
    protected $xdesc;
    
    protected $moderatorsLoaded = false;
    protected $moderators_cache;
    
    protected $position;
    
    protected $groupId = null;
    protected $isGroupDefault = null;
    
    private $page;
    private $threadsPerPage;
		/**
		 * basic constructor
		 *
		 * @param int $page the number of the page
		 * @param int $threadsPerPage how many Threads where displayed
		 */
    public function __construct($page = 1, $threadsPerPage = V_FORUM_THREADS_PER_PAGE) {
        parent :: __construct();
        $this->page = $page;
        $this->threadsPerPage = $threadsPerPage;
    }  

    /**
      * fill the data in the Model if the id is known
			*
			* @param int $id the id of Forum
			* @throws DBException
			*
			* @return ForumModel
      */
    public static function getForumById($id) {
        $DB = Database :: getHandle();

        $q = 'SELECT f.*, c.name AS category_name ' .
               'FROM ' . DB_SCHEMA . '.forum_fora AS f, ' .
                    '' . DB_SCHEMA . '.forum_categories as c ' .
              'WHERE f.id = ' . $DB->Quote($id) . ' AND c.id = f.category_id';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $forum = new ForumModel();
        
        foreach ($res as $k => $row)
            return $forum->buildFromRow($row);
    }

    /**
     * builds the data from array into the objekt
		 *
		 * @param array $dbRow
		 *
		 * @return ForumModel
     */
    protected function buildFromRow($dbRow) {
        $this->id = $dbRow['id'];
        $this->categoryId = $dbRow['category_id'];
        
        if(isset($dbRow['category_name']))
            $this->categoryName = $dbRow['category_name'];
        
        if(array_key_exists('forum_parent_id',$dbRow))
            $this->parentId = $dbRow['forum_parent_id'];
        
        $this->name = $dbRow['name'];
        $this->descRaw = $dbRow['description_raw'];
        $this->xdesc = $dbRow['description_parsed'];

        //$this->disableLogoffVisit = Database :: convertPostgresBoolean($dbRow['disable_logoff_visit']);
        $this->visibleId = $dbRow['visible'];
        
        $this->enableFormatCode = Database :: convertPostgresBoolean($dbRow['enable_formatcode']);
        $this->enableHtml = Database :: convertPostgresBoolean($dbRow['enable_html']);
        $this->enableSmileys = Database :: convertPostgresBoolean($dbRow['enable_smileys']);
        $this->enablePoints = Database :: convertPostgresBoolean($dbRow['enable_points']);
        $this->enablePostings = Database :: convertPostgresBoolean($dbRow['enable_postings']);
        $this->mayContainNews = Database :: convertPostgresBoolean($dbRow['may_contain_news']);
        $this->isImportant = Database :: convertPostgresBoolean($dbRow['important']);
        
        $this->numberOfEntries = $dbRow['number_of_entries'];
        $this->numberOfThreads = $dbRow['number_of_threads'];
        $this->lastEntryId = $dbRow['last_entry'];
        $this->forumTemplate = $dbRow['forum_template'];
        $this->position = $dbRow['position'];
        
        return $this;
    }
    /**
     * get all Subforum of one Forum by Id
     * 
     * @param int $parent_id the id of the parent forum
     * 
     * @throws DBException
     * 
     * @return array of ForumModels
     */
    public static function getForumByParentId($parent_id, $isLoggin = false, $isGroup = false) {
        $DB = Database :: getHandle();

        $q = 'SELECT * ' .
              'FROM ' . DB_SCHEMA .'.forum_fora
              WHERE forum_parent_id = ' . $DB->Quote($parent_id);
        if(!$isLoggin){   
            $q .= ' AND visible = (SELECT id FROM public.details_visible WHERE name = \'all\')';
        }
        if($isLoggin && !$isGroup){
        	$q .= ' AND visible IN (SELECT id FROM public.details_visible WHERE name = \'all\' OR name = \'logged in\')';
        }    
        $q .= ' ORDER BY position ASC';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $forums = array();
        
        foreach ($res as $k => $row){
            $forum = new ForumModel();
            $forum->buildFromRow($row);
            $forums[] = $forum;
        }
        
        return $forums;
    }
    
      public static function getForumByMayContainNews() {
        $DB = Database :: getHandle();

        $q = 'SELECT * ' .
              'FROM ' . DB_SCHEMA .'.forum_fora
              WHERE may_contain_news = ' . $DB->Quote(true);  
        $q .= ' ORDER BY name ASC';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $forums = array();
        
        foreach ($res as $k => $row){
            $forum = new ForumModel();
            $forum->buildFromRow($row);
            $forums[] = $forum;
        }
        
        return $forums;
    }

     public static function getForumByName($name, $isLoggin = false) {
        $DB = Database :: getHandle();

        $q = 'SELECT * ' .
              'FROM ' . DB_SCHEMA .'.forum_fora
              WHERE name = ' . $DB->Quote($name);
        if(!$isLoggin){   
               //$q .= ' AND disable_logoff_visit = FALSE';
               $q .= ' AND visible = (SELECT id FROM public.details_visible WHERE name = \'all\')';
            }
        $q .= ' ORDER BY position ASC';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $forums = array();
        
        foreach ($res as $k => $row){
            $forum = new ForumModel();
            $forum->buildFromRow($row);
            $forums[] = $forum;
        }
        
        return $forums;
    }


    /**
     * add to the categorie the forummodels to belong to it
		 *
		 * @param array $cats CategorieModels who will add the forums
		 * @throws DBException
		 *
		 * @return CategorieModels with ForumModels add
		 *
		 * @todo: User filtering (see only Forum who intrested by the User)
     */
    public static function getAllForumsByCategories($cats, $isLoggin = false) {
        $DB = Database :: getHandle();
        
        //TODO: dont use foreach
        
        foreach ($cats as $cat) {
            $q = 'SELECT * ' .
                   'FROM ' . DB_SCHEMA . '.forum_fora
                   WHERE category_id = ' . $DB->Quote($cat->id);
            
            if(!$isLoggin){   
               //$q .= ' AND disable_logoff_visit = \'true\' ';
               $q .= ' AND visible = (SELECT id FROM public.details_visible WHERE name = \'all\')';
            }
            $q .= ' ORDER BY forum_parent_id, position ASC';
            
            #var_dump($q);
            
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }

            $forum = array ();

            /** iterate over result set */
            foreach ($res as $k => $row) {

                $sForum = new ForumModel();
                $sForum->buildFromRow($row);

                $forum[] = $sForum;
            }

            $cat->forums = $forum;
        }

        return $cats;
    }
    
   /**
     * add to the categorie the forummodels to belong to it and be only parent forum
     *
     * @param array $cats CategorieModels who will add the forums
     * @throws DBException
     *
     * @return CategorieModels with ForumModels add
     *
     * @todo: User filtering (see only Forum who intrested by the User)
     * 
     * Notice: i dont ask of group Forum there are get by 'getGroupForumsByUser'
     * so i dont need to test that the forum are only for members visible
     */
    public static function getAllParentForumsByCategories($cats, $isLoggin = false) {
        $DB = Database :: getHandle();
        
        // TODO: dont use foreach
        //  remark: what does that mean???? (linap, 23.05.)
        
        foreach ($cats as &$cat) {
            $q = 'SELECT f.*, g.group_id ' .
                  'FROM ' . DB_SCHEMA . '.forum_fora f
              LEFT JOIN ' . DB_SCHEMA . '.groups_forums g
                     ON f.id = g.forum_id
                  WHERE category_id = ' . $DB->Quote($cat->id) . '
                    AND forum_parent_id IS NULL ';
            
            if(!$isLoggin){   
               //$q .= ' AND disable_logoff_visit = false';
               $q .= ' AND visible = (SELECT id FROM public.details_visible WHERE name = \'all\')';
            }
            $q .= ' ORDER BY position ASC';
               
            //var_dump($q);
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }

            $forum = array ();
            $forumsIds = array();

            /** iterate over result set */
            foreach ($res as $k => $row) {

                $sForum = new ForumModel();
                $sForum->buildFromRow($row);
                
                // load categoty cache
                $sForum->categoryLoaded = true;
                $sForum->category_cache = &$cat;
                
                // load group id cache
                if (!$row['group_id']) { 
                    $sForum->groupId = false;
                } else {
                    $sForum->groupId = $row['group_id'];
                }
                
                // save all forum ids in an array
                $forumIds[] = $sForum->id;

                $forum[] = $sForum;
            }

            $cat->forums = $forum;
        }
        
        // determine moderator ids for all fora
        $forumModIds = array();
        $q = 'SELECT forum_id, user_id 
                FROM ' . DB_SCHEMA . '.forum_moderator
               WHERE forum_id IN (' . Database::makeCommaSeparatedString($forumIds). ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // save user ids and forum-user relationship
        $userIds = array();
        foreach ($res as $row) {
            $userIds[] = $row['user_id'];
            if (!array_key_exists($row['forum_id'], $forumModIds)) {
                $forumModIds[$row['forum_id']][] = $row['user_id'];
            }
        }
        
        // load all neccessary user models
        $users = UserProtectedModel::getUsersByIds($userIds);
        
        // add moderator information to all models
        foreach ($cats as &$cat) {
            foreach ($cat->forums as $f) {
                $f->moderatorsLoaded = true;
                $f->moderators_cache = array();
                
                if (array_key_exists($f->id, $forumModIds)) {                
                    foreach ($forumModIds[$f->id] as $uid) {
                        $f->moderators_cache[] = &$users[$uid];
                    }
                }
            }
        }
        
        return $cats;
    }
   /**
    * get Course Forums by one user
    * 
    * @param UserModel $user
    * @throws DBException
    * 
    * @return array ForumModel 
    */
   public static function getCourseForumsByUser($user, $courseCategory = null){
   		$DB = Database :: getHandle();

		$subs = 'SELECT course_id
				 FROM '. DB_SCHEMA .'.courses_per_student
				 WHERE user_id =' . $DB->Quote($user->id);

		$subsel = 'SELECT forum_id
                   FROM ' . DB_SCHEMA . '.courses_data
                   WHERE course_id IN (' .$subs .')' ;

        $q = 'SELECT * FROM ' . DB_SCHEMA .'.forum_fora
              WHERE id IN (' . $subsel . ')
              ORDER BY position ASC';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $forums = array();
         
        foreach ($res as $k => $row){
            $forum = new ForumModel();
            $forum->buildFromRow($row);
            
            if ($courseCategory != null) {
                $forum->categoryLoaded = true;
                $forum->category_cache = $courseCategory;
            }
            
            $forums[] = $forum;
        }
        
        return $forums;
   }
   
   /**
    * get Group Forums by one user
    * 
    * @param UserModel $user
    * @throws DBException
    * 
    * @return array ForumModel 
    */
   public static function getGroupForumsByUser($user){
        $DB = Database :: getHandle();

        $subs = 'SELECT group_id
                 FROM '. DB_SCHEMA .'.user_group_membership
                 WHERE user_id =' . $DB->Quote($user->id);

        $subsel = 'SELECT forum_id
                   FROM ' . DB_SCHEMA . '.groups_forums
                   WHERE group_id IN (' .$subs .') AND is_default = TRUE ' ;

        $q = 'SELECT * FROM ' . DB_SCHEMA .'.forum_fora
              WHERE id IN (' . $subsel . ')
              ORDER BY position ASC';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $forums = array();
        
        foreach ($res as $k => $row){
            $forum = new ForumModel();
            $forum->buildFromRow($row);
            $forums[] = $forum;
        }
        
        return $forums;
   }
      
   /**
    * get Abo Forums by one user
    * 
    * @param UserModel $user
    * @throws DBException
    * 
    * @return array ForumModel 
    */
   public static function getAboForumsByUser($user){
        $DB = Database :: getHandle();

        $subsel = 'SELECT forum_id
                 FROM '. DB_SCHEMA .'.forum_abo
                 WHERE user_id =' . $DB->Quote($user->id);

        $q = 'SELECT * FROM ' . DB_SCHEMA .'.forum_fora
              WHERE id IN (' . $subsel . ')
              ORDER BY name ASC';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $forums = array();
        
        foreach ($res as $k => $row){
            $forum = new ForumModel();
            $forum->buildFromRow($row);
            $forums[] = $forum;
        }
        
        return $forums;
   }
   
    public function getDescriptionParsed() {
        if ($this->xdesc == null or $this->xdesc == '') {
           $this->xdesc = ParserFactory::parseWithDefaultSettings($this->descRaw);
        }
        return $this->xdesc;
    }
    
    public function getRealNumberOfThreads(){
    	$DB = Database :: getHandle();

        $q = 'SELECT count(id) AS nr
                 FROM '. DB_SCHEMA .'.forum_threads
                 WHERE forum_id =' . $DB->Quote($this->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }   
        return $res->fields['nr'];      
    }
    
    public function getCounter() {
        $numberOfThreads = $this->getRealNumberOfThreads();
        return self::nonLinearCounter( ceil($numberOfThreads / $this->threadsPerPage) , $this->page);
    }
    
    public function getPage() {
        return $this->page;
    }
    public function setPage($page) {
        $this->page = $page;
    }
    public function getThreadsPerPage() {
        return $this->threadsPerPage;
    }
    public function getTotalPages() {
        return ceil($this->numberOfThreads / $this->threadsPerPage);
    }
    public function getCategory() {
        if (!$this->categoryLoaded) {
            $this->category_cache = CategoryModel::getCategoryById($this->categoryId);
            $this->categoryLoaded = true;
        }
        return $this->category_cache;
    }
    
    public function getParent() {
        if ($this->parent_cache != null) {
            return  $this->parent_cache;
        }
        if ($this->parentId != null) {
            return $this->parent_cache = ForumModel::getForumById($this->parentId);
        }
        return null;
    }
    
    public function hasParent() {
        return ($this->parentId != null);
    }
    
    public function setParentId($newId){
    	$this->parentId = $newId;
    }
    
    public function getTags() {
        return $this->safeReturn('tags', 'getTagsCallback');
    }
    
    public function getName() {
      return $this->name;
    }
    public function setName($name) {
      $this->name = $name;
    }
    public function getDescriptionRaw() {
      return $this->descRaw;
    }
    public function setDescriptionRaw($desc) {
      $this->descRaw = $desc;
    }
    
    /*public function hasDisabledLogoffVisit() {
        return $this->disableLogoffVisit;
    }
    public function setDisabledLogoffVisit($flag) {
        $this->disableLogoffVisit = $flag;
    }*/
    
    public function getVisibleId(){
    	return $this->visibleId;
    }
    public function setVisibleId($newId){
    	$this->visibleId = $newId;
    }
    public function getVisibleObj(){
    	return DetailsVisibleModel::getDetailsVisibleById($this->visibleId);
    }
    
    public function hasEnabledSmileys() {
        return $this->enableSmileys;
    }
    public function setEnabledSmileys($flag) {
        $this->enableSmileys = $flag;
    }
    public function hasEnabledFormatcode() {
        return $this->enableFormatCode;
    }
    public function setEnabledFormatcode($flag) {
        $this->enableFormatCode = $flag;
    }
    public function hasEnabledHTML() {
        return $this->enableHtml;
    }
    public function setEnabledHTML($flag) {
        $this->enableHtml = $flag;
    }
    public function hasEnabledPoints() {
        return $this->enablePoints;
    }
    public function setEnabledPoints($flag) {
        $this->enablePoints = $flag;
    }
    public function hasEnabledPostings() {
        return $this->enablePostings;
    }
    public function setEnabledPostings($flag) {
        $this->enablePostings = $flag;
    }
    public function hasMayContainNews() {
        return $this->mayContainNews;
    }
    public function setMayContainNews($flag) {
        $this->mayContainNews = $flag;
    }
    
    public function isImportant() {
        return $this->isImportant;
    }
    public function setImportant($important) {
        $this->isImportant = $important;
    }
    
    public function getNumberOfEntries() {
        return $this->numberOfEntries;
    }
    public function getNumberOfThreads() {
        return $this->numberOfThreads;
    }
    
    public function getLastEntry() {
        if($this->lastEntry == null) {
            if($this->lastEntryId == null){
                return null;	
            }
            $this->lastEntry = ThreadEntryModel::getThreadEntryById($this->lastEntryId);
        }
        return $this->lastEntry;
    }
    public function getForumTemplate() {
        return $this->forumTemplate;
    }
    public function setForumTemplate($tpl) {
        $this->forumTemplate = $tpl;
    }
    
    public function getCategoryName() {
        return $this->categoryName;
    }
    public function getCategoryId() {
        return $this->categoryId;
    }
    public function setCategoryId($id) {
        $this->categoryId = $id;
    }

    
    /**
     * dynamic loading of vars
     */
    protected function __get($name) {
        throw new CoreException("don't use _get magic ($name)");
        /* test if desc is parsed if net parsed and save it */
       
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
    

    protected function __set($name, $value){
        
        throw new CoreException("don't use _set magic ($name)");
        /* test if desc is parsed if net parsed and save it */
        
    }

    /**
     * save the current model
		 *
		 * @throws DBException
     */
    public function save() {

        $keyValue = array();
        
        $DB = Database::getHandle();
        
        /* in db the default value is 'default' don't write other values */
        # wollen wir testen ob das template exestiert oder bauen wir das mit im template ein ???
        if($this->forumTemplate == null || $this->forumTemplate == ''){
            $this->forumTemplate = 'default.tpl';
        }
        
        $keyValue['category_id'] = $DB->quote($this->categoryId);
        $keyValue['name'] = $DB->quote($this->name);
        $keyValue['description_raw'] = $DB->quote($this->descRaw);
        $desc = $this->descRaw;
        $keyValue['description_parsed'] = $DB->quote(ParserFactory::parseWithDefaultSettings($desc));
        //$keyValue['disable_logoff_visit'] = $DB->quote(Database::makeBooleanString($this->disableLogoffVisit));
        $keyValue['visible'] = $DB->quote($this->visibleId);
        $keyValue['enable_formatcode'] = $DB->quote(Database::makeBooleanString($this->enableFormatCode));
        $keyValue['enable_html'] = $DB->quote(Database::makeBooleanString($this->enableHtml));
        $keyValue['enable_smileys'] = $DB->quote(Database::makeBooleanString($this->enableSmileys));
        $keyValue['enable_points'] = $DB->quote(Database::makeBooleanString($this->enablePoints));
        $keyValue['enable_postings'] = $DB->quote(Database::makeBooleanString($this->enablePostings));
        $keyValue['may_contain_news'] = $DB->quote(Database::makeBooleanString($this->mayContainNews));
        $keyValue['important'] = $DB->quote(Database::makeBooleanString($this->isImportant));
        $keyValue['forum_template'] = $DB->quote($this->forumTemplate);
        
        if($this->parentId != null){
            $keyValue['forum_parent_id'] = $DB->quote($this->parentId);
        }
        
        $q = null;
        
        /* it's an update or a insert statement? */
        if($this->id == null)
            $q = $this->buildSqlStatement('forum_fora', $keyValue);
        else
            $q = $this->buildSqlStatement('forum_fora', $keyValue, false, 'id=' . $DB->quote($this->id));

        $DB->StartTrans();
        
        //var_dump($q);
        
        /* save the forum */            
        if (!$DB->execute($q)) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        /* set the current id so we can add moderators */
        if($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'forum_fora', 'id');
        }

        /* save the moderators */
        if($this->getModerators() != null && $this->id != null) {

            $moderators = $this->getModerators();
            if ($moderators == null){
                $userIds = '0';
            } else {
                $userIds = Database::makeCommaSeparatedString($moderators, 'id');
            }

            /* delete the users that are no more moderators */
            $stmt = 'DELETE FROM '. DB_SCHEMA . '.forum_moderator WHERE forum_id=' . 
                $DB->quote($this->id) . ' AND user_id NOT IN (';
                
            
            $stmt .= $userIds . ') ';
            $res = $DB->execute($stmt);
            
            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
            
            /* add the new users */
            $stmt = 'INSERT INTO ' . DB_SCHEMA . '.forum_moderator (user_id, forum_id)  
                        SELECT id, ' . $DB->quote($this->id) . 
                        ' FROM ' . DB_SCHEMA . '.users 
                         WHERE id IN ('. $userIds . ') 
                           AND id NOT IN  
                                (SELECT user_id 
                                   FROM ' . DB_SCHEMA . '.forum_moderator 
                                  WHERE forum_id = ' . $DB->quote($this->id) . ' 
                                    AND user_id IN ('. $userIds . '))';
            $res = $DB->execute($stmt);
            
            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
                        
        }

        if(!$DB->CompleteTrans()){
            throw new DBException(DB_TRANSACTION_FAILED);
        }         
    }


    
    /**
     * IMPORTENT: if you want test if a user is a moderator of 
     *  a forum use <code>isUserModerator($userModel)</code>
		 *
		 * @throws DBException
     * 
     * @return array a array of UserModel objects that contains all moderators of the forum
     */
    protected function _getModerators() {
        
        /* load the data on demand */
        
        /* without a id is no way to load our moderators */
        if($this->id == null)
            throw new ArgumentNullException('id');
            
        $DB = Database::getHandle();            
            
        $q = 'SELECT user_id 
              FROM 
                ' . DB_SCHEMA . '.forum_moderator 
              WHERE forum_id =' . $DB->quote($this->id);
              
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        /* get user ids */
        $ids = array();        
        foreach($res as $row) {
            $ids[] = $row['user_id'];
        }
        
        /* publish the information that the moderators were loaded */
        $this->moderatorsLoaded = true;
        
        $this->setModerators(UserProtectedModel::getUsersByIds($ids));
    }

    /**
     * Test if a given user is a moderator of forum.
     * It's importent that this function is used because it
     * also test that the user can be a category admin
     * 
     * @return true when the user is a moderator
     */
    public function isModerator($userModel) {
        
        if($userModel->hasRight('FORUM_CATEGORY_ADMIN')){
        	return true;
        }

        if($this->isGroupForum() && $userModel->hasGroupRight('FORUM_GROUP_MODERATOR', $this->getGroupId())){
        	return true;
        }
        
        /* test the moderator of the forum */
        foreach ($this->getModerators() as $user) {
            if ($userModel->equals($user)) {
                return true;
            }
        }
        
        return $this->getCategory()->isModerator($userModel);
    }
    
    /**
     * deletes the current model form the DB
		 *
		 * @throws DBException
     */
    public function delete() {
        /* without a id is no way to load our moderators */
        if($this->id == null)
            throw new ArgumentNullException('id');
            
        $DB = Database::getHandle();            
            
        $q = 'DELETE FROM ' . DB_SCHEMA . '.forum_fora 
              WHERE id =' . $DB->quote($this->id);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }
    
    /**
     * rePosition the Forum
     */
    public function rePosition($value){
    	
        if($this->id == null){
            throw new ArgumentNullException('id');
        }
        
        $DB = Database::getHandle();            
        
        if ($value == 'down'){    
            $delta = 1;
        } else if ($value == 'up'){    
            $delta = -1;      
        } else {
            throw new ArgumentException('value', $value);
        }
        
        $q = 'SELECT public.reposition_forum(\'' . DB_SCHEMA . '\', ' . $DB->Quote($this->id) . '::bigint, ' . $delta . '::smallint);';        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }        
    }
    
    /**
     * Set the tags of the forum in $this->tags
     */
    protected function getTagsCallback() {
        if($this->id == null)
        	return array();
        
        $this->tags = TagModel::getTagByForum($this->id);
		$this->tagsLoaded = true;
    }
    
    
   protected function loadGroupId(){
        
        if($this->groupId !== null){
        	return $this->groupId;
        }
        
        if($this->id == null){
            throw new ArgumentNullException('id');
        }
        
        $DB = Database::getHandle();
        
        $q = 'SELECT group_id, is_default FROM '. DB_SCHEMA .'.groups_forums WHERE forum_id = '.$DB->quote($this->id);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        if(empty($res->fields['group_id'])){
        	$this->groupId = false;
            $this->isGroupDefault = null;
        }else{
            $this->groupId = $res->fields['group_id'];
            $this->isGroupDefault = Database::convertPostgresBoolean($res->fields['is_default']); 
        }
        
        return $this->groupId;
    }
    
    public function isGroupForum(){
    	return ($this->loadGroupId() !== false);
    }
    
    public function isGroupDefaultForum(){
    	if($this->isGroupDefault === null){
    		$this->loadGroupId();
    	}
        return $this->isGroupDefault;
    }
    
    public function getGroupId(){
        return $this->loadGroupId();
    }
    
    public function addGroup($groupId = 0){
        
        if($this->id == null){
            throw new ArgumentNullException('id');
        }
        
        $DB = Database::getHandle(); 
        
        $DB->StartTrans();
        
        $keyValue = array();
        $keyValue['forum_id'] = $DB->Quote($this->id);
        $keyValue['group_id'] = $DB->Quote($groupId);
        
        $q = $this->buildSqlStatement('groups_forums', $keyValue);
        //var_dump($q);
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        if(!$DB->CompleteTrans()){
            throw new DBException(DB_TRANSACTION_FAILED);
        }  
    }
    
    /**
     * replaces forum by newForum
     * @param ForumModel
     * @param ForumModel
     */
    public static function replaceForum($forum, $newForum) {
        // if forum is not valid
        if ($forum->id == 0 or $newForum->id == 0) {
            return false;
        }

        $DB = Database::getHandle();
       
        // replace author
        $q =  'UPDATE ' . DB_SCHEMA . '.forum_fora
                  SET forum_parent_id = ' . $DB->Quote($newForum->id) . '
                WHERE forum_parent_id = ' . $DB->Quote($forum->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        return true;
    }
}
?>
