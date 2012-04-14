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

// $Id: user_blog_advanced_model.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/blog/user_blog_advanced_model.php $

require_once MODEL_DIR . '/blog/blog_advanced_model.php';

/**
 * model that represents a complete blog of an user
 *  
 * @package Models
 * @subpackage Blog
 */
class UserBlogAdvancedModel extends BlogAdvancedModel {
    public static function getBlog($user, $hideInvisible = true) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, title, subtitle, flag_invisible
                FROM ' . DB_SCHEMA . '.blog_advanced_config
               WHERE user_id = ' . $DB->Quote($user->id);
        if ($hideInvisible) {
            $q.= 
               ' AND NOT flag_invisible';
        }
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        if ($res->EOF) {
            return null;
        }
        
        $bm = new UserBlogAdvancedModel($user);
        $bm->id = $res->fields['id'];
        $bm->title = $res->fields['title'];
        $bm->subtitle = $res->fields['subtitle'];
        $bm->isInvisible = Database::convertPostgresBoolean($res->fields['flag_invisible']);
        return $bm;
    }
    
    protected function _saveKey() {
    	return 'user_id';
    }
    
    public function getWhereClause($prefix = '') {
        $DB = Database::getHandle();
        
        $whereClause = null;
        
        if ($prefix != '') {
            $prefix = $prefix . '.';
        }
        
        
        $whereClause = '    ' . $prefix . 'user_id = ' . $DB->Quote($this->owner->id) . '
                        AND ' . $prefix . 'group_id IS NULL ';
        
        return $whereClause;       
    }
    
    public function isAdministrativeAuthority($user) {
    	return $this->owner->equals($user);
    }
}

?>
