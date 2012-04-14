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

# $Id: bbcode_parser.php 5807 2008-04-12 21:23:22Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/parser/bbcode_parser.php $

require_once CORE_DIR . '/interfaces/parse_strategy.php';
require_once CORE_DIR . '/utils/input_validator.php';

require_once CORE_DIR.  '/parser/tag.php';

/**
 * class for parsing bbcode
 * @package Modules
 * @version $Id: bbcode_parser.php 5807 2008-04-12 21:23:22Z trehn $
 */
class BBCodeParser implements ParseStrategy {
  private $maxImgCount;
  
  /**
   * detects plain urls in text and masks them via [url][/url]
   * @param string& $text text to work at
   * @return string with transformed urls
   */
  public static function& detectURLs( &$text ) {
    // match urls that are not preceeded by an [url]-tag or an [img]-tag
    $text = preg_replace('#(?<!\[url\]|\[img\]|\[url=)(' . InputValidator::URL_PATTERN . ')#i', '[url]\\1[/url]', $text);
    return $text;
  }
  
  public function __construct($maxImgCount = 20) {
  	$this->maxImgCount = $maxImgCount;
  }

  /**
   * Parses a string regarding BBCode and returns it in HTML format.
   * @param string $text to be parsed.
   * @return string string in HTML format
   */
  public function& parse( &$text ) {
    // trim text first
    $text=trim($text);
    
    if ($text == '') {
        return $text;
    }
    
    // set limit for replacement of [img]-tags
    Tag::resetImgCount($this->maxImgCount);
    
    // save preformatted text
    // can't use normal tag logic (see below)
    // because in [code] environments the \n must not be substituted
    $preStrings = array(); $i=0;
    while ($str = stristr($text,'[code')) {
      // check for language attribute
      $lang = substr($str,5,strpos($str,']')-5);
      $str = substr($str,0,stripos($str,'[/code]')+7);
      $text = str_replace($str, "***pre_string***$i", $text);

      // 6 = strlen('[code') + 1; 7 = strlen('[/code]')
      $source_str = substr($str,6+strlen($lang),-7);
      
      if (defined('HIGHLIGHT_AVAILABLE') && HIGHLIGHT_AVAILABLE && strlen($lang) > 1) {
        require_once BASE.'/lib/lib-highlight/highlight.php';
        // syntax_languages() comes from highlight.php
        // syntax_highlight() comes from highlight.php
        
        // check, if language is valid
        $languages = syntax_languages();
        // remove leading '='
        $lang = strtolower(substr($lang,1));
        if (!array_key_exists(strtolower($lang),$languages)) {
        	// if language is unknown, no syntax highlighting 
        	$p = htmlspecialchars(str_replace("\r\n","\n",$source_str));
        }
      	else {
          // do syntax highlighting
      	  $p = syntax_highlight(htmlspecialchars(str_replace("\r\n","\n",$source_str)), $languages[$lang]);
        }
      } else {
      	// if language is unknown, no syntax highlighting 
        $p = htmlspecialchars(str_replace("\r\n","\n",$source_str));
      }
      
      $preStrings[$i] = $p; 
      $i++;
    }
    
    // save latex forumlae, if neccessary
    if (defined('LATEX_AVAILABLE') && LATEX_AVAILABLE) {
      require_once BASE.'/lib/lib-latex/index.php';
      $latex = new LatexRender(LATEX_RENDER_PATH_PICTURES,LATEX_RENDER_HTTP_PATH_PICTURES,LATEX_RENDER_PATH_TEMP);
      
      $texStrings = array(); 
      $tex_str = '';
      $i=0;
      while ($str = stristr($text,'[tex]')) {
        // if we have no closing tag, we better ignore this one and all following
        if (($posClosing = stripos($str,'[/tex]')) === false) {
            break;
        }

        $str = substr($str, 0, $posClosing+6);
        $tex_str = substr($str,5,-6);
        $text = str_replace($str, "***tex_string***$i", $text);
        // do actual latex rendering
        $url = $latex->getFormulaURL($tex_str);
        if ($url != false) {
          $texStrings[$i] = '<img class="tex" src="'.$url.'" title="'.$tex_str.'" alt="'.$tex_str.'" />';
        } else {
          $texStrings[$i] = '[Unparseable or potentially dangerous latex formula. Error '.$latex->_errorcode.' '. $latex->_errorextra.']';
        }
        $i++;
      }
    }

    // search and replace all not-[url]ed urls
    $text = BBCodeParser::detectURLs($text);
    
    // stack for bbcode-tags
    $tag_stack = array();
    // stack for the text without tags
    $text_stack = array();
    // stack with the number of list entries in the [li] currently worked at
    $list_stack = array();

    // escape special html characters to avoid XSS
    $text = htmlspecialchars($text, ENT_NOQUOTES);
    
    // offset in $text to search from
    $offset = 0;
    // position, which the last char was found at
    $pos = 0;
    // true, if last found valid tag was a closing one
    $wasClosingTag = false;
    
    // find position of first opening bracket
    // verify, that an opening tag was found
    while (($pos=strpos($text,'[',$offset)) !== false) {
      // check, if last tag was a closing one
      if ($wasClosingTag) {
        // if so, append text to last entry on text stack
        $textEl = array_pop ($text_stack);
        $textEl .= substr($text, $offset, $pos-$offset);
        array_push ($text_stack, $textEl);
      } else {
        array_push ($text_stack, substr($text, $offset, $pos-$offset));
      }
      
      // find next closing ']'
      $offset = $pos+1;
      $pos=strpos($text,']',$offset);
      $tag = substr($text, $offset, $pos-$offset);
      
      // instanciate new tag-object for found tag      
      $tagObj = new Tag($tag, $pos+1);
      // use extracted tag name (case insensitive, etc.)
      $tag = $tagObj->getName();
      /*echo "new tag:";
      var_dump($tagObj);*/
      
      if (!$tagObj->isValid()) {
        // if tag was not valid, skip to next character
        /*echo "not valid tag";
        var_dump($text_stack);*/
        $textEl = array_pop ($text_stack);
        $textEl .= '[';
        array_push ($text_stack, $textEl);
        $wasClosingTag = true;
        continue;
      }
      
      // try to get tag on top of stack
      $lastTag = array_pop ($tag_stack);
      if ($lastTag) {
        array_push ($tag_stack, $lastTag);
      }
      // if opening tag and nested tags are allowed by parent tag
      if ($tagObj->isOpening() && (!$lastTag || $lastTag->allowsNestedTags($tagObj))) {
        // treatment of hr tags (because they have no closing [/hr])
        if ($tag == 'hr') {
          $wasClosingTag = true;
          $textEl = array_pop ($text_stack);
          $textEl .= "<hr />";
          array_push ($text_stack, $textEl);
        } 
        // treatment of list item tags (because they have no closing [/*])
        // check neccessary condition to be inside a list first (parent tag is [list])
        elseif ($tag == '*' && $lastTag && $lastTag->getName() == 'list') {
          $wasClosingTag = true;
          $textEl = rtrim(array_pop ($text_stack));
          $listCounter = array_pop($list_stack);
          // if firstElement in List
          if ($listCounter==0) {
            $textEl .= "<li>";
          } elseif ($listCounter>0) {
            $textEl .= "</li><li>";
          }
          array_push ($list_stack, ++$listCounter);
          // update text stack
          array_push ($text_stack, $textEl);
        } else {
          // add to tag stack
          //array_push ($tag_stack, new Tag($tag,$pos+1))
          array_push ($tag_stack, clone $tagObj);
          $wasClosingTag = false;
          
          // add new counter to list stack
          if ($tag == 'list') array_push ($list_stack, 0);
        }
        
      } 
      else if ($tagObj->isOpening() && !$lastTag->allowsNestedTags($tagObj)) {
        //echo("not allowed");
        // if tag was not valid, skip to next character
        $textEl = array_pop ($text_stack);
        $textEl .= '[';
        array_push ($text_stack, $textEl);
        $wasClosingTag = true;
        continue;
      }
      // if closing tag
      else {
      	/*echo("closing by ");
        var_dump($tag);*/
        $top = array_pop ($tag_stack);
        
        if ($top && $top->getName() == $tag) {
          $matchingTag = true;
        } else {
          $matchingTag = false;
        }
        
        // if closing [li]-tag is found and it contains one or more list items
        if ($matchingTag && $tag == 'list' && array_pop($list_stack)>0) {
          // add closing </li>-html-tag to text stack
          $textEl = rtrim(array_pop ($text_stack));
          $textEl .= "</li>";
          array_push ($text_stack, $textEl);
        }
        
        // fetch two last entries of text stack
        $textEl = array_pop ($text_stack);
        $textElOld = array_pop ($text_stack);
                
        // if tag name matches top of stack one's
        if ($matchingTag) {
          // merge last two elements of text stack
          if ($top->needsAdditionalArgument()) {
            // overwrite bbcode-tag content by empty string; it will be used as an argument to html convert routine
            $textEl = '';
            $argument = substr($text,$top->getPos(),$offset-$top->getPos()-1);
          } else {
            $argument = '';
          }
          $textElOld .= $top->getOpeningHtmlTag($argument).$textEl.$top->getClosingHtmlTag();
        } 
        // if tag name did not match
        else {
          // ignore last opening and last closing text,
          // give raw text instead
          $textElOld .= (($top) ? $top->getTag() : '')
                        . $textEl . $tagObj->getTag();
          /*echo "false:";
          var_dump($textElOld);*/
        }
        
        // add merged text to text stack
        array_push ($text_stack, $textElOld);
        // mark as closing tag
        $wasClosingTag = true;
      }
      $offset = $pos+1;
    }

    // if there are unclosed tags
    // convert them back into text
    $unmatched_tag_text = '';
    
    if (count($tag_stack) > 0) {
    	// remaing (after last found [tag]) text belongs to unmatched tag
        if (!$wasClosingTag) {
            array_push($text_stack, substr($text,$offset));
        } else {
        	array_push($text_stack, array_pop($text_stack) . substr($text,$offset));
        }
    } else {
    	// in case that all tags are closed, pretend
        // that text after last found (closing) tag corresponds
        // to an unmatched tag
    	$unmatched_tag_text = substr($text,$offset);
    }
    /*var_dump($tag_stack);
    var_dump($text_stack);*/
    
    for ($t=0; $t<count($tag_stack); $t++) {
    	$unmatched_tag_text .= $tag_stack[$t]->getTag();
        $unmatched_tag_text .= $text_stack[1+$t];
    }
    
    // build return/parsed string
    if (count($text_stack) > 0) {
    	// take parsed text  and remaining text     
        $text = $text_stack[0] . $unmatched_tag_text;
    }
    
    // replace all multiple new-lines with </p><p>
    // and surround whole text with <p></p>
    // in order to get consistent XHTML paragraphs
    $text = '<p>'.preg_replace('/\r?\n(\r?\n)+/','</p><p>',$text).'</p>';
    // remove empty paragraphs
    //var_dump($text);
    $text = preg_replace('#\<p\>\s*\<\/p\>#','',$text);
    //var_dump(substr($text,0,3));
    // remove surrounding <p></p>, if text is single paragraph
    if (!preg_match('#</p>#',substr($text,3,-4)) && substr($text,0,3)== '<p>' ) {
        $text = substr($text,3,-4);
    }
    //var_dump($text);
    // replace all single new-lines with <br />
    $text = nl2br($text);
        
    // restore preformatted texts
    for( $i=0; $i<count($preStrings); $i++ ) {	
      // determine number of lines
      $lines = 0;
      str_replace("\n","\n",$preStrings[$i], &$lines);
      // generate string of line numbers
      $ll = '';
      // we have at least one line
      $lines = max($lines,1);
      for ($l=1; $l<=$lines; $l++ ) {
      	$ll .= $l . "\n";
      }
      
      // if text is in a paragraph, we have
      // to break the paragraph, because in 
      // XHTML a table must not be contained in a <p>
      $str = "<table class=\"source\"><tr><td class=\"ln\"><pre>".$ll."</pre></td><td class=\"source\"><pre>" . $preStrings[$i] . "</pre></td></tr></table>";
      if (substr($text,0,3) == '<p>') {
      	$str = '</p>' . $str;
      }
      if (substr($text,-4) == '</p>') {
        $str = $str . '<p>';
      }
      $text = str_replace( "***pre_string***$i", $str, $text );
    }
    
    // insert latex texts, if neccessary
    if (defined('LATEX_AVAILABLE') && LATEX_AVAILABLE) {
      for( $i=0; $i<count($texStrings); $i++ ) {
        $text = str_replace( "***tex_string***$i", $texStrings[$i], $text );
      }
    }

    return $text;
  }

}

?>
