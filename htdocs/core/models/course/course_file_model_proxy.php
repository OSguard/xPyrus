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
/*
 * Created on 06.02.2008 by schnueptus
 * sunburner Unihelp.de
 */
 
 
require_once CORE_DIR . '/interfaces/syndicable_entry.php';
require_once CORE_DIR . '/interfaces/proxy.php';

class CourseFileFeedProxy implements SyndicableEntry, Proxy {
    protected $_courceFile;
    public function __construct($courceFile = null) {
        $this->_courceFile = $courceFile;
    }

    public function getSynTitle() {
        return $this->_courceFile->getCourse()->getName() . ' :: ' . $this->_courceFile->getFileName();
    }
    public function getSynLink() {
        return rewrite_course(array('courseFile' => $this->_courceFile, "extern" => true));
    }
    public function getSynCategories() {
        return array();
    }
    public function getSynAuthor() {
        return $this->_courceFile->getAuthor()->getUsername();
    }
    public function getSynContent() {
        return $this->_courceFile->getAuthor()->getUsername() . ': '.$this->_courceFile->getDescription();
    }
    public function getSynPublicationDate() {
        return $this->_courceFile->getInsertAt();
    }
    public function getSynGUID() {
        return $this->_courceFile->getInsertAt();
    }

    public function proxy($class) {
        return new CourseFileFeedProxy($class);
    }
}

?>
