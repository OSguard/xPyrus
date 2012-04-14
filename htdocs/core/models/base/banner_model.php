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

require_once MODEL_DIR . '/base/base_model.php';
require_once CORE_DIR . '/utils/client_infos.php';

/**
 * Represent a banner with all it's attributes
 * 
 * @author Kyle, Schnueptus
 * @package Models
 * @copyright Copyright &copy; 2006, Unihelp.de 
 */
 class BannerModel extends BaseModel{
      protected $name;
      protected $attachmentId;
      protected $bannerUrl;
      protected $authorInt;
      protected $authorExt;
      protected $author;
      protected $destURL;
      protected $height;
      protected $width;
      protected $isVisible;
      protected $entryTime;
      protected $lastUpdateTime;
      protected $startDate;
      protected $endDate;
      protected $postIp;
      protected $randomRate;
      protected $bannerViews;
      protected $bannerClicks;
      
      protected $bannerFile;
    
    function __construct($data = null) {
        
        if($data == null){
        	return $this;
        }	
      
       $this->setValues($data);
    }
    
    public function setValues($data){
    	
      if(array_key_exists('id',$data))  
        $this->id = $data['id'];
      if(array_key_exists('name',$data))    
        $this->name = $data['name'];
      if(array_key_exists('attachment_id',$data))    
        $this->attachmentId = $data['attachment_id'];
      if(array_key_exists('banner_url',$data))    
        $this->bannerUrl = $data['banner_url'];  
      if(array_key_exists('author_int',$data))    
        $this->authorInt = $data['author_int'];
      if(array_key_exists('author_ext',$data))  
        $this->authorExt = $data['author_ext'];
      if(array_key_exists('dest_url',$data))  
        $this->destURL = $data['dest_url'];
      if(array_key_exists('height',$data))  
        $this->height = $data['height'];
      if(array_key_exists('width',$data))  
        $this->width = $data['width'];
      if(array_key_exists('is_visible',$data))  
        $this->isVisible = DataBase::convertPostgresBoolean($data['is_visible']);
      if(array_key_exists('entry_time',$data))  
        $this->entryTime  = $data['entry_time'];
      if(array_key_exists('last_update_time',$data))  
        $this->lastUpdateTime = $data['last_update_time'];
      if(array_key_exists('start_date',$data))  
        $this->startDate = $data['start_date'];
      if(array_key_exists('end_date',$data))  
        $this->endDate = $data['end_date'];
      if(array_key_exists('post_ip',$data))  
        $this->postIp = $data['post_ip'];
      if(array_key_exists('random_rate',$data))  
        $this->randomRate = $data['random_rate'];
      if(array_key_exists('banner_views',$data))  
        $this->bannerViews = $data['banner_views'];
      if(array_key_exists('banner_clicks',$data))  
        $this->bannerClicks = $data['banner_clicks']; 
    }
    
    public static function getAllBanners(){
    	$DB = Database::getHandle();
        
        $res = $DB->execute('SELECT * FROM banner ORDER BY id desc');
        
        if(!$res)
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            
        $banners = array();
        
        foreach($res as $row) {
            $banners[] = new BannerModel($row);
        }
        
        return $banners;
    }
    
    public static function getRandomBanner(){
        $DB = Database::getHandle();
        
        $res = $DB->execute('SELECT * FROM ' . DB_SCHEMA . '.get_random_banner()');
        if(!$res)
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            
        return new BannerModel($res->fields);
    }
    
    public static function getBannerById($id) {
        $DB = Database::getHandle();
        
        $res = $DB->execute('SELECT * 
                               FROM ' . DB_SCHEMA . '.banner' . '
                              WHERE id=' . $DB->quote($id));
        
        if(!$res)
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            
        if($res == null)
            return null;
            
        return new BannerModel($res->fields);
    }
    
    /** 
     * add a click to the banner 
     */
    public function addClick() {
        $DB = Database::getHandle();
        
        $stmt = 'UPDATE ' . DB_SCHEMA . '.banner SET banner_clicks = banner_clicks + 1 WHERE id=' . $DB->quote($this->id);
        if (!$DB->execute($stmt)) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }        
    }
    
    public function save() {

        /** test that all necessacry fields are given */
        // TODO: test if Group given
        if ($this->destURL == null ) {
            throw new ArgumentNullException('destURL');
        }
        
        if ($this->randomRate < 0)
            $this->randomRate = 0;
        
        $keyValue = array ();

        $DB = Database :: getHandle();

        // start transaction for inserting
        $DB->StartTrans();
        
        if($this->id == null && $this->bannerFile !=null){
            $atm = $this->bannerFile;
            // insert attachment itsself
            $q = 'INSERT INTO ' . DB_SCHEMA . '.attachments
                                    (path,file_type,file_size,author_id)
                                VALUES
                                    (' . $DB->Quote($atm->getFilePath()) . ',
                                     ' . $DB->Quote($atm->getType()) . ',
                                     ' . $DB->Quote($atm->getFileSize()) . ',
                                     ' . $DB->Quote($this->author->id) . ')';
            $res = & $DB->execute($q);
            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
            
            $keyValue['attachment_id'] = $DB->quote(Database::getCurrentSequenceId($DB, 'attachments','id'));
        }elseif($this->bannerUrl != null){
        	$keyValue['banner_url'] = $DB->quote($this->bannerUrl);
        }
        
        // TODO: right resolution
        $keyValue['width'] = 468;
        $keyValue['height'] = 60;
        
        $keyValue['last_update_time'] = 'now()';

        // it's an insert so we need more data    
        if ($this->id == null) {    
            $keyValue['entry_time'] = 'now()';
            if ($this->getAuthor()->isExternal()) {
                $keyValue['author_ext'] = $DB->quote($this->getAuthor()->localId);
            } else {
                $keyValue['author_int'] = $DB->quote($this->getAuthor()->id);
            }
            $keyValue['post_ip'] = $DB->quote(ClientInfos::getClientIP());
        }
        
        $keyValue['post_ip'] = $DB->quote($this->postIp);
        $keyValue['name'] = $DB->quote($this->name);
        $keyValue['dest_url'] = $DB->quote($this->destURL);
        $keyValue['random_rate'] = $DB->quote($this->randomRate);
        
        if($this->randomRate == 0){
        	$this->isVisible = false;
        }
        
        $keyValue['is_visible'] = $DB->quote(Database :: makeBooleanString($this->isVisible));

        
        //TODO: use Target Date
        $keyValue['start_date'] = Database::getPostgresTimestamp($this->startDate);
        $keyValue['end_date'] = Database::getPostgresTimestamp($this->endDate);

        $q = null;

        /** is update? we need the a where clausel */
        if ($this->id != null)
            $q = $this->buildSqlStatement('banner', $keyValue, false, 'id = ' . $DB->quote($this->id));
        else
            $q = $this->buildSqlStatement('banner', $keyValue);
        
        //var_dump($q);
        
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'banner','id');
        }
        
        $q = 'SELECT ' . DB_SCHEMA . '.update_random_banner('.$this->id.') 
                FROM ' . DB_SCHEMA . '.banner ';
        
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // complete transaction for inserting
        $DB->CompleteTrans();

    }
    
    public function getName(){
    	return $this->name;
        }
    public function getAttachmentId(){      
        return $this->attachmentId;
        }
    public function getAuthorInt(){ 
        return $this->authorInt;
        }
    public function getAuthorExt(){
        return $this->authorExt; 
        }
    public function getDestURL(){
        return $this->destURL; 
        }
    public function getHeight(){
        return $this->height; 
        }
    public function getWidth(){
        return $this->width; 
        }
    public function isVisible(){
        return $this->isVisible; 
        }
    public function isExtern(){
    	return ($this->bannerUrl != null);
    }    
    public function getEntryTime(){
        return $this->entryTime; 
        }
    public function getLastUpdateTime(){
        return $this->lastUpdateTime; 
        }
    public function getStartDate(){ 
        return $this->startDate;
        }
    public function getEndDate(){
        return $this->endDate; 
        }
    public function getPostIp(){
        return $this->postIp; 
        }
    public function getRandomRate(){
        return $this->randomRate; 
        }
    public function getBannerViews(){
        return $this->bannerViews; 
        }
    public function getBannerClicks(){
        return $this->bannerClicks; 
        }
    
    public function getAuthor(){
            if($this->author != null)
                return $this->author;
            if($this->authorInt != null){
                $this->author = UserProtectedModel::getUserById($this->authorInt);   
                if($this->author = null){
                    $this->author = new UserAnonymousModel();
                }
                return $this->author;
            }      
            if($this->authorExt != null)
                return $this->author = UserExternalModel::getUserById($this->authorExt);
            return $this->author = new UserAnonymousModel();        
    } 
    public function getBannerFile(){
            if($this->bannerFile != null)
                return $this->bannerFile;
            if($this->attachmentId != null){
                $ids = array();
                array_push($ids, $this->attachmentId);
                $banners = EntryAttachmentModel::getAttachmentsByIds($ids);                
                return $this->bannerFile = $banners[0];
            }
            return null;        
    }
        
    public function isFlash(){
    	
        /*
         * extern banner can not be flash
         * schnueptus (09.06.2007)
         */
        if($this->bannerUrl != null){
            return false;
        }
        
        $banner = $this->getBannerFile();
        return ($banner->getType() == FileModel::TYPE_FLASH);
    }    
    
    public function getFileName() {
        return $this->getBannerFile()->getFileName();
    }

    public function getFilePath(){
    	
        if($this->bannerUrl != null){
        	return $this->bannerUrl;
        }
        
        $banner = $this->getBannerFile();
        $filePath = $banner->getFilePath();
        if (substr($filePath,0,1) == '.') {
            $filePath = substr($filePath,1);
        }
        return $filePath;
    } 
          
    public function setAuthor($var){
    	$this->author = $var;
    }
        
    public function setBannerFile($bFile){
    	$this->bannerFile = $bFile;
    }
 }
?>
