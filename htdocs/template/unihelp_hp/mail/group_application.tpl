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

*}[url={user_info_url user=$user extern=1}]{$user->getUsername()}[/url] möchte in die {if $group->title!='group'}{$group->title} {else}Organisation {/if}{$group->getName()} aufgenommen werden.

[url={group_info_url extern=1 groupId=$group->id add=$user->id}]jetzt hinzufügen[/url]
{* can't choose caption "Re: Bewerbung" here, because it is removed by a regexp in the PM-blc *}
[url={pm_url extern=1 new=true caption="Deine Bewerbung" receivers=$user->getUsername()}]per PM antworten[/url]

[url={group_info_url extern=1 groupToEdit=$group->id}]zur Verwaltung von {$group->getName()}[/url]
