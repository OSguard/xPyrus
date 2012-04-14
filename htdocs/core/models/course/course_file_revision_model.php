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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/course/course_file_revision_model.php $

require_once MODEL_DIR . '/base/base_filter.php';
require_once MODEL_DIR . '/base/file_model.php';
require_once MODEL_DIR . '/course/course_model.php';

/**
 * @class CourseFileRevisionModel
 * @brief model of a revision of an (uploaded) course file
 * 
 * @author linap
 * @version $Id: course_file_revision_model.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * The following properties are available from FileModel
 * <ul>
 * <li><var>id</var>                <b>int</b></li>
 * <li><var>filePath</var>          <b>string</b>   (relative) path to file</li>
 * <li><var>fileAbsolutePath</var>  <b>string</b>   absolute path to file</li>
 * <li><var>fileType</var>          <b>string</b>   file type; see class constants in FileModel</li>
 * <li><var>fileSize</var>          <b>int</b>      size of file in kilobytes(!!)</li>
 * <li><var>originalFilename</var>  <b>string</b>   original filename as it was uploaded</li>
 * </ul>
 * 
 * The following properties are available directly from this class:
 * <ul>
 * <li><var>uploadTime</var>        <b>string</b></li>  
 * </ul>
 * 
 * @package Models/Course
 */
class CourseFileRevisionModel extends FileModel {
    public $uploadTime;
    public $courseFileId;
    
    // substring of error message
    const DUPLICATE_FILE = 'duplicate key violates unique constraint "courses_files_revisions"';
    
    /**
     * @param int $id id
     * @param string $path absolute path of file
     */
    public function __construct($id = null, $path = null, $size = null, $type = null, $hash = null) {
        parent::__construct($id, null, $path, $size, $type, $hash);
        $this->courseFileId = null;
    }
    
    /**
     * takes a FileModel and creates a new CourseFileRevisionModel
     * with the same properties; <i>cast to CourseFileRevisionModel</i>
     * @param FileModel $fileModel originate file
     * @return CourseFileModel
     * @throws DBException on DB error
     */
    public static function createFromFileModel($fileModel) {
        $cfModel = new CourseFileRevisionModel($fileModel->id, $fileModel->filePath);
        $cfModel->fileType = $fileModel->fileType;
        $cfModel->hash = $fileModel->hash; 
        $cfModel->fileSize = $fileModel->fileSize;
        $cfModel->filePathAbsolute = $fileModel->filePathAbsolute;
        
        return $cfModel;
    }
    
    /**
     * returns the attachment specified by given id
     *
     * @param int id of attachment to retrieve
     * @return EntryAttachmentModel
     * @throws DBException on database error
     */
    public static function getRevisionById($id) {
        $DB = Database::getHandle();
        
        // if no entry is to fetch, return null
        if (empty($id)) return null;
        
        $q = 'SELECT id, file_id, path, file_size, file_type, hash
                FROM ' . DB_SCHEMA . '.courses_files_revisions
               WHERE id = ' . $DB->Quote($id);
        
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        if ($res->EOF) {
            return null;
        }
        
        $rev = new CourseFileRevisionModel($res->fields['id'], $res->fields['path'], 
            $res->fields['file_size'], $res->fields['file_type'], $res->fields['hash']);
        $rev->courseFileId = $res->fields['file_id']; 
        
        return $rev;
    }
    
    public function save() {
    	$DB = Database::getHandle();
        
        $q = 'INSERT INTO ' . DB_SCHEMA . '.courses_files_revisions
                    (path,file_type,file_size,file_id,hash)
                VALUES
                    (' . $DB->Quote( $this->getFilePathAbsolute() ) . ',
                     ' . $DB->Quote( $this->getType() ) . ',
                     ' . $DB->Quote( $this->getFileSize() ) . ',
                     ' . $DB->Quote( $this->courseFileId ) . ',
                     ' . $DB->Quote( $this->getHash() ) . ')';
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    public function deleteFile() {
        $DB = Database::getHandle();
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.courses_files_revisions
                    WHERE id = ' . $DB->Quote($this->id);
    
        $res = &$DB->execute($q);
        if (!$res) {            
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return;
        }
    }
}

?>
