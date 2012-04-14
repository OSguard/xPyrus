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
/*
 * Created on 01.09.2006 by schnueptus
 * sunburner Unihelp.de
 */
 require_once MODEL_DIR . '/base/base_model.php';
 
 class TagModel extends BaseModel{
    protected $name;
    
    public function __construct($id = null, $name = null){
        $this->id = $id;
        $this->name = $name;
    }
    
    public function getName() {
      return $this->name;
    }
    public function setName($name) {
      $this->name = $name;
    }

    public function save() {
        
        $keyValue = array ();

        $DB = Database :: getHandle();
      
        $keyValue['name'] = $DB->quote($this->name);

        $q = null;

        /** is update? we need the a where clausel */
        if ($this->id != null)
            $q = $this->buildSqlStatement('tag', $keyValue, false, 'id = ' . $DB->quote($this->id));
        else
            $q = $this->buildSqlStatement('tag', $keyValue, true);
        
        //var_dump($q);
        
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }

    public function getAllTags(){
    	$DB = Database :: getHandle();

        $q = 'SELECT * FROM ' . DB_SCHEMA . '.tag ORDER BY name';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        $tags = array();
        foreach($res as $row ){
        	$tag = new TagModel($row['id'],$row['name']);
            $tags[] = $tag; 
        }
        
        return $tags;
    }
    
    public function getTagById($id){
        $DB = Database :: getHandle();

        $q = 'SELECT * FROM ' . DB_SCHEMA . '.tag WHERE id = ' . $DB->quote($id);

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        foreach($res as $row ){
            $tag = new TagModel($row['id'],$row['name']);
            return $tag; 
        }        
    }
    
    public function getTagByStudyPath($id){
        $DB = Database :: getHandle();

        $q = 'SELECT t.* FROM ' . DB_SCHEMA . '.tag AS t, '.DB_SCHEMA .'.study_path_tag AS s
                 WHERE t.id = s.tag_id and s.study_path_id = '. $DB->quote($id) .'
                 ORDER BY t.name';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        $tags = array();
        foreach($res as $row ){
            $tag = new TagModel($row['id'],$row['name']);
            $tags[$row['id']] = $tag; 
        }
        
        return $tags;
    }
    
    public function getTagByForum($id){
        $DB = Database :: getHandle();

        $q = 'SELECT t.* FROM ' . DB_SCHEMA . '.tag AS t, '.DB_SCHEMA .'.forum_tag AS f
                 WHERE t.id = f.tag_id and f.forum_id = '. $DB->quote($id) .'
                 ORDER BY t.name';

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        $tags = array();
        foreach($res as $row ){
            $tag = new TagModel($row['id'],$row['name']);
            $tags[$row['id']] = $tag; 
        }
        
        return $tags;
    }       
    
    public static function setStudyPathTag($studyPathId, $tags) {
        $DB = Database::getHandle();
        
        $DB->StartTrans();
        
        $tagIds = implode(',',$tags);

        /* delete the users that are no more moderators */
        $stmt = 'DELETE FROM '. DB_SCHEMA . '.study_path_tag
                       WHERE study_path_id=' . $DB->quote($studyPathId);                    
        
        $res = $DB->execute($stmt);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        /* we have nothing to insert */
        if(count($tags) == 0) {
            if (!$DB->CompleteTrans()) {
                throw new DBException(DB_TRANSACTION_FAILED);        
            }	
        }

        foreach($tags as $tag){
            $stmt = 'INSERT INTO ' . DB_SCHEMA . '.study_path_tag
                                 (study_path_id, tag_id)
                          VALUES (' . $DB->quote($studyPathId) . ',                                  
                                  ' . $DB->quote($tag) . ')';
            //var_dump($stmt);                        
            $res = $DB->execute($stmt);
        }
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        if(!$DB->CompleteTrans()) {
            throw new DBException(DB_TRANSACTION_FAILED);        
        }
    }
    
  public static function setForumTag($forumId, $tags) {
        $DB = Database::getHandle();
        
        $DB->StartTrans();
        
        $tagIds = implode(',',$tags);

        /* delete the users that are no more moderators */
        $stmt = 'DELETE FROM '. DB_SCHEMA . '.forum_tag
                       WHERE forum_id=' . $DB->quote($forumId);                    
        
        $res = $DB->execute($stmt);
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        /* we have nothing to insert */
        if(count($tags) == 0) {
            if(!$DB->CompleteTrans()) {
                throw new DBException(DB_TRANSACTION_FAILED);        
            }   
            return;
        }
                
        /* add the new users */
        //TODO: eine bessere SQL statment bauen
        foreach($tags as $tag){
            $stmt = 'INSERT INTO ' . DB_SCHEMA . '.forum_tag
                                 (forum_id, tag_id)
                          VALUES (' . $DB->quote($forumId) . ',                                  
                                  ' . $DB->quote($tag) . ')';
            //var_dump($stmt);                        
            $res = $DB->execute($stmt);
        }
        
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        if(!$DB->CompleteTrans()) {
            throw new DBException(DB_TRANSACTION_FAILED);        
        }
    }  
    
    
 }
 
?>
