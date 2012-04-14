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

// $Id: guestbook_model.php 5743 2008-03-25 19:48:14Z ads $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/gb/guestbook_model.php $

require_once MODEL_DIR . '/gb/guestbook_entry.php';
require_once MODEL_DIR . '/base/interactive_user_element_model.php';
require_once MODEL_DIR . '/base/entry_attachment_model.php';
require_once CORE_DIR . '/constants/value_constants.php';
require_once CORE_DIR . '/utils/client_infos.php';

/**
 * model that represents the complete guestbook of one user
 *  
 * @package Models
 * @subpackage GB
 */
class GuestbookModel extends InteractiveUserElementModel {
    protected $user;
    
    public function __construct($user) {
        parent::__construct();
        $this->user = $user;
    }
    
    public function getEntryIds($limit = V_GUESTBOOK_ENTRIES_PER_PAGE, $offset = 0, $order = 'desc') {
        $DB = Database::getHandle();
        
        // set default sort order; also ensure that no sql injection is possible
        $order = strtolower($order);
        if ($order != 'asc' and $order != 'desc') {
            $order = 'desc';
        }
        
        // collect ids of entries to display
        $q =  'SELECT id
                 FROM '.DB_SCHEMA.'.guestbook
                WHERE user_id_for = ' . $DB->Quote($this->user->id);
        $q .= $this->getSQLFilterString();
        
        $q.='
             ORDER BY entry_time ' . $order . '
                LIMIT '.$DB->Quote($limit).'
               OFFSET '.$DB->Quote($offset);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // collect ids of relevant guestbook entries
        $entryIds = array();
        foreach ($res as $k => $row) {
            array_push($entryIds, $row['id']);
        }
        
        return $entryIds;
    }
    
    public function getAllParsedEntries($limit = V_GUESTBOOK_ENTRIES_PER_PAGE, $offset = 0, $order = 'desc', $useAnonymousModel = true) {
        // build up return array
        $entries = GuestbookEntry::getEntriesByIds(self::getEntryIds($limit, $offset, $order), $useAnonymousModel);
        return $entries;
    }
    
    /**
     * determines number of guestbook entries that have been made after
     * specified entry for $this->user or 0, if entry is invalid
     * @param int $entryId
     * @return int
     */
    public function getEntriesAfterEntryId($entryId) {
        $DB = Database::getHandle();
        
        // collect number of entries that have been made after given entry
        $q =  'SELECT COUNT(id) AS nr
                 FROM '.DB_SCHEMA.'.guestbook
                WHERE user_id_for = ' . $DB->Quote($this->user->id) . '
                  AND entry_time > COALESCE((SELECT entry_time 
                                               FROM '.DB_SCHEMA.'.guestbook
                                              WHERE id = ' . $DB->Quote($entryId) . '
                                                AND user_id_for = ' . $DB->Quote($this->user->id) . '),
                                             NOW())';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        return $res->fields['nr'];
    } 
    
    /**
     * returns guestbook entry specified by id, iff visiting user is author of entry
     * @param int $id gb entry id
     * @param UserModel $visitingUser requiring user
     * @param boolean $onlyOwn iff true, return only an entry written by $visitingUser
     * @return GuestbookEntry
     */
    public function getEntryById($id, $visitingUser, $onlyOwn) {
        $entry = GuestbookEntry::getEntryById($id);

        // return entry only, if guestbook model owner is author of entry
        // or "safety mode" is off
        if (!$onlyOwn or $entry->getAuthor()->equals($visitingUser)) {
            return $entry;
        }
        
        return null;
    }
    
    /**
     * deletes a guestbook entry referenced by id
     * @param int $id guestbook entry id
     * @param boolean $onlyOwn iff true, delete only an entry received by gb's owner
     */
    public function deleteEntryById($id, $onlyOwn) {
        $DB = Database::getHandle();
        
        // delete guestbook entry, iff it belongs to model owner
        $q = 'DELETE FROM ' . DB_SCHEMA . '.guestbook
                    WHERE id = ' . $DB->Quote($id);
        if ($onlyOwn) {
           $q .=    ' AND user_id_for = ' . $DB->Quote($this->user->id);
        }
        
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    public function getCounter($page=1, $entriesPerPage=V_GUESTBOOK_ENTRIES_PER_PAGE) {
        // if no filtering is applied, we can rely on the pre-stored values
        if ($this->filter == null) {
            return parent::getCounter('getGBEntries', $page, $entriesPerPage);
        }

        // otherwise a select count is neccessary
        $DB = Database::getHandle();
        
        // determine number of guestbook entries with filtering settings applied
        $q = ' SELECT COUNT(*) AS nr
                 FROM '.DB_SCHEMA.'.guestbook
                WHERE user_id_for = ' . $DB->Quote($this->user->id);
        // filter results, if neccessary by given criteria
        $q .= $this->getSQLFilterString();

        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        $guestbookNumber = ceil($res->fields['nr'] / $entriesPerPage);
        return self::nonLinearCounter($guestbookNumber,$page);
    }
    
    /**
     * provides an appropiate instance of BaseFilter class
     * to operate on guestbook entries
     * @param array
     * @return BaseFilter
     */
    public static function getFilterClass($filterOptions) {
        include_once MODEL_DIR . '/gb/guestbook_filter.php';
        return new GuestbookFilter($filterOptions);
    }
    
    public function hasEntryLately($author, $hours) {
        // if author is not valid
        if ($author->id == 0) {
            return false;
        }

        $DB = Database::getHandle();
       
        // collect ids of entries to display
        $q =  'SELECT COUNT(id) AS nr
                 FROM '.DB_SCHEMA.'.guestbook
                WHERE user_id_for = ' . $DB->Quote($this->user->id) . '
                  AND entry_time >= now() - interval \'' . (int) $hours. ' hours\'
                  AND weighting <> 0 ';
        // distinguish between internal and external authors
        if ($author->isExternal()) {
             $q.='
                 AND author_ext = ' . $DB->Quote($author->localId);
        } else {
             $q.='
                 AND author_int = ' . $DB->Quote($author->id);
        }

        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        return $res->fields['nr'] > 0;
    }
    
    /**
     * die nachfolgende function versuch ich us preMVC zeiten wieder zum laufen zubringen
     * try & error
     * 
     * @author schnueptus
     */
    public function getEntryStatistics($orderBy = 'number', $orderDir = 'desc', $limit = 10) {
        // set default sort criterion; also ensure that no sql injection is possible
        if ($orderBy != 'points' && $orderBy != 'number'){
            $orderBy = 'number';
        }
        // set default sort order; also ensure that no sql injection is possible
        if ($orderDir!='desc' && $orderDir!='asc'){
            $orderDir='desc';
        } 
    
        $DB = Database::getHandle();
        
        // TODO: query works only on internal users now
        
        $q = 'SELECT SUM(weighting) AS points, COUNT(id) AS number, author_int, weighting,
                     extract(epoch from age(now(),MIN(entry_time))) AS time_since_first
                FROM '.DB_SCHEMA.'.guestbook
               WHERE author_int IN (SELECT author_int
                                         FROM '.DB_SCHEMA.'.guestbook
                                        WHERE user_id_for='.$DB->Quote($this->user->id).'
                                     GROUP BY author_int';
                                     
        if ($orderBy=='number') {
          $q .=                    ' ORDER BY COUNT(id) '.$orderDir.', SUM(weighting) '.$orderDir;
        } else {
          $q .=                    ' ORDER BY SUM(weighting) '.$orderDir.', COUNT(id) '.$orderDir;
        }
        
        $q .=                      ' LIMIT '.$DB->Quote($limit).')
                                           AND user_id_for='.$DB->Quote($this->user->id).'
                                     GROUP BY weighting, author_int';
        $res = $DB->execute( $q );
        if( !$res ) {
          throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        $stats = array();
    
        // set some defines to parametrize following __compare_users
        define('ORDER_CRITERION', $orderBy);
        // second sort criterion
        $orderBy2 = '';
        if ($orderBy == 'number') {
            $orderBy2 = 'points';
        } else if ($orderBy == 'points') {
            $orderBy2 = 'number';
        }
        define('ORDER_CRITERION_2', $orderBy2);
        if ($orderDir=='asc') {
          define('ORDER_DIR', '-1');
        } else {
          define('ORDER_DIR', '1');
        }
    
        // quasi-anonymous function to sort statistics
        function __compare_users($a,$b) {
          $cmp = ORDER_DIR*$b[ORDER_CRITERION] - ORDER_DIR*$a[ORDER_CRITERION];
          if ($cmp == 0) {
            return ORDER_DIR*$a[ORDER_CRITERION_2] < ORDER_DIR*$b[ORDER_CRITERION_2];
          }
          return $cmp;
        }
    
        // build up return array
        while (!$res->EOF) {
            //$akey = ''.$res->fields['author_int'].'_'.$res->fields['author_ext'];
            $akey = ''.$res->fields['author_int'];
    
            // map weightings of entries with admin points
            // to normal range (-1,0,1)
            $gb_weighting = $res->fields['weighting'];
            if ($gb_weighting > 1) $gb_weighting = 1;
            if ($gb_weighting < -1) $gb_weighting = -1;
            
            if(!array_key_exists($akey, $stats)) {
                $stats[$akey] = array();
            }
            if(!array_key_exists('number',$stats[$akey])) {
                $stats[$akey]['number'] = 0;
            }
            if(!array_key_exists('weighting' . ($gb_weighting+1),$stats[$akey])) {
                $stats[$akey]['weighting' . ($gb_weighting+1)] = 0;
            }
            if(!array_key_exists('points',$stats[$akey])) {
                $stats[$akey]['points'] = 0;
            }
            if(!array_key_exists('efficiency',$stats[$akey])) {
                $stats[$akey]['efficiency'] = 0;
            }
                
            $stats[ $akey ]['number'] += $res->fields['number'];
            $stats[ $akey ]['weighting'.($gb_weighting+1)] += $res->fields['number'];
            $stats[ $akey ]['points'] += $res->fields['points'];
            $stats[ $akey ]['author_int'] = $res->fields['author_int'];
            //$stats[ $akey ]['author_ext'] = $res->fields['author_ext'];
            // accumulate to find global maximum
            $stats[ $akey ]['efficiency'] = max($res->fields['time_since_first'],$stats[ $akey ]['efficiency']);
            $res->MoveNext();
        }
        // sort array by given criterion
        usort($stats,'__compare_users');
    
        foreach ($stats as $key => $stat) {
          $eff = $stats[$key]['efficiency'] / 86400;
          if ($eff > 0.0) {
            $eff = $stat['points'] / max($eff, 1.0);
            $eff = sprintf("%.2f",$eff);
            $stats[$key]['efficiency'] = $eff;
          } else {
            $stats[$key]['efficiency'] = '0.00';
          }
          //$stats[$key]['author'] = UserExternalModel::getUserByIntOrExtId($stats[$key]['author_int'], $stats[$key]['author_ext']);
          $stats[$key]['author'] = UserExternalModel::getUserByIntOrExtId($stats[$key]['author_int'], null);
        }
    
        return $stats;
    }
    
    /**
     * replaces authorship of author by newAuthor
     * @param UserModel
     * @param UserModel
     * 
     * @note the id param is not working now; it could be extended to restrict the replacement to entries given by $id-param
     */
    public static function replaceAuthor($author, $newAuthor, $id = null) {
        return parent::replaceAuthor('guestbook', $author, $newAuthor, $id);
    }
}

?>
