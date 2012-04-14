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

*}{* $Id: friendlist.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{if !$box_friendslist_ajax}
<div class="box" id="box_friendslist:1">
<h3>Freundesliste</h3>
{dynamic}
{if $visitor->hasRight('FEATURE_BOX_REARRANGEMENT')}
<a href="{box_functions box=friendslist close=true}{*/index.php?dest=box&amp;bname=friendslist&amp;method=close*}" class="icon iconClose" title="Box schließen" id="friendslist:1_close"><span>x</span></a>
{if !$box_friendslist_minimized}
<a href="{box_functions box=friendslist minimize=true}{*/index.php?dest=box&amp;bname=friendslist&amp;method=minimize*}" class="icon iconMinimize" id="friendslist:1_collapse" title="Box minimieren"><span>_</span></a>
{else}
<a href="{box_functions box=friendslist maximize=true}{*/index.php?dest=box&amp;bname=friendslist&amp;method=maximize*}" class="icon iconMaximize" id="friendslist:1_collapse" title="Box maximieren"><span>O</span></a>
{/if}{* end minimized *}
{/if}{* end rights check *}
{/dynamic}
{/if}{* end ajax *}

{if !$box_friendslist_minimized}
    {* show friends *}
    {if $box_friendlist_users neq null}
        <ul class="boxcontent vertical">
            {if $box_friendlist_users_adv}

	            	{if $box_friendlist_users_Love}
	            		<li>Liebe:</li>
	            		{foreach from=$box_friendlist_users_Love item=user}
			            <li style="margin: 0 0 4px 0">
			                <a href="{user_info_url user=$user}"><img src="{if $user->isExternal()}{user_online_url user=$user}{else}/images/symbols/{if $user->isLoggedIn()}online{else}offline{/if}.gif{/if}" alt="Der Online-Status von {$user->username}" style="margin-right: .5em;"/>
			                 	{$user->username|truncate:12:"...":true}</a>
			            </li>
		            {/foreach}
	            	{/if}
	            	{if $box_friendlist_users_Friend}
	            		<li style="border-top: 1px solid #cfcfcf;">Freunde:</li>
	            		{foreach from=$box_friendlist_users_Friend item=user}
			            <li style="margin: 0 0 4px 0">
			                <a href="{user_info_url user=$user}"><img src="{if $user->isExternal()}{user_online_url user=$user}{else}/images/symbols/{if $user->isLoggedIn()}online{else}offline{/if}.gif{/if}" alt="Der Online-Status von {$user->username}" style="margin-right: .5em;"/>
			                 	{$user->username|truncate:12:"...":true}</a>
			            </li>
		            {/foreach}
	            	{/if}
	            	{if $box_friendlist_users_Family}
	            		<li style="border-top: 1px solid #cfcfcf;">Familie:</li>
	            		{foreach from=$box_friendlist_users_Family item=user}
			            <li style="margin: 0 0 4px 0">
			                <a href="{user_info_url user=$user}"><img src="{if $user->isExternal()}{user_online_url user=$user}{else}/images/symbols/{if $user->isLoggedIn()}online{else}offline{/if}.gif{/if}" alt="Der Online-Status von {$user->username}" style="margin-right: .5em;"/>
			                 	{$user->username|truncate:12:"...":true}</a>
			            </li>
		            {/foreach}
	            	{/if}
	            	{if $box_friendlist_users_Normal}
	            		<li style="border-top: 1px solid #cfcfcf;">Normal:</li>
	            		{foreach from=$box_friendlist_users_Normal item=user}
			            <li style="margin: 0 0 4px 0">
			                <a href="{user_info_url user=$user}"><img src="{if $user->isExternal()}{user_online_url user=$user}{else}/images/symbols/{if $user->isLoggedIn()}online{else}offline{/if}.gif{/if}" alt="Der Online-Status von {$user->username}" style="margin-right: .5em;"/>
			                 	{$user->username|truncate:12:"...":true}</a>
			            </li>
		            {/foreach}
	            	{/if}
            	
            {else}
	            {foreach from=$box_friendlist_users item=user}
	            <li style="margin: 0 0 4px 0">
	                <a href="{user_info_url user=$user}"><img src="{if $user->isExternal()}{user_online_url user=$user}{else}/images/symbols/{if $user->isLoggedIn()}online{else}offline{/if}.gif{/if}" alt="Der Online-Status von {$user->getUsername()}" style="margin-right: .5em;"/>
	                 	{$user->getUsername()|truncate:12:"...":true}</a>
	            </li>
	            {/foreach}
            {/if}
        </ul>
    {else}
        {* in case no friend is on the list *}
        <p class="boxcontent">Deine Freundesliste ist noch leer.</p>
    {/if}
    <p class="boxcontent"><a href="/user/{$visitor->getUsername()|escape:'url'}/friendlist" style="display: block; text-align: right; border:0px solid red;">Einstellungen</a></p>
    {if $visitor->hasRight('FEATURE_REVERSE_FRIENDLIST')}
        <p class="boxcontent"><a href="{user_info_url user=$visitor reverseFriendlist=true}" style="display: block;">Wer hat mich auf der Freundesliste?</a></p>
    {/if}
    {if $visitor->hasRight('PM_SENDTO_FRIENDS')}
    	<p class="boxcontent"><a href="{pm_url new=true receivers="[friends]" }">eine PM an alle Freunde schreiben</a></p>
    {/if}
{/if}{* box minimized *}

{if !$box_friendslist_ajax}
</div>
{/if}
