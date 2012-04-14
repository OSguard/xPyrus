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
<a href="{admin_url roles=true}#addNew">##edit_role_add##</a>	
<br /><br />
<div class="shadow"><div class="nopadding"><h3>##roles##</h3>
<table class="centralTable">
	<tr>
		<th>##options##</th>
		<th>##name##</th>
		<th>##description##</th>
		<th>##extraUser##</th>
	</tr>
	{foreach from=$roles item=role}
	<tr>
		<td>
			<a href="{admin_url role=$role edit=true}#edit">##edit##</a>
			<a href="{admin_url role=$role del=true}">##delete##</a>
		</td>
		<td>{$role->name}</td>
		<td>{$role->description}</td>
		<td>
			
		  <form action="{admin_url role=$role add=true}" method="post">
			  <input type="text" name="users" size="20" maxlength="40"/>
			  <input type="submit" name="add" value="##add##"/>
			  <input type="submit" name="del" value="##delete##"/>
		  </form>
		</td  
	</tr>
	{/foreach}
</table>
	
</div></div>

<div class="shadow"><div><h3>##role##</h3>
 <a name="addNew"></a><a name="edit"></a>
  <form action="{admin_url roles=true}" method="post">
  	{if $roleToEdit != null}
  		<input type="hidden" name="method" value="editRole"/>
  		<input type="hidden" name="save" value="true"/>
  		<input type="hidden" name="roleId" value="{$roleToEdit->id}"/>
	  	<label for="role_name">##name##</label> <input id="role_name" type="text" name="name" value="{$roleToEdit->name}"/><br/>
	  	<label for="role_desc">##description##</label> <input id="role_desc" type="text" size="70" maxlength="255"  name="description" value="{$roleToEdit->description}"/><br/>
  	{else}
  		<input type="hidden" name="method" value="editRole"/>
  		<input type="hidden" name="save" value="true"/>
	  	<label for="role_name">##name##</label> <input id="role_name" type="text" name="name"/><br/>
	  	<label for="role_desc">##description##</label> <input id="role_desc" type="text" name="description"/><br/>
  	{/if}
  	
  	<table class="centralTable">
  		<tr>
  			<th>##granted##</th>
  			<th>##denied##</th>
  			<th>##right##</th>
  			<th>##description##</th>
  		</tr>
  		{if $roleToEdit}
  			{assign var="roleRights" value=$roleToEdit->getRoleRights()}
  		{/if}	
  		{foreach from=$allRights item=right}
		{assign var="rid" value=$right->id}
		{assign var="negRid" value=-$rid}
  		<tr>
  			<td>
  				{if $roleToEdit != null}
					<input name="rights[]" type="checkbox" value="{$right->id}" {if $roleRights[$rid] !== null}checked="checked"{/if}/>
  				{else}
					<input name="rights[]" type="checkbox" value="{$right->id}"/>
  				{/if}
  			</td>
  			<td>
  				{if $roleToEdit != null}
					<input name="NOrights[]" type="checkbox" value="{$right->id}" {if $roleRights[$negRid] !== null}checked="checked"{/if}/>
  				{else}
					<input name="NOrights[]" type="checkbox" value="{$right->id}"/>
  				{/if}
  			</td>
  			<td>{$right->getName()}</td>
  			<td>{$right->getDescription()}</td>
  		</tr>
  		{/foreach}
  	</table>
  	
  	{if $roleToEdit != null}
	 	<input type="submit" name="submit" value="##save##"/>
 	{else}
	 	<input type="submit" name="submit" value="##add##"/>
 	{/if}
  </form>
  <br class="clear" />
</div>
</div>
