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

// $Id: virtual_archive_model.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/files/virtual_archive_model.php $

require_once MODEL_DIR.'/base/base_model.php';
require_once MODEL_DIR.'/base/user_protected_model.php';
require_once MODEL_DIR.'/base/file_model.php';

/**
 * class representing a course
 *
 * @package Models
 * @subpackage Files
 */
class VirtualArchiveModel extends BaseModel {
    protected $author;
    protected $name;
    protected $description;
    protected $insertTime;
    
    protected $files = null;
    protected $filesToAdd = array();
    protected $filesToDelete = array();
    
    /**
     * @param int $id id of virtual archive
     * @param UserModel $author author of archive
     * @param string $name name of the archive
     * @param string $description description of archive
     * @param array $files all files of this archive (array of EntryAttachmentModel)
     * @param string $insertTime time of creation of archive
     */
    public function __construct($id = null, $author = null, $name = null, $description = null, $files = array(), $insertTime = null) {
        $this->id = $id;
        $this->author = $author;
        if($this->author == null){
            $this->author = new UserAnonymousModel();
        }
        $this->name = $name;
        $this->description = $description;
        $this->files = $files;
        $this->insertTime = $insertTime;
    }
    
    /**
     * returns an archive model of the virtual archive specified by id
     * @param int $id id
     * @return VirtualArchiveModel
     */
    public static function getVirtualArchiveById($id) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, description, author_id, 
                     to_char(insert_at, \'DD.MM.YYYY, HH24:MI\') AS pretty_time
                FROM ' . DB_SCHEMA . '.virtual_archives
               WHERE id = ' . $DB->Quote($id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $archive = new VirtualArchiveModel($res->fields['id'],
                                           UserProtectedModel::getUserById($res->fields['author_id']),
                                           $res->fields['name'],
                                           $res->fields['description'],
                                           null,               // no files are added yet
                                           $res->fields['pretty_time']);
        
        return $archive;
    }
    
    /**
     * returns models of archives that the given user has uploaded
     * @param UserModel $author author of files
     * @param BaseFilter $filter filter the results (optional)
     * @return array array of VirtualArchiveModel
     * @throws DBException on DB error
     */
    public static function getVirtualArchivesByAuthor($author, $filter = null) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, description, author_id, 
                     to_char(insert_at, \'DD.MM.YYYY, HH24:MI\') AS pretty_time
                FROM ' . DB_SCHEMA . '.virtual_archives
               WHERE author_id = ' . $DB->Quote($author->id);
        if ($filter != null) {
        	$q .= $filter->getSQLFilterString();
        }
        $q.=' ORDER BY insert_at DESC';
        echo $q;
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $archives = array();
        
        foreach ($res as $row) {
            $archive = new VirtualArchiveModel($row['id'],
                                           $author,
                                           $row['name'],
                                           $row['description'],
                                           null,               // no files are added yet
                                           $row['pretty_time']);
            
            array_push($archives, $archive);
        }        
        
        return $archives;
    }
    
    protected function loadFileData() {
        $DB = Database::getHandle();
        $q = 'SELECT attachment_id
                FROM ' . DB_SCHEMA . '.virtual_archives_attachments
               WHERE archive_id = ' . $DB->Quote($this->id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $idArray = array();
        foreach ($res as $row) {
        	array_push($idArray, $row['attachment_id']);
        }
        
        $this->files = EntryAttachmentModel::getAttachmentsByIds($idArray);
    }    

    /**
     * saves this model to DB
     */
    public function save() {
    	$DB = Database :: getHandle();
        
        // start transaction for inserting
        $DB->StartTrans();

        $keyValue = array();
        if ($this->id == null) {
            $keyValue['author_id'] = $DB->quote($this->author->id);
            $keyValue['name'] = $DB->quote($this->name);
            $keyValue['description'] = $DB->quote($this->description);
        }
        // is update? we need a where clause then    
        if ($this->id != null) {    
            $q = $this->buildSqlStatement('virtual_archives', $keyValue,  false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('virtual_archives', $keyValue);
        }
        // if we have a query to execute
        if ($q) {
            $res = $DB->execute($q);            
            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
        }

        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'virtual_archives','id');
        }

    	/*****************************
         * save files
         *****************************/
        
        foreach ($this->filesToAdd as $fId) {
            // insert relationship between archive and file
            $q = 'INSERT INTO ' . DB_SCHEMA . '.virtual_archives_attachments
                        (archive_id, attachment_id)
                    VALUES
                        (' . $DB->Quote($this->id) . ',
                         ' . $DB->Quote($fId) . ')';
            $res = &$DB->execute( $q );
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
        }
        
        foreach ($this->filesToDelete as $fId) {
            // delete attachment-guestbook-relationship
            $q = 'DELETE FROM ' . DB_SCHEMA . '.virtual_archives_attachments
                        WHERE archive_id = ' . $DB->Quote($this->id) . '
                          AND attachment_id = ' . $DB->Quote($fId);
            $res = &$DB->execute( $q );
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
        }
        
        // complete transaction for inserting
        $DB->CompleteTrans();
    }
    
    private function __get($var) {
        switch ($var) {
        case 'name': return $this->name;
        case 'description': return $this->description;
        case 'insertTime': return $this->insertTime;
        case 'author': return $this->author;
        case 'files': return $this->safeReturn('files', 'loadFileData');
        }
    }
    
    /**
     * adds a file to this archive
     * @param int $id id of file to add
     */
    public function addFileById($id) {
        array_push($this->filesToAdd, $id);
    }
    
    public function deleteFileById($id) {
        array_push($this->filesToDelete, $id);
    }    
}
?>
