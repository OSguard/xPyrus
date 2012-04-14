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
 * Alle Controller sollten von diesem abgeleitet sein. 
 * Wichtig ist das nicht vergessen wird den Konstruktor 
 * aufzurufen!
 */
class BaseController {
    
    protected $logArray = array();
    
    protected $method;
    
	function __construct() {
		
	}

	protected function getAllowedMethods() {
		return array (
			'view'
		);
	}

	/**
	 * Started die Verarbeitung des Request. 
	 * Die Standard implementierung ruft die Funktion 
	 * auf die 'method' übergibt.
	 */
	public function process() {

		//default Methode setzen falls keine gesetzt ist
		if (isset($_REQUEST['method'])) {
            $method = $_REQUEST['method'];
        } else {
			$method = $this->getDefaultMethod();
        }
        $this->method = $method;

		//prüfen ob die Methode erlaubt ist
		if (!in_array($method, $this->getAllowedMethods())) {
            Logging::getInstance()->logSecurity('Methode: \'' . $method . '\' muss als erlaubt registriert sein!');
            throw new Exception("unallowed method call to " . $method);
		}

		//ausführen der Methode
		$this->$method();
        
        //write spezial log
        $this->writeLog($method);
	}
    
    public function postProcess($method, $params = array()) {
        if ($method == null) {
            $method = $this->getDefaultMethod();
        }
        $this->method = $method;
        
        //prüfen ob die Methode erlaubt ist
        if (!in_array($method, $this->getAllowedMethods())) {
            Logging::getInstance()->logSecurity('Methode: \'' . $method . '\' muss als erlaubt registriert sein!');
        }

        //ausführen der Methode
        $this->$method($params);

    }
    
    protected static function observeIPC($ipc, $flags, $view, $cacheKey = null) {
        foreach ($flags as $flag) {
            $view->clearCache($cacheKey, $ipc->getTime($flag));
        }
        $ipc->release();
    }
    
    protected static function notifyIPC($ipc, $flag) {
        $ipc->setTime($flag);
        $ipc->release();
    }

	/**
	 * method, that is executed if no one is explicity called
     * @return string
	 */
	protected function getDefaultMethod() {
		return 'view';
	}
	
	/**
	 * default implementation of the view method
	 */
	protected function view() {
		die('Sorry here is nothing to view yet');
	}
    /**
     * add something to log
     */
    protected function addLog($value){
        $this->logArray[] = $value;
    }
    /**
     * write the log
     */
    protected function writeLog($method = ''){    	        
        
        if(empty($this->logArray)){
    		return;
    	}
        
        require_once CORE_DIR . '/logging.php';
        
        if(isset($_SERVER['HTTP_USER_AGENT'])) $uastring=$_SERVER['HTTP_USER_AGENT'];
            else $uastring='not found';
        if(isset($_SERVER['REQUEST_URI'])) $ruri=$_SERVER['REQUEST_URI'];
            else $ruri='not found';
        if(isset($_SERVER['SCRIPT_FILENAME'])) $sfilen=$_SERVER['SCRIPT_FILENAME'];
            else $sfilen='not found';
        if(isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddr=$_SERVER['REMOTE_ADDR'];
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ipaddr .= ' / ' . $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        } else {
            $ipaddr='not found';
        }
    
        $postString = '{';
        foreach ($_POST as $key => $val) {
            $postString .= "'$key' : '$val' ,";
        }
        $postString .= '}';
        $getString = '{';
        foreach ($_GET as $key => $val) {
            $getString .= "'$key' : '$val' ,";
        }
        $getString .= '}';
        
        $logText = '';
        // log eintrag composing
        $logText .="Time: ".date('Y-m-d H:i:s (T)')."\n";
        $logText .="IP-Address: $ipaddr \n";
        $logText .="Request-URI: $ruri \n";
        $logText .="File: $sfilen \n";
        $logText .="User-Agent: $uastring \n";
        $logText .="GET-Variables: $getString \n";
        $logText .="POST-Variables: $postString \n";
        $logText .="Calling user: " . Session::getInstance()->getVisitorName(). " \n";
        
        foreach($this->logArray as $logZeile){
        	Logging::getInstance()->logAdmin($logZeile);
        }

        $this->logArray = array();
    }
}

?>
