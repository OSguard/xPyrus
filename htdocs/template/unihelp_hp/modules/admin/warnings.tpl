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

*}<table class="centralTable">
<tr>
 <th>User</th>
 <th>Verwarnt ab</th>
 <th>bis</th>
 <th>Grund</th>
 <th>&nbsp;</th>
</tr>
{foreach from=$user_warnings item='warn'}
{if $warn->getType() == $warn->TYPE_YELLOW}
 {assign var="color" value="ffff00"}
{elseif $warn->getType() == $warn->TYPE_YELLOWRED}
 {assign var="color" value="ff8040"}
{elseif $warn->getType() == $warn->TYPE_RED}
 {assign var="color" value="ff0000"}
{elseif $warn->getType() == $warn->TYPE_GREEN}
 {assign var="color" value="80ff80"}
{/if}
<tr>
 <td style="background-color: #{$color}">{user_info_link user=$warn->user}</td>
 <td style="background-color: #{$color}">{$warn->getInsertAt()|unihelp_strftime}</td>
 <td style="background-color: #{$color}">{$warn->getDeclaredUntil()|unihelp_strftime}</td>
 <td style="background-color: #{$color}">{$warn->getReason()}</td>
 <td style="background-color: #{$color}"><a href="{admin_url warnings=$warn->user}">edit</a></td>
</tr>
{foreachelse}
 <td colspan="5">Keine Verwarnungen</td>
{/foreach}
</table>
