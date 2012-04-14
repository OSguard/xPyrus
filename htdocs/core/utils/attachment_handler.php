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

# $Id: attachment_handler.php 5807 2008-04-12 21:23:22Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/attachment_handler.php $

require_once MODEL_DIR . '/base/file_model.php';
require_once CORE_DIR . '/database.php';

/**
 * class for handling file-uploads / attachments
 *
 * @package Utils
 */
class AttachmentHandler {
    /**
     * Moves an uploaded attachment file to its destination.
     *
     * The uploaded file is moved to realpath($targetPrefix.$fileDescription['name']).
     * $targetPrefix can be relative or absolute
     * If $useRandPrefix is true, it is moved to
     * realpath($targetPrefix.$randPrefix.$fileDescription['name']).
     *
     * <b>note:</b> the $fileDescription['name'] will be automatically converted into lowercase
     *
     * @param array $fileDescription description like $_FILE['foo']
     * @param string $targetPrefix prefix/path in filesystem
     * @param boolean $useRandPrefix if true, a random 16 char-prefix plus underscore '_'
     *      is put in front of filename (defaults to true)
     * @param int $maxSize maximum file size in bytes that is allowed (defaults to 102400 <=> 100KB)
     * @param boolean $hashing if true, a hash over uploaded file will be calculated
     *      (defaults to false)
     *
     * @return FileModel object, that describes the attachment; 
     *    returns null, if upload file was rejected
     *
     * @throws CoreException on certain upload failures
     */
    public static function handleAttachment($fileDescription, $targetPrefix, 
                        $useRandPrefix = true, $maxSize = 102400, $hashing = false) {
        
        // check, whether file size exceeds given maximum
        if ($fileDescription['size'] > $maxSize) return null;

        // get absolute path of prefix
        if (realpath($targetPrefix) == false) {
        	throw new CoreException( Logging::getErrorMessage(FILE_FILE_NOT_FOUND, $targetPrefix ) );
        }
        $realTargetPrefix = realpath($targetPrefix) . '/';
        
        // ensure, targetPrefix ends with path separator
        if ($targetPrefix[strlen($targetPrefix)-1] != '/') {
            $targetPrefix .= '/';
        }
    
        // check, whether to use additional random prefix
        if ($useRandPrefix) {
            // use 16 char prefix
            $prefix = self::getPrefix();
            $targetPrefix .= $prefix;
            $realTargetPrefix .= $prefix;
        }
    
        // check, if original filename can safely be used
        if (!self::isSafeFilename($fileDescription['name'])) {
            throw new CoreException( Logging::getErrorMessage(FILE_INVALID_FILENAME, $fileDescription['name']) );
        }
    
        $targetFilename = strtolower($fileDescription['name']);
        // avoid problems with filenames
        $targetFilename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $targetFilename);
        $realFilename = $realTargetPrefix . $targetFilename;
        
        // move uploaded file into destination place
        $result = move_uploaded_file( $fileDescription['tmp_name'], $realFilename);
    
        // was upload successful?
        if (!$result) {
            throw new CoreException( Logging::getErrorMessage(FILE_UPLOAD_FAILED, $fileDescription['tmp_name'], $realFilename ) );
        }
        
        // try to determine type of uploaded file
        $mimeType = self::getMimeType($realFilename);
        $fileType = FileModel::TYPE_MISC;
        if (stripos($mimeType,'image') !== false) {
            $fileType = FileModel::TYPE_IMAGE;
        } elseif (stripos($mimeType,'pdf') !== false) {
            $fileType = FileModel::TYPE_PDF;
        } elseif (stripos($mimeType,'zip') !== false || stripos($mimeType,'rar') !== false) {
            $fileType = FileModel::TYPE_ZIPPED;
        } elseif (stripos($mimeType,'flash') !== false){
        	$fileType = FileModel::TYPE_FLASH;
        } elseif (stripos($mimeType,'msword') !== false){
            $fileType = FileModel::TYPE_MSWORD;
        } elseif (stripos($mimeType,'x-c') !== false){
            $fileType = FileModel::TYPE_SOURCE;
        }
        
        if ($hashing) {
            $hash = sha1_file($realFilename);
        } else {
        	$hash = null;
        }
        
        // create attachment description in form of object
        $attachment = new FileModel (
          null, // id is null by default
          $targetPrefix.$targetFilename,
          $realFilename,
          $fileDescription['size'],
          $fileType,
          $hash
          );
        return $attachment;
    }
    
    protected static function getPrefix() {
        return substr(md5(uniqid(rand(),1)),0,16) . '_';
    }
   
  /**
   * Handles the upload of a user's (self-describing) picture.
   *
   * Ensures that neither image size nor image dimension constraints
   * are violated. Resamples uploaded image into two variants 'big' and 'small'
   *
   * The uploaded file is moved to BASE.$targetPrefix.$fileDescription['name'].
   * If $useRandPrefix is true, it is moved to
   * BASE.$targetPrefix.$randPrefix.$fileDescription['name'].
   *
   * <b>note:</b> the $fileDescription['name'] will be automatically converted into lowercase
   *
   * @param RegisteredUser $user user whom picture is to be given
   * @param array $fileDescription description like $_FILE['foo']
   * @param string $targetPrefix prefix in filesystem
   * @param boolean $useRandPrefix if true, a random 16 char-prefix is put in front of filename
   *      (defaults to true)
   * @param int $maxSize maximum file size in bytes that is allowed (defaults to 102400 <=> 100KB)
   * @param array $maxDimensions associative array with image dimension constraints
   *     it may have the following fields: big_maxwidth,big_maxheight,
   *     small_maxwidth,small_maxheight
   *
   * @return boolean false, if upload was rejected; true otherwise
   *
   * @throws CoreException on certain upload failures
   */
    public static function handleUserPicture( $user, $fileDescription, $targetPrefix, 
                        $useRandPrefix = true, $maxSize = 102400, $maxDimensions = null ) {
        return self::handlePicture("createUserPicture", "removeUserPicture", $user, $fileDescription, $targetPrefix, 
                        $useRandPrefix, $maxSize, $maxDimensions);
    }
  
    public static function handleGroupPicture( $group, $fileDescription, $targetPrefix, 
                        $useRandPrefix = true, $maxSize = 204800, $maxDimensions = null ) {
        return self::handlePicture("createGroupPicture", "removeGroupPicture", $group, $fileDescription, $targetPrefix, 
                        $useRandPrefix, $maxSize, $maxDimensions);
    }
  
    protected static function createUserPicture($user, $realTargetPrefix, $fileName, $tempFileName, $maxDimensions) {
        $targetFilenameBig = '___big___' . strtolower($fileName);
        $targetFilenameTiny = '___tiny___' . strtolower($fileName);
        $targetFilenameFancy = '___fancy___' . strtolower($fileName);
        $targetFilenameSmall = '___small___' . strtolower($fileName);
    
        $newWidth = 0;
        $newHeight = 0;
        // resample to _big_ image, if specified, and move image file into destination
        if (!self::pictureResample($tempFileName, $realTargetPrefix . $targetFilenameBig,
                $newWidth, $newHeight, $maxDimensions['big_maxwidth'], $maxDimensions['big_maxheight'], 'minimize_only')) {
            throw new CoreException( Logging::getErrorMessage(FILE_UPLOAD_FAILED, $tempFileName,
                $realTargetPrefix . $targetFilenameBig ) );
        }
        // resample to _fancy_ image, if specified, and move image file into destination
        if (!self::pictureMakeFancy( $realTargetPrefix . $targetFilenameBig, $realTargetPrefix . $targetFilenameFancy . '.png', 
                $newWidth / $newHeight, $user->getName()) ) {
            throw new CoreException( Logging::getErrorMessage(FILE_UPLOAD_FAILED,$tempFileName,
                                $realTargetPrefix . $targetFilenameFancy . '.png' ) );
        }
        // resample to _small_ image, if specified, and move image file into destination
        if (!self::pictureResample( $tempFileName, $realTargetPrefix . $targetFilenameSmall,
                $newWidth, $newHeight, $maxDimensions['small_maxwidth'], $maxDimensions['small_maxheight'], 'minimize_only')) {
            throw new CoreException( Logging::getErrorMessage(FILE_UPLOAD_FAILED, $tempFileName,
                $realTargetPrefix . $targetFilenameSmall ) );
        }
        // resample to _tiny_ image, if specified, and move image file into destination
        if (!self::pictureResample( $tempFileName, $realTargetPrefix . $targetFilenameTiny,
                $newWidth, $newHeight,  $maxDimensions['tiny_maxwidth'], $maxDimensions['tiny_maxheight'], 'minimize_only')) {
            throw new CoreException( Logging::getErrorMessage(FILE_UPLOAD_FAILED, $tempFileName,
                $realTargetPrefix . $targetFilenameTiny ) );
        }
        
        return $targetFilenameBig;
    }
    
    protected static function createGroupPicture($group, $realTargetPrefix, $fileName, $tempFileName, $maxDimensions) {
        $targetFilenameBig = '___big___' . strtolower($fileName);
        $targetFilenameTiny = '___tiny___' . strtolower($fileName);
    
        $newWidth = 0;
        $newHeight = 0;
        // resample to _big_ image, if specified, and move image file into destination
        if (!self::pictureResample($tempFileName, $realTargetPrefix . $targetFilenameBig,
                $newWidth, $newHeight, $maxDimensions['big_maxwidth'], $maxDimensions['big_maxheight'], 'minimize_only')) {
            throw new CoreException( Logging::getErrorMessage(FILE_UPLOAD_FAILED, $tempFileName,
                $realTargetPrefix . $targetFilenameBig ) );
        }
        // resample to _tiny_ image, if specified, and move image file into destination
        if (!self::pictureResample( $tempFileName, $realTargetPrefix . $targetFilenameTiny,
                $newWidth, $newHeight,  $maxDimensions['tiny_maxwidth'], $maxDimensions['tiny_maxheight'], 'minimize_only')) {
            throw new CoreException( Logging::getErrorMessage(FILE_UPLOAD_FAILED, $tempFileName,
                $realTargetPrefix . $targetFilenameTiny ) );
        }
        
        return $targetFilenameBig;
    }
    
    const SUCCESS = 0;
    const ERROR_SIZE = 1;
    const ERROR_MIME = 2;
    
    protected static function handlePicture($createMethod, $removeMethod, $entity, $fileDescription, $targetPrefix,
            $useRandPrefix = true, $maxSize = 204800, $maxDimensions = null) {
        // check, whether file size exceeds given maximum
        if ($fileDescription['size'] > $maxSize) {
            return self::ERROR_SIZE;
        }
    
        // check, if uploaded file is an image
        if (stripos(self::getMimeType($fileDescription['tmp_name']),'image') === false) {
            return self::ERROR_MIME;
        }
        
        // get absolute path of prefix
        $realTargetPrefix = realpath($targetPrefix) . '/';
    
        // check, whether to use additional random prefix
        if ($useRandPrefix) {
            // use 16 char prefix
            $prefix = self::getPrefix();
            $targetPrefix .= $prefix;
            $realTargetPrefix .= $prefix;
        }
    
        // check, if original filename can safely be used
        if (!self::isSafeFilename($fileDescription['name'])) {
            throw new CoreException( Logging::getErrorMessage(FILE_INVALID_FILENAME, $fileDescription['name']) );
        }
        
        // avoid problems with filenames
        $fileDescription['name'] = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileDescription['name']);
        
        $targetFilename = self::$createMethod($entity, $realTargetPrefix, $fileDescription['name'], $fileDescription['tmp_name'], $maxDimensions);
        
        // after resampling we can delete the uploaded original
        if (is_file($fileDescription['tmp_name'])) {
            unlink($fileDescription['tmp_name']);
        }
    
        // delete old user picture, if it exists
        if ($entity->getPictureFile()) {
            self::$removeMethod($entity);
        }
        
        if (strpos($realTargetPrefix, BASE) !== 0) {
            throw new CoreException( 'picture was not uploaded into DOCUMENT_ROOT' );
        }
        // path for userpic is absolute path from htdocs
        $path = substr($realTargetPrefix . $targetFilename, strlen(BASE));
        // path must begin with a slash
        if ($path[0] != '/') {
            $path = '/' . $path;
        }
        
        $entity->setPictureFile($path);
        
        return self::SUCCESS;
    }
  
  public static function handleNewsPicture( $fileDescription, $targetPrefix, 
                        $useRandPrefix = true, $maxSize = 204800, $maxDimensions = null ) {
    // check, whether file size exceeds given maximum
    if ($fileDescription['size'] > $maxSize) {
        return self::ERROR_SIZE;
    }

    // check, if uploaded file is an image
    if (stripos(self::getMimeType($fileDescription['tmp_name']),'image') === false) {
        return self::ERROR_MIME;
    }
    
    $realTargetPrefix = realpath($targetPrefix) . '/';

    // check, whether to use additional random prefix
    if ($useRandPrefix) {
      // use 16 char prefix
      $prefix = self::getPrefix();
      $targetPrefix .= $prefix;
      $realTargetPrefix .= $prefix;
    }

    // check, if original filename can safely be used
    if (!self::isSafeFilename($fileDescription['name'])) {
      throw new CoreException( Logging::getErrorMessage(FILE_INVALID_FILENAME, $fileDescription['name']) );
    }

    $targetFilename = strtolower($fileDescription['name']);
    $realFilename = $realTargetPrefix . $targetFilename;

    // resample to _big_ image, if specified, and move image file into destination
    if (!self::pictureResample( $fileDescription['tmp_name'], $realFilename,
            $newWidth, $newHeight, $maxDimensions['maxwidth'], $maxDimensions['maxheight'], 'minimize_only')) {

      throw new CoreException( Logging::getErrorMessage(FILE_UPLOAD_FAILED, $fileDescription['tmp_name'],
            $realFilename ) );
    }

    // after resampling we can delete the uploaded original
    if (is_file($fileDescription['tmp_name'])) {
      unlink($fileDescription['tmp_name']);
    }
    
    // create attachment description in form of object
    $attachment = new FileModel (
      null, // id is null by default
      $targetPrefix.$targetFilename,
      $realFilename,
      $fileDescription['size'],
      FileModel::TYPE_IMAGE,
      ''
      );
    return $attachment;
  }

    /**
     * Returns the path of the 'small'-variant of an 'big'-user picture
     *
     * @return string
     */
    public static function getSmallVariantPath($filePath) {
        if ($filePath == '') {
            return '';
        }
        // replace first occurrence of __big__ in filePath with small
        return substr_replace($filePath,'___small___',strpos($filePath,'___big___'),strlen('___big___'));
    }
    /**
     * Returns the path of the 'iny'-variant of an 'big'-user picture
     *
     * @return string
     */
    public static function getTinyVariantPath($filePath) {
        if ($filePath == '') {
            return '';
        }
        // replace first occurrence of __big__ in filePath with tiny
        return substr_replace($filePath,'___tiny___',strpos($filePath,'___big___'),strlen('___big___'));
    }
    
    /**
     * Returns the path of the 'fancy'-variant of an 'big'-user picture
     *
     * @return string
     */
    public static function getFancyVariantPath($filePath) {
        if ($filePath == '') {
            return '';
        }
        // replace first occurrence of __big__ in filePath with fancy
        return substr_replace($filePath,'___fancy___',strpos($filePath,'___big___'),strlen('___big___')) . '.png';
    }

    private static function getMimeType($realFilename) {
        return mime_content_type($realFilename);
    }

    /**
     * checks, if given filename can safely be used
     * in order to avoid any kind of path-traversal like 'dir/../../foo'
     * @param string $filename filename to check
     * @return boolean
     */
    public static function isSafeFilename($filename) {
        // reject too long names
        if (strlen($filename)>127) {
            return false;
        }

        // reject names that contain sequences of dots
        if (strpos($filename,'..') !== false) {
            return false;
        }

        return true;
    }
    
    /**
     * gets the path that all files that belong to given user are to be stored in
     * inside DOCUMENT_ROOT
     * 
     * @param $user UserModel
     * @return string "upload" path of user
     */
    public static function getAdjointPath($user) {
    	return RELATIVE_USERFILE_DIR . '/users/' . $user->id . '/';
    }
    /**
     * alternative version to getAdjointPath use $opjekt->name as url-folder
     * 
     * @param objekt $name - objekt need $name as public attribute
     * @return string "upload" path
     */
    public static function getAdjointPath2($group) {
        return RELATIVE_USERFILE_DIR . '/groups/' . $group->id . '/';
    }
    
    /**
     * gets the path that all files that belong to given user are to be stored in
     * outside DOCUMENT_ROOT
     * 
     * @param $user UserModel
     * @return string general "upload" path of user
     */
    public static function getAdjointGeneralPath($user) {
        return UPLOAD_DIR . '/users/' . $user->id . '/';
    }
    
    public static function getAdjointGeneralPath2($group) {
        return UPLOAD_DIR . '/groups/' . $group->id . '/';
    }
    
    /**
     * marks a given file for safe deletion,
     * i.e. create an entry in DB-attachments_old-tables
     * 
     * @param $path string relative or absolute path
     * @param $uploadTime string time of upload, if known; defaults to now()
     * @return boolean true, if mark was successfull
     */
    public static function markFileForDeletion($path, $uploadTime = null) {
    	if ($uploadTime == null) {
    		$uploadTime = date('Y-m-d');
    	}
    	$DB = Database::getHandle();
        $q = 'INSERT INTO ' . DB_SCHEMA . '.attachments_old
                    (path, upload_time)
               VALUES (' . $DB->Quote($path) . ',
                       ' . $DB->Quote($uploadTime) . ')';
        if (!$DB->execute($q)) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return false;
        }
        return true;
    }

    /**
     * Safely removes user's picture from filesystem and user-object
     * <b>note:</b> the changes to user model are not automatically commited
     * 
     * @param $user UserModel user object whose picture is to be deleted
     */
    public static function removeUserPicture($user) {
    	
    	$DB = Database::getHandle();
        $DB->StartTrans();
        // delete big user picture
        $filePath = BASE . $user->getPictureFile();
        if (!self::markFileForDeletion($filePath)) {
        	$DB->FailTrans();
            return;
        }

        // delete small variant of user picture
        $filePath = BASE . $user->getPictureFile('small');
        if (!self::markFileForDeletion($filePath)) {
            $DB->FailTrans();
            return;
        }
        
        // delete tiny variant of user picture
        $filePath = BASE . $user->getPictureFile('tiny');
        if (!self::markFileForDeletion($filePath)) {
            $DB->FailTrans();
            return;
        }
        
        // delete fancy variant of user picture
        $filePath = BASE . $user->getPictureFile('fancy');
        if (!self::markFileForDeletion($filePath)) {
            $DB->FailTrans();
            return;
        }
        
        $user->setPictureFile('');
        $DB->CompleteTrans();
    }
   
    /**
     * Safely removes group's logo from filesystem and group-object
     * <b>note:</b> the changes to group model are not automatically commited
     * 
     * @param $group GroupModel user object whose picture is to be deleted
     */ 
    public static function removeGroupPicture($group) {
        $DB = Database::getHandle();
        $DB->StartTrans();
        // delete big group picture
        $filePath = BASE . $group->getPictureFile('');
        if (!self::markFileForDeletion($filePath)) {
            $DB->FailTrans();
            return;
        }
        
        // delete tiny variant of user picture
        $filePath = BASE . $group->getPictureFile('tiny');
        if (!self::markFileForDeletion($filePath)) {
            $DB->FailTrans();
            return;
        }

        $group->setPictureFile('');
        $DB->CompleteTrans();
    }
    
    protected static function pictureMakeFancy($smallFile, $fancyFile, $rat, $username) {
    	$tmpFile = tempnam('/tmp',md5(uniqid())) . '.png';
        if (strlen($username) > 18) {
            $username = substr($username,0,17);
            $username .= '...';
        }
        $str = 'convert ' . escapeshellarg($smallFile) . '[0] -fill \'#010101\' -opaque \'#000\' -background \'#000\' -resize 116x116 -rotate 15 -transparent \'#000\' ' . $tmpFile;
        $t1 = -1; $t2 = -1;
        system($str, $t1);
        $offX = 0;
        $offY = 0;

        # adjust ratio, if it gets too big
        $limit = 1.85;
        if ($rat > $limit) {
            $rat = $limit + ($rat - $limit) / 10.0;
        }

        if ($rat < 1.0) {
            $offX = -56.75 * $rat + 72.0;
            $offY = -10.8 * $rat + 25.0;
        } else {
            $offX = 8.0 * $rat + 7.0;
            $offY = 38.0 * $rat - 24.0;
        }
        $str2 = 'convert \( -composite -compose atop -geometry +' . (int)round($offX) . '+' . (int)round($offY) . ' ' . BASE . '/images/foto.png ' . $tmpFile. ' \) -fill \'#196872\' -pointsize 14 -font ' . BASE . '/images/Whoosit.ttf -draw \'rotate 15 translate 55 135 text 0,0 "' . $username . '"\' ' . escapeshellarg($fancyFile);
        system($str2, $t2);
        
        return $t1 == 0 && $t2 == 0;
    }
    
  # pictureResample()
  #
  # written by Andreas 'ads' Scherbaum <ads@go2web24.de>
  # adopted for unihelp-v2 by trehn
  #
  # copy and resample an image
  #
  # parameter:
  #  - filename for picture (giv, jpeg or png)
  #  - new filename for picture
  #  - real new width (OUT)
  #  - real new height (OUT)
  #  - new width (this or height is optional)
  #  - new height (this or width is optional)
  #  - comma separated list with options
  #    - possible options:
  #      - rewritegiftopng
  #      - rewritegiftojpeg
  #      - rewritejpegtopng
  #      - rewritejpegtogif
  #      - rewritepngtogif
  #      - rewritepngtojpeg
  #      - jpeg_quality=<percent quality>
  #
  #      - minimize_only (introduced by trehn)
  # return:
  #  - new image data file
  public static function pictureResample ($file, $targetFile, &$new_width, &$new_height, $width = 0, $height = 0, $options = "") {
    # avoid errors
    if (!is_file($file)) {
      return 0;
    }
    if (!preg_match("/^\d+$/", $width)) {
      throw new ArgumentException('width', $width);
    }
    if (!preg_match("/^\d+$/", $height)) {
      throw new ArgumentException('height', $height);
    }
    $option = array();
    if (strlen($options) > 0) {
      $options = explode(",", $options);
      foreach ($options as $key => $value) {
        if (preg_match("/^([^\=]+)\=([^\=]+)$/", $value, $matches)) {
          $option[strtolower($matches[1])] = $matches[2];
        } else {
          $option[strtolower($value)] = 1;
        }
      }
    }
    # get the image info
    # 0 width
    # 1 height
    # 2  1 = GIF, 2 = JPG, 3 = PNG
    $info = GetImageSize($file);
    # check typ, we only work on gif, jpeg or png
    if ($info[2] == 1) {
      $picture_tmp = ImageCreateFromGIF($file);
    } else if ($info[2] == 2) {
      $picture_tmp = ImageCreateFromJPEG($file);
    } else if ($info[2] == 3) {
      $picture_tmp = ImageCreateFromPNG($file);
    } else {
      return 0;
    }
    # if both 0, copy original image
    if ($width == 0 and $height == 0) {
      $new_width = $info[0];
      $new_height = $info[1];
    } else if ($width == 0) {
      # calculate width
      $percent = 100 - (100 * $height / $info[1]);
      // prevent enlargement, if specified
      if (!$option['minimize_only'] || $percent > 0) {
        $new_width = round($info[0] - ($info[0] * $percent / 100));
        $new_height = round($info[1] - ($info[1] * $percent / 100));
      } else {
        $new_width = $info[0];
        $new_height = $info[1];
      }
    } else if ($height == 0) {
      # calculate height
      $percent = 100 - (100 * $width / $info[0]);
      // prevent enlargement, if specified
      if (!$option['minimize_only'] || $percent > 0) {
        $new_width = round($info[0] - ($info[0] * $percent / 100));
        $new_height = round($info[1] - ($info[1] * $percent / 100));
      } else {
        $new_width = $info[0];
        $new_height = $info[1];
      }
    } else {
      # use the given values
      if ($info[0] >= $info[1]) {
        # more width then height        
        # calculate height
        $percent = 100 - (100 * $width / $info[0]);
        // prevent enlargement, if specified
        if (!$option['minimize_only'] || $percent > 0) {
          $new_width = $width;
          $new_height = round($info[1] - ($info[1] * $percent / 100));
        } else {
          $new_width = $info[0];
          $new_height = $info[1];
        }
      } else {
        # more height then width
        # calculate width
        $percent = 100 - (100 * $height / $info[1]);
        // prevent enlargement, if specified
        if (!$option['minimize_only'] || $percent > 0) {
          $new_height = $height;
          $new_width = round($info[0] - ($info[0] * $percent / 100));
        } else {
          $new_width = $info[0];
          $new_height = $info[1];
        }
      }
    }
  
    # create new image
    $im = imagecreatetruecolor($new_width, $new_height);
    # copy and resample image
    if (function_exists("imagecopyresampled")) {
      // preserve transparency when resample
      imagealphablending($im, false);
      imagecopyresampled($im, $picture_tmp,
                        0, 0,
                        0, 0,
                        $new_width, $new_height,
                        $info[0], $info[1]);
      imagesavealpha($im, true);                  
    } else {
      # this is only a fallback, if the imagecopyresampled() function does not
      # exist in PHP
      imagealphablending($im, false);
      imagecopyresized($im, $picture_tmp,
                      0, 0,
                      0, 0,
                      $new_width, $new_height,
                      $info[0], $info[1]);
      imagesavealpha($im, true);
    }
    # copy image to a file
    $jpeg_quality = (array_key_exists("jpeg_quality", $option) and $option["jpeg_quality"] > 0) ? $option["jpeg_quality"] : 75;
    if ($info[2] == 1) {
      if (array_key_exists("rewritegiftopng", $option) and $option["rewritegiftopng"] == 1) {
        imagepng($im, $targetFile);
      } else if (array_key_exists("rewritegiftojpeg", $option) and $option["rewritegiftojpeg"] == 1) {
        imagejpeg($im, $targetFile, $jpeg_quality);
      } else {
        imagegif($im, $targetFile);
      }
    } else if ($info[2] == 2) {
      if (array_key_exists("rewritejpegtopng", $option) and $option["rewritejpegtopng"] == 1) {
        imagepng($im, $targetFile);
      } else if (array_key_exists("rewritejpegtogif", $option) and $option["rewritejpegtogif"] == 1) {
        imagegif($im, $targetFile);
      } else {
        imagejpeg($im, $targetFile, $jpeg_quality);
      }
    } else if ($info[2] == 3) {
      if (array_key_exists("rewritepngtogif", $option) and $option["rewritepngtogif"] == 1) {
        imagegif($im, $targetFile);
      } else if (array_key_exists("rewritepngtojpeg", $option) and $option["rewritepngtojpeg"] == 1) {
        imagejpeg($im, $targetFile, $jpeg_quality);
      } else {
        imagepng($im, $targetFile);
      }
    }
    # return the new image file
    return $targetFile;
  }
  
    #
    # get the image type string for a file
    #
    # this file is just a wrapper around getimagesize() and returns
    # the image type string instead the number given by getimagesize()
    #
    # written by Andreas 'ads' Scherbaum <ads@unihelp.de>
    #
    # comments:
    #
    # history:
    #
    # get_image_typ_from_file()
    #
    # get the image typ from a file and return the according string
    #
    # parameter:
    #  - filename
    # return:
    #  - image typ or ""
    function get_image_typ_from_file ($file) {
        # avoid errors
        if (!is_file($file)) {
            return "";
        }
        # get the image info
        # 0 width
        # 1 height
        # 2  1 = GIF, 2 = JPG, 3 = PNG
        $info = GetImageSize($file);
        if ($info[0] == 0 and $info[1] == 0) {
            # not an image
            return "";
        }
        if ($info[2] == 1) {
            return "image/gif";
        }
        if ($info[2] == 2) {
            return "image/jpeg";
        }
        if ($info[2] == 3) {
            return "image/png";
        }
        if ($info[2] == 4) {
            return "image/swf";
        }
        if ($info[2] == 5) {
            return "image/psd";
        }
        if ($info[2] == 6) {
            return "image/bmp";
        }
        if ($info[2] == 7) {
            return "image/tiff";
        }
        if ($info[2] == 8) {
            return "image/tiff";
        }
        if ($info[2] == 9) {
            return "image/jpc";
        }
        if ($info[2] == 10) {
            return "image/jp2";
        }
        if ($info[2] == 11) {
            return "image/jpx";
        }
        if ($info[2] == 12) {
            return "image/jb2";
        }
        if ($info[2] == 13) {
            return "image/swc";
        }
        if ($info[2] == 14) {
            return "image/iff";
        }
        return "";
    }
  
}

?>
