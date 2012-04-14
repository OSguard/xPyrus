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
// $Id: class_xml_check.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/class_xml_check.php $
// Source: http://kent.dl.sourceforge.net/sourceforge/phpxmlclasses/class_xml_check_1.0.tgz
// ##################################################################################
// Title                     : Class XML_check
// Version                   : 1.0
// Author                    : Luis Argerich (lrargerich@yahoo.com)
// Last modification date    : 07-10-2002
// Description               : A class to check if documents are well formed
//                             XML reporting error msg,line and col if not or
//                             statistics about the document if it is well formed.
// ##################################################################################
// History:
// 07-10-2002                : First version of this class.
// ##################################################################################
// To-Dos:
//
// ##################################################################################
// How to use it:
// Read the documentation in class_xml_check.html
// ##################################################################################
class XML_check {
  private $error_code;
  private $error_line;
  private $error_col;
  private $error_msg;
  private $size;
  private $elements;
  private $attributes;
  private $texts;
  private $text_size;

  function get_error_code() {
    return $this->error_code;
  }

  function get_error_line() {
    return $this->error_line;
  }

  function get_error_column() {
    return $this->error_col;
  }

  function get_error_msg() {
    return $this->error_msg;
  }

  function get_full_error() {
    return "Error: ".$this->error_msg." at line:".$this->error_line ." column:".$this->error_col;
  }

  function get_xml_size() {
    return $this->size;
  }

  function get_xml_elements() {
    return $this->elements;
  }

  function get_xml_attributes() {
    return $this->attributes;
  }

  function get_xml_text_sections() {
    return $this->texts;
  }

  function get_xml_text_size() {
    return $this->text_size;
  }

  function check_url($url) {
    $this->_init();
    $this->parser = xml_parser_create_ns("",'^');
    xml_set_object($this->parser,$this);
    xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
    xml_set_element_handler($this->parser, "_startElement", "_endElement");
    xml_set_character_data_handler($this->parser,"_data");
    if (!($fp = fopen($url, "r"))) {
      $this->error="Cannot open $rddl";
      return false;
    }
    while ($data = fread($fp, 4096)) {
      $this->size+=strlen($data);
      if (!xml_parse($this->parser, $data, feof($fp))) {
        $this->error_code = xml_get_error_code($this->parser);
        $this->error_line = xml_get_current_line_number($this->parser);
        $this->error_col = xml_get_current_column_number($this->parser);
        $this->error_msg = xml_error_string($this->error_code);
        return false;
      }
    }
    xml_parser_free($this->parser);
    return true;
  }

  function _init() {
    $this->error_code = '';
    $this->error_line = '';
    $this->error_col = '';
    $this->error_msg = '';
    $this->size = 0;
    $this->elements = 0;
    $this->attributes = 0;
    $this->texts = 0;
    $this->text_size = 0;
  }

  function _startElement($parser,$name,$attrs) {
    $this->elements++;
    $this->attributes+=count($attrs);
  }

  function _endElement($parser,$name) {

  }

  function _data($parser,$data) {
    $this->texts++;
    $this->text_size+=strlen($data);
  }

  function check_string($xml) {
    $this->_init();
    $this->parser = xml_parser_create_ns("",'^');
    xml_set_object($this->parser,$this);
    xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
    xml_set_element_handler($this->parser, "_startElement", "_endElement");
    xml_set_character_data_handler($this->parser,"_data");
    $this->size+=strlen($xml);
    if (!xml_parse($this->parser, $xml, true)) {
      $this->error_code = xml_get_error_code($this->parser);
      $this->error_line = xml_get_current_line_number($this->parser);
      $this->error_col = xml_get_current_column_number($this->parser);
      $this->error_msg = xml_error_string($this->error_code);
      return false;
    }
    xml_parser_free($this->parser);
    return true;
  }
}
?>
