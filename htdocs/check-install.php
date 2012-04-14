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
?>
<html>
<head>
	<title>Test the installation for completeness</title>
	<style type="text/css">
	  .green,
	  .red {
	  	font-weight: bold;
			color: white;
			width: 150px;
	  }
		.green {
			background-color: green;			
		
		}
		.red {
			background-color: red;			
		}
	</style>
</head>
<body>
<h1>UniHelp configuration requirements</h1>
<table>
<?php 

# base path for all other locations
if ($_SERVER['DOCUMENT_ROOT']) {
  define('BASE', $_SERVER['DOCUMENT_ROOT']);
} else {
  define('BASE', realpath(dirname(__FILE__) . '/../'));
}

# include local config, if there is one
if (file_exists("./conf/local_config.php")) {
  include_once("./conf/local_config.php");
}

require_once './conf/config_check.php';
foreach ($CONFIG_CHECKS as $check) {
    if (!$check->isSevere()) {
        continue;
    }
    
    $success = $check->doSafeCheck();
    $cssClass = $success ? 'green' : 'red';
    $string   = $success ? 'OK' : 'ERROR!';
    echo '<tr><td>' . $check->getName() . '</td><td class="' . $cssClass .'">' . $string . '</td></tr>';
}
?>
</table>
</body>
</html>