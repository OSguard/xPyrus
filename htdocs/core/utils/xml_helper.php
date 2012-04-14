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
 * Class providing essential methods for XML handling. Used by logging classes etc. All methods are static thus no constructor is necessary.
 *  copyright            : (C) 2005 unihelp (LD)
 *  email                :  info@unihelp.org
 *  @version $Id: xml_helper.php 5807 2008-04-12 21:23:22Z trehn $
 *  @package Core
 *
 *
 */

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

require_once "./conf/config.php";
require_once CORE_DIR . "/utils/class_xml_check.php";

class XMLHelper{

	/**
	* Check if given XML string is well-formed. Uses class_xml_check.php
	* @param string XML string
	* @return boolean TRUE if XML is well-formed, FALSE otherwise.
	*/
	public static function checkXMLString($XML){
		$checker = new XML_check();
		return $checker->check_string($XML);
	}

	/**
	* Read XML file into string.
	* @param string XML file name and path
	* @return string representing the XML contained in $file or empty string if nothing was loaded/read.
	*/
	public static function readXMLFile($file){
		$doc = simplexml_load_file($file);
		if (!$doc){
			return FALSE;
		}
		return $doc->asXML();
	}

	/**
	* Convert XML to object DOMDocument.
	* @param string XML string.
	* @return object DOMDocument representing the XML contained in $XML or FALSE if conversion failed.
	*/
	public static function XML2DOMDocument($XML){
		$domDoc = new DOMDocument();
		if (!$domDoc->loadXML( $XML )){
			$domDoc = FALSE;
		}
		return $domDoc;
	}

	/**
	* Convert XML string to SimpleXML object.
	* @param string XML string.
	* @return object SimpleXML representing the XML contained in $XML or FALSE if conversion failed.
	*/
	public static function XML2SimpleXML($XML){
		if (XMLHelper::checkXMLString($XML)){
			return simplexml_load_string($XML);
		}
		else return FALSE;
	}


	/**
	* Read any XML string or file.
	* @param string XML string or file.
	* @return string representing the valid XML code if any or FALSE if reading failed.
	* @see XMLHelper::readXMLFile, XMLHelper::getXMLByXPath
	*/
	public static function readXML($XML){
		if (XMLHelper::checkXMLString($XML)){
			return $XML;//$XML was a valid xml string
		}
		else{
			$res = XMLHelper::readXMLFile($XML);
			return $res;
		}
	}

	/**
	* Checks whether a given node exists in $XML.
	* @param string XML string.
	* @param string XPath query specifying the search path to node.
	* @return TRUE if node is found, FALSE otherwise.
	*/
	public static function nodeExists($XML, $query){
		$doc = XMLHelper::XML2SimpleXML($XML);
		if (!$doc){
			return FALSE;
		}
		$xp = $doc->xpath($query);
		return (count($xp)>0);
	}


	/**
	* Read any node from XML string/file using an XPath query.
	* Let's assume you search for the node 'exception' with attribute 'id' and attribute value '1'. (query = //exception[@id="1"])
	* You'll get all occurences of the node specified by XPath expression/query.
	*		<exception id="1" type="sample type">
	*			<file>sample file</file>
	*			<line>n</line>
	*			<text>blabla</text>
	*		</exception>
	* @param string XML string.
	* @param string $XPathQuery - any XPath query valid for the respective XML document. Case sensitive!
	* @return all nodes matching the $XPathQuery as an array with a tree-like structure similar to the original XML structure. The attributes of a node are appended in the special array "@".
	* Example: given the XML string above you'd get the following result: array( [exception] => array( [0] => array( [file] => "sample file", [line] => n, [text] => "blabla", [@] => array( [id] => 1, [type] => "sample type")))
	* FALSE if no nodes were found.
	*/

	public static function getXMLByXPath($XML, $XPathQuery){
		//use simpleXML internally
		$doc = XMLHelper::XML2SimpleXML($XML);
		if (!$doc){
			return FALSE;//conversion failed -> return FALSE
		}
		//$result = array();	//result is an array
		$list = $doc->xpath(trim($XPathQuery));
		if (is_array($list) || ($list instanceof SimpleXMLElement)){
			return XMLHelper::simpleXML2ISOArray($list);
		}
		else{
			return $list;
		}
/*		if (!is_null($list) and count($list) > 0){//if query delivered one or more nodes...
            foreach ($list as $node){//traverse all nodes in $list
            		$tree = XMLHelper::getCompleteTree($node);//get children and complete tree of node
            		array_push($result, $tree);//append to array
	            }
		}
		return $result;//return array*/
	}

	/**
    * Convert SimpleXMLElement object to ISO array
    * Copyright Daniel FAIVRE 2005 - www.geomaticien.com
    * Copyleft GPL license
    *
    * Modifications by Tobias Wantzen
    * Returned array will contain single-byte ISO-8859-1 using utf8_decode()
    * @param SimpleXMLElement XML as object of type 'SimpleXMLElement'
    * @return array Array representing XML code with parent/children relations. If a node contains only CDATA it will get a seperate array node called 'CDATA'.
    */
    public static function simpleXML2ISOArray($xml){
       if (get_class($xml) == 'SimpleXMLElement') {
		   $attributes = $xml->attributes();
           foreach($attributes as $k => $v) {
               if ($v) $a[$k] = trim((string) $v);	//if any then append attributes to array
           }
             $x = $xml;
             $xml = get_object_vars($xml);		//get all childs of current node
       }
       if (is_array($xml)) {					//if children existing
           if (count($xml) == 0 and (string)$x){
           		$r['CDATA'] = trim((string)$x);	// for CDATA
           }
           foreach($xml as $key=>$value) {		//for each child of node $xml
           		$r[$key] = XMLHelper::simpleXML2ISOArray($value);	//recursively parse tree
                // original line instead of the following if statement:
                //$r[$key] = simplexml2ISOarray($value);
                if ( !is_array( $r[$key] ) ) $r[$key] = utf8_decode( $r[$key] );	//if child is plain text, convert to utf8
           }
           if (isset($a)) $r['@'] = $a;    		// add array for attributes
           return $r;							//return result array
       }
       return trim((string) $xml);				//return plain text contained in $xml
     }

	/**
	* Read any node from XML file/string.
	* Let's assume you search for the node 'exception' with attribute 'id' and attribute value '1'.
	* You'll get all occurences of the node specified by $entityName = "exception", $attributeName = "id", $attributeValue = "1".
	*		<exception id="1" [some more attributes]>
	*			<file>...</file>
	*			<line>...</line>
	*			[some more tags]
	*		</exception>
	*
	* If $entityName or ($attributeName and $attributeValue) is empty, "/" (=root) is assumed!
	* @param string XML string.
	* @param string $entityName represents the tag (node) where to get the content from.
	* @param string $attributeName is the attribute name of the node.
	* @param mixed $attributeValue is the value of the attribute of the node. Case sensitive!
	* @return array all nodes matching the "query" as an array with following structure: array( nodeName => string, nodeAttributes => array( attribName => string, attribValue => mixed), nodeValue => string, nodeChildren => array (recursively the same as before since a child is also a node...)). Empty array if no nodes were found.
	*/
	public static function getXMLByAttribute($XMLString, $entityName = "/", $attributeName, $attributeValue){
		$entityName = trim($entityName);
		$attributeName = trim($attributeName);
		$attributeValue = trim($attributeValue);

		$query = "/";

		//if no entity given, assume root ("/")
		if (strcasecmp($entityName, "/")==0){
			$query .= "*";
		}
		else{
			//if attribute and attribute value given
            if (strlen($entityName)!=0 and strlen($attributeName)!=0 and strlen($attributeValue)!=0){
                $query .= "/". $entityName. "[@".$attributeName. "=\"" .$attributeValue. "\"]";
            }
            //if entity given, but no attribute and/or attribute value
            else{
            	$query .= "//" . $entityName;
            }
		}
		return XMLHelper::getXMLByXPath($XMLString, $query);
	}
}

/*	private static function getCompleteTree($root){
		if (is_null($root) || !($root instanceof SimpleXMLElement) ){//if NULL then return NULL
			return NULL;
		}

		$children = array();
		$attribs = array();

		if ($root->attributes()){//if there are attributes for the current node
        	foreach ($root->attributes() as $attname => $attValue){
        		array_push($attribs, array( "attribName" => $attname, "attribValue" => $attValue[0]));//add attributes to array $attribs
        	}
        }
print_r($attribs);die;
        if ($root->children()){//if there are children at the current node
        	foreach ($root->children as $child){
				array_push($children, XMLHelper::getCompleteTree($child));//recursively append children to array $children
        	}
        }
        return $result = array( "nodeName" => $root->nodeName, "nodeAttributes" => $attribs,
            					"nodeValue" => $root->nodeValue, "nodeChildren" => $children );//return complete tree after coming back from recursion
	}
*/

?>
