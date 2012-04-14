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

*}{*	$Id: nav.tpl 6210 2008-07-25 17:29:44Z trehn $
	$HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/nav.tpl $ *}
<div id="nav">
<ul>
    <li><a class="navhelp" href="{help_url faq="1"}">Hilfe</a>
	    <ul>
		    {*<li><a href="/help">Hilfe</a></li>*}
		    <li><a href="{help_url faq="1"}">FAQ</a></li>
			<li><a href="/support">Support</a></li>
			<li><a href="/imprint">Impressum</a></li>
		</ul>
	</li>
{if $visitor->isRegularLocalUser()}
	<li><a href="#">Mein [[local.local.project_name]]</a>
	    <ul>
		    <li><a href="{user_info_url user=$visitor}">Mein Profil</a></li>
			{if $visitor->hasBlog()}
		    <li><a href="{blog_url owner=$visitor}">Mein Blog</a></li>
			{/if}
		    <li><a href="{pm_url}">Meine Nachrichten</a></li>
			{foreach item=group name=groups from=$visitor->getGroupMembership()}
			<li>{group_info_link group=$group}</li>
			{/foreach}
			<li><a href="{user_management_url profile=$visitor}">Einstellungen</a></li>
		</ul>
	</li>
{else}	
	<li><a href="/newuser">Registrieren</a>
	    <ul>
	    	<li><a href="/newuser">Anmeldung</a></li>
		</ul>
	</li>
{/if}
	<li><a href="#">Community</a>
	    <ul>
		    <li><a href="/home">Startseite</a></li>
		    {* Panem et circenses *}
		    <li><a href="{sports_url home=1}">(Tipp)spiele</a></li>
			<li><a href="{blog_url}">Blogosphäre</a></li>
			<li><a href="/orgas">Organisationen</a></li>
			<li><a href="/usersearch">User-Suche</a></li>
			<li><a href="/home/oldnews">News-Archiv</a></li>
			<li><a href="{index_url events=true}">Campus-Kalender</a></li>			
			{if !$visitor->isLoggedIn()}
	        <li><a href="/newuser">Neu anmelden</a></li>
            {else}
	        <li><a href="/canvassuser">Freunde werben</a></li>
            {/if}
		</ul>
	</li>
	<li><a href="{forum_url}">Foren</a>
	    <ul>
		    <li><a href="{forum_url}">&Uuml;bersicht</a></li>
		    <li>{forum_link latest=true name="letzte Beitr&auml;ge" title="Zu den letzten Beitr&auml;gen"}</li>
		    <li>{forum_link search="1" name="Forumsuche" title="Forumsuche"}</li>
		    <li><a href="{forum_url marketplace=1}">Marktplatz</a></li>
			<li><a href="/forum#orgas">Meine Organisationen</a></li>
			<li><a href="/forum#course">Meine Studienforen</a></li>
		</ul>
	</li>
	<li><a href="{course_url}">Studium</a>
	    <ul>
		    <li><a href="{course_url}" title="Zum Studiumsbereich">Studiums-Startseite</a></li>
		    <li><a href="/forum/latest?show=studies">Letzte Beitr&auml;ge</a></li>
		    <li><a href="/course/file/latest"> Unterlagen </a>
		    {if $visitor->getCourses()}
			    <ul>
				{foreach from=$visitor->getCourses() item=course}
                    <li><a href="{course_url course=$course showFiles=true}" title="{$course->getName()|escape:"html"}">Unterlagen: {$course->getNameShortSafe()|escape:"html"}</a></li>
                {/foreach}
				</ul>
				{/if}
		    </li>
	        {if $visitor->getCourses()}
			<li><a href="{user_management_url courses=$visitor}">F&auml;cher verwalten</a></li>
		    <li><a href="/course">Meine F&auml;cher</a>
			    {if $visitor->getCourses()}
			    <ul>
				{foreach from=$visitor->getCourses() item=course}
                    <li><a href="{course_url course=$course}" title="{$course->getName()|escape:"html"}">{$course->getNameShortSafe()|escape:"html"}</a></li>
                {/foreach}
				</ul>
				{/if}
			</li>
			{else}{if $visitor->isLoggedIn()}
    		<li><a href="{user_management_url courses=$visitor}">F&auml;cher hinzuf&uuml;gen</a></li>
            	  {/if}
            {/if}
	    </ul>
	</li>
</ul>
</div> {* #navigation *}
