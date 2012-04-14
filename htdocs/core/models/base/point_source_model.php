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

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/point_source_model.php $

require_once MODEL_DIR . '/base/base_model.php';

/**
  * @class PointSourceModel
  * @brief This is the class representing a source of UniHelp point generation.
  * 
  * @author linap
  * @version $Id: point_source_model.php 5807 2008-04-12 21:23:22Z trehn $
  * @copyright Copyright &copy; 2006, Unihelp.de
  * 
  * The following properties are available via __get-magic:
  * 
  * <ul>
  * <li><var>id</var>           <b>int</b>          id of country</li>
  * <li><var>name</var>         <b>string</b>       country's name</li>
  * <li><var>pointsSum</var>    <b>int</b>          level points (positive, if they are generated)</li>
  * <li><var>pointsFlow</var>   <b>int</b>          economic points (positive, if they are generated)</li>
  * </ul>
  * 
  * @package Models/Base
  */
class PointSourceModel extends BaseModel {
    protected $name;
    protected $pointsSum;
    protected $pointsFlow;
           
    public function __construct($id = null, $name = null, $pointsSum = null,
                                $pointsFlow = null) {
        $this->id = $id;
        $this->name = $name;
        $this->pointsSum = $pointsSum;
        $this->pointsFlow = $pointsFlow;
    }
    
    /**
     * Collect all point sources
     * 
     * @return array associative array of PointSourceModel; hash keys are the source ids
     */
    public static function getAllPointSources() {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, points_sum_gen, points_flow_gen
                FROM ' . DB_SCHEMA . '.point_sources';
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $sources = array();
        
        foreach($res as $row) {
            $sources[$row['id']] = new PointSourceModel($row['id'], $row['name'],
                    $row['points_sum_gen'], $row['points_flow_gen']);
        }

        return $sources;
    }
    
    /**
     * Return one point source by name
     * @param string $name name of point source
     * @return PointSourceModel
     */
    public static function getPointSourceByName($name) {
        $DB = Database::getHandle();
        
        $q = 'SELECT id, name, points_sum_gen, points_flow_gen
                FROM ' . DB_SCHEMA . '.point_sources
               WHERE name = ' . $DB->Quote($name);
        
        $res = $DB->execute($q);
        
        if (!$res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        $source = new PointSourceModel($res->fields['id'], $res->fields['name'],
                    $res->fields['points_sum_gen'], $res->fields['points_flow_gen']);

        return $source;
    }
    
    public function save(){
        $keyValue = array ();

        $DB = Database :: getHandle();     
        
        $keyValue['name'] = $DB->quote($this->name);
        $keyValue['points_sum_gen'] = $DB->quote($this->pointsSum);
        $keyValue['points_flow_gen'] = $DB->quote($this->pointsFlow);
        
        $q = null;

        /** is update? we need the a where clausel */
        if ($this->id != null){
            $q = $this->buildSqlStatement('point_sources', $keyValue, false, 'id = ' . $DB->quote($this->id));
        }
        else{
            $q = $this->buildSqlStatement('point_sources', $keyValue, true, '');
        }        
        //var_dump($q);
        
        $res = $DB->execute($q);

        if (!$res) {
            throw new DBException(Logging :: getErrorMessage(DB_SQL_QUERY_FAILED, $DB->ErrorMsg()));
        }
    }
    
    public function getPointsSum() {
    	return $this->pointsSum;
    }
    public function setPointsSum($val) {
        $this->pointsSum = $val;
    }
    
    public function getPointsFlow($normalized = false) {
        if (!$normalized) {
            return $this->pointsFlow;
        } else {
            return (int) ($this->pointsFlow / GlobalSettings::getGlobalSetting('POINT_SOURCES_FLOW_MULTIPLICATOR'));
        }
    }
    public function setPointsFlow($val) {
        $this->pointsFlow = $val;
    }
    
    public function getName() {
        return $this->name;
    }
 
}
?>
