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

// $Id: create_index.php 4688 2007-05-26 19:23:56Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/trunk/htdocs/admin/create_index.php $

require_once dirname(__FILE__) . '/../conf/config.php';

function generateCSSandJS() {
  $templateDir = opendir(BASE . '/template');
  while (($dir = readdir($templateDir)) !== false) {
  	if ($dir == '.' || $dir == '..') {
  		continue;
  	}
  	$dir = BASE . '/template/' . $dir . '/';
  	if (!is_dir($dir)) {
  		continue;
  	}
  	
  	if (file_exists($dir . '/' . JAVASCRIPT_CONFIGURATION)) {
  		$confJS = file($dir . '/' . JAVASCRIPT_CONFIGURATION);
  		$JSbuffer = '';
  		foreach ($confJS as $filename) {
  			$filename = trim($filename);
  			if (!$filename) {
  				continue;
  			}
  			
  			$buffer = file($dir . $filename);
  			foreach ($buffer as $line) {
  				$JSbuffer .= trim($line) . "\n";
  			}
  		}
  		// strip JS comments
  		$JSbuffer = preg_replace('!/\*.*?\*/!', '', $JSbuffer);
  		$JSfile = fopen($dir . '/' . GENERATED_JAVASCRIPT_FILE, 'w+');
  		fwrite($JSfile, $JSbuffer);
  		fclose($JSfile);
  		$JSversion = fopen($dir . '/' . GENERATED_JAVASCRIPT_VERSION_FILE, 'w+');
  		fwrite($JSversion, sha1($JSbuffer));
  		fclose($JSversion);
  	}
  	
  	$dirRes = opendir($dir);
  	while (($file = readdir($dirRes)) !== false) {
  		if (preg_match('/' . preg_quote(CSS_CONFIGURATION) . '\.(.*)/', $file, $matches)) {
  			$confCSS = file($dir . $matches[0]);
  			$CSSbuffer = '';
  			foreach ($confCSS as $filename) {
  				$filename = trim($filename);
  				if (!$filename) {
  					continue;
  				}
  				
  				$buffer = file($dir . $matches[1] . '/' . $filename);
  				foreach ($buffer as $line) {
  					$CSSbuffer .= trim($line) . "\n";
  				}
  			}
  			$CSSfile = fopen($dir . $matches[1] . '/' . GENERATED_CSS_FILE, 'w+');
  			fwrite($CSSfile, $CSSbuffer);
  			fclose($CSSfile);
  			$CSSversion = fopen($dir . $matches[1] . '/' . GENERATED_CSS_VERSION_FILE, 'w+');
  			fwrite($CSSversion, sha1($CSSbuffer));
  			fclose($CSSversion);
  		}
  	}
  }
}

function generateRewriteSchema() {
  $rewriteDir = opendir(BASE . '/conf/rewrite');
  $rewriteRules = array();
  while (($file = readdir($rewriteDir)) !== false) {
  	if ($file == '.' || $file == '..') {
  		continue;
  	}
  	
    $file = BASE . '/conf/rewrite/' . $file;
  	if (!is_file($file)) {
  		continue;
  	}
  	
  	$rewriteRules = array_merge($rewriteRules, file($file, FILE_SKIP_EMPTY_LINES));
  }
  
  function filter_comments($rule) {
    return $rule && !preg_match('!^\s*#!', $rule);
  }
  $rewriteRules = array_map("trim", $rewriteRules);
  $rewriteRules = array_filter($rewriteRules, "filter_comments");
  
  define('EMPTY_MARKER', 'EEEEEEEEEMMMMMMMMPPPPPPPPPTTTTTTTTTYYYYYYYYY');
  
  // group by url stem
  $tempArray = array();
  foreach ($rewriteRules as $rule) {
    $parts = explode(' ', $rule);
    $pos = strpos($parts[0], '/');
	$rule = preg_replace('/(\s)+/', '$1', $rule);
    if ($pos !== false) {
      $tempArray[preg_replace('!^\^!', '', substr($parts[0], 0, $pos))][] = $rule;
    } else {
      $tempArray[EMPTY_MARKER][] = $rule;
    }
  }

  $rewriteFile = fopen(BASE . '/' . GENERATED_REWRITE_FILE, 'w+');
  fwrite($rewriteFile, '<?php function parseRequest($url) {');
  foreach ($tempArray as $stem => $rewriteRules) {
    if ($stem != EMPTY_MARKER && count($rewriteRules) > 2) {
      fwrite($rewriteFile, 'if (strpos($url, \'' . $stem . '\') === 0) {');
    }
    foreach ($rewriteRules as $rule) {
      $parts = explode(' ', $rule);
   
      // simulate QSA-flag
      $parts[0] = preg_replace('!\$$!', '(?:$|\?)', $parts[0]);
      fwrite($rewriteFile, 'if (preg_match(\'@' . $parts[0] . '@\', $url, $matches)) {');
      fwrite($rewriteFile, 'return array(\'' . $parts[1] . '\', $matches);');
      fwrite($rewriteFile, "}\n");
    }
    if ($stem != EMPTY_MARKER && count($rewriteRules) > 2) {
      fwrite($rewriteFile, ' }');
    }
  }
  fwrite($rewriteFile, 'return null; } ?>');
  fclose($rewriteFile);
}

generateCSSandJS();
generateRewriteSchema();

?>
