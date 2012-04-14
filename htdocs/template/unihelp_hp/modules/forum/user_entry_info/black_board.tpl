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

*}<p>
{if !$entry->isAnonymous() && !$entry->isForGroup()}
    {user_info_link user=$author}<br />
  {if $author->isRegularLocalUser()}
    {if $author->hasFirstName() || $author->hasLastName()}
    Name: {$author->getFirstName()} {$author->getLastName()}<br />
  	{/if}
    {if $author->hasPublicEmail()}
       	E-Mail: {$author->getPublicEmail()}<br />
	{/if}
    <span class="info"> 
    Punkte: {$author->getPoints()}<br />
    Beitr&auml;ge: {$author->getForumEntries()}<br />
    </span>
  {/if}{* end if regular local user *}
{elseif $entry->isForGroup()}
    {group_info_link group=$entry->getGroup()}
{else}
    <em>anonym</em>
{/if}

</p>
