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

# $Id: simple_parser.php 5807 2008-04-12 21:23:22Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/parser/simple_parser.php $


require_once CORE_DIR.'/interfaces/parse_strategy.php';

/**
 * class for eliminating malicious parts of user entries
 * @package Parser
 * @version $Id: simple_parser.php 5807 2008-04-12 21:23:22Z trehn $
 */
class SimpleParser implements ParseStrategy {
    public function __construct() {
    }
  
    /**
     * escapes HTML tags in given string and formats new-lines.
     * @param string text to be parsed.
     * @return string string in HTML format
     */
    public function& parse( &$text ) {
        // escape some characters to avoid XSS
        $text = htmlspecialchars($text, ENT_NOQUOTES);
        
        // replace all multiple new-lines with </p><p>
        // and surround whole text with <p></p>
        // in order to get consistent XHTML paragraphs
        $text = '<p>'.preg_replace('/\r?\n(\r?\n)+/','</p><p>',$text).'</p>';
        // remove surrounding <p></p>, if text is single paragraph
        if (!preg_match('#<p>#',substr($text,3,-4))) {
            $text = substr($text,3,-4);
        }
        // replace all single new-lines with <br />
        $text = nl2br($text);
        
        return $text;
    }

}

?>
