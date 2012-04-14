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
/*
 * $Id: error_page.php 5807 2008-04-12 21:23:22Z trehn $
 * $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/errors/error_page.php $
 * Created on 17.07.2006 by schnueptus
 * sunburner Unihelp.de
 */
 header('Content-Type: text/html; charset=utf-8');
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>UniHelp.de - Fehler</title>
    </head>
<body>
<div style="width: 500px; margin: 0 auto; text-align: center; font-size: 1.2em;">
<br /> <br />
<img src="/template/unihelp_hp/css/images/weblogo.png" alt="UniHelp-Logo" />
<br /> <br />

<b>Something ist wrong.</b>
<br /><br />
<b>Es ist leider ein Fehler aufgetreten.</b>

<br /> <br />

<i><a href="/home">Zur UniHelp-Startseite</a></i>

<br /> <br />
<i><a href="/support">Anfrage bei Problemen</a> mit Bezug auf Error-Code <?php print $errorId; ?></i>

</div>
</body>
</html>
