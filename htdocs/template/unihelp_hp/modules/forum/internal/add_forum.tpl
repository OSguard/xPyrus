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

*}{* $Id: add_forum.tpl 5807 2008-04-12 21:23:22Z trehn $
    $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/forum/internal/add_forum.tpl $ *}
    {* included in ../overview.tpl *}	

{errorbox caption="Fehler beim Forum Erstellen"}

<tr>
    <td colspan="6" class="AddForum"><a name="catId{$cat->id}" id="catId{$cat->id}"></a>
        <form action="{forum_url }" method="post">
        <fieldset id="addForum{$cat->id}">
            {if $forumToEdit != null && $forumCategory->id == $cat->id}
	            <legend>Forum editieren</legend>
        	{elseif $subforum}
        		<legend>Neues SubForum von "{$forum->getName()}" anlegen</legend>
        	{else}
		        <legend>Neues Forum anlegen</legend>
		    {/if}
  			{if $forumToEdit != null && $forumCategory->id == $cat->id}
        		<input type="hidden" name="method" value="editForum" />
				<input type="hidden" name="forumId" value="{$forumToEdit->id}" id="isEditTest" />
        	{else}
				<input type="hidden" name="method" value="addForum" />
				{if $subforum}
				<input type="hidden" name="parentId" value="{$forum->id}" />
				{/if}	
			{/if}
   			<input type="hidden" name="categoryId" value="{$cat->id}"/>
			<label for="forumName{$cat->id}">Name des Forums:</label>
    		{if $forumToEdit != null && $forumCategory->id == $cat->id}
				<input type="text" name="forumName" id="forumName{$cat->id}" value="{$forumToEdit->getName()}" /><br />
    		{else}
				<input type="text" name="forumName" id="forumName{$cat->id}" /><br />
			{/if}
			<br/>
	    	<label for="forumTemplate{$cat->id}">Template fürs Forum:</label>					
			{if $forumToEdit != null}
		    	<select name="forumTemplate" id="forumTemplate{$cat->id}" size="1">
				{foreach from=$userEntryInfoTemplates item=tpl}
					{if $tpl == $forumToEdit->getForumTemplate()}
			    		<option value="{$tpl}" selected="selected">{$tpl}</option>
					{else}
						<option value="{$tpl}">{$tpl}</option>
					{/if}
				{/foreach}
			    </select>
			{else}
				<select name="forumTemplate" id="forumTemplate{$cat->id}" size="1">
				{foreach from=$userEntryInfoTemplates item=tpl}
					{if $tpl == $cat->getDefaultTemplate()}
				    	<option value="{$tpl}" selected="selected">{$tpl}</option>
			    	{else}
					    <option value="{$tpl}">{$tpl}</option>
					{/if}
				{/foreach}
			    </select>
  			{/if}
			<br/><br/>
			<label for="description{$cat->id}">Beschreibung:</label>
        	{if $forumToEdit != null && $forumCategory->id == $cat->id}
				<textarea name="description" id="description{$cat->id}">{$forumToEdit->getDescriptionRaw()}</textarea><br /><br/>
				<label for="moderators{$cat->id}">Moderatoren (als CSV):</label>
                <input type="text" name="moderators" id="moderators{$cat->id}" value="{foreach name=mods from=$forumToEdit->getModerators() item=moderator}{$moderator->getUsername()}{if !$smarty.foreach.mods.last},{/if}{/foreach}"/><br>
        	{else}
				<textarea name="description" id="description{$cat->id}"></textarea><br />
				<label for="moderators{$cat->id}">Moderatoren (als CSV):</label>
                <input type="text" name="moderators" id="moderators{$cat->id}" value="{foreach name=mods from=$cat->getDefaultForumModerators() item=moderator}{$moderator->getUsername()}{if !$smarty.foreach.mods.last},{/if}{/foreach}"/><br>
			{/if}
			{if $forumToEdit != null && $forumCategory->id == $cat->id}
				{* TODO: do we need really the $cat->id suffix in the element ids?? (linap, 05.07.2007) *}
				<input type="checkbox" name="enablePostings" id="hasEnabledPostings{$cat->id}" {if $forumToEdit->hasEnabledPostings()}checked="checked"{/if} />
				<label for="hasEnabledPostings{$cat->id}">Postings erlauben</label><br />

                <input type="checkbox" name="mayContainNews" id="mayContainNews{$cat->id}" {if $forumToEdit->hasMayContainNews()}checked="checked"{/if} />
				<label for="mayContainNews{$cat->id}">News dürfen in dieses Forum veröffentlicht werden</label><br />
				
				<input type="checkbox" name="isImportant" id="isImportant{$cat->id}" {if $forumToEdit->isImportant()}checked="checked"{/if} />
                <label for="isImportant{$cat->id}">Forum ist wichtig (wichtig für Startseiten-Box &amp;c.)</label><br />
				
				<label for="visible">Sichtbar für:</label>
				<select id="visible" name="visible">
				{foreach from=$details_visible item=det}
				<option {if $forumToEdit->getVisibleId() == $det->id}selected="selected"{/if} value="{$det->id}">{translate privacy=$det->name}</option>
				{/foreach}
				</select>

				<input type="checkbox" name="enableFormatcode" id="hasEnabledFormatcode{$cat->id}" {if $forumToEdit->hasEnabledFormatcode()}checked="checked"{/if} />
				<label for="hasEnabledFormatcode{$cat->id}">Formatecode erlaubt:</label><br />

				{*<input type="checkbox" name="enableHTML" id="hasEnabledHTML{$cat->id}" {if $forumToEdit->hasEnabledHTML()}checked="checked"{/if}/>
				<label for="hasEnabledHTML{$cat->id}">HMTL erlaubt:</label><br />*}

				<input type="checkbox" name="enableSmileys" id="hasEnabledSmileys{$cat->id}" {if $forumToEdit->hasEnabledSmileys()}checked="checked"{/if}/>
				<label for="hasEnabledSmileys{$cat->id}">Smileys erlaubt:</label><br />

				{if $visitor->hasRight('FORUM_POINT_ADMIN')}
					<input type="checkbox" name="enablePoints" id="hasEnabledPoints{$cat->id}" {if $forumToEdit->hasEnabledPoints()}checked="checked"{/if}/>
					<label for="hasEnabledPoint{$cat->id}s">für Postings gibt es Punkte:</label>
				{/if}	
   			{else}
   				<input type="checkbox" name="enablePostings" id="hasEnabledPostings{$cat->id}" />
				<label for="hasEnabledPostings{$cat->id}">Postings erlauben:</label><br />

   				<input type="checkbox" name="mayContainNews" id="mayContainNews{$cat->id}" />
				<label for="mayContainNews{$cat->id}">News dürfen in dieses Forum veröffendlicht werden:</label><br />
				
				<label for="visible">Sichtbar für:</label>
				<select id="visible" name="visible">
				{foreach from=$details_visible item=det}
				<option value="{$det->id}">{translate privacy=$det->name}</option>
				{/foreach}
				</select>

				<input type="checkbox" name="enableFormatcode" id="hasEnabledFormatcode{$cat->id}" />
				<label for="hasEnabledFormatcode{$cat->id}">Formatecode erlaubt:</label><br />

				{*<input type="checkbox" name="enableHTML" id="hasEnabledHTML{$cat->id}" />
				<label for="hasEnabledHTM{$cat->id}L">HMTL erlaubt:</label><br />*}

				<input type="checkbox" name="enableSmileys" id="hasEnabledSmileys{$cat->id}" />
				<label for="hasEnabledSmileys{$cat->id}">Smileys erlaubt:</label><br />

    			{if $visitor->hasRight('FORUM_POINT_ADMIN')}
	    			<input type="checkbox" name="enablePoints" id="hasEnabledPoints{$cat->id}" />
		    		<label for="hasEnabledPoints{$cat->id}">für Postings gibt es Punkte:</label>
			    {/if}
		    {/if}
            <br />
            {if $forumToEdit != null}
            	<input type="submit" title="Einmal klicken um das Forum editieren" value="Forum editieren"/>
            {else}
		    	<input type="submit" title="Einmal klicken um das Forum anzulegen" value="Forum anlegen"/>
		    {/if}
	    </fieldset></form>
    </td>
</tr>
