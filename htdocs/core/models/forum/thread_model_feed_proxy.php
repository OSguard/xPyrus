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

// $Id: blog_advanced_comment_feed_proxy.php 5208 2007-09-02 17:27:11Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/mauritius-1.1/htdocs/core/models/blog/blog_advanced_comment_feed_proxy.php $

require_once CORE_DIR . '/interfaces/syndicable_entry.php';
require_once CORE_DIR . '/interfaces/proxy.php';

class ThreadModelFeedProxy implements SyndicableEntry, Proxy {
    protected $_thread;
    public function __construct($thread = null) {
        $this->_thread = $thread;
    }

    public function getSynTitle() {
        //return $this->_thread->getCaption();
        return $this->_thread->getForum()->getName() .' :: ' . $this->_thread->getCaption();
    }
    public function getSynLink() {
        return rewrite_forum(array("thread" => $this->_thread, "extern" => true));
    }
    public function getSynCategories() {
        return array();
    }
    public function getSynAuthor() {
        if($this->_thread->getLastEntry()->isAnonymous()){
        	return "Anonymouse";
        }
        if($this->_thread->getLastEntry()->isForGroup()){
            return $this->_thread->getLastEntry()->getGroup()->getName();
        }
        return $this->_thread->getLastEntry()->getAuthor()->getUsername();
    }
    public function getSynContent() {
        $caption = '';
        if ($this->_thread->getLastEntry()->getCaption() != '') {
            $caption = $this->_thread->getLastEntry()->getCaption() . ", ";
        }
        return NAME_FORUM_LATEST_ENTRY . ": " . $caption . $this->getSynAuthor() . ", " . strftime("%d.%m.%Y, %H:%M", $this->_thread->getLastEntry()->getTimeEntry());
    }
    public function getSynPublicationDate() {
        return $this->_thread->getLastEntry()->getTimeEntry();
    }
    public function getSynGUID() {
        return $this->_thread->id . "-" . $this->_thread->getLastEntry()->getTimeLastUpdate();
    }

    public function proxy($class) {
        return new ThreadModelFeedProxy($class);
    }
}

?>
