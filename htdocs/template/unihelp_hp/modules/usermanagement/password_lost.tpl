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

*}{*	$Id: password_lost.tpl 5895 2008-05-03 15:38:20Z schnueptus $
	$HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/usermanagement/password_lost.tpl $ *}
{* <h2 id="pagename">Passwort vergessen</h2> *}

	<div class="shadow"><div>
		<h3>Hilfe</h3>
			<ul class="bulleted">
		   		<li>Hier kannst Du Dein Passwort ändern, falls der Loggin nicht klappt.</li>
				<li>Dein Passwort ist dem [[local.local.project_name]]-Team nicht bekannt. Trage bitte Deinen Usernamen und Deine hier registrierte E-Mail-Adresse ein und Du bekommst ein neues Passwort zugeschickt. Dieses kannst Du jederzeit ändern.</li>
			</ul>	 
	</div></div>

	{if $wrong_data}
	<div class="box" style="border-color:red;"><h3 style="background-color: red;">Fehler</h3>
		<p style="color: red;">
			Die Kombination aus Username und E-Mail-Adresse gibt es nicht.
		</p>
	</div>
	{/if}

<div class="shadow"><div>	
<h3>Passwort vergessen?</h3>
  <form action="/passwordlost" method="post">
    <fieldset>
	<label for="lost_username">Username:</label>

	  <!-- #loginname is used for JS Behaviour -->
	  <input class="loginname" name="lost_username" id="lost_username" size="20" type="text" title="Dein Username" value="Dein Username" />
	  <br />
	  <label for="lost_email">E-Mail-Adresse:</label>
      <input name="lost_email" id="lost_email" size="20" title="Deine E-Mail-Adresse" /><br />
	  <input name="lost_submit" value="Abschicken" type="submit" title="Ab dafür!" /><br />
    </fieldset>
  </form>
</div></div>  
