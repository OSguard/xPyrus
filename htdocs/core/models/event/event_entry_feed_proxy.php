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

require_once CORE_DIR . '/interfaces/syndicable_entry.php';
require_once CORE_DIR . '/interfaces/proxy.php';

class EventEntryFeedProxy implements SyndicableEntry, Proxy {
    protected $eventEntry;
    
    public function __construct($eventEntry = null) {
        $this->eventEntry = $eventEntry;
    }
    
    public function getSynTitle() { return $this->eventEntry->getCaption(); }
    public function getSynLink() { return rewrite_index(array("events"=>true, "eventId"=>$this->eventEntry->id , "extern" => true)); }
    public function getSynCategories() { return array(); }
    public function getSynAuthor() { 
    	if($this->eventEntry->getGroupId() !=null){
    		return $this->eventEntry->getGroup()->getName();
    	}
      return $this->eventEntry->getAuthor()->getUsername(); 
    }	
    public function getSynContent() { 
    	return date("j F Y, G:i ",$this->eventEntry->getStartDate() ). 
                ' - ' . date("F j, Y, G:i ",$this->eventEntry->getEndDate()). 
                "<br />". $this->eventEntry->getContentParsed(true); 
        }
    public function getSynPublicationDate() { return $this->eventEntry->getStartDate(); }
    public function getSynGUID() { return $this->getSynLink(); }
    
    public function proxy($class) { return new EventEntryFeedProxy($class); }
}

?>
