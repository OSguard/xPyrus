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

*}{* $Id: part_award.tpl 5895 2008-05-03 15:38:20Z schnueptus $
   $BaseURL$ *}
{if count($awards) > 0}
    <p>{$userinfo_user->username} hat schon folgende [[local.local.project_name]]-Auszeichnungen erhalten:</p>
    <ul style="list-style: none">
    {foreach from=$awards item=UAward}
    	{assign value=$UAward->getAward() var="award"}
        <li class="compact" style="height: 110px;">
        {if $award->getIcon()}
        	<img alt="Icon" src="{$award->getIcon()}" />
        	<br />
        {/if}
        <strong>{$award->getName()}</strong>
        <br />Platz: {$UAward->getRank()} 
        {if $award->getLink()}
        	<br /><a href="{$award->getLink()}">Details lesen</a>
        {/if}
        </li>
    {/foreach}
    </ul>
{else}
    <p>{$userinfo_user->username} hat noch keine [[local.local.project_name]]-Auszeichnungen erworben.</p>
{/if}

<br class="clear" />
