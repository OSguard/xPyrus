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

*}{* $Id: edit_group.tpl 5807 2008-04-12 21:23:22Z trehn $
    $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/groups/edit_group.tpl $ *}
{* <h2 id="pagename">Admin: {$group->name}</h2> *}

{if $visitor->hasGroupRight('GROUP_INFOPAGE_EDIT', $group->id) || $visitor->hasRight('GROUP_INFOPAGE_ADMIN')}
    <div class="pagenav">
		<a href="{group_info_url group=$group}">Infopage</a>
  	    - <a href="{group_info_url editInfo=$group->id}">edit Infopage</a>  
  	    {if $visitor->hasRight('GROUP_ADMIN')}
  	    - <a href="{admin_url groups=true}">zum Admin von allen Gruppen</a>
  	    {/if}	    
    </div>
{/if}


 <div class="shadow"><div class="nopadding">
	<h3>Mitglieder verwalten</h3>
    <table class="centralTable nopadding" summary="Zeigt die Mitglieder dieser Gruppe und erlaubt Editierungen.">
    {*<caption>User in dieser Gruppe</caption>*}
        <thead>
            <tr><th>Username</th><th colspan="2">M&ouml;gliche Aktionen</th></tr>
        </thead>
	    <tbody>
            {foreach from=$group->getMembers() item=groupMember name=members}   
            <tr>
            <td>{user_info_link user=$groupMember}</td>
            <td><a href="{group_info_url groupId=$group->id remove=$groupMember->id}">aus der Gruppe entfernen</a></td>
            <td><a href="{group_info_url groupId=$group->id rights=$groupMember->id}">Rechte setzen</a></td>
            </tr>
            {/foreach}
        </tbody>
    </table>
	
	<form action="{group_info_url groupToEdit=$group->id}" method="post">
  <fieldset><legend>Neues Mitglied</legend>
    <label for="username_search2">Username:</label>
    {* #username_search is already used for JS Behaviour *}
    {* #username_search is used in user search in left column of site *}
    <input type="text" name="username" id="username_search2" size="15" value="{$newSearchMember}" />
    <input type="submit" value="Suchen" title="Einmal klicken um die Suche zu starten" />
  </fieldset>
</form>

{if $newSearchMember}
  	<fieldset id="results1">
  	<legend>Suchergebnisse</legend>
  	{foreach from=$newMemberList item=user}
	  	<p class="compact" style="height: 100px">
	    		<a href="{user_info_url user=$user}"><img src="{userpic_url tiny=$user}" alt="{$user->getUsername()}" /></a>
	    		<br /><a href="{user_info_url user=$user}">{$user->getUsername()}</a>
	    		<br /><a href="{group_info_url groupId=$group->id add=$user->id}" title="{$user->getUsername()} hinzufügen">
    User aufnehmen</a>
	    </p>
    {/foreach}
    </fieldset>
{/if}

  </div></div>

{if $member}
<div class="shadow"><div class="nopadding">
	<h3>Rechte von {$member->getUsername()} in der Gruppe {$group->name}</h3>
	<form action="/index.php?mod=groups&amp;dest=modul&amp;method=editGroup&amp;groupId={$group->id}&amp;saveGroup=true" method="post">
	<input type="hidden" name="userId" value="{$member->id}"/>
	<input type="hidden" name="groupId" value="{$group->id}"/>
		<table class="centralTable" summary="Zeigt die Rechte eines Users in der gew&auml;hlten Gruppen und bietet M&ouml;glichkeiten, sie zu &auml;ndern">
		{*<caption>Rechte von {$member->getUsername()} in der Gruppe {$group->name}</caption>*}
            <thead>
                <tr><th>Status</th><th>Recht</th><th>Beschreibung</th></tr>
            </thead>
            <tbody>
            {foreach from=$groupRights item=groupRight}
                {assign var="rid" value=$groupRight->id}
                {assign var="gid" value=$group->id}
                <tr>
                    <td><input id="granted{$groupRight->id}" type="checkbox" name="granted[]" value="{$groupRight->id}" {if $groupUserRights[$gid][$rid] === true}checked="checked"{/if}/></td>
                    <td><label for="granted{$groupRight->id}">{$groupRight->getName()}:</label></td>
                    <td>{$groupRight->getDescription()}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
 	<input type="submit" name="submit" title="Rechte setzen per Klick" />
 	<br class="clear" />
	</form>
</div></div>	
{else}
<div class="shadow"><div id="groupLogoEdit">
<h3>Logo bearbeiten</h3>

<img id="logo" src="{$group->logoUrl|default:"/images/kegel_group.png"}" alt="Logo" />
<br class="clear" />
<form enctype="multipart/form-data" action="{group_info_url groupToEdit=$group->id}" method="post">
	<label class="left" for="logo_picture">Gruppen Logo:</label>
	<input name="logo_picture" id="logo_picture" size="30" type="file" />
	<input type="submit" name="save" value="Hochladen" id="groupLogoUpload" />
</form>
<form enctype="multipart/form-data" action="{group_info_url groupToEdit=$group->id}" method="post">
				  <input name="logo_delete" type="submit" value="Logo löschen" />
</form>				  
<br class="clear" />
				  
</div></div>
{/if}{* $member *}

{if $rightsHaveBeenSet}
<div class="shadow"><div>
<h3>Gruppenrechte bearbeiten</h3>
<p>Die Rechte wurden erfolgreich bearbeitet.</p>
</div></div>
{/if}
