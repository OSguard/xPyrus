<?php

require_once './conf/config.php';
define('CRAZY_EQUALS', '===========~~~~~~~~~==========');
if (file_exists(BASE . '/' . GENERATED_REWRITE_FILE)) {
  require_once BASE . '/' . GENERATED_REWRITE_FILE;
  
  $url = urldecode($_SERVER['REQUEST_URI']);
  if (strpos($url, '/') === 0) {
    $url = substr($url, 1);
  }
  $matches = parseRequest($url);
  if ($matches !== null && is_array($matches)) {
    $pattern = $matches[0];
    $matches = $matches[1];
    
    if (preg_match('!index\.php\?(.*)!', $pattern, $patternMatches)) {
      foreach (explode('&', $patternMatches[1]) as $p) {
        $temp = str_replace('=', CRAZY_EQUALS, $p);
        foreach ($matches as $nr => $m) {
          $temp = str_replace('$' . $nr, $m, $temp);
        }
        $p = explode(CRAZY_EQUALS, $temp);
        if(!array_key_exists($p[0],$_REQUEST)){
            $_REQUEST[$p[0]] = $p[1];
        }
        if(!array_key_exists($p[0],$_GET)){
            $_GET[$p[0]] = $p[1];
        }
      }
    } else {
      $temp = $pattern;
      foreach ($matches as $nr => $m) {
        $temp = str_replace('$' . $nr, $m, $temp);
      }
      header('Location: ' . $temp);
    }
  } else if (!$url) {
    header('Location: /home');
  }
} else {
  die ('please run ' . BASE . '/admin/generate_files.php to generate url rewrite schema');
}
?>
