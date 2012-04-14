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

*}<!-- <div class="shadow"><div>
<h3>Online Awards: {$titel}</h3>
{foreach from=$showAward item=foo}
<ol> 
{$foo->username} 
{if $var==a}
{$foo->getCourseFilesDownloads()}
{elseif $var==b}
{$foo->getForumEntries()}
{elseif $var==c}
{$foo->getCourseFilesUploads()}
{else $var==d}
{$foo->getActivityIndex()}
{/if}
</ol>
{/foreach}

<a href="javascript:history.back()">Zurueck</a>
</div></div> -->
<div class="shadow"><div>
<h3>{$titel}</h3>
{errorbox caption="Fehler beim Senden"}
<form action="/index.php?mod=award&amp;method=onlineAwards&amp;var={$var}" method="post">
<input type="text" name='name' class="textfeld" value="{$titel}" size="50" />
<input value="OK" type="submit" title="Aenderung uebernehmen" />
</form>
<br>
<br>
</div></div>
<a href="javascript:history.back()">Zurueck</a>

