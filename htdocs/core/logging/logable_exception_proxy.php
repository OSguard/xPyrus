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
class LogableExceptionProxy implements Logable {
    protected $exception;
    public function __construct($exception) {
        $this->exception = $exception;
    }
    public function getMessage() { return '[' . get_class($this->exception) . '] ' . $this->exception->getMessage(); }
    public function getType() { return 'exception'; }
    public function getIP() { return null; }
    public function getUserAgent() { return null; }
    public function getRequestVars() { return null; }
    public function getStacktrace() { return $this->exception->getTraceAsString(); }
    public function getUser() { return null; }
    public function getTime() { return null; }
    public function getRequestURI() { return null; }
    public function getUID() {return null; }
}

?>
