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
<a href="{admin_url groups=true}#newGroup">##edit_group_add##</a>	
<br />
<div class="shadow"><div class="nopadding">
<h3>##organizations##</h3>
<table class="centralTable">
	<tr>
		<th id="group-name">##name##</th>
		<th id="group-desc">##description##</th>
		<th id="group-user">##extraUser##</th>
		<th id="group-option">##options##</th>
	</tr>
	{foreach from=$groups item=group}
	<tr>
		
		<td headers="group-name">
			<a href="{group_info_url group=$group}" title="##edit_group_info## {$group-name}">
				{$group->title} {$group->name}
			</a> ({$group->isVisible})
		</td>
		<td headers="group-desc">{$group->description}</td>
		<td headers="group-user">
			
		  <form action="{admin_url group=$group add=true}" method="post">
			  <input type="text" name="users" size="20" maxlength="40"/>
			  <input type="submit" name="add" value="add"/>
			  <input type="submit" name="del" value="del"/>
		  </form>
		</td>
		<td headers="group-option">
			<a href="{admin_url group=$group edit=true}#editGroup">##edit## </a><br />
			<a href="{admin_url group=$group del=true}">##delete##</a><br />
			<a href="{admin_url group=$group groupRights=true}">##edit_group_editRights##</a><br />
			<a href="{group_info_url groupToEdit=$group->id}">##manage##</a>
		</td>  
	</tr>
	{/foreach}
</table>

</div></div>

<div class="shadow"><div>
  	{if $groupToEdit != null}
  <h3>##edit_group_edit##</h3>
  <a name="editGroup"></a>
  <form action="{admin_url groups=true}" method="post">  	
  		<input type="hidden" name="method" value="editGroup"/>
  		<input type="hidden" name="groupId" value="{$groupToEdit->id}"/>
  		<input type="hidden" name="save" value="true" />
	  	<label for="group_title">##title##</label> <input id="group_title" type="text" name="title" value="{$groupToEdit->title}"/><br/>
	  	<label for="group_name">##name##</label> <input id="group_name" type="text" name="name" value="{$groupToEdit->name}"/><br/>
	    <input id="group_visible" type="checkbox" name="isVisible" {if $groupToEdit->isVisible}checked="checked"{/if}/>
		<label for="group_visible">##visible##</label><br />
	  	<label for="group_desc">##description##</label> <input id="group_desc" type="text" size="70" maxlength="255"  name="description" value="{$groupToEdit->description}"/><br/>
  	{else}
  	<h3>##edit_group_add##</h3>
  	<a name="newGroup"></a>
    <form action="/index.php?mod=i_am_god&amp;dest=module&amp;method=editGroup&amp;save=true" method="post"> 
  		<input type="hidden" name="method" value="editGroup"/>
	  	<label for="group_title">##title##</label> <input id="group_title" type="text" name="title"/><br/>
	  	<label for="group_name">##name##</label> <input id="group_name" type="text" name="name"/><br/>
	  	<input id="group_visible" type="checkbox" name="isVisible" checked="checked"/>
	  	<label for="group_visible">##visible##</label> <br/>
	  	<label for="group_desc">##description##</label> <input id="group_desc" type="text" name="description"/><br/>
  	{/if}
  	
  	{if $groupToEdit != null}
	 	<input type="submit" name="submit" value="##save##"/>
 	{else}
	 	<input type="submit" name="submit" value="##add##"/>
 	{/if}
 	<br class="clear" />
  </form>
</div>
</div>
