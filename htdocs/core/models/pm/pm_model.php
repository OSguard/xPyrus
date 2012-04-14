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
/*
 * Created on 26.07.2006 by schnueptus
 * sunburner Unihelp.de
 */
require_once MODEL_DIR . '/base/interactive_user_element_model.php';
require_once MODEL_DIR . '/base/entry_attachment_model.php';

require_once MODEL_DIR . '/pm/pm_entry_model.php';

require_once CORE_DIR . '/constants/value_constants.php';

require_once CORE_DIR . '/utils/client_infos.php'; 
 
class PmModel extends InteractiveUserElementModel {
    
    public function __construct() {
        parent::__construct();    
    }
    
    public static function nonLinearCounter($total, $position){
        return parent::nonLinearCounter($total, $position);
    }
    
    /**
     * replaces authorship of author by newAuthor
     * @param UserModel
     * @param UserModel
     * 
     * @note the id param is not working now; it could be extended to restrict the replacement to entries given by $id-param
     */
    public static function replaceAuthor($author, $newAuthor, $id = null) {
        return parent::replaceAuthor('pm', $author, $newAuthor, $id);
    }
    
    /**
     * change read Status of PM
     * @param array if ids
     * @param UserModel
     * @param boolean
     */
    public static function setRead($Ids, $user, $read = true){
        
        $DB = Database :: getHandle();
        
        foreach($Ids as $key => $addId){
            $Ids[$key] = $DB->Quote($addId);
        }
        
        $idString = implode(',',$Ids);
        
        $q = 'UPDATE ' . DB_SCHEMA . '.pm_for_users SET is_unread = ' . $DB->quote(Database::makeBooleanString(!$read)) .
                ' WHERE pm_id IN ('.$idString.')
                    AND user_id_for = ' . $DB->quote($user->id);
        #var_dump($q);            
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }            
    }
    
    /**
     * delets the PM for user is resiver
     * @param array if ids
     * @param UserModel
     */
    public static function delForUser($Ids, $user){
        
        $DB = Database :: getHandle();     
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.pm_for_users 
                       WHERE pm_id IN (' . Database::makeCommaSeparatedString($Ids) . ')
                         AND user_id_for =' . $DB->quote( $user->id );
                    
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }
    
    public static function delForAuthor($Ids, $user){
        
        $DB = Database :: getHandle();       
        
        $keyValues = array('author_has_deleted' => $DB->Quote(Database::makeBooleanString(true)) );
        
        $q = self::buildSqlStatement('pm', $keyValues, false, 'id IN (' . Database::makeCommaSeparatedString($Ids) . ')
                                                                 AND author_int ='.$DB->quote($user->id));
        $res = $DB->execute($q);

        //var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }
} 
 
 
?>
