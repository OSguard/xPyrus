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

// $Id: tag.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/parser/tag.php $

/**
 * helper class for parsing bbcode
 *
 * represents one [bbcode]-tag
 *
 * @package Parser
 */
class Tag {
  private $tagName;
  private $tagAttribute;
  private $tagPos;
  private $isOpening;
  private $hasValidArgument;
  private $originalText;
  private static $imgCount = 0;
  private static $maxImgCount = 1000;

  public static function resetImgCount($limit = 1000) {
    self::$maxImgCount = $limit;
    self::$imgCount = 0;
  }

  /**
   * constructor
   *
   * @param string $tag bbcode tag; content of []
   * @param int $pos position in original text, where tag ends
   */
  public function __construct($tag, $pos = 0) {
    // check, if tag is pure tag or has an argument given with '='
    $posEqual = strpos($tag, "=");
    if (!$posEqual) {
      $this->tagName = $tag;
    } else {
      $this->tagName = substr($tag, 0, $posEqual);
      $this->tagAttribute = substr($tag, $posEqual+1);
    }

    // closing tags begin with '/'
    if ($this->tagName and $this->tagName[0] == '/') {
      $this->tagName = substr($this->tagName,1);
      $this->isOpening = false;
    } else {
      $this->isOpening = true;
    }
   
    $this->tagName = strtolower($this->tagName); 
    $this->tagPos = $pos;
    
    // assume, arguments are valid
    // this can be negated after some checkURL/checkColor etc.
    $this->hasValidArgument = true;
    
    // save original text of tag
    $this->originalText = $tag;
  }
  
  /**
   * @return boolean true, if and only if tag is an opening one
   */
  public function isOpening() {
    return $this->isOpening;
  }
  
  /**
   * @return boolean true, if and only if tag is a valid bbcode tag
   */
  public function isValid() {
    if ($this->tagName == 'list') return true;
    if ($this->tagName == '*') return true;
    if ($this->tagName == 'b') return true;
    if ($this->tagName == 'i') return true;
    if ($this->tagName == 'u') return true;    
    if ($this->tagName == 'align') return true;
    if ($this->tagName == 'color') return true;
    if ($this->tagName == 'font') return true;
    if ($this->tagName == 'size') return true;
    if ($this->tagName == 'quote') return true;
    if ($this->tagName == 'hr') return true;
    if ($this->tagName == 'url') return true;
    if ($this->tagName == 'img') return true;
    if ($this->tagName == 'opener') return true;
    if ($this->tagName == 'tex' && defined('LATEX_AVAILABLE') && LATEX_AVAILABLE) return true;
    if ($this->tagName == 'code' && defined('HIGHLIGHT_AVAILABLE') && HIGHLIGHT_AVAILABLE) return true;
    if ($this->tagName == 'l33t') return true;    
    return false;
  }
  
  /**
   * name of tag, e.g. b, img, li, url
   * @return string
   */
  public function getName() {
    return $this->tagName;  
  }
  /**
   * tag itsself, e.g. [b], [img]
   * @return string
   */
  public function getTag() {
    return '[' . $this->originalText . ']';  
  }
  public function getPos() {
    return $this->tagPos;  
  }
  
  /**
   * @return boolean true, if and only if string is a valid URL
   */
  private static function checkURL($str) {
    return InputValidator::isValidURL($str, true);
  }
  
  /**
   * @return boolean true, if and only if string is a unihelpinternal URL
   */
  private static function isInternalURL($str) {
    return preg_match('#^(?:http|ftp)://[a-z\-0-9\._]+\.unihelp\.de(/.*)?$#i',$str);
  }
  
  /**
   * @return int 1, if color is a css name; 2, if color is hex-value; 0, if color is invalid
   */
  public static function checkColor($str) {
    // convert to lowercase first
    $str = strtolower($str);
    
    if ($str=='black') return 1;
    if ($str=='silver') return 1;
    if ($str=='gray') return 1;
    if ($str=='white') return 1;
    if ($str=='maroon') return 1;
    if ($str=='red') return 1;
    if ($str=='purple') return 1;
    if ($str=='fuchsia') return 1;
    if ($str=='green') return 1;
    if ($str=='lime') return 1;
    if ($str=='olive') return 1;
    if ($str=='yellow') return 1;
    if ($str=='navy') return 1;
    if ($str=='blue') return 1;
    if ($str=='teal') return 1;
    if ($str=='aqua') return 1;
    // check for valid color value #xyz or #rrggbb
    if (preg_match('/#([a-f0-9]{3,3})|([a-f0-9]{6,6})/i',$str)) return 2;
    
    return 0;
  }
  
  /**
   * @return string CSS-font-family-string, if and only if string is a valid font-family identifier; 
   *         returns null, if and only if identifier is invalid
   */
  public static function checkFontFamily($str) {
    // convert to lowercase first
    $str = strtolower($str);
    
    if ($str=='verdana') return '\'Verdana\'';
    if ($str=='helvetica') return '\'Helvetica\'';
    if ($str=='impact') return '\'Impact\'';
    if ($str=='lucidagrande') return '\'Lucida Grande\'';
    if ($str=='tahoma') return '\'Tahoma\'';
    if ($str=='garamond') return '\'Garamond\'';
    if ($str=='georgia') return '\'Georgia\'';
    if ($str=='timesnewroman') return '\'Times New Roman\'';
    if ($str=='comicsansms') return '\'Comic Sans MS\'';
    if ($str=='scriptc') return '\'ScriptC\'';
    if ($str=='couriernew') return '\'Courier New\',mono';
    
    return null;
  }
  /**
   * allows right, left, center, justify
   */
  public static function checkAlign($str){
  	 if ($str == 'right') return true;
     if ($str == 'left') return true;
     if ($str == 'center') return true;
     if ($str == 'justify') return true;
     
     return false;
  }
  
  /**
   * allows font sizes between 6pt and 29pt
   * @return boolean true, if and only if string is a valid font size
   */
  public static function checkFontSize($str) {
    // minimal font size is 6pt, maximal font size is 29pt
    if ($str >= 6 && $str <= 29) return true;
    
    return false;
  }
  
  /**
   * <b>note:</b>
   * - special treatment of singular codes: [hr] and [*] (list item)
   * - special treatment of code [pre]
   * @return string opening HTML-Tag for element
   */
  public function getOpeningHtmlTag( $arg = '' ) {
    if ($this->tagName == 'list' && !$this->tagAttribute) 
        return '</p><ul>';
    elseif ($this->tagName == 'list') {
        if ($this->tagAttribute != '1' && $this->tagAttribute != 'a')
            return '</p><ol>';
        else
            return '</p><ol type="'.$this->tagAttribute.'">';
    }
    if ($this->tagName == 'b') return '<b>';
    if ($this->tagName == 'i') return '<i>';
    if ($this->tagName == 'u') return '<u>';    
    if ($this->tagName == 'color') {
      $colorStyle = self::checkColor($this->tagAttribute);
      // color name or color value in #xxxxxx given
      if ($colorStyle)
        // note: space between colon and attribute is neccessary
        // because smiley parser could stumple upon :p etc. ...
        return '<span style="color : '.$this->tagAttribute.';">';
      // invalid color
      else
        return '<span>';
    }
    if ($this->tagName == 'font') {
      $fontStyle = self::checkFontFamily($this->tagAttribute);
      // valid font identifier given
      if ($fontStyle!=null)
        // note: space between colon and attribute is neccessary
        // because smiley parser could stumple upon :p etc. ...
        return '<span style="font-family : '.$fontStyle.';">';
      // invalid font identifier
      else
        return '<span>';
    }
    if ($this->tagName == 'align'){
    	if(self::checkAlign($this->tagAttribute)){
    		return '<div style="text-align: '.$this->tagAttribute.'">';
    	}else{
    		return '<div>';
    	}
    }
    if ($this->tagName == 'size') {
      if (self::checkFontSize($this->tagAttribute))
        // note: space between colon and attribute is neccessary
        // because smiley parser could stumple upon :p etc. ...
        return '<span style="font-size : '.$this->tagAttribute.'pt;">';
      else
        return '<span>';
    }
    if ($this->tagName == 'opener'){
        return '</p><div class="opener"><p>';
    }

    if ($this->tagName == 'quote' && !$this->tagAttribute) 
        return '</p><blockquote><p>';
    elseif ($this->tagName == 'quote') 
        return '<span class="quoteAuthor"><cite>'.$this->tagAttribute.'</cite> '.ENTRY_SCRIPSIT.':</span></p><blockquote><p>';
            
    if ($this->tagName == 'url' && !$this->tagAttribute) {
      // check, whether URL is valid
      if (self::checkURL($arg)) {
        $shortenedArg = $arg;
        if (strlen($arg) > V_URL_MAX_LENGTH) {
            $shortenedArg = substr_replace($shortenedArg, '&#8230;', V_URL_MAX_LENGTH - V_URL_TAIL - 1, 
                strlen($shortenedArg) - V_URL_MAX_LENGTH + 1);
        }
        if (self::isInternalURL($arg)) {
          return '<a title="ein interner Link, der sich im gleichen Fenster &ouml;ffnet" href="'.$arg.'">'. $shortenedArg;
        } else {
          return '<a target="_blank" title="ein externer Link, der sich in einem neuen Fenster &ouml;ffnet" href="'.$arg.'">' . $shortenedArg;
        }
      } else {
        // set state variable to false, so that no closing tag will be printed
        $this->hasValidArgument = false;
        // if no valid URL, return string only
        return $arg;
      }
    }
    elseif ($this->tagName == 'url') {
      // check, whether URL is valid
      if (self::checkURL($this->tagAttribute)) {
        if (self::isInternalURL($this->tagAttribute)) {
          return '<a title="ein interner Link, der sich im gleichen Fenster &ouml;ffnet" href="'.$this->tagAttribute.'">';
        } else {
          return '<a target="_blank" title="ein externer Link, der sich in einem neuen Fenster &ouml;ffnet" href="'.$this->tagAttribute.'">';
        }        
      } else {
        // set state variable to false, so that no closing tag will be printed
        $this->hasValidArgument = false;
        // if no valid URL, return string only
        return $arg;
      }
    }
            
    if ($this->tagName == 'img') {
      // check, whether URL is valid
      // and image limit is not reached
      if (self::checkURL($arg) and self::$imgCount < self::$maxImgCount) {
        self::$imgCount++;
        return '<img src="'.$arg.'" alt="ein externes Bild" />';
      } else {
        // set state variable to false, so that no closing tag will be printed
        $this->hasValidArgument = false;
        // if no valid URL, return string only
        return $arg;
      }
    }
    
    if($this->tagName == 'l33t') {
    	$newText = str_ireplace('hacker', 'H4xX0rz', $arg);
    	$newText = str_ireplace('er', '0r', $newText);
    	$newText = str_ireplace('au', '0w', $newText);
    	$newText = str_ireplace('ck', 'xX', $newText);
    	$newText = str_ireplace('ou', '00', $newText);
    	$newText = str_ireplace('f', 'ph', $newText);
    	
    	$newText = str_ireplace('a', '4', $newText);
    	$newText = str_ireplace('e', '3', $newText);
    	$newText = str_ireplace('o', '0', $newText);
    	$newText = str_ireplace('s', '$', $newText);
    	$newText = str_ireplace('t', '†', $newText);
    	$newText = str_ireplace('l', '1', $newText);
    	$newText = str_ireplace('!', '!!!11', $newText);
    	return $newText;
    }
    // note: special treatment of singular codes: [hr] and [*] (list item)
    // note: special treatment of code [pre]
    return '';
  }
  
  /**
   * <b>note:</b>
   * - special treatment of singular codes: [hr] and [*] (list item)
   * - special treatment of code [pre]
   * @return string closing HTML-Tag for element
   */
  public function getClosingHtmlTag() {
    if ($this->tagName == 'list' && !$this->tagAttribute) 
        return '</ul><p>';
    elseif ($this->tagName == 'list') 
        return '</ol><p>';
    if ($this->tagName == 'b') return '</b>';
    if ($this->tagName == 'i') return '</i>';
    if ($this->tagName == 'u') return '</u>';
    if ($this->tagName == 'align') return '</div>';
    if ($this->tagName == 'color') return '</span>';
    if ($this->tagName == 'font') return '</span>';
    if ($this->tagName == 'size') return '</span>';
    if ($this->tagName == 'opener') return '</p></div><p>';
    if ($this->tagName == 'quote') return '</p></blockquote><p>';
    if ($this->tagName == 'url' && $this->hasValidArgument) return '</a>';
    // note: special treatment of singular codes: [hr] and [*] (list item)
    // note: special treatment of code [pre]
    return '';
  }
  
  /**
   * @param Tag tag to be nested inside this tag 
   * @return boolean true, if and only if bbcode-tags inside this tag are allowed
   */
  public function allowsNestedTags($innerTag) {
    // generally forbid nesting url tags to avoid invalid XHTML
    if ($this->tagName == 'url' and !$this->tagAttribute) return false;
    // allow only images inside URLs
    if ($this->tagName == 'url' and $this->tagAttribute and $innerTag->tagName != 'img') return false;
    if ($this->tagName == 'img') return false;
    // no block level elements in inline elements
    if ($this->isInlineElement() and $innerTag->isBlockElement()) {
        // but we can try to fix this
        // NOTE: seems not work (linap),
        // because we would have to swap the closing tags, too
        // but we don't know them here
        /*$dummy = $this->tagName;
        $this->tagName = $innerTag->tagName;
        $innerTag->tagName = $dummy;
        
        $dummy = $this->tagAttribute;
        $this->tagAttribute = $innerTag->tagAttribute;
        $innerTag->tagAttribute = $dummy;
        
        $dummy = $this->tagPos;
        $this->tagPos = $innerTag->tagPos;
        $innerTag->tagPos = $dummy;
        
        $dummy = $this->isOpening;
        $this->isOpening = $innerTag->isOpening;
        $innerTag->isOpening = $dummy;
        
        $dummy = $this->hasValidArgument;
        $this->hasValidArgument = $innerTag->hasValidArgument;
        $innerTag->hasValidArgument = $dummy;
        
        $dummy = $this->originalText;
        $this->originalText = $innerTag->originalText;
        $innerTag->originalText = $dummy;     
        return true;   */
        return false;
    }
    // surrounding quote neems impossible
    // because we close a <p> before the <blockquote>
    // and do not expect open HTML tags there
    if ($innerTag->tagName == 'quote' and $this->tagName != 'quote') {
        return false;
    }
    return true;
  }
  
  public function isInlineElement() {
    if ($this->tagName == 'b') return true;
    if ($this->tagName == 'i') return true;
    if ($this->tagName == 'u') return true;
    if ($this->tagName == 'color') return true;
    if ($this->tagName == 'font') return true;
    if ($this->tagName == 'size') return true;
    if ($this->tagName == 'url') return true;
    if ($this->tagName == '*') return true;
    
    return false;
  }
  
  public function isBlockElement() {
    if ($this->tagName == 'quote') return true;  
    if ($this->tagName == 'opener') return true;
    if ($this->tagName == 'list') return true;
    if ($this->tagName == 'align') return true;
    if ($this->tagName == 'hr') return true;
    return false;
  }
  
  /**
   * @return boolean true, if and only if text between bbcode-[] [/] is neccessary for parsing
   */
  public function needsAdditionalArgument() {
    if ($this->tagName == 'url' && !$this->tagAttribute) return true;
    if ($this->tagName == 'img') return true;
    if ($this->tagName == 'l33t') return true;
    return false;
  }
}

?>
