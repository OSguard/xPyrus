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
require_once CORE_DIR . '/exceptions/core_exception.php';
require_once MODEL_DIR . '/base/user_external_model.php';
require_once MODEL_DIR . '/news/news_filter.php';
require_once CORE_DIR . '/parser/bbcode_parser.php';
 
 /**
  * @class NewsEntryModel
  * @brief This is the class for a news entry.
  * 
  * @author schnueptus, kyle, linap
  * @version $Id: news_entry.php 5815 2008-04-14 19:36:47Z trehn $
  * @copyright Copyright &copy; 2006, Unihelp.de
  * 
  * The following properties are available via __get-magic:
  * 
  * from NewsEntryModel
  * <ul>
  * <li><var>id</var>          <b>int</b>          the NewsId</li>
  * <li><var>caption</var>     <b>string</b>       caption/title of the News</li>
  * <li><var>abstract</var>    <b>string</b>       the abstract intruduction</li>
  * <li><var>abstractRaw</var> <b>string</b>       the abstract intruduction unparsed</li>
  * <li><var>group</var>       <b>GroupModel</b>   the GroupModel from the owner of this Entry</li>
  * <li><var>groupId</var>     <b>int</b>          the id of the OwnerGroup</li>
  * <li><var>threadId</var>    <b>int</b>          the id of the linked thread</li>
  * <li><var>startDate</var>   <b>date</b>         the date till the news should be shown</li>
  * <li><var>startDateFormated</var>   <b>string</b>   the date till the news should be shown pre Formated</li>
  * <li><var>endDate</var>     <b>date</b>         the last day the news should be shown</li>
  * <li><var>endDateFormated</var> <b>string</b>   the last day the news should be shown pre Formated</li>
  * <li><var>isSticky</var>    <b>boolean</b>      is the entry important
  * <li><var>isVisible</var>   <b>boolean</b>      is the entry visible
  * <li><var>postIp</var>      <b>string</b>       ip address entry was posted from</li>
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
 class NewsEntryModel extends BaseEntryModel {
 	
    /**
     * the caption/title of the news posting
     * @var string
     */
    protected $caption;
    
    protected $openerRaw;
    protected $opener;
    
    /**
     * IP from which the user has posted the entry. It's ok that this is
     * public because don't hide here a variable from the base class!
     * 
     * @author Kyle
     * 
     * sollten wir nun noch mal nachdenken,
     * weil "sensible" Information eine rechte abfrage eigendlich brauch
     * 
     * @author schnueptus
     */
    public $postIp;
    
    
    private $authorIntId;
    /**
     * saves the id of the Group who posts the news
     * @var int
     */
    private $groupId;
    
    /**
     * saves the id of the Thread who posts the news
     * @var int
     */
    private $threadId;
    
    /**
     * saves the GroupModel of the owner group
     * @var {@link GroupModel}
     */
    private $group_cache;
    
     /**
     * saves the ThreadModel
     * @var {@link GroupModel}
     */
    private $thread_cache;
    
    /**
     * saves the date where the news will be display
     * @var date
     */
    private $startDate;
    
    /**
     * saves the date tille the news will be display
     * @var date
     */
    private $endDate;
    
    /**
     * The news is visible to all other users
     */
    protected $isVisible;
    
    /**
     * The news is sticky
     */
    protected $isSticky;
    
    
    // override string description of attachment links
    protected $stringImageAttachment = 'Bild-Anhang zum News-Eintrag';
    protected $stringFileAttachment = 'Datei-Anhang zum News-Eintrag';
    
    /**
     * just the basic constructor method
     * 
     * @param string $content_raw -  the unparsd entry
     * @param UserModel $author  - who write the News
     * @param array $parseSettings - array with the parser settings
     * @param int $groupId - the id of the group who owns
     * @param string $caption - the caption/title of the news 
     */
    public function __construct($opener_raw = null, $content_raw = null, $author = null, $parseSettings = array (), $groupId = null, $caption = '') {
        parent :: __construct($content_raw, $author, $parseSettings);

        $this->caption = $caption;
        $this->openerRaw = $opener_raw;
        
        $this->groupId = $groupId;
        $this->postIp = ClientInfos :: getClientIP();
    }
    
    /**
     * just build the objekt from on row
     * 
     * @param array $row - the datafile of the news
     * 
     * @return NewsEntryModel
     */
    protected function buildFromRow($row) {
        $this->id = $row['id'];        
        $this->contentRaw = $row['entry_raw'];
        $this->content = $row['entry_parsed'];
        $this->caption = $row['caption'];
        $this->openerRaw = $row['opener_raw'];
        $this->opener = $row['opener_parsed'];

        /** no external author are supported for news composing */
        $this->authorIntId = $row['author_int'];
        $this->groupId = $row['group_id'];
        $this->threadId = $row['thread_id'];
        
        $this->postIp = $row['post_ip'];
        $this->timeEntry = $row['entry_time'];
        $this->timeLastUpdate = $row['last_update_time'];   
        
        $this->startDate =$row['start_date'];     
        $this->endDate =$row['end_date'];     
        
        $this->isVisible=Database :: convertPostgresBoolean($row['is_visible']);
        $this->isSticky=Database :: convertPostgresBoolean($row['is_sticky']);
        
        return $this;
    }
    
    public function update($opener_row = null, $content_raw = null, $parseSettings = array (), $groupId = 0, $caption = ''){
        $this->openerRaw = $opener_row;
        $this->opener = null;
        $this->contentRaw = $content_raw;
        $this->content = null;
        $this->parseSettings = $parseSettings;
        $this->caption = $caption;
        $this->groupId = $groupId;
    }
    
    /**
     * gives a array of NewsEntryModel
     * 
     * @param int $limit - who many news are to show
     * @param int $offset
     * @param 'asc'/'desc' $order
     * 
     * @return NewsEntryModels a array of all NewsEntrys
     * 
     * TODO: add isSticky proberty
     */
    public static function getAllParsedEntries($limit = 100, $offset = 0, $order = 'asc') {
        $DB = Database :: getHandle();

        // set default sort order; also ensure that no sql injection is possible
        $order = strtolower($order);
        if ($order != 'asc' and $order != 'desc') {
            $order = 'desc';
        }

        // retrieve entries without attachments
        $q = ' SELECT ' . DB_SCHEMA . '.news.id AS id, caption, post_ip, entry_parsed, entry_raw, opener_parsed, opener_raw,
                                  author_int, group_id, thread_id,
                                  start_date, end_date, is_visible, is_sticky,
                                  extract(epoch FROM entry_time) AS entry_time,
                                  extract(epoch FROM last_update_time) AS last_update_time
                             FROM ' . DB_SCHEMA . '.news ';

        $q .= 'ORDER BY ' . DB_SCHEMA . '.news.start_date ' . $order . ', id ' . $order . '
                            LIMIT ' . $DB->Quote($limit) . '
                           OFFSET ' . $DB->Quote($offset);
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        // will contain the forum thread entries
        $entries = array ();

        foreach ($res as $row) {
            $newNewsEntry = new NewsEntryModel();
            $newNewsEntry->buildFromRow($row);
            $entries[] = $newNewsEntry;
        }

        return $entries;
    }

   /**
     * gives a array of NewsEntryModel
     * 
     * @param int $limit - who many news are to show
     * @param int $offset
     * @param 'asc'/'desc' $order
     * 
     * @return NewsEntryModels a array of all NewsEntrys
     */
    public static function getEntriesByFilter($newsFilter = null, $limit = 10, $offset = 0, $order = 'desc', &$count = null) {
        $DB = Database :: getHandle();

        // set default sort order; also ensure that no sql injection is possible
        if ($order != 'desc' && $order != 'asc')
            $order = 'asc';

        // retrieve entries without attachments
        $q = ' SELECT ' . DB_SCHEMA . '.news.id AS id, caption, post_ip, entry_parsed, entry_raw, opener_parsed, opener_raw,
                        author_int, group_id,  start_date, thread_id,
                        end_date, is_visible, is_sticky,
                        extract(epoch FROM entry_time) AS entry_time,
                        extract(epoch FROM last_update_time) AS last_update_time
                 FROM ' . DB_SCHEMA . '.news';
                 
         if($newsFilter != null){         
            $q .= ' WHERE ' . $newsFilter->getFilterString();
         }

        $q .= ' ORDER BY is_sticky DESC,
                         ' . DB_SCHEMA . '.news.entry_time ' . $order . ', id ' . $order . '
                   LIMIT ' . $DB->Quote($limit) . '
                  OFFSET ' . $DB->Quote($offset);
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        // will contain the forum thread entries
        $entries = array ();
        if ($res->EOF) return $entries;
        foreach ($res as $row) {
            $newNewsEntry = new NewsEntryModel();
            $newNewsEntry->buildFromRow($row);
            $entries[] = $newNewsEntry;
        }

        if($count !== null){
        	$q = ' SELECT count(id) AS count
                     FROM ' . DB_SCHEMA . '.news';
            if($newsFilter != null){
                $q .= ' WHERE ' . $newsFilter->getFilterString();
            }
            $res = $DB->execute($q);

            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            }
            $count = $res->fields['count'];
        }
        
        return $entries;
    }
    
    /**
     * gives a array of NewsEntryModel
     * 
     * @param int $limit - who many news are to show
     * @param int $offset
     * @param 'asc'/'desc' $order
     * 
     * @return NewsEntryModels a array of all NewsEntrys
     */
    public static function getEntryIdsByFilter($newsFilter = null, $limit = 10, $offset = 0, $order = 'desc') {
        $DB = Database :: getHandle();

        // set default sort order; also ensure that no sql injection is possible
        if ($order != 'desc' && $order != 'asc')
            $order = 'asc';

        // retrieve entries without attachments
        $q = ' SELECT id
                 FROM ' . DB_SCHEMA . '.news';
                 
         if($newsFilter != null){         
            $q .= ' WHERE ' . $newsFilter->getFilterString();
         }

        $q .= ' ORDER BY is_sticky DESC,
                         ' . DB_SCHEMA . '.news.start_date ' . $order . ', id ' . $order . '
                   LIMIT ' . $DB->Quote($limit) . '
                  OFFSET ' . $DB->Quote($offset);
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        // will contain the forum thread entries
        $entries = array ();
        foreach ($res as $row) {
            $entries[] = $row['id'];
        }

        return $entries;
    }
    
    /**
     * gives a array of NewsEntryModel
     * 
     * @param int $limit - who many news are to show
     * @param int $offset
     * @param 'asc'/'desc' $order
     * 
     * @return NewsEntryModels a array of all NewsEntrys
     */
    public static function countEntryIdsByFilter($newsFilter = null) {
        $DB = Database :: getHandle();

        // retrieve entries without attachments
        $q = ' SELECT COUNT(*) AS nr
                 FROM ' . DB_SCHEMA . '.news';
                 
        if($newsFilter != null){         
            $q .= ' WHERE ' . $newsFilter->getFilterString();
        }

        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        return $res->fields['nr'];
    }
        
     /**
     * gives a  NewsEntryModel by the id
     * 
     * @param int $id
     * 
     * @return NewsEntryModels     
     */
    public static function getNewsById($id) {
        $DB = Database :: getHandle();

        if($id == null){
        	throw new ArgumentNullException('id');
        }
            

        // retrieve entries without attachments
        $q = ' SELECT ' . DB_SCHEMA . '.news.id AS id, caption, post_ip, entry_parsed, entry_raw, opener_parsed, opener_raw,
                                  author_int, group_id, thread_id,
                                  start_date, end_date, is_visible, is_sticky,
                                  extract(epoch FROM entry_time) AS entry_time,
                                  extract(epoch FROM last_update_time) AS last_update_time
                             FROM ' . DB_SCHEMA . '.news 
                             WHERE id ='. $DB->quote($id); 
                            
                
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }


        foreach ($res as $row) {
            $newNewsEntry = new NewsEntryModel();
            $newNewsEntry->buildFromRow($row);
            return $newNewsEntry;
        }
    }
    
    public static function getNewsByThreadId($threadId = null){
      
      $DB = Database :: getHandle();

        if( $threadId  == null){
            throw new ArgumentNullException('threadId');
        }
            

        // retrieve entries without attachments
        $q = ' SELECT ' . DB_SCHEMA . '.news.id AS id, caption, post_ip, entry_parsed, entry_raw, opener_parsed, opener_raw,
                                  author_int, group_id, thread_id,
                                  start_date, end_date, is_visible, is_sticky,
                                  extract(epoch FROM entry_time) AS entry_time,
                                  extract(epoch FROM last_update_time) AS last_update_time
                             FROM ' . DB_SCHEMA . '.news 
                             WHERE thread_id ='. $DB->quote($threadId); 
                            
                
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }


        foreach ($res as $row) {
            $newNewsEntry = new NewsEntryModel();
            $newNewsEntry->buildFromRow($row);
            return $newNewsEntry;
        }
    
        return null;
   }
    
    public function getThread(){
    	if($this->id == null){
            return null;
        }
        if($this->thread_cache != null){
        	return $this->thread_cache;
        }
        if($this->threadId != null){
        	return $this->thread_cache = ThreadModel::getThreadById($this->threadId);
        }
        return null;
    }
    
    protected function parseOpener($prepareForAutosave = false) {
    	// indicate, that we had to parse text
        // because it was not available before
        if($prepareForAutosave){
            $this->parsedTextNeedsSave = true;
        }
        // bbcode parser that doesn't replace any [img]-tags
        $ps = new BBCodeParser(0);
        $this->opener = $this->openerRaw;
        $ps->parse($this->opener);
        // parse with inline images
        $this->parseAttachments($this->opener, true, false);
        return $this->opener;
    }
    
    public function getCaption(){
            return $this->caption;
    }
    
    public function getOpenerRaw(){
            return $this->openerRaw;
    }
    
    public function getOpener($prepareForAutosave = false){
            if (!$this->opener) {
                return $this->parseOpener($prepareForAutosave);
            }
            return $this->opener;
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
    public function getThreadId(){
            return $this->threadId;
    }
    public function isSticky(){
            return $this->isSticky;
    }
    public function isVisible(){
    	    return $this->isVisible;
    }
    public function getStartDate(){
            return $this->startDate;
    }
    public function getEndDate(){
            return $this->endDate;
    }

    public function setCaption($value){
    	$this->caption = $value;
    }
    public function setStartDate($value){
        $this->startDate = $value;
    }
    public function setEndDate($value){
        $this->endDate = $value;
    }
    public function setVisible($value){
        $this->isVisible = $value;
    }
    public function setSticky($value){
        $this->isSticky = $value;
    }
    public function setContentRaw($string, $opener){
        $this->contentRaw = $string;
        $this->content = null;
        $this->openerRaw = $opener;
        $this->opener = null;
    }
    public function setThreadId($threadId){
        $this->threadId = $threadId;
    }
   /**
     * Save the current model to the database 
     */
    public function save() {

        /** test that all necessacry fields are given */
        // TODO: test if Group given
        if ($this->contentRaw == null || $this->getAuthor() == null )
            die("content, raw content, author or thread is not given...");

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
        $attachments = $this->saveAttachmentsToAdd($DB);
        
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
        $keyValue['opener_raw'] = $DB->quote($this->openerRaw);
        $keyValue['caption'] = $DB->quote($this->caption);

        /** new entry -> no last entry message */
        if ($this->id == null) {
            $keyValue['entry_parsed'] = $DB->quote($this->parse(false));            
        } else {
            $keyValue['entry_parsed'] = $DB->quote($this->parse());
            $keyValue['last_update_time'] = $DB->quote('now()');
        }
        
        // indicate, that we store parsed text to database
        $this->parsedTextNeedsSave = false;
        
        $keyValue['opener_parsed'] = $DB->quote($this->parseOpener());    
        $keyValue['enable_formatcode'] = $DB->quote(Database :: makeBooleanString($this->isParseAsFormatcode()));
        $keyValue['enable_html'] = $DB->quote(Database :: makeBooleanString($this->isParseAsHTML()));
        $keyValue['enable_smileys'] = $DB->quote(Database :: makeBooleanString($this->isParseAsSmileys()));
        $keyValue['is_visible'] = $DB->quote(Database :: makeBooleanString($this->isVisible));
        $keyValue['is_sticky'] = $DB->quote(Database :: makeBooleanString($this->isSticky));
        
        //TODO: use Target Date
        $keyValue['start_date'] = Database::getPostgresTimestamp($this->startDate);
        $keyValue['end_date'] = Database::getPostgresTimestamp($this->endDate);
        
        /* saves the right ownerGroup */
        $keyValue['group_id'] = $DB->quote($this->groupId);

        if($this->threadId != null){
            $keyValue['thread_id'] = $DB->quote($this->threadId);
        }

        $q = null;

        /** is update? we need the a where clausel */
        if ($this->id != null)
            $q = $this->buildSqlStatement('news', $keyValue, false, 'id = ' . $DB->quote($this->id));
        else
            $q = $this->buildSqlStatement('news', $keyValue);

        $res = $DB->execute($q);

        #var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
        
        // if we have no id, take the last inserted one
        if ($this->id == null) {
            $this->id = Database::getCurrentSequenceId($DB, 'news','id');
        }

        /*****************************
         * save attachments
         *****************************/
        $this->saveAttachmentsRelationshipToAdd($DB, $attachments, 'news_attachments');
        $this->saveAttachmentsToDelete($DB, 'news_attachments');                   

        // complete transaction for inserting
        $DB->CompleteTrans();

    }

    /**+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    /**                          all  methods which need a EntryModel                                 */
    /**+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

    public function parse($showLastUpdate = true, $addAttachmentLinks = true) {
        // update content
        return $this->content = parent :: parse($showLastUpdate, false);
    }

    public function getContentParsed() {
        if ($this->content === null) {
            // ensure that we have current parse settings
            $parseSettings = $this->getParseSettings();
            $str = '[opener]' . $this->getOpenerRaw() . '[/opener]' . $this->getContentRaw();
            $ps_array = ParserFactory::createParserFromSettings($parseSettings);
            // parse iteratively
            // apply parsers from ParseStrategy-Array
            foreach ($ps_array as $parseStrategy) {
                $str = $parseStrategy->parse($str);
            }
            
            // integrate attachment links/images into parsed content, if required
            $this->parseAttachments($str);
            $this->content = $str;
        }
        
        return $this->content;
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
                                        FROM ' . DB_SCHEMA . '.news 
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

    /**
      * gives all attachments of this entry
      * 
      * @return array array of EntryAttachmentModel objects
      */
    public function getAttachments() {
    	return parent::_getAttachments('news_attachments');
    }

    public function searchAttachment($inputString = 'file_attachment1', $maxAttachmentSize = null) {
        if ($_FILES[$inputString]['size']) {

            if ($maxAttachmentSize == null)
                $maxAttachmentSize = GlobalSettings :: getGlobalSetting('ENTRY_MAX_ATTACHMENT_SIZE') * 1024;

            // username of session object should not induce security risk
            // this username is contrainted by the database to digits and chars
            $atm = AttachmentHandler :: handleAttachment($_FILES[$inputString], 
                AttachmentHandler::getAdjointPath(Session :: getInstance()->getVisitor()), true, $maxAttachmentSize);
            // add attachment to object
            $this->addAttachment($atm);
        }

    }

    public function searchDelAttachment() {
        // read all attachment ids that are to be deleted
        // these are in POST: delattach<id>
        foreach ($_POST as $key => $val) {
            if (preg_match('/delattach(\d+)/', $key, $matches)) {
                $this->deleteAttachmentById($matches[1]);
            }
        }
    }

    public function getTimeLastUpdate() {
        return $this->timeLastUpdate;
    }

    public function getContentRaw() {
        return $this->contentRaw;
    }
    
    public function delete(){
    	$DB = Database::getHandle();
        
        // delete guestbook entry, iff it belongs to model owner
        $q = 'DELETE FROM ' . DB_SCHEMA . '.news
                    WHERE id = ' . $DB->Quote($this->id);
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
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
        $q = $this->buildSqlStatement('news', array('entry_parsed' => $DB->Quote($this->getContentParsed()),
                                                    'opener_parsed' => $DB->Quote($this->getOpener())),
            false, 'id = ' . $DB->Quote($this->id));
        $res = &$DB->Execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }        
    }
    
 }
?>
