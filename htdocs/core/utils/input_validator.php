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

// $Id: input_validator.php 6210 2008-07-25 17:29:44Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/input_validator.php $

require_once MODEL_DIR . '/base/study_path_model.php';
require_once CORE_DIR . '/interfaces/form_element.php';

/**
 * Collection of methods for validating user input
 * @author linap
 * @version $Id: input_validator.php 6210 2008-07-25 17:29:44Z trehn $
 */
class InputValidator {
    /**
     * test that the given username is valid due to character restrictions.
     *
     * @param $username string the username to test
     * @return true, if valid; otherwise false
     */
    public static function isValidUsername($username) {
        // check for illegal characters
        if($username == null || preg_match('/[^A-Za-z0-9\-_]/',$username) ) {
            return false;
        }

        // check for length
        if (strlen($username) > 30 or strlen($username) < 3) {
            return false;
        }

        if (!self::isValidUsernameByBlockedFile($username)) {
            return false;
        }

        if (!self::isValidUsernameByUniHelp($username)) {
            return false;
        }

        return true;
    }

    /**
     * check that the given password is a valid one.
     * Criteria are:
     *  - not empty
     *  - no whitespaces
     *  - minimum length 4 chars
     *
     * @param $password string the password to test
     * @return true, if valid; otherwise false
     */
    public static function isValidPassword($password) {
        if (empty($password))
            return false;

        // check for whitespaces
        if (preg_match('/\s/', $password))
            return false;

        if (strlen($password) < 4) {
        	return false;
        }

        // password seems valid
        return true;
    }

    /**
     * check that argument can reasonably be a valid realname
     *
     * @param $name string name
     * @return false, iff $emptyCheck is true and $val is shorter than two characters
     *   or given name is invalid
     */
    public static function isValidName($name, $required = true, $params = null) {
        if (!$required and $name == '') {
            return true;
        }
        return strlen($name)>1 and preg_match('/[^#%@$\/\~§"!\?=\*\+]+/',$name);
    }


    /**
     * check that argument can reasonably be a valid phone number
     *
     * @param string $phone phone number
     * @return false, iff $emptyCheck is true and $val is empty or given phone number is invalid
     */
    public static function isValidPhone($phone, $emptyCheck = false, $params = null) {
        // phone number can have at most 20 digits
        if (strlen($phone) > 20) {
            return false;
        }
        return ($emptyCheck and preg_match('#^\s*\+?[\(\)\d\s]+[/\-]?[\d\s]+$#',$phone)) or
               (!$emptyCheck or $phone!='');
    }

    /**
     * check that argument can reasonably be a valid mail address
     *
     * @param string $mail mail address
     * @return true, if argument seems to be valid mail address or if argument is empty and not required
     */
    public static function isValidMail($mail, $isRequired = false, $params = null) {
        // FIMXE: improve regexp
		//found on http://regexlib.com/REDetails.aspx?regexp_id=1448 by larsiuncle
        //removed by linap because of domain trouble
		/*$regexp = "^((([a-z]|[0-9]|!|#|$|%|&|'|\*|\+|\-|/|=|\?|\^|_|`|\{|\||\}|~)+(\.([a-z]|[0-9]|!|#|$|%|&|'|\*|\+|\-|/|=|\?|\^|_|`|\{|\||\}|~)+)*)@((((([a-z]|[0-9])([a-z]|[0-9]|\-){0,61}([a-z]|[0-9])\.))*([a-z]|[0-9])([a-z]|[0-9]|\-){0,61}([a-z]|[0-9])\.(af|ax|al|dz|as|ad|ao|ai|aq|ag|ar|am|aw|au|at|az|bs|bh|bd|bb|by|be|bz|bj|bm|bt|bo|ba|bw|bv|br|io|bn|bg|bf|bi|kh|cm|ca|cv|ky|cf|td|cl|cn|cx|cc|co|km|cg|cd|ck|cr|ci|hr|cu|cy|cz|de|dk|dj|dm|do|ec|eg|sv|gq|er|ee|et|fk|fo|fj|fi|fr|gf|pf|tf|ga|gm|ge|de|gh|gi|gr|gl|gd|gp|gu|gt| gg|gn|gw|gy|ht|hm|va|hn|hk|hu|is|in|id|ir|iq|ie|im|il|it|jm|jp|je|jo|kz|ke|ki|kp|kr|kw|kg|la|lv|lb|ls|lr|ly|li|lt|lu|mo|mk|mg|mw|my|mv|ml|mt|mh|mq|mr|mu|yt|mx|fm|md|mc|mn|ms|ma|mz|mm|na|nr|np|nl|an|nc|nz|ni|ne|ng|nu|nf|mp|no|om|pk|pw|ps|pa|pg|py|pe|ph|pn|pl|pt|pr|qa|re|ro|ru|rw|sh|kn|lc|pm|vc|ws|sm|st|sa|sn|cs|sc|sl|sg|sk|si|sb|so|za|gs|es|lk|sd|sr|sj|sz|se|ch|sy|tw|tj|tz|th|tl|tg|tk|to|tt|tn|tr|tm|tc|tv|ug|ua|ae|gb|uk|us|um|uy|uz|vu|ve|vn|vg|vi|wf|eh|ye|zm|zw|com|edu|gov|int|mil|net|org|biz|info|name|pro|aero|coop|museum|arpa))|(((([0-9]){1,3}\.){3}([0-9]){1,3}))|(\[((([0-9]){1,3}\.){3}([0-9]){1,3})\])))$^i";*/
        if (!$isRequired and !$mail) {
            return true;
        }

        if (strlen($mail) <= 7) {
            return false;
        }

        // checks proper syntax
        if(!preg_match("/^\S+@([\w\-])+([\w\.\-]+)+$/", $mail)) {
            return false;
        }
       
        // gets domain name
        list($username, $domain)=split('@', $mail);
        // checks for if MX records in the DNS
        $mxhosts = array();
        if (!getmxrr($domain, $mxhosts)) {
            // no mx records, ok to check domain
            if (!@fsockopen($domain,25,$errno,$errstr,30)) {
                return false;
            } else {
                return true;
            }
        } else {
            // mx records found
            foreach ($mxhosts as $host) {
                if (fsockopen($host,25,$errno,$errstr,30)) {
                    return true;
                }
            }
            return false;
        }
    }
    
    const URL_PATTERN = '(?:https?|ftp)://[\w\-\~,/%\.&\?=\#:@;\[\]\+]+[\w\-\~/%&\?=\#:@;\[\]]';

    /**
     * check that argument can reasonably be a valid URL
     *
     * @param string $url
     * @return true, if argument seems to be valid url or if argument is empty and not required
     */
    public static function isValidURL($url, $isRequired = false, $params = null) {
        // TODO: improve regexp
        return (strlen($url) > 9 and preg_match('#^' . self::URL_PATTERN . '#i',$url)) or
               (!$isRequired and !$url);
    }

    /**
     * check that argument is an integer number
     * @param string $str
     * @param boolean
     * @param array $params parameter 'lo' and 'hi'
     */
    public static function isValidInteger($str, $isRequired = false, $params = null) {
    	if (!is_array($params) and $params != null) {
            return false;
        }
        if (!$isRequired and !$str) {
            return true;
        }
        // if we have no bounds we only have to check for digits characters
        if ($params == null) {
            return ctype_digit($str);
        }
        // approx. infty
        $lo = -2147483647;
        $hi = 2147483647;
        if (array_key_exists('lo', $params)) {
            $lo = $params['lo'];
        }
        if (array_key_exists('hi', $params)) {
            $hi = $params['hi'];
        }
        if ($str != null and ctype_digit($str) and $lo <= (int)$str and $hi >= (int)$str) {
        	return true;
        }
        return false;
    }

    /**
     * check that argument has a certain minimal length
     *
     * @param string $str
     * @return true, if argument has valid length or if argument is empty and not required
     */
    public static function isValidByLength($str, $isRequired = false, $params = null) {
        if (!is_array($params)) {
        	return false;
        }
        $lengthLo = 0;
        // approx. infty
        $lengthHi = 1000000;
        if (array_key_exists('lengthLo', $params)) {
        	$lengthLo = $params['lengthLo'];
        }
        if (array_key_exists('lengthHi', $params)) {
            $lengthHi = $params['lengthHi'];
        }
        if ($lengthHi == 1000000 and $lengthLo == 0) {
        	return false;
        }
        return (strlen($str) >= $lengthLo and strlen($str) <= $lengthHi) or
               (!$isRequired and !$str);
    }
    
    public static function isValidCaptcha($str, $isRequired = false, $params = null) {
        if (!is_array($params)) {
            return false;
        }
        $captcha = $params['captcha'];
        if (null == $captcha) {
            return false;
        }
        return ($captcha->getExpectedResult() == trim($str)) or (!$isRequired and !$str);
    }

    /**
     * check that argument is a valid gender
     *
     * @param string $gender gender
     * @return false, iff $emptyCheck is true and $val is empty or given gender is invalid
     */
    public static function isValidGender($gender, $emptyCheck = false, $params = null) {
        return ($gender == 'm' or
                $gender == 'f' or
                $gender == '');
    }
    
    /**
     * check that argument is a valid notification method
     *
     * @param string $notification notification
     */
    public static function isValidNotification($notification, $isRequired = false, $params = null) {
        return ($notification == 'none' or
                $notification == 'email' or
                $notification == 'pm');
    }

    /**
     * check that argument is a valid flirt status
     *
     * @param string $flirt flirt status
     * @return false, iff no valid flirt status is given
     */
    public static function isValidFlirtStatus($flirt, $emptyCheck = false, $params = null) {
        return ($flirt == 'red' or
                $flirt == 'yellow' or
                $flirt == 'green' or
                $flirt == 'none');
    }

    public static function isValidFutureDate($date , $required = true, $params = null) {
    	if (!$required and !$date) {
    		return true;
    	}
        $today = getdate();
        $date = getdate($date);		
        return (mktime(0,0,0,$date["mon"], $date["mday"], $date["year"]) >= mktime(0,0,0,$today["mon"],$today["mday"],$today["year"]));
    }

	public static function isValidPastDate($date , $required = true, $params = null) {
    	if (!$required and !$date) {
    		return true;
    	}
		$today = explode('-', date('Y-m-d'));
        $date = explode('-', $date);
		$check = checkdate(intval($date[1]), intval($date[2]), intval($date[0]));
        return ($check && 
				(mktime(0,0,0,intval($date[1]), intval($date[2]), intval($date[0])) < 
				mktime(0,0,0,intval($today[1]),intval($today[2]),intval($today[0]))) );
	}
	
    public static function isValidPGPKey($pgpKey, $required = false) {
        if (!$required and !$pgpKey) {
            return true;
        }
        return strlen($pgpKey) >= 8 and preg_match('/[0-9a-fA-Fx ]+/', $pgpKey);
    }

    /*
    public static function isValidModelId($id, $required = false, $params = null) {
        if (!$params or !array_key_exists('models', $params)) {
            return false;
        }
        if (!$required and !$id) {
            return true;
        }
        // traverse model array seeking model with given id
        foreach ($params['models'] as $m) {
            if ($m->id == $id) {
                return true;
            }
        }
        return false;
    }
    */

    /**
     * check that argument is a valid study path
     *
     * @param int $studyPath id of study path
     * @return false, iff $emptyCheck is true and $val is empty or given study path is invalid
     */
    public static function isValidStudyPath($studyPath, $emptyCheck = false, $params = null) {
        $isValid = array_key_exists($studyPath, StudyPathModel::getAllStudyPaths());
        return ($emptyCheck and $isValid)
                or
               (!$emptyCheck and ($isValid or $studyPath == 0));
    }

    /**
     * check for argument existence, if specified; no further tests
     *
     * @param $val string
     * @return false, iff $emptyCheck is true and $val is  empty
     */
    public static function isValidAlmostAlways($val, $isRequired = false, $params = null) {
        return (!$isRequired or $val!='');
    }
    
    /**
     * check for argument existence, if specified; no further tests
     *
     * @param $val string
     * @return false, iff $emptyCheck is true and $val is  empty
     */
    public static function isValidAlmostAlways_NoWhitespaceAllowed($val, $isRequired = false, $params = null) {
        return (!$isRequired or trim($val) != '');
    }
    

    /**
     * check that value is valid for given form element
     * @param string  $val
     * @param boolean $emptyCheck iff true, a non empty value is required
     * @param FormElement $element
     * @return boolean
     */
    public static function isValidForFormElement($val, $emptyCheck, $element) {
        switch ($element->getType()) {
        case FORM_ELEMENT_RANGE:
            $range = $element->getTypeParameter();
            return (!$emptyCheck or ($val >= $range[0] and $val <= $range[1]));
        case FORM_ELEMENT_TEXT:
            return self::isValidAlmostAlways($val, $emptyCheck);
    	default:
            throw new NotImplementedException('unknown type of form element');
        }
    }

    /**
     * check username for new user against blockedusers.txt
     * @param string $username to test
     * @return true, if valid; otherwise false
     */
    private static function isValidUsernameByBlockedFile($username) {
        # not implemented for now
        # blockedusers.txt to find at
        # http://instinct.org/~pgl/misc_useful/default_usernames_to_block.txt
        return true;
    }

    /**
     * check username for new user
     * @param string $username to test
     * @return true, if valid; otherwise false
     */
    private static function isValidUsernameByUniHelp($username) {
        if (preg_match('/unihelp/i', $username)) {
            return false;
        }

        # don't find me with grep
        $string = 'w';
        $string .= 'e';
        $string .= 'b';
        $string .= 'u';
        $string .= 'n';
        $string .= 'i';
        if (preg_match("/$string/i", $username)) {
            return false;
        }

        return true;
    }
	

    /**
     * return data from $_REQUEST containing data from forms submited via post/get/cookie
     * @param string $key of the field/data
     * @return value of $key or $default if no value could be retrieved, $default is set to 'false' if no value is given
     */
	public static function getRequestData($key, $default = null){
		return array_key_exists($key, $_REQUEST) ? $_REQUEST[$key] : $default;
	}
    
    // TODO: integrate into validation framework
    public static function requireEncoding($string) {
        return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
    }

}

?>
