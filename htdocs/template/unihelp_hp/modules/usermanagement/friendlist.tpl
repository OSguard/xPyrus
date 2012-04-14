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
{* <h2 id="pagename">{$user->getUsername()} &ndash; Persönliche Einstellungen</h2>
 *}

{if $ie6}
{include file="modules/usermanagement/friendlist_nojs.tpl"}
{else}
	{if $admin_mode}
	<span class="adminNote">(ADMIN)</span>
	<a href="{admin_url user=$user}">zurück zum Adminbereich</a>
	{else}
	<div class="shadow">
	<div><h3>Hilfe</h3>
	{* this page should only be available, if user may categorize friends, so we need no if here *}
	    <ul class="bulleted">
	  		<li>Du kannst nun die Freunde, die Dir besonders wichtig sind, in bestimmte Kategorien einteilen.</li>
			<li>Hier kannst Du Deine Freunde direkt in den Kasten der jeweiligen Kategorie ziehen oder löschen.</li>
			<li>Auf die Ignoreliste kannst Du User setzen, von denen Du keine Nachrichten erhalten möchtest.</li>
		</ul>	
	</div></div>
	{/if} {* end if admin mode *}

	{* mode internal tab navigation *}
	{include file="modules/usermanagement/navigation.tpl" usermanagement_tabpanemode="friends"}
	<div class="shadow" id="friendsManagement"><div>
	<br class="clear" />
	<noscript><p>Du solltest JavaScript aktivieren, um unsere coolen Ajax-Features zu nutzen ;-) <a href="?nojs=1">Zur Version ohne Javascript</a></p></noscript>
	<table id="friends_table" summary="Eine Übersicht über die Kategorisierung Deiner Freunde">
	<thead>
		<tr>
			<th>Ohne Einteilung</th><th><img src="/images/symbols/friend_friendship.gif" alt="Freundschaft" title="Freundschaft" /></th><th><img src="/images/symbols/friend_family.gif" alt="Familie" title="Familie" /></th><th><img src="/images/symbols/friend_love.gif" alt="Liebe" title="Liebe" /></th>
		</tr>
	</thead>
	<tbody>
		<tr>
		<td id="friends_Normal" class="friend_drop">
		{foreach from=$user_friends item=friend name=friends}
		{if $friend->getFriendType()=="Normal"}
			<div id="friend_{$friend->id}" class="friend">
		     	<img width="24" src="{userpic_url tiny=$friend}" alt="{$friend->getUsername()}" /> 
				{$friend->getUsername()}
			</div>{/if}
		{/foreach}
		</td>
		<td id="friends_Friend" class="friend_drop">
		{foreach from=$user_friends item=friend name=friends}
		{if $friend->getFriendType()=="Friend"}
			<div id="friend_{$friend->id}" class="friend">
		     	<img width="24" src="{userpic_url tiny=$friend}" alt="{$friend->getUsername()}" /> 
				{$friend->getUsername()}
			</div>{/if}
		{/foreach}
		</td>
		<td id="friends_Family" class="friend_drop">
		{foreach from=$user_friends item=friend name=friends}
		{if $friend->getFriendType()=="Family"}
			<div id="friend_{$friend->id}" class="friend">
		     	<img width="24" src="{userpic_url tiny=$friend}" alt="{$friend->getUsername()}" /> 
				{$friend->getUsername()}
			</div>{/if}
		{/foreach}
		</td>
		<td id="friends_Love" class="friend_drop">
		{foreach from=$user_friends item=friend name=friends}
		{if $friend->getFriendType()=="Love"}
			<div id="friend_{$friend->id}" class="friend">
		     	<img width="24" src="{userpic_url tiny=$friend}" alt="{$friend->getUsername()}" /> 
				{$friend->getUsername()}
			</div>{/if}
		{/foreach}
		</td>
		</tr>
	</tbody>
	</table>

	{if $visitor->hasRight('FEATURE_REVERSE_FRIENDLIST')}
	    <a href="{user_info_url user=$user reverseFriendlist=true}" style="display: block; padding: 1em">Wer hat mich auf der Freundesliste?</a>
	{/if}

	<form action="{user_management_url friends=$user}" method="post" class="left">
	    <input type="hidden" name="searchFriend" value="1" />
	    <label for="username_search2">Neue Freunde suchen</label>
	    {* #username_search is already used for JS Behaviour *}
	    {* #username_search is used in user search in left column of site *}
	    <input type="text" name="username_search" id="username_search2" size="15" value="{$newSearchFriend}" />
	    <input type="submit" value="Suchen" title="Einmal klicken um die Suche zu starten" />
	</form>

    {* ajax_submit is used for Behaviour *}
	<form action="#" method="post" id="ajax_submit" class="right">
	    <input type="submit" name="submit" value="Speichern"/>
	</form>
	<span id="ajax_status" class="right">&nbsp;</span>

	<br class="clear" />

	<div>
	{if $newFriendList}
	<div class="left" id="searchResults">
	<h4>Suchergebnisse</h4>
	  <ul>
	  {* newFriends is an array itsself; first part is the user model, second part
	     a boolean, whether user is already on the friendlist *}
	  {foreach from=$newFriendList item=newFriend}
	    <li>  
	    {if $newFriend[1] == 1}{$newFriend[0]->getUsername()} ist schon auf Deiner Freundesliste.
	    {elseif $newFriend[1] == 2}<img src="/images/icons/status_busy.png" alt="aud Ignore" />{$newFriend[0]->getUsername()} &mdash; {$smarty.const.ERR_ON_IGNORELIST}
	    {elseif $newFriend[1] == 3}{$newFriend[0]->getUsername()} ist auf Deiner Ignoreliste.
	    {elseif $newFriend[0]->equals($visitor)}{$newFriend[0]->getUsername()} &mdash; {$smarty.const.ERR_FRIENDLIST_SELF}
	    {else}
		    <div id="friend_{$newFriend[0]->id}" class="friend">
		     	<img width="24" src="{userpic_url tiny=$newFriend[0]}" alt="{$newFriend[0]->getUsername()}" /> 
				{$newFriend[0]->getUsername()}
			</div>
		{/if}
	    </li>
	  {/foreach}
	  </ul>
	</div>
	{/if}

	{* <div id="friends_Delete" style="min-height:100px; min-width: 100px; background-color: #5264f9; float: left;" class="friend_drop">*}
	<div id="friends_Delete" class="friend_drop right">
	<h5>Mülleimer</h5>
	</div>
	{*<div id="friends_Ignore" class="friend_drop right">
	<h5>Ignoreliste</h5>
		{foreach from=$user_friends item=friend name=friends}
		{if $friend->getFriendType()=="Ignore"}
			<div id="friend_{$friend->id}" class="friend">
		     	<img width="24" src="{userpic_url tiny=$friend}" alt="{$friend->getUsername()}" /> 
				{$friend->getUsername()}
			</div>{/if}
		{/foreach}
	</div>*}
	</div>

	<br class="clear" />

	{* need to place <script> here in order to affect all prior div.friend tags *}
	{literal}
	<script type="text/javascript" language="javascript" charset="utf-8">
	$$("div.friend").each(function(element)  
	 {
	    new Draggable(element,{scroll:window,revert: true});
	 }
	);

	var prepareDrop = function(delement) {
	Droppables.add(delement.getAttribute('id'),{
	    accept:['friend'],
	    onDrop:function(dragable,droppable) {
	        var added = false;
	        try {
	            var elemBeforeInsert = $A(droppable.childNodes).detect(function(element){ 
	                if (typeof(element.nodeValue) == 'object' && element.nodeName=='DIV') {
	                    return (element.lastChild.data.toLowerCase() > dragable.lastChild.data.toLowerCase());
	                }
	                return false;
	            });
	            droppable.insertBefore(dragable,elemBeforeInsert);
	            added = true;
	        } catch(e) {
	        }
	        if(!added) {droppable.appendChild(dragable); }
	        Element.setStyle(dragable, { left : "0px" });
	    }
	})
	};

	$$("td.friend_drop").each(prepareDrop);
	$$("div.friend_drop").each(prepareDrop);

    function _sendSubmit() {
        $('ajax_submit').disabled = true;
        $('ajax_status').innerHTML = "Sende...";

        // serialize friends and their categories
        var friends = { Normal: Array(), Love: Array(), Family: Array(), Friend: Array(), Delete: Array(), Ignore: Array() };
        $$("div.friend").each(function(element)  
         {
            if (element.parentNode.nodeName == 'TD' || element.parentNode.nodeName == 'DIV') {
                friends[element.parentNode.getAttribute('id').substr(8)].push(element.getAttribute('id').substr(7));
            }
         }
        );

        var str = "{";
        for (var type in friends) {
            str += '"' + type + '" : [' + friends[type] + '],';
        }
        str = str.substr(0,str.length-1) + "}";
        
        var url = '/index.php'
        var pars = {
            method:      'ajaxFriendlist',
            mod:         'usermanagement',
            view:        'ajax',
            friends:     str
        }
        
        var myAjaxRequest = new Ajax.Request(url, {
            parameters: $H(pars).toQueryString(),
            onSuccess : function(request) {
                var status = request.responseText;
                if (status == '0') {
                    $('ajax_status').innerHTML = "Speichern erfolgreich!";
                    $('ajax_submit').disabled = false;
                    
                    // remove deleted friends
                    $$('div#friends_Delete div').each(Effect.Fade);
                } else {
                    $('ajax_status').innerHTML = "Die fehlen die Rechte für diese Aktion";
                    $('ajax_submit').disabled = false;
                }
            },
            onFailure: function(request) {
                $('ajax_status').innerHTML = "Fehler beim Speichern!";
                $('ajax_submit').disabled = false;
            }
        });
        
        /* don't use default behavoir */
        return false;
    }
	</script>
	{/literal}

	</div>
	</div>{* closing box ajax spielwiese  *}

	{include file="modules/usermanagement/friendlist_foe.tpl"}
{/if}
