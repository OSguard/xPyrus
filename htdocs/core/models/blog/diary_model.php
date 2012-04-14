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

// $Id: diary_model.php 5743 2008-03-25 19:48:14Z ads $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/blog/diary_model.php $

require_once MODEL_DIR . '/blog/diary_entry.php';
require_once MODEL_DIR . '/base/interactive_user_element_model.php';
require_once MODEL_DIR . '/base/entry_attachment_model.php';
require_once CORE_DIR . '/constants/value_constants.php';
require_once CORE_DIR . '/utils/client_infos.php';

/**
 * model that represents the complete diary of one user
 *  
 * @package Models
 * @subpackage Diary
 */
class DiaryModel extends InteractiveUserElementModel {
    protected $user;
    
    public function __construct($user) {
        parent::__construct();
        $this->user = $user;
    }
    
    public function getEntryIds($limit = V_BLOG_ENTRIES_PER_PAGE, $offset = 0, $order = 'desc') {
        $DB = Database::getHandle();
        
        // set default sort order; also ensure that no sql injection is possible
        if ($order!='desc' and $order!='asc') $order='desc';
        
        // collect ids of entries to display
        $q =  'SELECT id
                 FROM '.DB_SCHEMA.'.blog
                WHERE user_id = ' . $DB->Quote($this->user->id);
        $q .= $this->getSQLFilterString();
        
        $q.='ORDER BY entry_time ' . $order . ', id ' . $order . '
                LIMIT '.$DB->Quote($limit).'
               OFFSET '.$DB->Quote($offset);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // collect ids of relevant diary entries
        $entryIds = array();
        foreach ($res as $k => $row) {
            array_push($entryIds, $row['id']);
        }

        return $entryIds;
    }
    
    public function getAllParsedEntries($limit = V_BLOG_ENTRIES_PER_PAGE, $offset = 0, $order = 'desc') {
        return DiaryEntry::getEntriesByIds(self::getEntryIds($limit, $offset, $order));
    }   
    
    /**
     * determines number of diary entries that have been made after
     * specified entry by $this->user or 0, if entry is invalid
     * @param int $entryId
     * @return int
     */
    public function getEntriesAfterEntryId($entryId) {
    	$DB = Database::getHandle();
        
        // collect number of entries that have been made after given entry
        $q =  'SELECT COUNT(id) AS nr
                 FROM '.DB_SCHEMA.'.blog
                WHERE user_id = ' . $DB->Quote($this->user->id) . '
                  AND entry_time > COALESCE((SELECT entry_time 
                                               FROM '.DB_SCHEMA.'.blog
                                              WHERE id = ' . $DB->Quote($entryId) . '
                                                AND user_id = ' . $DB->Quote($this->user->id) . '),
                                             NOW())';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        return $res->fields['nr'];
    } 
        
    /**
     * returns diary entry specified by id
     * @param int $id diary entry id
     * @param boolean $onlyOwn iff true, return only an entry written by $visitingUser
     * @return DiaryEntry
     */
    public function getEntryById($id, $visitingUser, $onlyOwn) {
        $entry = DiaryEntry::getEntryById($id);

        // return entry only, if diary model owner is author of entry
        // or "safety mode" is off
        if ($entry->getAuthor()->equals($visitingUser) or !$onlyOwn) {
            return $entry;
        }
        
        return null;
    }
    
    /**
     * deletes diary entry specified by id
     * @param int $id diary entry id
     * @param boolean $onlyOwn iff true, delete only an entry received by gb's owner
     */
    public function deleteEntryById($id, $onlyOwn) {
        $DB = Database::getHandle();
        
        // delete diary entry, iff it belongs to model owner
        $q = 'DELETE FROM ' . DB_SCHEMA . '.blog
                    WHERE id = ' . $DB->Quote($id);
        if ($onlyOwn) {
            $q .=   ' AND user_id = ' . $DB->Quote($this->user->id);
        }
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    public function getCounter($page=1, $entriesPerPage=V_BLOG_ENTRIES_PER_PAGE) {
        // if no filtering is applied, we can rely on the pre-stored values
        if ($this->filter == null) {
            return parent::getCounter('getDiaryEntries', $page, $entriesPerPage);
        }

        // otherwise a select count is neccessary
        $DB = Database::getHandle();
        
        // determine number of diary entries with filtering settings applied
        $q = ' SELECT COUNT(*) AS nr
                 FROM ' . DB_SCHEMA . '.blog
                WHERE user_id = ' . $DB->Quote($this->user->id);
        // filter results, if neccessary by given criteria
        $q .= $this->getSQLFilterString();

        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $diaryNumber = ceil($res->fields['nr'] / $entriesPerPage);
        return self::nonLinearCounter($diaryNumber,$page);
    }
    
    /**
     * @param BaseFilter
     */
    public static function getEntries($filterBy, $orderBy, $limit = 20) {
    	$DB = Database::getHandle();
        
        $q = ' SELECT attachment_id
                 FROM ' . DB_SCHEMA . '.blog_attachments
                WHERE entry_id IN (SELECT id 
                                     FROM ' . DB_SCHEMA . '.blog
                                    WHERE true
                                         ' . $filterBy->getSQLFilterString() . ' 
                                    LIMIT ' . $DB->Quote($limit). ')';
        echo $q;
        $res = &$DB->execute($q);
        if (!$res) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ));
        }
        //foreach($res as $r) var_dump($r);
        
        $atmIds = array();
        foreach($res as $r) array_push($atmIds, $r['attachment_id']);
        
        return EntryAttachmentModel::getAttachmentsByIds($atmIds);
    }
    
    /**
     * provides an appropiate instance of BaseFilter class
     * to operate on diary entries
     * @param array
     * @return BaseFilter
     */
    public static function getFilterClass($filterOptions) {
    	include_once MODEL_DIR . '/blog/diary_filter.php';
        return new DiaryFilter($filterOptions);
    }
}

?>
