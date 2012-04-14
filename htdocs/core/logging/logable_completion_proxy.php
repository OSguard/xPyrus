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

require_once CORE_DIR . '/utils/client_infos.php';
require_once CORE_DIR . '/interfaces/logable.php';

class LogableCompletionProxy implements Logable {
    protected $logable;

    public function __construct($logable) {
        $this->logable = $logable;
    }

    public function getMessage() {
        $tmp = $this->logable->getMessage();
        if ($tmp) {
            return $tmp;
        }
        return '';
    }

    public function getType() {
        $tmp = $this->logable->getType();
        if ($tmp) {
            return $tmp;
        }
        return 'Proxy';
    }

    public function getIP() {
        $tmp = $this->logable->getIP();
        if ($tmp) {
            return $tmp;
        }
        return ClientInfos::getClientIP();
    }

    public function getUserAgent() {
        $tmp = $this->logable->getUserAgent();
        if ($tmp) {
            return $tmp;
        }
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    public function getRequestVars() {
        $tmp = $this->logable->getRequestVars();
        if ($tmp) {
            return $tmp;
        }
        $tmp = '';
        foreach ($_REQUEST as $key => $val) {
            $tmp .= "  $key => $val\n";
        }
        return $tmp;
    }

    public function getStacktrace() {
        $tmp = $this->logable->getStacktrace();
        if ($tmp) {
            return $tmp;
        }
        return '';
    }

    public function getUser() {
        $tmp = $this->logable->getUser();
        if ($tmp) {
            return $tmp;
        }
        return (class_exists('Session') && Session::hasBeenStarted()) ? Session::getInstance()->getVisitorName() : '';
    }

    private $time = null;
    public function getTime() {
        if ($this->time === null) {
            $tmp = $this->logable->getTime();
            if ($tmp) {
                $this->time = $tmp;
            } else {
                $this->time = date('Y-m-d H:i:s (T)');
            }
        }
        return $this->time;
    }

    public function getRequestURI() {
        $tmp = $this->logable->getRequestURI();
        if ($tmp) {
            return $tmp;
        }
        return $_SERVER['REQUEST_URI'];
    }

    private $uid = null;
    public function getUID() {
        if ($this->uid === null) {
            $tmp = $this->logable->getUID();
            if ($tmp) {
                $this->uid = $tmp;
            } else {
   		        $this->uid = md5(uniqid(rand(), true));
            }
        }
        return $this->uid;
    }
}

?>
