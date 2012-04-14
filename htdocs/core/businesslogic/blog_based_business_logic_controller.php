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

// $Id: blog_based_business_logic_controller.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/businesslogic/blog_based_business_logic_controller.php $

require_once CORE_DIR . '/businesslogic/business_logic_controller_base.php';

class BlogBasedLogicController extends BusinessLogicControllerBase {

    /** Konstruktor */
    function __construct($ajaxView = false) {
        // TODO: improve view=ajax concept
        //       so that we get rid of the evil hack below
        //            (linap, 17.06.07)
        
        // we want no ajax view because
        // we need the instanciation
        // of smarty view in out parent
        parent::__construct(false);
        // we want ajax view here, because we MUST NOT 
        // store last_get_array
        $this->ajaxView = true;
    }
    
    protected final function initializeSmartyView(&$view) {
        $view = ViewFactory::getSmartyBlogView(self::$TEMPLATE_FOR_USER);
    }
    
    protected function setCentralView($view, $blogModel) {
        $this->getSmartyView()->assign('blog_model', $blogModel);
        parent::setCentralView($view);
    }
    
    /**
     * überschriebene process Methode. Wir suchen unsere
     * destination und überlassen dem ganzen die Arbeit.
     */
    public function process() {
        $this->destinationModul();
    }
       
    /**
     * Das Ziel ist das jetzige Module. Wir nutzen einfach 
     * die implementierte Methode der Basisklasse
     */
    private function destinationModul() {
        // store GET-parameter in session
        // if it is not an ajax function

        if (!$this->ajaxView) {
            Session::getInstance()->storeViewData('last_get_array', $_GET);
        }
                
        parent::process();
    }
    
    protected static function getParseSettings() {
        $parseSettings = array();
        if (array_key_exists(F_ENABLE_FORMATCODE, $_REQUEST)) $parseSettings[BaseEntryModel::PARSE_AS_FORMATCODE] = true;
        if (array_key_exists(F_ENABLE_SMILEYS, $_REQUEST))    $parseSettings[BaseEntryModel::PARSE_AS_SMILEYS]    = true;

        return $parseSettings;
    }
}

?>
