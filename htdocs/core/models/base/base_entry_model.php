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

# $Id: base_entry_model.php 6210 2008-07-25 17:29:44Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/base_entry_model.php $

require_once MODEL_DIR . '/base/base_model_with_attachments.php';
require_once CORE_DIR . '/parser/parser_factory.php';

/**
 * Superclass for different types of entries
 *
 * @package Models
 * @subpackage Base
 *
 * @author linap 
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * @return available Model variabeln:
 * <ul>
 * <li><var>id</var><b> int</b> </li>
 * <li><var> author</var><b>{@link UserModel}</b> the author of the Entry</li>
 * <li><var> timeEntry</var><b> date</b> time where the entry is generated</li>
 * <li><var> timeLastUpdate</var><b> date</b> where the last change on the entry</li>
 * <li><var> content</var><b> string</b> parsed contend</li>
 * <li><var> contentRaw</var><b> string</b> not parsed contend</li>
 * <li><var> enableSmileys</var><b> boolean</b></li>
 * <li><var> enableFormatCode</var><b> boolean</b></li>
 * <li><var> attachments</var><b>{@link EntryAttachmentModel</b>}</b></li>
 * <ul>
 */
abstract class BaseEntryModel extends BaseModelWithAttachments {
    protected $content;
    protected $contentRaw;
    protected $timeEntry;
    protected $timeLastUpdate;
    /**
     * @var UserModel
     */
    protected $author;
    /**
     * @var GroupModel
     */
    protected $authorGroup;
    /**
     * @var boolean
     * if true, entry was read without parsed text
     * from database and has not been saved yet
     */
    protected $parsedTextNeedsSave = false;
    
    protected $stringImageAttachment = 'Bild-Anhang zum Eintrag';
    protected $stringFileAttachment = 'Datei-Anhang zum Eintrag';
    protected $stringLastUpdated = 'zuletzt bearbeitet';
    
    /*
     * constants defining requested parsing settings
     */
    const PARSE_AS_HTML = 'enable_html';
    const PARSE_AS_SMILEYS = 'enable_smileys';
    const PARSE_AS_FORMATCODE = 'enable_formatcode';
    const PARSE_AS_WIKI = 'enable_wiki';

    const PARSE_MAX_SMILEYS = 'MAX_SMILEYS_SETTINGS';

    /**
     * @var array
     * parse settings of this entry, see constants in this class
     */
    protected $parseSettings;

    public function __construct($content_raw = null, $author = null, $parseSettings = array()) {
        $this->contentRaw = $content_raw;
        $this->author = $author;
        $this->parseSettings = $parseSettings;
        $this->timeEntry = time();
        $this->authorGroup = null;
    }
    
    public function isParseAsFormatcode() {
        if ($this->parseSettings == null) {
            $this->getParseSettings();
        }
        if(array_key_exists(self::PARSE_AS_FORMATCODE,$this->parseSettings))
            return $this->parseSettings[self::PARSE_AS_FORMATCODE];
            
        return false;    
    }
    public function isParseAsSmileys() {
        if ($this->parseSettings == null) {
            $this->getParseSettings();
        }
        if(array_key_exists(self::PARSE_AS_SMILEYS,$this->parseSettings))
            return $this->parseSettings[self::PARSE_AS_SMILEYS];
        
        return false; 
    }
    public function isParseAsHTML() {
        if ($this->parseSettings == null) {
            $this->getParseSettings();
        }
        if(array_key_exists(self::PARSE_AS_HTML,$this->parseSettings))
            return  $this->parseSettings[self::PARSE_AS_HTML];
            
        return false;    
    }

    /**
     * gets parsed content of this entry
     * @param boolean $prepareForAutosave if set, a flag is set, so parsed content /can/ be saved
     *   automagically for instance in a __destruct method
     */
    public function getContentParsed($prepareForAutosave = false) {
        if ($this->content == null) {
            $this->content = $this->parse(false);

            if ($prepareForAutosave) {
                // indicate, that we had to parse text
                // because it was not available before
                $this->parsedTextNeedsSave = true;
            }
        }

        return $this->content;
    }
    abstract public function getTimeLastUpdate();
    abstract public function getContentRaw();
    abstract public function getParseSettings();
    
    public function setGroupId($groupId) {
      $this->authorGroup = $groupId;
    }
    public function setGroup($group) {
      $this->authorGroup = $group;
    }
    
    protected function __get($var) {
    	switch ($var) {
        case 'author': return $this->author;
        case 'timeEntry': return $this->timeEntry;
        case 'timeLastUpdate': return $this->timeLastUpdate;
        case 'content': return $this->getContentParsed();
        case 'contentRaw': return $this->getContentRaw();
        
        case 'enableSmileys': return $this->isParseAsSmileys();
        case 'enableFormatCode': return $this->isParseAsFormatcode();
        
        case 'attachments': return $this->getAttachments();
        
        case 'isForGroup': return $this->authorGroup != null;
        case 'group': 
          if (!($this->authorGroup instanceof GroupModel)) {
            $this->authorGroup = GroupModel::getGroupById($this->authorGroup);
          }
          return $this->authorGroup;
    	}
        
        return null;
    }
    
    public function getAuthor() { return $this->author; }
    
    public function setAuthor($user){
    	$this->author = $user;
    }
    
    public function getTimeEntry() { return $this->timeEntry; }
    public function isForGroup() { return $this->authorGroup != null; }
    public function getGroup() {
        if ($this->authorGroup == null) {
            return null;
        }
        if (!($this->authorGroup instanceof GroupModel)) {
            $this->authorGroup = GroupModel::getGroupById($this->authorGroup);
        }
        return $this->authorGroup;
    }
    public function getGroupId() {
        if ($this->authorGroup == null) {
            return null;
        }
        if ($this->authorGroup instanceof GroupModel) {            
            return $this->authorGroup->id;
        }
        // if it is not a group model, it is the id
        return $this->authorGroup;
    }
    
    
    public function setContentRaw($string) {
        $this->contentRaw = $string;
        $this->content = null;
    }
    
    public function setParseSettings($ps) {
        $this->parseSettings = $ps;
    }
    
    public function getQuote($username = '') {
        $contentRaw = $this->getContentRaw();

        // remove possible inline attachments
        $contentRaw = preg_replace('/\[atm\=[A-Fa-f0-9]+\]\s*/i', '', $contentRaw);
        
        // remove possible ugly opener tags
        $contentRaw = preg_replace('#\[opener\](.*)\[/opener\]#s', '$1', $contentRaw);
        
        if ($username != '') {
            return '[quote=' . $username . ']' . $contentRaw . "[/quote]\n";
        } else {
            return '[quote]' . $contentRaw . "[/quote]\n";
        }
    }
  
    /**
     * Parses the entry so that special tags etc. are shown as smilies and so on
     *
     * @param boolean $showLastUpdate true, if "last_update" string should be appended,
     * (defaults to false)
     * @param boolean $addAttachmentLinks true, if attachment links/images should
     * be integrated into the parsing result (defaults to true)
     *
     * @return string Ready parsed string. Codes from the respective code base is
     * converted to another format.
     */
    protected function parse($showLastUpdate = false, $addAttachmentLinks = true) {
        // ensure that we have current parse settings
        $parseSettings = $this->getParseSettings();

        $str = $this->getContentRaw();
        $ps_array = ParserFactory::createParserFromSettings($parseSettings);

        // parse iteratively
        // apply parsers from ParseStrategy-Array
        foreach ($ps_array as $parseStrategy) {
            $str = $parseStrategy->parse($str);
        }
        
        // integrate attachment links/images into parsed content, if required
        if ($addAttachmentLinks) {
            $this->parseAttachments($str);
        }
        if ($showLastUpdate) {
            $str .= '<br /><br /><ins>' . $this->stringLastUpdated.': ' . date('d.m.Y, H:i') . '</ins>';
        }
        
        
        return $str;
    }
    
    protected function parseAttachments(&$str, $inlineAttachments = true, $externalAttchments = true) {
        $attachments = $this->getAttachments();
        // variables for storing markup regarding attachments
        $images = $misc = '';
        foreach ($attachments as $atm) {
            //var_dump( $atm );
            if ($atm->isImage()) {
                // remove trailing point from path
                // e.g. "./userfiles/users/3"
                $filePath = $atm->getFilePath();
                if (substr($filePath,0,1) == '.') {
                    $filePath = substr($filePath,1);
                }
                $imageHTML = '<img alt="'.$this->stringImageAttachment.'" src="'.$filePath.'" />';
                if ($inlineAttachments and ($strNew = str_replace('[atm=' . ($atm->getTempId()) . ']', $imageHTML, $str, $replaceCount)) and $replaceCount == 1) {
                    // we have already done everything we wanted (successfully replaces at exactly one tag)
                    // set new string
                    $str = $strNew;
                } else {
                    $images .= $imageHTML;
                }
            } else {
                //$misc .= '<li><a target="_blank" title="'.self::$stringFileAttachment.'" href="'.$atm->getFilePath().'">'.$atm->getFileName().'</a> ('.$atm->getFileSize(true).'K)</li>';
                $linkHTML = '<a target="_blank" title="'.self::$this->stringFileAttachment.'" href="/attachments/'.$atm->id.'">'.$atm->getFileName() .'</a> ('.$atm->getFileSize(true).'K)';
                if ($inlineAttachments and ($strNew = str_replace('[atm=' . ($atm->getTempId()) . ']', '<span class="atmlink">' . $linkHTML . '</span>', $str, $replaceCount)) and $replaceCount == 1) {
                    // we have already done everything we wanted (successfully replaces at exactly one tag)
                    // set new string
                    $str = $strNew;
                } else {
                    $misc .= '<li>' . $linkHTML . '</li>';
                }
            }
        }
        if ($externalAttchments) {
            if ($images) $str .= '<div class="imgattachments">'.$images.'</div>';
            if ($misc) $str .= '<ul class="miscattachments">'.$misc.'</ul>';
        }
        //return $str;
    }
    
    public abstract function save();
    
    protected static function highlightTerms($searchTerms, $text) {
        // prepare regexp
        $patterns = array();
        $subs = array();
        foreach ($searchTerms as $t) {
            // because umlauts were replaced by normal characters by our stemmer
            // we give the highlighter the opportunity to find them here
            // again despite the transformation
            $t = str_replace(array('a','o','u', 'ss'), array('(?:a|ä)', '(?:o|ö)', '(?:u|ü)', '(?:ss|ß)'), $t);
            // highlight the search term, if it is at the beginning of a word
            $patterns[] = '/\b(' . $t . '[\wäöüÄÖÜß]*)/i';
            $subs[] = '<strong class="adminNote">$1</strong>';
        }
        // work on entry and caption
        return preg_replace($patterns, $subs, $text);
    }
    
    public function highlight($searchTerms) {
        $content = '';
        $oldPos = 0;
        
        $realContent = $this->getContentParsed();
        
        // first safe all HTML tags
        $tags = array();
        $tagReplace = array();
        $tagCounter = 0;
        while (($pos = strpos($realContent, '<', $oldPos)) !== false) {
            $rpos = strpos($realContent, '>', $pos) + 1;
            
            if ($rpos !== false) {
                $tags[] = substr($realContent, $pos, $rpos - $pos);
                $tagReplace[] = ' ___TAG' . $tagCounter . '___ ';
                $content .= substr($realContent, $oldPos, $pos - $oldPos);
                $content .= $tagReplace[$tagCounter++];

                $oldPos = $rpos;
            } else {
                break;
            }
        }
        $content .= substr($realContent, $oldPos);
        
        $this->content = str_replace($tagReplace, $tags, self::highlightTerms($searchTerms, $content));
    }
}

?>
