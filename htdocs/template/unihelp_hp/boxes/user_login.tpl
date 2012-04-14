{*

xPyrus - Framework for Community and knowledge exchange
Copyright (C) 2003-2008 UniHelp e.V., Germany

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, only version 3 of the
License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program. If not, see http://www.gnu.org/licenses/agpl.txt

*}{* $Id: user_login.tpl 5895 2008-05-03 15:38:20Z schnueptus $ *}
<div class="box">
<h3>Anmeldung</h3>
{* try to reduce multiple ampersands created by module-minimize etc. functions *}
{*{assign var="url" value=$smarty.server.SCRIPT_NAME|cat:"?"|cat:$smarty.server.QUERY_STRING|regex_replace:"/minimize=[^&]*/":""|regex_replace:"/&[&]+/":"&"|escape:"html"}*}
{* {assign var="url" value=$smarty.server.SCRIPT_NAME|cat:"?dest=box&bname=user_login&method=login"}*}

{* login errors *}
{* constants are defined in LoginHandler *}
{if $boxes_user_login_failed_login == 1} 
	<p class="boxcontent" style="color:red;">Username oder Passwort falsch</p>
{elseif $boxes_user_login_failed_login == 2} 
	<p class="boxcontent" style="color:red;">Zugriff wegen zu vieler Fehlversuche gesperrt</p>
{elseif $boxes_user_login_failed_login == 4} 
	<p class="boxcontent" style="color:red;">Dein Login wurde temporär gesperrt</p>
{/if}

  <form class="boxcontent" id="loggin_form" action="/login" method="post">
    <fieldset><label class="hidden">Login-Form</label>
	<input name="login" id="login" value="yes" type="hidden" />
	
	<label for="loginname">Username</label><br />

	{* input.loginname is used for JS Behaviour *}
	<input style="width: 90%" class="loginname" name="loginname" id="loginname" size="13" type="text" title="Dein Username" /> <br />
    <label for="password">Passwort</label><br />
    <input style="width: 90%" name="password" id="password" size="13" type="password" title="Dein Passwort" /><br />
    <input type="checkbox" id="persistentLogin" name="persistentLogin" /><label for="persistentLogin">LogIn merken</label><br />
	<input value="LogIn" type="submit" title="Ab daf&uuml;r!" /><br />
	
    </fieldset>
  </form>
<ul class="boxcontent vertical">
  <li><a href="/passwordlost">LogIn klappt nicht?</a></li>
  <li><a href="/newuser">Neu bei [[local.local.project_name]]?</a></li>
</ul>
</div>
