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

*}{* $Id: user_logout.tpl 5807 2008-04-12 21:23:22Z trehn $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/boxes/user_logout.tpl $ *}
<div class="box">
<h3>Userinformationen</h3>
{* Display Avatar and key-Information *}
  <div class="boxcontent">
  <a href="{user_info_url user=$boxes_user_login_user}">
	<img src="{userpic_url tiny=$boxes_user_login_user}" alt="{$boxes_user_login_user->getUsername()}" style=" float: left; margin: 2px; border: #cfcfcf 1px solid; padding: 1px; margin-right: .5em;" />
  </a><strong>{user_info_link user=$boxes_user_login_user}</strong>
{if $boxes_user_login_user->isRegularLocalUser()}
    {strip}
  <ul style="list-style: none; float: right;">
  	<li style="text-align: right;">{$boxes_user_login_user->getPoints()} Punkte</li>
  	<li style="text-align: right;">
  	{if $boxes_user_login_user->getGBEntriesUnread() == 0}
		<a href="{user_info_url user=$boxes_user_login_user}#guestbook_anchor" title="Direkt zum Profil um die Eintr&auml;ge zu lesen">
		{if $boxes_user_login_user->getGBEntries() == 1}
			{$boxes_user_login_user->getGBEntries()}&nbsp;GB-Eintrag
    	{else}
			{$boxes_user_login_user->getGBEntries()}&nbsp;GB-Eintr&auml;ge
		{/if}
		</a>
	{else}
		<strong><a href="{user_info_url user=$boxes_user_login_user}#guestbook_anchor" title="Direkt zum Profil um die neuen Eintr&auml;ge zu lesen">{$boxes_user_login_user->getGBEntriesUnread()} x <img src="/images/symbols/gbook.gif" alt="neue G&auml;stebucheintr&auml;ge" /></a></strong>
	{/if}
	</li>
	{if $boxes_user_login_user->getPMsUnread() > 0}
		<li style="text-align: right;">
			<strong>
				<a href="{pm_url}" title="Pers&ouml;nliche Nachrichten abrufen">
					{$boxes_user_login_user->getPMsUnread()} x <img src="/images/symbols/letter.gif" alt="PN" />
				</a>
			</strong>
		</li>
	{else}
		<li style="text-align: right;">
			<a href="{pm_url }">{$visitor->getPMs()} PN</a>
		</li>
	{/if}
  </ul>
   <br style="clear: both" />
  {/strip}
{/if}{* end if local user *}
  </div>

{* main navigation to get into user settings *}
{strip}
<ul class="boxcontent usersetting">
{if $boxes_user_login_user->hasRight('PROFILE_MODIFY')}
	<li><a href="{user_management_url profile=$boxes_user_login_user}">Einstellungen</a></li>
{/if}
	<li><a href="{user_management_url logout=$boxes_user_login_user}">LogOut</a></li>
</ul>
<div class="boxcontent"><br style="clear: both" /></div>

{/strip}

</div>
