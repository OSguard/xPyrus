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

require_once CORE_DIR . '/logging/logable_completion_proxy.php';
require_once CORE_DIR . '/logging/file_log_strategy.php';
require_once CORE_DIR . '/logging/mail_log_strategy.php';
require_once CORE_DIR . '/logging/default_logables.php';
require_once CORE_DIR . '/logging/logable_exception_proxy.php';




/**
 * Logging class providing basic functionality for logging. Logging will be needed everywhere so a centralized logging class appears to be helpful.
 * @package Core
 */
class Logging {

	private $logStrategies = null;
	private static $instance = null;

	private function __construct() {
		$this->logStrategies = array(new FileLogStrategy(), new MailLogStrategy());
	}

	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new Logging();
		}
		return self::$instance;
	}

	public function logException($exception) {
		return $this->log(new LogableExceptionProxy($exception));
	}

	public function logWarning($message) {
		return $this->log(new LogWarning($message));
	}

	public function logSecurity($message) {
		return $this->log(new LogSecurity($message));
	}

	public function logAdmin($message) {
		return $this->log(new LogAdmin($message));
	}

	public function logUserDelete($message) {
		return $this->log(new LogUserDelete($message));
	}

	public function log($logable) {
		$completedLogable = new LogableCompletionProxy($logable);
		foreach ($this->logStrategies as $logStrategy) {
			if ($logStrategy->log($completedLogable)) {
				break;
			}
		}
		return $completedLogable->getUID();
	}

	/*
	 * Composes string from error message and arguments given. Error messages should be maintained in global config files.
	 * Replaces placeholders ("variable") by respective argument/parameter.
	 * Example: $fileName = "test.txt"; getErrorMessage("File '&&&' cannot be found.", $fileName)
	 * 			=> File 'test.txt' cannot be found.
	 * Works with any number of placeholders. These are defined as 'PARAMETER_TOKEN' in global config files.
	 * @param string Code is the code name or number defined in global config files.
	 * @param mixed Any number of arguments for the error message.
	 */
	public static function getErrorMessage($code){

		if (!is_string($code) || strlen($code)==0){
			throw new CoreException( $this->getErrorMessage( GENERAL_ARGUMENT_MISSING, "ErrorCode", E_NOTICE));
		}

		$arg_list = array_slice( func_get_args(), 1);		//get all arguments passed to this function and remove first argument '$code'
		$num_of_args = func_num_args()-1;					//get number of arguments/parameters
		$string_parts = explode( PARAMETER_TOKEN, $code);	//split error message into pieces using standard token
		$num_of_vars = count($string_parts)-1;				//number of variables in error message to replace by arguments/parameters
		$result = "";
		$temp = "";

		//if error message requires no arguments, just return the error message given by the code number ($code)
		if (strcasecmp( $string_parts[0], $code) == 0 ){
			$result = $code;
		}
		else{
			for ($i=0;$i<$num_of_vars;$i++){
				if ($i<$num_of_args){					//if there are more arguments/parameters available
					$temp = $arg_list[$i];
				}
				else{
					$temp = "???";						//number of variables in error message > number of arguments given!
				}
				$result .= $string_parts[$i] . $temp;	//compose string
			}
			$result .= $string_parts[$i];				//append last string part from splitted array
		}
		return $result;
	}

}

?>
