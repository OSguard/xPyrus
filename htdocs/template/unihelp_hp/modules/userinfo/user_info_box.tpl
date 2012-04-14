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

*}<dl>
    {* restrict online status visibility to logged in users
       TODO: make this configurable   (linap, 24.05.2007) *}
    <dt>Username</dt><dd>{$userinfo_user->getUsername()}&nbsp;{if $visitor->isLoggedIn() && $userinfo_user->isLoggedIn()}ist <strong>ONLINE</strong>{/if}</dd>
    <dt>Geschlecht</dt> <dd>{$gender}</dd>
    {if $userinfo_user->hasAge()}
    <dt>Alter</dt><dd>{$userinfo_user->getAge()} {if $userinfo_user->hasBirthdate()}({$userinfo_user->getBirthdate()}){/if}</dd>
    {/if}
    {assign var="nationality" value=$userinfo_user->getNationality()}
    {if $nationality->getName() != 'unbekannt' and $nationality->getName() != 'unknown'}
      <dt>Nationalität</dt><dd><img src="/images/flags/{$nationality->getIsoCode()|lower}.png" alt="" /> <em>{$nationality->getNationality()}</em></dd>
    {/if}{* end if nationality *}
    {if $userinfo_user->getFlirtStatus()!='none'}
    <dt>Status</dt><dd>
      {user_status user=$userinfo_user}
    </dd>
    {/if}{* end if flirtstat  *}
	  <dt>Hochschule</dt><dd>{$userinfo_user->getUniNameShort()}</dd>
	{foreach from=$userinfo_user->getStudyPathsObj() item=path name=path}
	  {* first entry in array is primary study path *}
	  {if $smarty.foreach.path.first}
		{assign var="label" value="Hauptstudiengang"}
	  {else}
		{assign var="label" value="Zweitstudiengang"}
	  {/if}
	  <dt>{$label}</dt><dd title="{$path->getName()}">{$path->getName()|truncate:20:"...":true}&nbsp;({$path->getNameShort()})</dd>
	{/foreach}
	{* Group membership *}
	{foreach item=group name=groups from=$userinfo_user->getGroupMembership()}
			<dt>{if $group->title!='group'}{$group->title}{else}Organisation{/if}</dt>
			<dd>
        		{group_info_link group=$group}
        	</dd>       
    {/foreach}
    {if $userinfo_user->getHomepage()}
      <dt>Homepage</dt><dd>
	     <a target="_blank" href="{$userinfo_user->getHomepage()|escape:"html"}" title="Zur Homepage des Users">{$userinfo_user->getHomepage()|escape:"html"|truncate:25:"...":true}</a>
	  </dd>
	{/if}

    
    <dt>Online-Aktivität</dt><dd>{$userinfo_user->getActivityIndex()|string_format:"%.2f"}</dd>
    <dt><strong>Punkte</strong></dt><dd><strong>{$userinfo_user->getPoints()|default:0}</strong></dd>
  	{if $userinfo_user->hasPointsEconomic()}<dt><em>W-Punkte</em></dt><dd><em>{$userinfo_user->getPointsEconomic()|default:0}</em></dd>{/if}
	</dl>
	<br class="clear" />
