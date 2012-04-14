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
    {user_info_link user=$author}
    <br />
    <a href="{user_info_url  user=$author}" title="{$author->getUsername()}">
    	<img src="{userpic_url tiny=$author}" alt="UserBild" style="margin-top: 3px;"/>
    </a>	
    <br />
    <span class="info"> 
    Punkte: {$author->getPoints()}<br />
    Beitr&auml;ge: {$author->getForumEntries()}<br />
    Status: {user_status user=$author}
    </span>
    
{elseif $entry->isForGroup()}
    {group_info_link group=$entry->getGroup()}
    {assign var="group" value=$entry->getGroup()}
    <br />
    <a href="{group_info_url group=$group}" >
    		<img src="{$group->getPictureFile('tiny')|default:"/images/kegel_group.png"}" alt="Logo von {$group->name}" />
    </a>
{else}
    <em>anonym</em>
{/if}

</p>
