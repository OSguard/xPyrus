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

// $Id: box_controller_factory.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/boxes/box_controller_factory.php $

class BoxControllerFactory {
	public static function getBox($box) {
        switch ($box[0]) {
        case 'user_login':
            return self::getUserLoginBox($box[1]);
		case 'birthday':
            return self::getBirthdayBox($box[1]);
        case 'birthday_personal':
            return self::getBirthdayPersonalBox($box[1]);    
        case 'random_userpic':
            return self::getRandomUserpicBox($box[1]);
        case 'user_online':
            return self::getUserOnlineBox($box[1]);
        case 'user_search':
            return self::getUserSearchBox($box[1]);
        case 'friendslist':
            return self::getFriendslistBox($box[1]);
        case 'courses':
            return self::getCoursesBox($box[1]);
        case 'courses_files':
            return self::getCoursesFilesBox($box[1]);   
        case 'shoutbox':
            return self::getShoutbox($box[1]);    
        case 'wetter_com':
            return self::getWetterComBox($box[1]);
        case 'blog':
            return self::getBlogBox($box[1]);       
        default:
            throw new NotImplementedException('selected box ' . $box[0] . ' does not exist');
		}
	}
    
    private static function getBirthdayBox($instance) {
    	include_once CORE_DIR . '/boxes/birthday_box.php';
        return new BirthdayBox($instance);
    }
    
    private static function getBirthdayPersonalBox($instance) {
        include_once CORE_DIR . '/boxes/birthday_personal_box.php';
        return new BirthdayPersonalBox($instance);
    }
    
    private static function getBlogBox($instance) {
        include_once CORE_DIR . '/boxes/blog_box.php';
        return new BlogBox($instance);
    }
    
    private static function getFriendslistBox($instance) {
        include_once CORE_DIR . '/boxes/friendslist_box.php';
        return new FriendslistBox($instance);
    }
    
    private static function getCoursesBox($instance) {
        include_once CORE_DIR . '/boxes/courses_box.php';
        return new CoursesBox($instance);
    }
    
    private static function getCoursesFilesBox($instance) {
        include_once CORE_DIR . '/boxes/courses_files_box.php';
        return new CoursesFilesBox($instance);
    }
    
    private static function getRandomUserpicBox($instance) {
        include_once CORE_DIR . '/boxes/random_userpic_box.php';
        return new RandomUserpicBox($instance);
    }
    
    private static function getUserOnlineBox($instance) {
        include_once CORE_DIR . '/boxes/user_online_box.php';
        return new UserOnlineBox($instance);
    }
    
    private static function getUserLoginBox($instance) {
        include_once CORE_DIR . '/boxes/user_login_box.php';
        return new UserLoginBox($instance);
    }
    
    private static function getUserSearchBox($instance) {
        include_once CORE_DIR . '/boxes/user_search_box.php';
        return new UserSearchBox($instance);
    }
    
    private static function getShoutbox($instance) {
        include_once CORE_DIR . '/boxes/shoutbox.php';
        return new Shoutbox($instance);
    }
    
    private static function getWetterComBox($instance) {
        include_once CORE_DIR . '/boxes/wetter_com_box.php';
        return new WetterComBox($instance);
    }
}
?>
