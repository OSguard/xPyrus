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
<div class="shadow"><div>
<h3>alle Tags</h3>
	<ul>
		{foreach from=$tags item=tag}
			<li>{$tag->getName()} <a href="/index.php?mod=i_am_god&amp;dest=module&amp;method=editTag&amp;showValue=true&amp;tagId={$tag->id}">bearbeiten</a></li>	
		{/foreach}
	</ul>
</div></div>

<div class="shadow"><div>
{if !$tagToEdit}
<h3>neuer Tag</h3>
<form enctype="multipart/form-data" action="/index.php?mod=i_am_god&dest=module&method=editTag" method="post">
{else}
<h3>Tag bearbeiten</h3>
<form enctype="multipart/form-data" action="/index.php?mod=i_am_god&dest=module&method=editTag" method="post">
<input name="tagId" value="{$tagToEdit->id}" type="hidden" />
{/if}

<label for="tagName">Tag Name:</label>
<input name="tagName" size"30" type="text" {if $tagToEdit}value="{$tagToEdit->getName()}"{/if} /><br />

<input type="submit" name="save" value="Abschicken"  />
</form>
<br class="clear" />	
</div>
</div>
