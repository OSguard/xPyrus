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

// $Id: index.php 6210 2008-07-25 17:29:44Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/index.php $


require_once "./conf/config.php";

require_once "./dispatch.php";

// TODO: rename view==ajax into something else; perhaps 'light view'? (linap, 10.05.2007)
$ajaxView = isset($_REQUEST['view']) && $_REQUEST['view'] == 'ajax';

$gatherStats = !$ajaxView && array_key_exists('showStats', $_COOKIE) && $_COOKIE['showStats'] == STAT_SECRET;
if ($gatherStats) {
    $START_TIME = explode(" ", microtime());
}

function _error_handler($errNo, $errStr, $errFile, $errLine, $errContext) {
    if ($errNo != E_ERROR and $errNo != E_USER_ERROR and $errNo != E_USER_WARNING) {
        return true;
    }
	$message = "($errNo) " . $errStr . ' in ' . $errFile . ' at line ' . $errLine . "\nContext:\n";
	foreach ($errContext as $var => $val) {
		$message .= ' ' . $var . ' => ' . print_r($val, true) . "\n";
	}
	if ($errNo == E_ERROR or $errNo == E_USER_ERROR) {
		throw new Exception($message);
	} else if (/*$errNo == E_WARNING or*/ $errNo == E_USER_WARNING) {
		// can't log E_WARNING due to smarty code
		Logging::getInstance()->logWarning($message);
	}
	
	return true;
}


function main($ajaxView) {
	require_once CORE_DIR . "/url_rewrite.php";
	require_once CORE_DIR . "/controller_factory.php";
	
	// do session data handling
	// initialize session object once
	require_once CORE_DIR . "/session.php";
	Session::getInstance();
	
	if (DEVEL) {
		if(in_array('debugSQLQueries', $_REQUEST))
			;
		if(in_array('debugSQLTiming', $_REQUEST))
			;
	    if(array_key_exists('showDebug',$_REQUEST) && $_REQUEST['showDebug'] == 'on'){
	    	setcookie('show_debug','true');
	    }
	    if(array_key_exists('showDebug',$_REQUEST) && $_REQUEST['showDebug'] == 'off'){
	        setcookie('show_debug','false');
	    }    
	}

	if (array_key_exists('showStats', $_GET) and $_GET['showStats'] == STAT_SECRET . 'Off') {
	    setcookie('showStats', 'foo', time() - 86400);
	    unset($_COOKIE['showStats']);
	} else if (array_key_exists('showStats', $_GET) and $_GET['showStats'] == STAT_SECRET . 'On') {
	    setcookie('showStats', STAT_SECRET, time() + 86400 * 365);
	    $_COOKIE['showStats'] = STAT_SECRET;
	}
		
	// set a default controller when nothing is set
	if (isset($_REQUEST['mod'])) {
	    $controller = $_REQUEST['mod'];
	    // if we don't have an ajax request, we can store last successful
	    // controller call
	    if (!$ajaxView) {
	        Session::getInstance()->storeViewData('last_mod', $controller);
	    }
	} else {
		// try to load last known controller
		if (!($controller = Session::getInstance()->getViewData('last_mod'))) {
	        // if no last controller is known, use default 
	        $controller = "index";
	    }
	}
	
	if (DEVEL === TRUE and array_key_exists('quiet', $_REQUEST)) {
		error_reporting(E_WARNING);
	} else if(DEVEL === TRUE){
	    error_reporting(E_ALL);
	}
	
	$controllerName = $controller;
	$controller = ControllerFactory::createControllerByName($controller, $ajaxView);
	
	if ($controller == null) {
	    throw new CoreException (Logging::getErrorMessage(CORE_CONTROLLER_FAILED, $controllerName));
	}
	
	//let the modul handler take control over the process
	$controller->process();
	
	global $gatherStats;
	if ($gatherStats) {
	    $END_TIME = explode(" ", microtime());
    	global $START_TIME;
    	$timeNeeded = $END_TIME[0] - $START_TIME[0];
    	$timeNeeded += $END_TIME[1] - $START_TIME[1];
    	
	    $load = explode(' ', file_get_contents('/proc/loadavg'));
	    print 'page presented in ' . $timeNeeded  . ' seconds at load ' . $load[0] . ' / ' . $load[1] . ' / ' . $load[2];
	}
}



/*
 * CSS switcher
 */
$cssDir = 'css';
//if (DEVEL) {
    //if (array_key_exists('cssswitch', $_GET)) {
        //if ($_GET['cssswitch'] != 'standard') {
            //setcookie('css', 'alternative', time() + 8640000, '/');
            //$cssDir = 'css_alternative';
        //} else {
            setcookie('css', 'standard', time() + 8640000, '/');
            $_COOKIE['css'] = 'standard';
            $cssDir = 'css';
        //}
    //}
    
    //if (array_key_exists('css', $_COOKIE) and $_COOKIE['css'] == 'alternative') {
        //$cssDir = 'css_alternative';
    //}
//}

if (DEVEL === true) {
    main($ajaxView);
} else {
    try {
        set_error_handler("_error_handler");
        main($ajaxView);
    } catch (Exception $e) {
        $id = Logging::getInstance()->logException($e);
        if (!$ajaxView) {
            GlobalShowErrorPage($id);
        }
	}
}
?>
