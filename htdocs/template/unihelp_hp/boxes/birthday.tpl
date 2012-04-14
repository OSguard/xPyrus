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

*}{* $Id: birthday.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{if !$box_birthday_ajax}
<div class="box" id="box_birthday:1">
<h3>Geburtstagskinder</h3>
{dynamic}
{if $visitor->hasRight('FEATURE_BOX_REARRANGEMENT')}
<a href="{box_functions box=birthday close=true}{*/index.php?dest=box&amp;bname=birthday&amp;method=close*}" class="icon iconClose" title="Box schließen" id="birthday:1_close"><span>x</span></a>
{if !$box_birthday_minimized}
<a href="{box_functions box=birthday minimize=true}{*/index.php?dest=box&amp;bname=birthday&amp;method=minimize*}" class="icon iconMinimize" id="birthday:1_collapse" title="Box minimieren"><span>_</span></a>
{else}
<a href="{box_functions box=birthday maximize=true}{*/index.php?dest=box&amp;bname=birthday&amp;method=maximize*}" class="icon iconMaximize" id="birthday:1_collapse" title="Box maximieren"><span>O</span></a>
{/if}{* end minimized *}
{/if}{* end rights check *}
{/dynamic}
{/if}{* end ajax *}

{if !$box_birthday_minimized}
{if $box_birthday_users}
<ul class="boxcontent vertical birthdaylist">
{foreach from=$box_birthday_users item=user}
    <li>
    <a href="{user_info_url user=$user}" title="Herzlichen Glückwunsch">
    {$user->getAge()} {$user->getUsername()|truncate:16}
    </a></li>
{/foreach}
</ul>
{/if}
{/if}

{if !$box_birthday_ajax}
</div>
{/if}
