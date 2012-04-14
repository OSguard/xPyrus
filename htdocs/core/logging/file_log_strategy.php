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

require_once CORE_DIR . '/logging/flat_logger.php';

/**
 * @version $Id$
 */
class FileLogStrategy extends FlatLogger {
	const LOG_FILENAME = 'log';

	protected function openLog($postfix) {
		if (!defined('LOG_DIR')) {
			throw new ArgumentException('LOG_DIR');
		} else if (!@is_dir(LOG_DIR)) {
			throw new CoreException(Logging::getErrorMessage(FILE_FILE_NOT_FOUND, LOG_DIR));
		}
		return @fopen( LOG_DIR . '/' . self::LOG_FILENAME . $postfix . '.log', "a");
	}

	protected function write($type, $string) {
		if ($type !== null) {
			$type = '_' . $type;
		} else {
			$type = '';
		}

		$file = $this->openLog($type);
		$logSuccess = false;
		if ($file and @flock($file, LOCK_EX)) { // do an exclusive lock
			if (@fwrite($file, $string) !== false){
				$logSuccess = true;
			}
			@fwrite($file, '------------------------------------------------------------------'."\n");

			// unlock file
			@flock($file, LOCK_UN);
			 
			//close file
			@fclose($file);
		}
		return $logSuccess;
	}
}

?>
