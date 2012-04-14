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

*}Du wurdest zu der {if $group->title!='group'}{$group->title} {else}Organisation {/if}[url={group_info_url extern=1 group=$group}]{$group->getName()}[/url] hinzugefügt.

Wenn Du damit nicht einverstanden bist kannst Du [url={group_info_url extern=1 leaveId=$group->id}]wieder austreten[/url].
