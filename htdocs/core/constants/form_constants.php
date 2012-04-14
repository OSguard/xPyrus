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

// $Id: form_constants.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/constants/form_constants.php $

/*
 * define some form (GET/POST-variables) constants (with prefix F_)
 */
define('F_BLOG_ID',                       'diaryid');
define('F_BLOG_PAGE',                     'diarypage');
define('F_BLOG_MODE_EDIT',                'editdiary');
define('F_BLOG_ACTION_ADD_EDIT',          'diary_entry');
define('F_BLOG_ACTION_DELETE',            'deldiary');
define('F_BLOG_CONTENT_RAW',         		'entry_text');

define('F_GUESTBOOK_ID',                  'gbid');
define('F_GUESTBOOK_PAGE',                'gbpage');
define('F_GUESTBOOK_WEIGHTING',               'bewertung');
define('F_GUESTBOOK_WEIGHTING_ADDITIONAL',    'pluspunkt');
define('F_GUESTBOOK_MODE_COMMENT',        'commentgb');
define('F_GUESTBOOK_MODE_EDIT',           'editgb');
define('F_GUESTBOOK_MODE_QUOTE',          'quotegb');
define('F_GUESTBOOK_ACTION_ADD',		  'gb_entry_add');
define('F_GUESTBOOK_ACTION_EDIT',		  'gb_entry_edit');
define('F_GUESTBOOK_ACTION_COMMENT',      'gb_comment_entry');
define('F_GUESTBOOK_ACTION_DELETE',       'delgb');
define('F_GUESTBOOK_ACTION_NONE',       '');

define('F_GUESTBOOK_CONTENT_RAW',                   'entry_text');

define('F_ENTRY_RANDID',                   'randid');



define('F_ENABLE_SMILEYS',                'enable_smileys');
define('F_ENABLE_FORMATCODE',             'enable_formatcode');
define('F_ENABLE_UPDATE_NOTICE',          'enable_update_notice');

define('F_FRIENDLIST_ACTION_ADD',         'addfriend');
define('F_FRIENDLIST_ACTION_DELETE',      'delfriend');

define('F_SEARCH_SUBMIT',                 'search_submit');
define('F_SEARCH_QUERY',                  'query');

define('F_MODE',							'mod');
define('F_SUB_MODE', 						'sub_mod');
define('F_USER_INFO', 						'user_info');
define('F_USER_NAME', 						'username');

define('F_STATUS',							'form_status');
define('F_STATUS_SUBMITED',					'submited');
define('F_STATUS_NOT_SUBMITED',				'not_submited');

/* Konstanten fuer die Mantisschnittstelle */
define('F_DIRECTLINK', 'directlink');
define('F_SOURCE', 'source');
define('F_SOURCE_CAT', 'source_cat');
define('F_ENTRY_TEXT', 'entry_text');

define('F_SOURCE_REPORT_ENTRY', 'entryreport');
define('F_SOURCE_ERROR_REPORT', 'errorreport');
define('F_SOURCE_DELETE_ENTRY', 'entrydel');
define('F_SOURCE_GENERAL_QUERY', 'generall');
define('F_SOURCE_FEATURE_REQUEST', 'feature');
define('F_SOURCE_CHANGE_USERNAME', 'changeusername');
define('F_SOURCE_CHANGE_UNI', 'changeuni');
define('F_SOURCE_CHANGE_BIRTHDAY', 'birthday');
define('F_SOURCE_DELETE_ACCOUNT', 'delaccount');
define('F_SOURCE_ADD_ME_TO_GROUP', 'addgroup');
define('F_SOURCE_DELETE_ME_FROM_GROUP', 'removegroup');
define('F_SOURCE_FOUND_GROUP', 'foundgroup');
define('F_SOURCE_MISSING_COURSE', 'missingcourse');
define('F_SOURCE_MISC', 'other');
define('F_SOURCE_UNKNOWN', 'unkown');
define('F_SOURCE_ANWSER', 'user_answer');
define('F_SOURCE_CALENDER', 'calender');
define('F_MANTIS_ID', 'support_id');


?>
