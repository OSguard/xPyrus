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

// $Id: business_logic_controller.php 5867 2008-05-03 10:10:50Z schnueptus $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/businesslogic/business_logic_controller.php $
-
require_once CORE_DIR . '/businesslogic/business_logic_controller_base.php';
require_once CORE_DIR . '/boxes/box_controller_factory.php';

require_once MODEL_DIR . '/base/city_model.php';
require_once MODEL_DIR . '/base/file_model.php';
require_once MODEL_DIR . '/course/course_model.php';

// TODO: make include dependent on user's language
require_once BASE . '/lang/de.php.inc';

/**
 * Der Basis BusinessLogicController. Er stellt das 3 spaltige 
 * Layout zur Verf�gung und der Benutzer muss nur noch 
 * den Hauptteil f�llen.
 * 
 * Wichtig ist das hier eine Unterteilung des Ziels herrscht.
 * M�gliche Ziele sind in <code>getControllerDestinations()</code>
 * definiert. Das ist ein Hash der 'ziel' => 'funktion' aufgebaut ist. 
 * Die Funktion soll dann z.B. bei mehreren Boxen die Richtige raussuchen 
 * und dem Boxen Controller die Arbeit �berlassen.
 * 
 * Von vorne herein gibts Controller Destinations f�r 
 * 'module' => das jetzige Module ist das Ziel (selbst verantwortlich)
 * 'box'    => eine Box Links oder Rechts ist das Ziel
 * 
 * Normalerweise sollte es nicht n�tig sein weitere zu registrieren.
 */
class BusinessLogicController extends BusinessLogicControllerBase {
	/** boxes controller off the LEFT side */    
    private $boxesLeft;
    
	/** boxes controller off the RIGHT side */    
    private $boxesRight;
    
    /**
     * @var boolean
     * true iff user is logged in at the beginning of page load
     */
    private $userLoginState;

    protected final function initializeSmartyView(&$view) {
        $view = ViewFactory::getSmartyThreeColumnView(self::$TEMPLATE_FOR_USER, 'default.tpl');
    }
    
	/** Konstruktor */
	public function __construct($ajaxView = false) {
		parent::__construct($ajaxView);
        
        if (!$ajaxView) {
           
            // init box arrays
            $this->boxesLeft = array();
            $this->boxesRight = array();
            
            $this->userLoginState = Session::getInstance()->getVisitor()->isLoggedIn();
            $this->initializeBoxes();        
        }
	}
    
    protected function initializeBoxes() {
    	// init box arrays
        $this->boxesLeft = array();
        $this->boxesRight = array();

        // check for user based configuration of modules
        if (Session::getInstance()->getVisitor()->isRegularLocalUser()) {
        	$user = Session::getInstance()->getVisitor();
            if ($user->hasConfigBoxesLeft()) {
            	$boxesLeftString = $user->getConfigBoxesLeft();
            } else {
            	// default configuration for boxes on the left for logged-in users
                $boxesLeftString = self::getDefaultBoxesLeft(true);
            }
            if ($user->hasConfigBoxesRight()) {
                $boxesRightString = $user->getConfigBoxesRight();
            } else {
                // default configuration for boxes on the right for logged-in users
                $boxesRightString = self::getDefaultBoxesRight(true);
            }
        } else {
            // default configuration for boxes for not-logged-in users
            $boxesLeftString = self::getDefaultBoxesLeft(false);
            $boxesRightString = self::getDefaultBoxesRight(false);
        }
        
        // create appropriate objects
        foreach (explode(',',$boxesLeftString) as $boxName) {
            if ($boxName != '') {
                $box = explode(':',$boxName);
                if(empty($box[1])){
                    $box[1] = 1;
                }
                $this->boxesLeft[$box[0].':'.$box[1]] = BoxControllerFactory::getBox($box);
            }
        }
        foreach (explode(',',$boxesRightString) as $boxName) {
        	if ($boxName != '') {
                $box = explode(':',$boxName);
                if(empty($box[1])){
                    $box[1] = 1;
                }
                $this->boxesRight[$box[0].':'.$box[1]] = BoxControllerFactory::getBox($box);
            }
        }
    }
    
    public static function getDefaultBoxesLeft($loggedIn = false) {
        if (!$loggedIn) {
            return BOX_DEFAULT_LEFT;
        } else {
            return BOX_DEFAULT_LEFT_LOGGIN;
        }
    }
    
    public static function getDefaultBoxesRight($loggedIn = false) {
        if (!$loggedIn) {
            return BOX_DEFAULT_RIGHT;
        } else {
            return BOX_DEFAULT_RIGHT_LOGGIN;
        }
    }
    
    /**
     * returns a comma separated string of available boxes dependent
     * on given login status; login_box ('user_login') is <b>never</b> returned
     * @param boolean $loggedIn login status
     */
    public static function getAvailableBoxes($loggedIn = false) {
        require BASE . "/conf/enabled_modules.php";
        
        // $enabledBoxes comes from config
        if($enabledBoxes === null or !is_array($enabledBoxes)){
        	new NotImplementedException('$enabledBoxes not defined');
        }
        $returnString = '';
        foreach($enabledBoxes as $key => $reqiereLoggin){
        	 if ($reqiereLoggin && !$loggedIn){
        	 	continue;
        	 } 
             $returnString .= $key . ',';
        }
        $returnString = substr($returnString, 0, -1);
        return $returnString;
        
    }
    
    /**
     * sets central content view
     * @param SmartyView
     * @param boolean
     * @param boolean
     */
    protected function setCentralView($view, $showBanner = true, $showBreadcrumbs = true) {
        if (!$showBanner) {
            $this->getSmartyView()->assign('nobanner', 1);
        }
        if (!$showBreadcrumbs) {
            $this->getSmartyView()->assign('nobreadcrumbs', 1);
        }
        parent::setCentralView($view);
    }
    
    protected function view($properDisplay = true) {
		//collect views
    	$leftBoxViews = array();
    	$rightBoxViews = array();
    	
    	foreach ($this->boxesLeft as $boxController) {
    		array_push($leftBoxViews, $boxController->getView());
        }
    		
    	foreach ($this->boxesRight as $boxController) {
    		array_push($rightBoxViews, $boxController->getView());
        }

        // set box layout
        $this->getSmartyView()->setBoxViews(array('left' => $leftBoxViews, 'right' => $rightBoxViews));

        // for random user picture
        $this->getSmartyView()->assign('box_random_userpic_user', UserProtectedModel::getUserByRandom());
        
        $this->getSmartyView()->assign('local_city', CityModel::getLocalCity());
        
        // TODO: visitor is assigned in SmartyView::assignVariables
        //       can we move it hither?
        
        parent::view($properDisplay);
    }
	
    /**
     * displays a confirmation Text
     * 
     * 
     * @param string $confirmationCause
     * @param string $confirmationConsequences
     * @param string $confirmationOkLink
     * @param string $confirmationCancelLink
     */
    protected function confirmationView($confirmationCause, $confirmationConsequences, $confirmationOkLink, $confirmationCancelLink ){
        
        $main = ViewFactory::getSmartyView(Session::getInstance()->getTemplateDirectory(), 'common/confirmation.tpl');
        
        $main->assign('confirmationCause', $confirmationCause);
        $main->assign('confirmationConsequences', $confirmationConsequences);
        $main->assign('confirmationOkLink', htmlspecialchars($confirmationOkLink));
        $main->assign('confirmationCancelLink', htmlspecialchars($confirmationCancelLink));
        
        $this->setCentralView($main);
        $this->view();
    }
    
	/**
	 * überschriebene process Methode. Wir suchen unsere
	 * destination und überlassen dem ganzen die Arbeit.
	 */
	public function process() {
		//destination suchen und aufrufen
		if (isset($_REQUEST['dest']))
            $dest = $_REQUEST['dest'];
		
        $registredDestinations = $this->getControllerDestinations();
        
		if (!isset($dest) || !array_key_exists($dest, $registredDestinations))
			$dest = 'module';
	
		//let the request be processed
		$this->$registredDestinations[$dest]();
	}
    
	/**
	 * (siehe Klassenbeschreibung)
	 * Gibt alle m�glichen Ziele f�r den Request wieder.
	 */
	protected function getControllerDestinations() {
		return array(
			'module' => 'destinationModul',
			'box'    => 'destinationBox'
		);
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

            // if login state has changed, we have to reload box configuration
            if ($this->userLoginState != Session::getInstance()->getVisitor()->isLoggedIn()) {
                $this->initializeBoxes();
            }
        }
        
        parent::process();
	}
	
	/** 
	 * The destination of the request is a box on the left or 
	 * right side and will be dispatched. Importent is that the 
	 * destination box is identified with the request parameter
	 * 'bname'
	 */
	private function destinationBox() {
        // ensure parameter existence
        if (!array_key_exists('bname',$_REQUEST)) {
            throw new CoreException( Logging::getErrorMessage(CORE_BOX_CONTROLLER_FAILED ) );
        }
        $instance = empty($_REQUEST['instance']) ? 1 : $_REQUEST['instance'];
        $box = $_REQUEST['bname'] . ':' . $instance;
        
        $boxController = null;
        // if boxes have not been initialized yet, initialize them now
        if ($this->boxesLeft === null or $this->boxesRight === null) {
            $this->initializeBoxes();
        }
        
        //get box controller
        if (array_key_exists($box,$this->boxesLeft)) {
            $boxController = $this->boxesLeft[$box];
        } else if (array_key_exists($box,$this->boxesRight)) {
            $boxController = $this->boxesRight[$box];
        }
        
        /* when a ajax request can't be processed in a productive system tell it the box that make the request */
        if (!$boxController && $this->ajaxView && !DEVEL) {
            header("HTTP/1.0 503 Service Unavailable");
            die();
        } else if(!$boxController) {
			$l = print_r($this->boxesLeft, true);
			$r = print_r($this->boxesRight, true);
            throw new ArgumentException('box', $box . " in $l // $r");
        }
        
		//let the request process
        $boxController->process();

        // if login state has changed, we have to reload box configuration
        // if action went to basic box controller. we should also reload
        if ($boxController->doesNeedReload()) {
            $this->initializeBoxes();
        }
        
        $method = '';
        
        /*
         * FIX:
         * ich habe das eingefügt, wenn ich in einer Box ein AJAX request
         * ausführe, dann will ich kein BLC methoden ausfürhen!
         */
        if ($this->ajaxView) {
        	return true;
        }

        // try to use last GET parameters 
        if ($newGET = Session::getInstance()->getViewData('last_get_array')) {
            // overwrite current $_GET
            $_GET = $newGET;
            // and rebuild $_REQUEST
            $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
            
            // try to get method name
            $method = array_key_exists('method', $_GET) ? $_GET['method'] : '';
        } 
        // if no method name is available, use default one
        if (!$method) {
            $method = $this->getDefaultMethod();
        }
        $this->method = $method;
        
        // trigger view
        $this->$method();
	}    
}


?>
