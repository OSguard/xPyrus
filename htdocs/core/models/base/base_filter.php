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

// $Id: base_filter.php 5743 2008-03-25 19:48:14Z ads $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/base_filter.php $

/**
 * class for providing suitable SQL statements
 * to filter on certain classes of entries
 * @package Models
 * @subpackage Base
 */
abstract class BaseFilter {
	/**
     * @var array
	 */
	protected $filterOptions;
    /**
     * @var string
     * name of date column to be filtered against
     */
    protected $dateColumn = null;
    /**
     * @var string
     * name of author column to be filtered against
     */
    protected $authorColumn = null;
    /**
     * @var string
     * name of entry text column to be filtered against
     */
    protected $entryTextColumn = null;
    
    const FILTER_ENTRYDATE = 'entrydate';
    const FILTER_ENTRYDATE_SINGLE = 'entrydatesingle';
    const FILTER_AUTHOR   = 'author';
    const FILTER_AT       = 'filter_at'; // id of blog/gb owner etc. to apply the filter at
    const FILTER_TEXT     = 'text';
    
    /**
     * constructs a filter based on given criteria
     * @param array $filter filter options: 
     *      associative array with possible keys 'FILTER_AUTHOR'
     *      and 'FILTER_ENTRYDATE'; the expected types are
     *      'FILTER_AUTHOR': UserModel
     *      'FILTER_ENTRYDATE': array with keys 'from' and/or 'to':
     *                          values are string dates in 
     *                          (ISO) 'YYYY-MM-DD' format
     *      'FILTER_ENTRYDATE_SINGLE': array('YYYY','MM', 'DD'[optional])
     */
    public function __construct($filter) {
        $this->filterOptions = $filter;
        if ($this->dateColumn == null) {
            $this->dateColumn = 'entry_time';
        }
        if ($this->authorColumn == null) {
            $this->authorColumn = 'user_id';
        }
        if ($this->entryTextColumn == null) {
            $this->entryTextColumn = 'entry_raw';
        }
    }
    
    /**
     * returns filter string for SQL queries with respect to filtering
     *
     * e.g. " AND user_id = 6" (if filtering by user(name) is needed)
     * 
     * the return string always starts and ends with a whitespace,
     * the first conditional clause is always introduced via AND
     * as well as the other conditions are connected via AND
     *
     * @return string
     */
    public function getSQLFilterString() {
        $DB = Database::getHandle();
        
        // query to build
        $q = '';
        
        if (array_key_exists(self::FILTER_ENTRYDATE, $this->filterOptions) and
                array_key_exists('from', $this->filterOptions[self::FILTER_ENTRYDATE])) {
        	$q .= $this->getFilterDateFrom($DB);
        }
        if (array_key_exists(self::FILTER_ENTRYDATE, $this->filterOptions) and
                array_key_exists('to', $this->filterOptions[self::FILTER_ENTRYDATE])) {
            $q .= $this->getFilterDateTo($DB);
        }
        if (array_key_exists(self::FILTER_ENTRYDATE_SINGLE, $this->filterOptions)) {
            $q .= $this->getFilterDateSingle($DB);
        }
        if (array_key_exists(self::FILTER_AUTHOR, $this->filterOptions)) {
            $q .= $this->getFilterAuthor($DB);
        }
        if (array_key_exists(self::FILTER_TEXT, $this->filterOptions)) {
            $q .= $this->getFilterText($DB);
        }
        return $q . ' ';
    }
    
    /**
     * filter by author
     * @return string
     */
    protected function getFilterAuthor($DB) {
    	return ' AND ' . $this->authorColumn . ' IN (' . Database::makeCommaSeparatedString($this->filterOptions[self::FILTER_AUTHOR], 'id') . ')';
    }
    
    /**
     * filter by lower date bound
     * @return string
     */
    protected function getFilterDateFrom($DB) {    	
        return ' AND ' . $this->dateColumn . ' >= ' . $DB->Quote($this->filterOptions[self::FILTER_ENTRYDATE]['from'] );
    }
    
    /**
     * filter by upper date bound
     * @return string
     */
    protected function getFilterDateTo($DB) {    
        return ' AND ' . $this->dateColumn . ' <= (date' . $DB->Quote($this->filterOptions[self::FILTER_ENTRYDATE]['to']) . " + 1)";

    }
    
    /**
     * filter by entry text
     * @return string
     */
    protected function getFilterText($DB) {
        $string = $this->filterOptions[self::FILTER_TEXT];
        $tok = explode(' ', $string);
        $ret = '';
        foreach ($tok as $t) {
            $ret .= ' AND ' . $this->entryTextColumn . ' ILIKE ' . $DB->Quote('%' . trim($t) . '%');
        }
        return $ret;
    }
    
    /**
     * filter by date or part of date
     * @return string
     */
    protected function getFilterDateSingle($DB) {    
        $q =  ' AND EXTRACT(YEAR FROM ' . $this->dateColumn . ') = ' . $DB->Quote($this->filterOptions[self::FILTER_ENTRYDATE_SINGLE][0]) . '
                AND EXTRACT(MONTH FROM ' . $this->dateColumn . ') = ' . $DB->Quote($this->filterOptions[self::FILTER_ENTRYDATE_SINGLE][1]);
        if (count($this->filterOptions[self::FILTER_ENTRYDATE_SINGLE]) == 3) {
        	$q .=  ' AND EXTRACT(DAY FROM ' . $this->dateColumn . ') = ' . $DB->Quote($this->filterOptions[self::FILTER_ENTRYDATE_SINGLE][2]);
        }
        return $q;
    }
    
    public function getOption($name) {
    	if (array_key_exists($name, $this->filterOptions)) {
    		return $this->filterOptions[$name];
    	}
        return null;
    }
}

?>
