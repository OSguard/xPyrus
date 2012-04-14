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

require_once BASE . '/conf/config_check_classes.php';

$CONFIG_CHECKS = array(
    new OptionalConstantCheck('DEVEL', 'true'),
    new OptionalConstantCheck('LIB_DIR', 'BASE . "/lib"'),
    new OptionalConstantCheck('CORE_DIR', 'BASE . "/core"'),
    new OptionalConstantCheck('MODEL_DIR', 'CORE_DIR . "/models"'),
    # define directory, where smileys should be stored
    new OptionalConstantCheck('SMILEY_DIR', 'BASE . "/images/smileys"'),
    # define URL, where smileys are available for browsers/users
    new OptionalConstantCheck('SMILEY_URL', '"/images/smileys"'),
    # define file, which contains smileys and their replacements
    new OptionalConstantCheck('SMILEY_INCLUDE_FILE', 'SMILEY_DIR . "/smileys.php.inc"'),
    new OptionalConstantCheck('GLOBAL_SETTINGS_INCLUDE_FILE', 'BASE . "/conf/global_settings.php.inc"'),
    new OptionalConstantCheck('USER_TEMPLATE_DIR', '"unihelp_hp"'),
    # define directory, where user's files (e.g. uploaded pictures) should be stored
    new OptionalConstantCheck('RELATIVE_USERFILE_DIR', '"./userfiles"'),
    
    new OptionalConstantCheck('TSEARCH2_AVAILABLE', 'false'),
    new OptionalConstantCheck('TABLELOG_AVAILABLE', 'false'),
    new OptionalConstantCheck('DIJKSTRA_AVAILABLE', 'false'),
    new OptionalConstantCheck('BASIC_STUDIES_AVAILABLE', 'false'),
    
    new OptionalConstantCheck('PASSWORD_SALT', '""'),
    new OptionalConstantCheck('URL_SALT', '""'),
    new OptionalConstantCheck('STAT_SECRET', '"showMeStats"'),
    new OptionalConstantCheck('LOG_SESSIONS', 'false'),
    new OptionalConstantCheck('TOOLBAR_VERSION', '"unknown"'),
    
    new OptionalConstantCheck('CONTRIB_AVAILABLE', 'false'),
    
    # support/mantis stuff
    new OptionalConstantCheck('MANTIS_TYPE', '"email"'),
   
    new OptionalConstantCheck('BOX_DEFAULT_RIGHT', '""'),
    new OptionalConstantCheck('BOX_DEFAULT_LEFT', '"user_login"'),
    new OptionalConstantCheck('BOX_DEFAULT_RIGHT_LOGGIN', '""'),
    new OptionalConstantCheck('BOX_DEFAULT_LEFT_LOGGIN', '"user_login"'),
   
    new OptionalConstantCheck('NO_REGISTER_VALIDATION', 'false'),
   
    new NeccessaryConstantCheck('SHARED_MEMORY_OFFSET', 0),
    new NeccessaryConstantCheck('DB_SCHEMA', 'your city'),
    new NeccessaryConstantCheck('DOMAIN_BASE'),
    new NeccessaryConstantCheck('LOG_DIR', "my log dir"),
    new NeccessaryConstantCheck('UPLOAD_DIR', "the upload dir"),
    new NeccessaryConstantCheck('ADMIN_MAIL'),
    
    new NeccessaryConstantCheck('PROJECT_NAME'),
    
    new PHPModuleCheck('pg_connect'),
    
    # validate PHP config
    new FatalPHPCheck('register_globals', false),
    new FatalPHPCheck('magic_quotes_gpc', false),
    new FatalPHPCheck('magic_quotes_runtime', false),
    new FatalPHPCheck('session.use_cookies', true),
    new OptionalPHPCheck('post_max_size', '11M'),
);

if (php_sapi_name() != 'cli') {
    array_push($CONFIG_CHECKS, new ApacheModuleCheck('mod_rewrite'));
}

?>
