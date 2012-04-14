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

// $Id: course_file_rating_model.php 5743 2008-03-25 19:48:14Z ads $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/course/course_file_rating_model.php $

require_once MODEL_DIR.'/base/base_model.php';

/**
 * class representing one rating by one user for a course file
 *
 * @package Models
 * @subpackage Course
 */
class CourseFileRatingModel extends BaseModel {
    protected $author;
    protected $time;
    /**
     * the ratings separated by categories
     */
    protected $ratingsSingle;
    /**
     * total rating according to weight vector
     */
    protected $totalRating;
    
    /**
     * @param int $id
     * @param UserModel $author
     * @param string $time
     * @param array $ratingsSingle associative array of associative arrays
     *          'ratingCategoryName' => ['comment' => user comment to rating (string)
     *                                   'rating' => rating value (int)
     *                                   'category' => rating category (CourseFileRatingCategoryModel)]
     */
    public function __construct($id = null, $author = null, $time = null, $ratingsSingle = array()) {
        $this->id = $id;
        $this->author = $author;
        $this->time = $time;
        $this->totalRating = 0;        
        $this->ratingsSingle = $ratingsSingle;
    }
    
    /**
     * gives all ratings of the given file
     * @param FileModel $file
     * @return array array of CourseFileRatingModel
     */
    public static function getRatingsByFile($file) {
        $ratingCatgeories = CourseFileRatingCategoryModel::getAllCategories();
        
        $DB = Database::getHandle();
        $q = 'SELECT cfr.*,cfrs.*,
                     to_char(cfr.time, \'DD.MM.YYYY, HH24:MI\') AS pretty_time,
                     cfrc.name, cfrc.type
                FROM '.DB_SCHEMA.'.courses_files_ratings AS cfr,
                     '.DB_SCHEMA.'.courses_files_ratings_single AS cfrs,
                     '.DB_SCHEMA.'.courses_files_ratings_categories AS cfrc
               WHERE cfr.file_id='.$DB->Quote($file->id).'
                 AND cfrs.rating_id=cfr.id
                 AND cfrs.rating_category_id=cfrc.id
            ORDER BY date_trunc(\'second\',time) DESC, cfr.user_id, cfrs.rating_category_id';
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }

        // initialize return array
        $ratings = array();
        
        // last (rating) id in loop
        $lastId = 0;
        
        // one rating by a user is temporarily stored in this variable
        // because it it worked on during several loop runs
        $tempRating = null;
        
        // array to store user ids temporarily to fetch objects later at once
        $tempUsers = array();
        
        // weights to calculate total rating from categories
        $weights = self::getDefaultWeighting();
        
        // set parser settings for comment:
        // always format with BBCode and smileys
        $ps_array = ParserFactory::createParserFromSettings(
            array(BaseEntryModel::PARSE_AS_FORMATCODE => true,
                  BaseEntryModel::PARSE_AS_SMILEYS => true));
        
        // fill return array
        foreach ($res as $k => $row) {
            // if first loop run, store author
            if ($lastId == 0) {
                $tempRating = new CourseFileRatingModel($row['rating_id'], 
                    $row['user_id'], $row['pretty_time']);
                array_push($tempUsers, $row['user_id']);
            }

            // on user change, add new rating packge
            if ($lastId != $row['rating_id'] && $lastId != 0) {
                array_push($ratings,$tempRating);
                // create new object
                $tempRating = new CourseFileRatingModel($row['rating_id'], 
                    $row['user_id'], $row['pretty_time']);
                array_push($tempUsers, $row['user_id']);
            }
            
            // if rating consists of free text, parse it
            if ($row['type'] == FORM_ELEMENT_TEXT) {
                // parse rating iteratively
                // apply parsers from ParseStrategy-array
                foreach ($ps_array as $parseStrategy) {
                    $row['rating'] = $parseStrategy->parse($row['rating']);
                }
            }

            $tempRating->ratingsSingle[$ratingCatgeories[$row['rating_category_id']]->name] =
                  array('rating'    => $row['rating'],
                        'category'  => $ratingCatgeories[$row['rating_category_id']]);
            $tempRating->totalRating += $weights[$ratingCatgeories[$row['rating_category_id']]->name] * $row['rating'];
            
            $lastId = $row['rating_id']; 
        }
        // add last rating if existent
        if ($tempRating != null) {
        	array_push($ratings,$tempRating);
        }
        
        // fetch needed user objects
        $tempUsers = UserProtectedModel::getUsersByIds($tempUsers);
        // use user objects for ratings
        foreach ($ratings as $r) {
        	$r->author = $tempUsers[$r->author];
        }
        
        return $ratings;
    }
    
    /**
     * adds this rating to the given file
     * <b>note</b>: it must be ensured that all current categories have a rating
     * this checking is _not_ done by this method
     * @param FileModel $file
     */
    public function addRatingToFile($file) {
    	$DB = Database::getHandle();
        $DB->StartTrans();
        
        // insert file rating
        $q = 'INSERT INTO ' . DB_SCHEMA . '.courses_files_ratings
                    (file_id, user_id)
                VALUES 
                    (' . $DB->Quote($file->id) . ',' . $DB->Quote($this->author->id) . ')';
        $res = &$DB->execute( $q );
        if (!$res) {
            $DB->CompleteTrans();
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return;
        }
        
        // insert single ratings by category
        foreach ($this->ratingsSingle as $r) {
            $q = 'INSERT INTO ' . DB_SCHEMA . '.courses_files_ratings_single
                        (rating_id, rating_category_id, rating)
                    VALUES 
                        ((SELECT MAX(id) FROM ' . DB_SCHEMA . '.courses_files_ratings),
                        ' . $DB->Quote($r['category']->id) . ','
                          . $DB->Quote($r['rating']) . ')';
            $res = &$DB->execute( $q );
            if (!$res) {
                $DB->CompleteTrans();
                throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
                return;
            }
        }
        
        // category medians should have been updated via trigger now
        
        $DB->CompleteTrans();
    }
    
    /**
     * deletes this rating from the given file
     * @param FileModel
     */
    public function deleteRatingFromFile($file) {
    	$DB = Database::getHandle();
        
        // delete file rating
        $q = 'DELETE FROM ' . DB_SCHEMA . '.courses_files_ratings
                    WHERE id=' . $DB->Quote($this->id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
            return;
        }
        
        // deletion of "single ratings" is done via DB-foreign key-constraint
    }
    
    /**
     * returns default weighting vector for total rating calculation
     * @return array associative array; keys are the categories, values: their weights
     */
    public static function getDefaultWeighting() {
        $ratingCatgeories = CourseFileRatingCategoryModel::getAllCategories();
        
        $ratingWeighting = array();
        foreach ($ratingCatgeories as $id => $cat) {
            $ratingWeighting[$cat->name] = 1.0 / count($ratingCatgeories);
        }
        return $ratingWeighting;
    }
    
    public function getAuthor(){ return $this->author; }
    public function getTime(){ return $this->time; }
    public function getRatingsSingle(){ return $this->ratingsSingle; }
    public function getTotalRating(){ return sprintf("%.2f", $this->totalRating); }        
}

?>
