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

*}{* $Id: birthday.tpl 5178 2007-08-23 16:06:02Z trehn $ *}
{if !$box_birthday_personal_ajax}
<div class="box" id="box_birthday_personal:1">
<h3>nächste Geburtstage</h3>
{dynamic}
{if $visitor->hasRight('FEATURE_BOX_REARRANGEMENT')}
<a href="{box_functions box=birthday_personal close=true}{*/index.php?dest=box&amp;bname=birthday&amp;method=close*}" class="icon iconClose" title="Box schließen" id="birthday_personal:1_close"><span>x</span></a>
{if !$box_birthday_personal_minimized}
<a href="{box_functions box=birthday_personal minimize=true}{*/index.php?dest=box&amp;bname=birthday&amp;method=minimize*}" class="icon iconMinimize" id="birthday_personal:1_collapse" title="Box minimieren"><span>_</span></a>
{else}
<a href="{box_functions box=birthday_personal maximize=true}{*/index.php?dest=box&amp;bname=birthday&amp;method=maximize*}" class="icon iconMaximize" id="birthday_personal:1_collapse" title="Box maximieren"><span>O</span></a>
{/if}{* end minimized *}
{/if}{* end rights check *}
{/dynamic}
{/if}{* end ajax *}

{if !$box_birthday_personal_minimized}
{if $box_birthday_personal_users}
<ul class="boxcontent vertical birthdaylist">
{foreach from=$box_birthday_personal_users item=users key=k}
    <li><strong>{$k|unihelp_strftime:"DATEONLY"}</strong></li>
    {foreach from=$users item=user}
	    <li>
	    <a href="{user_info_url user=$user}" title="Herzlichen Glückwunsch">
	    {$user->getAge()} {$user->getUsername()|truncate:16}
	    </a></li>
	{/foreach}    
{/foreach}
</ul>
{/if} {* end if users available *}

<form action="/index.php?dest=box&amp;method=setNotifyPMBefore&amp;bname=birthday_personal&amp;instance={$instance}" method="post">
<p class="boxcontent">
<label for="reminder-days-before">PN-Benachrichtigung</label>
<select id="reminder-days-before" name="reminder">
<option value="-1" {if $box_birthday_personal_users_days == -1}selected="selected"{/if}>gar nicht</option>
<option value="0" {if $box_birthday_personal_users_days == 0}selected="selected"{/if}>am Tag</option>
<option value="1" {if $box_birthday_personal_users_days == 1}selected="selected"{/if}>einen Tag vorher</option>
<option value="2" {if $box_birthday_personal_users_days == 2}selected="selected"{/if}>zwei Tage vorher</option>
<option value="3" {if $box_birthday_personal_users_days == 3}selected="selected"{/if}>drei Tage vorher</option>
</select>
<input type="submit" value="Speichern" />
<br class="clear" />
</p>
</form>
{/if}

{if !$box_birthday_personal_ajax}
</div>
{/if}
