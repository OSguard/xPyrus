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

// $Id: user_protected_model.php 5807 2008-04-12 21:23:22Z trehn $
// $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/models/base/user_protected_model.php $

require_once MODEL_DIR . '/base/user_model.php';
require_once CORE_DIR . '/privacy_context.php';

class UserProtectedModel extends UserModel {
    public function __construct($user) {
        parent::__construct($user);
    }
    public static function getUserById($id, $showValidOnly = true) {
        $user = UserModel::getUserById($id, $showValidOnly);
        if ($user == null) {
            return null;
        }
        return new UserProtectedModel($user);
    }
    public static function getUserByUsername($username, $showValidOnly = true) {
        $user = UserModel::getUserByUsername($username, $showValidOnly);
        if ($user == null) {
            return null;
        }
        return new UserProtectedModel($user);
    }
    public static function getUserByUsernamePassword($username, $password) {
        $user = UserModel::getUserByUsernamePassword($username, $password);
        if ($user == null) {
            return null;
        }
        return new UserProtectedModel($user);
    }
    public static function getUserByUsernameCookie($identifier) {
        $user = UserModel::getUserByUsernameCookie($identifier);
        if ($user == null) {
            return null;
        }
        return new UserProtectedModel($user);
    }
    public static function getUserByRandom() {
        $user = UserModel::getUserByRandom();
        if ($user == null) {
            return null;
        }
        return new UserProtectedModel($user);
    }
    public static function getUserByNewest() {
        $user = UserModel::getUserByNewest();
        if ($user == null) {
            return null;
        }
        return new UserProtectedModel($user);
    }
    public static function getUserByActivationString($activationString) {
        $user = UserModel::getUserByActivationString($activationString);
        if ($user == null) {
            return null;
        }
        return new UserProtectedModel($user);
    }
    public static function getUserByCanvass($canvassCode) {
        $user = UserModel::getUserByCanvass($canvassCode);
        if ($user == null) {
            return null;
        }
        return new UserProtectedModel($user);
    }
    public static function getUsersByBirthday($showInvisible = false, $day = false, $month = false, $year = false) {
        $users = array();
        $_users = UserModel::getUsersByBirthday($showInvisible, $day, $month, $year);
        foreach ($_users as $k => $u) {
            $users[$k] = new UserProtectedModel($u);
        }
        return $users;
    }
    public static function getUsersByIds($ids, $order='', $showValidOnly = true) {
        $users = array();
        $_users = UserModel::getUsersByIds($ids, $order, $showValidOnly);
        foreach ($_users as $k => $u) {
            $users[$k] = new UserProtectedModel($u);
        }
        return $users;
    }
    public static function getUsersByUsernames($usernames, $order='') {
        $users = array();
        $_users = UserModel::getUsersByUsernames($usernames, $order);
        foreach ($_users as $k => $u) {
            $users[$k] = new UserProtectedModel($u);
        }
        return $users;
    }
    public static function getSystemUser() {
        $user = self::getUserById(SYSTEM_USER_ID, false);
        if ($user == null) {
            return null;
        }
        return new UserProtectedModel($user);
    }
    
    public static function searchUser($subString, $limit=30, $showValidOnly=true) {
        $users = array();
        $_users = UserModel::searchUser($subString, $limit, $showValidOnly);
        foreach ($_users as $k => $u) {
            $users[$k] = new UserProtectedModel($u);
        }
        return $users;
    }
    public static function searchUserAdvanced($subString, $studyPathId, $gender, $flirtStatus, $picture, $uni, $order_by, $order, $limit, $offset=0,$showValidOnly=true) {
        $users = array();
        $_users = UserModel::searchUserAdvanced($subString, $studyPathId, $gender, $flirtStatus, $picture, $uni, $order_by, $order, $limit, $offset, $showValidOnly);
        foreach ($_users as $k => $u) {
            $users[$k] = new UserProtectedModel($u);
        }
        return $users;
    }
    
    
    public function hasImICQ() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('instant_messanger')->allowsAccess($pc->getLevel())) {
            return parent::hasImICQ();
        }
        return false;
    }
    public function getImICQ() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('instant_messanger')->allowsAccess($pc->getLevel())) {
            return parent::getImICQ();
        }
        return '';
    }
    public function hasImJabber() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('instant_messanger')->allowsAccess($pc->getLevel())) {
            return parent::hasImJabber();
        }
        return false;
    }
    public function getImJabber() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('instant_messanger')->allowsAccess($pc->getLevel())) {
            return parent::getImJabber();
        }
        return '';
    }
    public function hasImAIM() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('instant_messanger')->allowsAccess($pc->getLevel())) {
            return parent::hasImAIM();
        }
        return false;
    }
    public function getImAIM() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('instant_messanger')->allowsAccess($pc->getLevel())) {
            return parent::getImAIM();
        }
        return '';
    }
    public function hasImMSN() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('instant_messanger')->allowsAccess($pc->getLevel())) {
            return parent::hasImMSN();
        }
        return false;
    }
    public function getImMSN() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('instant_messanger')->allowsAccess($pc->getLevel())) {
            return parent::getImMSN();
        }
        return '';
    }
    public function hasImYahoo() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('instant_messanger')->allowsAccess($pc->getLevel())) {
            return parent::hasImYahoo();
        }
        return false;
    }
    public function getImYahoo() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('instant_messanger')->allowsAccess($pc->getLevel())) {
            return parent::getImYahoo();
        }
        return '';
    }
    public function hasSkype() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('instant_messanger')->allowsAccess($pc->getLevel())) {
            return parent::hasSkype();
        }
        return false;
    }
    public function getSkype() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('instant_messanger')->allowsAccess($pc->getLevel())) {
            return parent::getSkype();
        }
        return '';
    }
    public function hasTelephoneMobil() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('telephone')->allowsAccess($pc->getLevel())) {
            return parent::getTelephoneMobil() != '';
        }
        return false;
    }
    public function getTelephoneMobil() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('telephone')->allowsAccess($pc->getLevel())) {
            return parent::getTelephoneMobil();
        }
        return '';
    }
    
    public function hasStreet() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('address')->allowsAccess($pc->getLevel())) {
            return parent::getStreet() != '';
        }
        return false;
    }
    public function getStreet() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('address')->allowsAccess($pc->getLevel())) {
            return parent::getStreet();
        }
        return '';
    }
    public function hasZipCode() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('address')->allowsAccess($pc->getLevel())) {
            return parent::getZipCode() != '';
        }
        return false;
    }
    public function getZipCode() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('address')->allowsAccess($pc->getLevel())) {
            return parent::getZipCode();
        }
        return '';
    }
    public function hasLocation() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('address')->allowsAccess($pc->getLevel())) {
            return parent::getLocation() != '';
        }
        return false;
    }
    public function getLocation() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('address')->allowsAccess($pc->getLevel())) {
            return parent::getLocation();
        }
        return '';
    }
    
    
    public function hasPublicPGPKey() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('mail_address')->allowsAccess($pc->getLevel())) {
            return parent::hasPublicPGPKey();
        }
        return false;
    }
    public function getPublicPGPKey() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('mail_address')->allowsAccess($pc->getLevel())) {
            return parent::getPublicPGPKey();
        }
        return '';
    }
    public function hasPublicEmail() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('mail_address')->allowsAccess($pc->getLevel())) {
            return parent::getPublicEmail() != '';
        }
        return false;
    }
    public function getPublicEmail() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('mail_address')->allowsAccess($pc->getLevel())) {
            return parent::getPublicEmail();
        }
        return '';
    }
    
    
    public function hasPointsEconomic() {
        $pc = PrivacyContext::getContext();
        $noOneModel = DetailsVisibleModel::getDetailsVisibleByName('no one');
        if ($noOneModel->allowsAccess($pc->getLevel())) {
            // every user has economic points
            return true;
        }
        return false;
    }
    public function getPointsEconomic() {
        $pc = PrivacyContext::getContext();
        $noOneModel = DetailsVisibleModel::getDetailsVisibleByName('no one');
        if ($noOneModel->allowsAccess($pc->getLevel())) {
            return parent::getPointsEconomic();
        }
        return 0;
    }
    
    public function hasAge() {
        $pc = PrivacyContext::getContext();
        // special treatment of privacy level for birthdate
        // see explaination in user profile settings
        $noOneModel = DetailsVisibleModel::getDetailsVisibleByName('no one');
        if (!$this->detailVisible('birthdate')->equals($noOneModel) or
                $this->detailVisible('birthdate')->allowsAccess($pc->getLevel())) {
            // every user has an age
            return true;
        }
        return false;
    }
    public function getAge() {
        $pc = PrivacyContext::getContext();
        // special treatment of privacy level for birthdate
        // see explaination in user profile settings
        // only hide age, if setting is "no one"
        $noOneModel = DetailsVisibleModel::getDetailsVisibleByName('no one');
        if (!$this->detailVisible('birthdate')->equals($noOneModel) or
                $this->detailVisible('birthdate')->allowsAccess($pc->getLevel())) {
            return parent::getAge();
        }
        return 0;
    }
    
    public function hasBirthdate() {
        $pc = PrivacyContext::getContext();
        // special treatment of privacy level for birthdate
        // see explaination in user profile settings
        // in friendlist mode, grant friends and more access to birthdate
        // in not-friendlist mode, show birthdate only to "no one [else but me]"
        $friendlistModel = DetailsVisibleModel::getDetailsVisibleByName('on friendlist');
        $noOneModel = DetailsVisibleModel::getDetailsVisibleByName('no one');
        if (($this->detailVisible('birthdate')->equals($friendlistModel) and
                 $this->detailVisible('birthdate')->allowsAccess($pc->getLevel()))
              or $noOneModel->allowsAccess($pc->getLevel())) {
            // every user has an birthdate
            return true;
        }
        return false;
    }
    public function getBirthdate() {
        $pc = PrivacyContext::getContext();
        // special treatment of privacy level for birthdate
        // see explaination in user profile settings
        // in friendlist mode, grant friends and more access to birthdate
        // in not-friendlist mode, show birthdate only to "no one [else but me]"
        $friendlistModel = DetailsVisibleModel::getDetailsVisibleByName('on friendlist');
        $noOneModel = DetailsVisibleModel::getDetailsVisibleByName('no one');
        if (($this->detailVisible('birthdate')->equals($friendlistModel) and
                 $this->detailVisible('birthdate')->allowsAccess($pc->getLevel()))
              or $noOneModel->allowsAccess($pc->getLevel())) {
            // every user has an birthdate
            return parent::getBirthdate();
        }
        return false;
    }
    
    public function hasFirstName() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('real_name')->allowsAccess($pc->getLevel())) {
            return parent::getFirstName() != '';
        }
        return false;
    }
    public function getFirstName() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('real_name')->allowsAccess($pc->getLevel())) {
            return parent::getFirstName();
        }
        return '';
    }
    public function hasLastName() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('real_name')->allowsAccess($pc->getLevel())) {
            return parent::getLastName() != '';
        }
        return false;
    }
    public function getLastName() {
        $pc = PrivacyContext::getContext();
        if ($this->detailVisible('real_name')->allowsAccess($pc->getLevel())) {
            return parent::getLastName();
        }
        return '';
    }
}

?>
