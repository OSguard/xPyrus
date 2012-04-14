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

//$Id: uni_data_handler_magdeburg.php 5807 2008-04-12 21:23:22Z trehn $
/**
* Class providing methods for handling university data for the Otto-von-Guericke University of Magdeburg, Germany.
*
* This class implements the interface UniData for the above mentioned university. At Magdeburg the import of data is done via XML files from the univis-system (http//:univis.uni-magdeburg.de).
* This class works ONLY with ONE XML file/string at a time!!! All method calls will be executed parsing one XML file/string. See methods for details.
*
*
*   This program is free software; you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation; either version 2 of the License, or
*   (at your option) any later version.
*
* @package Core
* @author $Id: uni_data_handler_magdeburg.php 5807 2008-04-12 21:23:22Z trehn $
* @copyright 2005, unihelp e.V.
* @version $Id: uni_data_handler_magdeburg.php 5807 2008-04-12 21:23:22Z trehn $
* @see UniData, admin/univis.dtd
*/

require_once './conf/config.php';
require_once CORE_DIR . "/utils/xml_helper.php";

class UniDataReader_Magdeburg {

	private $domDoc = NULL;
	private $XML = "";
	private $XMLset = FALSE;
	public $PersonsXML = NULL;
	private $LecturesXML = NULL;
	private $FacultiesXML = NULL;

	private $XML_SET_NONE = 	0;
	private $XML_SET_PERSONS = 	1;
	private $XML_SET_FACULTIES =2;
	private $XML_SET_LECTURES = 4;
	//private $XML_SET_ALL = ($XML_SET_LECTURES & $XML_SET_PERSONS & $XML_SET_FACULTIES);

	private $UNIVIS_XML_HEADER = "<?xml version=\"1.0\"?>\n<!DOCTYPE UnivIS SYSTEM \"http://univis.uni-magdeburg.de/univis.dtd\">\n<UnivIS version=\"1.3\">\n";
	private $UNIVIS_XML_FOOTER = "</UnivIS>";

	/**
	* Constructor.
	* @param string File name of XML file to read.
	* @throws UnihelpException in case the file cannot be read or accessed or the XML
	*/
	public function __construct( $XML ){
		$this->setXML($XML);
	}

	private function isXMLSet( $type = "" ){
		$type = (int)$type;
		if (!$type){
			return $this->XMLSet;
		}
		else{
			if ($type & $XML_SET_LECTURES){
				return ($LecturesXML != NULL ? TRUE : FALSE);
			}
			if ($type & $XML_SET_PERSONS){
				return ($PersonsXML != NULL ? TRUE : FALSE);
			}
			if ($type & $XML_SET_FACULTIES){
				return ($FacultiesXML != NULL ? TRUE : FALSE);
			}
		}
		return FALSE;
	}

	/**
	* Set XML file/string for use.
	* @param string File name/XML string of XML file to read.
	*/
	public function setXML($XML){
		$this->XML = XMLHelper::readXML($XML);
        if ($this->XML){
        	$this->domDoc = XMLHelper::XML2DOMDocument($this->XML);
        	$this->XMLset = TRUE;

        	//try to extract persons, lectures and faculties to seperate XML setc in order to accelerate future access
        	$tmp = XMLHelper::XML2SimpleXML($this->XML);
        	if ($tmp){
        		$this->PersonsXML = $tmp->xpath("//Person");
        		if ($this->PersonsXML){
/*        		   $cnt = count($this->PersonsXML);
        		   $tmp2 = "";
				   for ($i=0;$i<$cnt;$i++){
				   		$tmp2 .= $this->PersonsXML[$i]->asXML();
				   }
				   $this->PersonsXML = XMLHelper::XML2SimpleXML( $UNIVIS_XML_HEADER . $tmp2 . $UNIVIS_XML_FOOTER );*/
        		}
        		else{
        			$this->PersonsXML = NULL;
        		}
        		$this->LecturesXML = $tmp->xpath("//Lecture");
        		if ($this->LecturesXML){
/*        		   $cnt = count($this->LecturesXML);
        		   $tmp2 = "";
				   for ($i=0;$i<$cnt;$i++){
				   		$tmp2 .= $this->LecturesXML[$i]->asXML();
				   }
				   $this->LecturesXML = $UNIVIS_XML_HEADER . $tmp2 . $UNIVIS_XML_FOOTER;*/
        		}
        		else{
        			$this->LecturesXML = NULL;
        		}

        		$this->FacultiesXML = $tmp->xpath("//Org");
        		if ($this->FacultiesXML){
/*        		   $cnt = count($this->FacultiesXML);
        		   $tmp2 = "";
				   for ($i=0;$i<$cnt;$i++){
				   		$tmp2 .= $this->FacultiesXML[$i]->asXML();
				   }
				   $this->FacultiesXML = $UNIVIS_XML_HEADER . $tmp2 . $UNIVIS_XML_FOOTER;*/
        		}
        		else{
        			$this->FacultiesXML = NULL;
        		}
        	}
        }
        else{
        	$this->XMLset = FALSE;
        }
	}

    /**
    * Return all faculties from a university as array.
    *
    * @return array Associative array containing the data. Array should look like this: array ( 'university' => $university, 'faculties' => array( 'faculty1', 'faculty2', ..., 'facultyN'))
    */
	public function getFaculties( ){
                $cnt = count($this->FacultiesXML);

                return $cnt;
	}

    /**
    * Return all details for a given person. This person can be any member of the university (teacher, employee, assistant...)
    *
    * @param string Unique Name/ID of the person.
    * @param string Defines the department (faculty, work group etc.) of the university from where to get the data from. Optional parameter.
    * @return array Associative array containing the data. Array looks like this: array ( 'firstname', 'gender', 'lastname' , 'lehrperson' , 'lehraufgaben' , 'lehrpersonentyp' , 'locations' => array( [0] => array('email' , 'fax' , 'institution' , 'mobile' , 'ort' , 'office' , 'street' , 'tel' , 'url'), [1] => ...) ,  'officehours' => array('comment' , 'endtime' , 'repeat' , 'office' , 'starttime') , 'organisationsname' , 'schwerpunkte' , 'title' , 'workdesc'). Empty array if person could not be found in current context.
    */
	public function getPersonDetails( $personID ){
		$result = array();
		$query = "///Person[@key=\"".$personID."\"]";
		$node = XMLHelper::getXMLByXPath( $this->XML, $query);
		if ($node){
            //map query result to array structure
            $result['firstname'] = (string)$node[0]['firstname'];
            $result['gender'] = (string)$node[0]['gender'];
            $result['lastname'] = (string)$node[0]['lastname'];
            $result['lehrperson'] = (string)$node[0]['lehr'];
            $result['lehraufgaben'] = (string)$node[0]['lehraufg'];
            $result['lehrpersonentyp'] = (string)$node[0]['lehrtyp'];

			$result['locations'] = array();
			$cnt = count((array)$node[0]['locations']['location']);
			if (is_array($node[0]['locations']['location'][0])){
                for ($i=0;$i<$cnt;$i++){
                    $email = (string)$node[0]['locations']['location'][$i]['email'];
                    $fax = (string)$node[0]['locations']['location'][$i]['fax'];
                    $institution = (string)$node[0]['locations']['location'][$i]['institution'];
                    $mobile = (string)$node[0]['locations']['location'][$i]['mobile'];
                    $ort = (string)$node[0]['locations']['location'][$i]['ort'];
                    $office = (string)$node[0]['locations']['location'][$i]['office'];
                    $street = (string)$node[0]['locations']['location'][$i]['street'];
                    $tel = (string)$node[0]['locations']['location'][$i]['tel'];
                    $url = (string)$node[0]['locations']['location'][$i]['url'];

                    $tmp2 = array( 'email' => $email, 'fax' => $fax, 'institution' => $institution, 'mobile' => $mobile, 'ort' => $ort, 'office' => $office,
                        'street' => $street, 'tel' => $tel, 'url' => $url);
                    $result['locations'][$i] = $tmp2;
                }
            }
            else{
                    $email = (string)$node[0]['locations']['location']['email'];
                    $fax = (string)$node[0]['locations']['location']['fax'];
                    $institution = (string)$node[0]['locations']['location']['institution'];
                    $mobile = (string)$node[0]['locations']['location']['mobile'];
                    $ort = (string)$node[0]['locations']['location']['ort'];
                    $office = (string)$node[0]['locations']['location']['office'];
                    $street = (string)$node[0]['locations']['location']['street'];
                    $tel = (string)$node[0]['locations']['location']['tel'];
                    $url = (string)$node[0]['locations']['location']['url'];

                    $tmp2 = array( 'email' => $email, 'fax' => $fax, 'institution' => $institution, 'mobile' => $mobile, 'ort' => $ort, 'office' => $office,
                        'street' => $street, 'tel' => $tel, 'url' => $url);
                    $result['locations'][0] = $tmp2;
            }

			$result['officehours'] = array();
			$cnt = count((array)$node[0]['officehours']['officehour']);
			if (is_array($node[0]['officehours']['officehour'][0])){
                for ($i=0;$i<$cnt;$i++){
                    $comment = (string)$node[0]['officehours']['officehour'][$i]['comment'];
                    $endtime = (string)$node[0]['officehours']['officehour'][$i]['endtime'];
                    $repeat = (string)$node[0]['officehours']['officehour'][$i]['repeat'];
                    $office = (string)$node[0]['officehours']['officehour'][$i]['office'];
                    $starttime = (string)$node[0]['officehours']['officehour'][$i]['starttime'];

                    $tmp2 = array( 'comment' => $comment, 'endtime' => $endtime, 'repeat' => $repeat, 'office' => $office, 'starttime' => $starttime);
                    $result['officehours'][$i] = $tmp2;
                }
            }
            else{
                    $comment = (string)$node[0]['officehours']['officehour']['comment'];
                    $endtime = (string)$node[0]['officehours']['officehour'][$i]['endtime'];
                    $repeat = (string)$node[0]['officehours']['officehour'][$i]['repeat'];
                    $office = (string)$node[0]['officehours']['officehour'][$i]['office'];
                    $starttime = (string)$node[0]['officehours']['officehour'][$i]['starttime'];
                    $tmp2 = array( 'comment' => $comment, 'endtime' => $endtime, 'repeat' => $repeat, 'office' => $office, 'starttime' => $starttime);
                    $result['officehours'][0] = $tmp2;
            }

            $result['organisationsname'] = (string)$node[0]['orgname'];
            $result['schwerpunkte'] = (string)$node[0]['schwerpkt'];
            $result['title'] = (string)$node[0]['title'];
            $result['workdesc'] = (string)$node[0]['work'];
		}
		return $result;
	}

    /**
    * Return all persons for a given university and department.
    * @param string Ignored parameter. Only for reasons of compatibility with interface.
    * @return array Associative array containing the data. Array looks like this: array ( 'persons' => array ( [0] => array [see method getPersonDetails for structure of the array], [1] => array (...), ...)
    */
	public function getPersons( $department = "" ){
		$result = array();
		$cnt = count($this->PersonsXML);
        for($i=0;$i<$cnt;$i++){
        	$result[$i] = $this->getPersonDetails( $this->getAttributeValue($this->PersonsXML[$i], "key") );
        }
		return $result;
	}

	private function getAttributeValue( $simplexml_object, $attribute){
		foreach ( $simplexml_object->attributes() as $tmp => $t){
			if ($tmp == $attribute){
				return $t;
			}
		}
		return FALSE;
	}

    public static function getCourseKey($course) {
        return md5($course->getName());
    }

    public function getLectures() {
        $lectures = array();
        foreach ($this->LecturesXML as $lec) {
            $lec->type = strtolower($lec->type);
            if (substr($lec->type,0,1) == 'v' or $lec->type == 'p' or $lec->type == 'hs' or $lec->type == 'ps' or $lec->type == 's') {
                $lecName = (string)$lec->name;
    
                if (strlen($lecName) >= 150 and strpos($lecName,'/') > strlen($lecName) / 3) {
                    $lecName = substr($lecName, 0, strpos($lecName,'/'));
                }
                if (strlen($lecName) >= 150) {
                    $lecName = substr($lecName, 0, 146) . '...';
                }
                $lecNameShort = $lec->short;
                if (strlen($lecNameShort) >= 50) {
                    $lecNameShort = substr($lecNameShort, 0, 46) . '...';
                }
                
                $newCourse = new CourseModel(0, 
                                        $lecName,
                                        $lecNameShort,
                                        $lec->ects_name);
                if (array_key_exists(self::getCourseKey($newCourse), $lectures)) {
                    continue;
                }
                
                $lectures[self::getCourseKey($newCourse)] = $newCourse;
                $l = $lectures[self::getCourseKey($newCourse)];
                if ($lec->studs->stud) {
                    foreach ($lec->studs->stud as $sp) {
                        $_richt = (string)$sp->richt;
                        $pos = strpos($_richt,'-');
                        if ($pos !== false) {
                            $estimatedSP = substr($_richt,0, $pos);
                        } else {
                            $estimatedSP = $_richt;
                        }
                        $_sem = (string)$sp->sem;
                        $sem = array();
                        $_l = strlen($_sem)-1;
                        $sMin = sscanf($_sem[0], '%X');
                        $sMax = sscanf($_sem[$_l], '%X');
                        //var_dump((string)$estimatedSP, $_richt);
                        $spObj = StudyPathModel::getStudyPathByNameShort($estimatedSP);
                        //var_dump($spObj, $sMin, $sMax);
                        /*if ($spObj == null) {
                            var_dump((string)$estimatedSP, $_richt);
                        }*/
                        //var_dump($spObj == null);
                        if ($spObj != null) {
                            $l->addStudyPath($spObj, $sMin[0], $sMax[0]);
                        }
                    }
                }
            }
        }
        return $lectures;
	}

    /**
    * Return details for a given lecture.
    *
    * @param string Defines the department (faculty, work group etc.) of the university from where to get the data from. Optional parameter.
    * @param string Defines the specific lecture.
    * @return array Associative array containing the data. Array could(!) look like this: array ( 'university' => $university, 'department' => $department, 'lecture' => array ( 'name' => 'Introduction to Simulation', 'faculty' => 'Computer Science', 'teacher' => 'Prof. Dr. Graham Horton', 'time' => 'Monday, 13.15h - 14.45h', 'Credits' => 4, ...) )
    */
    /*<!ELEMENT Lecture ( courseof? , adopted? , allocation? , benschein? , bonus? , classification? , comment? , courses? , dozs? , ects? , ects_cred? , ects_literature? , ects_name? , ects_organizational? , ects_summary? , enddate? , eturnout? , evaluation? , id? , keywords? , literature? , name , malus? , module? , number? , order? , ordernr? , organizational? , orgname? , orgroot? , orgunits? , origin? , parent-lv? , prelim_dates? , regsystem? , schein? , schemes? , short? , startdate? , studs? , summary? , sws? , turnout? , terms? , time_description? , type , url_description? )>
*/
    public function getLectureDetails( $department = "", $lectureID ){
		$result = array();
		$query = "///Lecture[@key=\"".$lectureID."\"]";
		$node = XMLHelper::getXMLByXPath( $this->XML, $query);
		if ($node){
            //map query result to array structure
            $result['besondereBelegungskennzeichen'] = (string)$node[0]['allocation'];
            $result['benoteterSchein'] = (string)$node[0]['benschein'];
            $result['bonusPunkte'] = (string)$node[0]['bonus'];
            //$result['Einordnung'] = $this->resolveUnivisRef($node[0]['classification']);
            $result['Kommentar'] = (string)$node[0]['comment'];

			$cnt = count((array)$node->courses);
			$result['courses'] = array();
			for ($i=0;$i<$cnt;$i++){
            	$course = (string)$node->locations->location[$i]->url;

                $tmp2 = array( 'email' => $email, 'fax' => $fax, 'institution' => $institution, 'mobile' => $mobile, 'ort' => $ort, 'office' => $office,
                	'street' => $street, 'tel' => $tel, 'url' => $url);
                $result['locations'][$i] = $tmp2;
			}

			$cnt = count((array)$node->officehours);
			$result['officehours'] = array();
			for ($i=0;$i<$cnt;$i++){
				$comment = (string)$node->officehours->officehour[$i]->comment;
            	$endtime = (string)$node->officehours->officehour[$i]->endtime;
            	$repeat = (string)$node->officehours->officehour[$i]->repeat;
            	$office = (string)$node->officehours->officehour[$i]->office;
            	$starttime = (string)$node->officehours->officehour[$i]->starttime;

            	$tmp2 = array( 'comment' => $comment, 'endtime' => $endtime, 'repeat' => $repeat, 'office' => $office, 'starttime' => $starttime);
                $result['officehours'][$i] = $tmp2;
            }

            $result['organisationsname'] = (string)$node->orgname;
            $result['schwerpunkte'] = (string)$node->schwerpkt;
            $result['title'] = (string)$node->title;
            $result['workdesc'] = (string)$node->work;
		}
		return $result;
    }


}

?>
