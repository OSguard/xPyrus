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

*}{* $Id: delete_account.tpl 5895 2008-05-03 15:38:20Z schnueptus $ *}
{* $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/usermanagement/delete_account.tpl $ *}

{* <h2 id="pagename">Account löschen</h2> *}

<div class="shadow"><div>
<br class="clear" />
<form action="{user_management_url delete=$userToDelete edit=$admin_mode}" method="post">

<div class="errorbox"><p>Bitte beachte: die Löschung Deines Accounts ist endgültig und unwiederbringlich. Alle Daten werden gemäß unserer Datenschutzbestimmungen und Nutzungsbedingungen gelöscht.</p>
  <p style="font-size:larger;color: #FF0000;"><strong>Achtung: Eine Neuanmeldung bei [[local.local.project_name]]  ist nicht mehr möglich! Wenn Du nun Deinen Account löschst, wirst Du Dich nicht noch einmal bei [[local.local.project_name]] anmelden können!</strong></p>
</div>

Du kannst uns über die Gründe Deiner Löschung informieren, wenn Du magst. Diese Angabe ist freiwillig.

<label for="reason">Bemerkung:</label>
<textarea id="reason" name="reason" cols="60" rows="5"></textarea><br />
{if !$admin_mode}
<label for="passwort">Dein Passwort:</label>
<input name="password" id="passwort" type="password" /><br />
{else}
 <p style="font-size:larger;color: #FF0000;">Account von {$userToDelete->username} in Papierkorb verschieben</p>
{/if}
<input name="delete_submit" type="submit" value="Ja, ich will meinen Account endgültig löschen." />
<input name="cancel_submit" type="submit" value="Nein, doch lieber nicht." />
</form>

<br class="clear" />

</div></div>
