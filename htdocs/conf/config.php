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

# $Id: config.php 5760 2008-03-29 16:45:37Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/conf/config.php $
#
# contains default configs

if (!defined('HAVE_CONFIG')) {

    # remember that we already read this config file
    define('HAVE_CONFIG', TRUE);

    # base path for all other locations
    if ($_SERVER['DOCUMENT_ROOT']) {
      define('BASE', $_SERVER['DOCUMENT_ROOT']);
    } else {
      define('BASE', realpath(dirname(__FILE__) . '/../'));
    }

    # include local config, if there is one
    if (file_exists(BASE . "/conf/local_config.php")) {
      include_once(BASE . "/conf/local_config.php");
    }
    
    # include file generation settings
    if (file_exists(BASE . "/conf/generated_files.conf.php")) {
      include_once(BASE . "/conf/generated_files.conf.php");
    }
    
    require_once BASE . '/conf/config_check.php';

    foreach ($CONFIG_CHECKS as $check) {
        $check->doCheck();
    }

    if (!DEVEL) {
    	ini_set('display_errors', 'off');
    	ini_set('log_errors', 'on');
    }
    // overload multibyte string function if available
    // cf. http://bugs.unihelp.de/view.php?id=1159
    if (function_exists('mb_list_encodings')) {
    	ini_set('mbstring.internal_encoding', 'UTF8');
   		ini_set('mbstring.func_overload', '2');
   	}
    
    require_once(CORE_DIR . "/error_codes.php");

    function GlobalShowErrorPage($errorId) {
    	include BASE . '/errors/error_page.php';
        exit;
    }

    require_once CORE_DIR . '/exceptions/core_exception.php';
    require_once CORE_DIR . '/exceptions/db_exception.php';
    require_once CORE_DIR . '/exceptions/not_implemented_exception.php';
    require_once CORE_DIR . '/exceptions/argument_exception.php';
    require_once CORE_DIR . '/exceptions/argument_null_exception.php';
    require_once CORE_DIR . '/logging.php';
	
    // set locale for time display (e.g. used by smarty)
    setlocale(LC_TIME, "de_DE.utf-8");

    if (DEVEL) {
      //debug messages are stored here
      $_UNIHELP_DEBUG_OUTPUT = array();
      $_UNIHELP_DEBUG_SOUTPUT = array();
		
      /**
      * add a debug message at the end of the website output in a comment
      */
      function addDebugOutput($debugMessage, $show = false) {
        global $_UNIHELP_DEBUG_OUTPUT, $_UNIHELP_DEBUG_SOUTPUT;
		    
        if ($show) {
          $_UNIHELP_DEBUG_SOUTPUT[] = $debugMessage;
          return;
        }
		    
        $_UNIHELP_DEBUG_OUTPUT[] = $debugMessage;
      }
		
      function printDebugOutput($show = false) {
        global $_UNIHELP_DEBUG_OUTPUT;
        global $_UNIHELP_DEBUG_SOUTPUT;
			
        if (!$show) {
          return;
        }
			
        //output of the debug messages
        if (DEVEL === true && count($_UNIHELP_DEBUG_OUTPUT) > 0) {
          print "<!--\r\n";
			
          foreach ($_UNIHELP_DEBUG_OUTPUT as $line) {
            print $line;
            print "\r\n";
          }		
				
          print "\r\n-->";	
        }
        if (DEVEL === true && count($_UNIHELP_DEBUG_SOUTPUT) > 0) {
          print "\r\n";
          print "SQL-Statements executed: ~" . count($_UNIHELP_DEBUG_OUTPUT)/2; 
          print "\r\n";
          print "<br />";
          foreach ($_UNIHELP_DEBUG_SOUTPUT as $line) {
            print $line;
            print "<br />\r\n";
          }       
			    
          print "\r\n";    
        }
      }
    } // end if DEVEL
    else {
        function addDebugOutput($debugMessage, $show = false) { }
        function printDebugOutput($show = false) { }
    }
} # !defined('HAVE_CONFIG')

?>
