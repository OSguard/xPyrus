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

/**
* This interface provides methods needed to extract information about the university (faculties, persons, lectures, tutorials).
* Implementation of this interface is context sensitive which means that for instance 'getLectures' returns all lectures in the given university context. If the lectures in a university are only department-wise stored/provided then this method returns all lectures for this specific department-wise context. The reason for this behavior lies in the uncertainty of the import interfaces which might be connected to the classes implementing this interface. One might be connected via XML, while another one is connected via database connections. Therefore there is no possibility to predict the context where this interface might be implemented. The conclusion is that for each city AND university there must be an implementation of this interface!
* @package Core
* @version $Id: uni_data_handler.php 5807 2008-04-12 21:23:22Z trehn $
*/

interface UniData{

    /**
    * Return all faculties for a given university in a given context.
    * @return array Associative array containing the data. Array should look like this: array ( 'university' => $university, 'faculties' => array( 'faculty1', 'faculty2', ..., 'facultyN'))
    */
	public function getFaculties( );

    /**
    * Return all details for a given person. This person can be any member of the university (teacher, employee, assistant...)
    *
    * @param string Defines the department (faculty, work group etc.) of the university from where to get the data from. Optional parameter.
    * @param string Unique Name/ID of the person.
    * @return array Associative array containing the data. Array could(!) look like this: array ( 'university' => $university, 'person' => $person, 'family_name' => 'Miller', 'givenName' => 'Peter', 'position' => 'Professor', 'faculty' => 'Computer Science', 'department' => 'Simulation and Graphics', ...)
    */
	public function getPersonDetails( $personID );

    /**
    * Return all persons for a given university and department.
    * @param string Defines the department (faculty, work group etc.) of the university from where to get the data from. Optional parameter.
    * @return array Associative array containing the data. Array could(!) look like this: array ( 'university' => $university, 'department' => $department, 'persons' => array ( [0] => array ( 'university' => $university, 'person' => $person, 'family_name' => 'Miller', 'givenName' => 'Peter', 'position' => 'Professor', 'faculty' => 'Computer Science', 'department' => 'Simulation and Graphics', ...) ), [1] => array (...), ...)
    */
	public function getPersons( $department = "" );


    /**
    * Return all lectures for given university and department.
	*
    * @param string Defines the department (faculty, work group etc.) of the university from where to get the data from. Optional parameter.
    * @return array Associative array containing the data. Array could(!) look like this: array ( 'university' => $university, 'department' => $department, 'lectures' => array ( [0] => 'Introduction to Simulation', [1] => 'Service Oriented Architectures', [2] => ...))
    */
	public function getLectures( $department = "" );

    /**
    * Return details for a given lecture.
    *
    * @param string Defines the department (faculty, work group etc.) of the university from where to get the data from. Optional parameter.
    * @param string Defines the specific lecture.
    * @return array Associative array containing the data. Array could(!) look like this: array ( 'university' => $university, 'department' => $department, 'lecture' => array ( 'name' => 'Introduction to Simulation', 'faculty' => 'Computer Science', 'teacher' => 'Prof. Dr. Graham Horton', 'time' => 'Monday, 13.15h - 14.45h', 'Credits' => 4, ...) )
    */
    public function getLectureDetails( $department = "", $lectureID );

}

?>
