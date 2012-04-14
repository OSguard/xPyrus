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

// $Id: blog_advanced_model.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/blog/blog_advanced_model.php $

require_once MODEL_DIR . '/blog/blog_advanced_entry.php';
require_once MODEL_DIR . '/blog/blog_advanced_category_model.php';
require_once MODEL_DIR . '/base/interactive_user_element_model.php';
require_once MODEL_DIR . '/base/entry_attachment_model.php';
require_once CORE_DIR . '/constants/value_constants.php';
require_once CORE_DIR . '/utils/client_infos.php';

/**
 * model that represents a complete blog
 *  
 * @package Models
 * @subpackage Blog
 */
abstract class BlogAdvancedModel extends InteractiveUserElementModel {
    protected $owner;
    protected $title;
    protected $subtitle;
    protected $isInvisible;
    
    public function getTitle() { return $this->title; }
    public function setTitle($title) { $this->title = $title; }
    
    public function getSubtitle() { return $this->subtitle; }
    public function setSubtitle($subtitle) { $this->subtitle = $subtitle; }
    
    public function isInvisible() { return $this->isInvisible; }
    public function setInvisible($inv) { $this->isInvisible = $inv; }
    
    public function __construct($owner) {
        parent::__construct();
        $this->owner = $owner;
    }
   
    abstract protected function _saveKey();
    
    public function save() {
    	$keyValue = array();
        
        $DB = Database::getHandle();
        
        // give user_id on INSERT
        if ($this->id == null) {
            $keyValue[$this->_saveKey()] = $DB->quote($this->owner->id);
        }
        
        $keyValue['title'] = $DB->quote($this->title);
        $keyValue['subtitle'] = $DB->quote($this->subtitle);
        $keyValue['flag_invisible'] = $DB->Quote(Database::makeBooleanString($this->isInvisible));

        $q = null;

        /* is update? add extra fields */
        if ($this->id != null) {
            $q = $this->buildSqlStatement('blog_advanced_config', $keyValue, false, 'id=' . $DB->quote($this->id));
        } else {        
            $q = $this->buildSqlStatement('blog_advanced_config', $keyValue);
        }
        
        /* save the category */
        if (!$DB->execute($q)) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'blog_advanced_config', 'id');
        }
    }
    
    public function getOwner() { return $this->owner; }

    /**
     * gives a clause, which can be used in a sql query,
     * that selects entries with respect to owner of the
     * blog model
     * 
     * @oaram string prefix (optional) table prefix
     * 
     * @return string
     */
    abstract public function getWhereClause($prefix = '');
    
    public function getAllParsedEntries($categoryId = null, $limit = V_BLOG_ADVANCED_ENTRIES_PER_PAGE, $offset = 0, $groupByDate = true, $order = 'desc') {
        $DB = Database::getHandle();
        
        // set default sort order; also ensure that no sql injection is possible
        if ($order!='desc' and $order!='asc') $order='desc';
        
        // collect ids of entries to display
        // if categoryId is given, we need to join another table
        if ($categoryId == null) {
            $q =  'SELECT id, EXTRACT(EPOCH FROM DATE_TRUNC(\'day\',entry_time)) AS unixtime
                     FROM '.DB_SCHEMA.'.blog_advanced 
                    WHERE ' . $this->getWhereClause();
        } else {
        	$q =  'SELECT ba.id, EXTRACT(EPOCH FROM DATE_TRUNC(\'day\',entry_time)) AS unixtime
                     FROM '.DB_SCHEMA.'.blog_advanced AS ba,
                          '.DB_SCHEMA.'.blog_advanced_entriescat AS bae
                    WHERE ' . $this->getWhereClause() . '
                      AND bae.entry_id = ba.id
                      AND bae.category_id = ' . $DB->Quote($categoryId);
        }
        $q .= $this->getSQLFilterString();
        
        $q.='ORDER BY entry_time ' . $order . '
                LIMIT '.$DB->Quote($limit).'
               OFFSET '.$DB->Quote($offset);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // collect ids of relevant blog entries
        $entryIds = array();
        $entryDate = array();
        foreach ($res as $k => $row) {
            array_push($entryIds, $row['id']);
            // push entry id to array corresponding to date of entry
            $entryDate[$row['unixtime']][] = $row['id'];
        }

        // build up return array
        $tempEntries = BlogAdvancedEntry::getEntriesByIds($entryIds);
        $entries = array();
        if ($groupByDate) {
            foreach ($entryDate as $date => $ed) {
                $entries[$date] = array();
                // substitute ids by entries
                foreach ($ed as $e) {
                	array_push($entries[$date], $tempEntries[$e]);
                } 
            }
        } else {
        	foreach ($entryDate as $date => $ed) {
                // substitute ids by entries
                foreach ($ed as $e) {
                    array_push($entries, $tempEntries[$e]);
                } 
            }
        }
        //var_dump($entries);
        //return $tempEntries;
        return $entries;
    }
    
   
    public static function getAllParsedEntriesFromAllUsers($limit = V_BLOG_ADVANCED_ENTRIES_PER_PAGE, $offset = 0, $groupByDate = true, $order = 'desc') {
        $DB = Database::getHandle();
        
        // set default sort order; also ensure that no sql injection is possible
        if ($order!='desc' and $order!='asc') $order='desc';
        
        // collect ids of entries to display
        $q =  'SELECT id, EXTRACT(EPOCH FROM DATE_TRUNC(\'day\',entry_time)) AS unixtime
                 FROM '.DB_SCHEMA.'.blog_advanced
                WHERE true 
             ORDER BY entry_time ' . $order . '
                LIMIT '.$DB->Quote($limit).'
               OFFSET '.$DB->Quote($offset);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // collect ids of relevant blog entries
        $entryIds = array();
        $entryDate = array();
        foreach ($res as $k => $row) {
            array_push($entryIds, $row['id']);
            // push entry id to array corresponding to date of entry
            $entryDate[$row['unixtime']][] = $row['id'];
        }

        // build up return array
        $tempEntries = BlogAdvancedEntry::getEntriesByIds($entryIds);
        $entries = array();
        if ($groupByDate) {
            foreach ($entryDate as $date => $ed) {
                $entries[$date] = array();
                // substitute ids by entries
                foreach ($ed as $e) {
                    array_push($entries[$date], $tempEntries[$e]);
                } 
            }
        } else {
            foreach ($entryDate as $date => $ed) {
                // substitute ids by entries
                foreach ($ed as $e) {
                    array_push($entries, $tempEntries[$e]);
                } 
            }
        }
        //var_dump($entries);
        //return $tempEntries;
        return $entries;
    }    
    
    /**
     * returns whether an entry belongs to owner of this blog model
     * @param BlogAdvancedEntryModel
     * @return boolean
     */
    protected function belongsToOwner($entry) {
    	return $this->isAdministrativeAuthority($entry->getAuthor());
    }
    
    abstract public function isAdministrativeAuthority($user);
    
    /**
     * returns blog entry specified by id
     * @param int $id blog entry id
     * @param boolean $onlyOwn iff true, return only an entry written by $visitingUser
     * @return BlogAdvancedEntry
     */
    public function getEntryById($id, $onlyOwn) {
        $entry = BlogAdvancedEntry::getEntryById($id);

        // return entry only, if blog model owner is author of entry
        // or "safety mode" is off
        
        if ($this->belongsToOwner($entry) or !$onlyOwn) {
        	return $entry;
        }
        
        return null;
    }
    
    public function getEntriesNumber($categoryId = null) {
        $DB = Database::getHandle();
        
        if ($categoryId == null) {
            // determine number of blog entries with filtering settings applied
            $q = ' SELECT COUNT(id) AS nr
                     FROM ' . DB_SCHEMA . '.blog_advanced
                    WHERE ' . $this->getWhereClause();
            // filter results, if neccessary by given criteria
            $q .= $this->getSQLFilterString();
        } else {
        	// determine number of blog entries with filtering settings applied
            // and respect to selected category
            $q = ' SELECT COUNT(ba.id) AS nr
                     FROM ' . DB_SCHEMA . '.blog_advanced ba,
                          ' . DB_SCHEMA . '.blog_advanced_entriescat bae
                    WHERE ' . $this->getWhereClause('ba') . '
                      AND ba.id = bae.entry_id
                      AND bae.category_id = ' . $DB->Quote($categoryId);
            // filter results, if neccessary by given criteria
            $q .= $this->getSQLFilterString();
        }
        

        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        return $res->fields['nr'];
    }
    
    /**
     * @param BaseFilter
     */
    public static function getEntries($filterBy, $orderBy, $limit = 20) {
        $DB = Database::getHandle();
        
        $q = ' SELECT attachment_id
                 FROM ' . DB_SCHEMA . '.blog_advanced_attachments
                WHERE entry_id IN (SELECT id 
                                     FROM ' . DB_SCHEMA . '.blog_advanced
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
     * to operate on blog entries
     * @param array
     * @return BaseFilter
     */
    public static function getFilterClass($filterOptions) {
        include_once MODEL_DIR . '/blog/diary_filter.php';
        return new DiaryFilter($filterOptions);
    }
    
    public function getEntryDays($year, $month) {
        $DB = Database::getHandle();
        
        $q = ' SELECT DISTINCT extract(day from entry_time) AS day
                 FROM ' . DB_SCHEMA . '.blog_advanced
                WHERE extract(month from entry_time) = ' . $DB->Quote($month). '
                  AND extract(year from entry_time) = ' . $DB->Quote($year) . '
                  AND ' . $this->getWhereClause();
        $res = &$DB->execute($q);
        if (!$res) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ));
        }
        
        $days = array();        
        foreach ($res as $r) {
        	$days[$r['day']] = true;
        }
        
        return $days;
    }
    
    public function getMinMaxDate() {
        $DB = Database::getHandle();
        
        $q = ' SELECT extract(epoch from min(entry_time)) AS min,
                      extract(epoch from max(entry_time)) AS max
                 FROM ' . DB_SCHEMA . '.blog_advanced
                WHERE ' . $this->getWhereClause();
        $res = &$DB->execute($q);
        if (!$res) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ));
        }
        return $res->fields;
    }
    
    /**
     * gives all categories that are available within this
     * blog model
     * @return array array of BlogAdvancedCategoryModel
     */
    public function getCategories() { 
        return BlogAdvancedCategoryModel::getAllCategoriesByOwner($this->owner);
    }
}

?>
