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

// $Id: interactive_user_element_model.php 5743 2008-03-25 19:48:14Z ads $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/interactive_user_element_model.php $

abstract class InteractiveUserElementModel extends BaseModel {
    protected $filter;
        
    public function __construct() {
        parent::__construct();
        
        // no filtering by default
        $this->filter = null;
    }
    
    /**
     * replaces authorship of author by newAuthor
     * @param UserModel
     * @param UserModel
     * 
     * @note the id param is not working now; it could be extended to restrict the replacement to entries given by $id-param
     */
    protected static function replaceAuthor($tableName, $author, $newAuthor, $id = null) {
        // if author is not valid
        if ($author->id == 0 or $newAuthor->id == 0) {
            return false;
        }

        $DB = Database::getHandle();
       
        // replace author
        $q =  'UPDATE ' . DB_SCHEMA . '.' . $tableName . '
                  SET author_int = ' . $DB->Quote($newAuthor->id) . '
                WHERE author_int = ' . $DB->Quote($author->id);
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // replace author on attachments
        $q =  'UPDATE ' . DB_SCHEMA . '.attachments
                  SET author_id = ' . $DB->Quote($newAuthor->id) . '
                WHERE author_id = ' . $DB->Quote($author->id) . '
                  AND EXISTS (SELECT * 
                                FROM ' . DB_SCHEMA . '.' . $tableName . '_attachments 
                               WHERE ' . DB_SCHEMA . '.' . $tableName . '_attachments.attachment_id = ' . DB_SCHEMA . '.attachments.id)';
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        return true;
    }
    
    /**
     * sets filter options
     * @param BaseFilter
     */
    public function setFilter($filter) {
        $this->filter = $filter;
    }
    
    final protected function getSQLFilterString() {
    	if ($this->filter != null) {
    	   return $this->filter->getSQLFilterString();
        } else {
        	return '';
        }
    }
    
    /**
     * build an array of page numbers to offer a navigation across this InteractiveUserElement
     * @param string $dataName name of data entry in __SCHEMA__.user_data that counts the related entry number
     *                         e.g. 'blog_entries', 'gb_entries'
     * @return array
     * @throws DBException on error
     */
    protected function getCounter($dataName, $page, $entriesPerPage) {
        $DB = Database::getHandle();
    
        // determine number of entries
        $entries = $this->user->$dataName();
        
        $number = ceil($entries / $entriesPerPage);
        return self::nonLinearCounter($number,$page);
    }
    
    public static function nonLinearCounter($total, $position) {
        /* non linear counter - code by Tramp and Jester */
        
        #
        # non linear counter
        #
        # this file contains a non linear counter
        #
        # written by Norbert Kolb <norbert.kolb@la-puckhunters.de>
        #
        # comments:
        #
        # history:
        #
        
        # nlc()
        #
        # non linear counter - code by Tramp and Jester
        #
        # parameter:
        #  - highest value
        #  - actual position
        #  - array with steps
        #  - steps (optional)
        # return:
        #  - array with positions
        //function nlc ($total, $position, $incborders = array(1,2,5,10,20,50,100,1000,10000), $countperinc = 2) {
        //$incborders = array(1,2,5,10,50,100,1000,10000);
        $incborders = array();
        if ($position < 100) {
            $incborders = array(1,2,5,20,100,1000,10000);
        } else {
            $incborders = array(1,2,100,10000);
        }
        $countperinc = 1;
        
        $upper = $position + 1;
        $lower = $position - 1;
        $toprint = array($position);
        for ($value = 0; $value <= count($incborders) - 1; $value++) {
          $v = ($incborders[$value] == 0) ? 1 : $incborders[$value];
          $v1 = (!isset($incborders[$value+1]) || $incborders[$value+1] == 0) ? 1 : $incborders[$value+1];
        
          for ($i=0; $i<$countperinc; $i++) {
            array_push($toprint, $upper, $lower);
            if ($i < $countperinc-1) {
              $upper += $v;
              $lower -= $v;
            }
          }
          $upper = (floor($upper / $v1) + 1) * $v1;
          $lowernew = (floor($lower / $v1)) * $v1;
          $lower = ($lowernew == $lower) ? $lowernew-$v1 : $lowernew;
          if (($upper > $total) && ($lower < 0)) {
            $value = count($incborders) - 1;
          }
        }
        sort($toprint);
        $return = array();
        /* remove negative or too big values */
        for($i = 0; $i <= count($toprint) - 1; $i++) {
          if (($toprint[$i] >= 0) && ($toprint[$i] <= $total)) {
            array_push($return, $toprint[$i]);
          }
        }
        /* make sure the array retoured starts with a 1 */
        if (array_key_exists(1,$return) and $return[1] == 1) {
          array_shift($return);
        }
        if ($return[0] == 0) {
          $return[0] = 1;
        }
        /* make sure, the last value is in the array */
        if ($return[count($return) - 1] < $total) {
          array_push($return, $total);
        }
        return $return;
    }
}

?>
