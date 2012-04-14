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

// $HeadURL:svn://unihelp.de/unihelp_dev/v2/trunk/htdocs/core/models/user/user_data_item_model.php $

/**
 * class representing one single element of a UserDataModel
 * 
 * @author linap
 * @version $Id: user_data_item_model.php 5743 2008-03-25 19:48:14Z ads $
 * @package Models
 * @subpackage User
 */
class UserDataItemModel {
  /**
   * value of item
   * @var mixed
   */
  public $value;
  /**
   * id of item
   * @var int
   */
  public $id;
  /**
   * difference to last read value from DB
   * @var int
   */
  public $delta;
  /**
   * @var string
   * may be set to name of data item
   */
  public $name;
  
  /**
   * constructor
   *
   * @param mixed $value
   * @param int $id
   */
  public function __construct( $value, $id=0 ) {
    $this->value = $value;
    $this->id    = $id;
    $this->delta = 0;
  }
}

?>
