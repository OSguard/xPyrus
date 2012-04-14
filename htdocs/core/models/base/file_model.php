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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/file_model.php $

require_once MODEL_DIR . '/base/base_model.php';

/**
 * @class FileModel
 * @brief model of a file in the unihelp system
 * 
 * @author linap
 * @version $Id: file_model.php 5743 2008-03-25 19:48:14Z ads $
 * @copyright Copyright &copy; 2006, Unihelp.de
 *
 * The following properties are available:
 * <ul>
 * <li><var>id</var>                <b>int</b></li>
 * <li><var>fileSize</var>          <b>int</b>   file size in bytes</li>
 * <li><var>filePath</var>          <b>string</b>   (relative) path to file</li>
 * <li><var>fileAbsolutePath</var>  <b>string</b>   absolute path to file</li>
 * <li><var>fileType</var>          <b>string</b>   file type; see class constants</li>
 * <li><var>originalFilename</var>  <b>string</b>   original filename as it was uploaded</li>
 * </ul>
 * 
 * @package Models/Base
 */
class FileModel extends BaseModel {
    const TYPE_IMAGE = 'image';
    const TYPE_PDF = 'pdf';
    const TYPE_ZIPPED = 'zip';
    const TYPE_MISC = 'misc';
    const TYPE_MSWORD = 'doc';
    const TYPE_FLASH = 'flash';
    const TYPE_SOURCE_CODE = 'source';
    
    protected $fileSize;
    protected $filePath;
    protected $filePathAbsolute;
    
    /**
     * @var string 
     * type of file, see constants
     */
    protected $fileType;
        
    protected $hash;
    
    /**
     * @var string
     * alternative id, independet of the database id
     */
    protected $tempId;
    public function getTempId() { return $this->tempId; }
    
    /**
     * constructor
     *
     * @param int $id id of attachment
     * @param string $path path to the attachment on the server
     * @param string $absolutePath absolute path to the attachment on the server
     * @param int $size size of the file
     * @param string $type type of the attachment (see class-constants)
     * @param string $hash hash value on attachment
     */
    public function __construct( $id, $path, $absolutePath, $size, $type, $hash ) {
        $this->filePath = $path;
        $this->filePathAbsolute = $absolutePath;
        $this->fileSize = $size;
        $this->fileType = $type;
        $this->id = $id;
        $this->tempId = md5($size . $this->getFileName());
        
        $this->hash = $hash;
    }
    
    /**
    * @return boolean true, if attachment is of image type, false otherwise
    */
    public function isImage() {
        return $this->fileType == EntryAttachmentModel::TYPE_IMAGE;
    }
    
    /**
    * @return string filename of the attachment on the server
    */
    public function getFilePath() {
        return $this->filePath;
    }
    
    /**
    * @return string filename of the attachment on the server
    */
    public function getFilePathAbsolute() {
        if (!$this->filePathAbsolute && $this->filePath) {
            $this->filePathAbsolute = realpath($this->filePath);
        }
        return $this->filePathAbsolute;
    }
    
    /**
    * @param $kb int true, if return value should be in kilobytes (defaults to false)
    * @return int size of the attachment file
    */
    public function getFileSize($kb=false) {
        if ($kb) {
          return ceil($this->fileSize / 1024);
        } else {
          return $this->fileSize;
        }
    }
    
    /**
    * @return string type identifier of file (see class-constants)
    */
    public function getType() {
        return $this->fileType;
    }
    
    /**
    * @return string hash (typically sha1)
    */
    public function getHash() {
        return $this->hash;
    }
    
    /**
     * returns original file name, without prefix and path
     * <b>note:</b>
     * this method assumes, the filename was prefixed by a 16 character
     * string, e.g. 0123456789abcdef_$realFileName 
     * @return string original file name
     */
    public function getFileName() {
    	// first try relative path, then absolute path
    	$path = $this->filePath;
        if (!$path) {
        	$path = $this->filePathAbsolute;
        }
        
        if (!$path) {
        	// don't know, which path this file has
            throw new CoreException(Logging::getErrorMessage(GENERAL_ARGUMENT_MISSING,'path'));
        }

        return substr($path, strrpos($path,'/')+18);
    }  
}

?>
