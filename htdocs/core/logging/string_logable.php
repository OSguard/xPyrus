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

require_once CORE_DIR . '/interfaces/logable.php';

/**
 * @version $Id$
 */
abstract class StringLogable implements Logable {
	protected $string;
	protected $type;
	public function __construct($type, $string) {
		$this->type = $type;
		$this->string = $string;
	}
	
	public function getMessage() { return $this->string; }
    public function getType() { return $this->type; }
    public function getIP() { return null; }
    public function getUserAgent() { return null; }
    public function getRequestVars() { return null; }
    public function getStacktrace() { return null; }
    public function getUser() { return null; }
    public function getTime() { return null; }
    public function getRequestURI() { return null; }
    public function getUID() {return null; }
}

?>
