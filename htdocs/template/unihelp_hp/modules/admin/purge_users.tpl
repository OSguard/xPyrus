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

*}<div class="shadow">
<div>

<h3>User endgültig löschen</h3>	
	
  <form action="{admin_url purgeusers=true}" method="post">
		<table style="width: 99%">
			<tr style="border-bottom: 2px solid black;">
				<th>löschen?</th>
				<th>User</th>
				<th>Datum/Zeit</th>
				<th>Grund</th>
			</tr>
			
			{foreach from=$users_to_delete item="user"}
			<tr>
			<td><input type="checkbox" name="usersToDelete[]" value="{$user.user->id}" id="delete{$user.user->id}" /></td>
			<td>{user_info_link user=$user.user}</td>
			<td>{$user.insertAt|unihelp_strftime}</td>
			<td>{$user.reason}</td>
			</tr>
			{foreachelse}
			<tr><td colspan="4">Keine Löschwünsche vorhanden.</td></tr>
			{/foreach}
			
		</table>
		<input type="submit" name="recoverSubmit" value="Markierte User wiederherstellen" />		
		<input type="submit" name="requestPurgeConfirmation" value="Markierte User endgültig löschen" />

  </form>
  <br class="clear" />

</div></div>
