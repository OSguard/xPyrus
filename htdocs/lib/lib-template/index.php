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

# $Id: index.php 6210 2008-07-25 17:29:44Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/lib/lib-template/index.php $
#
# index file for loading Smarty template engine

# define template lib dir
define('TEMPLATE_DIR', LIB_DIR . "/lib-template");
if (!is_dir(TEMPLATE_DIR)) {
  die("Please install template layer<br />\n");
}

# define cache dir
#define('TEMPLATE_CACHE_DIR',  "/tmp/cache");
if (defined('LOCAL_CACHE_DIR')) {
  define('TEMPLATE_CACHE_DIR', LOCAL_CACHE_DIR . "/cache");
} else {
  define('TEMPLATE_CACHE_DIR', TEMPLATE_DIR . "/cache");
}
if (!is_dir(TEMPLATE_CACHE_DIR)) {
  if (!mkdir(TEMPLATE_CACHE_DIR, 0700, true)) {
    die("could not create cache base directory: " . TEMPLATE_CACHE_DIR . "<br />\n");
  }
}

# this basically points to the smarty directory
define('SMARTY_VERSION', "Smarty-2.6.19");
if (!defined('SMARTY_DIR')) {
  define('SMARTY_DIR', TEMPLATE_DIR . "/" . SMARTY_VERSION . "/libs/");
}
# include Smarty
if (is_file(SMARTY_DIR . "Smarty.class.php")) {
  require_once(SMARTY_DIR . "Smarty.class.php");
} else {
  die("Please install template layer<br />\n");
}
include "lang.php";
$_SMARTY = new smartyML;
            
$_SMARTY->assign('THIS_YEAR', date('Y'));
$_SMARTY->assign('THIS_MONTH', date('m'));
$_SMARTY->assign('THIS_DAY', date('d'));

# set cache directories
$_SMARTY->compile_dir = TEMPLATE_CACHE_DIR . "/templates_c";
if (!is_dir($_SMARTY->compile_dir)) {
  if (!mkdir($_SMARTY->compile_dir)) {
    die("could not create template cache directory: " . $_SMARTY->compile_dir . "<br />\n");
  }
}
$_SMARTY->config_dir = TEMPLATE_CACHE_DIR . "/configs";
if (!is_dir($_SMARTY->config_dir)) {
  if (!mkdir($_SMARTY->config_dir)) {
    die("could not create config cache directory: " . $_SMARTY->config_dir . "<br />\n");
  }
}
$_SMARTY->cache_dir = TEMPLATE_CACHE_DIR . "/cache";
if (!is_dir($_SMARTY->cache_dir)) {
  if (!mkdir($_SMARTY->cache_dir)) {
    die("could not create website cache directory: " . $_SMARTY->cache_dir . "<br />\n");
  }
}
$_SMARTY->use_sub_dirs = true;

# set or reset debugging
if (DEVEL === TRUE and array_key_exists('debug', $_REQUEST)) {
    $_SMARTY->debugging = true;
}

# enable caching
if (DEVEL === TRUE) {
  # not in developing
  $_SMARTY->caching = false;  # true, false, -1, 0, 1, 2
  $_SMARTY->force_compile = true;  # true, false, -1, 0, 1, 2
} else {
  # but in production environment
  $_SMARTY->caching = 2;  # true, false, -1, 0, 1, 2
  $_SMARTY->compile_check = false;
}


// modify output for bad browers
$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$stupidBrowser = strpos($userAgent, 'MSIE') !== false &&
            strpos($userAgent, 'MSIE 7') === false;



# replace_help_tags()
#
# function for replacing the help tags
#
# parameter:
#  - tag string to look for
#  - callback function for matches
#  - template source code
#  - reference to smarty handler
# return:
#  - source code
function replace_localization_tags ($tag, $callback, $source, &$template) {
  $search = array();
  $replace = array();
  $helpfiles_content = array();
    
  $help_dir = $template->template_dir . "/$tag/";
    
  # get all help tags from source
  $number_matches_1 = preg_match_all("/\[\[$tag\.([a-zA-Z0-9_\/\-]+)\.([a-zA-Z0-9_\-]+)\]\]/", $source, $matches_1);
    //var_dump($number_matches_1, $help_dir);
  if ($number_matches_1 == 0) {
    # no matches, nothing to replace, return source
    return $source;
  }
    
  # get all possible help files
  $files = array();
  $dir = opendir($help_dir);
  while (false !== ($fname = readdir($dir))) {
  	if (substr($fname, -4) != '.txt') {
  		continue;
  	}
  	$files[] = substr($fname, 0, -4);
  }
  closedir($dir);
    
  # read in all help files
  foreach ($files as $filename) {
    # read in the help file
    $content_parsed = array();
    # read content of helpfile
      $content = file($help_dir . $filename . '.txt');
      $help_tag = "";
      $help_tag_content = "";
      # get all help tags
      foreach ($content as $key => $value) {
        $value = trim($value);
        if (preg_match("/^[a-zA-Z0-9_\-]+:$/", $value)) {
          # new help tag
          if ($help_tag != "") {
            # add old help tag to array
            $content_parsed[$help_tag] = trim($help_tag_content);
          }
          $help_tag = substr($value, 0, (strlen($value) - 1));
          $help_tag_content = "";
        } else {
          # just a help line
          $help_tag_content .= $value . "\n";
        }
      }
      # add last tag which is now in the cache
      if (strlen($help_tag_content) > 0 and strlen($help_tag) > 0) {
        $content_parsed[$help_tag] = trim($help_tag_content);
      }
    $helpfiles_content[$filename] = $content_parsed;
  }
  
  # build all replacements for help tags without images
  # use the standard help image: /images/symbols/help.gif
  for ($i = 0; $i < $number_matches_1; $i++) {
    if (!isset($matches_1[1][$i]) or !isset($matches_1[2][$i])) {
      continue;      
    }
    if (strlen($matches_1[1][$i]) == 0 or strlen($matches_1[2][$i]) == 0) {    
      continue;
    }
    #print $matches_1[1][$i] . "." . $matches_1[2][$i] . "<br />\n";
    $search_str = "[[$tag." . $matches_1[1][$i] . "." . $matches_1[2][$i] . "]]";
    $replace_str = $callback($helpfiles_content, $matches_1, $i);
    array_push($search, $search_str);
    array_push($replace, $replace_str);
  }
    
  # replace help tags in source
  $source = str_replace($search, $replace, $source);
    
  return $source;
}

function callback_help_tag(&$file_content, &$matches, $offset) {
	global $stupidBrowser;

	// different XHTML code for bad browsers (linap, 13.06.2007)
	if (!$stupidBrowser) {
      $replace_str = '<a href="'.rewrite_help(array('helpTextLink' => true, 'option'=> urlencode($matches[1][$offset]) )).
                     '#' . urlencode($matches[2][$offset]) . '" ' .
                     //' title="Per Klick zur Hilfe | neues Fenster" target="Help"' .
                     // we need no link at the moment - schnueptus (22.06.2007)
                     ' class="add-feature"><img src="/images/symbols/help.gif"' .
                     ' alt="[?]" /><span class="add-feature-popup">' .
                     nl2br(htmlspecialchars($file_content[$matches[1][$offset]][$matches[2][$offset]])) . '</span></a>';
    } else {
      $replace_str = '<a href="'.rewrite_help(array('helpTextLink' => true, 'option'=> urlencode($matches[1][$offset]) )).
                     '#' . urlencode($matches[2][$offset]) . '" title="' . 
                     nl2br(htmlspecialchars($file_content[$matches[1][$offset]][$matches[2][$offset]])) . '" ' .
                     //' target="Help"' .
                     // we need no link at the moment - schnueptus (22.06.2007)
                     '><img src="/images/symbols/help.gif" alt="[?]" /></a>';
    }
    
    return $replace_str;
}

function smarty_replace_help_tags($source, &$template) {
	return replace_localization_tags('help', 'callback_help_tag', $source, $template);
}

function callback_local_tag(&$file_content, &$matches, $offset) {
    // can't use htmlspecialchars here, because we need to replace (simple) HTML tags
	return nl2br($file_content[DB_SCHEMA][$matches[2][$offset]]);
}

function smarty_replace_local_tags($source, &$template) {
	return replace_localization_tags('local', 'callback_local_tag', $source, $template);
}

# smarty_delete_comments()
#
# function for deleting comments
#
# parameter:
#  - template source code
#  - reference to smarty handler
# return:
#  - source code
function smarty_delete_comments ($source, &$template) {
  #$source = preg_replace("/<\!\-\-.*?\-\->/", "", $source);
  $source = preg_replace("/<\!\-\- .Id:.*?\-\->[\s\t\r\n]*/", "", $source);
  $source = preg_replace("/<\!\-\- .HeadURL:.*?\-\->[\s\t\r\n]*/", "", $source);
    
  return $source;
}

# smarty function representing a block, {dynamic} [...] {/dynamic}
# that will never be cached
#
# see /smarty_manual/caching.cacheable.html
function smarty_block_dynamic($param, $content, &$smarty) {
  return $content;
}

# smarty function generating a link to user's profile page
# parameter:
#  - user: user-object
#  - title: link title (optional), warning: will not be escaped
# return:
#  - link text
function smarty_user_info_link($params, &$smarty) {
  $user = $params['user'];
  
  if ($user == null or $user === false) {
    return 'NULL user!';
  }
  
  if (array_key_exists('truncate', $params) and strlen($user->getUsername()) > $params['truncate']) {
    $usernameHTML = htmlspecialchars(substr($user->getUsername(), 0, $params['truncate']-2)) . '&hellip;';
  } else {
    $usernameHTML = htmlspecialchars($user->getUsername());
  }  
      
  // handle a few special cases
  if ($user->isSystemUser()) {
    return '<a href="' . rewrite_mantis(array()) . '" title="'.SYSTEM_USER_LINK.'">' . SYSTEM_USER_NAME . '</a>'; 
  }
  if ($user->isAnonymous()) {
    return $usernameHTML;
  }
  
  $usernameURL  = urlencode($user->getUsername());
    
  if ($user->isExternal()) { 
    $serverURL = 'http://' . $user->getCity()->getSchemaName() . '.' . DOMAIN_BASE;
    $usernameHTML .= '@' . $user->getCity()->getName();
  } else { 
    $serverURL = '';
  }
    
    
  if (!array_key_exists('title', $params)) {
    # FIXME: i18n
    $title = LINK_USER_PAGE . $usernameHTML;
  } else {
    $title = $params['title'];
  }
  
  $link = '<a href="' . $serverURL . '/user/' . $usernameURL . '" title="' . $title . '">' . $usernameHTML . '</a>';

  if ($user->isInvisible()) {
    $link .= ' <em>('.NAME_INVISIBLE.')</em>';
  }

  return $link;
}

# smarty function generating a link to a user info page
# parameter:
#  to be documented ...
function smarty_user_info_url($params, &$smarty) {
  $user = $params['user'];
  
  if ($user != null && ($user instanceof UserModel) && $user->isSystemUser()) {
    return  rewrite_mantis(array()); 
  }
  
  if ($user != null && ($user instanceof UserModel) && $user->isAnonymous()) {
    return '#';
  }
  
  return rewrite_userinfo($params);
}

# smarty function generating the url to user's online status symbol
# parameter:
#  - user: user-object
# return:
#  - url
function smarty_user_online_url($params, &$smarty) {
  $user = $params['user'];
  
  if($user == null or $user === false)
    return 'NULL user!';
  
  if ( ($user instanceof UserModel) && $user->isAnonymous()) {
    return '';
  }
  
  if ($user->isExternal()) { 
    $serverURL = 'http://' . $user->city->schemaName . '.' . DOMAIN_BASE;
  } else { 
    $serverURL = '';
  }
  $serverURL .= '/online_status.php?u=' . rawurlencode($user->getUsername());

  return $serverURL;
}

# smarty function generating a link to (advanced) blog pages
# parameter:
#  - content: string inside <a></a>
#  - anchor: optional
#  - urlonly: optional
# return:
#  - link text
function smarty_blog_link($params, &$smarty) {
  $anchor = '';
  if (array_key_exists('anchor', $params)) {
    $anchor = '#' . $params['anchor'];
  }
  
  $link = '<a href="' . rewrite_blog($params) . $anchor . '">' . $params['content'] . '</a>';

  return $link;
}


# smarty function generating a URL to (advanced) blog pages
function smarty_blog_url($params, &$smarty) {
  return rewrite_blog($params);
}

# smarty function generating a link to groups's profile page
# parameter:
#  - group: GroupModel
#  - show_group_title: show title (optional)
#  - default_group_title: title if no title in GroupModel (optional), warning: will not be escaped
function smarty_group_info_link($params, &$smarty) {
  $group = $params['group'];  
  $groupNameHTML = htmlspecialchars($group->name);
    
  if ($params['show_group_title']==true) {
    if($group->title != 'group'){
      $groupNameHTML = $group->title . ' '. $groupNameHTML;
    } else {
      $groupNameHTML = DEFAULT_GROUP . ' '. $groupNameHTML;
    }
  }
  
  $url = rewrite_group($params);
  $url = htmlspecialchars($url);
  
  $link = '<a href="'.$url.'">' . $groupNameHTML . '</a>';
  return $link;
}

function smarty_group_info_url($params, &$smarty) {
  $url = rewrite_group($params);
  $url = htmlspecialchars($url);
  return $url;
}

function smarty_forum_link($params, &$smarty){
  $url = rewrite_forum($params);
  $url = htmlspecialchars($url);
    
  /*generate the name of the link */    
  if (!array_key_exists('name',$params) && array_key_exists('forum',$params)) {
    $forum = $params['forum'];
    $name = $forum->getName();
  } else if (!array_key_exists('name',$params) && array_key_exists('thread',$params)) {
    $thread = $params['thread'];
    $name = $thread->getCaption();
  } else if (!array_key_exists('name',$params) && array_key_exists('tag',$params)) {
    $tag = $params['tag'];
    $name = $tag->getName();
  } else {
    $name = array_key_exists('name',$params) ? $params['name'] : 'Link';
  }
  
  /* search after a title */
  $title = array_key_exists('title',$params) ? $params['title'] : '';
  
  $link = '<a href="' . $url . '" title="' . $title . '">' . $name . '</a>';

  return $link;
}

function smarty_pm_link($params, &$smarty){
  $url = rewrite_pm($params);
  $url = htmlspecialchars($url);
    
  /*generate the name of the link */    
  $name = array_key_exists('name',$params) ? $params['name'] : 'Link';
  
  /* search after a title */
  $title = array_key_exists('title',$params) ? $params['title'] : '';
  
  $link = '<a href="' . $url . '" title="' . $title . '">' . $name . '</a>';

  return $link;
}
function smarty_pm_url($params, &$smarty){
  $url = rewrite_pm($params);
  $url = htmlspecialchars($url);
  return $url;
}

function smarty_mantis_url($params, &$smarty){
  $url = rewrite_mantis($params);
  $url = htmlspecialchars($url);
  return $url;
}

function smarty_forum_url($params, &$smarty){
  $url = rewrite_forum($params);
  $url = htmlspecialchars($url);

  return $url;
}

function smarty_index_url($params, &$smarty){
  $url = rewrite_index($params);
  $url = htmlspecialchars($url);

  return $url;
}

function smarty_course_url($params, &$smarty){
  $url = rewrite_course($params);
  $url = htmlspecialchars($url);
  return $url;
}

# smarty function generating a link to a user info page
# parameter:
#  to be documented ...
function smarty_user_management_url($params, &$smarty) {
  return htmlspecialchars(rewrite_usermanagement($params));
}

function smarty_help_url($params, &$smarty) {
  return htmlspecialchars(rewrite_help($params));
}

function smarty_sports_url($params, &$smarty) {
  return htmlspecialchars(rewrite_sports($params));
}

function smarty_admin_url($params, &$smarty) {
  return htmlspecialchars(rewrite_admin($params));
}

function smarty_unihelp_strftime($timestamp, $format = 'TODAY') {
  # FIXME: i18n
  if ($format == 'NOTODAY' or $format == 'TODAY') {
    $today = mktime(0, 0, 0, date("m"), date("d"),  date("Y"));
    $yesterday = $today - 86400;
    if ($today <= $timestamp and $timestamp < $today + 86400) {
        if ($format == 'NOTODAY') {
            return strftime('%H:%M', $timestamp);
        } else { // format == TODAY
            return strftime(NAME_DATE_TODAY.', %H:%M', $timestamp);
        }
    } else if ($yesterday <= $timestamp and $timestamp < $yesterday + 86400) {
        return strftime(NAME_DATE_YESTERDAY.', %H:%M', $timestamp);
    } else {
        return strftime('%d.%m.%Y, %H:%M', $timestamp);
    }
  }
  if ($format == 'DATEONLY'){
  	$today = mktime(0, 0, 0, date("m"), date("d"),  date("Y"));
    $yesterday = $today - 86400;
    $tomorrow = $today + 86400;
    if ($today <= $timestamp and $timestamp < $today + 86400) {
        return strftime(NAME_DATE_TODAY, $timestamp);
    } else if ($yesterday <= $timestamp and $timestamp < $yesterday + 86400) {
        return strftime(NAME_DATE_YESTERDAY, $timestamp);
    } else if ($tomorrow <= $timestamp and $timestamp < $tomorrow + 86400) {
        return strftime(NAME_DATE_TOMORROW, $timestamp);
    } else {
        return strftime('%d.%m.%Y', $timestamp);
    }
  }
  
  if ($format == 'RSS'){
    //return strftime(DATE_RFC822, $timestamp);
   return date('D, d M Y H:i:s O', $timestamp);
  }
  return strftime($format, $timestamp);
}


function get_default_userpic($user, $version) {
	$postfix = array('big' => '',
					 'fancy' => '_fancy',
					 'tiny' => '_tiny',
					 'small' => '_small');
	$postfixStr = $postfix[$version];
	if ($user->isRegularLocalUser()) {
		$postfixStr = (($user->getGender() != '') ? $user->getGender() : 'u') . $postfixStr;
	} else if ($user->isExternal()) {
		$postfixStr = $user->getCity()->getSchemaName() . $postfixStr;
	}
	return '/images/kegel-' . $postfixStr . '.png';
}

function smarty_userpic($params, &$smarty) {
    $url = '';
    if (array_key_exists('big', $params)) {
        $user = $params['big'];
        $url = $user->getUserpicFile();
        if (!$url) {
            $url = get_default_userpic($user, 'big');
        }
    } else if (array_key_exists('fancy', $params)) {
        $user = $params['fancy'];
        $url = $user->getUserpicFile('fancy');
    	if (!$url) {
            $url = get_default_userpic($user, 'fancy');
        }
    } else if (array_key_exists('tiny', $params)) {
        $user = $params['tiny'];
        $url = $user->getUserpicFile('tiny');
    	if (!$url) {
            $url = get_default_userpic($user, 'tiny');
        }
    } else if (array_key_exists('small', $params)) {
        $user = $params['small'];
        $url = $user->getUserpicFile('small');
    	if (!$url) {
            $url = get_default_userpic($user, 'small');
        }
    }
    
    return $url;
}

function smarty_translate($params, &$smarty) {
  // translate functions are in lang/*inc files
  if (array_key_exists('privacy',$params)) {
    return translate_privacy($params['privacy']);
  }
  if (array_key_exists('rating_cat',$params)) {
    return translate_rating_cat($params['rating_cat']);
  }
  if (array_key_exists('nationality',$params)) {
    return translate_nationality($params['nationality']);
  }
  if (array_key_exists('box',$params)) {
    return translate_box($params['box']);
  }
  if (array_key_exists('right', $params)){
  	return translate_rights($params['right']);
  }
  if (array_key_exists('mantis', $params)){
    return translate_mantis($params['mantis']);
  }
  return '';
}

function smarty_user_status($params, &$smarty) {
	if(!array_key_exists('user', $params)){
		return '';
	}
    $user = $params['user'];
    if($user->getFlirtStatus() == 'red'){
    	return '<img class="status" src="/images/icons/bullet_red.png" alt="'.COLOR_RED.'" /> ('.STATUS_RED.')';
    }
    if($user->getFlirtStatus() == 'yellow'){
        return '<img class="status" src="/images/icons/bullet_yellow.png" alt="'.COLOR_YELLOW.'" /> ('.STATUS_YELLOW.')';
    }
    if($user->getFlirtStatus() == 'green'){
        return '<img class="status" src="/images/icons/bullet_green.png" alt="'.COLOR_GREEN.'" /> ('.STATUS_GREEN.')';
    }
    return '('.STATUS_NONE.')';
}

function smarty_box_functions($params, &$smarty){
	 return htmlspecialchars(rewrite_box_functions($params));
}

function smarty_genitiv($word) {
  // genitiv function is in lang/*inc files
  return genitiv($word);
}

// convert relative to absolute URIs
function smarty_relative_to_absolute($string) {
    // insert server address before relative URIs in href oder src
    // attribute of HTML tags
    return preg_replace('#(<[^>]+(?:href|src)=")/#', 
                        '$1' . 'http://' . $_SERVER['SERVER_NAME'] . '/',
                        $string);
}

function smarty_banner($params, &$smarty) {
    include_once MODEL_DIR . '/base/banner_model.php';
    $banner = BannerModel::getRandomBanner();
    if ($banner->isEmpty()) {
        $banner = null;
    }
    $smarty->assign('banner_object', $banner);
}

function smarty_user_online_box_url($params, &$smarty){
  return htmlspecialchars(rewrite_user_online($params));
}

function smarty_forum_new($params, &$smarty) {
    $forumRead = ForumRead::getInstance(Session::getInstance()->getVisitor(), true);
    //var_dump($params);
	$firstNewEntryId = $forumRead->firstNewEntryId($params['thread']);
	if ($firstNewEntryId) {
		return '<a href="' . rewrite_forum(array("entryId" => $firstNewEntryId)) . '" title="' . NAME_FIRST_NEW_ENTRY . '"><img src="/images/icons/new.png" alt="NEU" /></a>';
	}
    /*if ($forumRead->isNew($params['forumId'], $params['threadId'])) {
        return '<img src="/images/icons/new.png" alt="NEU" />';   
    }*/
    return '';
}

function smarty_course_rating_desc($params, &$smarty) {
	if(array_key_exists('rating',$params)){
		if($params['rating'] == 0){
			return '';
		}
        return course_rating_desc(round($params['rating']));
	}
    
    return '';
}

function smarty_breadcrumbs($params, &$smarty) {
    if (!array_key_exists('method',$params)){
        return '';
    }
    
    $method = $params['method'];
    $crumbs = array();
    
    while ($method != null) {
        array_push($crumbs, $method);
        $method = $method->getParentMethod();
    }
    
    $c = '';
    $isFirst = true;
    $count = 0;
    $crumbsSize = count($crumbs);
    
    while ($m = array_pop($crumbs)) {
        ++$count;
        
        // first <ul> gets an id
        $c .= '<ul' . (($count == 1) ? ' id="breadcrumbs"' : '') . '><li>';
        if (!$isFirst) {
            $c .= '&raquo; ';
        }
        if ($m->getURL() and $count != $crumbsSize) {
            $c .= '<a href="' . $m->getURL() . '">';
        }
        $c .= $m->getName();
        if ($m->getURL() and $count != $crumbsSize) {
            $c .= '</a>';
        }
        if ($count == $crumbsSize) {
            // TODO: move string constant into language file
            $c .= '<a href="' . $m->getURL(). '" title="' . NAME_RELOAD . '"><img src="/images/icons/arrow_refresh_small_sw.png" alt="reload" /></a>';
        }
        
        $isFirst = false;
    }
    while ($count--) {
        $c .= '</li></ul>';
    }
    $pagename = $params['method']->getName();
    if (array_key_exists('pagename', $params)) {
        $pagename = $params['pagename'];
    }
    $c .= '<h2 id="pagename">' . $pagename . '</h2>';
    
    return $c;
}

function smarty_fuck_ie($tpl_source, &$smarty) {
    // replace all static images by their gif equivalent
    return preg_replace('!src="(/(?:images)(?:/bewertungen/|/icons/|/flags/|/symbols/|/)[^"/]+)png"!', 'src="$1gif"', $tpl_source);
}

function smarty_errorbox($params, &$smarty) {
    if (!array_key_exists('var', $params)) {
        $var = 'central_errors';
    } else {
        $var = $params['var'];
    }
    $errors = $smarty->get_template_vars($var);
    if (null == $errors) {
        return '';
    }
    
    $result = '';
    $caption = ERR_ERROR_OCCURED;
    $preString = '';
    if (array_key_exists('caption', $params)) {
        $caption = $params['caption'];
    }
    if (array_key_exists('prestring', $params)) {
        // do not escape because string may containt HTML
        $preString = $params['prestring'];
    }
    
    $result .= 
     '<div class="box errorbox">' . $preString . '<h3>' . htmlspecialchars($caption) . '</h3><ul>';
    foreach ($errors as $error) {
        if (is_string($error)) {
            $result .= '<li>' . htmlspecialchars($error) . '</li>';
        }
    }
    $result .= 
     '</ul></div>';
     
     return $result;
}

function smarty_user_random_pic($params, &$smarty) {
    if(!array_key_exists('user',$params)){
    	return '<img src="/images/foto.png" alt="Dein Userbild hier!" />';
    }
    $user = $params['user'];
    if($user == null || $user->id == null){
        return '<img src="/images/foto.png" alt="Dein Userbild hier!" />';
    }
    $url = rewrite_userinfo(array('user'=>$user));
    $pic = smarty_userpic(array('fancy'=>$user), $smarty);
    return '<a href="'.$url.'" title="'. LINK_USER_PAGE .$user->getUsername().'" id="randompic"><img src="'.$pic.'" alt="'. LINK_USER_PAGE .$user->getUsername().'" /></a>';
}

function generate_js_include($smarty, $filename, $version = '1') {
	return '<script src="' . $smarty->get_template_vars('TEMPLATE_DIR') . '/' . $filename . '?' . $version . '" type="text/javascript"></script>';
}

function generate_css_include($smarty, $filename, $version = '1') {
	return '<link title="css" rel="stylesheet" href="' . $smarty->get_template_vars('TEMPLATE_DIR') . '/' . $smarty->get_template_vars('CSS_DIR') . '/' . $filename . '?' . $version . '" type="text/css" />';
}

function collect_files($configurationFile, $generatedFile, $generatedVersion, $templateDir, $suffix = '') {
	$files = array();
	
    $suffixPath = ($suffix != '') ? ($suffix . '/') : '';
    $suffix = ($suffix != '') ? ('.' . $suffix) : '';
	if (DEVEL || !file_exists($templateDir . $suffixPath . $generatedFile)) {
		$conf = file($templateDir . $configurationFile . $suffix);
		foreach ($conf as $include) {
			array_push($files, trim($include));
		}
	} else {
		array_push($files, $generatedFile);
	}
	
	$version = null;
	if (DEVEL || !file_exists($templateDir . $suffixPath . $generatedVersion)) {
		$version = sha1(uniqid(rand()));
	} else {
		$version = file_get_contents($templateDir . $suffixPath . $generatedVersion);
	}
	
	return array($files, $version);
}

function smarty_generate_headers($params, $smarty) {
	$headers = '';
	
	$templateDir = $smarty->template_dir . '/';
	$jsFiles = collect_files(JAVASCRIPT_CONFIGURATION, GENERATED_JAVASCRIPT_FILE, GENERATED_JAVASCRIPT_VERSION_FILE, $templateDir);
	$cssFiles = collect_files(CSS_CONFIGURATION, GENERATED_CSS_FILE, GENERATED_CSS_VERSION_FILE, $templateDir, $smarty->get_template_vars('CSS_DIR'));
	
	foreach ($cssFiles[0] as $cssInclude) {
		if ($cssInclude) {
			$headers .= generate_css_include($smarty, $cssInclude, $cssFiles[1]) . "\n";
		}
	}
	
	foreach ($jsFiles[0] as $jsInclude) {
		if ($jsInclude) {
			$headers .= generate_js_include($smarty, $jsInclude, $jsFiles[1]) . "\n";
		}
	}
	return $headers;
}

if (DEVEL === TRUE and !defined('CACHETEST')) {
  # in development versions clear template cache on every request
  //$_SMARTY->clear_compiled_tpl();
  $_SMARTY->force_compile = true;
}

$_SMARTY->register_prefilter("smarty_replace_help_tags");
$_SMARTY->register_prefilter("smarty_replace_local_tags");

$_SMARTY->register_function('userpic_url', "smarty_userpic");
$_SMARTY->register_function('user_info_link', "smarty_user_info_link");
$_SMARTY->register_function('user_info_url', "smarty_user_info_url");
$_SMARTY->register_function('user_online_url', "smarty_user_online_url");
$_SMARTY->register_function('user_management_url', "smarty_user_management_url");
$_SMARTY->register_function('group_info_link', "smarty_group_info_link");
$_SMARTY->register_function('group_info_url', "smarty_group_info_url");
$_SMARTY->register_function('forum_link', "smarty_forum_link");
$_SMARTY->register_function('forum_url', "smarty_forum_url");
$_SMARTY->register_function('index_url', "smarty_index_url");
$_SMARTY->register_function('pm_link', "smarty_pm_link");
$_SMARTY->register_function('pm_url', "smarty_pm_url");
$_SMARTY->register_function('course_url', "smarty_course_url");
$_SMARTY->register_function('blog_link', "smarty_blog_link");
$_SMARTY->register_function('blog_url', "smarty_blog_url");
$_SMARTY->register_function('mantis_url', "smarty_mantis_url");
$_SMARTY->register_function('admin_url', "smarty_admin_url");
$_SMARTY->register_function('help_url', "smarty_help_url");
$_SMARTY->register_function('sports_url', "smarty_sports_url");

$_SMARTY->register_function('user_online_box_url', "smarty_user_online_box_url");
$_SMARTY->register_function('box_functions', "smarty_box_functions");
$_SMARTY->register_function('user_random_pic', "smarty_user_random_pic");

$_SMARTY->register_function('banner', "smarty_banner");
$_SMARTY->register_function('translate', "smarty_translate");
$_SMARTY->register_function('user_status', "smarty_user_status");
$_SMARTY->register_function('course_rating_desc', "smarty_course_rating_desc");

$_SMARTY->register_function('breadcrumbs', "smarty_breadcrumbs");

$_SMARTY->register_function('forum_new', "smarty_forum_new", false, array('thread'));

$_SMARTY->register_function('errorbox', "smarty_errorbox");

$_SMARTY->register_function('generate_headers', "smarty_generate_headers");

$_SMARTY->register_modifier('unihelp_strftime', "smarty_unihelp_strftime");
$_SMARTY->register_modifier('genitiv', "smarty_genitiv");
$_SMARTY->register_modifier('relativeToAbsolute', "smarty_relative_to_absolute");

if ($stupidBrowser) {
    $_SMARTY->register_outputfilter("smarty_fuck_ie");
}

if (DEVEL === FALSE) {
  $_SMARTY->register_prefilter("smarty_delete_comments");
}

$_SMARTY->register_block('dynamic', 'smarty_block_dynamic', false);

?>
