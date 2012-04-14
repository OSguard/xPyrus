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

# $Id: raw_parser.php 5807 2008-04-12 21:23:22Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/parser/raw_parser.php $

require_once CORE_DIR . '/interfaces/parse_strategy.php';
require_once CORE_DIR . '/utils/input_validator.php';

require_once CORE_DIR.  '/parser/PorterStemmer_de.class.php';

/**
 * class for removing bbcode and umlauts
 * @package Modules
 * @version $Id: raw_parser.php 5807 2008-04-12 21:23:22Z trehn $
 */
class RawParser implements ParseStrategy {
  public function __construct() {
  }

  /**
   * removes bbcode and umlauts from a string
   * @param string $text to be parsed.
   * @return string
   */
  public function& parse( &$text ) {
    // trim text first
    $text=trim($text);
    
    // remove all bbcode-tag like structures
    $text = preg_replace('#\[/?[\w\d]+(?:=[\d\w]+)?\]#', '', $text);
    
    // remove all html entity like structures
    $text = preg_replace('/&#x?[0-9a-fA-F]{2,4};/','', $text);

    // lowercase string
    $text = mb_strtolower($text, 'UTF-8');
    
    $stemmer = new PorterStemmer_de;
    $tok = strtok($text, ' -?!,.');
    $newText = '';
    while ($tok !== false) {
        if (strlen($tok) >= V_FORUM_SEARCH_MINIMAL_LENGTH) {
        //var_dump($stemmer->stem($tok), $tok);
            $newText .= $stemmer->stem($tok) . ' ';
        }
        
        $tok = strtok(' -?!,.');
    }
    $text = $newText;
    //var_dump($newText);
    
    // translate umlauts
    /*$text = strtr($text, array(
        'ä' => 'ae',
        'ö' => 'oe',
        'ü' => 'ue',
        'Ä' => 'Ae',
        'Ö' => 'Oe',
        'Ü' => 'Ue',
        'ß' => 'ss'));*/
    
    return $text;
  }
  
  
    /**
     * prepare query, see tsearch2 manual
     * @param string
     * @return string
     * 
     * @todo integrate this function into an interface
     */
    public function parseQuery($queryString) {
        // make query string safe
        $queryString = preg_replace('/[^ßÄÖÜäöü\w\-" ]/', '', $queryString);
        // preserve negation of search terms
        $negatedSearchToken = '=====not=====';
        $queryString = preg_replace('/(^| )\-(\S)/', '$1' . $negatedSearchToken . ' $2', $queryString);
        // NOTE: àáâäåçèéêëìíîïñòóôöùúûü seems to fail later, because
        // it is getting no valid utf8. 
        // Before extending the search to other special charcters we
        // have to examine further this issue
        $this->parse($queryString);
        $queryString = trim($queryString);
        // support only negation at the moment
        $queryString = str_replace($negatedSearchToken . ' ', '!', $queryString);
        // all terms are ANDed
        $queryString = str_replace(' ', ' & ', $queryString);
        
        return $queryString;
    }

}

?>
