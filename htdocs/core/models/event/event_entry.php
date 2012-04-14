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
 
require_once MODEL_DIR . '/base/base_entry_model.php';
 
/**
 * @class EventEntryModel
 */
class EventEntryModel extends BaseEntryModel {
  
   private $authorIntId;
   private $groupId;
   private $categoryId;
   private $startDate;
   private $endDate;
   private $visible;
   private $caption;
   public $postIp;
   
   // override string description of attachment links
   protected $stringImageAttachment = 'Bild-Anhang zum Kalender-Eintrag';
   protected $stringFileAttachment = 'Datei-Anhang zum Kalender-Eintrag';
   
   public function __construct($entryRaw = null, $autor = null, $caption = null, $parseSettings = array()) {
        parent :: __construct($entryRaw, $autor, $parseSettings = array());
        
        $this->caption = $caption;
   }
   
   protected function buildFromRow($row) {
        $this->id = $row['id'];        
        $this->contentRaw = $row['entry_raw'];
        $this->content = $row['entry_parsed'];
        $this->caption = $row['caption'];

        /** no external author are supported for news composing */
        $this->authorIntId = $row['author_int'];
        $this->groupId = $row['group_id'];
        $this->categoryId = $row['category_id'];
        
        $this->postIp = $row['post_ip'];
        $this->timeEntry = $row['entry_time'];
        $this->timeLastUpdate = $row['last_update_time'];   
        
        $this->startDate =$row['start_date'];     
        $this->endDate =$row['end_date'];     
        
        $this->visible=DetailsVisibleModel::getDetailsVisibleById($row['visible']);
        
        return $this;
    }
    
    public static function getAllEvents($start='CURRENT_DATE', $interval=28, $order = 'asc', $orderByDate = true) {
        $DB = Database :: getHandle();

        // set default sort order; also ensure that no sql injection is possible
        if ($order != 'desc' && $order != 'asc')
            $order = 'asc';
        //TODO: welche anderen typen gibt es hier?
        if ($start!='CURRENT_DATE'){
        	$start='CURRENT_DATE';
        }
        /*
         * $interval have to be int ant not be quote, should hoppfull the save way instead quote
         * schnueptust (28.05.2007)
         */
        $interval = intval($interval);
        
        // retrieve entries without attachments
        $q = ' SELECT * , extract(epoch FROM entry_time) AS entry_time,
                          extract(epoch FROM last_update_time) AS last_update_time,
                          extract(epoch FROM start_date) AS start_date,
                          extract(epoch FROM end_date) AS end_date
                 FROM ' . DB_SCHEMA . '.event '.
             '  WHERE (start_date, end_date) OVERLAPS (' . $start . ', INTERVAL \'' . $interval . ' days\')';
                            
        $q .= ' ORDER BY ' . DB_SCHEMA . '.event.start_date ' . $order . ', ' . DB_SCHEMA . '.event.end_date ' . $order . 
        					', id ' . $order;
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        // will contain the forum thread entries
        $entries = array ();

        foreach ($res as $row) {
            $newEventEntry = new EventEntryModel();
            $newEventEntry->buildFromRow($row);
            
            if($orderByDate){
                // -1 secend need that a date from 00:00 to 24:00 only display on this day
                // and not on the next day
                $days = $newEventEntry->endDate - $newEventEntry->startDate - 1;
                $days = floor($days/86400)+1;
        		for($i=0; $i<$days; $i++){
                	$index = date('Ymd',$newEventEntry->startDate+3600*24*$i);
                	if (empty($entries[$index]) || !is_array($entries[$index])){
                		$entries[$index] = array();
                	}
                	$entries[$index][] = $newEventEntry;
        		}
            }else{
            	$entries[] = $newEventEntry;
            }
        }

        return $entries;
    }
    
    public static function getEventById($eventId) {
        $DB = Database :: getHandle();

        if ($eventId == null){
        	throw new ArgumentNullException('eventId');
        }
            
        // retrieve entries without attachments
        $q = ' SELECT * , extract(epoch FROM entry_time) AS entry_time,
                          extract(epoch FROM last_update_time) AS last_update_time,
                          extract(epoch FROM start_date) AS start_date,
                          extract(epoch FROM end_date) AS end_date
                 FROM ' . DB_SCHEMA . '.event '.
              ' WHERE id ='. $DB->quote($eventId); 
                            
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }


        foreach ($res as $row) {
            $newEventEntry = new EventEntryModel();
            $newEventEntry->buildFromRow($row);
            return $newEventEntry;
        }
    }
    
    public function isOnDay($proveDate){
    	return (date('Ymd',$this->startDate) == date('Ymd',$proveDate));
    }
    
    public function getCaption(){
            return $this->caption;
    }
    public function getAuthor(){
        if($this->author == null){
            $this->author = UserModel :: getUserById($this->authorIntId);
        }
        if($this->author == null){
            $this->author = new UserAnonymousModel();
        }
        return $this->author;
    }  
    public function getGroupId(){
            return $this->groupId;  
    }        
    public function getGroup(){
            if($this->group_cache != null)
                return $this->group_cache;
            if($this->groupId != null)
                return $this->group_cache = GroupModel :: getGroupById($this->groupId);
                
            return null;        
    }
    public function getCategoryId(){
            return $this->categoryId;  
    }
    public function getStartDate(){
            return $this->startDate;
    }
    // TODO: outsource to template function for L10N?
    public function getStartDateFormated(){
            return date('l, \d\e\n d. F Y' ,strtotime($this->startDate));             
    }
    public function getEndDate(){
            return $this->endDate;
    }
    // TODO: outsource to template function for L10N?
    public function getEndDateFormated(){
            return date('l, \d\e\n d. F Y' ,strtotime($this->endDate));             
    }
    public function getTimeEntry(){
            return $this->timeEntry;
    }
    // TODO: outsource to template function for L10N?
    public function getTimeEntryFormated(){
            return date('l, \d\e\n d. F Y' ,strtotime($this->timeEntry));             
    }
    public function getTimeLastUpdate(){
            return $this->timeLastUpdate;
    }
    // TODO: outsource to template function for L10N?
    public function getTimeLastUpdateFormated(){
            return date('l, \d\e\n d. F Y' ,strtotime($this->timeLastUpdate));             
    }
    public function getAttachments(){
    	return array();
    }
    public function getContentRaw(){
    	return $this->contentRaw;
    }
    
    public function setStartDate($value){
        $this->startDate = $value;
    }
    public function setEndDate($value){
        $this->endDate = $value;
    }
    public function setGroupId($value){
    	$this->groupId = $value;
    }
    public function setCaption($value){
        $this->caption = $value;
    }
    public function setContentRaw($value){
    	$this->contentRaw = $value;
    }
    
    public function getParseSettings() {
        if ($this->parseSettings == null) {

            if ($this->id == null) {
                $parseSettings = array ();
                if (array_key_exists(F_ENABLE_FORMATCODE, $_REQUEST)) {
                    $this->parseSettings[BaseEntryModel :: PARSE_AS_FORMATCODE] = true;
                }
                if (array_key_exists(F_ENABLE_SMILEYS, $_REQUEST)) {
                    $this->parseSettings[BaseEntryModel :: PARSE_AS_SMILEYS] = true;
                }
            } else {
                $DB = Database :: getHandle();

                $q = 'SELECT enable_formatcode, enable_html, enable_smileys 
                        FROM ' . DB_SCHEMA . '.event 
                       WHERE id=' . $DB->Quote($this->id);

                $res = $DB->execute($q);
                if (!$res) {
                    throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
                }

                // if no entry with current id can be found, throw exception
                if ($res->EOF) {
                    throw new CoreException($this->getErrorMessage(GENERAL_ARGUMENT_INVALID, $this->id), E_ERROR);
                }

                // initialize parse settings from DB values
                $this->parseSettings = array ();
                if (Database :: convertPostgresBoolean($res->fields['enable_html']))
                    $this->parseSettings[BaseEntryModel :: PARSE_AS_HTML] = true;
                if (Database :: convertPostgresBoolean($res->fields['enable_formatcode']))
                    $this->parseSettings[BaseEntryModel :: PARSE_AS_FORMATCODE] = true;
                if (Database :: convertPostgresBoolean($res->fields['enable_smileys']))
                    $this->parseSettings[BaseEntryModel :: PARSE_AS_SMILEYS] = true;
            }
        }
            
        return $this->parseSettings;
    }
    
    public function parse($showLastUpdate = true, $addAttachmentLinks = true) {
        // update content
        return $this->content = parent :: parse($showLastUpdate, $addAttachmentLinks);
    }
    
       /**
     * Save the current model to the database 
     */
    public function save() {
	    //var_dump($this);
        /** test that all necessacry fields are given */
        if ($this->contentRaw == null) {
        	throw new ArgumentNullException('contentRaw');
        } else if ($this->getAuthor() == null ) {
        	throw new ArgumentNullException('author');
        }

        $keyValue = array ();

        $DB = Database :: getHandle();

        // start transaction for inserting
        $DB->StartTrans();
        
        
        // force reparsing of content
        $this->content = null;
        
        
        $keyValue['last_update_time'] = 'now()';

        /** it's an insert so wie need more data */
        if ($this->id == null) {
            $keyValue['entry_time'] = 'now()';

            $keyValue['author_int'] = $DB->quote($this->author->id);          
        } else{
            $keyValue['author_int'] = $DB->quote($this->author->id);
        }

        /** 
         * Save the post ip also on update
         * 
         * no bussiness logic here! this should do the BussinessLogicObject.
         * Use the class ClientInfo there is a function that return the ip!
         */
        $keyValue['post_ip'] = $DB->quote($this->postIp);

        /** used in all operations */
        $keyValue['entry_raw'] = $DB->quote($this->contentRaw);
        $keyValue['caption'] = $DB->quote($this->caption);

        /** new entry -> no last entry message */
        if ($this->id == null) {
        	//Todo: Parsing
            $keyValue['entry_parsed'] = $DB->quote($this->parse(false, false));            
        } else {
            $keyValue['entry_parsed'] = $DB->quote($this->parse(true, false));
        }
        
        // indicate, that we store parsed text to database
        $this->parsedTextNeedsSave = false;
        
        //TODO: use Target Date
        $keyValue['start_date'] = Database::getPostgresTimestamp($this->startDate);
        $keyValue['end_date'] = Database::getPostgresTimestamp($this->endDate);
        
        /* saves the right ownerGroup*/ 
        
        if($this->groupId!=null){
        	$keyValue['group_id'] = $DB->quote($this->groupId);
        }

        $q = null;

        /** is update? we need the a where clausel */
        if ($this->id != null)
            $q = $this->buildSqlStatement('event', $keyValue, false, 'id = ' . $DB->quote($this->id));
        else
            $q = $this->buildSqlStatement('event', $keyValue);

        $res = $DB->execute($q);

        #var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'event','id');
        }

        // complete transaction for inserting
        $DB->CompleteTrans();

    }
    
    public function delete(){
    	$DB = Database::getHandle();
        
        // delete event entry, if it belongs to model owner
        $q = 'DELETE FROM ' . DB_SCHEMA . '.event
                    WHERE id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
  }
?>
