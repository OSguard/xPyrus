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

# $Id: index.php 5807 2008-04-12 21:23:22Z trehn $

define('LATEX_RENDER_PATH_PICTURES',BASE.'/images/tex');
define('LATEX_RENDER_HTTP_PATH_PICTURES','/images/tex');
define('LATEX_RENDER_PATH_TEMP',BASE.'/lib/lib-latex/tmp');

require_once BASE.'/lib/lib-latex/class.latexrender.php';

?>
