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

# $Id: feature_model.php 5807 2008-04-12 21:23:22Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/feature_model.php $

require_once MODEL_DIR.'/base/base_model.php';
require_once MODEL_DIR.'/base/right_model.php';

/**
 * class representing one feature on UniHelp
 *
 * @package Models
 * @subpackage Base
 */
class FeatureModel extends BaseModel {
    protected $name;
    protected $pointLevel;
    protected $isEnabled;
    protected $isAvailable;
    /**
     * @var boolean
     * true, iff feature status is supposed to be consistent with
     * value stored in the database
     */
    protected $isSaved;

    protected $description;
    protected $descriptionEnglish;
    protected $pictureURL;
    protected $rightId;

    public function getName() {
        return $this->name;
    }
    public function getPointLevel() {
        return $this->pointLevel;
    }
    public function isEnabled() {
        return $this->isEnabled;
    }
    public function isSaved() {
        return $this->isSaved;
    }
    public function isAvailable() {
        return $this->isAvailable;
    }
    public function getDescription() {
        return $this->description;
    }
    public function getDescriptionEnglish() {
        return $this->descriptionEnglish;
    }
    public function getPictureURL() {
        return $this->pictureURL;
    }
    public function getRightId() {
        return $this->rightId;
    }
    
    public function setName($prop) { $this->name = $prop; }
    public function setPointLevel($prop) { $this->pointLevel = $prop; }
    public function setEnabled($prop) { $this->isEnabled = $prop; }
    public function setAvailable($prop) { $this->isAvailable = $prop; }
    public function setSaved($prop) { $this->isSaved = $prop; }
    public function setDescription($prop) { $this->description = $prop; }
    public function setDescriptionEnglish($prop) { $this->descriptionEnglish = $prop; }
    public function setPictureURL($prop) { $this->pictureURL = $prop; }
    public function setRightId($prop) { $this->rightId = $prop; }
    

    public function __construct($id = null, $name = null, $pointLevel = null, 
            $enabled = false, $available = false) {
        $this->id = $id;
        $this->name = $name;
        $this->pointLevel = $pointLevel;
        $this->isEnabled = $enabled;
        $this->isAvailable = $available;
        $this->isSaved = true;
    }
    
    /**
     * @param array array of int
     * @param array array of int
     * @param UserModel
     * @return boolean true iff feature-status has changed
     */
    public static function changeFeatures($featuresToAdd, $featuresToDelete, $user) {
        $DB = Database::getHandle();
        $DB->StartTrans();
        
        $somethingChanged = false;
        
        if (count($featuresToDelete) > 0) {
            $q = 'DELETE FROM ' . DB_SCHEMA . '.user_features 
                        WHERE feature_id IN (' . Database::makeCommaSeparatedString($featuresToDelete) . ')
                          AND user_id = ' . $DB->Quote($user->id);
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
                return false;
            }
            $somethingChanged = true;
        }
        
        foreach ($featuresToAdd as $id) {
            $q = 'INSERT INTO ' . DB_SCHEMA . '.user_features (feature_id, user_id) 
                       VALUES (' . $DB->Quote($id). ',' . $DB->Quote($user->id) . ')';
            $res = $DB->execute($q);
            if (!$res) {
                throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
                return false;
            }
            $somethingChanged = true;
        }
        
        if (!$DB->CompleteTrans()) {
        	throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
            return false;
        }
        
        return $somethingChanged;
    }
    
    /**
     * collects all features and marks the ones that specified user has enabled
     *
     * @param UserModel $user user to examine
     * @return array associative array of FeatureModel; hash keys are the feature ids
     * @throws DBException on DB error
     */
    public static function getAllFeaturesWithUser($user) {
        if (!$user->id) {
            return array(); 
        }
        
        $DB = Database::getHandle();
        
        // load all basic rights, that everyone has
        $q = 'SELECT f.point_level, f.id, r.name, (uf.user_id>0) AS enabled,
                     f.description, f.description_english, f.picture_url
                FROM '.DB_SCHEMA.'.rights AS r,
                     '.DB_SCHEMA.'.features AS f
           LEFT JOIN '.DB_SCHEMA.'.user_features AS uf
                  ON uf.feature_id = f.id AND uf.user_id = ' . $DB->Quote($user->id) . '
               WHERE f.right_id = r.id
            ORDER BY point_level ASC, name ASC';
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // initialize return array
        $featuresArray = array();
        foreach ($res as $k => $row) {
            $feat = new FeatureModel($row['id'], $row['name'], $row['point_level'], 
                Database::convertPostgresBoolean($row['enabled']),
                $user->getPoints() >= $row['point_level']);
            $feat->description = $row['description'];    
            $feat->descriptionEnglish = $row['description_english'];
            $feat->pictureURL = $row['picture_url'];
            $featuresArray[$row['id']] = $feat;
        }
        
        return $featuresArray;
    }
    
    /**
     * collects all feature ids that specified user has enabled
     *
     * @param int $user_id id of user to examine
     * @return array associative array of boolean, hash keys are the related right ids
     * @throws DBException on DB error
     */
    public static function getAllFeatureIdsByUser($user_id) {
        if (!$user_id) {
            return array();	
        }
        
        $DB = Database::getHandle();
        
        // load all user features
        $q = 'SELECT feature_id, right_id
                FROM '.DB_SCHEMA.'.user_features AS uf,
                     '.DB_SCHEMA.'.features AS f
               WHERE f.id = uf.feature_id
                 AND user_id = ' . $DB->Quote($user_id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // initialize return array
        $featuresArray = array();
        foreach ($res as $k => $row) {
            $featuresArray[$row['right_id']] = true;
        }
        
        return $featuresArray;
    }
    
    public static function getAllFeatureIds() {
        $DB = Database::getHandle();
        
        // load all basic rights, that everyone has
        $q = 'SELECT right_id
                FROM '.DB_SCHEMA.'.features';
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // initialize return array
        $featuresArray = array();
        foreach ($res as $k => $row) {
            $featuresArray[$row['right_id']] = true;
        }
        
        return $featuresArray;
    }
    
    public static function getAllFeatures() {
        $DB = Database::getHandle();
        
        // load all basic rights, that everyone has
        $q = 'SELECT f.* , r.name AS name
                FROM '.DB_SCHEMA.'.features AS f,
                     '.DB_SCHEMA.'.rights AS r
                WHERE f.right_id = r.id';
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        // initialize return array
        $featuresArray = array();
        foreach ($res as $k => $row) {
            $feat = new FeatureModel($row['id'], $row['name'], $row['point_level'], 
                false, false);
            $feat->description = $row['description'];    
            $feat->descriptionEnglish = $row['description_english'];
            $feat->pictureURL = $row['picture_url'];   
            $featuresArray[$row['right_id']] = $feat;   
        }
        
        return $featuresArray;
    }
    
    /**
     * returns an array of rights
     *
     * @param boolean $showGroupSpecific including the rights spezial for groups
     *
     * @return array array of RightModel
     * @throws DBException on database error
     */
    public static function getAllNonFeatures() {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, description
                FROM public.rights
               WHERE is_group_specific=' . $DB->quote(Database::makeBooleanString(false)) . '
                 AND id NOT IN (SELECT right_id FROM ' . DB_SCHEMA . '.features)
            ORDER BY name';
                
        $res = $DB->execute($q);
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $rights = array();
        foreach ($res as $k => $row) {
            $right = new RightModel($row['id'], $row['name'], $row['description']);
            $rights[] = $right;
        }
        
        return $rights;
    }
    
    public static function getFeatureById($id) {
        $DB = Database::getHandle();
        
        // load all basic rights, that everyone has
        $q = 'SELECT f.* , r.name AS name
                FROM '.DB_SCHEMA.'.features AS f,
                     '.DB_SCHEMA.'.rights AS r
                WHERE f.right_id = r.id and f.id = ' . $DB->quote($id);
        $res = &$DB->execute( $q );
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        foreach ($res as $row) {
            $feat = new FeatureModel($row['id'], $row['name'], $row['point_level'], 
                false, false);
            $feat->description = $row['description'];    
            $feat->descriptionEnglish = $row['description_english'];
            $feat->pictureURL = $row['picture_url'];
            return $feat;
        }
                
    }
    
    public function save(){
    	$keyValue = array ();

        $DB = Database :: getHandle();     
        
        $keyValue['description'] = $DB->quote($this->description);
        $keyValue['description_english'] = $DB->quote($this->descriptionEnglish);
        $keyValue['picture_url'] = $DB->quote($this->pictureURL);
        $keyValue['point_level'] = $DB->quote($this->pointLevel);
        
        $q = null;

        /** is update? we need the a where clausel */
        if ($this->id != null){
            $q = $this->buildSqlStatement('features', $keyValue, false, 'id = ' . $DB->quote($this->id));
        }
        else{
            if($this->rightId == null) {
                throw new ArgumentNullException('rightId');
            }   
            $keyValue['right_id'] = $DB->quote($this->rightId);
            $q = $this->buildSqlStatement('features', $keyValue);
        }        
        //var_dump($q);
        
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }
}

?>
