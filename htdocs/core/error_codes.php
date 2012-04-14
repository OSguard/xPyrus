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
# $Id: error_codes.php 5743 2008-03-25 19:48:14Z ads $

/**
 * Central definition of error codes for use in the exception/logging system
 * @package Core
 */

/***************************************************************************
 *   copyright            : (C) 2005 unihelp (LD)
 *   email                :  info@unihelp.org
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

/**
 * This file lists all error codes. See Logging class for details on how to use them. 
 * Please look thoroughly BEFORE creating a new error code if it is already existing!!! 
 * If not, please feel free to create a new one.
 * Categories are (to be amended if necessary):<br />
 *	FILE	-	for all file system related errors<br />
 *	CORE	-	for all core related errors<br />
 *	DB	-	for all database related errors<br />
 *	USER	-	for all user related errors<br />
 *	GENERAL	-	for all general errors that are not really part of the categories mentioned above
 *
 */

//-------------------------------------
//token used to signal a parameter
define ('PARAMETER_TOKEN', '&&&');
//-------------------------------------

//-------------------------------------
//section CORE
//define ( 'CORE_USER_DATA_NON_EXISTENT', "Data/details/config about user '".PARAMETER_TOKEN."' was not found in database.");
define ( 'CORE_LOGGING_FAILED', "Logging failed.");
define ( 'CORE_PRIVATE_KEY_FAILED', "Using of private key failed.");
define ( 'CORE_BOX_CONTROLLER_FAILED', "No box destination given.");
define ( 'CORE_CONTROLLER_FAILED', "Could not create controller.");
define ( 'CORE_SHARED_MEMORY_ACQUIRE_FAILED', "Could not acquire shared memory for key " . PARAMETER_TOKEN . " and size " . PARAMETER_TOKEN . ".");
define ( 'CORE_SEND_MAIL_FAILED', "Could not send email.");

//-------------------------------------
// section DB
define ( 'DB_SQL_QUERY_FAILED', "Database failed with '".PARAMETER_TOKEN."'.");
define ( 'DB_TRANSACTION_FAILED', "Transaction could not be committed.");
define ( 'DB_COUNT_FAILED', "COUNT returned no result.");
define ( 'DB_SEARCH_PATH_FAILED', "Could not set search path for schema '".DB_SCHEMA."'.");
define ( 'DB_CONNECTION_FAILED', "Could not open database connection");
//-------------------------------------
//section FILE
define ( 'FILE_FILE_NOT_FOUND', "File '".PARAMETER_TOKEN."' does not exist or cannot be accessed.");
define ( 'FILE_INVALID_FORMAT', "Invalid format for file '".PARAMETER_TOKEN."'.");
define ( 'FILE_INVALID_FILENAME', "Invalid file name for file '".PARAMETER_TOKEN."'.");
define ( 'FILE_UPLOAD_FAILED', "Move of uploaded file '".PARAMETER_TOKEN."' to '".PARAMETER_TOKEN."' failed.");
define ( 'FILE_MKDIR_FAILED', "Creation of directory '".PARAMETER_TOKEN."' failed.");

//-------------------------------------
//section GENERAL
define ( 'GENERAL_ARGUMENT_MISSING', "Argument '".PARAMETER_TOKEN."' is missing.");
define ( 'GENERAL_ARGUMENT_INVALID', "Argument '".PARAMETER_TOKEN."' is invalid.");
define ( 'GENERAL_INVALID_XML', "XML code is invalid.");
define ( 'GENERAL_NODE_NOT_FOUND', "Could not find XML node '".PARAMETER_TOKEN."'.");

//-------------------------------------
//section USER
define ( 'USER_REQUESTED_DELETION', PARAMETER_TOKEN . ": uid '" . PARAMETER_TOKEN . "' requested deletion\n");
define ( 'USER_ACCOUNT_PURGED', PARAMETER_TOKEN . ": uid '" . PARAMETER_TOKEN . "' was purged\n");

//-------------------------------------
//-------------------------------------
// error codes (not exceptions)
define ('E_SECURITY_WARNING', 8192);

?>
