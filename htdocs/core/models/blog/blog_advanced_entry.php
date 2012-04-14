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

# $Id: blog_advanced_entry.php 5807 2008-04-12 21:23:22Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/blog/blog_advanced_entry.php $

require_once MODEL_DIR . '/base/base_entry_model.php';
require_once MODEL_DIR . '/base/user_protected_model.php';
require_once MODEL_DIR . '/blog/blog_advanced_category_model.php';
require_once MODEL_DIR . '/blog/blog_advanced_comment_model.php';
require_once MODEL_DIR . '/blog/blog_advanced_trackback_model.php';
require_once CORE_DIR . '/exceptions/core_exception.php';
require_once CORE_DIR . '/utils/client_infos.php';
require_once CORE_DIR . '/interfaces/subscribable.php';

/**
 * represents one entry of user's blog
 *
 * @package Models
 * @subpackge Blog
 */
class BlogAdvancedEntry extends BaseEntryModel implements Subscribable {
	protected $title;
    protected $comments;
    protected $commentsNumber;
    protected $trackback;
    protected $trackbacksNumber;
    protected $categories;
    protected $allowComments;
    
    // override string description of attachment links
    protected $stringImageAttachment = 'Bild-Anhang zum Blog-Eintrag';
    protected $stringFileAttachment = 'Datei-Anhang zum Blog-Eintrag';
    
    public function getTitle() { return $this->title; }
    public function setTitle($title) { $this->title = $title; }
    
    public function isAllowComments() { return $this->allowComments; }
    public function setAllowComments($flag) { $this->allowComments = $flag; }
    
    protected $subscriptors = null;
    
    public function getSubscriptors() { return $this->safeReturn('subscriptors', 'loadSubscriptors'); }
    public function getSubscription($user) { 
        $subscriptors = $this->getSubscriptors();
        if (array_key_exists($user->id, $subscriptors)) {
            return $subscriptors[$user->id]['type'];
        } else {
            return 'none';
        }
    }
    
    public function getCommentsNumber() { return $this->commentsNumber; } 
    public function getTrackbacksNumber() { return $this->trackbacksNumber; }
    
    public function getCategories() {
        return $this->categories;
    }
    public function setCategories($cat) {
        $this->categories = $cat;
    }
    
    public function getComments() {
        if ($this->comments == null) {
            $this->comments = BlogAdvancedCommentModel::getCommentsByBlogEntry($this);
        }
        return $this->comments;
    }
    public function getTrackbacks() {
        if ($this->trackbacks == null) {
            $this->trackbacks = BlogAdvancedTrackbackModel::getTrackbacksByBlogEntry($this);
        }
        return $this->trackbacks;
    }

    public function __construct($content_raw = null, $author = null, $parseSettings = array()) {
        parent::__construct($content_raw, $author, $parseSettings);
    }
    
    /**
     * returns blog entry with specified id
     *
     * @param int $id blog entry id
     * @return BlogEntry blog entry; is null, if no entry with given id is found
     * @throws DBException on database error
     */
    public static function getEntryById($id) {
        $blogEntry = new BlogAdvancedEntry;
        
        $DB = Database::getHandle();
        
        $q = 'SELECT id, user_id, title, entry_parsed,
                     group_id,
                     extract(epoch from entry_time) AS entry_time,
                     comments, trackbacks, allow_comments
                FROM '.DB_SCHEMA.'.blog_advanced
               WHERE id='.$DB->Quote($id);
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // if no entry is found, return null
        if ($res->EOF) {
            return null;
        }
        
        $blogEntry->id               = $res->fields['id'];
        $blogEntry->title            = $res->fields['title'];
        $blogEntry->content          = $res->fields['entry_parsed'];
        $blogEntry->timeEntry        = $res->fields['entry_time'];
        $blogEntry->author           = UserProtectedModel::getUserById($res->fields['user_id']);
        if($blogEntry->author == null){
            $blogEntry->author = new UserAnonymousModel();
        }
        $blogEntry->commentsNumber   = $res->fields['comments'];
        $blogEntry->trackbacksNumber = $res->fields['trackbacks'];
        $blogEntry->categories       = BlogAdvancedCategoryModel::getAllCategoriesByBlogEntry($blogEntry);
        $blogEntry->allowComments    = Database::convertPostgresBoolean($res->fields['allow_comments']);
        if ($res->fields['group_id']) {
            $blogEntry->authorGroup  = GroupModel::getGroupById($res->fields['group_id']);
        }
        
        return $blogEntry;
    }
    
    /**
     * returns an array of blog entry with specified ids
     *
     * @param array $ids array of int: the ids of blog entries to retrieve
     * @return array associative array of BlogAdvancedEntry; keys are entry ids
     * @throws DBException on database error
     */
    public static function getEntriesByIds($ids) {
        $DB = Database::getHandle();
        
        // if no entries are to fetch, return empty array
        if (count($ids)==0) {
            return array();
        }
        
        // to_char(entry_time, \'DD.MM.YYYY, HH24:MI\') AS entry_time,
        $q = 'SELECT id, user_id, group_id, title, entry_parsed,
                     extract(epoch from entry_time) AS entry_time,
                     comments, trackbacks, allow_comments
                FROM ' . DB_SCHEMA . '.blog_advanced
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $entries = array();
        $tempAuthorIds = array();
        $tempGroupIds = array();
        foreach ($res as $k => $row) {
            $blogEntry = new BlogAdvancedEntry;
            $blogEntry->id               = $row['id'];
            $blogEntry->title            = $row['title'];
            $blogEntry->content          = $row['entry_parsed'];
            $blogEntry->timeEntry        = $row['entry_time'];
            $blogEntry->author           = $row['user_id'];
            if ($row['group_id']) {
                $blogEntry->authorGroup  = $row['group_id'];
                // temporarily save needed group ids to fetch them at once later
                array_push($tempGroupIds, $row['group_id']);
            }
            $blogEntry->commentsNumber   = $row['comments'];
            $blogEntry->trackbacksNumber = $row['trackbacks'];
            $blogEntry->allowComments    = Database::convertPostgresBoolean($row['allow_comments']);
            // temporarily save needed user ids to fetch them at once later
            array_push($tempAuthorIds, $row['user_id']);
            
            // put in right position in return array
            $entries[$blogEntry->id] = $blogEntry;
        }
        
        $categories = BlogAdvancedCategoryModel::getAllCategoriesByBlogEntries($entries);
        // retrieve user objects of authors
        $users = UserProtectedModel::getUsersByIds($tempAuthorIds);
        // retrieve group objects of authors
        $groups = GroupModel::getGroupsByIds($tempGroupIds);
        // need to traverse array again to store user/author objects
        foreach ($entries as $e) {
            // substitute entry author-id by corresponding user object
            $e->author = $users[$e->author];
            // substitute entry group-id by corresponding user object
            if ($e->authorGroup) {
                $e->authorGroup = $groups[$e->authorGroup];
            }
            // if there are categories for this entry, append them
            if (array_key_exists($e->id, $categories)) {
                $e->categories = $categories[$e->id];
            }
        }

        return $entries;
    }
    
    protected function loadSubscriptors() {
        if ($this->id == 0) {
            $this->subscriptors = array();
            return;
        }

        $DB = Database::getHandle();
            
        $q = 'SELECT user_id, notification_type
                FROM ' . DB_SCHEMA . '.blog_advanced_subscription
               WHERE entry_id = ' . $DB->Quote($this->id);

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $this->subscriptors = array();
        if ($res->EOF) {
            return;
        }
        
        $uids = array();
        $uidTypes = array();
        foreach ($res as $k => $row) {
            $uids[] = $row['user_id'];
            $uidTypes[$row['user_id']] = $row['notification_type'];
        }
        $users = UserProtectedModel::getUsersByIds($uids);
        
        foreach ($uidTypes as $uid => $type) {
            $this->subscriptors[$uid] = array('user' => $users[$uid], 'type' => $type);
        }
    }
    
    public function addSubscriptor($user, $type) {
        $DB = Database::getHandle();
            
        $q = 'INSERT INTO ' . DB_SCHEMA . '.blog_advanced_subscription
                    (user_id, entry_id, notification_type)
                  VALUES
                    (' . $DB->quote($user->id) . ', ' . $DB->quote($this->id) . ', ' . $DB->quote($type). ')';
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // clear cache
        $this->subscriptors = null;
    }
    
    public function removeSubscriptor($user) {
        $DB = Database::getHandle();
            
        $q = 'DELETE FROM ' . DB_SCHEMA . '.blog_advanced_subscription
                    WHERE user_id = ' . $DB->quote($user->id) . '
                      AND entry_id = ' . $DB->quote($this->id);
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // clear cache
        if ($this->subscriptors !== null) {
            unset($this->subscriptors[$user->id]);
        }
    }
    
    private function getLazyLoadedVariable($var, $dbField) {
        // if variable is not already loaded, fetch now
        if ($this->$var === null) {
            $DB = Database::getHandle();
            
            $q = 'SELECT ' . $dbField . '
                    FROM ' . DB_SCHEMA . '.blog_advanced
                   WHERE id=' . $DB->Quote($this->id);
            
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
            // if no entry with current id can be found, throw exception
            if ($res->EOF) {
                throw new CoreException(Logging::getErrorMessage(GENERAL_ARGUMENT_INVALID, $this->id), E_ERROR);
            }
            $this->$var = array_pop($res->fields);
        }

        return $this->$var;
    }
    
    public function getContentRaw() {
        return $this->getLazyLoadedVariable('contentRaw', 'entry_raw');
    }
    
    public function getTimeLastUpdate() {
        return $this->getLazyLoadedVariable('timeLastUpdate', "extract(epoch from last_update_time) AS entry_time");
    }
    
    /**
     * gives an associative array defining the parse settings
     * the keys are e.g. BaseEntryModel::PARSE_AS_FORMATCODE, BaseEntryModel::PARSE_AS_SMILEYS
     * @return array
     */
    public function getParseSettings() {
        if ($this->parseSettings == null && $this->id != 0) {
            $DB = Database::getHandle();
            
            $q = 'SELECT enable_formatcode, enable_html, enable_smileys
                    FROM ' . DB_SCHEMA . '.blog_advanced
                   WHERE id=' . $DB->Quote($this->id);
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
            
            // if no entry with current id can be found, throw exception
            if ($res->EOF) {
                throw new CoreException($this->getErrorMessage(GENERAL_ARGUMENT_INVALID, $this->id), E_ERROR);
            }
            
            // initialize parse settings from DB values
            $this->parseSettings = array();
            if(Database::convertPostgresBoolean($res->fields['enable_html']))
                $this->parseSettings[BaseEntryModel::PARSE_AS_HTML] = true;
            if(Database::convertPostgresBoolean($res->fields['enable_formatcode']))
                $this->parseSettings[BaseEntryModel::PARSE_AS_FORMATCODE] = true;
            if(Database::convertPostgresBoolean($res->fields['enable_smileys']))
                $this->parseSettings[BaseEntryModel::PARSE_AS_SMILEYS] = true;
        }
        return $this->parseSettings;
    }
    
    /**
     * gives all attachments of this entry
     * 
     * @return array array of EntryAttachmentModel objects
     */
    public function getAttachments() {
        return parent::_getAttachments('blog_advanced_attachments');
    }
    
    public function parse($showLastUpdate = true, $addAttachmentLinks = true) {
        $this->content = parent::parse($showLastUpdate, $addAttachmentLinks);
        return $this->content;
    }
    
    public function getOwner() {
        if ($this->isForGroup()) {
            return $this->getGroup();
        } else {
            return $this->getAuthor();
        }
    }
    
    /**
     * Save the current model to the database 
     */
    public function save() {
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
        
        /*****************************
         * save entry itsself
         *****************************/
        
        $keyValue = array();
        $keyValue['last_update_time'] = 'now()';

        // it's an insert so we need more data    
        if ($this->id == null) {    
            $keyValue['entry_time'] = 'now()';
            $keyValue['user_id'] = $DB->quote($this->author->id);
            $keyValue['post_ip'] = $DB->quote(ClientInfos::getClientIP());
        }
        
        if ($this->authorGroup != null) {
        	$keyValue['group_id'] = $DB->quote($this->getGroupId());
        }
        
        // indicate, that we store parsed text to database
        $this->parsedTextNeedsSave = false;
        
        // used in all operations
        $keyValue['entry_raw'] = $DB->quote($this->getContentRaw());
        $keyValue['entry_parsed'] = $DB->quote($this->getContentParsed());
        $keyValue['title'] = $DB->quote($this->title);
        $keyValue['allow_comments'] = $DB->quote(Database::makeBooleanString($this->allowComments));
        $keyValue['enable_formatcode'] = $DB->quote(Database::makeBooleanString($this->isParseAsFormatcode()));
        $keyValue['enable_html'] = $DB->quote(Database::makeBooleanString($this->isParseAsHTML()));
        $keyValue['enable_smileys'] = $DB->quote(Database::makeBooleanString($this->isParseAsSmileys()));

        $q = null;
        
        // is update? we need a where clausel then    
        if ($this->id != null) {
            // build update statement
            $q = $this->buildSqlStatement('blog_advanced', $keyValue,  false, 'id = ' . $DB->quote($this->id));
        } else {
            // build insert statement
            $q = $this->buildSqlStatement('blog_advanced', $keyValue);
        }
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'blog_advanced','id');
        }
        
        /*****************************
         * save categories
         *****************************/
        // delete all categories of this entry
        $q = 'DELETE FROM ' . DB_SCHEMA . '.blog_advanced_entriescat 
                    WHERE entry_id = ' . $DB->Quote($this->id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        // (re-)insert categories
        foreach ($this->categories as $cat) {
            // insert relationship between category and blog entry
            $q = 'INSERT INTO ' . DB_SCHEMA . '.blog_advanced_entriescat
                        (entry_id, category_id)
                    VALUES
                        ( ' . $DB->Quote($this->id). ',
                          ' . $DB->Quote($cat->id) . ')';
            $res = &$DB->execute( $q );
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
        }
        
        /*****************************
         * further save attachments
         *****************************/
        
        $this->saveAttachmentsRelationshipToAdd($DB, $attachments, 'blog_advanced_attachments');
        $this->saveAttachmentsToDelete($DB, 'blog_advanced_attachments');
        
        // complete transaction for inserting
        $DB->CompleteTrans();
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
        $q = $this->buildSqlStatement('blog_advanced', array('entry_parsed' => $DB->Quote($this->getContentParsed())),
            false, 'id = ' . $DB->Quote($this->id));
        $res = &$DB->Execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    /*public function __get($val) {
    	
        return parent::__get($val);
    }*/
    
    /**
     * deletes a blog entry
     */
    public function delete() {
        $DB = Database::getHandle();
        
        $DB->StartTrans();
        
        // delete entry comments
        $q = 'DELETE FROM ' . DB_SCHEMA . '.blog_advanced_comments
                    WHERE entry_id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            $DB->CompleteTrans();
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return;
        }
        
        // delete entry trackbacks
        $q = 'DELETE FROM ' . DB_SCHEMA . '.blog_advanced_trackbacks
                    WHERE entry_id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            $DB->CompleteTrans();
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return;
        }
        
        // delete entry categories
        $q = 'DELETE FROM ' . DB_SCHEMA . '.blog_advanced_entriescat
                    WHERE entry_id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            $DB->CompleteTrans();
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return;
        }
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.blog_advanced
                    WHERE id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            $DB->CompleteTrans();
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return;
        }
        
        $DB->CompleteTrans();
    }

}

?>
