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
 * Created on 22.09.2006 by schnueptus
 * sunburner Unihelp.de
 */
 class VirtuellForumModel extends InteractiveUserElementModel{
 	
    
     public function __construct($page = 1, $threadsPerPage = 20) {
        parent :: __construct();
        $this->page = $page;
        $this->threadsPerPage = $threadsPerPage;
    }
    
    public function getAllForenIdsByTag($tag){
    	$DB = Database :: getHandle();

        /* temporary fix ;) */
        if($tag == null) {
            throw new ArgumentNullException('tag');
        }
        
        $q = 'SELECT forum_id FROM '. DB_SCHEMA .'.forum_tag WHERE tag_id =' . $DB->quote($tag->id);
        
        $res = $DB->execute($q);

        #var_dump($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }

        $forumIds = array ();

        foreach ($res as $k => $row) {
            $forumIds[] = $row['forum_id'];
        }

        return $forumIds;
    }
    
 }
?>
