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

require_once CORE_DIR . '/interfaces/log_strategy.php';

/**
 * @version $Id$
 */
abstract class FlatLogger implements LogStrategy {
	protected static function logableToString($logable) {
		$s = '';
		$s .= 'Message: ' . $logable->getMessage() . "\n";
		$s .= 'UID: ' . $logable->getUID() . "\n";
		$s .= 'Time: ' . $logable->getTime() . "\n";
		$s .= 'URI: ' . $logable->getRequestURI() . "\n";
		$s .= 'IP: ' . $logable->getIP() . "\n";
		$s .= 'User-Agent: ' . $logable->getUserAgent() . "\n";
		$s .= 'User: ' . $logable->getUser() . "\n";
		$s .= 'Stacktrace: ' . $logable->getStacktrace() . "\n";
		$s .= 'REQUEST: ' . "\n" . $logable->getRequestVars() . "\n";
		return $s;
	}

	abstract protected function write($type, $string);

	public function log($logable) {
		try {
			return $this->write($logable->getType(), self::logableToString($logable));
		} catch (Exception $e) {
			return false;
		}
	}
}

?>
