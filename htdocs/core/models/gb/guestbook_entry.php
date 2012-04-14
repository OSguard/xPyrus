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

# $Id: guestbook_entry.php 5743 2008-03-25 19:48:14Z ads $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/gb/guestbook_entry.php $

require_once MODEL_DIR.'/base/base_entry_model.php';
require_once MODEL_DIR.'/base/user_protected_model.php';
require_once MODEL_DIR.'/base/user_external_model.php';
require_once CORE_DIR.'/exceptions/core_exception.php';
require_once CORE_DIR . '/utils/client_infos.php';

/**
 * represents one entry of user's guestbook
 *
 * @package Models
 * @subpackage GB
 */
class GuestbookEntry extends BaseEntryModel {
    /**
     * @var UserModel
     * the recipient of this guestbook entry
     */
    protected $recipient = null;
    /**
     * @var int
     * the weighting of this guestbook entry
     */
    protected $weighting = null;
    protected $isUnread = null;
    
    protected $comment = null;
    protected $commentTime = null;
    
    // override string description of attachment links
    protected $stringImageAttachment = 'Bild-Anhang zum Gästebuch-Eintrag';
    protected $stringFileAttachment = 'Datei-Anhang zum Gästebuch-Eintrag';
    
    // TODO: move to base entry model class
    protected $postIp;
    
    /**
     * @param string raw content
     * @param UserModel author of the entry
     * @param UserModel recipient of the entry
     * @param array parse settings
     */
    public function __construct($content_raw = null, $author = null, $recipient = null, $parseSettings = array()) {
        parent::__construct($content_raw, $author, $parseSettings);
        $this->recipient = $recipient;
        $this->comment = '';
    }
    
    /**
     * returns guestbook entry with specified id
     *
     * @param int $id guestbook entry id
     * @return GuestbookEntry guestbook entry; is null, if no entry with given id is found
     * @throws DBException on database error
     */
    public static function getEntryById($id) {
        $guestbookEntry = new GuestbookEntry;
        
        $DB = Database::getHandle();
        $q = 'SELECT id, author_int, author_ext, entry_parsed, weighting, is_unread,
                     extract(epoch from entry_time) AS entry_time
                FROM ' . DB_SCHEMA . '.guestbook
               WHERE id=' . $DB->Quote($id);
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // if no entry is found, return null
        if ($res->EOF) {
            return null;
        }
        
        $guestbookEntry->id               = $res->fields['id'];
        $guestbookEntry->content          = $res->fields['entry_parsed'];
        $guestbookEntry->timeEntry        = $res->fields['entry_time'];
        $guestbookEntry->isUnread         = Database::convertPostgresBoolean($res->fields['is_unread']);
        $guestbookEntry->weighting        = $res->fields['weighting'];
        if ($res->fields['author_int']!=null) {
            $guestbookEntry->author       = UserProtectedModel::getUserById($res->fields['author_int']);
        } else if ($res->fields['author_ext']!=null) {
            $guestbookEntry->author       = UserExternalModel::getUserById($res->fields['author_ext']);
        } else {
            $guestbookEntry->author       = null;
        }
        $guestbookEntry->comment          = null;
        
        return $guestbookEntry;
    }
    
    /**
     * returns an array of guestbook entry with specified ids
     * <b>note:</b> the array preserves the order of the given ids
     *
     * @param array $ids array of int: the ids of guestbook entries to retrieve
     * @return array array of GuestbookEntry
     * @throws DBException on database error
     */
    public static function getEntriesByIds($ids, $useAnonymousModel) {
        $DB = Database::getHandle();
        
        // if no entries are to fetch, return empty array
        if (count($ids)==0) {
            return array();
        }
        
        $q = 'SELECT id, author_int, author_ext, entry_parsed, weighting, is_unread,
                     extract(epoch from entry_time) AS entry_time
                FROM ' . DB_SCHEMA . '.guestbook
               WHERE id IN (' . Database::makeCommaSeparatedString($ids) . ')';
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up array to keep order of entries to retrieve
        // orderArray is hash with key [id] and value [position of id in $ids]
        $orderArray = array();
        for ($i=0;$i<count($ids);$i++) {
            $orderArray[$ids[$i]] = $i;
        }

        // build up temporary array out of which return array will be created;
        // pre-fill array with null, so that insertion order of entries does
        // not matter
        $entries = array_fill(0,count($ids),null);
        $tempAuthorIds = array();
        $tempAuthorExtIds = array();
        foreach ($res as $k => $row) {
            $guestbookEntry = new GuestbookEntry;
            $guestbookEntry->id               = $row['id'];
            $guestbookEntry->content          = $row['entry_parsed'];
            $guestbookEntry->timeEntry        = $row['entry_time'];
            $guestbookEntry->isUnread         = Database::convertPostgresBoolean($row['is_unread']);
            $guestbookEntry->weighting        = $row['weighting'];
            $guestbookEntry->comment          = null;
            if ($row['author_int']!=null) {
                // temporarily save needed user ids to fetch them at once later
                array_push($tempAuthorIds, $row['author_int']);
                $guestbookEntry->author       = $row['author_int'];
            } else if ($row['author_ext']!=null) {
                array_push($tempAuthorExtIds, $row['author_ext']);
                // temporarily assign negative id to distinguish later
                $guestbookEntry->author       = -$row['author_ext'];
            } else {
                $guestbookEntry->author       = null;
            }
            
            // put in right position in return array
            $entries[$orderArray[$guestbookEntry->id]] = $guestbookEntry;
        }
        
        // retrieve user objects of authors
        $users = UserProtectedModel::getUsersByIds($tempAuthorIds, '', $useAnonymousModel);
        $usersExt = UserExternalModel::getUsersByIds($tempAuthorExtIds);
        // need to traverse array again to store user/author objects
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
    
    private function getLazyLoadedVariable($var, $dbField) {
        // if variable is not already loaded, fetch now
        if ($this->$var === null) {
            $DB = Database::getHandle();

            $q = "SELECT " . $dbField . "
                    FROM " . DB_SCHEMA . ".guestbook
                   WHERE id=" . $DB->Quote($this->id);
            
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
    
    public function getQuote() {
        return parent::getQuote($this->getAuthor()->getUsername());
    }
    
    public function getTimeLastUpdate() {
        return $this->getLazyLoadedVariable('timeLastUpdate', "extract(epoch from last_update_time)");
    }
    
    public function getPostIP() {
        return $this->getLazyLoadedVariable('postIp', "post_ip");
    }
    
    public function setWeighting($val) {
        $this->weighting = $val;
    }
    
    /**
     * marks the given entries as read in database, but
     * don't update their internal (PHP) status, so that the
     * models will remain in the read status they had before
     * 
     * @param array array of int (GBEntry ids)
     */
    public static function setReadButDontDisplay($entries) {
        if (count($entries) == 0) {
          // nothing to do here
          return 0;
        }
        $DB = Database::getHandle();
        
        $q = 'UPDATE ' . DB_SCHEMA . '.guestbook
                 SET is_unread = false
               WHERE id IN (' . Database::makeCommaSeparatedString($entries) . ')';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        //$DB->CompleteTrans();
        
        //return $unreadEntries;
    }
    
    public function getComment() {
        return $this->getLazyLoadedVariable('comment', 'comment');
    }
    public function getCommentTime() {
        return $this->getLazyLoadedVariable('commentTime', 'extract(epoch from comment_time)');
    }
    
    public function setComment($comment) {
        $this->comment = $comment;
        $this->commentTime = time();
        // force reparsing of content
        $this->content = null;
    }
    
    public function removeComment() {
        $this->comment = '';
        $this->commentTime = '';
        // force reparsing of content
        $this->content = null;
    }
    
    public function getRecipient() {
        if ($this->recipient == null) {
            $this->recipient = UserProtectedModel::getUserById($this->getLazyLoadedVariable('recipient', 'user_id_for'));
        }
        
        return $this->recipient;
    }
    
    /*public function loadForAjax(){
    	$this->Unread = $this->__get('isUnread');
        $this->conntentText = $this->getContentParsed();
        $this->authorName = $this->__get('author')->getUsername();
        $this->weightingValue = $this->getLazyLoadedVariable('weighting', 'weighting');
        $this->entryTime = $this->__get('timeEntry');
    }*/
    
    protected function __get($var) {
        // TESTME
        throw new CoreException ("don't use __get-magic (" . $var . "), you have to use new getter functions instead");
        /*switch ($var) {
        case 'value': return $this->weighting;
        case 'isUnread': return $this->isUnread;
        case 'comment': return $this->getComment();
        case 'recipient': return $this->getRecipient();
        }
        
        return parent::__get($var);*/
    }
    
    public function getValue() { return $this->weighting; }
    public function isUnread() { return $this->isUnread; }
    
    public function getParseSettings() {
        if ($this->parseSettings == null and $this->id != null) {
            $DB = Database::getHandle();
            
            $q = "SELECT enable_formatcode, enable_html, enable_smileys
                    FROM " . DB_SCHEMA . ".guestbook
                WHERE id=" . $DB->Quote($this->id);
            
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
            
            // if no entry with current id can be found, throw exception
            if ($res->EOF) {
                throw new CoreException(Logging::getErrorMessage(GENERAL_ARGUMENT_INVALID, $this->id), E_ERROR);
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
        return parent::_getAttachments('guestbook_attachments');
    }
    
    public function parse($showLastUpdate = true, $addAttachmentLinks = true) {
        $commentString = '';
        // require comment and commentTime to be not empty for adding a comment here
        // if comment is empty string but not null, it will be removed later on
        if ($this->getComment() and $this->getCommentTime()) {
            $commentString = '<p><ins>Kommentar von ' . $this->getRecipient()->getUsername() . ' (' . 
                date('d.m.Y, H:i', $this->getCommentTime()) .
                '): ' . $this->comment . '</ins></p>';
        }
        $this->content = parent::parse($showLastUpdate, $addAttachmentLinks) . $commentString;
        return $this->content;
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
        
        // force reparsing of content
        // commented out by linap, 01.04.07 because of errors with "edit with notice"
        //$this->content = null;
        
        /*****************************
         * save entry itsself
         *****************************/
        
        $keyValue = array();
        $keyValue['last_update_time'] = 'now()';

        // it's an insert so we need more data    
        if ($this->id == null) {    
            $keyValue['entry_time'] = 'now()';
            $keyValue['user_id_for'] = $DB->quote($this->recipient->id);
            if ($this->getAuthor()->isExternal()) {
                $keyValue['author_ext'] = $DB->quote($this->getAuthor()->localId);
            } else {
            	$keyValue['author_int'] = $DB->quote($this->getAuthor()->id);
            }
            $keyValue['post_ip'] = $DB->quote(ClientInfos::getClientIP());
        }
        
        // indicate, that we store parsed text to database
        $this->parsedTextNeedsSave = false;
        
        // used in all operations
        if (ClientInfos::getClientIP() == '141.44.11.21' and $this->weighting > 0) {
            # this host is a bot using more than one username to produce points
            # so we decided to transform all points into minuspoints
            # technical staff meeting: 15 Sep 2007
            $keyValue['weighting'] = $DB->quote($this->weighting * (-1));
        } else {
            $keyValue['weighting'] = $DB->quote($this->weighting);
        }
        $keyValue['entry_raw'] = $DB->quote($this->getContentRaw());
        $keyValue['entry_parsed'] = $DB->quote($this->getContentParsed());
        $keyValue['enable_formatcode'] = $DB->quote(Database::makeBooleanString($this->isParseAsFormatcode()));
        $keyValue['enable_html'] = $DB->quote(Database::makeBooleanString($this->isParseAsHTML()));
        $keyValue['enable_smileys'] = $DB->quote(Database::makeBooleanString($this->isParseAsSmileys()));
        if ($this->comment !== null) {
            $keyValue['comment'] = $DB->quote($this->comment);
        }
        if ($this->commentTime !== null) {
            // on empty comment, also remove comment time
            if ($this->comment != '') {
                $keyValue['comment_time'] = $DB->quote(date('c', $this->commentTime));
            } else {
                $keyValue['comment_time'] = 'NULL';
            }
        }

        $q = null;
        
        // is update? we need a where clausel then    
        if ($this->id != null) {    
            $q = $this->buildSqlStatement('guestbook', $keyValue,  false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('guestbook', $keyValue);
        }
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
        	$this->id = Database::getCurrentSequenceId($DB, 'guestbook','id');
        } 
        
        /*****************************
         * further save attachments
         *****************************/
        
        $this->saveAttachmentsRelationshipToAdd($DB, $attachments, 'guestbook_attachments');
        $this->saveAttachmentsToDelete($DB, 'guestbook_attachments');
        
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
        $q = $this->buildSqlStatement('guestbook', array('entry_parsed' => $DB->Quote($this->getContentParsed())),
            false, 'id = ' . $DB->Quote($this->id));
        $res = &$DB->Execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }        
    }
    
    public static function countEntriesByFilter($filter) {
        $DB = Database::getHandle();
        
        $q = 'SELECT COUNT(*) AS nr
                FROM ' . DB_SCHEMA . '.guestbook
               WHERE true ' . 
                     $filter->getSQLFilterString();
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        return $res->fields['nr'];
    }
    
    public static function getEntriesByFilter($filter, $limit = 10, $offset = 0) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, user_id_for, author_int, author_ext, entry_parsed, weighting, is_unread,
                     extract(epoch from entry_time) AS entry_time
                FROM ' . DB_SCHEMA . '.guestbook
               WHERE true ' . 
                     $filter->getSQLFilterString() . '
            ORDER BY entry_time DESC
               LIMIT ' . $DB->Quote($limit) . '
              OFFSET ' . $DB->Quote($offset);

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up temporary array out of which return array will be created;
        // pre-fill array with null, so that insertion order of entries does
        // not matter
        $entries = array();
        $tempAuthorIds = array();
        $tempAuthorExtIds = array();
        foreach ($res as $k => $row) {
            $guestbookEntry = new GuestbookEntry;
            $guestbookEntry->id               = $row['id'];
            $guestbookEntry->content          = $row['entry_parsed'];
            $guestbookEntry->timeEntry        = $row['entry_time'];
            $guestbookEntry->isUnread         = Database::convertPostgresBoolean($row['is_unread']);
            $guestbookEntry->weighting        = $row['weighting'];
            $guestbookEntry->comment          = null;
            $guestbookEntry->recipient        = $row['user_id_for'];
            array_push($tempAuthorIds, $row['user_id_for']);
            
            if ($row['author_int']!=null) {
                // temporarily save needed user ids to fetch them at once later
                array_push($tempAuthorIds, $row['author_int']);
                $guestbookEntry->author       = $row['author_int'];
            } else if ($row['author_ext']!=null) {
                array_push($tempAuthorExtIds, $row['author_ext']);
                // temporarily assign negative id to distinguish later
                $guestbookEntry->author       = -$row['author_ext'];
            } else {
                $guestbookEntry->author       = null;
            }
            
            // put in right position in return array
            $entries[] = $guestbookEntry;
        }
        
        // retrieve user objects of authors
        $users = UserProtectedModel::getUsersByIds($tempAuthorIds, '', false);
        $usersExt = UserExternalModel::getUsersByIds($tempAuthorExtIds);
        // need to traverse array again to store user/author objects
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
            $e->recipient = $users[$e->recipient];
        }

        return $entries;
    }

}

?>
