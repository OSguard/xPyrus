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

*}{* <h2 id="pagename">User werben</h2> *}

<div class="shadow"><div>
<h3>Hilfe</h3>

  <ul class="bulleted">
    <li>Wenn Du eine/n Freund/in einladen möchtest sich bei [[local.local.project_name]] anzumelden, kannst Du ihm/ihr eine Einladung schicken.
    	<ol>
		<li>Gib einfach Namen und E-Mail-Adresse an,</li>
		<li>schreib ein paar nette Zeilen und</li>
		<li>klicke auf "Absenden".</li>
		</ol>
	 </li> 
	 <li>	
	Wenn Sich Dein/e Freund/in mit der von Dir angegebenen E-Mail-Adresse anmeldet und aktiviert hat, bekommst Du automatisch {$points->getPointsSum()} Levelpunkte und {math equation="x / 10" x=$points->getPointsFlow()} Wirtschaftspunkte auf Dein Konto gutgeschrieben.
    </li>
  </ul>
  

</div></div>

{errorbox caption="Fehler beim Senden"}

{if $success}
<div class="shadow"><div>
<h3>Erfolgreich abgeschickt</h3>

	<p>Die E-Mail wurde erfolgreich abgeschickt!</p>  

</div></div>
{else}
<div class="shadow"><div>
<h3>User Werben</h3>

  <form action="/canvassuser" method="post">
  	<fieldset>
  	<legend>Hallo</legend>
  	{if $central_errors.canvassname}<span class="missing">{/if}
  	<label for="canvassname">Name:</label>
    {* NOTE: canvassname text is used by Behaviour/JS *}
    <input type="text" name="canvassname" id="canvassname" size="40" maxlength="40"/>
    {if $central_errors.canvassname}</span>{/if}<br />
  	{if $central_errors.canvassemail}<span class="missing">{/if}
  	<label for="canvassemail">Email:</label>
    <input type="text" name="canvassemail" id="canvassemail" size="40" maxlength="40"/>
    {if $central_errors.canvassemail}</span>{/if}<br />
  	<label for="canvassdefault">Unser Text:</label>
    {assign var="canvassName" value="'Name'"}
    {assign var="registrationURL" value="'Registrierungs-URL'"}
    {assign var="username" value=$visitor->getUsername()}
    {* NOTE: canvassdefault text is changed by Javascript *}
    <textarea name="canvassdefault"  id="canvassdefault" rows="10" cols="50" wrap="on" readonly="readonly">{include file="mail/user_canvass.tpl"}</textarea>
  	<label for="canvasstext">Dein Text:</label>
    <textarea name="canvasstext"  id="canvasstext" rows="10" cols="50" wrap="on"></textarea>
  	
  	<input type="submit" name="save" value="Absenden"/>
  	<input type="reset" />
  
  </fieldset>
  </form>
</div></div>
{/if}
 
