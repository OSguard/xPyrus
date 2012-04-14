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

# $Id: parser_factory.php 6210 2008-07-25 17:29:44Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/parser/parser_factory.php $

require_once MODEL_DIR . '/base/base_entry_model.php';
require_once CORE_DIR . '/global_settings.php';

/**
 * Encapsulates all access to Parsers, so that classes don't
 * need to care about which Parser to use for which $parseSettings
 *
 * @package Parser
 */
class ParserFactory {
    /**
     * Creates a ParseStrategy based on parseSettings.
     *
     * @param array $parseSettings associative array defining requested parseSettings, see BaseEntryModel
     * @return array array of ParseStrategy, that should be applied to fulfill requested parseSettings
     */
    public static function createParserFromSettings($parseSettings, $parseOptions = null) {
        $parserArray = array();

        // assume that no other parser will be chosen,
        // so some basic formatting will be needed
        $needSimpleParser = true;
        
        if (array_key_exists(BaseEntryModel::PARSE_AS_HTML, $parseSettings)
                and $parseSettings[BaseEntryModel::PARSE_AS_HTML]) {
            //array_push($parserArray, new HTMLParser);
        }
        if (array_key_exists(BaseEntryModel::PARSE_AS_FORMATCODE, $parseSettings)
                and $parseSettings[BaseEntryModel::PARSE_AS_FORMATCODE]) {
            include_once CORE_DIR.'/parser/bbcode_parser.php';
            array_push($parserArray, new BBCodeParser(GlobalSettings::getGlobalSetting('ENTRY_MAX_INLINE_IMAGES')) );
            // basic parsing done by BBCodeParser; no need for extra
            $needSimpleParser = false;
        }
        
        // if no "real" parser is going to be applied, use SimpleParser
        // to avoid XSS / HTML-injection
        if ($needSimpleParser) {
            include_once CORE_DIR.'/parser/simple_parser.php';
            array_push($parserArray, new SimpleParser() );
        }
        
        if (array_key_exists(BaseEntryModel::PARSE_AS_SMILEYS, $parseSettings)
                and $parseSettings[BaseEntryModel::PARSE_AS_SMILEYS]) {
            include_once CORE_DIR.'/parser/smiley_parser.php';
            
            if(array_key_exists(BaseEntryModel::PARSE_MAX_SMILEYS, $parseSettings)){
                $entryMaxSmilies = $parseSettings[BaseEntryModel::PARSE_MAX_SMILEYS];
            }else{
                $entryMaxSmilies = GlobalSettings::getGlobalSetting('ENTRY_MAX_SMILEYS');
            }
            array_push($parserArray, new SmileyParser($entryMaxSmilies) );
        }
        
        return $parserArray;
    }
    
    public static function getFormatcodeNullParser() {
        include_once CORE_DIR.'/parser/bbcode_null_parser.php';
        return new BBCodeNullParser;
    }
    
    /**
     * parses a string with respect to FORMATCODE and SMILEYS
     * @param string $text text to parse
     * @return string
     */
    public static function parseWithDefaultSettings(&$text) {
    	$parsers = self::createParserFromSettings( 
                array(BaseEntryModel::PARSE_AS_FORMATCODE => 'true',
                      BaseEntryModel::PARSE_AS_SMILEYS    => 'true' ) );
        foreach ($parsers as $p) {
        	$text = $p->parse($text);
        }
        return $text;
    }
    
    public static function getRawParser() {
        include_once CORE_DIR.'/parser/raw_parser.php';
        return new RawParser;
    }
}

?>
