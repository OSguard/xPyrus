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

require_once MODEL_DIR . '/base/base_entry_model.php';
require_once MODEL_DIR . '/base/user_protected_model.php';
require_once MODEL_DIR . '/base/group_model.php';
require_once CORE_DIR . '/exceptions/core_exception.php';
require_once MODEL_DIR . '/forum/thread_model.php';
require_once MODEL_DIR . '/base/user_external_model.php';

/**
 * @class ThreadEntryModel
 * @brief represents one entry of one thread in the forum
 * 
 * @author schnueptus, kyle, linap
 * @version $Id: thread_entry_model.php 5743 2008-03-25 19:48:14Z ads $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * The following properties are available:
 * 
 * from ThreadEntryModel
 * <ul>
 * <li><var>id</var>            <b>int</b></li>
 * <li><var>caption</var>       <b>string</b>   caption/title of the Entry (extra)</li>
 * <li><var>isAnonymous</var>   <b>boolean</b>  the author will not shown</li>
 * <li><var>threadId</var>      <b>int</b>      The id of the Thread who the entry is inside</li>
 * <li><var>thread</var>        <b>ThreadModel</b>  the Model of the Thread who the entry inside</li>
 * <li><var>forum</var>         <b>ForumModel</b>   the Model of the Forum who the entry inside</li>
 * <li><var>postIp</var>        <b>string</b>   ip address entry was posted from</li>
 * <li><var>nrInThread</var>    <b>int></b>     the number of the posting in the thread
 * </ul>
 * 
 * from BaseEntryModel
 * <ul>
 * <li><var>author</var>            <b>UserModel</b>    the author of the Entry</li>
 * <li><var>timeEntry</var>         <b>date</b>         time when the entry was generated</li>
 * <li><var>timeLastUpdate</var>    <b>date</b>         time when the last change took place</li>
 * <li><var>content</var>           <b>string</b>       parsed content</li>
 * <li><var>contentRaw</var>        <b>string</b>       not parsed content</li>
 * <li><var>enableSmileys</var>     <b>boolean</b></li>
 * <li><var>enableFormatCode</var>  <b>boolean</b></li>
 * <li><var>attachments</var>       <b>EntryAttachmentModel</b></li>
 * </ul>
 * 
 * @package Models/Forum
 */
class ThreadEntryModel extends BaseEntryModel {

    /**
     * the author will not shown
     * @var boolean
     */
    protected $isAnonymous;
    /**
     * the id of the thread of the entry
     * @var int
     */
    protected $threadId;

    /**
     * the caption/title of the entry
     * @var string
     */
    protected $caption;

    protected $authorIntId;
    protected $authorExtId;

    /**
     * IP from which the user has posted the entry. It's ok that this is
     * public because don't hide here a variable from the base class!
     * 
     * @author Kyle
     * @var string
     */
    protected $postIp;
    
    protected $nrInThread;
    
    /**
     * saves the ThreadModel
     * @var ThreadModel
     */
    protected $thread_cache;
    
    /**
     * saves the ForumModel
     * @var ForumModel
     */
    protected $forum_cache;
    
    public $hiddenChanges = false;
    
    // override string description of attachment links
    protected $stringImageAttachment = 'Bild-Anhang zum Thread-Eintrag';
    protected $stringFileAttachment = 'Datei-Anhang zum Thread-Eintrag';
    
    /**
     * just the basic constructor method
     * 
     * @param string $content_raw - the unparsd entry
     * @param UserModel $author - who write the News
     * @param array $parseSettings - with the parser settings
     * @param int threadId - the id of the thread
     * @param boolean $isAnonymous - will the author shown
     * @param string caption - the caption/title of the news 
     */
    public function __construct($content_raw = null, $author = null, $parseSettings = array (), $threadId = null, $isAnonymous = false, $caption = '') {
        parent :: __construct($content_raw, $author, $parseSettings);
        $this->caption = $caption;

        $this->isAnonymous = $isAnonymous;
        $this->threadId = $threadId;
        $this->postIp = ClientInfos :: getClientIP();
        
    }
    
    /**
     * just build the objekt from on row
     * 
     * @param array $row - the datafile of the news
     * 
     * @return ThreadEntryModel
     */
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->threadId = $row['thread_id'];
        $this->contentRaw = $row['entry_raw'];
        $this->content = $row['entry_parsed'];
        $this->caption = $row['caption'];

        /* it's an internal or external author? */
        //$this->author = UserExternalModel::getUserByIntOrExtId($row['author_int'], $row['author_ext']);
        $this->authorIntId = $row['author_int'];
        $this->authorExtId = $row['author_ext'];
        //var_dump($row['group_id']);
        if ($row['group_id'] != null) {
          $this->authorGroup = GroupModel::getGroupById($row['group_id']);
        }        
        
        $this->postIp = $row['post_ip'];
        $this->timeEntry = $row['entry_time'];
        $this->timeLastUpdate = $row['last_update_time'];
        $this->isAnonymous = Database :: convertPostgresBoolean($row['enable_anonymous']);

        return $this;
    }

    /**
      * Queries the Database to retrieve an array with all ThreadEntryModel.
      * Does not collect information about attachments; rely on already parsed string.
      *
      * @param int $sessionUserId user id of session user
      * @param int $limit show only the first/last $limit entries
      * @param int $offset skip the first/last $offset entries
      * @param string $order $order = ( 'asc' | 'desc' ), sort order ascending or descending
      * @throws DBException on DB error
      *
      * @return array array with EntryModel Objects:
      *
      * @todo optimize DB-query
      * @todo implement filtering
      */
    public static function getAllParsedEntriesByThreadId($threadId = 0, $limit = V_FORUM_THREAD_ENTRIES_PER_PAGE, $offset = 0, $order = 'asc') {
        $DB = Database :: getHandle();

        // set default sort order; also ensure that no sql injection is possible
        if ($order != 'desc' && $order != 'asc')
            $order = 'asc';

        // retrieve entries without attachments
        $q = ' SELECT ' . DB_SCHEMA . '.forum_thread_entries.id AS id, caption, thread_id, post_ip, entry_parsed, entry_raw, 
                                  author_int, author_ext, enable_anonymous, group_id,
                                  extract(epoch FROM entry_time) AS entry_time,
                                  extract(epoch FROM last_update_time) AS last_update_time
                             FROM ' . DB_SCHEMA . '.forum_thread_entries
                            WHERE thread_id = ' . $DB->Quote($threadId);

        $q .= 'ORDER BY ' . DB_SCHEMA . '.forum_thread_entries.entry_time ' . $order . ', id ' . $order . '
                            LIMIT ' . $DB->Quote($limit) . '
                           OFFSET ' . $DB->Quote($offset);
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        // will contain the forum thread entries
        $entries = array ();
        $tempAuthorIds = array();
        $tempAuthorExtIds = array();
        
        
        foreach ($res as $row) {
            $newForumEntry = new ThreadEntryModel();
            $newForumEntry->buildFromRow($row);
            
            if ($row['author_int']!=null) {
                // temporarily save needed user ids to fetch them at once later
                array_push($tempAuthorIds, $row['author_int']);
                $newForumEntry->author       = $row['author_int'];
            } else if ($row['author_ext']!=null) {
                array_push($tempAuthorExtIds, $row['author_ext']);
                $newForumEntry->author       = -$row['author_ext'];
            } else {
                $newForumEntry->author = null;
            }
            $entries[] = $newForumEntry;
        }
        $users = UserProtectedModel::getUsersByIds($tempAuthorIds, '', false);
        $usersExt = UserExternalModel::getUsersByIds($tempAuthorExtIds);
        foreach ($entries as $e) {
            // substitute entry author-id by corresponding user object
            if ($e->author > 0) {
                if (array_key_exists($e->author, $users)) {
                    $e->author = $users[$e->author];
                } else {
                    $e->author = new UserAnonymousModel;
                }
            } else if ($e->author < 0) {
                $e->author = $usersExt[-$e->author];
            } else {
                $e->author = new UserAnonymousModel;
            }
        }
        
        return $entries;
    }
    
    public static function getEntryIdsByThreadId($threadId = 0, $limit = V_FORUM_THREAD_ENTRIES_PER_PAGE, $offset = 0, $order = 'asc') {
        $DB = Database :: getHandle();

        // set default sort order; also ensure that no sql injection is possible
        if ($order != 'desc' && $order != 'asc') {
            $order = 'asc';
        }

        // retrieve entries without attachments
        $q = ' SELECT id
                 FROM ' . DB_SCHEMA . '.forum_thread_entries
                WHERE thread_id = ' . $DB->Quote($threadId);

        $q .= 'ORDER BY entry_time ' . $order . ', id ' . $order . '
                  LIMIT ' . $DB->Quote($limit) . '
                 OFFSET ' . $DB->Quote($offset);
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $entries = array();
        
        foreach ($res as $row) {
            $entries[] = $row['id'];
        }
        
        return $entries;
    }
    
    /**
     * get a ThreadEntry by his id
     * Does not collect information about attachments; rely on already parsed string.
     * 
     * @param int $id
     * @throws DBException on DB error
     * 
     * @return ThreadEntryModel
     * 
     */
    public static function getThreadEntryById($id) {
        $DB = Database :: getHandle();

        // retrieve entries without attachments
        $q = ' SELECT ' . DB_SCHEMA . '.forum_thread_entries.id AS id, caption, thread_id, post_ip, author_int, ' .
                 'author_ext, entry_parsed, entry_raw, enable_anonymous, group_id,
                  extract(epoch FROM entry_time) AS entry_time,
                  extract(epoch FROM last_update_time) AS last_update_time
             FROM ' . DB_SCHEMA . '.forum_thread_entries
            WHERE id=' . $DB->Quote($id);

        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        foreach ($res as $row) {
            $newForumEntry = new ThreadEntryModel();
            $newForumEntry->buildFromRow($row);
            return $newForumEntry;
        }
    }
    
    public static function getThreadEntryByIds($ids) {
        $DB = Database :: getHandle();
        
        // check, if we have ids to work on
        if (count($ids) == 0) {
            return array();
        }
        
        // retrieve entries without attachments
        $q = ' SELECT ' . DB_SCHEMA . '.forum_thread_entries.id AS id, caption, thread_id, post_ip, author_int, ' .
                 'author_ext, entry_parsed, entry_raw, enable_anonymous, group_id,
                  extract(epoch FROM entry_time) AS entry_time,
                  extract(epoch FROM last_update_time) AS last_update_time
             FROM ' . DB_SCHEMA . '.forum_thread_entries
            WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';

        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        return self::buildEntriesFromRes($res);
    }
    
    public static function countEntriesByFilter($filter) {
        $DB = Database::getHandle();
        
        $q = 'SELECT COUNT(*) AS nr
                FROM ' . DB_SCHEMA . '.forum_thread_entries
               WHERE true ' . 
                     $filter->getSQLFilterString();
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        return $res->fields['nr'];
    }
    
    protected static function buildEntriesFromRes($res) {
        // will contain the forum thread entries
        $entries = array ();
        $tempAuthorIds = array();
        $tempAuthorExtIds = array();
        
        foreach ($res as $row) {
            $newForumEntry = new ThreadEntryModel();
            $newForumEntry->buildFromRow($row);
            
            if ($row['author_int']!=null) {
                // temporarily save needed user ids to fetch them at once later
                array_push($tempAuthorIds, $row['author_int']);
                $newForumEntry->author       = $row['author_int'];
            } else if ($row['author_ext']!=null) {
                array_push($tempAuthorExtIds, $row['author_ext']);
                $newForumEntry->author       = -$row['author_ext'];
            } else {
                $newForumEntry->author = null;
                //Logging::getInstance()->logWarning('author unknown');
            }
            $entries[$newForumEntry->id] = $newForumEntry;
        }
        $users = UserProtectedModel::getUsersByIds($tempAuthorIds, '', false);
        $usersExt = UserExternalModel::getUsersByIds($tempAuthorExtIds);
        foreach ($entries as $e) {
            // substitute entry author-id by corresponding user object
            if ($e->author > 0) {
                if (array_key_exists($e->author, $users)) {
                    $e->author = $users[$e->author];
                } else {
                    $e->author = new UserAnonymousModel;
                }
            } else if ($e->author < 0) {
                $e->author = $usersExt[-$e->author];
            } else {
                $e->author = new UserAnonymousModel;
            }
        }
        
        return $entries;
    }
    
    public static function getEntriesByFilter($filter, $limit = 10, $offset = 0) {
        $DB = Database :: getHandle();
        
        // retrieve entries without attachments
        $q = ' SELECT ' . DB_SCHEMA . '.forum_thread_entries.id AS id, caption, thread_id, post_ip, author_int, ' .
                 'author_ext, entry_parsed, entry_raw, enable_anonymous, group_id,
                  extract(epoch FROM entry_time) AS entry_time,
                  extract(epoch FROM last_update_time) AS last_update_time
             FROM ' . DB_SCHEMA . '.forum_thread_entries
            WHERE true ' . 
                     $filter->getSQLFilterString() . '
            ORDER BY entry_time DESC
               LIMIT ' . $DB->Quote($limit) . '
              OFFSET ' . $DB->Quote($offset);
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        return self::buildEntriesFromRes($res);
    }
    
    public static function getThreadEntryByFulltext($query, $limit = 10, $cat = null, $loggedIn = false, $groups = array()) {
        $DB = Database :: getHandle();
        
        // if we have no query string, we can't find any suitable entries ...
        if (trim($query) == '') {
            return array();
        }

        // retrieve entries without attachments
        /*$q = ' SELECT id, caption, thread_id, post_ip, 
                      author_int, author_ext, entry_raw, enable_anonymous, group_id,
                      extract(epoch FROM entry_time) AS entry_time,
                      extract(epoch FROM last_update_time) AS last_update_time,
                      headline(entry_parsed, q,
                      \'MaxWords=75, MinWords=25, StartSel=<strong>,StopSel=</strong>\') AS entry_parsed
                                
                FROM ' . DB_SCHEMA . '.forum_thread_entries, to_tsquery(\'simple\', ' . $DB->quote($query) . ') AS q 
               WHERE idx_fulltext @@ q';*/
        $q = ' SELECT id, caption, thread_id, post_ip, 
                      author_int, author_ext, entry_parsed, entry_raw, enable_anonymous, group_id,
                      extract(epoch FROM entry_time) AS entry_time,
                      extract(epoch FROM last_update_time) AS last_update_time
                FROM ' . DB_SCHEMA . '.forum_thread_entries
               WHERE idx_fulltext @@ to_tsquery(\'simple\', ' . $DB->quote($query) . ')';       
        if ($cat != null){
        	 $sub3 = 'SELECT id FROM ' . DB_SCHEMA . '.forum_categories WHERE category_type = ' . $DB->Quote($cat) . ' ';
             $sub2 = 'SELECT id FROM ' . DB_SCHEMA . '.forum_fora WHERE category_id IN ('.$sub3.') ';
             $sub = 'SELECT id FROM ' . DB_SCHEMA . '.forum_threads WHERE forum_id IN ('.$sub2.') ';
             $q .= ' AND thread_id IN ('.$sub.') ';
        }
        
        // restrict search domain with respect to user's login status and group membership
        if (!$loggedIn) {
            $q .= ' AND thread_id IN (SELECT id 
                                        FROM ' . DB_SCHEMA . '.forum_threads
                                       WHERE forum_id IN (SELECT id 
                                                            FROM ' . DB_SCHEMA . '.forum_fora
                                                           WHERE visible = (SELECT id FROM public.details_visible WHERE name = \'all\')
                                                         )
                                     )';
        } else {
            $q .= ' AND thread_id IN (SELECT id 
                                        FROM ' . DB_SCHEMA . '.forum_threads
                                       WHERE forum_id IN

                                        (SELECT id 
                                           FROM ' . DB_SCHEMA . '.forum_fora f
                                          WHERE visible IN (SELECT id FROM public.details_visible WHERE name = \'all\' OR name = \'logged in\')
                                       UNION ALL
                                         SELECT f.id 
                                           FROM ' . DB_SCHEMA . '.forum_fora f,
                                                ' . DB_SCHEMA . '.groups_forums gf
                                          WHERE f.visible = (SELECT id FROM public.details_visible WHERE name = \'group\')
                                            AND f.id = gf.forum_id
                                            AND gf.group_id IN (' . Database::makeCommaSeparatedString($groups, 'id') . ')
                                        )
                                     )';
        }

        $q .= ' 
             ORDER BY entry_time DESC
                LIMIT ' . $DB->Quote($limit);
        $res = $DB->execute($q);
        //var_dump($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // will contain the forum thread entries
        $entries = array ();
        $tempAuthorIds = array();
        $tempAuthorExtIds = array();
        
        foreach ($res as $row) {
            $newForumEntry = new ThreadEntryModel();
            $newForumEntry->buildFromRow($row);
            
            // highlight search terms
            $_searchTerms = explode(' & ', $query);
            $searchTerms = array();
            foreach ($_searchTerms as $t) {
                // ignore all search terms that start with a ! (negation!)
                if (preg_match('/^\s*!/', $t)) {
                    continue;
                }
                $searchTerms[] = $t;
            }
            
            $newForumEntry->highlight($searchTerms);
            
            if ($row['author_int']!=null) {
                // temporarily save needed user ids to fetch them at once later
                array_push($tempAuthorIds, $row['author_int']);
                $newForumEntry->author       = $row['author_int'];
            } else if ($row['author_ext']!=null) {
                array_push($tempAuthorExtIds, $row['author_ext']);
                $newForumEntry->author       = -$row['author_ext'];
            } else {
                $newForumEntry->author == null;
            }
            $entries[$newForumEntry->id] = $newForumEntry;
        }
        $users = UserProtectedModel::getUsersByIds($tempAuthorIds, '', false);
        $usersExt = UserExternalModel::getUsersByIds($tempAuthorExtIds);
        foreach ($entries as $e) {
            // substitute entry author-id by corresponding user object
            if ($e->author > 0) {
                if (array_key_exists($e->author, $users)) {
                    $e->author = $users[$e->author];
                } else {
                    $e->author = new UserAnonymousModel;
                }
            } else if ($e->author < 0) {
                $e->author = $usersExt[-$e->author];
            }
        }
        
        return $entries;
    }
    
    public function highlight($searchTerms) {
        parent::highlight($searchTerms);
        
        $this->caption = self::highlightTerms($searchTerms, $this->caption);
    }
    
    public function isAnonymous() {
        return $this->isAnonymous;
    }
    
    public function getThreadId() {
        return $this->threadId;
    }
    
    public function getCaption() {
        return $this->caption;
    }
    
    public function getAuthor(){
    	if($this->author == null){
    		$this->author = UserExternalModel::getUserByIntOrExtId($this->authorIntId, $this->authorExtId);
    	}
        if($this->author == null){
            $this->author = new UserAnonymousModel();
        }
        return $this->author;
    }
    
    public function getThread() {
        if ($this->thread_cache != null) {
            return $this->thread_cache;
        }
        if ($this->threadId != null) {
            return $this->thread_cache = ThreadModel::getThreadById($this->threadId);
        }
        return null;
    }
    
    public function getForum() {
        if ($this->forum_cache != null) {
            return $this->forum_cache;
        }
        if ($this->thread_cache != null) {
            return $this->forum_cache = $this->thread_cache->getForum();
        }
        if ($this->threadId != null) {
            $this->thread_cache = ThreadModel :: getThreadById($this->threadId);
            $this->forum_cache = $this->thread_cache->getForum();
            return $this->forum_cache;
        }
        return null;
    }
    
    public function getNrInThread() {
        if($this->nrInThread == null){
            $DB = Database :: getHandle();

            // retrieve entries without attachments
            $q = ' SELECT count(id) as nr
                 FROM ' . DB_SCHEMA . '.forum_thread_entries
                WHERE thread_id=' . $DB->Quote($this->threadId)
              . ' AND id < ' . $DB->quote($this->id);
    
            $res = $DB->execute($q);
            
            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
            $this->nrInThread = $res->fields['nr'] + 1;
            	
        }
        return $this->nrInThread;
    }
    
    public function getPostIP() {
        return $this->postIp;
    }
    
    public function setPostIP($ip) {
        $this->postIp = $ip;
    }


    public function setForum($forum) {
        $this->forum_cache = $forum;
    }
    
    public function setCaption($caption) {
        $this->caption = $caption;
    }
    
    public function setThreadId($id) {
        $this->threadId = $id;
    }
    
    public function setThread(&$thread){
    	$this->thread_cache = $thread;
    }
    
    public function setAnonymous($anon) {
        $this->isAnonymous = $anon;
    }
    
    public function getQuote() {
        if($this->isAnonymous){
        	$username = 'Anonymous';
        }elseif($this->authorGroup){
        	$username = $this->authorGroup->getName();
        }else{
        	$username = $this->getAuthor()->getUsername();
            if($this->getAuthor()->isExternal()){
            	$username .= '@' . $this->getAuthor()->getCityName();
            }
        }
        
        return parent::getQuote($username);
    }

    /**
     * Save the current model to the database 
     */
    public function save() {
        /** test that all necessacry fields are given */
        if ($this->contentRaw == null || $this->author == null || $this->threadId == null){
            var_dump($this->contentRaw); var_dump($this->author); var_dump($this->threadId);
            trigger_error('content, raw content, author or thread is not given...',E_USER_ERROR);
        }

        $keyValue = array ();

        $DB = Database :: getHandle();

        // start transaction for inserting
        $DB->StartTrans();

        // before we save the entry, save the attachments
        // in order to retrieve their ids
        // to embed them in the pre-parsed content
        
        // save array of added attachments
        // for later relationship assignment
        // can't save relationship here, because entry id is unknown
        $attachments = $this->saveAttachmentsToAdd($DB);
        
        // force reparsing of content
        $this->content = null;
        
        $keyValue['last_update_time'] = 'now()';

        /** it's an insert so wie need more data */
        if ($this->id == null) {
            $keyValue['entry_time'] = 'now()';
            $keyValue['thread_id'] = $DB->quote($this->threadId);

            /* save internal or external author */
            if ($this->author->isExternal())
                $keyValue['author_ext'] = $DB->quote($this->author->localId);
            else
                $keyValue['author_int'] = $DB->quote($this->author->id);
        }
        elseif ($this->authorGroup != null){
        	/* save internal or external author */
            if ($this->author->isExternal())
                $keyValue['author_ext'] = $DB->quote($this->author->localId);
            else
                $keyValue['author_int'] = $DB->quote($this->author->id);
        }
        
        /** 
         * Save the post ip also on update
         * 
         * no bussiness logic here! this should do the BussinessLogicObject.
         * Use the class ClientInfo there is a function that return the ip!
         */
        $keyValue['post_ip'] = $DB->quote($this->postIp);

        /** used in all operations */
        $keyValue['entry_raw'] = $DB->quote($this->contentRaw);
        // OPTIMIZEME: update fulltext index only, if raw content has changed?
        if (TSEARCH2_AVAILABLE == 'true') {
            $normalizer = ParserFactory::getRawParser();
            $normalizedString = $this->getCaption() . ' ' . $this->contentRaw;
            $normalizer->parse($normalizedString);
            $keyValue['idx_fulltext'] = 'to_tsvector(\'simple\', ' . $DB->quote($normalizedString) . ')';
        }
        $keyValue['caption'] = $DB->quote($this->caption);
        
        if ($this->authorGroup == null) {
          $keyValue['group_id'] = 'NULL';
        } else if ($this->authorGroup instanceof GroupModel) {
          $keyValue['group_id'] = $DB->quote($this->authorGroup->id);
        } else {
          $keyValue['group_id'] = $DB->quote($this->authorGroup);
        }

        /** new entry -> no last entry message */
        if ($this->id == null) {
        	/* if id!=null show changes */
            $keyValue['entry_parsed'] = $DB->quote($this->parse( false ));
        } else {
            $keyValue['entry_parsed'] = $DB->quote($this->parse( !$this->hiddenChanges ));
            $keyValue['last_update_time'] = 'now()';
        }
        
        // indicate, that we store parsed text to database
        $this->parsedTextNeedsSave = false;
        
        $keyValue['enable_anonymous'] = $DB->quote(Database :: makeBooleanString($this->isAnonymous));
        $keyValue['enable_formatcode'] = $DB->quote(Database :: makeBooleanString($this->isParseAsFormatcode()));
        $keyValue['enable_html'] = $DB->quote(Database :: makeBooleanString($this->isParseAsHTML()));
        $keyValue['enable_smileys'] = $DB->quote(Database :: makeBooleanString($this->isParseAsSmileys()));

        $q = null;

        /** is update? we need the a where clausel */
        if ($this->id != null)
            $q = $this->buildSqlStatement('forum_thread_entries', $keyValue, false, 'id = ' . $DB->quote($this->id));
        else
            $q = $this->buildSqlStatement('forum_thread_entries', $keyValue);

        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'forum_thread_entries','id');
        }
        
        /*****************************
         * further save attachments
         *****************************/
        
        $this->saveAttachmentsRelationshipToAdd($DB, $attachments, 'forum_thread_entries_attachments');
        $this->saveAttachmentsToDelete($DB, 'forum_thread_entries_attachments');               

        // complete transaction for inserting
        $DB->CompleteTrans();

    }

    /**+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    /**                          all  methods which need a EntryModel                                 */
    /**+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

    public function parse($showLastUpdate = true, $addAttachmentLinks = true) {
        // update content
        return $this->content = parent :: parse($showLastUpdate, $addAttachmentLinks);
    }


    /**
     * gets the ParseSettings form db or input
     * include also the settings form the forum
     * 
     * @return array of settings
     */
    public function getParseSettings() {
        if ($this->parseSettings == null) {

            if ($this->id == null) {
                $parseSettings = array ();
                if (array_key_exists(F_ENABLE_FORMATCODE, $_REQUEST)) {
                    $this->parseSettings[BaseEntryModel :: PARSE_AS_FORMATCODE] = true;
                }
                if (array_key_exists(F_ENABLE_SMILEYS, $_REQUEST)) {
                    $this->parseSettings[BaseEntryModel :: PARSE_AS_SMILEYS] = true;
                }
            } else {
                $DB = Database :: getHandle();

                $q = 'SELECT enable_formatcode, enable_html, enable_smileys 
                                        FROM ' . DB_SCHEMA . '.forum_thread_entries 
                                    WHERE id=' . $DB->Quote($this->id);

                $res = $DB->execute($q);
                if (!$res) {
                    throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
                }

                // if no entry with current id can be found, throw exception
                if ($res->EOF) {
                    throw new CoreException($this->getErrorMessage(GENERAL_ARGUMENT_INVALID, $this->id), E_ERROR);
                }

                // initialize parse settings from DB values
                $this->parseSettings = array ();
                if (Database :: convertPostgresBoolean($res->fields['enable_html']))
                    $this->parseSettings[BaseEntryModel :: PARSE_AS_HTML] = true;
                if (Database :: convertPostgresBoolean($res->fields['enable_formatcode']))
                    $this->parseSettings[BaseEntryModel :: PARSE_AS_FORMATCODE] = true;
                if (Database :: convertPostgresBoolean($res->fields['enable_smileys']))
                    $this->parseSettings[BaseEntryModel :: PARSE_AS_SMILEYS] = true;
            }
        }
        /* get information of the forumsettings */
        if($this->threadId != null && array_key_exists('threadId',$_REQUEST)){
            $this->threadId == $_REQUEST['threadId'];
        }
        
        if ($this->getForum() == null) {
            trigger_error('ThreadEntryModel::getParseSettings : Leider können keine globalen Einstellungen geladen werden', E_USER_ERROR);  
        }
                
        //var_dump($this->parseSettings[BaseEntryModel :: PARSE_AS_SMILEYS]);
        //var_dump($this->forum->enableSmileys);        
        if(array_key_exists(BaseEntryModel :: PARSE_AS_FORMATCODE, $this->parseSettings)) {
            $this->parseSettings[BaseEntryModel :: PARSE_AS_FORMATCODE] &= $this->getForum()->hasEnabledFormatcode();
        }
        if(array_key_exists(BaseEntryModel :: PARSE_AS_SMILEYS, $this->parseSettings)) {
            $this->parseSettings[BaseEntryModel :: PARSE_AS_SMILEYS] &= $this->getForum()->hasEnabledSmileys();
        }
        if(array_key_exists(BaseEntryModel :: PARSE_AS_HTML, $this->parseSettings)) {
            $this->parseSettings[BaseEntryModel :: PARSE_AS_HTML] &= $this->getForum()->hasEnabledHTML();
        }
        
        //var_dump($this->parseSettings[BaseEntryModel :: PARSE_AS_SMILEYS]);
    
        return $this->parseSettings;
    }

    /**
      * gives all attachments of this entry
      * 
      * @return array array of EntryAttachmentModel objects
      */
    public function getAttachments() {
        return parent::_getAttachments('forum_thread_entries_attachments');
    }

    public function searchAttachment($inputString = 'file_attachment1', $maxAttachmentSize = null) {
        if ($_FILES[$inputString]['size']) {

            if ($maxAttachmentSize == null)
                $maxAttachmentSize = GlobalSettings :: getGlobalSetting('ENTRY_MAX_ATTACHMENT_SIZE') * 1024;

            // username of session object should not induce security risk
            // this username is contrainted by the database to digits and chars
            $atm = AttachmentHandler :: handleAttachment($_FILES[$inputString], 
                AttachmentHandler::getAdjointPath(Session :: getInstance()->getVisitor()), true, $maxAttachmentSize);
            // add attachment to object
            $this->addAttachment($atm);
        }

    }

    public function searchDelAttachment() {
        // read all attachment ids that are to be deleted
        // these are in POST: delattach<id>
        foreach ($_POST as $key => $val) {
            if (preg_match('/delattach(\d+)/', $key, $matches)) {
                $this->deleteAttachmentById($matches[1]);
            }
        }
    }

    /**
     * adds an attachment to this entry
     * 
     * @param EntryAttachment $attachment attachment to add
     */
    public function addAttachment($attachment) {
        parent :: addAttachment($attachment);
    }

    public function getTimeLastUpdate() {
        return $this->timeLastUpdate;
    }

    public function getContentRaw() {
        return $this->contentRaw;
    }
    
    /**
     * destructor
     * 
     * saves parsed text to database, if has not already been parsed
     */
    public function __destruct() {
        // check, if we have to do save operation
        if ($this->id == null or !$this->parsedTextNeedsSave) {
            return;
        }

        $DB = Database :: getHandle();
        
        $q = $this->buildSqlStatement('forum_thread_entries', array('entry_parsed' => $DB->Quote($this->getContentParsed())),
            false, 'id = ' . $DB->Quote($this->id));
        $res = &$DB->Execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }        
    }
    
    /**
     * deletes a forum entry
     */
    public function delete() {
        $DB = Database::getHandle();
        
        // delete guestbook entry, iff it belongs to model owner
        $q = 'DELETE FROM ' . DB_SCHEMA . '.forum_thread_entries
                    WHERE id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }

}
?>
