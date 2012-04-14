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

# $Id: diary_entry.php 5743 2008-03-25 19:48:14Z ads $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/blog/diary_entry.php $

require_once MODEL_DIR.'/base/base_entry_model.php';
require_once MODEL_DIR.'/base/user_protected_model.php';
require_once CORE_DIR.'/exceptions/core_exception.php';
require_once CORE_DIR . '/utils/client_infos.php';

/**
 * represents one entry of user's diary
 *
 * @package Models
 * @subpackge Diary
 */
class DiaryEntry extends BaseEntryModel {
    // override string description of attachment links
    protected $stringImageAttachment = 'Bild-Anhang zum Tagebuch-Eintrag';
    protected $stringFileAttachment = 'Datei-Anhang zum Tagebuch-Eintrag';
    
    public function __construct($content_raw = null, $author = null, $parseSettings = array()) {
        parent::__construct($content_raw, $author, $parseSettings);
    }
    
    /**
     * returns diary entry with specified id
     *
     * @param int $id diary entry id
     * @return DiaryEntry diary entry; is null, if no entry with given id is found
     * @throws DBException on database error
     */
    public static function getEntryById($id) {
        $diaryEntry = new DiaryEntry;
        
        $DB = Database::getHandle();
        
        $q = 'SELECT id, user_id, entry_parsed,
                     extract(epoch from entry_time) AS entry_time
                FROM '.DB_SCHEMA.'.blog
               WHERE id='.$DB->Quote($id);
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // if no entry is found, return null
        if ($res->EOF) {
            return null;
        }
        
        $diaryEntry->id               = $res->fields['id'];
        $diaryEntry->content          = $res->fields['entry_parsed'];
        $diaryEntry->timeEntry        = $res->fields['entry_time'];
        $diaryEntry->author           = UserProtectedModel::getUserById($res->fields['user_id']);
        if ($diaryEntry->author == null){
            $diaryEntry->author = new UserAnonymousModel();
        }
        
        return $diaryEntry;
    }
    
    /**
     * returns an array of diary entry with specified ids
     * <b>note:</b> the array preserves the order of the given ids
     *
     * @param array $ids array of int: the ids of diary entries to retrieve
     * @return array array of DiaryEntry
     * @throws DBException on database error
     */
    public static function getEntriesByIds($ids) {
        $DB = Database::getHandle();
        
        // if no entries are to fetch, return empty array
        if (count($ids)==0) {
            return array();
        }
        
        // create string of quoted IDs to fetch
        $idsString = implode(',', $ids);
        
        $q = 'SELECT id, user_id, entry_parsed,
                     extract(epoch from entry_time) AS entry_time
                FROM ' . DB_SCHEMA . '.blog
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
        foreach ($res as $k => $row) {
            $diaryEntry = new DiaryEntry;
            $diaryEntry->id               = $row['id'];
            $diaryEntry->content          = $row['entry_parsed'];
            $diaryEntry->timeEntry        = $row['entry_time'];
            $diaryEntry->author           = $row['user_id'];
            // temporarily save needed user ids to fetch them at once later
            array_push($tempAuthorIds, $row['user_id']);
            
            // put in right position in return array
            $entries[$orderArray[$diaryEntry->id]] = $diaryEntry;
        }
        
        // retrieve user objects of authors
        $users = UserProtectedModel::getUsersByIds($tempAuthorIds);
        // need to traverse array again to store user/author objects
        foreach ($entries as $e) {
            // substitute entry author-id by corresponding user object
            $e->author = $users[$e->author];
        }

        return $entries;
    }
    
    private function getLazyLoadedVariable($var, $dbField) {
        // if variable is not already loaded, fetch now
        if ($this->$var === null) {
            $DB = Database::getHandle();
            
            $q = 'SELECT ' . $dbField . '
                    FROM ' . DB_SCHEMA . '.blog
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
        return $this->getLazyLoadedVariable('timeLastUpdate', "extract(epoch from last_update_time)");
    }
    
    /**
     * gives an associative array defining the parse settings
     * the keys are e.g. BaseEntryModel::PARSE_AS_FORMATCODE, BaseEntryModel::PARSE_AS_SMILEYS
     * @return array
     */
    public function getParseSettings() {
        if ($this->parseSettings == null and $this->id != null) {
            $DB = Database::getHandle();
            
            $q = 'SELECT enable_formatcode, enable_html, enable_smileys
                    FROM ' . DB_SCHEMA . '.blog
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
    
    public function getAttachments() {
    	return parent::_getAttachments('blog_attachments');
    }
    
    
    public function parse($showLastUpdate = true, $addAttachmentLinks = true) {
        $this->content = parent::parse($showLastUpdate, $addAttachmentLinks);
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
            $keyValue['user_id'] = $DB->quote($this->author->id);
            $keyValue['post_ip'] = $DB->quote(ClientInfos::getClientIP());
        }        
        
        // indicate, that we store parsed text to database
        $this->parsedTextNeedsSave = false;
        
        // used in all operations
        $keyValue['entry_raw'] = $DB->quote($this->getContentRaw());
        $keyValue['entry_parsed'] = $DB->quote($this->getContentParsed());
        $keyValue['enable_formatcode'] = $DB->quote(Database::makeBooleanString($this->isParseAsFormatcode()));
        $keyValue['enable_html'] = $DB->quote(Database::makeBooleanString($this->isParseAsHTML()));
        $keyValue['enable_smileys'] = $DB->quote(Database::makeBooleanString($this->isParseAsSmileys()));

        $q = null;
        
        // is update? we need a where clausel then    
        if ($this->id != null) {
        	// build update statement
            $q = $this->buildSqlStatement('blog', $keyValue,  false, 'id = ' . $DB->quote($this->id));
        } else {
        	// build insert statement
            $q = $this->buildSqlStatement('blog', $keyValue);
        }
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'blog','id');
        }
        
        /*****************************
         * further save attachments
         *****************************/
        
        $this->saveAttachmentsRelationshipToAdd($DB, $attachments, 'blog_attachments');
        $this->saveAttachmentsToDelete($DB, 'blog_attachments');
        
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
        $q = $this->buildSqlStatement('blog', array('entry_parsed' => $DB->Quote($this->getContentParsed())),
            false, 'id = ' . $DB->Quote($this->id));
        $res = &$DB->Execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }        
    }


    protected function __get($var) {
        // TESTME
        throw new CoreException ("don't use __get-magic (" . $var . "), you have to use new getter functions instead");
    }
}

?>
