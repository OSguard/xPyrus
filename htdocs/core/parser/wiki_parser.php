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

# $Id: wiki_parser.php 5807 2008-04-12 21:23:22Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/parser/wiki_parser.php $


require_once CORE_DIR.'/interfaces/parse_strategy.php';

define('WIKI_PHP_FILE', '/wiki.php');

/**
 * class for parsing wiki entries
 * @package Parser
 * @version $Id: wiki_parser.php 5807 2008-04-12 21:23:22Z trehn $
 */
class WikiParser implements ParseStrategy {
  // namespace the wiki entry is in
  private $namespace;
  
  public function __construct($namespace) {
    $this->namespace = $namespace;
  }
  
  /**
   * Parses a string regarding wiki formattings.
   * @param string text to be parsed.
   * @return string string in HTML format
   *
   * @todo extend functionality
   */
  public function& parse( &$text ) {        
    // mask allowed <br />-tag by double new-line
		$text = preg_replace('#<br />#', "\n\n", $text);
		// destroy all other html-tags
    // and escape entities
		//$text = htmlspecialchars( $text );
    $text = htmlentities($text);
		
		// replace in-wiki,in-namespace links
		$text = preg_replace('/\[\[([A-Za-z0-9]+)\]\]/',
			'<a href="'.WIKI_PHP_FILE.'?wiki='.$this->namespace.'.\1">\1</a>', $text);
    // replace in-wiki,off-namespace links
		$text = preg_replace('/\[\[([A-Za-z0-9]+)\.([A-Za-z0-9]+)\]\]/',
			'<a href="'.WIKI_PHP_FILE.'?wiki=\1.\2">\1.\2</a>', $text);
		// replace triple-quoted by bold text
		$text = preg_replace('/\'\'\'([^\']+)\'\'\'/',
			'<b>\1</b>', $text);
		// replace double-quoted by bold text
		$text = preg_replace('/\'\'([^\']+)\'\'/',
			'<i>\1</i>', $text);
		// replace double line-break by <br />-tag
		$text = preg_replace('/\n\r?\n/',
			'<br />', $text);
    
    return $text;
  }

}

?>
