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

*}{* $Id: courses.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{* $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/usermanagement/courses.tpl $ *}

{* <h2 id="pagename">{$user->getUsername()} &ndash; Persönliche Einstellungen {if $admin_mode}<span class="adminNote">(ADMIN)</span>{/if}</h2>
 *}

{if $admin_mode}
<span class="adminNote">(ADMIN)</span>
<a href="{admin_url user=$user}">zurück zum Adminbereich</a>
{else}
<div class="shadow"><div><h3>Hilfe</h3>
<ul class="bulleted">
    <li>Auf dieser Seite kannst Du Deine Vorlesungen auswählen, die Du an der Uni hörst.</li>
    <li>Falls Du ein Fach nicht finden solltest kannst Du <a href="{mantis_url missingcourse=1}" title="neues Fach">beim Support</a> dies 
    melden. Nach einer Prüfung legen wir das Fach für Dich an. Bitte schau <em>genau</em> nach, ob Du es vielleicht übersehen hast oder Dein
    Suchbegriff unpassend ist.</li>
</div></div>
{/if} {* end if admin mode *}

{errorbox caption="Fehler bei der Profiländerung"}

{* mode internal tab navigation *}
{include file="modules/usermanagement/navigation.tpl" usermanagement_tabpanemode="courses"}
<div class="shadow"><div>
<br class="clear" />
<form action="{user_management_url courses=$user edit=$admin_mode}" method="post">
<fieldset><legend>Fächer</legend>

{if $smarty.const.BASIC_STUDIES_AVAILABLE && !$no_basic_studies}
    <h4>Auswahl fürs Grundstudium</h4>
    
    <input type="submit" name="no_basic_studies_form" value="Ich bin reif fürs Hauptstudium." /><br />
    
    {* c is an array; c[0] is a CourseModel, c[1] states whether user has subscribed to the course or not *}
    {foreach from=$suggested_courses item="c"}
    <input type="checkbox" name="c{$c[0]->id}" id="course{$c[0]->id}" {if $c[1]}checked="checked"{/if}/>
    <label for="course{$c[0]->id}">{$c[0]->getName()|escape:html}</label><br />
    {/foreach}
    
    <input name="changestudies_form" type="submit" value="Neue Grundstudiumsauswahl speichern" /><br />
{elseif $smarty.const.BASIC_STUDIES_AVAILABLE}
    <input type="submit" name="basic_studies_form" value="Bitte Grundstudiumsauswahl anzeigen." /><br />
{/if}
</form>

<h4><a name="generalSelection" id="generalSelection"></a>Allgemeine Auswahl</h4>

<form action="{user_management_url courses=$user edit=$admin_mode}#generalSelection" method="post" id="courseManage">
<label for="courses">Deine F&auml;cher:</label>
<select name="courses[]" id="courses" size="10" multiple="multiple" class="wide">
  {foreach from=$courses item=course}
    <option value="{$course->id}">{$course->getName()|escape:html}</option>
  {/foreach}
</select>
[[help.user_change.courses]]<br />
<input id="courseManageDel" name="delcourses_form" type="submit" value="Fach entfernen" title="Einmal Klicken um ausgew&auml;lte F&auml;cher zu entfernen" /> 
<input id="courseManagePage1" name="showcoursepage_form" type="submit" value="Homepage des Faches" title="Einmal Klicken um zur Homepage des gew&auml;lten Faches zu gelangen; neues Fenster" /><br /> 

<label for="searchcourses">Ein Fach erg&auml;nzen:</label>
<input type="text" id="searchcourses" name="coursename" />
<input type="submit" id="courseManageSearch" name="searchcourses_form" value="Suchen" /> [[help.user_change.searchcourses]]<br />

{if $new_courses}
<label for="findcourses">Suchergebnisse:</label>
<select id="findcourses" name="findcourses[]" size="5" multiple="multiple" class="wide">
{foreach from=$new_courses item=course}
    <option value="{$course->id}" {*selected="selected"*}>{$course->getName()}</option>
{/foreach}
</select>
[[help.user_change.findcourses]]<br />
<input type="submit" id="courseManageAdd" name="addcourses_form" value="Fach hinzuf&uuml;gen" title="Einmal Klicken um ausgew&auml;lte F&auml;cher hinzuzuf&uml;gen" /> 
<input type="submit" id="courseManagePage2" name="showcoursepage_form" value="Homepage des Faches" title="Einmal Klicken um zur Homepage des gew&auml;lten Faches zu gelangen; neues Fenster" /><br />
{else}
<br />
{/if}

<p>Fehlt ein Fach? <a href="{mantis_url missingcourse=1}">Sagt uns Bescheid.</a></p>

</fieldset>
</form>

</div></div>
