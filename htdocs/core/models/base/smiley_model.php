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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/smiley_model.php $

require_once MODEL_DIR.'/base/base_model.php';

/**
  * @class SmileyModel
  * @brief This is the class representing a country.
  * 
  * @author linap
  * @version $Id: smiley_model.php 6210 2008-07-25 17:29:44Z trehn $
  * @copyright Copyright &copy; 2006, Unihelp.de
  * 
  * The following properties are available via __get-magic:
  * 
  * <ul>
  * <li><var>id</var>           <b>int</b>          id of country</li>
  * <li><var>name</var>         <b>string</b>       country's name</li>
  * <li><var>nationality</var>  <b>string</b>       country's nationality</li>
  * <li><var>areaCode</var>     <b>string</b>       area code (telephone)</li>
  * <li><var>zipCode</var>      <b>string</b>       zip code</li>
  * <li><var>zipCodeLength</var>    <b>string</b>   length of zip code</li>
  * </ul>
  * 
  * @package Models/Base
  */
class SmileyModel extends BaseModel {
    protected $text;
    protected $textAlternative;
    protected $url;
    
    public function __construct($id = null, $text = null, $textAlternative = null,
                                $url = null) {
        $this->id = $id;
        $this->text = $text;
        $this->textAlternative = $textAlternative;
        $this->url = $url;
    }
    
    public static function getSmileyById($id) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, text, text_alternative, url
                FROM ' . DB_SCHEMA . '.smileys
               WHERE id = ' . $DB->Quote($id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $smiley = new SmileyModel($res->fields['id'], $res->fields['text'],
                                 $res->fields['text_alternative'], $res->fields['url']);
        
        return $smiley;
    }
    
    /**
     * Collect all smileys
     * 
     * @return array array of SmileyModel
     */
    public static function getAllSmileys($withSpecialSmileys = true) {
        $DB = Database::getHandle();
       
        // fetch all smileys but ignore the country smileys
        $q = 'SELECT id, text, text_alternative, url
                FROM ' . DB_SCHEMA . '.smileys'; 
        
        if (!$withSpecialSmileys) {
            $q .= ' WHERE url NOT LIKE \'%countries/%\'';
        }

        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $countries = array();
        
        foreach($res as $row) {
            $smileys[] = new SmileyModel($row['id'], $row['text'],
                                 $row['text_alternative'], $row['url']);
        }

        return $smileys;
    }
    
    public function getText() {
        return $this->text;
    }
    public function getTextAlternative() {
        return $this->textAlternative;
    }
    public function getURL() {
        return $this->url;
    }
    
}
?>
