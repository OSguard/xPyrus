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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/blog/blog_advanced_comment_model.php $

require_once MODEL_DIR . '/base/base_model.php';
require_once MODEL_DIR . '/base/user_external_model.php';
require_once CORE_DIR . '/utils/client_infos.php';

/**
 * @class BlogAdvancedCommentModel
 * @brief model of a comment on an advanced blog entry
 * 
 * @author linap
 * @version $Id: blog_advanced_comment_model.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * The following properties are available:
 * <ul>
 * <li><var>id</var>               <b>int</b></li>
 * <li><var>authorUnihelp</var>    <b>UserModel</b> null, if author was anonymous</li>
 * <li><var>authorName</var>       <b>string</b></li>
 * <li><var>email</var>            <b>string</b>    author's email address</li>
 * <li><var>comment</var>          <b>string</b></li>
 * <li><var>timeEntry</var>        <b>string</b>    entry_time as unix timestamp</li>
 * <li><var>blogEntry</var>        <b>BlogAdvancedModel</b>    associated blog entry</li>
 * </ul>
 * 
 * @package Models/Blog
 */
class BlogAdvancedCommentModel extends BaseModel {
    public $authorUnihelp;
    public $authorName;
    public $comment;
    public $email;
    public $timeEntry;
    public $blogEntry;
        
    public function __construct($id = null) {
        $this->id = $id;
    }
    
    protected function buildFromRow($row) {
    	$this->id = $row['id'];
        $this->authorName = $row['author_name'];
        $this->comment = $row['comment'];
        $this->email = $row['email'];
        $this->timeEntry = $row['entry_time'];
    }
    
    public function getComment() {
        $content = $this->comment;
        $ps = ParserFactory::createParserFromSettings(array(BaseEntryModel::PARSE_AS_SMILEYS => true));
        foreach ($ps as $p) {
            $content = $p->parse($content);
        }
        return $content;
    }
    
    public static function getCommentsByBlogEntry($blogEntry, $useAnonymousModel = true) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, author_int, author_ext, author_name,
                     comment, extract(epoch from entry_time) AS entry_time,
                     email
                FROM ' . DB_SCHEMA . '.blog_advanced_comments
               WHERE entry_id = ' . $DB->Quote($blogEntry->id) . '
            ORDER BY entry_time ASC';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $comments = array();
        $tempAuthorIds = array();
        $tempAuthorExtIds = array();
        foreach ($res as $k => $row) {
            $com = new BlogAdvancedCommentModel;
            $com->buildFromRow($row);
            
            if ($row['author_int']!=null) {
                // temporarily save needed user ids to fetch them at once later
                array_push($tempAuthorIds, $row['author_int']);
                $com->authorUnihelp       = $row['author_int'];
            } else if ($row['author_ext']!=null) {
                array_push($tempAuthorExtIds, $row['author_ext']);
                // temporarily assign negative id to distinguish later
                $com->authorUnihelp       = -$row['author_ext'];
            }
            
            $com->blogEntry = $blogEntry;
            
            array_push($comments, $com);
        }
        
                // retrieve user objects of authors
        $users = UserProtectedModel::getUsersByIds($tempAuthorIds, '', $useAnonymousModel);
        $usersExt = UserExternalModel::getUsersByIds($tempAuthorExtIds);
        // need to traverse array again to store user/author objects
        foreach ($comments as $e) {
            // substitute entry author-id by corresponding user object
            if ($e->authorUnihelp > 0) {
                if (array_key_exists($e->authorUnihelp, $users)) {
                    $e->authorUnihelp = $users[$e->authorUnihelp];
                } else {
                    $e->authorUnihelp = new UserAnonymousModel;
                }
            } else if ($e->authorUnihelp < 0) {
                $e->authorUnihelp = $usersExt[-$e->authorUnihelp];
            }
        }
        
        return $comments;
    }
    
    public static function getLatestComments($blog, $limit = V_BLOG_ADVANCED_ENTRIES_PER_FEED, $useAnonymousModel = true) {
        $DB = Database::getHandle();
        
        $modelClause = ' true ';
        if ($blog != null) {
            $modelClause = ' entry_id IN (SELECT id FROM  ' . DB_SCHEMA . '.blog_advanced WHERE ' . $blog->getWhereClause() . ') ';
        }
        
        $q = 'SELECT id, author_int, author_ext, author_name,
                     comment, extract(epoch from entry_time) AS entry_time,
                     email, entry_id
                FROM ' . DB_SCHEMA . '.blog_advanced_comments
               WHERE ' . $modelClause . ' 
            ORDER BY entry_time DESC
               LIMIT ' . (int) $limit;
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $comments = array();
        $tempAuthorIds = array();
        $tempAuthorExtIds = array();
        $tempEntries = array();
        foreach ($res as $k => $row) {
            $com = new BlogAdvancedCommentModel;
            $com->buildFromRow($row);
            
            if ($row['author_int']!=null) {
                // temporarily save needed user ids to fetch them at once later
                array_push($tempAuthorIds, $row['author_int']);
                $com->authorUnihelp       = $row['author_int'];
            } else if ($row['author_ext']!=null) {
                array_push($tempAuthorExtIds, $row['author_ext']);
                // temporarily assign negative id to distinguish later
                $com->authorUnihelp       = -$row['author_ext'];
            }
            
            $tempEntries[] = $row['entry_id'];
            $com->blogEntry = $row['entry_id'];
            
            array_push($comments, $com);
        }
        
        // TODO: lazy loading of entry models? (linap, 01.09.2007)
        //       perhaps not neccessary because the only place right now
        //       where this method is called, the entries are always needed (comment RSS feed)
        
        // retrieve user objects of authors
        $users = UserProtectedModel::getUsersByIds($tempAuthorIds, '', $useAnonymousModel);
        $usersExt = UserExternalModel::getUsersByIds($tempAuthorExtIds);
        $entries = BlogAdvancedEntry::getEntriesByIds($tempEntries);
        // need to traverse array again to store user/author objects
        foreach ($comments as $e) {
            // substitute entry author-id by corresponding user object
            if ($e->authorUnihelp > 0) {
                if (array_key_exists($e->authorUnihelp, $users)) {
                    $e->authorUnihelp = $users[$e->authorUnihelp];
                } else {
                    $e->authorUnihelp = new UserAnonymousModel;
                }
            } else if ($e->authorUnihelp < 0) {
                $e->authorUnihelp = $usersExt[-$e->authorUnihelp];
            }
            $e->blogEntry = $entries[$e->blogEntry];
        }
        
        return $comments;
    }
    
    public static function getCommentById($blogEntry, $commentId) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, author_int, author_ext, author_name,
                     comment, extract(epoch from entry_time) AS entry_time,
                     email
                FROM ' . DB_SCHEMA . '.blog_advanced_comments
               WHERE entry_id = ' . $DB->Quote($blogEntry->id) . '
                 AND id = ' . $DB->Quote($commentId);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $com = null;
        foreach ($res as $k => $row) {
            $com = new BlogAdvancedCommentModel;
            $com->buildFromRow($row);
            
            $com->authorUnihelp       = UserExternalModel::getUserByIntOrExtId($row['author_int'], $row['author_ext']);            
            $com->blogEntry = $blogEntry;
        }
                
        return $com;
    }
    
    public function save() {
    	$DB = Database :: getHandle();
        
        $keyValue = array();

        // it's an insert so we need more data    
        if ($this->id == null) {    
            $keyValue['entry_time'] = 'now()';
            if ($this->authorUnihelp != null and $this->authorUnihelp->isExternal()) {
                $keyValue['author_ext'] = $DB->quote($this->authorUnihelp->localId);
            } else if ($this->authorUnihelp != null) {
            	$keyValue['author_int'] = $DB->quote($this->authorUnihelp->id);
            }
            $keyValue['post_ip'] = $DB->quote(ClientInfos::getClientIP());
            $keyValue['author_name'] = $DB->quote($this->authorName);
            $keyValue['entry_id'] = $DB->quote($this->blogEntry->id);
        }        
                
        // used in all operations
        $keyValue['comment'] = $DB->quote($this->comment);
        $keyValue['email'] = $DB->quote($this->email);

        $q = null;
        
        // is update? we need a where clausel then    
        if ($this->id != null) {
            // build update statement
            $q = $this->buildSqlStatement('blog_advanced_comments', $keyValue,  false, 'id = ' . $DB->quote($this->id));
        } else {
            // build insert statement
            $q = $this->buildSqlStatement('blog_advanced_comments', $keyValue);
        }
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'blog_advanced_comments','id');
        }
    }
    
    public function delete() {
        $DB = Database::getHandle();
        
        // delete comment
        $q = 'DELETE FROM ' . DB_SCHEMA . '.blog_advanced_comments
                    WHERE id = ' . $DB->Quote($this->id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return;
        }        
    }
}
?>
