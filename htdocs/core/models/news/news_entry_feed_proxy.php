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

class NewsEntryFeedProxy implements SyndicableEntry, Proxy {
    protected $_news;
    public function __construct($news = null) {
        $this->_news = $news;
    }

    public function getSynTitle() {
        return $this->_news->getCaption();
    }
    public function getSynLink() {
        return rewrite_index(array("extern" => true));
    }
    public function getSynCategories() {
        return array();
    }
    public function getSynAuthor() {
        return $this->_news->getAuthor()->getUsername();
    }
    public function getSynContent() {
        return $this->_news->getOpener(true);
    }
    public function getSynPublicationDate() {
        return $this->_news->getTimeEntry();
    }
    public function getSynGUID() {
        return $this->_news->getTimeLastUpdate();
    }

    public function proxy($class) {
        return new NewsEntryFeedProxy($class);
    }
}

?>
