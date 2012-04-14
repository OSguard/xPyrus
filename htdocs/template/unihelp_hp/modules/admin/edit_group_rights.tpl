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

*}
{literal}
<style>
input{
margin: 5px;
}
</style>
{/literal}
<br /><br />
<a href="{admin_url groups=true}">##edit_group_rights_backToOverview##</a>
<br />

<div class="shadow"><div class="nopadding">
	<form action="{admin_url group=$userGroup groupRights=true}" method="post">
	<h3>##edit_group_rights_edit## {$userGroup->name}</h3>
	<input type="hidden" name="userId" value="{$user->id}"/>
	<input type="hidden" name="groupId" value="{$userGroup->id}"/>
		<table class="centralTable">
			<tr style="border-bottom: 2px solid black;">
				<th>##set##</th>
				<th>##name##</th>
				<th>##description##</th>
			</tr>
			{foreach from=$groupRights item=groupRight}
				{assign var="rname" value=$groupRight->id}
			<tr style="border-bottom: 1px solid lightgrey;">
				<td>
					<input type="checkbox" name="granted[]" id="right{$groupRight->id}" value="{$groupRight->id}" {if $groupUserRights[$rname] === true}checked="checked"{/if}/>
  				</td>				
				<td><label for="right{$groupRight->id}">{$groupRight->getName()}</label></td>
				<td>{$groupRight->getDescription()}</td>
			</tr>
			{/foreach}
		</table>
 	<input type="submit" name="save" value="##save##" />		
	</form>
	<br class="clear" />
</div></div>
