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

*}{* $Id: overview.tpl 5895 2008-05-03 15:38:20Z schnueptus $ *}
{* $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/admin/overview.tpl $ *}
<div class="shadow"><div>
<h3>[[local.local.project_name]] {$local_city->getName()} administrieren</h3>
<noscript>
FÜR DEN ADMIN-BEREICH MUSS JAVASCRIPT AKTIVIERT SEIN!!1!
</noscript>
<fieldset>
{if $visitor->hasRight('PROFILE_ADMIN')}
<legend>User </legend>
<p>
	<form action="{admin_url}" method="post">
	  	<label for="username2" >Username oder EMail</label>
	  	<input type="text" name="username" id="username2" value="{$userSearch}" />
	  	<br />
		<input type="submit" name="showRights" value="Suchen"/>
  	</form>
</p><br class="clear" />
	{foreach from=$users item=user2 name=results}
    	<p class="compact" style="height: 110px;">
    		<a href="{admin_url user=$user2}">
	    		<img src="{userpic_url tiny=$user2}" alt="{$user2->getUsername()}" /><br/>
	    		{$user2->getUsername()}
	    		{if $user2->getOldUsernames() }
	    			({foreach from=$user2->getOldUsernames() item="nick" name="oldnick"}
	    				{$nick}{if !$smarty.foreach.oldnick.last},{/if}
	    			{/foreach})
	    		{/if}
	    		<br />
	    		{$user2->getFirstName()} {$user2->getLastName()}<br />
	    		{$user2->getPrivateEmail()}
    		</a>
    	</p>
    {/foreach}
</fieldset>

{if $user}
	<fieldset>
	<legend>{$user->getUsername()}</legend>
	<img src="{userpic_url fancy=$user}" alt="{$user->getUsername()}" style="float:left" />
	<ul style="float:right; width: 60%">
		<li><strong>Username: {user_info_link user=$user}</strong></li>
		{if $user->getOldUsernames() }
			<li>bisherige Usernamen:
			{foreach from=$user->getOldUsernames() item="nick" name="oldnick"}
				{$nick}{if !$smarty.foreach.oldnick.last},{/if}
			{/foreach}
			</li>
		{/if}
		<li>Name: {$user->getFirstName()} {$user->getLastName()}</li>
	    <li>Email: {$user->getPrivateEmail()}</li>
	    <li>Uni-Email: {$user->getUniEmail()}</li>
	    <li>Geburtstag: {$user->getBirthdate()}</li>
	    {foreach from=$user->getStudyPathsObj() item=path name=path}
		  {* first entry in array is primary study path *}
		  {if $smarty.foreach.path.first}
			{assign var="label" value="Hauptstudiengang"}
		  {else}
			{assign var="label" value="Zweitstudiengang"}
		  {/if}
		  <li>{$label}: {$path->getName()}&nbsp;({$path->getNameShort()})</li>
		{/foreach}
		<li>angemeldet seit: {$user->getFirstLogin()|unihelp_strftime}</li>
		<li>letztes Login: {$user->getLastLogin()|unihelp_strftime}</li>
	</ul>
	<br />
	<ul style="float:right; width: 60%; margin-top: 10px;">
		<li>
			<a href="{admin_url profile=$user}" title="">Profil ändern</a>
		</li>
		<li>
			<a href="{admin_url contactData=$user}" title="">Kontaktdaten ändern</a>
		</li>
		<li>
			<a href="{admin_url privacy=$user}" title="">Privatsphäre ändern</a>
		</li>
		<li>
			<a href="{admin_url courses=$user}" title="">Studium &amp; Fächer ändern</a>
		</li>
		<li>
			<a href="{admin_url friendlist=$user}" title="">Freundesliste ändern</a>
		</li>
		<li>
			<a href="{admin_url features=$user}" title="">Features ändern</a>
		</li>
		<li>
			<a href="{admin_url boxes=$user}" title="">Boxen ändern</a>
		</li>
		{if $visitor->hasRight('USER_RIGHT_ADMIN')}
		<li>
			<a href="{admin_url rights=$user}" title="">Rechte setzen</a>
		</li>
		{/if}
		{if $visitor->hasRight('USER_WARNING_ADD')}
		<li>
			<a href="{admin_url warnings=$user}" title="">Userverwarnungen setzen</a>
		</li>
		{/if}
		{if $visitor->hasRight('PM_SEND_AS_SYSTEM')}
		<li>
			<a href="{admin_url systemPm=1 targetuser=$user}">PN als System an den User schreiben</a>
		</li>
		{/if}
	<ul>
	</fieldset>
{/if}
{/if}
<!--div class="shadow"><div-->
<fieldset>
<legend>Admin: User </legend>
{if $visitor->hasRight('USER_CREATE')}
    <p><a href="{admin_url newuser=true}">User hinzufügen</a></p>   
    <p><a href="{admin_url newguest=true}">Gast-User hinzufügen</a></p>
{/if}
{if $visitor->hasRight('USER_DELETE')}
    <p><a href="{admin_url purgeusers=true}">Übersicht &ndash; User endgültig löschen</a></p>
{/if}    
{if $visitor->hasRight('PROFILE_ADMIN')}
	<p><a href="/index.php?mod=i_am_god&amp;method=showEmailLog">Emails einsehen</a></p>
{/if}
{if $visitor->hasRight('PROFILE_ADMIN')}
	<p><a href="/index.php?mod=i_am_god&amp;method=showUserWarnings">User-Verwarnungen ansehen</a></p>
{/if}
{if $visitor->hasRight('GB_ENTRY_ADMIN')}	
	<p><a href="{admin_url searchEntries=1}">Big Brother</a></p>
{/if}	
{if $visitor->hasRight('PROFILE_ADMIN')}
	<p><a href="?mod=award">Award vergeben</a></p>
{/if}		
{if $visitor->hasRight('PM_SEND_AS_SYSTEM')}	
	<p><a href="{admin_url systemPm=1 toAll=1}">eine PM an alle User schreiben</a></p>
	<p><a href="{admin_url systemPm=1 toOnline=1}">eine PM an alle User schreiben, die Online sind</a></p>
{/if}
{if $visitor->hasRight('ROLE_ADMIN')}
	<p><a href="{admin_url roles=true}">Rolle bearbeiten</a></p>
{/if}
{if $visitor->hasRight('GROUP_ADMIN')}	
	<p><a href="{admin_url groups=true}">Organisationen bearbeiten</a></p>
{/if}
{if $visitor->hasRight('ACCESS_STATS')}	
	<p><a href="/i_am_god?method=overview&method=showStats">Statistik anzeigen</a></p>
{/if}	
</fieldset>

<fieldset>
<legend>Admin: Studium </legend>
{if $visitor->hasRight('COURSE_ADMIN')}	
	<p><a href="{admin_url coursesEdit=true}{*/index.php?mod=courses&dest=module&method=adminCourse*}">Fächer bearbeiten</a></p>
    <p><a href="{admin_url coursesMerge=true}">Fächer zusammenführen</a></p>
	{*<p><a href="/index.php?mod=courses&dest=module&method=adminUnivisImport">Fächer importieren von Univis (Vorsicht: HACK!, FIXME)</a></p>*}
	<p><a href="/index.php?mod=courses&dest=module&method=adminSubsidies">Unterlagen-Subvention bearbeiten</a></p>
	<p><a href="{*/index.php?mod=i_am_god&dest=module&method=editStudyPaths*}{admin_url studyPaths=true}">Studiengänge bearbeiten</a></p>
{/if}
{if $visitor->hasRight('COURSE_FILE_ADMIN')}	
	<p><a href="{admin_url editFiles=true}">Unterlagen bearbeiten (allgemein)</a></p>
{/if}
{if $visitor->hasRight('TAG_ADMIN')}
	<p><a href="{admin_url tags=true}{*/index.php?mod=i_am_god&dest=module&method=editTag*}">Tags bearbeiten</a></p>
{/if}
</fieldset>

<fieldset>
<legend>Admin: [[local.local.project_name]] System </legend>
{if $visitor->hasRight('FEATURE_ADMIN')}
<p><a href="/index.php?mod=i_am_god&amp;dest=module&amp;method=editFeatures">Features bearbeiten</a></p>
{/if}
{if $visitor->hasRight('POINT_SOURCE_ADMIN')}
<p><a href="/index.php?mod=i_am_god&amp;dest=module&amp;method=editPointSources">Punktequellen und -senken bearbeiten</a></p>
{/if}
{if $visitor->hasRight('SMILEY_ADMIN')}
<p><a href="/index.php?mod=i_am_god&amp;dest=module&amp;method=generateSmileyArray">Smiley-Datei neu erzeugen</a></p>
{/if}
{if $visitor->hasRight('GLOBAL_SETTINGS_ADMIN')}
<p><a href="/index.php?mod=i_am_god&amp;dest=module&amp;method=generateGlobalSettingsArray">Lokal globale Einstellungs-Datei neu erzeugen</a></p>
{/if}
</fieldset>

<fieldset>
<legend>Admin: Anzeigen/News </legend>
{if $visitor->hasRight('BANNER_ADMIN')}
<p><a href="/index.php?mod=i_am_god&amp;dest=module&amp;method=editBanner">Banner bearbeiten</a></p>
{/if}
</fieldset>

<fieldset>
<legend>Admin: Blog </legend>
<p>
	<form action="/index.php?mod=blogadvanced&amp;method=editUserBlogVisibility" method="post">
	  	<label for="username3" >Benutzer, dessen Blog zu bearbeiten ist</label>
	  	<input type="text" name="bloguser" id="username3"/>
		<input type="submit" name="edit_blog" value="Abschicken"/>
  	</form>
  	<form action="/index.php?mod=blogadvanced&amp;method=editUserBlogVisibility" method="post">
	  	<label for="groupname" >Organisationen, deren Blog zu bearbeiten ist</label>
	  	<input type="text" name="bloggroup" id="groupname"/>
		<input type="submit" name="edit_blog" value="Abschicken"/>
  	</form>
</p>
</fieldset>

{*
<fieldset>
<legend>Admin: Regelmäßige Skripte (cron)</legend>
<p><a href="/update_feature_slots.php" target="_blank">Anzahl der verfügbaren Feature-Slots korrigieren</a><br />
<a href="/update_users_online.php" target="_blank">"Users online" korrigieren</a><br />
<a href="/expire_user_warnings.php" target="_blank">Verwarnungen/Rechte korrigieren</a>
</fieldset>*}

</div>
</div>
