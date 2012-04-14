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

# $Id: bbcode_null_parser.php 5807 2008-04-12 21:23:22Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/parser/bbcode_null_parser.php $

require_once CORE_DIR . '/interfaces/parse_strategy.php';
require_once CORE_DIR . '/utils/input_validator.php';

require_once CORE_DIR.  '/parser/tag.php';

/**
 * class for removing bbcode safely
 * @package Modules
 * @version $Id: bbcode_null_parser.php 5807 2008-04-12 21:23:22Z trehn $
 */
class BBCodeNullParser implements ParseStrategy {
    /**
     * Parses a string regarding BBCode and strips all BBCode tags.
     * @param string $text to be parsed.
     * @return string stripped string
     */
    public function& parse( &$text ) {
        $offset = 0;
        $newText = '';
        
        while (($pos=strpos($text,'[',$offset)) !== false) {
            $newText .= substr($text, $offset, $pos - $offset);           
            // find next closing ']'
            $offset = $pos + 1;
            $pos = strpos($text,']',$offset);
            if ($pos === false) {
                break;
            }
            $tag = substr($text, $offset, $pos-$offset);
          
            // instanciate new tag-object for found tag
            $tagObj = new Tag($tag, $pos+1);
            // if we have found a possibly valid bbcode tag, skip it
            // in parsed text
            if (!$tagObj->isValid()) {
                $newText .= '[' . $tag . ']';
            }
            $offset = $pos + 1;
        }
        $newText .= substr($text, $offset);
        $text = $newText;
        
        return $text;
    }

}

?>
