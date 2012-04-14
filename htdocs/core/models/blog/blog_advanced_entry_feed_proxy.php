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

// $Id: blog_advanced_entry_feed_proxy.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/blog/blog_advanced_entry_feed_proxy.php $

require_once CORE_DIR . '/interfaces/syndicable_entry.php';
require_once CORE_DIR . '/interfaces/proxy.php';

class BlogAdvancedEntryFeedProxy implements SyndicableEntry, Proxy {
    protected $blogEntry;
    
    public function __construct($blogEntry = null) {
        $this->blogEntry = $blogEntry;
    }
    
    public function getSynTitle() { return $this->blogEntry->getTitle(); }
    public function getSynLink() { return rewrite_blog(array("owner" => $this->blogEntry->getOwner(), "entry" => $this->blogEntry, "extern" => true)); }
    public function getSynCategories() { return $this->blogEntry->getCategories(); }
    public function getSynAuthor() { return $this->blogEntry->getAuthor()->getUsername(); }
    public function getSynContent() { return $this->blogEntry->getContentParsed(true); }
    public function getSynPublicationDate() { return $this->blogEntry->getTimeEntry(); }
    public function getSynGUID() { return $this->getSynLink(); }
    
    public function proxy($class) { return new BlogAdvancedEntryFeedProxy($class); }
}

?>
