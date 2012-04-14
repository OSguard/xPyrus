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


require_once CORE_DIR . '/utils/input_validator.php';
require_once CORE_DIR . '/businesslogic/business_logic_controller.php';

/**
 *  @todo your includes here 
 */
 
define('MEDIACENTER_TEMPLATE_DIR', 'modules/mediacenter/');

/**
 * @author
 * @version $Id: media_center_business_logic_controller.php 5807 2008-04-12 21:23:22Z trehn $
 */
class MediaCenterBusinessLogicController extends BusinessLogicController {
    
    public function __construct($ajaxView = false) {
        parent::__construct($ajaxView);
    }   


   /** 
     * default method
     * @return string
     */
    protected function getDefaultMethod() {
        return 'foo';
    }
    
    /**
      * list of all methods that are allowed
      * @return string
      */
    public function getAllowedMethods() {
        $array = parent::getAllowedMethods();
        array_push($array, 
                   'foo');
        return $array;
      }
    
    protected function foo(){
        
    }
}
?>
