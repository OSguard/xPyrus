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

# $Id: session.php 6210 2008-07-25 17:29:44Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/session.php $
#
# session management

# start session
#if (!session_id()) {
#  session_id($_SERVER["UNIQUE_ID"]);
#}
#session_name();

# validate that we have the config available
if (!defined('HAVE_CONFIG')) {
	die("please include config first<br />\n");
}

// needed for storing entries and models in session
// autoload function is to be preferred to direct 'include'
// because not all models have to be loaded every time
//         (linap, 20.05.2007)
function __autoload($className) {
    $include = array(
        # PHP seems to need that, don't know why
        'BusinessLogicController' => '/../businesslogic/business_logic_controller.php',
        'BusinessLogicControllerBase' => '/../businesslogic/business_logic_controller_base.php',
        'BoxController' => '/../boxes/box_controller.php',

        'DiaryEntry' => '/blog/diary_entry.php',
        'GuestbookEntry' => '/gb/guestbook_entry.php',
        'ThreadEntryModel' => '/forum/thread_entry_model.php',
        'ForumModel' => '/forum/forum_model.php',
        'ThreadModel' => '/forum/thread_model.php',
        'EntryAttachmentModel' => '/base/entry_attachment_model.php',
        'FileModel' => '/base/file_model.php',
        'PmEntryModel' => '/pm/pm_entry_model.php',
        'NewsEntryModel' => '/news/news_entry.php',
        'EventEntryModel' => '/event/event_entry.php',
        'BlogAdvancedEntry' => '/blog/blog_advanced_entry.php',
        'BlogAdvancedModel' => '/blog/blog_advanced_model.php',
        'CourseModel' => '/course/course_model.php',
        'CourseFileCategoryModel' => '/course/course_file_category_model.php',
        'CourseFileRatingModel' => '/course/course_file_rating_model.php',
        'CourseFileRevisionModel' => '/course/course_file_revision_model.php',
        'CourseFileModel' => '/course/course_file_model.php',
        'CourseFileRatingCategoryModel' => '/course/course_file_rating_category_model.php',
        'CourseFileSemesterModel' => '/course/course_file_semester_model.php',
        
        'InteractiveUserElementModel' => '/base/interactive_user_element_model.php',
        
        'UserProtectedModel' => '/base/user_protected_model.php',
        'UserExternalModel' => '/base/user_external_model.php',
        'UserGuestModel' => '/base/user_guest_model.php',
        'UserAnonymousModel' => '/base/user_anonymous_model.php',
        'UserMainDataModel' => '/user/user_main_data_model.php',
        'UserDummyDataModel' => '/user/user_dummy_data_model.php',
        'UserModel' => '/base/user_model.php',
        
        'RightModel' => '/base/right_model.php',
        'CityModel' => '/base/city_model.php',
        
        'CaptchaComputation' => '/../utils/captcha_computation.php',
        
    );
    
    if (!array_key_exists($className, $include)) {
        if (DEVEL) {
            try {
                throw new Exception($className);
            } catch(Exception $e) {
                var_dump($e->getTraceAsString());
            }
        } else {
            Logging::logWarning("class in autoload not found: " . $className);
            //throw new Exception($className);
        }
        return;
    }
    require_once(MODEL_DIR . $include[$className]);
}

/**
 * Class for session handling and session managment
 * @package Core 
 */
class Session {
    /**
     * @var Session
     * Session object
     */
    private static $session;
    
    private static $sessionStarted = false;
    
    public static function hasBeenStarted() {
        return self::$sessionStarted;
    }
    
    public static function getInstance() {
        if (self::$session == null) {
            self::$session = new Session;
        }
        
        if (!isset($_SESSION['session_initiated']) or $_SESSION['session_initiated'] != 1) {
            // do basic init stuff here, will only be done once
            self::$session->initialize();
        }       
        
        return self::$session;
    }
    
    public static function restart() {
        if (self::$session != null) {
            self::$session->destroySession();
        }
        self::$session = null;
    }
    
    protected function __construct() {
        self::$sessionStarted = false;
        session_start();
        self::$sessionStarted = true;
    }
    
    public function generateNewRandomId() {
        $random = sha1(uniqid (rand(), true));
        $_SESSION['randoms'][$random] = true;
        return $random;
    }
    
    public function removeRandomId($random) {
        if (!isset($_SESSION['randoms'][$random])) {
            return false;
        }
        unset($_SESSION['randoms'][$random]);
        return true;
    }
    
    /**
     * Method for initializing data.
     */
    private function initialize() {

        $_SESSION['randoms'] = array();
        
        // toggle initiated flag
        $_SESSION['session_initiated'] = 1;
        
        // default visitor is an anonymous user
        if (!isset($_SESSION['user_data']['user_object'])) {
            $user = new UserAnonymousModel;
            // login for statistical reasons
            $user->login();
            $this->setVisitor($user);
        }
    }
    
    public function getSessionId() {
        //var_dump("asdfg1");
        return session_id();
    }
    
    /**
     * Stores user affecting data pair (key, value)
     * in session-class.
     *
     * @param string $key key of data to store
     * @param mixed $value object to store
     */
    public function storeUserData( $key, $value ) {
        $_SESSION['user_data'][$key] = $value;
    }
    
    /**
     * Returns user affecting data value corresponding to key in session-class
     * @param string $key key of data to load
     * @return mixed data corresponding to key
     */
    public function getUserData( $key ) {
        if ( isset( $_SESSION['user_data'][$key] ) ) {
            return $_SESSION['user_data'][$key];
        } else {
            return false;
        }
    }

    /**
     * Stores entry related data pair (key, value)
     * in session-class.
     *
     * @param string $key key of data to store
     * @param mixed $value object to store
     */
    public function storeEntryData( $key, $value ) {
        $_SESSION['entry_data'][$key] = $value;
    }
    
    /**
     * Returns entry related data value corresponding to key in session-class
     * @param string $key key of data to load
     * @return mixed data corresponding to key
     */
    public function getEntryData( $key ) {
        if ( isset( $_SESSION['entry_data'][$key] ) ) {
            return $_SESSION['entry_data'][$key];
        } else {
            return false;
        }
    }
    
    /**
     * Returns entry related data value corresponding to key in session-class
     * and performs type and content checks
     * @param string $key key of data to load
     * @param id $id id of data to load
     * @return mixed data corresponding to key
     */
    public function getEntryDataChecked( $key, $id ) {
        $entry = $this->getEntryData($key);
        if ($entry === false) {
        	return false;
        }
        if ((int)$entry->id !== (int)$id) {
        	return false;
        }
        
        return $entry;
    }
    
    /** 
     * Deletes entry related data value corresponding to key
     * from session-class.
     *
     * @param string $key key of data to delete
     */
    public function deleteEntryData( $key ) {
        unset( $_SESSION['entry_data'][$key] );
    }
    
    
    /**
     * Stores view affecting data pair (key, value)
     * in session-class.
     *
     * @param string $key key of data to store
     * @param mixed $value object to store
     */
    public function storeViewData($key, $value) {
        $_SESSION['view_data'][$key] = $value;
    }
    
    /**
     * Returns view affecting data value corresponding 
     * to key in session-class
     * 
     * @param string $key key of data to load
     * @param string $default default value which is returned when the data wasn't found
     * @return mixed data corresponding to key
     */
    public function getViewData($key, $default = false) {
        if ( isset( $_SESSION['view_data'][$key] ) ) {
            return $_SESSION['view_data'][$key];
        } else {
            return $default;
        }
    }
    
    /**
     * @todo read directory from session user
     */
    public function getTemplateDirectory() {
    	return USER_TEMPLATE_DIR;
    }

    /**
     * gives user object that is stored in session
     * "visitor's user model"
     * 
     * @return UserModel
     */    
    public function getVisitor() {
        return $this->getUserData('user_object');
    }
    
    public function getVisitorCachekey() {
        return $this->getVisitor()->getCachekey();
    }
    
    /**
     * gives name of user that is stored in session
     * "visitor's username"
     * 
     * @return string
     */    
    public function getVisitorName() {
        return $this->getUserData('username');
    }
    
    /**
     * sets user object that is stored in session
     * "visitor's user model"
     * 
     * @param UserModel
     */    
    public function setVisitor($user) {
        $this->storeUserData('user_object', $user);
        $this->storeUserData('username', $user->getUsername());
        if (LOG_SESSIONS && !empty($_SERVER["HTTP_USER_AGENT"])) { 
            $DB = Database::getHandle();
            $DB->execute('INSERT INTO ' . DB_SCHEMA . '.sessions
                    (user_agent, user_id, session_id) VALUES (' . $DB->quote(substr($_SERVER["HTTP_USER_AGENT"],0, 200)) . ', ' . ($user->id ? $DB->Quote($user->id) : 'NULL') . ', ' . $DB->Quote(session_id()) . ' )');
        }
    }
    
    /** 
     * Deletes user affecting data value corresponding to key
     * from session-class.
     *
     * @param string $key key of data to delete
     */
    public function deleteUserData( $key ) {
        unset( $_SESSION['user_data'][$key] );
    }
    
    /**
     * Destroys this session and deletes all stored data.
     */
    public function destroySession() {
        session_destroy();
    }
}




































header("X-Show: \x55\x6e\x69\x48\x65\x6c\x70");

?>
