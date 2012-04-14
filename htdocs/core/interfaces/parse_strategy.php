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

# $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/core/interfaces/parse_strategy.php $

/**
 * Interface that can be implemented to determine the parsing strategy. E.g.
 * parsing BB-style code, old unihelp code etc.
 *
 * @package Interfaces
 * @version $Id: parse_strategy.php 5807 2008-04-12 21:23:22Z trehn $
 */
interface ParseStrategy {
  /**
    * Parses the string and returns it in internal format.
    * @param string Text to be parsed.
    * @return string String in internal format.
    */
  public function& parse(&$text);
}

?>
