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
require_once MODEL_DIR . '/base/user_protected_model.php';
require_once MODEL_DIR . '/base/group_model.php';
require_once MODEL_DIR . '/base/user_external_model.php';
 
require_once CORE_DIR . '/exceptions/core_exception.php';
require_once CORE_DIR . '/utils/client_infos.php';
require_once CORE_DIR . '/interfaces/notifier.php';
require_once CORE_DIR . '/constants/form_constants.php';
require_once CORE_DIR . '/utils/user_ipc.php';

 /**
  * @class PmEntryModel
  * @brief This is the class for a pm entry.
  * 
  * @author schnueptus, kyle, linap
  * @version $Id: pm_entry_model.php 5807 2008-04-12 21:23:22Z trehn $
  * @copyright Copyright &copy; 2006, Unihelp.de
  * 
  * The following properties are available via __get-magic:
  * 
  * from PmEntryModel
  * <ul>
  * <li><var>id</var>              <b>int</b>          the NewsId</li>
  * <li><var>caption</var>         <b>string</b>       caption/title of the News</li>
  * <li><var>postIp</var>          <b>string</b>       ip address entry was posted from</li>
  * <li><var>isUnread</var>        <b>boolean</b>      is the message unread</li>
  * <li><var>receiver</var>        <b>UserModel</b>    the riceiver of the PM</li>
  * <li><var>recipientString</var> <b>string</b>       the input of the riceivers of the PM</li>
  * <li><var>receiverView</var>    <b>boolean</b>      if you have get the message</li>
  * <li><var>replyTo</var>       <b>int</b>          the id where the pm is replay to (can be null)</li>
  * <li><var>senderView</var>      <b>boolean</b>      if you have send the message</li>
  * </ul>
  * 
  * from BaseEntryModel
  * <ul>
  * <li><var>author</var>              <b>UserModel</b>    the author of the Entry</li>
  * <li><var>timeEntry</var>           <b>date</b>         time when the entry was generated</li>
  * <li><var>timeLastUpdate</var>      <b>date</b>         time when the last change took place</li>
  * <li><var>content</var>             <b>string</b>       parsed content</li>
  * <li><var>contentRaw</var>          <b>string</b>       not parsed content</li>
  * <li><var>enableSmileys</var>       <b>boolean</b></li>
  * <li><var>enableFormatCode</var>    <b>boolean</b></li>
  * <li><var>attachments</var>         <b>EntryAttachmentModel</b></li>
  * </ul>
  * 
  * @package Models/News
  */
class PmEntryModel extends BaseEntryModel implements Notifier {
    
    /**
     * the caption/title of the pm
     * @var string
     */
    protected $caption;
    /**
     * is the massege unread (receiverView)
     */
    protected $isUnread = null;
    
    /**
     * 
     */
    protected $isDeleted;
    
    protected $authorIntId;
    protected $authorExtId;
    /**
     * the id of the receiver (receiverView)
     */
    protected $receiverId;
    /**
     * saves the UserModel of the receiver (receiverView)
     */
    protected $receiver_cache;
    /**
     * saves a array of receivers to send
     */
    protected $receivers;
    /**
     * upper limit for number of receivers
     */
    protected $receiverLimit;
    /**
     * save the interpret string where the message send to
     */
    protected $recipientString;
    /**
     * array of group ids to send
     */
    protected $groupIds;
    /**
     * array if course ids to send
     */
    protected $courseIds;
    /**
     * boolean if you want to sent to all on your friendlist
     */
    protected $sendToFriendlist;
    /**
     * boolean if you want to send to all Users
     */
    protected $sendToAll;
    /**
     * boolean if you want to send to all online Users
     */
    protected $sendToOnline;
    /**
     * boolean if you want to send to all group Admins
     */
    protected $sendToGroupAdmin;
    /**
     * id where the PM is replay;
     */
    protected $replyTo = null;
    
    protected $postIp;
    
    /**
     * boolean
     * true, if pm costs points; false, if pm is free
     */
    protected $costPolicy;
    
    protected $NextPmId = -1, $BeforePmId = -1;
    
    // override string description of attachment links
    protected $stringImageAttachment = 'Bild-Anhang zur Privatnachricht';
    protected $stringFileAttachment = 'Datei-Anhang zur Privatnachricht';
    
    /**
     * just the basic constructor method
     * 
     * @param string $content_raw -  the unparsd entry
     * @param UserModel $author  - who write the News
     * @param array $parseSettings - array with the parser settings
     * @param int $groupId - the id of the group who owns
     * @param string $caption - the caption/title of the news 
     * @param string $recipientString - the sting with $receivers information 
     */
    public function __construct($content_raw = null, $author = null, $parseSettings = array (), $caption = '', $recipientString = '', $costPolicy = true) {
        parent :: __construct($content_raw, $author, $parseSettings);

        $this->caption = $caption;
        $this->recipientString = $recipientString;
        $this->attachmentsToAdd = array();
        $this->costPolicy = $costPolicy;
        
        $this->postIp = ClientInfos :: getClientIP();
    }
    
    
    public function update($content_raw = null, $parseSettings = array (), $caption = '', $recipientString = ''){
        $this->contentRaw = $content_raw;
        $this->content = null;
        $this->parseSettings = $parseSettings;
        $this->caption = $caption;
        $this->recipientString = $recipientString;
    }
   /**
     * just build the objekt from on row
     * 
     * @param array $row - the datafile of the news
     * 
     * @return PmEntryModel
     */
    protected function buildFromRow($row) {
        $this->id = $row['id'];        
        $this->contentRaw = $row['entry_raw'];
        $this->content = $row['entry_parsed'];
        $this->caption = $row['caption'];

        /* it's an internal or external author? */
        $this->authorIntId = $row['author_int'];
        $this->authorExtId = $row['author_ext'];        
        
        $this->postIp = $row['post_ip'];
        $this->timeEntry = $row['entry_time'];   
        
        if(array_key_exists('is_unread',$row))        
            $this->isUnread = Database :: convertPostgresBoolean($row['is_unread']);
        
        if(array_key_exists('receiver',$row))
            $this->receiverId = $row['receiver'];       
        
        if(array_key_exists('recipient_string',$row))
            $this->recipientString = $row['recipient_string'];
        
        return $this;
    }
    /**
     * get all PM by UserId
     * 
     * @param int $userId the id of the user
     * @param int $limit
     * @param int offset
     * @param (asc/desc) $order
     * 
     * @return array of PmEntryModel description
     * 
     * @throws DBExceptions
     */
    public static function getPmByUserId($userId = null, $limit = V_PM_ENTRIES_PER_PAGE, $offset = 0, $order = 'asc'){
        $DB = Database :: getHandle();

        // set default sort order; also ensure that no sql injection is possible
        $order = strtolower($order);
        if ($order != 'asc' and $order != 'desc') {
            $order = 'asc';
        }
        
        // retrieve entries without attachments
        $q = ' SELECT m.id AS id, m.caption AS caption, m.post_ip AS post_ip, 
                      m.entry_parsed AS entry_parsed, m.entry_raw AS entry_raw, 
                      m.author_int AS author_int, m.author_ext AS author_ext, 
                      extract(epoch FROM m.entry_time) AS entry_time,
                      e.user_id_for AS receiver, e.is_unread AS is_unread                           
               FROM ' . DB_SCHEMA . '.pm AS m, ' 
                      . DB_SCHEMA . '.pm_for_users AS e 
               WHERE e.user_id_for = ' . $DB->Quote($userId)
                     .'and e.pm_id = m.id ';
                     
        $q .= 'ORDER BY m.entry_time ' . $order . ', m.id ' . $order . '
                            LIMIT ' . $DB->Quote($limit) . '
                           OFFSET ' . $DB->Quote($offset);
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        // will contain the pm thread entries
        $entries = array ();
        $tempAuthorIds = array();
        $tempAuthorExtIds = array();
       
        foreach ($res as $row) {
            $newPmEntry = new PmEntryModel();
            $newPmEntry->buildFromRow($row);
            
            if ($row['author_int']!=null) {
                // temporarily save needed user ids to fetch them at once later
                array_push($tempAuthorIds, $row['author_int']);
                $newForumEntry->author       = $row['author_int'];
            } else if ($row['author_ext']!=null) {
                array_push($tempAuthorExtIds, $row['author_ext']);
                $newForumEntry->author       = -$row['author_ext'];
            } else {
                $newForumEntry->author == null;
            }
            $entries[] = $newPmEntry;
        }
        $users = UserProtectedModel::getUsersByIds($tempAuthorIds, '', false);
        $usersExt = UserExternalModel::getUsersByIds($tempAuthorExtIds);
        foreach ($entries as $e) {
            // substitute entry author-id by corresponding user object
            if ($e->author > 0) {
                if (array_key_exists($e->author, $users)) {
                    $e->author = $users[$e->author];
                } else {
                    $e->author = new UserAnonymousModel;
                }
            } else if ($e->author < 0) {
                $e->author = $usersExt[-$e->author];
            }
        }

        return $entries;
        
    }
    /**
     * gives all PM by one author
     * 
     * @param UserModel $author
     * @param int $limit
     * @param int $offset
     * @param (asc/desc) $order
     * 
     * @return array of PmEntryModel description
     * 
     * @throws DBException
     * TODO: dont build for every PM a new UserModel 
     */
    public static function getPmByAuthor($author = null, $limit = V_PM_ENTRIES_PER_PAGE, $offset = 0, $order = 'desc'){
        $DB = Database :: getHandle();

        // set default sort order; also ensure that no sql injection is possible
        $order = strtolower($order);
        if ($order != 'asc' and $order != 'desc') {
            $order = 'desc';
        }

        // retrieve entries without attachments
        $q = ' SELECT id, caption, post_ip, recipient_string,
                      entry_parsed, entry_raw, 
                      author_int,  author_ext, extract(epoch FROM m.entry_time) AS entry_time
                  FROM ' . DB_SCHEMA . '.pm AS m ';
                             
        if($author->isExternal()){
                   $q .= ' WHERE m.author_ext = ' . $DB->Quote($author->id);
        }else{
                   $q .= ' WHERE m.author_int = ' . $DB->Quote($author->id);
        }
        $q .= ' and m.author_has_deleted = ' . $DB->Quote(Database::makeBooleanString(false));

        $q .= 'ORDER BY m.entry_time ' . $order . ', m.id ' . $order . '
                            LIMIT ' . $DB->Quote($limit) . '
                           OFFSET ' . $DB->Quote($offset);
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        // will contain the pm thread entries
        $entries = array ();
        $tempAuthorIds = array();
        $tempAuthorExtIds = array();
       
        foreach ($res as $row) {
            $newPmEntry = new PmEntryModel();
            $newPmEntry->buildFromRow($row);
            
            if ($row['author_int']!=null) {
                // temporarily save needed user ids to fetch them at once later
                array_push($tempAuthorIds, $row['author_int']);
                $newForumEntry->author       = $row['author_int'];
            } else if ($row['author_ext']!=null) {
                array_push($tempAuthorExtIds, $row['author_ext']);
                $newForumEntry->author       = -$row['author_ext'];
            } else {
                $newForumEntry->author == null;
                //Logging::getInstance()->logWarning('author unknown');
            }
            $entries[] = $newPmEntry;
        }
        $users = UserProtectedModel::getUsersByIds($tempAuthorIds, '', false);
        $usersExt = UserExternalModel::getUsersByIds($tempAuthorExtIds);
        foreach ($entries as $e) {
            // substitute entry author-id by corresponding user object
            if ($e->author > 0) {
                if (array_key_exists($e->author, $users)) {
                    $e->author = $users[$e->author];
                } else {
                    $e->author = new UserAnonymousModel;
                }
            } else if ($e->author < 0) {
                $e->author = $usersExt[-$e->author];
            }
        }
        
        return $entries;
        
    }
    /**
     * get a PM form a Author
     * 
     * @param UserModel $author
     * @param int $id - the id of the Pm
     * 
     * @return PmEntryModel
     * 
     * @throws DBException
     */
    public static function getPmByIdAuthor($author = null, $id = null){
        $DB = Database :: getHandle();

        // retrieve entries without attachments
        $q = ' SELECT  id,  caption,  post_ip, recipient_string,
                       entry_parsed,  entry_raw, 
                       author_int, author_ext, extract(epoch FROM m.entry_time) AS entry_time
               FROM ' . DB_SCHEMA . '.pm AS m '; 
                                     
       if($author->isExternal()){
                   $q .= ' WHERE m.author_ext = ' . $DB->Quote($author->id);
       }else{
                   $q .= ' WHERE m.author_int = ' . $DB->Quote($author->id);
       }            
                                 
       $q .= ' and m.id = ' . $DB->Quote($id);
       $q .= ' and m.author_has_deleted = ' . $DB->Quote(Database::makeBooleanString(false));

       $res = $DB->execute($q);

        //var_dump($q);

       if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
       }

       foreach ($res as $row) {
            $newPmEntry = new PmEntryModel();
            $newPmEntry->buildFromRow($row);
            return $newPmEntry;
       }
        return null;
    }
    /**
     * get a PM form a UserId as receiver
     * 
     * @param UserModel $author
     * @param int $id - the id of the Pm
     * 
     * @return PmEntryModel
     * 
     * @throws DBException
     */
    public static function getPmById($userId = null, $id = null){
        $DB = Database :: getHandle();

        // retrieve entries without attachments
        $q = ' SELECT m.id AS id, m.caption AS caption, m.post_ip AS post_ip, 
                      m.entry_parsed AS entry_parsed, m.entry_raw AS entry_raw, 
                      m.author_int AS author_int, m.author_ext AS author_ext, 
                      extract(epoch FROM m.entry_time) AS entry_time,
                      e.user_id_for AS receiver, e.is_unread AS is_unread,
                      m.recipient_string as recipient_string                
               FROM ' . DB_SCHEMA . '.pm AS m, ' 
                      . DB_SCHEMA . '.pm_for_users AS e 
               WHERE e.user_id_for = ' . $DB->Quote($userId)
                     . ' and m.id = ' . $DB->Quote($id)
                     . 'and e.pm_id = m.id ';

        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        foreach ($res as $row) {
            $newPmEntry = new PmEntryModel();
            $newPmEntry->buildFromRow($row);
            return $newPmEntry;
        }
        
        return null;
    }
    
    public function notify($user, $subject, $body) {
        return self::sendSystemPm($body, $subject, $user->getUsername(), array($user), true, true);
    }
    
    public function notifyAll($users, $subject, $body) {
        return self::sendSystemPm($body, $subject, 'undisclosed recipients', $users, true, true);
    }
    
    /**
     * creates a new pm to be used as a system generated pm
     * @return boolean
     */
    public static function sendSystemPm($contentRaw, $caption, $recipientString, $recipients, $useBBCode = false, $useSmilies = true) {
        $systemUser = UserModel::getSystemUser();
        if ($systemUser == null) {
            return false;
        }
        if($useBBCode){
            $parseSettings = array(F_ENABLE_SMILEYS => $useSmilies, F_ENABLE_FORMATCODE => true);
        }else{
            $parseSettings = array(F_ENABLE_SMILEYS => $useSmilies);
        }
        
        
        // create new charge-free pm
        $pm = new PmEntryModel($contentRaw, $systemUser, $parseSettings, 
            $caption, $recipientString, false);
        if(is_array($recipients)){
            $pm->setReceivers($recipients);
        }elseif($recipients == 'toAll'){
        	$pm->setAsAll(true);
        }elseif($recipients == 'toOnline'){
        	$pm->setAsOnline(true);
        }else{
        	new CoreException('no reciver for SystemPm');
        }
        
        // System is Sender so no one can read this as sender => mark as deleted
        $pm->isDeleted = true;
        $pm->postIp = '127.0.0.1';
        $pm->save();
        
        if(is_array($recipients)){
            foreach ($recipients as $r) {
                self::signalPmsChanged($r);
            }
        }
        
        return true;
    }
    
    public static function signalPmsChanged($user) {
        // signal pm number changed
        $userIPC = new UserIPC($user->id);
        $userIPC->setFlag('PMS_CHANGED');
    }
    
    public static function signalPmsSentChanged($user) {
        // signal pm sent number changed
        $userIPC = new UserIPC($user->id);
        $userIPC->setFlag('PMS_SENT_CHANGED');
    }
    
    /**
     * get the Id of the Next PM
     * 
     * @return int
     */
    protected function loadNextPmId($revert = false){
        $DB = Database :: getHandle();
        
        if(empty($this->receiverId)){
            return null;
        }
        
        // TODO: (comment by linap, 10.01.2007)
        // is this function neccessary? better determine next PM
        // only when needed ...
        
        if(!$revert){
            $operator = '<';
        }
        else{
            $operator = '>';
        }
        
        // retrieve entries without attachments
        $q = ' SELECT m.id AS id           
               FROM ' . DB_SCHEMA . '.pm AS m, ' 
                      . DB_SCHEMA . '.pm_for_users AS e 
               WHERE e.user_id_for = ' . $DB->Quote($this->receiverId)
                     . ' and m.entry_time '.$operator.' (SELECT entry_time FROM ' . DB_SCHEMA . '.pm WHERE id = '.$DB->Quote($this->id).')'
                     . 'and e.pm_id = m.id ';
        
        if(!$revert){
            $q .= 'ORDER BY entry_time DESC LIMIT 1';
        }else{
            $q .= 'ORDER BY entry_time ASC LIMIT 1';
        }
        
        $res = $DB->execute($q);

        #var_dump($q);

        if (!$res) {
            return null;
        }

        return $res->fields['id'];
        
    }
    /**
     * PM cant change
     * always return null
     */
    public function getTimeLastUpdate(){
        return null;
    }
    public function getAuthor(){
        if($this->author == null){
            $this->author = UserExternalModel::getUserByIntOrExtId($this->authorIntId, $this->authorExtId);
        }
        if($this->author == null){
            $this->author = new UserAnonymousModel();
        }
        return $this->author;
    }
    public function setAuthor($value){
        $this->author = $value;
    }
    /**
     * get content unparst
     */
    public function getContentRaw() {
        return $this->contentRaw;
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
                                        FROM ' . DB_SCHEMA . '.pm 
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
    
    public function getAttachments() {
        return parent::_getAttachments('pm_attachments');
    }
    
    /**
     * set the Receivers
     * 
     * @param array of UserModel $receivers
     * @param int $receiverLimit upper bound of receiver number; 
     *        if set to -1, number is unbounded  
     */
    public function setReceivers($receivers, $receiverLimit = -1){
        $this->receivers = $receivers;
        $this->receiverLimit = $receiverLimit;
    }  
    /**
     * set the ids where the message send to groups
     * 
     * @param array of int $groups with ids of groups
     */
    public function setGroupIds($groups){
        $this->groupIds = $groups;
    } 
    /**
     * set the ids where the message send to courses
     * 
     * @param array of int $courseswith ids of courses
     */
    public function setCourseIds($courses){
        $this->courseIds = $courses;
    } 
    /**
     * set that the message send to author friendlist
     * 
     * @param boolean
     */
    public function setAsFriendlist($value){
        if( !($value === true or $value === false) ){
            throw new ArgumentException('value', $value);
        }
        
        $this->sendToFriendlist = $value;
    }
    /**
     * set that the message send to all users
     * 
     * @param boolean
     */
    public function setAsAll($value){
        if( !($value === true or $value === false) ){
            throw new ArgumentException('value', $value);
        }
        
        $this->sendToAll = $value;
    }
    /**
     * set that the message send to all online users
     * 
     * @param boolean
     */
    public function setAsOnline($value){
        if( !($value === true or $value === false) ){
            throw new ArgumentException('value', $value);
        }
        
        $this->sendToOnline = $value;
    }
     /**
     * set that the message send to all group admins
     * 
     * @param boolean
     */
    public function setAsGroupAdmin($value){
        if( !($value === true or $value === false) ){
            throw new ArgumentException('value', $value);
        }
        
        $this->sendToGroupAdmin = $value;
    }
    /**
     * change to unread of the message
     * 
     * @param boolean
     */
    public function setUnread($value){      
        if ($this->id == null) {
        	throw new ArgumentNullException('id');
        } else if ($this->receiverId == null) {
        	throw new ArgumentNullException('receiverId');
        }
        
        $DB = Database :: getHandle();
        
        $keyValue = array ();
        $keyValue['is_unread'] = $DB->quote(Database :: makeBooleanString($value));
        
        $q = $this->buildSqlStatement('pm_for_users', $keyValue, false,
                    'pm_id = ' . $DB->quote($this->id) . ' and user_id_for = ' . $DB->quote($this->receiverId) );
                    
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }            
    }          
    
    /**
     * adds an attachment to this entry
     * 
     * @param EntryAttachment $attachment attachment to add
     */
    public function addAttachment($attachment) {
        parent :: addAttachment($attachment);
    }
    /**
     * del the message for a user
     * 
     * @param UserModel $user 
     */
    public function delForUser($user){
        $DB = Database :: getHandle();
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.pm_for_users 
                       WHERE pm_id =' . $DB->quote( $this->id ) .
                       ' AND user_id_for =' . $DB->quote( $user->id );
                    
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }
    
    public function delForAuthor(){
        $DB = Database :: getHandle();
        
        $keyValues = array('author_has_deleted' => $DB->Quote(Database::makeBooleanString(true)) );
        
        $q = $this->buildSqlStatement('pm', $keyValues, false, 'id ='.$this->id);
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }
    /**
     * the overloading get methode, alle var see Model description
     * 
     * @return the search Model var
     */
    protected function __get($name) {
        // TESTME
        throw new CoreException ("don't use __get-magic (" . $name . "), you have to use new getter functions instead");
        /*if ($name == 'caption')
            return $this->caption;
        if ($name == 'id')
            return $this->id;
        if ($name == 'isUnread'){
            return $this->isUnread;
        }
        if ($name == 'receiver'){
            if($this->receiver_cache != null)
                return $this->receiver_cache;
            if($this->receiverId != null)
                return $this->receiver_cache = UserProtectedModel::getUserById($this->receiverId, true);
            return null;        
        }                       
        if($name == 'recipientString'){
            return $this->recipientString;
        }
        if( $name == 'senderView'){
            return ($this->receiverId === null);
        }
        if( $name == 'receiverView'){
            return !($this->receiverId === null);
        }
        if( $name == 'replyTo'){
            return $this->replyTo;
        }
        if( $name == 'NextPmId'){
            if( $this->NextPmId == -1){
                $this->NextPmId = $this->loadNextPmId(true);
            }
            return $this->NextPmId;
        }
        if( $name == 'BeforePmId'){
            if( $this->BeforePmId == -1 ){
                $this->BeforePmId = $this->loadNextPmId(false);
            }
            return $this->BeforePmId;
        }
        
        $res = parent :: __get($name);
        
        if(DEVEL && $res === null) {
            trigger_error('PmEntryModel::__get: Property with the name \''. 
                $name .'\' was not found!', E_USER_NOTICE);
        }
        
        return $res;*/
    }
    
    public function getCaption() { return $this->caption; }
    public function isUnread() { return $this->isUnread; }
    public function getReceiver() {
        if ($this->receiver_cache != null) {
            return $this->receiver_cache;
        }
        if($this->receiverId != null) {
            return $this->receiver_cache = UserProtectedModel::getUserById($this->receiverId, true);
        }
        return null;
    }
    public function getRecipientString() {
        return $this->recipientString;
    }
    public function isSenderView() {
        return ($this->receiverId === null);
    }
    public function isReceiverView() {
        return ($this->receiverId !== null);
    }
    public function getReplyTo() {
        return $this->replyTo;
    }
    public function getNextPMId() {
        if ($this->NextPmId == -1){
            $this->NextPmId = $this->loadNextPmId(true);
        }
        return $this->NextPmId;
    }
    public function getBeforePMId() {
        if ($this->BeforePmId == -1){
            $this->BeforePmId = $this->loadNextPmId(false);
        }
        return $this->BeforePmId;
    }
    
    public function setCaption($caption) {
        $this->caption = $caption;
    }
    
    public function setReplyTo($val) {
        $this->replyTo = $val;
    }
    public function getPostIP() {
        return $this->postIp;
    }
     public function getQuote() {
        $username = $this->getAuthor()->getUsername();
        if($this->getAuthor()->isExternal()){
            $username .= '@' . $this->getAuthor()->getCityName();
        }
        return parent::getQuote($username);
    }
    public function isSystemPM(){
        return $this->getAuthor()->isSystemUser();
    }
    
    /**
     * save and send
     */
    public function save(){
        
        /** test that all necessacry fields are given */
        if ($this->contentRaw == null || $this->author == null )
            trigger_error('not all information given to save PM', E_ERROR);

        $keyValue = array ();

        $DB = Database :: getHandle();

        // start transaction for inserting
        $DB->StartTrans();
        
        // before we save the entry, save the attachments
        // in order to retrieve their ids
        // to embed them in the pre-parsed content
        
        // save array of added attachments
        // for later relationship assignment
        // can't save relationship here, because entry id is unknown
        if($this->attachmentsToAdd == null) {
            $this->attachmentsToAdd = array();   
        }
        $attachments = $this->saveAttachmentsToAdd($DB);
        
        // force reparsing of content
        $this->content = null;
        
        /** it's an insert so wie need more data */
        if ($this->id == null) {
            $keyValue['entry_time'] = 'now()';

            /* save internal or external author */
            if ($this->author->isExternal())
                $keyValue['author_ext'] = $DB->quote($this->author->id);
            else
                $keyValue['author_int'] = $DB->quote($this->author->id);
                
            if($this->isDeleted !== null){
            	$keyValue['author_has_deleted'] = $DB->quote(Database::makeBooleanString($this->isDeleted));
            }    

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
        $keyValue['recipient_string'] = $DB->quote($this->recipientString);
        if(!empty($this->replyTo)){  
            $keyValue['previous_pm'] = $DB->quote($this->replyTo);
        }    
      
        $keyValue['entry_parsed'] = $DB->quote($this->parse( ($this->id != null) ));
        
        // indicate, that we store parsed text to database
        $this->parsedTextNeedsSave = false;
                
        $keyValue['enable_formatcode'] = $DB->quote(Database :: makeBooleanString($this->isParseAsFormatcode()));
        $keyValue['enable_html'] = $DB->quote(Database :: makeBooleanString($this->isParseAsHTML()));
        $keyValue['enable_smileys'] = $DB->quote(Database :: makeBooleanString($this->isParseAsSmileys()));

        $q = $this->buildSqlStatement('pm', $keyValue);

        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        $this->id = Database::getCurrentSequenceId($DB, 'pm','id');
        

        /*****************************
         * further save attachments
         *****************************/        
        $this->saveAttachmentsRelationshipToAdd($DB, $attachments, 'pm_attachments');
        /* save MC attachemts */
        //$this->saveAttachmentsRelationshipToAdd($DB, $this->attachmentsMCToAdd, 'pm_attachments');
        
        /*************************************************
         * send the pm to the reciver and calculate points
         *************************************************/
        
        $error = true;
        $points = $this->author->getPoints();
        $pointsEconomic = $this->author->getPointsEconomic();
        /* send to users */
        if($this->receivers != null){
            $error = $this->sendToReceivers($DB);
        }
        /* send the message to all on firendslist */
        if($error && $this->sendToFriendlist){
            $error = $this->sendToFriendlist($DB);
        }
        if($error && $this->sendToAll){
            $error = $this->sendToAll($DB);
        }
        if($error && $this->sendToOnline){
            $error = $this->sendToOnline($DB);
        }
        if($error && $this->sendToGroupAdmin){
            $error = $this->sendToGroupAdmin($DB);
        }
        if($error && $this->groupIds){
            $error = $this->sendToGroup($DB);
        }
        if($error && $this->courseIds){
            $error = $this->sendToCourse($DB);
        }
        //NOTICE: no attachment del
        
        if(!$error){
            //$DB->RollbackTrans();
            $DB->FailTrans();
            $DB->CompleteTrans();
            $this->author->reloadPoints();
            return false;
        }
        else{
            /* save the points of the User */
            $this->author->save(); 
        }    
        
        // complete transaction for inserting
        $DB->CompleteTrans();
        return true;
    }
    /**
     * help method for save
     * 
     * @param AdoDB class $DB
     */
    protected function sendToReceivers($DB){

        $receiversIdString = Database::makeCommaSeparatedString($this->receivers, 'id');
        
        $q = 'INSERT INTO ' . DB_SCHEMA . '.pm_for_users 
                   (pm_id, user_id_for)
                 SELECT DISTINCT ON (id) '.$DB->Quote($this->id).', id 
                   FROM ' . DB_SCHEMA . '.users 
                   WHERE id IN (' . $receiversIdString . ')
                     AND flag_active=true  
                     AND NOT EXISTS (SELECT user_id_for 
                                       FROM ' . DB_SCHEMA . '.pm_for_users 
                                      WHERE pm_id='.$DB->Quote($this->id).'
                                        AND user_id_for = pm_for_users.id)';
        //var_dump($q);
        $res = & $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        if ($this->costPolicy) {
            /* change the points */
            $many = $DB->Affected_Rows();
            if (!$this->author->hasEnoughPoints('PM_SENT', $many)) {
                return false;
            }
            $ps = PointSourceModel::getPointSourceByName('PM_SENT');
            $this->author->increaseUnihelpPoints($many * $ps->getPointsSum(), 
                                                $many * $ps->getPointsFlow());
        }

        return true;
    }
        /**
     * help method for save
     * 
     * @param AdoDB class $DB
     */
    protected function sendToFriendlist($DB){
        
        $authorId = $this->author->id;
        
        $q = 'INSERT INTO ' . DB_SCHEMA . '.pm_for_users 
                    (pm_id, user_id_for)
                SELECT DISTINCT ON (f.friend_id) '.$DB->Quote($this->id).', f.friend_id 
                  FROM ' . DB_SCHEMA . '.user_friends AS f,
                       ' . DB_SCHEMA .'.users    AS u
                 WHERE f.user_id=' . $DB->Quote($authorId) . '
                      AND f.friend_id = u.id
                      AND u.flag_active = true
                      AND f.friend_type NOT IN (SELECT id FROM public.friend_types WHERE is_type_friend = false)
                      AND NOT EXISTS (SELECT user_id_for 
                                        FROM ' . DB_SCHEMA . '.pm_for_users 
                                       WHERE pm_id='.$DB->Quote($this->id).'
                                         AND user_id_for = friend_id)';
        
        //var_dump($q);
        
        $res = & $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        if ($this->costPolicy) {
            /* change the points */    
            $many = $DB->Affected_Rows();                        
            if (!$this->author->hasEnoughPoints('PM_SENT', $many)) {
                return false;
            }
            $ps = PointSourceModel::getPointSourceByName('PM_SENT');
            $this->author->increaseUnihelpPoints($many * $ps->getPointsSum(), 
                                                $many * $ps->getPointsFlow());
        }
        
        return true;
    }
    
    /**
     * help method for save
     * 
     * @param AdoDB class $DB
     */
    protected function sendToAll($DB){
        $q = 'INSERT INTO ' . DB_SCHEMA . '.pm_for_users 
                    (pm_id, user_id_for)
                SELECT DISTINCT ON (id) '.$DB->Quote($this->id).', id 
                  FROM ' . DB_SCHEMA . '.users 
                 WHERE flag_activated = true
                   AND flag_invisible = false
                   AND NOT EXISTS (SELECT user_id_for 
                                     FROM ' . DB_SCHEMA . '.pm_for_users 
                                    WHERE pm_id='.$DB->Quote($this->id).'
                                      AND user_id_for = id)';
        $res = & $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        return true;
    }
    
    protected function sendToOnline($DB){
        $q = 'INSERT INTO ' . DB_SCHEMA . '.pm_for_users 
                    (pm_id, user_id_for)
                SELECT DISTINCT ON (user_id) '.$DB->Quote($this->id).', id 
                  FROM ' . DB_SCHEMA . '.user_online 
                 WHERE NOT EXISTS (SELECT user_id_for 
                                     FROM ' . DB_SCHEMA . '.pm_for_users 
                                    WHERE pm_id='.$DB->Quote($this->id).'
                                      AND user_id_for = id)';
        
        $res = & $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        return true;
    }
    
    protected function sendToGroupAdmin($DB){
        $q = 'INSERT INTO ' . DB_SCHEMA . '.pm_for_users 
                    (pm_id, user_id_for)
                SELECT DISTINCT ON (user_id) '.$DB->Quote($this->id).', user_id 
                  FROM ' . DB_SCHEMA . '.rights_user_group 
                 WHERE NOT EXISTS (SELECT user_id_for 
                                     FROM ' . DB_SCHEMA . '.pm_for_users 
                                    WHERE pm_id='.$DB->Quote($this->id).'
                                      AND user_id_for = id)
                   AND right_id = (SELECT id FROM '.DB_SCHEMA.'.rights WHERE name = \'GROUP_OWN_ADMIN\') ';
        
        $res = & $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        return true;
    }
    
    /**
     * help method for save
     * 
     * @param AdoDB class $DB
     */
    protected function sendToGroup($DB){    
        
        $q = 'INSERT INTO ' . DB_SCHEMA . '.pm_for_users 
                    (pm_id, user_id_for)
                SELECT DISTINCT ON (m.user_id) '.$DB->Quote($this->id).', m.user_id 
                  FROM ' . DB_SCHEMA . '.user_group_membership AS m,
                       ' . DB_SCHEMA . '.users AS u
                 WHERE m.group_id IN ( ' . Database::makeCommaSeparatedString($this->groupIds) . ' )
                   AND m.user_id = u.id
                   AND u.flag_active = true
                   AND NOT EXISTS (SELECT user_id_for 
                                     FROM ' . DB_SCHEMA . '.pm_for_users 
                                    WHERE pm_id='.$DB->Quote($this->id).'
                                      AND user_id_for = user_id)';
        
        //var_dump($q);
        $res = & $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        if ($this->costPolicy) {
            $many = 0; 
            foreach($this->groupIds as $groupId ){
                if(!$this->author->isMemberOfGroup($groupId)){
                    $many++;
                }
            }
            if (!$this->author->hasEnoughPoints('PM_SENT', $many)) {
                return false;
            }
            $ps = PointSourceModel::getPointSourceByName('PM_SENT');
        
            $this->author->increaseUnihelpPoints($many * $ps->getPointsSum(), 
                                                $many * $ps->getPointsFlow());
        }
        
        return true;
    }
    /**
     * help method for save
     * 
     * @param AdoDB class $DB
     */    
    protected function sendToCourse($DB){
        
        $q = 'INSERT INTO ' . DB_SCHEMA . '.pm_for_users 
                    (pm_id, user_id_for)
                SELECT '.$DB->Quote($this->id).', * 
                  FROM (
                        SELECT DISTINCT ON (user_id) user_id 
                          FROM ' . DB_SCHEMA . '.courses_per_student cs,
                               ' . DB_SCHEMA . '.users u
                         WHERE cs.user_id = u.id
                           AND course_id IN ( ' . Database::makeCommaSeparatedString($this->courseIds) . ' )
                           AND u.flag_active = true
                           AND NOT EXISTS (SELECT user_id_for 
                                             FROM ' . DB_SCHEMA . '.pm_for_users 
                                            WHERE pm_id='.$DB->Quote($this->id).'
                                              AND user_id_for = user_id)) x';
        if ($this->receiverLimit != -1) {
            $q .=
            ' ORDER BY random()
                 LIMIT ' . $DB->Quote($this->receiverLimit);
        }       
        //var_dump($q);
        $res = & $DB->execute($q);
        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        } 
        
        if ($this->costPolicy) {
            /* change the points */    
            $many = $DB->Affected_Rows();                        
            if (!$this->author->hasEnoughPoints('PM_SENT', $many)) {
                return false;
            }
            $ps = PointSourceModel::getPointSourceByName('PM_SENT');
            $this->author->increaseUnihelpPoints($many * $ps->getPointsSum(), 
                                                $many * $ps->getPointsFlow());
        }
        return true;
    }
    
    /**
     * destructor
     * 
     * saves parsed text to database, if has not already been parsed
     */
    public function __destruct() {
        // check, if we have to do save operation
        if ($this->id == null or !$this->parsedTextNeedsSave) {
            return;
        }

        $DB = Database :: getHandle();
        $q = $this->buildSqlStatement('pm', array('entry_parsed' => $DB->Quote($this->getContentParsed())),
            false, 'id = ' . $DB->Quote($this->id));
        $res = &$DB->Execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }        
    }
}
 
?>
