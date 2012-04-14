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

# $Id: index.php 5743 2008-03-25 19:48:14Z ads $
#
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/lib/lib-sql/index.php $
# index file for loading AdoDB engine

# define sql layer dir
define('SQL_LAYER_DIR', LIB_DIR . "/lib-sql");
if (!is_dir(SQL_LAYER_DIR)) {
  die("Please install database layer<br />\n");
}

# define cache dir
define('SQL_LAYER_CACHE_DIR', SQL_LAYER_DIR . "/cache");
$ADODB_CACHE_DIR = SQL_LAYER_CACHE_DIR;


# this basically points to the adodb directory

/*
 *         WARNING    --     WARNING
 * 
 *  adodb-ads may not be compatible with
 *  the original adodb-version,
 *  especially when it comes to optional parameters
 *   (cf. CompleteTrans($autoComplete))
 * 
 *         WARNING    --     WARNING
 */
define('ADODB_VERSION', "adodb-ads");
if (!defined('ADODB_DIR')) {
  define('ADODB_DIR', SQL_LAYER_DIR . "/" . ADODB_VERSION . "/");
}
# include AdoDB
if (is_file(ADODB_DIR . "adodb.inc.php")) {
  require_once(ADODB_DIR . 'adodb.inc.php');
} else {
  die("Please install database layer<br />\n");
}


// create a new AdoDB instance
$_DB = ADONewConnection('postgres');

$_DB->debug = DEVEL;
# output sql queries
$_DB->debug_sql_queries = true;
# time all queries
$_DB->debug_sql_timing = true;
# show the debugging
$_DB->debug_hide_debugging = true;

// and connect the database
if (!$_DB->Connect(DB_SERVER . ':' . DB_PORT, DB_USERNAME, DB_PASSWORD, DB_NAME)) {
    throw new DBException($this->getErrorMessage(DB_CONNECTION_FAILED), E_ERROR);
}

// improve performance
//$ADODB_COUNTRECS = false; # does not really work with ADODB_FETCH_ASSOC

// set fetch mode
$_DB->SetFetchMode(ADODB_FETCH_ASSOC);  # ADODB_FETCH_NUM|ADODB_FETCH_ASSOC|ADODB_FETCH_BOTH

// set search path for schema
if ($_DB->Execute("SET search_path TO " . DB_SCHEMA . ", public") === false) {
    throw new DBException($_DB->getErrorMessage(DB_SEARCH_PATH_FAILED, DB_SCHEMA), E_ERROR);
}

?>
