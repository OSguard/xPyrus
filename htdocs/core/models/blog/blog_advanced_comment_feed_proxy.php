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

// $Id: blog_advanced_comment_feed_proxy.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/blog/blog_advanced_comment_feed_proxy.php $

require_once CORE_DIR . '/interfaces/syndicable_entry.php';
require_once CORE_DIR . '/interfaces/proxy.php';

class BlogAdvancedCommentFeedProxy implements SyndicableEntry, Proxy {
    protected $blogComment;
    
    public function __construct($blogComment = null) {
        $this->blogComment = $blogComment;
    }
    
    public function getSynTitle() { return $this->blogComment->authorName . ': ' . $this->blogComment->blogEntry->getTitle(); }
    public function getSynLink() { return rewrite_blog(array("owner" => $this->blogComment->blogEntry->getOwner(), "comment" => $this->blogComment, "extern" => true)); }
    public function getSynCategories() { return array(); }
    public function getSynAuthor() { return $this->blogComment->authorName; }
    public function getSynContent() { return $this->blogComment->getComment(); }
    public function getSynPublicationDate() { return $this->blogComment->timeEntry; }
    public function getSynGUID() { return $this->getSynLink(); }
    
    public function proxy($class) { return new BlogAdvancedCommentFeedProxy($class); }

}

?>
