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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/blog/blog_advanced_trackback_model.php $

require_once MODEL_DIR . '/base/base_model.php';
require_once CORE_DIR . '/utils/client_infos.php';

/**
 * @class BlogAdvancedTrackbackModel
 * @brief model of a trackback on an advanced blog entry
 * 
 * @author linap
 * @version $Id: blog_advanced_trackback_model.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * The following properties are available:
 * <ul>
 * <li><var>id</var>               <b>int</b></li>
 * <li><var>weblogName</var>       <b>string</b> name of original weblog</li>
 * <li><var>weblogURL</var>        <b>string</b> URL of original weblog</li>
 * <li><var>body</var>             <b>string</b> body of trackback</li>
 * <li><var>timeEntry</var>        <b>string</b>    entry_time as unix timestamp</li>
 * <li><var>blogEntry</var>        <b>BlogAdvancedModel</b>    associated blog entry</li>
 * </ul>
 * 
 * @package Models/Blog
 */
class BlogAdvancedTrackbackModel extends BaseModel {
    public $weblogName;
    public $weblogURL;
    public $title;
    public $body;
    public $timeEntry;
    public $blogEntry;
        
    public function __construct($id = null) {
        $this->id = $id;
    }
    
    protected function buildFromRow($row) {
        $this->id = $row['id'];
        $this->weblogName = $row['weblog_name'];
        $this->weblogURL = $row['weblog_url'];
        $this->title = $row['title'];
        $this->body = $row['body'];
        $this->timeEntry = $row['entry_time'];
    }
    
    public static function getTrackbacksByBlogEntry($blogEntry) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, weblog_name, weblog_url, title, body,
                     extract(epoch from entry_time) AS entry_time
                FROM ' . DB_SCHEMA . '.blog_advanced_trackbacks
               WHERE entry_id = ' . $DB->Quote($blogEntry->id) . '
            ORDER BY entry_time ASC';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $trackbacks = array();
        foreach ($res as $k => $row) {
            $trackback = new BlogAdvancedTrackbackModel;
            $trackback->buildFromRow($row);
            $trackback->blogEntry = $blogEntry;
            
            array_push($trackbacks, $trackback);
        }
        
        return $trackbacks;        
    }
    
    public static function getTrackbackById($blogEntry, $trackbackId) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, weblog_name, weblog_url, title, body,
                     extract(epoch from entry_time) AS entry_time
                FROM ' . DB_SCHEMA . '.blog_advanced_trackbacks
               WHERE entry_id = ' . $DB->Quote($blogEntry->id) . '
                 AND id = ' . $DB->Quote($trackbackId);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $trackback = null;
        foreach ($res as $k => $row) {
            $trackback = new BlogAdvancedTrackbackModel;
            $trackback->buildFromRow($row);
            $trackback->blogEntry = $blogEntry;
        }
        
        return $trackback;        
    }
    
    public function save() {
        $DB = Database :: getHandle();
        
        $keyValue = array();

        // it's an insert so we need more data    
        if ($this->id == null) {    
            $keyValue['entry_time'] = 'now()';
            $keyValue['post_ip'] = $DB->quote(ClientInfos::getClientIP());
            $keyValue['weblog_name'] = $DB->quote($this->weblogName);
            $keyValue['weblog_url'] = $DB->quote($this->weblogURL);
            $keyValue['entry_id'] = $DB->quote($this->blogEntry->id);
        }        
                
        // used in all operations
        $keyValue['body'] = $DB->quote($this->body);
        $keyValue['title'] = $DB->quote($this->title);

        $q = null;
        
        // is update? we need a where clausel then    
        if ($this->id != null) {
            // build update statement
            $q = $this->buildSqlStatement('blog_advanced_trackbacks', $keyValue,  false, 'id = ' . $DB->quote($this->id));
        } else {
            // build insert statement
            $q = $this->buildSqlStatement('blog_advanced_trackbacks', $keyValue);
        }
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'blog_advanced_trackbacks','id');
        }
    }
    
    public function delete() {
        $DB = Database::getHandle();
        
        // delete comment
        $q = 'DELETE FROM ' . DB_SCHEMA . '.blog_advanced_trackbacks
                    WHERE id = ' . $DB->Quote($this->id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return;
        }        
    }
}

?>
