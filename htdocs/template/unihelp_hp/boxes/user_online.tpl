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

*}{* $Id: user_online.tpl 6210 2008-07-25 17:29:44Z trehn $ *}

{if !$box_user_online_ajax}
<div class="box" id="box_user_online:1">
<h3>User online</h3>
{dynamic}
{if $visitor->hasRight('FEATURE_BOX_REARRANGEMENT')}
<a href="{box_functions box=user_online close=true}{*/index.php?dest=box&amp;bname=user_online&amp;method=close*}" class="icon iconClose" title="Box schließen" id="user_online:{$instance}_close"><span>x</span></a>
{if !$box_user_online_minimized}
<a href="{box_functions box=user_online minimize=true}{*/index.php?dest=box&amp;bname=user_online&amp;method=minimize*}" class="icon iconMinimize" id="user_online:1_collapse" title="Box minimieren"><span>_</span></a>
{else}
<a href="{box_functions box=user_online maximize=true}{*/index.php?dest=box&amp;bname=user_online&amp;method=maximize*}" class="icon iconMaximize" id="user_online:1_collapse" title="Box maximieren"><span>O</span></a>
{/if}{* end minimized *}
{/if}{* end rights check *}
{/dynamic}
{/if}{* end ajax *}

{if !$box_user_online_minimized}
	<p class="boxcontent" style="padding-top: 10px; text-align:center;"><strong id="online_number">{$box_user_online_users_number}</strong> User online
	{if $visitor_logged_in}
        <br /><a href="{index_url chat=1}" target="_chat" title="Den Chat betreten, neues Fenster">zum Chat</a></p>
		{strip}
		<ul class="boxcontent sort-user" id="user-online-sort-links">
			<li><a class="first" href="{user_online_box_url sortByAge=true}" title="sortiere nach Alter">AG</a></li>
			<li><a href="{user_online_box_url sortByGender=true}" title="sortiere nach Geschlecht">GE</a></li>
			<li><a href="{user_online_box_url sortByStatus=true}" title="sortiere nach Status">ST</a></li>
			<li><a href="{user_online_box_url sortByCourse=true}" title="sortiere nach Studiengang">SG</a></li>
			<li><a href="{user_online_box_url sortByUsername=true}" title="sortiere nach User-Namen">US</a></li>
		</ul>
		{/strip}
		
		{if $box_user_online_users_number != 0}
			{include file="boxes/user_online_list.tpl"}
		<span class="boxcontent" style="padding: 0 10px;">mit {$box_user_online_guests_number} {if $box_user_online_guests_number==1}Gast{else}Gästen{/if}</span>
		{else}
		<p class="boxcontent">Es spielen alle verstecken!</p>
		{/if}
	{/if}
{/if}{* box minimized *}

{if !$box_user_online_ajax}
</div>
{/if}
