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

// $Id: course_file_filter.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/course/course_file_filter.php $

require_once MODEL_DIR . '/base/base_filter.php';

/**
 * the class provides (SQL) filter statements
 * for course files
 *  
 * @package Models
 * @subpackage Course
 */
class CourseFileFilter extends BaseFilter {
	public function __construct($filter) {
		parent::__construct($filter);
        $this->dateColumn = 'upload_time';
        $this->authorColumn = 'author_id';        
	}
    
    const FILTER_CATEGORY = 'category';
    const FILTER_SEMESTER = 'semester';
    const FILTER_ORDER = 'order';
    const FILTER_ORDERDIR = 'orderDir';
    const FILTER_RATING = 'rating';
	const FILTER_DESCRIPTION = 'description';
	const FILTER_FILENAME = 'filename';
	const FILTER_COURSE = 'course';
	const FILTER_AUTHOR = 'author';
    
    const FILTER_SEARCH = 'search';
    
    public function getSQLFilterString() {
        return $this->getSQLWhereString() . ' ' . $this->getSQLOrderString();
    }
    public function getSQLWhereString() {
        $DB = Database::getHandle();
        
        // query to build
        $q = '';
        if (array_key_exists(self::FILTER_CATEGORY, $this->filterOptions)) {
        	$q .= $this->getFilterCategory($DB);
        }
        if (array_key_exists(self::FILTER_SEMESTER, $this->filterOptions)) {
            $q .= $this->getFilterSemester($DB);
        }
        if (array_key_exists(self::FILTER_RATING, $this->filterOptions)) {
            $q .= $this->getFilterRating($DB);
        }
        if (array_key_exists(self::FILTER_DESCRIPTION, $this->filterOptions)) {
            $q .= $this->getFilterDescription($DB);
        }
        if (array_key_exists(self::FILTER_FILENAME, $this->filterOptions)) {
            $q .= $this->getFilterFilename($DB);
        }
        if (array_key_exists(self::FILTER_COURSE, $this->filterOptions)) {
            $q .= $this->getFilterCourse($DB);
        }
		if (array_key_exists(self::FILTER_AUTHOR, $this->filterOptions)) {
            $q .= $this->getFilterAuthor($DB);
        }
        if (array_key_exists(self::FILTER_SEARCH, $this->filterOptions)) {
            $q .= $this->getFilterSearch($DB);
        }
       
        return parent::getSQLFilterString() . $q;
    }
    
    protected function getSQLOrderString(){
        $q = '';
        // check valid range of arguments
        if( !array_key_exists(self::FILTER_ORDERDIR, $this->filterOptions) 
			|| !array_key_exists(self::FILTER_ORDERDIR, $this->filterOptions)
            || ($this->filterOptions[self::FILTER_ORDERDIR] != 'asc' 
                and $this->filterOptions[self::FILTER_ORDERDIR] != 'desc') ) {
            $orderDir = 'desc';
        }else{
        	$orderDir = $this->filterOptions[self::FILTER_ORDERDIR];
        }
        
         // sort result by given criterion
        if (array_key_exists(self::FILTER_ORDER, $this->filterOptions) 
                && $this->filterOptions[self::FILTER_ORDER] == 'semester') {
            $q.=
          ' ORDER BY semester_id ' . $orderDir;
        } elseif (array_key_exists(self::FILTER_ORDER, $this->filterOptions) 
                && $this->filterOptions[self::FILTER_ORDER] == 'category') {
            $q.=
          ' ORDER BY category_id ' . $orderDir;
        } elseif (array_key_exists(self::FILTER_ORDER, $this->filterOptions) 
                && $this->filterOptions[self::FILTER_ORDER] == 'time'){
             $q.=
          ' ORDER BY cf.insert_at ' . $orderDir;
        } elseif (array_key_exists(self::FILTER_ORDER, $this->filterOptions) 
                && $this->filterOptions[self::FILTER_ORDER] == 'downloads'){
             $q.=
          ' ORDER BY download_number ' . $orderDir;
        } elseif (array_key_exists(self::FILTER_ORDER, $this->filterOptions) 
                && $this->filterOptions[self::FILTER_ORDER] == 'costs'){
             $q.=
          ' ORDER BY costs ' . $orderDir;
        } elseif (array_key_exists(self::FILTER_ORDER, $this->filterOptions) 
                && $this->filterOptions[self::FILTER_ORDER] == 'rating'){
             $q.=
          ' ORDER BY rating ' . $orderDir;
        } elseif (array_key_exists(self::FILTER_ORDER, $this->filterOptions)
					&& $this->filterOptions[self::FILTER_ORDER] == 'description'){
			 $q .=
		   ' ORDER by description '.$orderDir;
		} elseif (array_key_exists(self::FILTER_ORDER, $this->filterOptions)
					&& $this->filterOptions[self::FILTER_ORDER] == 'filename'){
			 $q .=
		   ' ORDER by file_name '.$orderDir;
		} elseif (array_key_exists(self::FILTER_ORDER, $this->filterOptions)
					&& $this->filterOptions[self::FILTER_ORDER] == 'course'){
			 $q .=
		   ' ORDER by '.DB_SCHEMA . '.courses.name '.$orderDir;
		} elseif (array_key_exists(self::FILTER_ORDER, $this->filterOptions)
					&& $this->filterOptions[self::FILTER_ORDER] == 'author'){
			 $q .=
			 ' ORDER by '.DB_SCHEMA . '.users.name '.$orderDir;
		}
        if($q == ''){
        	$q= ' ORDER BY cf.insert_at desc';
        }
        return $q;

    }
    
    protected function getFilterCategory($DB) {    
        return ' AND category_id = ' . $DB->Quote($this->filterOptions[self::FILTER_CATEGORY]->id);
    }
    
    protected function getFilterSemester($DB) {    
        return ' AND semester_id = ' . $DB->Quote($this->filterOptions[self::FILTER_SEMESTER]->id);
    }
    
    protected function getFilterRating($DB) {    
        return ' AND rating_category_id = (SELECT id 
                                             FROM ' . DB_SCHEMA . '.courses_files_ratings_categories 
                                            WHERE name = ' . $DB->Quote(CourseFileRatingCategoryModel::getQuickvoteCategoryName()) . ')
                 AND rating >=  ' . $this->filterOptions[self::FILTER_RATING];
    }
	
	protected function getFilterDescription($DB){
		return ' AND description ILIKE '.$DB->Quote('%'.$this->filterOptions[self::FILTER_DESCRIPTION].'%');
	}
	
	protected function getFilterFilename($DB){
		return ' AND file_name ILIKE '.$DB->Quote('%'.$this->filterOptions[self::FILTER_FILENAME].'%');
	}
	
	protected function getFilterCourse($DB){
		$courses = $filter->getOption(self::FILTER_COURSE);	
		return ' AND course_id IN (' . Database::makeCommaSeparatedString($courses, 'id') .') ';
	}
	
	protected function getFilterAuthor($DB){
		return ' AND author_id = '.$DB->Quote($this->filterOptions[self::FILTER_AUTHOR]->id);
	}
    /**
     * the search can be found in filename or in desctiption
     */
    protected function getFilterSearch($DB){
    	return ' AND (description ILIKE '.$DB->Quote('%'.$this->filterOptions[self::FILTER_SEARCH].'%')
               . ' OR file_name ILIKE '.$DB->Quote('%'.$this->filterOptions[self::FILTER_SEARCH].'%')
               . ')';
    }
}

?>
