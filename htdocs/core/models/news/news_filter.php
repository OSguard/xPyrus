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

require_once CORE_DIR . '/database.php';

/**
 * A filter for news entries
 * 
 * @author Kyle
 */
class NewsFilter {
    
    /** the group id should filtered */
    const FILTER_GROUPS = 'group_id';
    
    /** the visible status should filtered */
    const FILTER_VISIBLE = 'is_visible';
    
    /** the sticky status should filtered */
    const FILTER_STICKY = 'is_sticky';

    /** the start date should filtered */
    const FILTER_START_DATE = 'start_date';

    /** the end date should filtered */
    const FILTER_END_DATE = 'end_date';

    /** constant for the current date */
    const CURRENT_DATE = 'CURRENT_DATE';
    
    /** the build filter */
    private $filter = '';
    
    /** Database instance used to quote values */
    private $DB;
    
    public function __construct() {
        $this->DB = Database::getHandle();
    }
    
    /** 
     * Add a Filter
     * 
     * @param string $logicalOperator can be AND or OR
     * @param string $filterType one of the FILTER_* above
     * @param string $operator the operator to test e.g. <, >, <=, >=, =
     * @param misc   $value the value to test for groups this can be a array of group models
     */
    public function addFilter($filterType, $operator, $value, $logicalOperator = 'AND') {
        if (!in_array($operator, array('<', '<=', '=', '>=', '>')))
			throw new ArgumentException('operator', $operator);

		if (!in_array($logicalOperator, array('', 'AND', 'OR', 'NOT', 'IN', 'NOT IN')))
        	throw new ArgumentException('logicalOperator', $logicalOperator);
            
		if (!in_array($filterType, array(self::FILTER_GROUPS, self::FILTER_VISIBLE, 
        		self::FILTER_STICKY, self::FILTER_START_DATE, self::FILTER_END_DATE)))
			throw new ArgumentException('filterType', $filterType);
       
        /* special handler for groups */  
        if($filterType == NewsFilter::FILTER_GROUPS) {
            /* build a string with the ids of the groups */
            if(count($value) == 0){
                /*
                 * TODO: wenn ich nach leeren Gruppen suche darf das SQL statment keinen Wert zurückliefern
                 * ich weiß nicht ob es einen besseren weg gibt, als zu fordern, das der eintrag
                 * den wert null zurückliefert?
                 * Bzw. ich weiß nicht ob er mit allen $logicalOperator zusammen arbeitet? Was ist, wenn man ein fettes
                 * NOT IN bauen will?
                 */
                $this->filter .= ' AND id = 0 ';
                return array();
            }
        
            $ids = '';
                
            foreach($value as $group) {
                $ids .= $this->DB->quote($group->id) . ', ';
            }
            
            $ids = substr($ids, 0, strlen($ids) - 2);

            $this->filter .= " $logicalOperator $filterType IN (" . $ids . ')';           
            return;
        } 

        /* the boolean types */
        if($filterType == self::FILTER_VISIBLE || $filterType == self::FILTER_STICKY) {
            $this->filter .= " $logicalOperator $filterType $operator " . $this->DB->quote(Database::makeBooleanString($value));           
            return;
        }

        if($value != self::CURRENT_DATE)
            $this->filter .= " $logicalOperator $filterType $operator " . $this->DB->quote($value);
        else
            $this->filter .= " $logicalOperator $filterType $operator CURRENT_DATE";
    }
    
    public function getFilterString() {
        return $this->filter;
    }
}
?>
