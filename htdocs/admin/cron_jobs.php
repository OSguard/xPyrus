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

# $Id: cron_jobs.php 5807 2008-04-12 21:23:22Z trehn $
# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/admin/cron_jobs.php $

require_once dirname(__FILE__) . '/../conf/config.php';
require_once dirname(__FILE__) . '/../core/database.php';
require_once dirname(__FILE__) . '/../core/session.php';
require_once dirname(__FILE__) . '/../core/constants/value_constants.php';
require_once dirname(__FILE__) . '/../core/models/user/user_warning_model.php';
require_once dirname(__FILE__) . '/../core/models/base/friend_model.php';
require_once dirname(__FILE__) . '/../core/models/base/role_model.php';
require_once dirname(__FILE__) . '/../core/models/pm/pm_entry_model.php';
require_once dirname(__FILE__) . '/../core/views/view_factory.php';
require_once dirname(__FILE__) . '/../core/utils/mailer.php';
require_once dirname(__FILE__) . '/../core/utils/notifier_factory.php';
require_once dirname(__FILE__) . '/../core/boxes/box_controller_factory.php';

require_once dirname(__FILE__) . '/../lang/de.php.inc';

function update_user_online() {
  $DB = Database::getHandle();
  
  $q = 'DELETE FROM ' . DB_SCHEMA . '.user_online
              WHERE online_since + \'' . V_USER_ONLINE_TIMEOUT . ' minutes\'::interval < now()';
  
  $res = $DB->execute($q);
  if (!$res) {
    throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
  }
}

function update_feature_slots() {
  // TODO: define slot steps somewhere else
  $slotSteps = array(
  // diff         actual number
               // 50   (first limit, cf. trigger spread_user_data
     50,       // 100
     100,      // 200
     100,      // 300
     100,      // 400
     100,      // 500
     250,      // 750
     250,      // 1000
    );
  for ($i=0; $i<30; ++$i) {
    // proceed in 500-points-steps
    array_push($slotSteps, 500);
  }
    
  $DB = Database::getHandle();
  
  $q = 'SELECT ' . DB_SCHEMA . '.update_feature_slots(id, ARRAY[' . implode(',', $slotSteps) . ']) 
          FROM ' . DB_SCHEMA . '.users';
  
  $res = $DB->execute($q);
  if (!$res) {
      throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
  }
}

function update_online_activity() {
  $DB = Database::getHandle();
  
  // we assume 5 hours as a good duration to surf daily on unihelp
  define('ASSUMED_DURATION', ((int)(5 * 3600)) . '.0');
  
  $DB->StartTrans();
  // TODO: optimize query, extract the division 
  $q = 'UPDATE ' . DB_SCHEMA . '.users
           SET activity_index = 
                        (
                        ( 2*(online_time[1] - abs(online_time[1]-' . ASSUMED_DURATION . '))
                        + 2*(online_time[2] - abs(online_time[2]-' . ASSUMED_DURATION . '))
                        + 2*(online_time[3] - abs(online_time[3]-' . ASSUMED_DURATION . '))
                        + 2*(online_time[4] - abs(online_time[4]-' . ASSUMED_DURATION . '))
                        + 2*(online_time[5] - abs(online_time[5]-' . ASSUMED_DURATION . '))
                        + 2*(online_time[6] - abs(online_time[6]-' . ASSUMED_DURATION . '))
                        + 2*(online_time[7] - abs(online_time[7]-' . ASSUMED_DURATION . '))
                        +   (online_time[8] - abs(online_time[8]-' . ASSUMED_DURATION . '))
                        +   (online_time[9] - abs(online_time[9]-' . ASSUMED_DURATION . '))
                        +  (online_time[10] - abs(online_time[10]-' . ASSUMED_DURATION . '))
                        ) / (17.0 * 2.0 * ' . ASSUMED_DURATION . ') + 0.5 
                        )
                        
                        *
                        
                        (
                        0.5 * (atan(
                        (
                          2*level_points[1] 
                        + 2*level_points[2]
                        + 2*level_points[3]
                        + 2*level_points[4]
                        + 2*level_points[5]
                        + 2*level_points[6]
                        + 2*level_points[7]
                        +   level_points[8]
                        +   level_points[9]
                        +  level_points[10]
                        ) / 17.0 - 0.7
                        ) + pi() / 2.0)
                        )
         FROM ' . DB_SCHEMA . '.user_stats 
        WHERE ' . DB_SCHEMA . '.users.id = ' . DB_SCHEMA . '.user_stats.user_id';

  $res = $DB->execute($q);
  if (!$res) {
      throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
  }
  
  $q = 'UPDATE ' . DB_SCHEMA . '.user_stats 
           SET online_time[2:10] = online_time[1:9], 
               level_points[2:10] = level_points[1:9]';

  $res = $DB->execute($q);
  if (!$res) {
      throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
  }
  
  $q = 'UPDATE ' . DB_SCHEMA . '.user_stats 
           SET online_time[1] = 0, 
               level_points[1] = 0';

  $res = $DB->execute($q);
  if (!$res) {
      throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
  }
  
  $DB->CompleteTrans();
}

function expire_user_warnings() {
  $DB = Database::getHandle();
  $DB->StartTrans();
  
  $q = 'SELECT id, user_id, 
               declared_until < now() AS expired,
               warning_type
          FROM ' . DB_SCHEMA . '.user_warnings
         WHERE NOT role_corrected
           AND warning_type <> \'g\'';
  $res = $DB->execute($q);
  if (!$res) {
      throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
  }
  if ($res->EOF) {
    $DB->CompleteTrans();
    return;
  }
  
  $warningCard = new UserWarningModel;
  // map warning types to corresponding roles
  $adjointRoles = array($warningCard->TYPE_YELLOW => RoleModel::getRoleByName('card_yellow'),
                        $warningCard->TYPE_YELLOWRED => RoleModel::getRoleByName('card_yellow_red'),
                        $warningCard->TYPE_RED => RoleModel::getRoleByName('card_red'));
  
  // collect ids of all roles that might have to be changed
  $cardRoleIds = array();
  foreach ($adjointRoles as $type => $role) {
    $cardRoleIds[] = $role->id;
  }
  
  // mark the roles that can be expired per user
  $cardIds = array();
  $users = array();
  foreach ($res as $row) {    
    if (Database::convertPostgresBoolean($row['expired'])) {
        if (!array_key_exists($row['user_id'], $users)) {
            $users[$row['user_id']] = array();
        }
        $cardIds[] = $row['id'];
        array_push($users[$row['user_id']], $adjointRoles[$row['warning_type']]->id);
    }
  }
  //var_dump($users);
  
  foreach ($users as $uid => $rolesToDelete) {
      // determine ids of roles which membership can safely be removed for this user
      //$rolesToDelete = array_diff($cardRoleIds, $neededRoles);
      if (count($rolesToDelete) == 0) {
        continue;
      }
      
      // correct role membership
      $q = 'DELETE FROM ' . DB_SCHEMA . '.user_role_membership
             WHERE user_id = ' . $DB->Quote($uid) . '
               AND role_id IN (\'' . implode('\',\'', $rolesToDelete). '\')';
      $res = $DB->execute($q);
      if (!$res) {
          throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
      }	
      
      // enforce rights reload
      $q = 'DELETE FROM ' . DB_SCHEMA . '.user_online
             WHERE user_id = ' . $DB->Quote($uid);
      $res = $DB->execute($q);
      if (!$res) {
          throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
      }
  }
  
  if (count($cardIds) > 0) {
      // set flag on all users that have been worked at
      $q = 'UPDATE ' . DB_SCHEMA . '.user_warnings
               SET role_corrected = \'t\'
             WHERE id IN (\'' . implode('\',\'', $cardIds) . '\')';
      $res = $DB->execute($q);
      if (!$res) {
          throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
      }
  }
  $DB->CompleteTrans();
}

function expire_user_accounts() {
  $DB = Database::getHandle();
  $DB->StartTrans();
  
  $q = 'SELECT user_id, expires
          FROM ' . DB_SCHEMA . '.user_expiration
         WHERE expires <= NOW()';
  $res = $DB->execute($q);
  if (!$res) {
      throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
  }
  $uids = array();
  $expired = array();
  foreach ($res as $k => $row) {
      $uids[] = $row['user_id'];
      $expired[$row['user_id']] = $row['expires'];
  }
  $users = UserProtectedModel::getUsersByIds($uids, null, false);
  foreach ($users as $u) {
      $u->moveToRecycleBin('account expired ' . $expired[$u->id]);
  }
  
  $q = 'DELETE FROM ' . DB_SCHEMA . '.user_expiration
         WHERE user_id IN (' . Database::makeCommaSeparatedString($users, 'id') . ')';
  $res = $DB->execute($q);
  if (!$res) {
      throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
  }
  
  $DB->CompleteTrans();
}

/**
 * sends a birthday reminder as a PM to all friends of users who
 * have birthday in two days
 */
function birthday_reminder() {
    $DB = Database::getHandle();
  
    $DB->StartTrans();
  
    $birthdaysToLookFor = array(0, 1, 2, 3);
    foreach ($birthdaysToLookFor as $birthday) {
        $inDays = time() + $birthday * 86400;
        $u = UserModel::getUsersByBirthday(false, date('j', $inDays), date('n', $inDays));
      
        foreach ($u as $user) {
            $friends = FriendModel::getFriendsByUser($user);
            $realFriends = array();
            foreach ($friends as $f) {
                if (FriendModel::isFriendOf($f, $user)) {
                    $bp = BoxControllerFactory::getBox(array('birthday_personal', 1));
                    $days = $bp->notifyPMBefore($f);
                    if ($days == $birthday) {
                        array_push($realFriends, $f);
                    }
                }
            }
            if (count($realFriends) > 0) {
                $text = ViewFactory::getSmartyView(USER_TEMPLATE_DIR, 'mail/birthday_reminder.tpl');
                $text->assign('friend', $user);
                $text->assign('days', $birthday);
                $success = NotifierFactory::createNotifierByName('pm')->notifyAll($realFriends, CAPTION_BIRTHDAY_REMINDER, $text->fetch());
            
                if (!$success) {
                    Logging::getInstance()->logWarning('sending system pm failed');
                }
            }
        }
    }
  
    $DB->CompleteTrans();
}

/**
 * deletes all PMs who have no recipient
 *
 * this may happen, because a user was deleted
 */
function clear_pms_without_recipient() {
    $DB = Database::getHandle();
  
    // TODO: why do we need the AND clause here?
    $q = 'DELETE FROM ' . DB_SCHEMA . '.pm
           WHERE NOT EXISTS (SELECT * 
                               FROM ' . DB_SCHEMA . '.pm_for_users pmu
                              WHERE pmu.pm_id = ' . DB_SCHEMA . '.pm.id)
             AND (author_has_deleted OR author_int IS NULL)';
    $res = $DB->execute($q);
    if (!$res) {
        throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
    }
}

/*
 * delete all shoutbox entries that are older them 2 days
 */
function delete_old_shout_entries() {
    $DB = Database::getHandle();

	$q = 'DELETE FROM ' . DB_SCHEMA . '.box_shoutbox WHERE entry_time < (NOW()-\'2 days\'::INTERVAL)';
    $res = $DB->execute($q);
    if (!$res) {
        throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
    }		
}

/**
 * updates the ad banners to show
 */
function update_random_banner() {
    $DB = Database::getHandle();
  
    // prepare banner_select table
    $q = 'SELECT ' . DB_SCHEMA . '.update_random_banner(id)
            FROM ' . DB_SCHEMA . '.banner';
  
    $res = $DB->execute($q);
    if (!$res) {
        throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
    }
    
    $DB->StartTrans();
    $q = 'LOCK TABLE ' . DB_SCHEMA . '.banner_show
              IN ACCESS EXCLUSIVE MODE';
    $res = $DB->execute($q);
    if (!$res) {
        throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
    }
    
    // gather banner statistics
    // prepare banner_select table
    $q = 'SELECT banner_id, COUNT(banner_id) AS views
            FROM ' . DB_SCHEMA . '.banner_show
        GROUP BY banner_id';
    $res = $DB->execute($q);
    if (!$res) {
        throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
    }
    // update statistics
    foreach ($res as $row) {
        $q = 'UPDATE ' . DB_SCHEMA . '.banner
                 SET banner_views = banner_views + ' . $row['views'] . '
               WHERE id = ' . $row['banner_id'];
        $_res = $DB->execute($q);
        if (!$_res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
    }
    
    // delete all old entries
    $q = 'DELETE FROM ' . DB_SCHEMA . '.banner_show';
    $res = $DB->execute($q);
    if (!$res) {
        throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
    }
    
    $DB->CompleteTrans();
}

function delete_old_attachments() {
    $DB = Database::getHandle();
    
    // get all files 7 days after their requested deletion
    $q = 'SELECT *
            FROM ' . DB_SCHEMA . '.attachments_old
           WHERE delete_time < CURRENT_TIMESTAMP - interval \'7 days\'';
    $res = $DB->execute($q);
    if (!$res) {
        throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
    }

    // update statistics
    foreach ($res as $row) {
        // determine absolute path to file
        $filePath = $row['path'];
        // some file are relative to htdocs (user pictures &c.)
        if ($filePath[0] == '.') {
            $filePath = BASE . '/' . $filePath;
        }
        $filePath = realpath($filePath);
        
        // start transaction here because
        // deletion of record can be rolled back,
        // whereas unlinking the file can't
        $DB->StartTrans();
        
        $q = 'DELETE FROM ' . DB_SCHEMA . '.attachments_old
                    WHERE id = ' . $row['id'];
        $_res = $DB->execute($q);
        if (!$_res) {
            throw new DBException( Logging::getErrorMessage(DB_SQL_QUERY_FAILED,$DB->ErrorMsg() ) );
        }
        
        # do not try to delete the entry if it is not a file
        # this should also fix the annoying bug where entries only
        # having the BASE as value are tried to remove
        if (is_file($filePath)) {
            if ($filePath !== false && !unlink($filePath)) {
                $DB->FailTrans();
                print 'ERROR: deletion of file \'' . $filePath . '\' (id = ' . $row['id'] . ') failed.' . "\n";
            }
        }
        
        $DB->CompleteTrans();
    }
}

function send_mail() {
    $mails = MailModel::getUnsentMail();
    foreach ($mails as $m) {
        Mailer::sendPreparedMail($m);
    }
}


function generate_compressed_files() {
	include_once dirname(__FILE__) . '/generate_files.php';
}


function weekly() {
    delete_old_attachments();
}

function daily() {
    expire_user_warnings();
    expire_user_accounts();
    update_online_activity();
    birthday_reminder();
    clear_pms_without_recipient();
    update_random_banner();
    delete_old_shout_entries();
}

function hourly() {
    update_feature_slots();
    generate_compressed_files();
}

function quarterhourly() {
    update_user_online();
}

function minutely() {
    send_mail();
}

function test() {
    //print "nothing to do here\n";
}

$valid_arguments = array('minutely' => 1,
                         'quarterhourly' => 1,
                         'hourly' => 1,
                         'daily' => 1,
                         'weekly' => 1,
                         'test' => 1,
                        );

// decide wheter we want to be called via HTTP oder CLI
if (php_sapi_name() == 'cli') {
    if ($_SERVER['argc'] > 1) {
        if (array_key_exists($_SERVER['argv'][1], $valid_arguments)) {
            $_SERVER['argv'][1]();
            exit;
        }
    }
    
    die("usage: " . $_SERVER['argv'][0] . " (weekly|daily|hourly|quarterhourly|minutely)\n");
} else {
    if (!empty($_GET['mod'])) {
        if (array_key_exists($_GET['mod'], $valid_arguments)) {
            $_GET['mod']();
            exit;
        }
        die ('no such mod');
    } 
    
    die('no arguments given.');
}


?>
