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

// $Id: advanced_filter.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/advanced_filter.php $

require_once MODEL_DIR . '/base/base_filter.php';

/**
 * the class provides (SQL) filter statements
 * for models with external authors and tsearch support
 *  
 * @package Models
 */
abstract class AdvancedFilter extends BaseFilter {
    protected $tsearchAvailable = true;
    
    protected function getFilterAuthor($DB) {
        // need to overwrite method because we need to distinguish between internal
        // and external users
        $authors = $this->filterOptions[BaseFilter::FILTER_AUTHOR];
        $externalIds = array();
        $internalIds = array();
        foreach ($authors as $author) {
            if ($author->isExternal()) {  
                array_push($externalIds, $author->localId);
            } else {
                array_push($internalIds, $author->id);
            }
        }

        $q = '';
        if (count($externalIds)) {
            $q .= ' AND author_ext IN (' . Database::makeCommaSeparatedString($externalIds) . ')';
        }
        if (count($internalIds)) {
            $q .= ' AND author_int IN (' . Database::makeCommaSeparatedString($internalIds) . ')';
        }
        return $q;
    }
    
    protected function getFilterText($DB) {
        if (!TSEARCH2_AVAILABLE) {
            return parent::getFilterText($DB);
        }
        
        $string = $this->filterOptions[BaseFilter::FILTER_TEXT];
        return ' AND idx_fulltext @@ to_tsquery(\'simple\', ' . $DB->Quote(ParserFactory::getRawParser()->parseQuery($string)) . ')';
    }
}

?>
