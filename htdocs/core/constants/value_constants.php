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

// $Id: value_constants.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/constants/value_constants.php $

/*
 * define some value constants like blog entries per page, ... (with prefix V_)
 */
define('V_BLOG_ENTRIES_PER_PAGE',         3);
define('V_BLOG_START_PAGE',		  		  1);
define('V_BLOG_MIN_FILTER_ENTRIES',       5);

define('V_BLOG_ADVANCED_ENTRIES_PER_PAGE',         6);
define('V_BLOG_ADVANCED_START_PAGE',               1);
define('V_BLOG_ADVANCED_ENTRIES_PER_FEED',        20);

define('V_GUESTBOOK_ENTRIES_PER_PAGE',   10);
define('V_GUESTBOOK_START_PAGE',		  1);

define('V_FORUM_THREAD_ENTRIES_PER_PAGE',    10);
define('V_FORUM_THREADS_PER_PAGE',    20);
define('V_FORUM_LATEST_THREADS_PER_PAGE',    40);
define('V_FORUM_SEARCH_MINIMAL_LENGTH', 3);
define('V_FORUM_EDIT_THREAD_ENTRY_TIME', 6); // in hours

define('V_PM_ENTRIES_PER_PAGE',    10);

define('V_SHOUTBOX_ENTRIES', 10);

define('V_NEWS_ARCHIV_ENTRIES_PER_PAGE',    5);

define('V_COURSE_HOME_FILES_PER_PAGE',    5);
define('V_COURSE_HOME_THREADS_PER_PAGE',    5);

define('V_COURSE_FILES_PER_PAGE',    10);
define('V_COURSE_FILES_START_PAGE',    1);
define('V_COURSE_FILES_DEFAULT_COSTS', 1);

define('V_GROUP_INFOPAGE_PARSE_AS_HTML', false);
define('V_GROUP_INFOPAGE_PARSE_AS_FORMATCODE', true);
define('V_GROUP_INFOPAGE_PARSE_AS_SMILEYS', true);

define('V_BANNER_FILE_SIZE', 102400);

define('V_USER_NAME_DISPLAY_LENGHT', 16);

define('V_ENTRY_MAX_CHARS', 32576);
define('V_SIGNATURE_MAX_LINES', 5);
define('V_SIGNATURE_MAX_CHARS', 500);
define('V_GB_COMMENT_MAX_CHARS', 255);
define('V_CAPTION_MAX_CHARS', 200);
define('V_URL_MAX_LENGTH', 35);
define('V_URL_TAIL', 6);

// in seconds
define('V_USER_MODEL_DATA_CACHE_LIFETIME', 5 * 60);
define('V_USER_MODEL_ONLINE_LIFETIME', 2 * 60);
define('V_FORUM_READ_LOAD_LIFETIME', 2 * 60);
define('V_USER_INFO_VISIT_LIFETIME', 10 * 60);
define('V_FORUM_THREAD_VISIT_LIFETIME', 10 * 60);

// in minutes
define('V_USER_ONLINE_TIMEOUT', 30);
define('V_USER_ONLINE_DISPLAY_TIMEOUT', 30);

?>
