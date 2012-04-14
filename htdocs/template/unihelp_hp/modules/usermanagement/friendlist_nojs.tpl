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

*}{* $Id: friendlist_nojs.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{* <h2 id="pagename">{$user->getUsername()} &ndash; Persönliche Einstellungen {if $admin_mode}<span class="adminNote">(ADMIN)</span>{/if}</h2> *}

{if $admin_mode}
<span class="adminNote">(ADMIN)</span>
{else}
<div class="shadow">
<div><h3>Hilfe</h3>
{if $extended_categories}
    <ul class="bulleted">
  		<li>Du kannst nun die Freunde, die Dir besonders wichtig sind, in bestimmte Kategorien einteilen.</li>
		<li>Im Drop-Down-Menü hinter den Usernamen kannst Du die Kategorie auswählen.</li>
		<li>Auf die Ignoreliste kannst Du User setzen, von denen Du keine Nachrichten erhalten möchtest.</li>
	</ul>	
{else}
  <ul class="bulleted">
  		<li>Hier sind alle Deine Freunde aufgelistet. Wenn Du Deine Freunde kategorisieren möchtest, kannst Du diese Funktion unter "Features" freischalten. </li>
  		<li>Auf die Ignoreliste kannst Du User setzen, von denen Du keine Nachrichten erhalten möchtest.</li>
	</ul>	
{/if}
</div></div>
{/if} {* end if admin mode *}

{* mode internal tab navigation *}
{include file="modules/usermanagement/navigation.tpl" usermanagement_tabpanemode="friends"}
<div class="shadow"><div>
<br class="clear" />

{if $extended_categories}
<form action="{user_management_url friends=$user}" method="post">
  <input type="hidden" name="saveFriend" value="1" />
  <input type="hidden" name="nojs" value="1" />
  <fieldset><legend>Freunde gruppieren und erg&auml;nzen</legend>
{strip}
  {foreach from=$user_friends item=friend name=friends}
    {if $friend->getFriendType()!="Ignore"}
    <label for="user_{$friend->id}">{$friend->getUsername()}</label>
    <select name="user_{$friend->id}" id="user_{$friend->id}">
      <optgroup label="Neutrale und positive Kategorien">
        <option value="Normal" {if $friend->getFriendType()=="Normal"}selected="selected"{/if}>ohne Einordnung</option>
        <option value="Love" {if $friend->getFriendType()=="Love"}selected="selected"{/if}>Liebe</option>
        <option value="Family" {if $friend->getFriendType()=="Family"}selected="selected"{/if}>aus der Familie</option>
        <option value="Friend" {if $friend->getFriendType()=="Friend"}selected="selected"{/if}>{if $friend->getGender()!="f"}Freund{else}Freundin{/if}</option>
      </optgroup>
      {*<optgroup label="Negative Kategorien">
        <option value="Ignore" {if $friend->getFriendType()=="Ignore"}selected="selected"{/if}>auf der Ignore-Liste</option>
      </optgroup>*}
    </select>[[help.friendlist.types]]
    &nbsp;<a href="{user_management_url friends=$user}?nojs=1&amp;delFriend=1&amp;friendId={$friend->id}">von der Freundesliste l&ouml;schen</a><br />
   {/if}
  {foreachelse}
    Deine Freundesliste ist noch leer!
  {/foreach}
  <input type="submit" value="&Auml;ndern" />
  {/strip}
  </fieldset>
</form>
{else}
<ul>
{foreach from=$user_friends item=friend name=friends}
	{if $friend->getFriendType()!="Ignore"}
    	<li>{$friend->getUsername()}
    	&nbsp;<a href="{user_management_url friends=$user}?nojs=1&amp;delFriend=1&amp;friendId={$friend->id}">von der Freundesliste l&ouml;schen</a>
    	</li>
    {/if}	
{foreachelse}
    Deine Freundesliste ist noch leer!
{/foreach}
</ul>
{/if}

<form action="{user_management_url friends=$user}" method="post" id="searchResults">
  <input type="hidden" name="searchFriend" value="1" />
  <input type="hidden" name="nojs" value="1" />
  <fieldset><legend>Neue Freunde</legend>
    <label for="username_search2">Username:</label>
    {* #username_search is already used for JS Behaviour *}
    {* #username_search is used in user search in left column of site *}
    <input type="text" name="username_search" id="username_search2" size="15" value="{$newSearchFriend}" />
    <input type="submit" value="Suchen" title="Einmal klicken um die Suche zu starten" /><br />
    
    {if $newFriendList}
    <h4>Suchergebnisse</h4>
    <ul>
    {* newFriends is an array itsself; first part is the user model, second part
        a boolean, whether user is already on the friendlist *}
    {foreach from=$newFriendList item=newFriend}
        <li>
	    {if $newFriend[1] == 1}{$newFriend[0]->getUsername()} ist schon auf Deiner Freundesliste.
    	{elseif $newFriend[1] == 2}
    		<img src="/images/icons/status_busy.png" alt="aud Ignore" title="Hat Dich auf der Ignoreliste" />
    		{$newFriend[0]->getUsername()} &mdash; {$smarty.const.ERR_ON_IGNORELIST}
    	{elseif $newFriend[1] == 3}{$newFriend[0]->getUsername()} ist auf Deiner Ignoreliste.
	    {elseif $newFriend[0]->equals($visitor)}{$newFriend[0]->getUsername()} &mdash; {$smarty.const.ERR_FRIENDLIST_SELF}
        {else}{$newFriend[0]->getUsername()} <a href="{user_management_url friends=$user}?nojs=1&amp;addFriend=1&amp;friendId={$newFriend[0]->id}" title="{$newFriend[0]->getUsername()} zu Deinem Freund machen!">zur Freundesliste hinzuf&uuml;gen</a>{/if}
        </li>
    {/foreach}
    </ul>
    {/if}
  </fieldset>
</form>

{if $visitor->hasRight('FEATURE_REVERSE_FRIENDLIST')}
    <a href="{user_info_url user=$user reverseFriendlist=true}" style="display: block; padding: 1em">Wer hat mich auf der Freundesliste?</a>
{/if}

</div></div>

{assign var="nojs" value="1" }
{include file="modules/usermanagement/friendlist_foe.tpl"}
