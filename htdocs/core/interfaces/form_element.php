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

# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/interfaces/form_element.php $

/**
 * @class FormElement
 * @brief parametrization of user defined form elements
 * 
 * @author linap
 * @version $Id: form_element.php 5807 2008-04-12 21:23:22Z trehn $
 * @copyright Copyright &copy; 2006, Unihelp.de
 * 
 * Interface that can be implemented for everything
 * that can occur as different kind of form elements;
 * types are e.g. 'range', 'text'
 *
 * @package Interfaces
 */
interface FormElement {
    /**
     * @return string type of form element
     */
    public function getType();
    
    /**
     * @param int $index optional parameter index
     * @return array|mixed single or all type parameters of form element
     */
    public function getTypeParameter($index = null);
}

// some constants
define('FORM_ELEMENT_TEXT',     'text');
define('FORM_ELEMENT_RANGE',    'range');

?>
