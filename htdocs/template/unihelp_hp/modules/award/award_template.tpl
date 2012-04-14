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

*}<div class="shadow"><div>
<h3>Hall of Fame</h3>
{foreach from=$awards item=foo}
<ul><b><i>{$foo->getName()}</i></b> <sub><img src="{$foo->getIcon()}" width="20" height="20" alt=""></sub> {if $visitor->hasRight(PROFILE_ADMIN)} <a href="/index.php?mod=award&amp;method=modAward&amp;zahl={$foo->id}" >edit</a> <a href="/index.php?mod=award&amp;method=delete&amp;zahl={$foo->id}">del</a>{/if} <a href="{$foo->getLink()}" target="_blank">zum Event</a></ul>
{foreach from=$foo->getUserAward() item=too}
<ul>{$too->getRank()}. {assign var="neuerUser" value=$too->getUsers()} <a href="/user/{$neuerUser->username}">{$neuerUser->username}</a> {if $visitor->hasRight('PROFILE_ADMIN')} <a href="/index.php?mod=award&amp;method=editUserAward&amp;id={$too->id}">edit</a> <a href="/index.php?mod=award&amp;method=deleteRank&amp;id={$too->id}">del</a>{/if}</ul>
{/foreach}
{if $visitor->hasRight('PROFILE_ADMIN')}<a href="/index.php?mod=award&amp;method=addUserAward&amp;zahl={$foo->id}">addRank</a>{/if}<br><br>
{/foreach}
{if $visitor->hasRight('PROFILE_ADMIN')}
<br>
<br>
<a href="/index.php?mod=award&amp;method=addAward">addAward</a>
{/if}
</div></div>
{if $visitor->hasRight('PROFILE_ADMIN')}
<div class="shadow"><div>
<h3>Online Award hinzufügen</h3>
<a href="/index.php?mod=award&amp;method=onlineAwards&amp;var=a">add/update Download-Ranking</a><br />
<a href="/index.php?mod=award&amp;method=onlineAwards&amp;var=b">add/update Forum-Ranking</a><br />
<a href="/index.php?mod=award&amp;method=onlineAwards&amp;var=c">add/update Upload-Ranking</a><br />
<a href="/index.php?mod=award&amp;method=onlineAwards&amp;var=d">add/update Aktivitaets-Ranking</a><br />
</div></div>
{/if}



