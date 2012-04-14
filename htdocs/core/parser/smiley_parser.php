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

# $Id: smiley_parser.php 5807 2008-04-12 21:23:22Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/parser/smiley_parser.php $


require_once CORE_DIR.'/interfaces/parse_strategy.php';

/**
 * class for parsing smileys
 * @package Parser
 * @version $Id: smiley_parser.php 5807 2008-04-12 21:23:22Z trehn $
 * @author linap
 */
class SmileyParser implements ParseStrategy {
  // maximal number of smileys that are replaced
  private $maxSmileys;
  
  public function __construct($maxSmileys=100000) {
    $this->maxSmileys = $maxSmileys;
  }
  
  /**
   * Parses a string regarding smileys and returns it in HTML format.
   * @param string text to be parsed.
   * @return string string in HTML format
   *
   * @todo more efficient parser? e.g. static hash array
   */
  public function& parse( &$text ) {
    
    require SMILEY_INCLUDE_FILE;
    // $smileys and $tags come from this include file

    // save all areas, in which smiley parsing must not be applied
    // such as <pre></pre> 
    $oldPos = 0;
    // collect text to subsitute smileys ins
    $textToSubstitute = array();
    // collect substrings not to parse
    $preStrings = array();
    while (($pos = strpos($text,'<pre>',$oldPos)) !== false) {
    	array_push($textToSubstitute, substr($text,$oldPos,$pos-$oldPos));
        $oldPos = strpos($text,'</pre>',$pos)+6;
        // append "<pre> $foo </pre>"-substring
        array_push($preStrings, substr($text,$pos,$oldPos-$pos));
    }
    // append part after last </pre>
    array_push($textToSubstitute, substr($text,$oldPos));

    // do substitution
    // use nested str_replace to obey $this->maxSmileys limit
    // preg_replace allows limiting only per smileys and not per complete substitution process
    // additionally, preg_replace is slower than str_replace

    // number of substitutions made so far
    $substCount = 0;

    for ($i = count($smileys)-1; $i>=0; --$i) {
    	// parse only strings that are wanted to be parsed
        foreach ($textToSubstitute as &$text) {
            $lastCount = 0;
            // do substitution and look ahead ...
            $text = str_replace($smileys[$i], $tags[$i], $text, $lastCount);
            $substCount += $lastCount;
          
            // if substitutions exceeded limit, undo
            if ($substCount > $this->maxSmileys) {
                $text = str_replace($tags[$i], $smileys[$i], $text);
                // replace exact $this->maxSmileys smileys in total
                $text = preg_replace('/' . preg_quote($smileys[$i]). '/', $tags[$i], $text, $lastCount - ($substCount - $this->maxSmileys));
                $substCount -= $lastCount;
                break;
            }
            // if substitutions will exceed limit next loop, exit loop
            else if ($substCount == $this->maxSmileys) {
                break;
            }
        }
    }

    // rebuild original string from parsed and unparsed parts
    // can't use $text here, PHP stumbles over references ...
    $mytext = '';
    for ($i=0; $i<count($textToSubstitute)-1; $i++) {
    	$mytext .= $textToSubstitute[$i] . $preStrings[$i];
    }
    $mytext .= $textToSubstitute[count($textToSubstitute)-1];
    $text = $mytext;
    
    return $text;
  }

}

?>
