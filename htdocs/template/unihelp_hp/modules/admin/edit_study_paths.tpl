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

*}{if !$studyTag}
<div class="shadow"><div>
{if $pathToEdit != null}
	<h3>##edit_study_paths_edit##</h3>
{else}
	<h3>##edit_study_paths_add##</h3>
{/if}
<fieldset>

<form action="/index.php?mod=i_am_god&dest=module&method=editStudyPaths" method="post">
{if $pathToEdit != null}<input type="hidden" name="sp_id" value="{$pathToEdit->id}" >{/if}
<label for="sp_name">##name##</label>
<input type="text" name="sp_name" {if $pathToEdit != null} value="{$pathToEdit->getName()}" {/if} size="15">
<br />
<label for="sp_name_english">##name## ##english##</label>
<input type="text" name="sp_name_english" {if $pathToEdit != null} value="{$pathToEdit->getNameEnglish()}" {/if} size="15">
<br />
<label for="sp_name_short">##abbreviation##</label>
<input type="text" name="sp_name_short" {if $pathToEdit != null} value="{$pathToEdit->getNameShort()}" {/if} size="15">
<br />
<label for="sp_description">##description##</label>
<input type="text" name="sp_description" {if $pathToEdit != null} value="{$pathToEdit->getDescription()}" {/if} size="50">
<br />
<label for="uniId">##university##</label>
<select name="uniId" id="uniId">
	{foreach from=$universities item=uni}
		{if $pathToEdit != null && $pathToEdit->uniId == $uni->id}
			<option value="{$uni->id}" selected="selected">{$uni->getName()}</option>
		{else}
			<option value="{$uni->id}">{$uni->getName()}</option>
		{/if}
	{/foreach}
</select>
<br />
<input type="checkbox" name="sp_available" {if $pathToEdit == null || $pathToEdit->isAvailable} checked="checked" {/if} />
<label for="sp_available">##available##</label><br />
<input type="submit" name="save" value="##submit##"  />
<br />
</form>
</fieldset>
</div></div>
{/if}


{if $studyTag}
<div class="shadow"><div>
<h3>##edit_study_paths_editTag## {$studyTag->getName()}</h3>

<form action="/index.php?mod=i_am_god&amp;dest=module&amp;method=editStudyPaths" method="post">
<input type="hidden" name="studyId" value="{$studyTag->id}" />
<ul>
{foreach from=$tags item=tag}
 <li>
 	{assign var="tid" value=$tag->id}
 	<input name="newTags[]" type="checkbox" value="{$tag->id}" {if $studyTag->tags[$tid] != null}checked="checked"{/if} />
 	{$tag->getName()} 	
 </li>
 {/foreach}
</ul>
<input type="submit" name="saveTag" value="##submit##"  />
<br />
</form>
</div></div>
{/if}

<div class="shadow"><div>
<h3>##edit_study_paths_all##</h3>
{foreach from=$study_paths item=path}
{$path->name} 
	<a href="/index.php?mod=i_am_god&dest=module&method=editStudyPaths&amp;edit_mode=true&amp;sp_id={$path->id}">##edit##</a> - 
	<a href="/index.php?mod=i_am_god&dest=module&method=editStudyPaths&amp;tag_mode=true&amp;sp_id={$path->id}">##edit_study_paths_editTags##</a>
	<br />
{/foreach}
</div></div>
