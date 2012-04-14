<?php

// $Id: en.php.inc 6210 2008-07-25 17:29:44Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/lang/en.php.inc $

define('WORD_YES','yes');
define('WORD_NO','no');
define('WORD_UNKOWN','unknown');
define('WORD_EMAIL','email');

define('ERR_FUNCTION_NOT_AVAILABLE', 'Sorry, this feature is not available yet.');
define('ERR_NO_USER', 'We coud\'nt find any user with this name.');
define('ERR_NO_LOGGIN', 'This feature is available for registered users only.');
define('ERR_NO_EXTERN', 'This feature is available for registered users of this UniHelp location only.');
define('ERR_NO_GROUP', 'This organization doesn\'t exist.');
define('ERR_NO_COURSE', 'No course was choose or this course doesn\'t exist.');
define('ERR_NO_ENTRY', 'No entry was choosen or the entry doesn\'t exist.');
define('ERR_NO_TAG', 'No date was choosen or the date doesn\'t exist.');
define('ERR_NO_EVENT', 'No event was choosen or the event does\'nt exist.');
define('ERR_NO_NEWS', 'No news were choosen or the news don\'t exist.');
define('ERR_NO_ATTACHMENT', 'Your attachment wasn\'t found.');
define('ERR_NO_FILE', 'The file wasn\'t found.');
define('ERR_NO_STUDY_PATH', 'No course of studies was given.');

define('ERR_COURSE_DUPLICATE_FILE', 'Either you or any other user has saved this document before.');
define('ERR_FORM_NOT_VALID', 'Your information are not sufficient to run the admired task. The marked fields are not or not correct duly completed.');
define('ERR_COURSE_NO_FILE', 'You didn\'t mentioned a file or the file is empty.');
define('ERR_COURSE_FILE_NOT_DOWNLOADED', 'You haven\'t downloaded the file until now.');
define('ERR_COURSE_LARGE_FILE', 'Your file has reached the size limit.');
define('ERR_COURSE_DUAL_RATE', 'You may rate any file only once.');
define('ERR_COURSE_FILE_TOO_EXPENSIVE', 'Sorry, You\'re score is not sufficient to download this file.');

define('ERR_ATTACHMENT', 'Your attachment has reached the size limit.');
define('ERR_ATTACHMENT_IMAGE', 'Your attachment is no valid picture file.');
define('ERR_PICTURE_MIMETYPE', 'The uploaded file is not an image.');
define('ERR_NEWS_CAPTION_SHORT', 'The heading is too long.');
define('ERR_NEWS_START_DATE', 'The initial date is empty or elapsed.');
define('ERR_NEWS_END_DATE', 'The finish date is empty or elapsed');

define('ERR_DIARY_NO_TEXT', 'The diary entry must not be empty, e.g. must not be consist of attachments only.');
define('ERR_GUESTBOOK_NO_TEXT', 'The guestbook entry must not be empty, e.g. must not be consist of attachments only.');

define('ERR_NO_ENTRY_TO_REPORT', 'There is no entry to report.');

define('ERR_ON_IGNORELIST', 'You are on the black list of this user.');

define('ERR_ENTRY_TOO_LONG', 'Your text is too large.');
define('ERR_ENTRY_TITLE_TOO_LONG', 'Your subject line is too large.');
define('ERR_INVALID_RANDID', 'Importent information are missing and the entry could not be handled.');

define('ERR_PM_NO_RESIVERS', 'No valid recipients were found.');
define('ERR_PM_POINTS', 'Your score is not sufficient to send this pm.');

define('ERR_INSUFFICIENT_RIGHTS', 'Your rights are not sufficient for this task.');

define('ERR_FORUM_POINTS', 'Your score is not sufficient to post anonymous.');
define('ERR_FORUM_SHOW', 'No board is choosen.');
define('ERR_FORUM_EXIST', 'The board doesn\'t exist.');
define('ERR_FORUM_LOGIN', 'Only registered user may enter this board.');
define('ERR_FORUM_GROUP', 'Only members of the organization may enter this board.');

define('ERR_FORUM_THREAD_SHOW', 'No thread is choosen.');
define('ERR_FORUM_THREAD_EDIT', 'The information are invalid, the changes are not saved.');
define('ERR_FORUM_THREAD_LINK', 'No thread linked with this moved thread was found.');
define('ERR_FORUM_THREAD_CAPTION', 'The caption of the thread is not valid.');
define('ERR_FORUM_THREAD_RATING_USER', 'No user is chooser or the user doesn\'t exist.');
define('ERR_FORUM_THREAD_RATING_OWN', 'You\'re evil, don\'t rate yourself!');
define('ERR_FORUM_THREAD_RATING_NOWRITE', 'You\'re evil, first write en entry, after that you may rate others!');
define('ERR_FORUM_THREAD_RATING_NOAUTHOR', 'You\'re evil, first write to this user, after that you may rate him or her.!');
define('ERR_FORUM_THREAD_RATING_NOVALUE', 'No rating could be found.');
define('ERR_FORUM_THREAD_RATING_REPEAT', 'You may rate only once.');
define('ERR_FORUM_THREAD_DEL_FIRST_ENTRY', 'The first entry must not be deleted; you may close or hide the thread.');

define('ERR_FORUM_ADD', 'The information are not valid, the board is not established.');
define('ERR_FORUM_EDIT', 'The information are not valid, the changes were not saved.');
define('ERR_FORUM_NO_TEMPLATE', 'Choose a template please!');
define('ERR_CATEGORY_SHOW', 'There is no category choosen.');
define('ERR_CATEGORY_ADD', 'The information are not valid, no category is set up.');
define('ERR_CATEGORY_EDIT', 'The information are not valid, the changes were not saved.');

define('ERR_GROUP_EDIT', 'The name and/or the description of the organization are invalid.');
define('ERR_GROUP_MEMBERSHIP', 'You are not a member of this organization.');

define('ERR_BANNER_ADMIN', 'The input is not valid, the banner will not be uploaded.');
define('ERR_BANNER_ADMIN_STARTDATE', 'The initial date is invalid.');
define('ERR_BANNER_ADMIN_ENDDATE', 'The finish date is invalid');
define('ERR_BANNER_ADMIN_FILE', 'The file could not be saved, the file might be too large.');
define('ERR_BANNER_ADMIN_NO_FILE', 'No image was uploaded.');
define('ERR_BANNER_INSUFFICIENT_INFORMATION', 'The information to create an entry were not sufficient.');

define('ERR_TAG_ADMIN', 'Invalid tag.');
define('ERR_ROLE_NOT_EXISTING_ADMIN', 'role does not exist.');
define('ERR_USER_NOT_GIVEN_ADMIN', 'No user specified.');
define('ERR_ROLE_NOT_GIVEN_ADMIN', 'No role specified.');
define('ERR_GROUP_NOT_GIVEN_ADMIN', 'No  organization specified.');
define('ERR_ADD_OR_DEL_ADMIN', 'Choose between the options delete or add.');
define('ERR_NO_NAME_OR_DESCRIPTION', 'Name and/or description are missing.');
define('ERR_INVALID_BIRTHDATE', 'Date of birth is invalid.');

define('ERR_SEARCH', 'No search parameters were found.');

define('ERR_USER_REGISTRATION_NONMATCHING_UNIMAIL', 'Your email address don\'t match your university or school.');
// TODO: use rewrite_ - function here for URL
define('ERR_USER_REGISTRATION_EXISTING_UNIMAIL', 'You are or have been already a registered user of UniHelp. You may ask our <a href="/support">support team</a> :-).');
define('ERR_USER_REGISTRATION_INVALID_USERNAME', 'The user name contains invalid characters.');
define('ERR_USER_REGISTRATION_EXISTING_USERNAME', 'The user name is already in use.');
define('ERR_USER_REGISTRATION_INVALID_PRIVATE_EMAIL', 'Your email address is invalid.');
define('ERR_USER_REGISTRATION_INVALID_PASSWORD', 'Your password is empty or contains blank spaces.');
define('ERR_USER_REGISTRATION_NONMATCHING_PASSWORD', 'The passwords do not correspond.');

define('ERR_USER_CANVASS_LOGOFF','You need to be registered to recommend UniHelp to others.');
define('ERR_USER_CANVASS_NAME','Enter a name please.');
define('ERR_USER_CANVASS_EMAIL','Enter a valid email address.');

define('ERR_FEATURES_TOOMANY', 'You choosed more features than allowed.');
define('ERR_FEATURE_NOT_EXISTING', 'This feature doesn\'t exist.');

define('ERR_FRIENDLIST_SELF', 'Are you narcissistic?');

define('ERR_USER_INVALID_PASSWORD', 'The password is invalid. It has to consits of at least 4 characters and must not contain any blank spaces.');
define('ERR_INVALID_PASSWORD', 'Invalid password, the task is aborted.');
define('ERR_INVALID_TERMS_OF_USE', 'Please accept the terms of user.');


define('ERR_BLOG_DOESNT_EXIST', 'This weblog doesn\'t exist in UniHelp.');
define('ERR_BLOG_EXISTS', 'You have already a weblog in UniHelp.');

define('ERR_USERNAME_TOO_SHORT', 'Not enough characters entered. A user name must consist of at least 3 characters.');

define('ERR_ERROR_OCCURED', 'An error occured.');

define('ENTRY_SCRIPSIT', 'wrote');
define('ENTRY_NO_SUBJECT', 'no subject');

define('COLOR_RED','red');
define('COLOR_YELLOW','yellow');
define('COLOR_GREEN', 'green');


// need better slang here
define('STATUS_RED', 'Spoken for');
define('STATUS_YELLOW', 'possibly to convince');
define('STATUS_GREEN', 'I\'m looking for adventure');
define('STATUS_NONE', 'It\'s a secret');

define('CAPTION_BIRTHDAY_REMINDER', 'Birthday reminder');
define('CAPTION_USER_WARNING', 'Warning');
define('CAPTION_GROUP_APPLICATION', 'Admission into an organization');
define('CAPTION_BLOG_NEW_COMMENT', 'New comment to weblog');
define('CAPTION_WELCOME_NEW', 'Welcome at UniHelp.de');

define('SYSTEM_USER_NAME', 'UniHelp System');
define('SYSTEM_USER_LINK', 'go to system site');

#MediaCneter
define('MC_ROOT_DIR_NAME', 'My documents');
define('MC_ROOT_DIR_DESC', 'All uploaded documents');


#Mail
define('MANTIS_MAIL_MESSAGE_HEADER', 'Hi,\n\n thank you for your message. We will handle your request as soon as possible under the reference number ');
define('MANTIS_MAIL_MESSAGE_REQUEST', 'For further request please provide this number .');
define('MANTIS_MAIL_MESSAGE_FOOTER', "Best Regards\n\n UniHelp e. V.\n\n-- \n This mail was generated automatically, please do not answer, use the support instead.");
define('MANTIS_MAIL_MESSAGE_CAPTION', 'Your request to UniHelp.de');
define('MANTIS_MAIL_MESSAGE_SENDER', 'Contact form of UniHelp.de');

define('MANTIS_EMAIL_USE_NOTICE','only if important, use in general PM');

define('SUPPORT_MAIL', 'UniHelp Support');

define('CAPTCHA_CALCULATE', 'Please calculate: ');

define('DEFAULT_GROUP', 'Organization');


define('BLOGOSPHERE_TITLE', 'UniHelp.de - Blogosphere');
define('BLOGOSPHERE_DESCRIPTION', 'All weblogs in UniHelp.de');

# navigation
// IMPORTANT (linap, 10.05.2007): do _not_ name the constants hierarchically by the place
// where it ought to be called in our rewrite structure. It is meant as an abstraction!
define('NAME_HOME', 'Homepage');
define('NAME_GROUPS', 'Organizations');
define('NAME_PM_INBOX', 'Message box');
define('NAME_PM_OUTBOX', 'Message box&mdash;sent messages');
define('NAME_PM_COMPOSE', 'Compose message');

define('NAME_NEWS_ARCHIEV', 'Announcements&mdash;archive');
define('NAME_NEWS_ADD', 'Write an announcement');
define('NAME_NEWS_EDIT', 'Edit an annpouncement');
define('NAME_NEWS_DEL', 'Delete an announcement');
define('NAME_NEWS_RSS', 'Announcements of UniHelp.de');

define('NAME_ENTRY_DEL', 'entry delete');
define('NAME_ENTRY_DEL_FROM', 'entry delete from');
define('NAME_FORUM_DEL','forum delete');
define('NAME_CATEGORY_DEL','Categorie delöete');
define('NAME_AWARD_DEL','award delete');
define('NAME_AWARD_RANG_DEL','delete rang');
define('NAME_USERS_DEL','delete User');
define('NAME_ROLE_DEL','delete role');
define('NAME_GROUP_DEL','delete organisation');
define('NAME_FILE_DEL', 'delete file');
define('NAME_EVENT_DEL_FROM','delete event from');

define('NAME_EVENTS', 'campus calender');
define('NAME_EVENTS_FEED', 'campus calender');

define('NAME_FORUM_ALL', 'UniHelp Boards');
define('NAME_FORUM_LATEST', 'Last entries');
define('NAME_FORUM_SEARCH', 'Board search');
define('NAME_FORUM_LATEST_RSS', 'Last board entries of UniHelp');
define('NAME_FORUM_VIRTUAL', 'Virtuel board');
define('NAME_FORUM_HISTORY', 'History of entry');
define('NAME_FORUM_THREAD_DEL','Thread delete');

define('NAME_USER_PROFILE', 'Account settings');
define('NAME_USER_DELETE', 'Delete account');
define('NAME_USER_NEW', 'register');
define('NAME_USER_ACTIVATE', 'Activate');
define('NAME_USER_PASSWORD_LOST', 'Password lost?');
define('NAME_USER_SEARCH', 'User search');
define('NAME_USER_CANVASS', 'User canvass');

define('NAME_REVERSE_FRIENDLIST', 'reverse friendlist');

define('NAME_HELP', 'Help');
define('NAME_FAQ', 'Frequently asked questions');
define('NAME_FORMATCODE', 'Format code');
define('NAME_SUPPORT', 'request for support');

define('NAME_STUDIES', 'Studies');
define('NAME_COURSE', 'Course');
define('NAME_COURSE_FILE', 'Document');
define('NAME_COURSE_FILES', 'Documents');
define('NAME_COURSE_FILE_LATEST', 'Last documents');
define('NAME_COURSE_FILE_LATEST_RSS', 'Last course files of UniHelp');

define('NAME_TERMS_OF_USE', 'Terms of use');
define('NAME_PRIVACY', 'Privacy');
define('NAME_IMPRINT', 'Imprint');
define('NAME_TOOLBAR', 'UniHelp toolbar');

define('NAME_BLOGOSPHERE', 'Blogosphere');
define('NAME_COMMENTS', 'Comments');

define('NAME_APPLICATION', 'Apply');
define('NAME_LEAVE', 'Leave');

define('NAME_RELOAD', 'reload');
define('NAME_EDIT', 'Edit');
define('NAME_DELETE', 'Delete');
define('QUOTE_LEFT', '“');
define('QUOTE_RIGHT', '”');
define('NAME_INVISIBLE','invisible');

define('NAME_DATE_YESTERDAY', 'yesterday');
define('NAME_DATE_TODAY', 'today');
define('NAME_DATE_TOMORROW', 'tomorrow');

define('NAME_SOCCER_BET', 'Soccer Betting');
define('NAME_SOCCER_BET_RANKING', 'Ranking');

define('LINK_USER_PAGE', 'go to userpage from ');

define('DO_ACTION_FORUM_THREAD_DEL','This thread will be deleted!');
define('DO_ACTION_FORUM_ENTRY_DEL','The Entry of the thread will be deleted forever!');
define('DO_ACTION_FORUM_DEL','All threads and entries of this forum will removed!');
define('DO_ACTION_CATEGORY_DEL','ALL entries, threads and fora of this category will removed and can not restore');
define('DO_ACTION_AWARD_DEL','This award will be deleted an can not restore');
define('DO_ACTION_AWARD_RANG_DEL','This rang will be deleted an can not restore');
define('DO_ACTION_USERS_DEL','This user will be deleted an can not restore!');
define('DO_ACTION_ROLE_DEL','This role will be deleted an can not restore an all user subscripte lost her rights related!');
define('DO_ACTION_GROUP_DEL','organisation will be deleted an can not restore and user loses memberhsip!');
define('DO_ACTION_FILE_DEL','file will be deleted an can not restore!');
define('DO_ACTION_NEWS_DEL','news will be deleted an can not restore!');
define('DO_ACTION_EVENT_DEL','event will be deleted an can not restore!');

# translating some DB entities
function translate_privacy($name) {
    switch ($name) {
    case 'no one': return 'no one';
    case 'all': return 'everybody';
    case 'logged in': return 'registered users';
    case 'group': return 'organization members';
    case 'on friendlist': return 'on friendlist';
    }
    return '';
}
function translate_box($name) {
    switch ($name) {
    case 'friendslist': return 'friendlist';
    case 'birthday': return 'Birthdays';
    case 'shoutbox': return 'Shoutbox';
    case 'courses': return 'Courses';
    case 'courses_files': return 'Documents';
    case 'user_search': return 'Search';
    case 'user_online': return 'User online';
    case 'blog': return 'Blogosphere';
    }
    // return original text here is appropiate here
    // because somebody could forget to insert translation here for new boxes ...
    return $name;
}
function translate_rating_cat($name) {
    switch ($name) {
    case 'helpful': return 'helpfulness';
    case 'scientific level': return 'scientific level';
    case 'document description': return 'document description';
    case 'freetext': return 'remarks';
    }
    return '';
}

function translate_rights($name) {
    switch ($name) {
    case 'BLOG_FILTER': return 'diary filter';
    case 'FEATURE_BOX_REARRANGEMENT': return 'rearrange boxes';
    case 'FEATURE_REVERSE_FRIENDLIST': return 'reverse friendlist';
    case 'FEATURE_SMALLWORLD': return 'smallworld';
    case 'FRIENDLIST_EXTENDED_CATEGORIES': return 'extended friendlist';
    case 'GB_ADVANCED_STATS': return 'advanced guestbook statistics';
    case 'GB_ENTRY_COMMENT': return 'comment on guestbook entries';
    case 'GB_FILTER': return 'filter guestbook';
    case 'PM_ADD_ATTACHMENT': return 'PM /w attachments';
    case 'PM_SENDTO_FRIENDS': return 'Send PM to friends';
    }
    return $name;
}

function translate_nationality($nat) {
    if ($nat == 'unknown') {
        return 'unknown';
    }
    return $nat;
}

function translate_mantis($text){
	switch ($text) {
	case F_SOURCE_REPORT_ENTRY: return 'report entry';
    case F_SOURCE_ERROR_REPORT: return 'report error';
    case F_SOURCE_DELETE_ENTRY: return 'ask for cancellation';
    case F_SOURCE_GENERAL_QUERY: return 'general request';
    case F_SOURCE_FEATURE_REQUEST: return 'feature request';
    case F_SOURCE_CHANGE_USERNAME: return 'change username';
    case F_SOURCE_CHANGE_UNI: return 'change university';
    case F_SOURCE_CHANGE_BIRTHDAY: return 'change birthday';
    case F_SOURCE_DELETE_ACCOUNT: return 'delete account';
    case F_SOURCE_ADD_ME_TO_GROUP: return 'join organization';
    case F_SOURCE_DELETE_ME_FROM_GROUP: return 'leave organization';
    case F_SOURCE_FOUND_GROUP: return 'create new organization';
    case F_SOURCE_MISSING_COURSE: return 'report missing course';
    case F_SOURCE_MISC: return 'something else';
    case F_SOURCE_UNKNOWN: return 'unknown';
	case F_SOURCE_CALENDER: return 'calender';	
	}
    return $text;
}

function genitiv($word) {
    if ($word[strlen($word)-1] == 's') {
        return $word . '\''; // it could also be \'s, both is correct James' and James's
    }
    // no Deppenapostroph here---but in Great Britain there are a lot of deppen :-)
    return $word . '\'s';
}

function course_rating_desc($rating){
	switch ($rating) {
		case 6: return 'excellent';
        case 5: return 'very good';
        case 4: return 'good';
        case 3: return 'fair to middling';
        case 2: return 'poor';
        case 1: return 'worthless';
	}
    return $rating;
}

?>
