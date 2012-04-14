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
 * Created on 11.07.2006 by schnueptus
 * sunburner Unihelp.de
 */
require_once MODEL_DIR . '/base/base_model.php';
require_once CORE_DIR . '/parser/parser_factory.php';

/**
 * the model for the group info page
 * @version $Id: group_infopage_model.php 5807 2008-04-12 21:23:22Z trehn $
 */
class GroupInfopageModel extends BaseEntryModel {
	/**
     * @var GroupModel
     * group the page is about
	 */
    protected $group;
    
    public function __construct($group, $content = null, $contentRaw = null) {
    	parent::__construct($contentRaw);
        $this->group = $group;
        if ($content != ''){
            $this->content = $content;
        }    
    }
    
    public function save() {

        /** test that all necessacry fields are given */
        if ($this->group->id == null) {
            throw new ArgumentNullException('group');
        }
        $keyValue = array ();

        $DB = Database :: getHandle();

        /* values not in db */
        //$keyValue['last_update_time'] = 'now()';
        //$keyValue['post_ip'] = $DB->quote($this->postIp);

        /** used in all operations */
        $keyValue['infopage_raw'] = $DB->quote($this->contentRaw);        

        /** new entry -> no last entry message */
        if ($this->content == null) {
            $keyValue['infopage_parsed'] = $DB->quote($this->parse(false));
        } else {
            $keyValue['infopage_parsed'] = $DB->quote($this->parse());
            /* value not in db */
            //$keyValue['last_update_time'] = $DB->quote('now()');
        }
        
        // indicate, that we store parsed text to database
        $this->parsedTextNeedsSave = false;
                
        /* only update existing group */
        $q = $this->buildSqlStatement('groups', $keyValue, false, 'id = ' . $DB->quote($this->group->id));

        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }     

    }
    
    public function getContentRaw(){
    	if ($this->contentRaw === null) {
            /* if no group given */
            if ($this->group == null || $this->group->id == null) {
                throw new ArgumentNullException('group'); 
            }
            
            $DB = Database::getHandle();
            $q = 'SELECT infopage_raw, infopage_parsed
                    FROM ' . DB_SCHEMA . '.groups   
                   WHERE id = '.$DB->Quote($this->group->id);
            
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            }
            
            foreach ($res as $row){
            	$this->content = $row['infopage_parsed'];
                $this->contentRaw = $row['infopage_raw'];
            }
        }
        return $this->contentRaw;
    }
    
    public function getParseSettings(){
        if (!is_array($this->parseSettings)) {
            $this->parseSettings = array();
        }
        $this->parseSettings[BaseEntryModel :: PARSE_AS_FORMATCODE] = V_GROUP_INFOPAGE_PARSE_AS_FORMATCODE;
        $this->parseSettings[BaseEntryModel :: PARSE_AS_SMILEYS] = V_GROUP_INFOPAGE_PARSE_AS_SMILEYS;
        
        return $this->parseSettings;
    }
    
    /**
     * Parses the entry so that special tags etc. are shown as smilies and so on
     */
    protected function parse() {
        return parent::parse(false, false);
    }
    
    public function getAttachments() {
    	throw new NotImplementedException();
    }
    
    public function getTimeLastUpdate() {
        throw new NotImplementedException();
    }
    
    /**
     * destructor
     * 
     * saves parsed text to database, if has not already been parsed
     */
    public function __destruct() {
        // check, if we have to do save operation
        if ($this->group->id == null or !$this->parsedTextNeedsSave) {
            return;
        }
        
        $DB = Database :: getHandle();
        // TODO: remove use of __get function
        $q = $this->buildSqlStatement('groups', array('infopage_parsed' => $DB->Quote($this->__get('content'))),
            false, 'id = ' . $DB->Quote($this->group->id));
        $res = &$DB->Execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }        
    }
} 
?>
