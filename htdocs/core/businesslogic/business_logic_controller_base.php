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

// $Id: business_logic_controller_base.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/businesslogic/business_logic_controller_base.php $

require_once CORE_DIR . '/base_controller.php';
require_once CORE_DIR . '/views/view_factory.php';
require_once CORE_DIR . '/utils/attachment_handler.php';
require_once CORE_DIR . '/utils/input_validator.php';
require_once CORE_DIR . '/interfaces/i_with_attachments.php';
require_once CORE_DIR . '/blc_method.php';

require_once MODEL_DIR . '/base/city_model.php';
require_once MODEL_DIR . '/base/file_model.php';

// TODO: make include dependent on user's language
require_once BASE . '/lang/de.php.inc';


abstract class BusinessLogicControllerBase extends BaseController {
    /**
     * the smarty view we use for output
     * @var SmartyView
     */
    private $smartyView;

    /**
     * array of userspace-errors that have occured during the process
     * @var array
     */
    protected $errors;

    /**
     * cache for method parameters
     * @var array
     */
    protected $_parameters = array();

    protected static $TEMPLATE_FOR_USER = USER_TEMPLATE_DIR;
    
    protected $ajaxView;
    
    /**
     * @var boolean
     */
    protected $previewMode;
    /**
     * @var boolean
     */
    protected $uploadMode;
    
    /** Konstruktor */
    public function __construct($ajaxView = false) {
        parent::__construct();
        $this->ajaxView = $ajaxView;
        
        if (!$ajaxView) {
            $this->initializeSmartyView(&$this->smartyView);
                     
            // mark active session user as online
            // if he has not already been in "users online" list
            // his rights will be reloaded
            Session::getInstance()->getVisitor()->markAsOnline();
            
            // some variables for dealing with entries
            // check, if this page is called in entry-preview mode
            $this->previewMode = array_key_exists('preview_submit', $_REQUEST) || 
                    (array_key_exists('preview_flag', $_REQUEST) && !array_key_exists('save', $_REQUEST) && !array_key_exists('makeVisible', $_REQUEST));
            
            // check, if this page is called in entry-upload mode
            $this->uploadMode = array_key_exists('upload_submit', $_REQUEST);
            
            // a click on an attachment-delete button leads into upload mode, too
            foreach ($_POST as $key => $val) {
                if (preg_match('/delattach(-?\d+)/', $key, $matches)) {
                    $this->uploadMode = true;
                }
            }
        }
        
        // no errors so far
        $this->errors = array();
    }
    
    abstract protected function initializeSmartyView(&$view);
    
    protected function getSmartyView() {
        return $this->smartyView;
    }
    
    
    /**
     * sets central content view
     * @param SmartyView
     */
    protected function setCentralView($view) {
        $this->smartyView->setCentralView($view);
    }
    
    protected function view($properDisplay = true) {
        // error display
        $this->smartyView->assign('central_errors', $this->errors);
        
        // TODO: visitor is assigned in SmartyView::assignVariables
        //       can we move it hither?
        
        if ($properDisplay) {
            $this->smartyView->assign('requested_method', $this->getMethodObject($this->method));
        }
        
        // display
        $this->smartyView->display();
    }
    
    /**
     * "displays" a file; direct output; file path and name are taken from given model
     * @param FileModel
     */
    protected function fileView($fileModel) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileModel->getFilename() . '"; size=' . $fileModel->getFileSize());
        // print file given by absolute path
        readfile($fileModel->getFilePathAbsolute());
    }
    
    /**
     * simply displays a template with 1 day caching
     */
    protected function simpleView($template) {
        $main = ViewFactory::getSmartyView(self::$TEMPLATE_FOR_USER, $template);
        if (defined('CACHETEST')) {
            $main->enableCaching();
        }
        $main->setCacheParameter(86400, '');
        
        $this->setCentralView($main, false);
        $this->view();
    }
    
    /**
     * "displays" an error message in three column or fullpage mode
     * @param string $errorString
     */
    protected function errorView($errorString, $secWarning = null) {
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'error.tpl');
        // cache this page one day by md5 of given error string
        $main->setCacheParameter(86400, 'error|'.md5($errorString));
        $main->assign('error_string', $errorString);
        
        // TODO: better solution than 'last_mod' in the session, perhaps
        //       a name property of the BLC
        $main->assign('controller_url', '/index.php?mod=' . Session::getInstance()->getViewData('last_mod') . 
                    '&method=' . $this->getDefaultMethod());
        if ($this->smartyView != null) {
            $this->setCentralView($main, false, false);
            $this->view(false);
        } else {
            $main->display();
        }
        
        if (null != $secWarning) {
            Logging::getInstance()->logSecurity($secWarning, false);
        }
        
        exit;
    }
    
    /**
     * displays an error message that informs user
     * that he is missing a right for a desired action
     * 
     * the method exits PHP execution after call
     * 
     * @param string $rightName
     */
    protected function rightsMissingView($rightName) {
        $this->errorView(ERR_INSUFFICIENT_RIGHTS, 'insufficient rights for ' . $rightName);
    }
    
    public function homeView() {
        header('Location: ' . rewrite_index(array('extern' => true)));
        exit;
    }
    
    protected function showFeed($entries, $metadata, $display = true) {
        $feedVersion = InputValidator::getRequestData('feed_ver', 'rss2');
        switch ($feedVersion) { 
        case 'rss2':
        default:
            $templateFile = 'feed_rss2.tpl';
            break;
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 
            'common/' . $templateFile);
        
        if ($entries !== null) {
            $main->assign('entries', $entries);
        }
        $main->assign('metadata', $metadata);
        $main->assign('localSubdomain', CityModel::getLocalCity()->getSchemaName());
        
        if ($display) {
            // show as XML
            $main->display(true);
            return null;
        }
        return $main;
    }
    
    
    public function getMethodObject($method) {
        return BLCMethod::getDefaultMethod();
    }
    
    protected function getParameters($method) {
        if (!array_key_exists($method, $this->_parameters)) {
            $this->collectParameters($method);
        }
        return $this->_parameters[$method];
    }
    
    protected function getParameter($method, $name) {
        $param = $this->getParameters($method);
        return $param[$name];
    }
    
    protected function collectParameters($method) {
    }
    
    protected function copyParameters($srcName, $destName) {
        if (array_key_exists($destName, $this->_parameters)) {
            return;
        }
        $this->_parameters[$destName] = &$this->_parameters[$srcName];
    }
    
    /**
     * This methods validates REQUESTed input (e.g. from form fields)
     * with respect to given validation methods.
     * 
     * The method takes an associative array with the name of REQUEST
     * parameters as keys and associative arrays as values, which have
     * the following structure:
     *  - 'required': boolean   set if an not empty form field is required
     *  - 'check':    string    name of method of InputValidator the check
     *                          will be performed by
     *  - 'params':   array     additional parameters for the check-method
     *                (optional)
     *  - 'escape':   boolean   if set the REQUEST field will be escaped
     *                          via htmlspecialchars
     *                (optional, defaults to TRUE)
     * 
     * @param array specification of the form fields
     * @param array if not null, parameters are stored there
     * @return boolean true if all fields checked ok, false otherwise; returns true also if $formFields is empty!!!
     */
    protected function validateInput($formFields, &$parameters = null) {
        $allFieldsOK = true;
        foreach ($formFields as $field => $val) {
            $method = $val['check'];
            
            // wrapper for possibly not existing REQUEST variables
            if (!empty($_REQUEST[$field])) {
                $fieldValue = $_REQUEST[$field];
            } else {
                $fieldValue = null;
            }
            // params is an optional parameter in the field specification
            if (!empty($val['params'])) {
                $params = $val['params'];
            } else {
                $params = null;
            }
            
            if (!InputValidator::$method($fieldValue, $val['required'], $params)) {
                self::addIncompleteField($this->errors, $field);
                $allFieldsOK = false;
            }
            // escape value if requested
            if ($fieldValue != null and (!array_key_exists('escape', $val) or $val['escape'] == true)) {
                // TODO: this line is for compatability to old code only
                //       we ought to be able to use this method with every REQUEST method
                $_POST[$field] = htmlspecialchars($_POST[$field]);
                
                $_REQUEST[$field] = htmlspecialchars($_REQUEST[$field]);
            }
            // enforce utf-8 encoding
            if ($fieldValue != null and (!array_key_exists('forceEncoding', $val) or $val['forceEncoding'] == true)) {
                // TODO: this line is for compatability to old code only
                //       we ought to be able to use this method with every REQUEST method
                $_POST[$field] = InputValidator::requireEncoding($_POST[$field]);
                
                $_REQUEST[$field] = InputValidator::requireEncoding($_REQUEST[$field]);
            }
            
            // if we have a destination array given, we store
            // the field values there
            if ($parameters !== null) {
                if(array_key_exists($field, $_REQUEST)){
                    $parameters[$field] = $_REQUEST[$field];
                }else{
                	$parameters[$field] = null;
                }
            }
        }
        return $allFieldsOK;
    }
    
    protected static function addIncompleteField(&$errors, $field, $reason = ERR_FORM_NOT_VALID) {
        $errors['missingFields'] = $reason;
        $errors['missingFieldsObj'][$field] = true;
    }
    
    /**
     * caches array by given cache key
     * @param string
     * @param array
     */
    protected static function cacheIds($cacheKey, $ids, $clearCache = false) {
        if (!defined('CACHETEST')) {
            return;
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), '_id_cache.tpl');
        $main->enableCaching();
        $main->setCacheParameter(-1, $cacheKey);
        
        if ($clearCache) {
            $main->clearCache(null, null, true);
        }
        
        $main->assign('ids', implode(',', $ids));
        $main->fetch();
    }
    
    /**
     * caches array by given cache key
     * @param string
     */
    protected static function clearCacheIds($cacheKey) {
        if (!defined('CACHETEST')) {
            return;
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), '_id_cache.tpl');
        $main->enableCaching();
        $main->setCacheParameter(-1, $cacheKey);
        
        $main->clearCache(null, null, true);
    }
    
    /**
     * returns cached array by given cache key
     * @param string
     * @param array [out] cached array
     * @return boolean success of cache operation
     */
    protected static function getCachedIds($cacheKey, &$ids) {
        if (!defined('CACHETEST')) {
            return false;
        }
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), '_id_cache.tpl');
        $main->enableCaching();
        $main->setCacheParameter(-1, $cacheKey);
        
        if (!$main->isCached()) {
            return false;
        }
        
        $state = $main->fetch();
        $ids = explode(",", $state);
        return true;
    }
    
     /**
     * handles attachment (add and delete) requests with respect to given entry
     * 
     * @param BaseEntryModel $entry entry to add attachments to
     * @param UserModel $recvUser user whom the attachments are assigned to (concerns especially upload path)
     * @param boolean if true, method will look for attachments to be removed (via POST/delattach)
     * @return boolean false, if file was too big; otherwise true, if upload was successful 
     * @throws CoreException if arguments of method are invalid or upload failed
     */
    protected function handleAttachment($entry, $recvUser, $maxAttachmentSize, $checkForAttachmentRemove = true) {
        
        if (!array_key_exists('file_attachment1',$_FILES)) {
            return true;
        }
        
        // check validity of arguments
        if (!($entry instanceof IWithAttachments)) {
            throw new CoreException( Logging::getErrorMessage(GENERAL_ARGUMENT_INVALID, 'entry'));
        }
        if (!($recvUser instanceof UserModel)) {
            throw new CoreException( Logging::getErrorMessage(GENERAL_ARGUMENT_INVALID, 'recvUser'));
        }
        
        if ($_FILES['file_attachment1']['size']) {
            // check accumulated attachment size limit
            if ($entry->getAccumulatedAttachmentSize() + $_FILES['file_attachment1']['size'] > GlobalSettings::getGlobalSetting('ENTRY_MAX_ATTACHMENT_SIZE_CUMMULATED') * 1024) {
                return false;
            }
            
            // username of session object should not induce security risk
            // this username is contrainted by the database to digits and chars
            $atm = AttachmentHandler::handleAttachment( $_FILES['file_attachment1'],
                AttachmentHandler::getAdjointPath($recvUser), true, $maxAttachmentSize );
            // check, if uploaded file is too big
            if ($atm == null) {
                // if yes, return false
                return false;
            }
            // add attachment to object
            $entry->addAttachment( $atm );
        }
        
        // read all attachment ids that are to be deleted
        // these are in POST: delattach<id>
        // note: attachments that are not yet in the database
        // have negative ids!
        foreach ($_POST as $key => $val) {
            if (preg_match('/delattach(-?\d+)/', $key, $matches)) {
                $entry->deleteAttachmentById($matches[1]);
            }
        }
        return true;
    }
    
    protected function getAllowedMethods() {
        $array = parent::getAllowedMethods();
        array_push($array, 
            'downloadAttachment'
                  );
        return $array;
    }
    
    protected function downloadAttachment() {
        if (!Session::getInstance()->getVisitor()->isLoggedIn()) {
            $this->errorView(ERR_NO_LOGGIN);
        }
        
        if (empty($_REQUEST['atm_id']) or 
                !($atm = EntryAttachmentModel::getAttachmentById($_REQUEST['atm_id']))) {
            $this->errorView(ERR_NO_ATTACHMENT);
            return;
        }
        
        $this->fileView($atm);
    }
    
    protected static function getParseSettings() {
        $parseSettings = array();
        if (array_key_exists(F_ENABLE_FORMATCODE, $_REQUEST)) $parseSettings[BaseEntryModel::PARSE_AS_FORMATCODE] = true;
        if (array_key_exists(F_ENABLE_SMILEYS, $_REQUEST))    $parseSettings[BaseEntryModel::PARSE_AS_SMILEYS]    = true;

        return $parseSettings;
    }
    
    protected static function getAjaxParseSettings() {
        $parser = array();
        $parser[BaseEntryModel::PARSE_AS_FORMATCODE] = ($_REQUEST[F_ENABLE_FORMATCODE] == 'true');
        $parser[BaseEntryModel::PARSE_AS_SMILEYS] = ($_REQUEST[F_ENABLE_SMILEYS] == 'true');
        
        return $parser;
    }
        
    
    protected static function getSmartyDate($data, $name , $withTime = false){

        $day = $name.'Day';
        $month = $name.'Month';
        $year = $name.'Year';
        
        if(!array_key_exists($day,$data) 
            || !array_key_exists($month,$data) 
            || !array_key_exists($year,$data)){
               return false;
        }
        
        if ($withTime){
        	$hour = $name.'Hour';
            $min = $name.'Minute';
            return mktime($data[$hour], $data[$min], 0, $data[$month], $data[$day], $data[$year]);
        } else {
            return mktime(0, 0, 0, $data[$month], $data[$day], $data[$year]);
        }        
    }
}


?>
