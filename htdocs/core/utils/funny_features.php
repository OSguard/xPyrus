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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/funny_features.php $

class FunnyFeatures {
    /**
     * @throws DBException on DB error
     */
    public static function smallWorld($user1, $user2) {
    	if (!DIJKSTRA_AVAILABLE) {
    	    return array();
    	}
        
        // ensure that both users are valid
        if ($user1->id == null or $user2->id == null) {
    		return array();
    	}
    	// we can only compute connection in one city
    	if (!$user1->isRegularLocalUser() || !$user2->isRegularLocalUser()) {
        	return array();
        }
        if ($user1->equals($user2)) {
        	return array($user1->getUsername());
        }
        
        $DB = Database::getHandle();
        $q = 'SELECT vertex_id 
                FROM public.dijkstra(
                           \'SELECT id::int4, user_id::int4 AS source, friend_id::int4 AS target, 1 AS cost
                               FROM ' . DB_SCHEMA . '.user_friends\',
                           ' . $DB->Quote($user1->id) . ',
                           ' . $DB->Quote($user2->id) . ', \'t\')';
        $res = $DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $smallWorld = array();
        foreach ($res as $row) {
            $smallWorld[] = $row['vertex_id'];
        }
        return $smallWorld;
    }
}

?>
