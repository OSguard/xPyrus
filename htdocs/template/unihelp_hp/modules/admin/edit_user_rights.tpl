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
<div>
<h3>##edit_user_rights_edit## {$user->getUsername()}</h3>	

{if $showRights != null}

	
  <form action="{admin_url rights=$user}" method="post">
	<input type="hidden" name="userId" value="{$user->id}"/>
	<input type="hidden" name="save" value="true"/>
 	<input type="submit" name="submit"/>
    <br /><br />

		<table>
			<tr style="border-bottom: 2px solid black;">
				<th>##granted##</th>
				<th>##denied##</th>
				<th>##role##</th>
				<th>##name##</th>
				<th>##description##</th>
			</tr>
			
			{foreach from=$rights item=right}
				{assign var="rid" value=$right->id}
				{assign var="rname" value=$right->getName()}
			<tr style="border-bottom: 1px solid lightgrey;">
				<td>
					<input type="checkbox" name="granted[]" value="{$right->id}" {if $userRights[$rid] === true}checked="checked"{/if}/>
  				</td>
				<td>
					<input type="checkbox" name="noMoreGranted[]" value="{$right->id}" {if $userRights[$rid] === false}checked="checked"{/if}/>
  				</td>
				<td>
					<input type="checkbox" {if $roleRights[$rid] != null}checked="checked"{/if} disabled="disabled"/>
					##role## (					
					{foreach name=role from=$roles item=role}
                        {assign var="rolerights" value=$role->getRoleRights()}
                        {assign var="negativeRid" value=-$rid}
					 	{if $rolerights[$rid] !== null}{$role->name} (+),{elseif $rolerights[$negativeRid] !== null}{$role->name} (-),{/if}
			 		{/foreach}
			 		 )
  				</td>
				<td>{$right->getName()}</td>
				<td>{$right->getDescription()}</td>
			</tr>
			{/foreach}
		</table>
		<input type="submit" name="submit"/>

  </form>
	
{/if}
</div>	</div>	
