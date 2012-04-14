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

require_once MODEL_DIR . '/event/event_playlist.php';

// the directory, where your templates are placed 
define('EVENT_TEMPLATE_DIR', 'modules/event/');

/**
 * @package controller
 * @author YOU
 * @version $Id: skeleton_business_logic_controller.php 4384 2007-05-13 15:55:41Z trehn $
 */
class EventBusinessLogicController extends BusinessLogicController {
    
    public function __construct($ajaxView = false) {
        parent::__construct($ajaxView);
    }
    
    // ----------------------------------------------------------------------
    // NECCESSARY OVERRIDDEN METHODS
    //
    
    /**
     * set methods that are allowed for calling by the controller
     */
    protected function getAllowedMethods() {
        return array_merge(parent::getAllowedMethods(),array(
                // does something, call via index.php?mod=${myControllerName}&method=foo
                'playlist')
            );
    }
    
    /**
     * method, that is executed if no one is explicity called
     * @return string
     */
    protected function getDefaultMethod() {
        return 'playlist';
    }
    
    public function getMethodObject($method) {
        if ('playlist' == $method) {
            return new BLCMethod('PlayList-Party in der Festung', '', BLCMethod::getDefaultMethod());
        }
        return parent::getMethodObject($method);  
     }
    
    /**
     * collects and preprocesses REQUEST parameters for the named method
     * @param string method name
     */
    protected function collectParameters($method) {
        // array to store our parameters in
        $parameters = array();
        
        if ('playlist' == $method) {
        	if(array_key_exists('save',$_POST)){
        		$parameters['save'] = true;
                $formFields = array(
                'artist_1'      => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'song_1'        => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'artist_2'      => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'song_2'        => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'artist_3'      => array('required' => false, 'check' => 'isValidAlmostAlways'),
                'song_3'        => array('required' => false, 'check' => 'isValidAlmostAlways'),
                                );
            // validate input according to our specification
            // and store ESCAPEd values in our $parameters array 
            $this->validateInput($formFields, $parameters);
            
        	} else{
        		$parameters['save'] = false;
        	}
            
        }
        
        // save collection for further use
        $this->_parameters[$method] = $parameters;

        // give our parent the possibility to collect some parameters on its own
        parent::collectParameters($method);
    }
    
    
    
    // ----------------------------------------------------------------------
    // OWN METHODS - world callable
    //
    
    /**
     * for Playlist Party 05.12.2007 Festung Mark
     */
    protected function playlist() {
        // get the expected request parameters
        $parameters = $this->getParameters('playlist');

        $visitor = Session::getInstance()->getVisitor();
        
        if(!$visitor->isLoggedIn()){
        	$this->errorView(ERR_NO_LOGGIN);
        }
        if($visitor->isExternal()){
            $this->errorView(ERR_NO_EXTERN);
        }
        
        $countSongs = EventPlaylistModel::countSongs($visitor->id);
        // instanciate view to operate on
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, EVENT_TEMPLATE_DIR . 'playlist.tpl');
        
        if ($parameters['save'] && count($this->errors) == 0) {
        	
            if(!empty($parameters['artist_1']) && !empty($parameters['song_1']) && $countSongs < 3){
                $song1 = new EventPlaylistModel();
                $song1->setArtist($parameters['artist_1']);
                $song1->setSong($parameters['song_1']);
                $song1->setUserId($visitor->id);
                $song1->save();
                $countSongs += 1;
        	}
            if(!empty($parameters['artist_2']) && !empty($parameters['song_2']) && $countSongs < 3){
                $song2 = new EventPlaylistModel();
                $song2->setArtist($parameters['artist_2']);
                $song2->setSong($parameters['song_2']);
                $song2->setUserId($visitor->id);
                $song2->save();
                $countSongs += 1;
            }
            if(!empty($parameters['artist_3']) && !empty($parameters['song_3']) && $countSongs < 3){
                $song1 = new EventPlaylistModel();
                $song1->setArtist($parameters['artist_3']);
                $song1->setSong($parameters['song_3']);
                $song1->setUserId($visitor->id);
                $song1->save();
                $countSongs += 1;
            }
        }
        
        $main->assign('countSongs',$countSongs);
        
        // set central content and do not show an ad banner or breadcrumbs navigation
        $this->setCentralView($main, true, true);
        
        // display the template
        $this->view();
    }
    
}

?>
