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

*}<br /><br />
<a href="{admin_url user=$user}">##backToUserAdminMenu##</a>

<div class="shadow">
<div >
{if $showWarnings != null}
	
<h3>Verwarnungen von {$user->getUsername()} bearbeiten</h3>	
	
  <form action="{admin_url warnings=$user}" method="post">
	<input type="hidden" name="userId" value="{$user->id}"/>
	<input type="hidden" name="save" value="true" />

		<table style="width: 99%">
			<tr style="border-bottom: 2px solid black;">
				<th>lfd. Nr.</th>
				<th>Typ</th>
				<th>von</th>
				<th>bis</th>
				<th>Grund</th>
				<th>Aktion</th>
			</tr>
			
			{counter start=0 print=false}
			
			{foreach from=$user_warnings item="warn"}
			<tr {if !$warn->hasExpired()}style="background-color: #def"{/if}>
			<td>{counter}</td>
			<td>{if $warn->getType() == $warn->TYPE_YELLOW}gelb
				{elseif $warn->getType() == $warn->TYPE_YELLOWRED}gelb-rot
				{elseif $warn->getType() == $warn->TYPE_RED}rot
				{elseif $warn->getType() == $warn->TYPE_GREEN}grün/Notiz
				{/if}</td>
			<td>{$warn->getInsertAt()|unihelp_strftime:'%d.%m.%Y, %H:%M'}</td>
			<td>{$warn->getDeclaredUntil()|unihelp_strftime:'%d.%m.%Y, %H:%M'}</td>
			<td>{$warn->getReason()}</td>
			<td>{if !$warn->hasExpired()}<input type="submit" name="suspend{$warn->id}" value="aufheben" />{/if}</td>
			</tr>
			{foreachelse}
			<tr><td colspan="6">Keine Warnungen vorhanden.</td></tr>
			{/foreach}
			
		</table>
		
		<p>
		<h4>Verwarnung aussprechen</h4>
		<label for="warning_type">Typ</label>
		<select name="warning_type" id="warning_type">
			<option value="{$warnEmty->TYPE_YELLOW}">gelb</option>
			<option value="{$warnEmty->TYPE_YELLOWRED}">gelb-rot</option>
			<option value="{$warnEmty->TYPE_RED}">rot</option>
			<option value="{$warnEmty->TYPE_GREEN}">grün/Notiz</option>
		</select>
		<br />
		<label for="duration">Dauer</label>
		<select name="duration" id="duration">
			<option value="2">2 Tage</option>
			<option value="3">3 Tage</option>
			<option value="4">4 Tage</option>
			<option value="5" selected="selected">5 Tage</option>
			<option value="6">6 Tage</option>
		</select>
		<br />
		<label for="reason">Grund</label>
		<textarea id="reason" name="reason" rows="3" cols="66"></textarea>
		</p>
		
		<input type="submit" name="impose" value="verwarnen" />

  </form>
	<br class="clear" />
{/if}


</div>	</div>	
