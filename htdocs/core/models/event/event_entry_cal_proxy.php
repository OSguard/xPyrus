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
# Created on 24.04.2008 by schnueptus
#
// $Id: blog_advanced_entry_feed_proxy.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/trunk/htdocs/core/models/blog/blog_advanced_entry_feed_proxy.php $

require_once CORE_DIR . '/interfaces/proxy.php';

class EventEntryCalProxy implements Proxy {
    protected $eventEntry;
    
    public function __construct($eventEntry = null) {
        $this->eventEntry = $eventEntry;
    }

    public function getCalUID() { return "UHcal-" . $this->eventEntry->id; }
    public function getCalStartTime() { return $this->eventEntry->getStartDate(); }
    public function getCalEndTime() { return $this->eventEntry->getEndDate(); }
    public function getCalSummary() { return $this->eventEntry->getCaption(); }
    public function getCalDescription() { return $this->eventEntry->getContentRaw(); }
    public function getCalPublic() { return true; }
    
    public function proxy($class) { return new EventEntryCalProxy($class); }
}

?>
