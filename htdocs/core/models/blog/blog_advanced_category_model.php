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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/blog/blog_advanced_category_model.php $

require_once MODEL_DIR . '/base/base_model.php';

/**
 * @class BlogAdvancedCategoryModel
 * @brief model of a category for an advanced blog entry
 * 
 * @author linap
 * @version $Id: blog_advanced_category_model.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * The following properties are available:
 * <ul>
 * <li><var>id</var>                <b>int</b></li>
 * <li><var>name</var>              <b>string</b></li>
 * <li><var>owner</var>             <b>UserModel</b> or <b>GroupModel</b> associated user of category</li>
 * </ul>
 * 
 * @package Models/Blog
 */
class BlogAdvancedCategoryModel extends BaseModel {
    public $name;
    public $owner;
    protected static $categories;

    const DUPLICATE_CATEGORY = 'duplicate key violates unique constraint "blog_advanced_categories_user_id_key"';
    
    public function __construct($id = null, $owner = null, $name = null) {
        $this->id = $id;
        $this->owner = $owner;
        $this->name = $name;
    }
    
    public static function getAllCategoriesByOwner($owner) {
        $hash = substr(get_class($owner),0,2) . $owner->id;
        if (self::$categories === null) {
            self::$categories == array();
        } else if(array_key_exists($hash, self::$categories)) {
            return self::$categories[$hash];
        }
        
        $DB = Database::getHandle();

        $crit = 'user_id';
        if ($owner instanceof GroupModel) {
            $crit = 'group_id';	
        }
        $q = 'SELECT id, name
                FROM ' . DB_SCHEMA . '.blog_advanced_categories
               WHERE ' . $crit . ' = ' . $DB->Quote($owner->id) . '
            ORDER BY name';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $categories = array();
        foreach ($res as $k => $row) {
            $cat = new BlogAdvancedCategoryModel($row['id'], $owner, $row['name']);
            $categories[$cat->id] = $cat;
        }
        
        self::$categories[$hash] = $categories;
        
        return $categories;
    }
    
    public static function getAllCategoriesByBlogEntry($blog) {
        $DB = Database::getHandle();

        $q = 'SELECT bac.id, bac.name
                FROM ' . DB_SCHEMA . '.blog_advanced_categories AS bac,
                     ' . DB_SCHEMA . '.blog_advanced_entriescat AS bae
               WHERE bae.entry_id = ' . $DB->Quote($blog->id) . '
                 AND bae.category_id = bac.id
            ORDER BY name';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $categories = array();
        foreach ($res as $k => $row) {
            $cat = new BlogAdvancedCategoryModel($row['id'], null, $row['name']);
            $categories[$cat->id] = $cat;
        }        
        
        return $categories;
    }
    
    public static function getAllCategoriesByBlogEntries($blogs) {
        $DB = Database::getHandle();
        
        // if no entries are to fetch, return empty array
        if (count($blogs)==0) {
            return array();
        }

        $q = 'SELECT bae.entry_id, bac.id, bac.name
                FROM ' . DB_SCHEMA . '.blog_advanced_categories AS bac,
                     ' . DB_SCHEMA . '.blog_advanced_entriescat AS bae
               WHERE bae.entry_id IN (' . Database::makeCommaSeparatedString($blogs, 'id') . ')
                 AND bae.category_id = bac.id
            ORDER BY bae.entry_id, bac.name';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // build up return array
        $categories = array();
        foreach ($res as $k => $row) {
            if (!array_key_exists($row['entry_id'], $categories)) {
            	$categories[$row['entry_id']] = array();
            }
            array_push($categories[$row['entry_id']], new BlogAdvancedCategoryModel($row['id'], null, $row['name']));
        }
        return $categories;
    }
    
    public function save() {
    	$keyValue = array();

        $DB = Database::getHandle();

        $keyValue['name'] = $DB->quote($this->name);
        // user id must only be set on insert
        if ($this->id == null) {
            // choose the correct owner category
            if ($this->owner instanceof UserModel) {
                $keyValue['user_id'] = $DB->quote($this->owner->id);
            } else if ($this->owner instanceof GroupModel) {
                $keyValue['group_id'] = $DB->quote($this->owner->id);
            }
        }
                
        /** is update? we need the a where clausel */
        if ($this->id != null)
            $q = $this->buildSqlStatement('blog_advanced_categories', $keyValue, false, 'id = ' . $DB->quote($this->id));
        else
            $q = $this->buildSqlStatement('blog_advanced_categories', $keyValue);
            
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // reset internal category cache
        self::$categories = null;
    }
    
    public function delete() {
        $DB = Database::getHandle();
        
        $DB->StartTrans();
        
        // delete relationship between entries and categories
        $q = 'DELETE FROM ' . DB_SCHEMA . '.blog_advanced_entriescat
                    WHERE category_id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            $DB->CompleteTrans();
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return;
        }
        
        // delete categories
        $q = 'DELETE FROM ' . DB_SCHEMA . '.blog_advanced_categories
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
