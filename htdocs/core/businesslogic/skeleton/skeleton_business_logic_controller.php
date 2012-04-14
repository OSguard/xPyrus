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

/**
 *  @todo your includes here 
 */

require_once CORE_DIR . '/utils/input_validator.php';
require_once CORE_DIR . '/businesslogic/business_logic_controller.php';

// the directory, where your templates are placed 
define('SKELETON_TEMPLATE_DIR', 'modules/skeleton/');



// internal class - DON'T DO THAT AT HOME ;)
// every class should be placed in a separate file!
require_once MODEL_DIR . '/base/base_model.php'; 
class MyCrazyModel extends BaseModel {
    protected $firstName;
    protected $lastName;
    public function __construct() { parent::__construct(); }
    public function setFirstName($n) { $this->firstName = $n;}
    public function setLastName($n) { $this->lastName = $n;}
    public function getFirstName() { return $this->firstName;}
    public function getLastName() { return$this->lastName;}
    public function save() { /* nothing to do here for now */ }
}




/**
 * @package controller
 * @author YOU
 * @version $Id: skeleton_business_logic_controller.php 5807 2008-04-12 21:23:22Z trehn $
 */
class SkeletonBusinessLogicController extends BusinessLogicController {
    
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
                'foo',
                
                // does something else, call via index.php?mod=${myControllerName}&method=magic
                'magic',
                
                // last example method
                'advancedMagic')
            );
    }
    
    /**
     * method, that is executed if no one is explicity called
     * @return string
     */
    protected function getDefaultMethod() {
        return 'foo';
    }
    
    /**
     * collects and preprocesses REQUEST parameters for the named method
     * @param string method name
     */
    protected function collectParameters($method) {
        // array to store our parameters in
        $parameters = array();
        
        // collect parameters for our 'magic' method below
        if ('magic' == $method) {
            // safely access $_REQUEST['number']
            // 'safely' refers to PHP errors only -- no escaping is done here!
            $parameters['number'] = InputValidator::getRequestData('number', 0);
        }
        else if ('advancedMagic' == $method) {
            // form fields/REQUEST parameters and their requirements
            //
            // firstName is not required, so no error is issued if this field is missing
            // lastName is required; if it doesn't exist, an error is detected
            // both input fields are also checked, if they seem to be valid human names
            //    (i.e. do not contain fancy characters)
            // further validation methods can be found in utils/InputValidator class
            $formFields = array(
                'firstName'      => array('required' => false, 'check' => 'isValidName'),
                'lastName'       => array('required' => true,  'check' => 'isValidName'),
                                );
            // validate input according to our specification
            // and store ESCAPEd values in our $parameters array 
            $this->validateInput($formFields, $parameters);
        }
        /** @todo your methods here */
        
        
        // save collection for further use
        $this->_parameters[$method] = $parameters;

        // give our parent the possibility to collect some parameters on its own
        parent::collectParameters($method);
    }
    
    
    
    // ----------------------------------------------------------------------
    // OWN METHODS - world callable
    //
    
    /**
     * method callable from the world
     */
    protected function foo() {
        // simply show the template
        $this->simpleView(SKELETON_TEMPLATE_DIR . 'foo.tpl');
    }
    
    /**
     * another method callable from the world
     * 
     * call via index.php?mod=${myControllerName}&method=magic&number={$myFancyNumber}
     */
    protected function magic() {
        // get the expected request parameters
        $parameters = $this->getParameters('magic');
        
        // call an interal method operating on $_REQUEST['number']
        $v = $this->doSomePreparations($parameters['number']);
        
        // instanciate view to operate on
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, SKELETON_TEMPLATE_DIR . 'another_template.tpl');
        
        // assign a variable to our template
        $main->assign('answer', $v);
        
        // automatically assigned variables are:
        // - 'visitor' (UserModel) the visiting user
        // - 'local_city' (CityModel) the city this page is display for
        
        // set central content and do not show an ad banner or breadcrumbs navigation
        $this->setCentralView($main, false, false);
        
        // display the template
        $this->view();
    }
    
    /**
     * advanced example
     * 
     * call via index.php?mod=${myControllerName}&method=magic&firstName={$myFirstName}&lastName={$myLastName}
     */
    protected function advancedMagic() {
        // get the expected request parameters
        $parameters = $this->getParameters('advancedMagic');
        
        // check for errors
        if (count($this->errors) == 0) {
            // if no error occured,
            // save input data in a model
            // (after it has been escaped!)
            $model = new MyCrazyModel();
            $model->setFirstName($parameters['firstName']);
            $model->setLastName($parameters['lastName']);
            $model->save();
        }
        
        // instanciate view to operate on
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, SKELETON_TEMPLATE_DIR . 'form_template.tpl');
        
        // assign variables to our template
        $main->assign('first_name', $parameters['firstName']);
        $main->assign('last_name', $parameters['lastName']);
        
        // possible errors from $this->errors
        // are automatically assigned to the templates
        // via the variable central_errors
        
        // set central content and do not show an ad banner or breadcrumbs navigation
        $this->setCentralView($main, false, false);
        
        // display the template
        $this->view();
    }
    
    
    
    // ----------------------------------------------------------------------
    // OWN METHODS - not world callable, internal only
    //
    
    /**
     * not callable from world, for internal use only
     * @param int
     * @return int
     */
    protected function doSomePreparations($i) {
        if ($i > 23) {
            return 'big';
        } else {
            return 'small';
        }
    }
}

?>
