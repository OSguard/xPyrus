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
 * Created on 14.06.2006
 */
require_once MODEL_DIR . '/base/base_entry_model.php';
require_once MODEL_DIR . '/base/user_protected_model.php';
require_once MODEL_DIR . '/base/interactive_user_element_model.php';
require_once CORE_DIR . '/exceptions/core_exception.php';
require_once CORE_DIR . '/constants/value_constants.php';
require_once CORE_DIR . '/models/forum/thread_entry_model.php';
/**
 * @class ThreadModel
 * @brief represants a tread of the forum
 * 
 * @author schnueptus, kyle, linap
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * @package Models/Forum
 * 
 * from ThreadModel
 * <ul>
 * <li><var>id</var><b>int </b></li>
 * <li><var>caption</var><b> string</b> the caption/tiltle of the thread</li>
 * <li><var>timeEntry</var><b> time</b> the time where thread stars</li>
 * <li><var>lastEntryTime</var><b> time</b> the time where last entry add</li>
 * <li><var>firstEntry</var><b> ThreadEntryModel</b> of the first entry</li>
 * <li><var>lastEntry</var><b> ThreadEntryModel</b> of the last entry</li>
 * <li><var>isVisible</var><b> boolean </b></li>
 * <li><var>isClosed</var><b> boolean</b></li>
 * <li><var>isSticky</var><b> boolean</b></li>
 * <li><var>numberOfEntries</var><b> int</b> counter of entries</li>
 * <li><var>numberOfViews</var><b> int</b> view counter</li>
 * <li><var>forum</var><b> ForumModel</b></li>
 * <li><var>forumId</var><b> int</b> the id of the Forum</li>
 * <li><var>forumName</var><b> string</b> the name of the Fourm</li>
 * <li><var>categoryName</var><b> string</b> the Name of the Category</li>
 * <li><var>counter</var><b> array of int</b> the non-linear counter</li>
 * <li><var>page</var><b> int</b> the current page number</li>
 * <li><var>entriesPerPage</var><b> int</b> who many entrys per page are display</li>
 * <li><var>totalPages</var><b> int</b> </li>
 * <li><var>$linkToThread</var><b> ThreadModel</b> if the Thread is moved her will the right Thread linked</li>
 * </u1>
 */
class ThreadModel extends InteractiveUserElementModel {

    /* creation date of the thread */
    protected $timeEntry;
    protected $lastEntryTime;
    
    /**
     * ThreadModel of first an last Entry
     */    
    protected $firstEntryId;
    protected $lastEntryId;
    protected $firstEntry;
    protected $lastEntry;
    
    /**
     * caption of the Thread
     */
    protected $caption;
    /**
     * some optians
     */
    protected $isVisible;
    protected $isClosed;
    protected $isSticky;
    
    /**
     * some statistics
     */
    protected $numberOfEntries;
    protected $numberOfViews;
    
    /**
     * saves the ForumModel
     */
    private $forum_cache;
    /**
     * saves information about forum & categorys
     */
    protected $forumId;
    protected $forumName;
    protected $categoryName;
    protected $categoryId;
    
    
    /**
     * need for page output
     */
    private $page;
    
    /**
     * the Model with Linked Thread
     * @var ThreadModel
     */
    protected $linkToThread;

    protected $hasRatings = array();
    protected $authorIds = array();

    // TODO: remove: page property from model (linap, 06.05.2007)
    public function __construct($page = 1) {   
        parent :: __construct();
        $this->page = $page;
        //var_dump($this);    
    }

    protected function buildFromRow($row) {
        $this->id = $row['id'];
        
        $this->forumId = $row['forum_id'];

        //$this->firstEntry = ThreadEntryModel::getThreadEntryById($row['first_entry']);            
        //$this->lastEntry = ThreadEntryModel::getThreadEntryById($row['last_entry']);
        $this->firstEntryId = $row['first_entry'];
        $this->lastEntryId = $row['last_entry'];
          
            
        $this->lastEntryTime = $row['last_entry_time'];
        $this->timeEntry = $row['entry_time'];
        
        $this->caption = $row['caption'];

        $this->isClosed = Database :: convertPostgresBoolean($row['is_closed']);
        $this->isVisible = Database :: convertPostgresBoolean($row['is_visible']);
        $this->isSticky = Database :: convertPostgresBoolean($row['is_sticky']);
        
        $this->numberOfEntries = $row['number_of_entries'];
        $this->numberOfViews = $row['number_of_views'];                
        /*
        $this->categoryName = $row['category_name'];
        $this->categoryId = $row['category_id'];
        $this->forumName = $row['forum_name'];       
        */
        /* insert the thread this thread linked to */
        if(!empty($row['link_to_thread']))
            $this->linkToThread = $this->getThreadById($row['link_to_thread']);
        else   
            $this->linkToThread = null;         
        
        return $this;
    }

    /**
     * Get the first part of the select statement
     */
    private function getSelectSqlStatement_old(){
        return 'SELECT
            t.*, c.id AS category_id, t.first_entry, t.last_entry,
            extract(epoch from entry_time) AS entry_time,
            extract(epoch from last_entry_time) AS last_entry_time,
            last_entry_time AS order_last_entry_time
        FROM
            '. DB_SCHEMA . '.forum_threads AS t
        WHERE ';
    }

    private static function getSelectSqlStatement(){
        return 'SELECT
            t.*, t.first_entry, t.last_entry,
            extract(epoch from entry_time) AS entry_time,
            extract(epoch from last_entry_time) AS last_entry_time,
            last_entry_time AS order_last_entry_time
        FROM
            '. DB_SCHEMA . '.forum_threads AS t
        WHERE ';
    }
    
    public static function loadLastEntry($threads){
    	/**
         * load the last Entry
         */
        $lastEntryId = array();
        foreach($threads as $thread){
            $lastEntryId[] = $thread->getLastEntryId();
        }
        $lastEntrys = ThreadEntryModel::getThreadEntryByIds($lastEntryId);
        foreach($threads as $thread){
            if($thread->getLastEntryId() != ''){
                $thread->addLastEntry($lastEntrys[$thread->getLastEntryId()]);
            }
        }
        
        return $threads;
    }
    
    public static function getThreadById($id) {
        $DB = Database :: getHandle();

        if ($id == null) {
            throw new ArgumentNullException('id');
        }

        // retrieve entries without attachments
        $q = self::getSelectSqlStatement() . 't.id = ' . $DB->quote($id);
        //. ' AND f.id = t.forum_id AND c.id = f.category_id';

        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $thread = new ThreadModel ();

        foreach ($res as $row) {
            return $thread->buildFromRow($row);
        }
    }

    /**
    * Get a array with all ThreadModels form a ForumId
    *
    * @param int $forumId the id of the forum where the threads are
    * @param int $limit show only the first/last $limit entries
    * @param int $offset skip the first/last $offset entries
    * @param string $order $order = ( 'asc' | 'desc' ), sort order ascending or descending
    * @param boolean $showInvisible show (not) hidden threads
    * @throws DBException on DB error
    *
    * @return array with ThreadModels
    *
    * @todo optimize DB-query
    * @todo implement filtering
    */
    public static function getAllThreadsByForumId($forumId = 0, $limit = V_FORUM_THREADS_PER_PAGE, $offset = 0, $order = 'desc', $showInvisible = false) {
        $DB = Database :: getHandle();

        /* temporary fix ;) */
        if($forumId == null || $forumId == 0) {
            throw new ArgumentNullException('forumId');
        }

        // retrieve entries without attachments
        $q = self::getSelectSqlStatement() . 't.forum_id = ' . $DB->Quote($forumId);
        //$q .= ' AND f.id = t.forum_id AND c.id = f.category_id';

        // additionally filter by given criteria, if neccessary
        //$q.=$this->getSQLFilterString();
        if (!$showInvisible) {
            $q .= ' AND t.is_visible = \'TRUE\' ';
        }
        $q .= ' ORDER BY t.is_sticky DESC, t.last_entry_time ' . $order . ', id ' . $order . '
                    LIMIT ' . $DB->Quote($limit) . '
                   OFFSET ' . $DB->Quote($offset);
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $threads = array ();

        foreach ($res as $k => $row) {
            $newThread = new ThreadModel();
            $newThread->buildFromRow($row);

            $threads[] = $newThread;
        }
        
        $threads = ThreadModel::loadLastEntry($threads);
        
        return $threads;
    }
    
    /**
    * Get a array with all ThreadModels by lates changes personal by User
    *   
    * @param int $limit show only the first/last $limit entries
    * @param int $offset skip the first/last $offset entries
    * @param string $order $order = ( 'asc' | 'desc' ), sort order ascending or descending
    * @param boolean $showInvisible show (not) hidden threads
    * @param string $category_type filter by category_type
    * @throws DBException on DB error
    *
    * @return array with ThreadModels
    *
    * @todo optimize DB-query
    * @todo implement filtering
    */
    public static function getAllThreadsByLatestUser($user, $limit = V_FORUM_THREADS_PER_PAGE, $offset = 0, $order = 'desc', $showInvisible = false, $categoryName = 'default') {
        $DB = Database :: getHandle();

        // retrieve entries without attachments    
        $q = self::getSelectSqlStatement();
        $q .= ' t.link_to_thread IS NULL ';
        if (!$showInvisible) {
            $q .= ' AND t.is_visible = \'TRUE\' ';
        }
        
        $visibleClause = array();
        $categoryClause = ' TRUE ';
        if ($user->isLoggedIn()) {
            $groups = $user->getGroupMembership();
            if (count($groups) == 0) {
                $visibleClause[] = ' visible IN (SELECT id FROM public.details_visible WHERE name = \'all\' OR name = \'logged in\')';
            } else {
                $visibleClause[] = ' visible IN (SELECT id FROM public.details_visible WHERE name = \'all\' OR name = \'logged in\')';
                $visibleClause[] = '
                                visible = (SELECT id FROM public.details_visible WHERE name = \'group\')
                                   AND id IN (SELECT forum_id 
                                                        FROM ' . DB_SCHEMA . '.groups_forums 
                                                       WHERE group_id IN ( ' . Database::makeCommaSeparatedString($groups, 'id') . '))';
            }
        } else {
            $visibleClause[] = ' visible IN (SELECT id FROM public.details_visible WHERE name = \'all\')';
        }
        $q .= ' AND t.forum_id IN (SELECT id 
                                     FROM ' . DB_SCHEMA . '.forum_fora 
                                    WHERE __VISIBLE__ 
                                      AND important ';
        if ($categoryName != '') {
                             $q .=  ' AND category_id IN (SELECT id 
                                                            FROM '. DB_SCHEMA .'.forum_categories 
                                                           WHERE category_type= ' . $DB->Quote($categoryName) . ')';
        }
        $q .=                       ')';
        $qStr = '';
        foreach ($visibleClause as &$v) {
            $v = str_replace('__VISIBLE__', $v, $q);
        }
        $q = implode(' UNION ALL ', $visibleClause);
        $q .= ' ORDER BY order_last_entry_time ' . $order . ', id ' . $order . '
                    LIMIT ' . $DB->Quote($limit) . '
                   OFFSET ' . $DB->Quote($offset);
                   
        /* be sure that there is not to much mixed forums */  
        //$q = 'SELECT * FROM (' . $q . ') AS x ORDER BY forum_id';         
        //var_dump($q);  
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $threads = array ();

        foreach ($res as $k => $row) {
            $newThread = new ThreadModel();
            $newThread->buildFromRow($row);

            $threads[] = $newThread;
        }
        
        $threads = ThreadModel::loadLastEntry($threads);
        
        return $threads;
    }
    
    /**
    * Get a array with all ThreadModels by lates changes of all user signed courses   
    *   
    * @param int $limit show only the first/last $limit entries
    * @param int $offset skip the first/last $offset entries
    * @param string $order $order = ( 'asc' | 'desc' ), sort order ascending or descending
    * @param boolean $showInvisible show (not) hidden threads
    * @param UserModel $user the user who sign the course
    * @throws DBException on DB error
    *
    * @return array with ThreadModels
    *
    * @todo optimize DB-query
    * @todo implement filtering
    */
    public static function getAllCourseThreadsByUser( $limit = V_FORUM_THREADS_PER_PAGE, $offset = 0, $order = 'desc', $showInvisible = false, $user = null, $tagThreads = false) {
        $DB = Database :: getHandle();
        
        if($user == null){
        	return array();
        }
        
        $order = strtolower($order);
        if ($order != 'asc' and $order != 'desc') {
            $order = 'desc';
        }
        
        // retrieve entries without attachments
        $q = self::getSelectSqlStatement();
        
        $subs = 'SELECT course_id
                   FROM '. DB_SCHEMA .'.courses_per_student
                  WHERE user_id =' . $DB->Quote($user->id);

        $subsel = 'SELECT forum_id
                     FROM ' . DB_SCHEMA . '.courses_data
                    WHERE course_id IN (' .$subs .')' ;
            
        $q .= ' t.forum_id IN ('. $subsel .') ';
        
        $q .= ' AND link_to_thread IS NULL';      
        if (!$showInvisible) {
            $q .= ' AND t.is_visible = \'TRUE\' ';
        }
        
        if($tagThreads and $user->isLoggedIn() and !$user->isExternal()){
        	$q .= "\n" . 'UNION ' . self::getSelectSqlStatement();
            
            /* search all tags of the User */
            $linkedTags = array();
            foreach ($user->getStudyPathsId() as $studyPathId){
                $linkedTags = $linkedTags + TagModel::getTagByStudyPath($studyPathId);
            }
            
            $subselect = 'SELECT forum_id ' .
                           'FROM '. DB_SCHEMA .'.forum_tag ' .
                          'WHERE tag_id IN (' . Database::makeCommaSeparatedString($linkedTags, 'id', true) . ')';
            
            $q .= ' forum_id IN (' . $subselect . ')';
        }
        /*
         * get forum from course_old cathegory
         */
        /*
         * dont need this any more, becouse a lot of user want to remove this
         * maybe they happy without, just a live test
         * (schnueptus 31.06.07)
         * 
         * $q .= "\n" . 'UNION ' . self::getSelectSqlStatement();
         *
         * $subsel =  'SELECT id 
         *             FROM '. DB_SCHEMA .'.forum_fora
         *             WHERE category_id IN (SELECT id 
         *                                    FROM '. DB_SCHEMA .'.forum_categories 
         *                                   WHERE category_type= ' . $DB->Quote('course_old') . ')';
         *
         * $q .= ' t.forum_id IN ('. $subsel .') ';
         */
         
        $q .= ' ORDER BY order_last_entry_time ' . $order . ', id ' . $order . '
                    LIMIT ' . $DB->Quote($limit) . '
                   OFFSET ' . $DB->Quote($offset);
                   
        #var_dump($q);           
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $threads = array ();

        foreach ($res as $k => $row) {
            $newThread = new ThreadModel();
            $newThread->buildFromRow($row);

            $threads[] = $newThread;
        }
        
        $threads = ThreadModel::loadLastEntry($threads);
        
        return $threads;
    }

    public static function getAllThreadsByTag($tagId, $limit = V_FORUM_THREADS_PER_PAGE, $offset = 0, $order = 'desc', $showInvisible = false, $category = ''){
    	$DB = Database :: getHandle();

        $order = strtolower($order);
        if ($order != 'asc' and $order != 'desc') {
            $order = 'desc';
        }
        
        /* temporary fix ;) */
        if($tagId == null) {
            throw new ArgumentNullException('tagId');
        }
        
        $subselct = 'SELECT forum_id 
                       FROM '. DB_SCHEMA .'.forum_tag 
                      WHERE tag_id = ' . $DB->Quote($tagId);
        
        $q = self::getSelectSqlStatement();
        
        // retrieve entries without attachments
        $q .=  't.forum_id IN ('.$subselct.')';
        //$q .= ' AND f.id = t.forum_id AND c.id = f.category_id';

        // additionally filter by given criteria, if neccessary
        //$q.=$this->getSQLFilterString();
        if (!$showInvisible) {
            $q .= ' AND t.is_visible = \'TRUE\' ';
        }
        $q .= ' ORDER BY t.is_sticky DESC, t.last_entry_time ' . $order . ', id ' . $order . '
                    LIMIT ' . $DB->Quote($limit) . '
                   OFFSET ' . $DB->Quote($offset);
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $threads = array ();

        foreach ($res as $k => $row) {
            $newThread = new ThreadModel();
            $newThread->buildFromRow($row);

            $threads[] = $newThread;
        }
        
        $threads = ThreadModel::loadLastEntry($threads);
        
        return $threads;
        
    }

    public static function getAllAboThreadsByUserId($userId = 0, $limit = V_FORUM_THREADS_PER_PAGE, $offset = 0, $order = 'desc', $showInvisible = false) {
        $DB = Database :: getHandle();

        /* temporary fix ;) */
        if($userId == null || $userId == 0) {
            throw new ArgumentNullException('userId');
        }

        $subselect = 'SELECT thread_id FROM '. DB_SCHEMA .'.forum_abo WHERE user_id = '. $DB->Quote($userId);
        
        // retrieve entries without attachments
        $q = self::getSelectSqlStatement() . 't.id IN (' . $subselect . ')';
        //$q .= ' AND f.id = t.forum_id AND c.id = f.category_id';

        // additionally filter by given criteria, if neccessary
        //$q.=$this->getSQLFilterString();
        if (!$showInvisible) {
            $q .= ' AND t.is_visible = \'TRUE\' ';
        }
        $q .= ' ORDER BY t.is_sticky DESC, t.last_entry_time ' . $order . ', id ' . $order . '
                    LIMIT ' . $DB->Quote($limit) . '
                   OFFSET ' . $DB->Quote($offset);
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $threads = array ();

        foreach ($res as $k => $row) {
            $newThread = new ThreadModel();
            $newThread->buildFromRow($row);

            $threads[] = $newThread;
        }
        
        $threads = ThreadModel::loadLastEntry($threads);
        
        return $threads;
    }
    
   /**
    * updates the viewCounter of one Thread
    *
    * @throws DBException on DB error
    */
   public function incViewCounter(){
        $DB = Database :: getHandle();

        if($this->id == null) {
            throw new ArgumentNullException('id');
        }

        $q = 'UPDATE ' . DB_SCHEMA . '.forum_threads ' .
               ' SET number_of_views = number_of_views + 1 ' .
             ' WHERE id = ' . $DB->Quote($this->id);
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

   }

    public function save() {
        if ($this->forumId == null){
            throw new ArgumentNullException('forumId');
        }

        $keyValue = array();

        $DB = Database::getHandle();

        /* it's an insert? */
        if($this->id == null) {
            $keyValue['entry_time'] = 'now()';
        }

        $keyValue['caption'] = $DB->quote($this->caption);
        $keyValue['is_visible'] = $DB->quote(Database::makeBooleanString($this->isVisible));
        $keyValue['is_closed'] = $DB->quote(Database::makeBooleanString($this->isClosed));
        $keyValue['is_sticky'] = $DB->quote(Database::makeBooleanString($this->isSticky));
        $keyValue['forum_id'] = $DB->quote($this->forumId);
        
        
        if ($this->firstEntryId != null) {
          $keyValue['first_entry'] = $DB->quote($this->firstEntryId);
        }
        if ($this->lastEntryId != null) {
          $keyValue['last_entry'] = $DB->quote($this->lastEntryId);
        }
        if($this->linkToThread != null)
            $keyValue['link_to_thread'] = $DB->quote($this->linkToThread->id);
        
        /** is update? we need the a where clausel */    
        if($this->id != null)    
            $q = $this->buildSqlStatement('forum_threads', $keyValue,  false, 'id = ' . $DB->quote($this->id));
        else
            $q = $this->buildSqlStatement('forum_threads', $keyValue);   

        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        if($this->id == null){
            $this->id = Database::getCurrentSequenceId($DB, 'forum_threads', 'id');
        }

    }
    
    /**
     * Link the current thread to a given forum.
     * 
     * @param $destForum ForumModel the destination forum
     */
    public function linkToForum($destForum) {
        
        $fId = $this->forumId;

        $DB = Database::getHandle();
        $DB->StartTrans();
        
        /* set the id of the new forum link it so */
        $this->forumId = $destForum->id;
        $this->save();  
        
        /* create a new thread as link to that one */
        $linkThread = new ThreadModel();
        $linkThread->isVisible = $this->isVisible();
        $linkThread->isSticky = $this->isSticky();
        $linkThread->isClosed = $this->isClosed();
        $linkThread->caption = $this->getCaption();
        $linkThread->linkToThread = $this;
        $linkThread->forumId = $fId;
        $linkThread->firstEntryId = $this->getFirstEntryId();
        $linkThread->lastEntryId = $this->getLastEntryId();
        $linkThread->save();        
        
        // update stats of destination forum 
        $q = 'UPDATE ' . DB_SCHEMA . '.forum_fora 
                 SET number_of_entries = number_of_entries + (SELECT number_of_entries 
                                                                FROM ' . DB_SCHEMA . '.forum_threads 
                                                               WHERE id = ' . $DB->Quote($this->id) . ')
               WHERE id=' . $DB->quote($this->forumId);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // update stats of old forum (a trigger incremented #threads on INSERT above (linkToThread->save)) 
        $q = 'UPDATE ' . DB_SCHEMA . '.forum_fora 
                 SET number_of_threads = number_of_threads - 1
               WHERE id=' . $DB->quote($fId);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        if (!$DB->CompleteTrans()) {
            throw new DBException(DB_TRANSACTION_FAILED); 
        }
    }

    public function getCounter($page = 0, $entriesPerPage = V_FORUM_THREAD_ENTRIES_PER_PAGE) {
        // TODO: use given parameter page and not model property
        return self::nonLinearCounter( ceil($this->numberOfEntries / $entriesPerPage), $this->page);
    }
    public function getPage() {
        return $this->page;
    }
    public function setPage($page) {
        $this->page = $page;
    }
    public function getTotalPages($entriesPerPage = V_FORUM_THREAD_ENTRIES_PER_PAGE) {
        return ceil($this->numberOfEntries / $entriesPerPage);
    }
    public function getForum() {
        if ($this->forum_cache == null){ 
            return $this->forum_cache = ForumModel::getForumById($this->forumId);
        } else {
            return $this->forum_cache;
        }
    }
    
    public function getTimeEntry() {
        return $this->timeEntry;
    }
    public function getTimeLastEntry() {
        return $this->lastEntryTime;
    }
    
    public function getFirstEntry() {
        if($this->firstEntry == null){
        	$this->firstEntry = $this->firstEntry = ThreadEntryModel::getThreadEntryById($this->firstEntryId);
        }        
        return $this->firstEntry;
    }
    public function getFirstEntryId() {
    	return $this->firstEntryId;
    }
    public function getLastEntry() {
        if($this->lastEntry == null){
            $this->lastEntry = $this->firstEntry = ThreadEntryModel::getThreadEntryById($this->lastEntryId);
        }
        return $this->lastEntry;
    }
    public function getLastEntryID() {
    	return $this->lastEntryId;
    }
    public function addLastEntry($entry){
    	$this->lastEntry = $entry;
    }
    public function getCaption() {
        return $this->caption;
    }
    public function setCaption($caption) {
        $this->caption = $caption;
    }
    
    public function isVisible() {
        return $this->isVisible;
    }
    public function setVisible($flag) {
        $this->isVisible = $flag;
    }
    
    public function isClosed() {
        return $this->isClosed;
    }
    public function setClosed($flag) {
        $this->isClosed = $flag;
    }
    
    public function isSticky() {
        return $this->isSticky;
    }
    public function setSticky($flag) {
        $this->isSticky = $flag;
    }
    
    public function getNumberOfEntries() {
        return $this->numberOfEntries;
    }
    
    public function getNumberOfViews() {
        return $this->numberOfViews;
    }
    
    public function getForumId() {
        return $this->forumId;
    }
    public function setForumId($id) {
        $this->forumId = $id;
    }
    
    public function getForumName() {
        return $this->getForum()->getName();
    }
    
    public function getCategoryName() {
        return $this->getForum()->getCategory()->getName();
    }
    
    public function getCategoryId() {
        return $this->getForum()->getCategoryId();
    }
    
    public function getLinkToThread() {
        return $this->linkToThread;
    }
    
    protected function __set($name, $value){
        throw new CoreException ("don't use __set-magic (" . $name . "), you have to use new setter functions instead");
    }
    
    public function getThreadStatsByTag($tagId = 0, $showInvisible = false){
        $DB = Database :: getHandle();

        /* temporary fix ;) */
        if ($tagId == null) {
            throw new ArgumentNullException('tagId');
        }
        
        $subselct = 'SELECT forum_id FROM '. DB_SCHEMA .'.forum_tag WHERE tag_id =' . $DB->quote($tagId);
        
        $q = 'SELECT count(id) AS number_of_threads, sum(number_of_entries) AS number_of_entries
               FROM '. DB_SCHEMA . '.forum_threads
               WHERE forum_id IN ('.$subselct.')';        

        // additionally filter by given criteria, if neccessary
        //$q.=$this->getSQLFilterString();
        if (!$showInvisible) {
            $q .= ' AND is_visible = \'TRUE\' ';
        }

        $res = $DB->execute($q);

        #var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }


        foreach ($res as $k => $row) {
            if($row['number_of_entries'] == null){ 
                    $row['number_of_entries'] = 0;
            }
            return $row;
        }                                
    }
    
    public function addRating($author, $user, $value){
    	 $DB = Database :: getHandle();
         
         if($author == null) {
         	throw new ArgumentNullException('author');
         } else if ($user == null) {
         	throw new ArgumentNullException('user');
         } else if (empty($value)) {
         	throw new ArgumentNullException('value');
         }
         
         $keyValue = array();
         $keyValue['thread_id'] = $DB->quote($this->id);
         $keyValue['user_id'] = $DB->quote($user->id);
         $keyValue['rated_user_id'] = $DB->quote($author->id);
         $keyValue['rating'] = $DB->quote($value);
         
         $q =  $q = $this->buildSqlStatement('forum_thread_ratings', $keyValue);
         
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        return true;
    }
    
    public function hasUserRating($author, $user){
    	if($author == null) {
         	throw new ArgumentNullException('author');
         } else if ($user == null) {
         	throw new ArgumentNullException('user');
         }
        
        $authorId = 0;
        if ($author instanceof UserModel) {
            $authorId = $author->id;
        } else {
            $authorId = $author;
        }
        
        if (empty($this->hasRatings) ||empty($this->hasRatings[$user->id])) {
            $this->hasRatings[$user->id] = array();
            $DB = Database :: getHandle();
            
            $q = 'SELECT rated_user_id ' .
                  ' FROM ' . DB_SCHEMA . '.forum_thread_ratings ' .
                 ' WHERE user_id = ' . $DB->quote($user->id) .
                   ' AND thread_id = ' . $DB->quote($this->id);
            
            $res = $DB->execute($q);
            //var_dump($q);
            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
            
            foreach ($res as $k => $row) {
                $this->hasRatings[$user->id][$row['rated_user_id']] = true;
            }
        }
        return array_key_exists($authorId, $this->hasRatings[$user->id]);
    }
    
    public function isAuthor($user){
        
        if($user == null ){
            throw new ArgumentNullException('user');
        }        
        
        if(!empty($this->authorIds)){
                return array_key_exists($user->id, $this->authorIds);         
        }
        else{
            $this->hasRatings[$user->id] = array();
            $DB = Database :: getHandle();
            
            $q = 'SELECT DISTINCT(author_int) ' .
                   'FROM ' . DB_SCHEMA . '.forum_thread_entries ' .
                  'WHERE thread_id = ' . $DB->quote($this->id) .
                   ' AND enable_anonymous = ' . $DB->quote(Database::makeBooleanString(false)) .
                   ' AND group_id IS NULL';
            
            $res = $DB->execute($q);
            //var_dump($q);
            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
            
            foreach ($res as $k => $row) {
            
                $this->authorIds[$row['author_int']] = true;
            }  
            
            return array_key_exists($user->id, $this->authorIds);  
        }
    }
    
    public static function nonLinearCounter($total, $position){
        return parent::nonLinearCounter($total, $position);
    }
    
    /**
     * provides an appropiate instance of BaseFilter class
     * to operate on thread entries
     * @param array
     * @return BaseFilter
     */
    public static function getFilterClass($filterOptions) {
        include_once MODEL_DIR . '/forum/thread_entry_filter.php';
        return new ThreadEntryFilter($filterOptions);
    }
    
      /**
     * deletes a forum entry
     */
    public function delete() {
        $DB = Database::getHandle();
        
        // delete this thread
        $q = 'DELETE FROM ' . DB_SCHEMA . '.forum_threads WHERE id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
  public function addAbo($userId = 0){
    	
        if($this->id == null){
            throw new ArgumentNullException('id');
        }
        
        $DB = Database::getHandle(); 
        
        $DB->StartTrans();
        
        $keyValue = array();
        $keyValue['thread_id'] = $DB->Quote($this->id);
        $keyValue['user_id'] = $DB->Quote($userId);
        
        $q = $this->buildSqlStatement('forum_abo', $keyValue);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        if(!$DB->CompleteTrans()){
            throw new DBException(DB_TRANSACTION_FAILED);
        }  
    }
    
     public function removeAbo($userId = 0){
        
        if($this->id == null){
            throw new ArgumentNullException('id');
        }
        
        $DB = Database::getHandle(); 
        
        $DB->StartTrans();
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.forum_abo
              WHERE thread_id =' . $DB->quote($this->id) . '
              AND user_id ='. $DB->quote($userId);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        if(!$DB->CompleteTrans()){
            throw new DBException(DB_TRANSACTION_FAILED);
        }  
    }
    
   public function hasAbo($userId = 0){
        
        if($this->id == null){
            throw new ArgumentNullException('id');
        }
        
        $DB = Database::getHandle(); 
        
        $q = 'SELECT id FROM '. DB_SCHEMA .'.forum_abo WHERE thread_id = '.$DB->quote($this->id) . '
                AND user_id = '. $DB->Quote($userId);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        return $res->fields['id'] > 0;
    }
    
    /**
     * replaces authorship of author by newAuthor
     * @param UserModel
     * @param UserModel
     * 
     * @note the id param is not working now; it could be extended to restrict the replacement to entries given by $id-param
     */
    public static function replaceAuthor($author, $newAuthor, $id = null) {
        return parent::replaceAuthor('forum_thread_entries', $author, $newAuthor, $id);
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
        $q =  'UPDATE ' . DB_SCHEMA . '.forum_threads
                  SET forum_id = ' . $DB->Quote($newForum->id) . '
                WHERE forum_id = ' . $DB->Quote($forum->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        return true;
    }
}
?>
