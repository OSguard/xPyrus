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

require_once MODEL_DIR . '/base/base_model.php';

/**
 * Represent a click on a banner with all 
 * information.
 * 
 * @author Kyle
 * @package Models
 * @copyright Copyright &copy; 2006, Unihelp.de
 */
class BannerClickModel extends BaseModel{
    /** the banner for which the click is registred */
    public $bannerId;
    
    /** the user who clicked on the banner */
    public $user_int_id;

    /** the user who clicked on the banner */
    public $user_ext_id;

    /** the ip that clicked on the banner */
    public $ip;

    /** the entry time for this click */
    public $clickTime;
    
    public function save() {

        $DB = Database::getHandle();
        
        $keyValue = array();

        $keyValue['banner_id'] = $DB->quote($this->bannerId);
        
        if(is_numeric($this->user_int_id) || is_numeric($this->user_ext_id)) {
            if ($this->user_int_id != null) {
                $keyValue['user_int'] = $DB->quote($this->user_int_id);
            } else if ($this->user_ext_id != null) {
                $keyValue['user_ext'] = $DB->quote($this->user_ext_id);
            }
        }
        
        $keyValue['ip'] = $DB->quote($this->ip);
        
        $q = '';
        
        /* insert or update? */
        if($this->id != null) {
            $q = $this->buildSqlStatement('banner_clicks', $keyValue, false, 'id = ' . $DB->quote($this->id));
        } else {
            $q = $this->buildSqlStatement('banner_clicks', $keyValue);
        }
        
        $res = $DB->execute($q);
        if(!$res) {
            throw new DBException(Logging::getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }
}

?>
