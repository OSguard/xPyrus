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

*}{* $Id: edit_forum_tags.tpl 5807 2008-04-12 21:23:22Z trehn $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/forum/edit_forum_tags.tpl $ *}

<div class="shadow"><div>
<h3>Tag zu Forum {$forum->getName()} bearbeiten</h3>

<form action="/index.php?mod=forum&amp;dest=module&amp;method=editTagsForum" method="post">
<input type="hidden" name="forumId" value="{$forum->id}" />
<ul>
{foreach from=$tags item=tag}
 <li>
 	{assign var="tid" value=$tag->id}
 	{assign var="forumTags" value=$forum->getTags()}
 	<input id="tag{$tid}" name="newTags[]" type="checkbox" value="{$tag->id}" {if $forumTags[$tid] != null}checked="checked"{/if} />
 	<label for="tag{$tid}">{$tag->getName()}</label>
</li><br />
 {/foreach}
</ul>
<input type="submit" name="saveTag" value="Abschicken"  />
<br />
</form>
</div></div>
