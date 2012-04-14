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

*}{* $Id: add_category.tpl 5807 2008-04-12 21:23:22Z trehn $
    $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/forum/internal/add_category.tpl $ *}
    {* included in ../overview.tpl *}


<a id="showCat" name="showCat"></a>
<form action="{forum_url }" method="post">
	{* {if $categoryToEdit == null}style="display: none;"{/if} *}
	<fieldset id="newCat">
	<legend>{if $categoryToEdit != null}Kategorie Editieren{else}Neue Kategorie anlegen{/if}</legend>
	    {if $categoryToEdit != null}
			<input type="hidden" name="method" value="editCategory"/>
			<input type="hidden" name="catId" value="{$categoryToEdit->id}"/>
		{else}
			<input type="hidden" name="method" value="addCategory"/>
		{/if}
		
		<label for="name">Name der Kategorie:</label>
		<input type="text" name="name" id="name" {if $categoryToEdit != null}value="{$categoryToEdit->getName()}"{/if} /><br />
		<br/>
		
		<label for="categoryTemplate">Default Template fürs Forum:</label>
		{if $categoryToEdit != null}
			<select name="categoryTemplate" id="categoryTemplate" size="1">
				{foreach from=$userEntryInfoTemplates item=tpl}
					{if $tpl == $categoryToEdit->getDefaultTemplate()}
						<option value="{$tpl}" selected="selected">{$tpl}</option>
					{else}
						<option value="{$tpl}">{$tpl}</option>
					{/if}
				{/foreach}
			</select>
		{else}
			<select name="categoryTemplate" id="categoryTemplate" size="1">
				{foreach from=$userEntryInfoTemplates item=tpl}
					{if $tpl == "default.tpl"}
						<option value="{$tpl}" selected="selected">{$tpl}</option>
					{else}
						<option value="{$tpl}">{$tpl}</option>
					{/if}
				{/foreach}
			</select>
  		{/if}
		<br/>

		{if $categoryToEdit != null}
			<label for="moderators">Moderatoren (als CSV):</label>
            <input type="text" name="moderators" id="moderators" value="{foreach name=mods from=$categoryToEdit->getModerators() item=moderator}{$moderator->getUsername()}{if !$smarty.foreach.mods.last},{/if}{/foreach}"/><br>

            <label for="defaultForumModerators">Default Forum Mods (als CSV):</label>
            <input type="text" name="defaultForumModerators" id="defaultForumModerators" value="{foreach name=mods from=$categoryToEdit->getDefaultForumModerators() item=moderator}{$moderator->getUsername()}{if !$smarty.foreach.mods.last},{/if}{/foreach}"/><br>
		{else}
			<label for="moderators">Moderatoren (als CSV):</label>
            <input type="text" name="moderators" id="moderators" /><br>
			<label for="defaultForumModerators">Default Forum Mods (als CSV):</label>
            <input type="text" name="defaultForumModerators" id="defaultForumModerators" /><br>
		{/if}
		<br/>
		<label for="description">Beschreibung:</label>
		<textarea name="description" id="description">{if $categoryToEdit != null}{$categoryToEdit->getDescriptionRaw()}{/if}</textarea><br />
		{if $categoryToEdit == null}
        	<input type="submit" title="Einmal klicken um die Kategorie anzulegen" value="Kategorie anlegen" />
        {else}
        	<input type="submit" title="Einmal klicken um die Kategorie anzulegen" value="Kategorie editieren" />
        {/if}		
	</fieldset>
</form>
