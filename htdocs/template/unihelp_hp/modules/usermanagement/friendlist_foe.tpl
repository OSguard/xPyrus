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

*}<div class="shadow"><div>
<h3>Ignore Liste</h3>

<div id="friends_Ignore" style="border: 1px solid red;">
<h5>Ignoreliste</h5>
	{foreach from=$user_friends item=friend name=friends}
	{if $friend->getFriendType()=="Ignore"}
		<div>
	     	<img width="24" src="{userpic_url tiny=$friend}" alt="{$friend->username}" /> 
			{$friend->username}
			 <a href="{user_management_url friends=$user}?delFoe=1&amp;foeId={$friend->id}{if $nojs}&amp;nojs=1{/if}" title="{$friend->getUsername()} von Deiner Ignoreliste entfernen!">von der Ignoreliste entfernen</a>
		</div>{/if}
	{/foreach}
</div>

<form action="{user_management_url friends=$user}" method="post" class="left">
<fieldset>
<legend>User suchen für Ignoreliste</legend>
    <input type="hidden" name="searchFoe" value="1" />
    {if $nojs}<input type="hidden" name="nojs" value="1" />{/if}
    <label for="username_search2">Username:</label>
    {* #username_search is already used for JS Behaviour *}
    {* #username_search is used in user search in left column of site *}
    <input type="text" name="username_search_foe" id="username_search3" size="15" value="{$newSearchFoe}" />
    <input type="submit" value="Suchen" title="Einmal klicken um die Suche zu starten" />
</fieldset>
</form>
<br class="clear" />
    {if $newFoeList}
    <h4>Suchergebnisse</h4>
    <ul>
    {* newFriends is an array itsself; first part is the user model, second part
        a boolean, whether user is already on the friendlist *}
    {foreach from=$newFoeList item=newFriend}
        <li>
	    {if $newFriend[1] == 3}{$newFriend[0]->getUsername()} ist schon auf Deiner Ignoreliste.
    	{elseif $newFriend[1] == 2}
    		<img src="/images/icons/status_busy.png" alt="aud Ignore" title="Hat Dich auf der Ignoreliste" />{$newFriend[0]->getUsername()} 
    		<a href="{user_management_url friends=$user}?addFoe=1&amp;foeId={$newFriend[0]->id}{if $nojs}&amp;nojs=1{/if}" title="{$newFriend[0]->getUsername()} zu Deiner Ignoreliste hinzufügen!">zur Ignoreliste hinzuf&uuml;gen</a>
	    {elseif $newFriend[0]->equals($visitor)}
	    	{$newFriend[0]->getUsername()} &mdash; {$smarty.const.ERR_FRIENDLIST_SELF}
        {else} {* nojs=1&amp; *}
        	{$newFriend[0]->getUsername()} <a href="{user_management_url friends=$user}?addFoe=1&amp;foeId={$newFriend[0]->id}{if $nojs}&amp;nojs=1{/if}" title="{$newFriend[0]->getUsername()} zu Deiner Ignoreliste hinzufügen!">zur Ignoreliste hinzuf&uuml;gen</a>
        {/if}
        </li>
    {/foreach}
    </ul>
    {/if}

<br class="clear" />
</div></div>

