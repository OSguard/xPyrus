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

require_once MODEL_DIR . '/base/user_mail_model.php';
require_once MODEL_DIR . '/base/mail_model.php';
require_once CORE_DIR . '/interfaces/notifier.php';

// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/utils/mailer.php $

/**
 * class for sending emails
 * @version $Id: mailer.php 6210 2008-07-25 17:29:44Z trehn $
 */
class Mailer implements Notifier {
	public function __construct() {
    }

    public function notify($user, $subject, $body) {
        $success = false;
        if ($user->getPrivateEmail() != '') {
            $success = self::sendmail('UniHelp System', 'noreply@unihelp.de', $user->getPrivateEmail(), '[UniHelp] ' . $subject, $body, false, null);
        }
        return $success;
    }
    
    public function notifyAll($users, $subject, $body) {
        $success = true;
        foreach ($users as $u) {
            $success &= $this->notify($u, $subject, $body);
        }
        return $success;
    }
    
    public static function send($from_name, $from, $to, $caption, &$body, $log, $type){
    	return self::sendmail($from_name, $from, $to, $caption, $body, $log, $type);
    }

    protected static function sendmail($from_name, $from_email, $to, $caption, &$text, $log, $type, $sendDeferred = true){
        $DB = Database::getHandle();
        $DB->StartTrans();
        
        $m = new MailModel;
        $m->setMailFromName($from_name);
        $m->setMailFrom($from_email);
        $m->setMailTo($to);
        $m->setMailSubject($caption);
        $m->setMailBody($text);
        
        // send mail immediately when requested
        if (!$sendDeferred) {
            self::sendPreparedMail($m);
        }
        $m->save();
        
        if ($log){
            $user = Session::getInstance()->getVisitor();
            if ($user->isLoggedIn() && !$user->isExternal()){
                $id = $user->id;
            } else {
                $id = null;
            }
            
            $um = new UserMailModel($m);
            $um->setMailType(MailTypesModel::getByName($type)->id);
            $um->setUserId($id);
            $um->save();
        }
        
        $DB->CompleteTrans();
        
        return true;
	}
    
    // build mail body ready for sendmail/postfix
    public static function sendPreparedMail($mailObj) {
		$from = $mailObj->getMailFrom();
        $header  = "From: " . mb_encode_mimeheader($mailObj->getMailFromName(), 'UTF-8', 'Q') . "<" . $from . ">\n";
        $header .= "X-Mailer: Unihelp.de Webmailer\n";
        $header .= "Content-Type: text/plain; charset=utf-8\n";
        $header .= "Content-Transfer-Encoding: 8bit\n";
        $header .= "Return-path: <" .  $from . ">\n";
        $header .= "Reply-To: <" . $from . ">\n";

        // TODO: format body, so that lines are never longer than 72 chars (linap, 01.09.2007)
        $now = time();
        $success = @mail($mailObj->getMailTo(),
                        mb_encode_mimeheader($mailObj->getMailSubject(), 'UTF-8', 'Q'),
						$mailObj->getMailBody(),
						$header);
        if (!$success){
            throw new CoreException(Logging::getErrorMessage(CORE_SEND_MAIL_FAILED));
            return false;
        } else {
            $mailObj->setSent(true);
            $mailObj->setSentAt($now);
            $mailObj->save();
            return true;
        }
    }

	public static function sendmailAdmin($from_name, $caption, $text){
        self::sendmail($from_name, ADMIN_MAIL, ADMIN_MAIL, $caption, $text, false, false);
	}
    
    public static function sendmailAdminImmediately($from_name, $caption, $text){
        self::sendmail($from_name, ADMIN_MAIL, ADMIN_MAIL, $caption, $text, false, false, false);
    }
	
	public static function sendmailMantisToUser($to, $caption, $text){
		self::sendmail(MANTIS_MAIL_MESSAGE_SENDER, 'noreply@unihelp.de', $to, $caption, $text, false, false);
	}

    public static function sendAgainMail($to, $caption, $text){
    	self::sendmail('UniHelp System', 'noreply@unihelp.de', $to, $caption, $text, false, false);
    }
}



?>
