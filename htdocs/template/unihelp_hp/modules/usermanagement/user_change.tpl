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

*}{* $Id: user_change.tpl 5895 2008-05-03 15:38:20Z schnueptus $ *}
{* $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/usermanagement/user_change.tpl $ *}

{if $admin_mode}
<span class="adminNote">(ADMIN)</span>
{else}
<div class="shadow">
<div><h3>Hilfe</h3>
  <ul class="bulleted">
	  <li>Auf dieser Seite kannst Du Deine pers&ouml;nlichen Daten eintragen oder &auml;ndern.</li>
	  <li>Du kannst beliebige Felder &auml;ndern. Mit einem Klick auf "Speichern" am Ende der Seite werden alle &Auml;nderungen &uuml;bernommen.</li>
	  <li>Deinen Geburtstag kannst Du ändern lassen, indem Du <a href="{mantis_url changebirthday=1}">hier klickst</a>.</li>
	  <li>Zur Änderung des Usernamens ist ein wichtiger Grund anzugeben. Dies kannst Du <a href="{mantis_url changeusername=1}">hier beim Support</a></li>
	</ul>
</div></div>
{/if} {* end if admin mode *}

{errorbox caption="Fehler bei der Profiländerung"}

{if $setNewPassword && $central_errors == null}
<div class="box errorbox">
<h3>Passwort</h3>
 <ul><li>Das Passwort wurde erfolgreich geändert. Bitte beim nächsten LogIn beachten!</li></ul>
</div>
{/if}
{if $admin_mode}
	<a href="{admin_url user=$user}">zurück zum Adminbereich</a>
{/if}
{* mode internal tab navigation *}
{include file="modules/usermanagement/navigation.tpl" usermanagement_tabpanemode="general"}

<div class="shadow"><div>
<br class="clear" />
<form enctype="multipart/form-data" action="{user_management_url profile=$user edit=$admin_mode}" method="post"
{if $show_all_options} {* JS-based check for admins *}
onsubmit="return safety_check();"
{/if}>

{if !$ie6}
<input name="changeprofile_form" type="submit" value="Alles speichern" /><br />
{/if}

{* include file="modules/usermanagement/user_change_common.tpl" *}
<fieldset><legend>Allgemeines</legend>

{if $central_errors.missingFieldsObj.first_name}<span class="missing">{/if}
<label class="left" for="first_name">Vorname:</label>
<input type="text" name="first_name" id="first_name" value="{$user->getFirstName()}" />
{if $central_errors.missingFieldsObj.first_name}</span>{/if}
[[help.user_change.name_after]]<br />

{if $central_errors.missingFieldsObj.last_name}<span class="missing">{/if}
<label class="left" for="last_name">Nachname:</label>
<input type="text" name="last_name" id="last_name" value="{$user->getLastName()}" />
{if $central_errors.missingFieldsObj.last_name}</span>{/if}
[[help.user_change.name_after]]<br />
<p class="note">Die Sichtbarkeitsstufe für Deinen Namen ist <em>{translate privacy=$user->detailVisibleName('real_name')}</em>. Das kannst Du im <a href="{user_management_url privacy=$user}#real_name">Reiter Privatsphäre</a> ändern.</p>

<label class="left" for="birthdate">Geburtstag:</label>
<input disabled="disabled" id="birthdate" type="text" value="{$user->getBirthdate()}" />[[help.user_change.birthday_after]]<br />
<p class="note">Die Sichtbarkeitsstufe für Deinen Geburtstag ist <em>{translate privacy=$user->detailVisibleName('birthdate')}</em>. Das kannst Du im <a href="{user_management_url privacy=$user}#birthdate">Reiter Privatsphäre</a> ändern.</p>

{if $central_errors.missingFieldsObj.gender}<span class="missing">{/if}
<label class="left" for="gender">Geschlecht:</label>
<select name="gender" id="gender">
  <option value="m" {if $user->getGender()=='m'}selected="selected"{/if}>m&auml;nnlich</option>
  <option value="f" {if $user->getGender()=='f'}selected="selected"{/if}>weiblich</option>
  <option value="" {if $user->getGender()==''}selected="selected"{/if}>indifferent</option>
</select>
{if $central_errors.missingFieldsObj.gender}</span>{/if}
<br />

{counter assign="sp_counter" start=0}
{* begin - selection of study paths *}
{foreach item=user_path from=$user->getStudyPathsObj() name=user_paths}
  {* for each study_path, that the user has already selected, show selection list *}
  {if $smarty.foreach.user_paths.first}
    {if $central_errors.missingFieldsObj.study_path0}<span class="missing">{/if}
    <br style="clear: left" /><label class="left" for="study_path{$sp_counter}" id="label_study_path{$sp_counter}">Hauptstudiengang:</label>
    {* initialize counter for secondary study_paths *}
  {else}
    <br style="clear: left" /><label class="left" for="study_path{$sp_counter}" id="label_study_path{$sp_counter}">Nebenstudiengang {$sp_counter}:</label>
  {/if}
  <select name="study_path{$sp_counter}" id="study_path{$sp_counter}" class="wide">
  {if $sp_counter > 0}<option label="-- Eintrag löschen --" value="0">-- Eintrag löschen --</option>{/if}
  {strip}
  {foreach item=path from=$study_paths}
    {if $user_path->id == $path->id}
      <option value="{$path->id}" selected="selected">{$path->getName()|escape:"html"}</option>
    {elseif $path->isAvailable()}
      <option value="{$path->id}">{$path->getName()|escape:"html"}</option>
    {/if}
  {/foreach}
  {/strip}</select>
  
  {if $smarty.foreach.user_paths.first}
    {if $central_errors.missingFieldsObj.study_path0}</span>{/if}
  {/if}
  {if $smarty.foreach.user_paths.last}
    <input name="add_studypath_form" id="add_studypath_form" type="submit" value="Studiengang hinzufügen" class="nofloat nomargin"/>
  {/if}
  
  {counter assign="sp_counter"}
  
  [[help.user_change.study_path]]
{foreachelse}
  {* in case user has not selected any study path yet, give one list *}
  
  {if $central_errors.missingFieldsObj.study_path0}<span class="missing">{/if}
  
  <br style="clear: left" /><label class="left" for="study_path0" id="label_study_path0">Hauptstudiengang:</label>
  <select name="study_path0" id="study_path0">
    <option label="-- Bitte auswählen --" value="0">-- Bitte auswählen --</option>
  {foreach item=path from=$study_paths}
    <option label="{$path->getName()|escape:"html"}" value="{$path->id}">{$path->getName()|escape:"html"}</option>
  {/foreach}
  </select>
  {if $central_errors.missingFieldsObj.study_path0}</span>{/if}
  [[help.user_change.study_path2]]
{/foreach}
{* end - selection of study paths *}
{if $add_mode}
    <br style="clear: left" /><label class="left" for="study_path{$sp_counter}" id="label_study_path{$sp_counter}">Nebenstudiengang {$sp_counter}:</label>
    <select name="study_path{$sp_counter}" id="study_path{$sp_counter}">
    <option label="-- Bitte Eintrag auswählen --" value="0">-- Bitte Eintrag auswählen --</option>
    {foreach item=path from=$study_paths}
        <option label="{$path->getame()|escape:"html"}" value="{$path->id}">{$path->getName()|escape:"html"}</option>
    {/foreach}
    </select><br />
{/if}
<br /><input name="changeprofile_form" type="submit" value="Speichern" />
</fieldset>

{if $show_all_options}
<fieldset>
<legend>Administrativa</legend>
{if $central_errors.missingFieldsObj.username_new}<span class="missing">{/if}
<label for="username_new">Username:</label>
<input type="text" name="username_new" id="username_new" value="{$user->getUsername()}" /> <br />
{if $central_errors.missingFieldsObj.username_new}</span>{/if}
<input type="hidden" name="username" id="username" value="{$user->getUsername()}" />
<label for="flag_activated">ist aktiviert:</label>
<input type="checkbox" name="flag_activated" id="flag_activated" {if $user->isActivated()}checked="checked"{/if} />
<br />
<label for="flag_active">ist aktiv:</label>
<input type="checkbox" name="flag_active" id="flag_active" {if $user->isActive()}checked="checked"{/if} />
<br />
<label for="flag_invisible">ist unsichtbar:</label>
<input type="checkbox" name="flag_invisible" id="flag_invisible" {if $user->isInvisible()}checked="checked"{/if} /><br />
<label for="person_type_id">Personentyp:</label>
<select name="person_type_id" id="person_type_id">
{foreach from=$person_types item=person}
	<option value="{$person->id}" {if $user->getPersonTypeId()==$person->id}selected="selected"{/if}>{$person->getName()}</option>
{/foreach}
</select>
<br />
<label for="uni_id">Hochschule:</label>
<select name="uni_id" id="uni_id">
{foreach item=uni from=$universities}
<option value="{$uni->id}" {if $user->getUniId()==$uni->id}selected="selected"{/if}>{$uni->getName()}</option>
{/foreach}
</select>[[help.user_change.university]]
<br />

{if $central_errors.missingFieldsObj.birthdate}<span class="missing">{/if}
<label class="left" for="birthdate_day">Geburtstag:</label>
{html_select_date start_year="-50" end_year="-16" field_order="DMY" reverse_years="true" time=$user->getBirthdate('iso') day_extra='id="birthdate_day"'}[[help.user_change.birthday]]<br />
{if $central_errors.missingFieldsObj.birthdate}</span>{/if}
<br />
<label for="economic-points">W-Punkte:</label>
<input type="text" readonly="readonly" name="economic-points" id="economic-points" value="{$user->getPointsEconomic()}" />
</fieldset>

{literal}
<script type="text/javascript"><!--
   function safety_check() {
      // check only, if username has changed
	  if ($("username").value == $("username_new").value) {
	  	return true;
	  }
      msg = "Bist Du sicher, dass Du den Usernamen ändern möchtest?";
      return confirm(msg);
   }
//--></script>
{/literal}
{/if}{* end admin part *}

<fieldset><legend>Passwort</legend>
{if $central_errors.missingFieldsObj.password_old}<span class="missing">{/if}
<label for="password_old">altes Passwort<sup>(*)</sup>:</label>
<input type="password" name="password_old" id="password_old" />
{if $central_errors.missingFieldsObj.password_old}</span>{/if}
[[help.user_change.password_old]]<br />

{if $central_errors.missingFieldsObj.password_new}<span class="missing">{/if}
<label for="password_new">Passwort:</label>
<input type="password" name="password_new" id="password_new" />
{if $central_errors.missingFieldsObj.password_new}</span>{/if}
[[help.user_change.password]]<br />

{if $central_errors.missingFieldsObj.password_check}<span class="missing">{/if}
<label for="password_check">noch mal<sup>(*)</sup>:</label>
<input type="password" name="password_check" id="password_check" /><br />
{if $central_errors.missingFieldsObj.password_check}</span>{/if}
<br /><input name="changeprofile_form" type="submit" value="Passwort ändern" />
</fieldset>

<fieldset><legend>Ergänzendes</legend>

<label for="flirt_status">Status:</label>
<select name="flirt_status" id="flirt_status">
  <option value="red" {if $user->getFlirtStatus()=="red"}selected="selected"{/if}>Rot: Vergeben</option>
  <option value="yellow" {if $user->getFlirtStatus()=="yellow"}selected="selected"{/if}>Gelb: Eventuell zu überzeugen</option>
  <option value="green" {if $user->getFlirtStatus()=="green"}selected="selected"{/if}>Grün: Ich schau mich um</option>
  <option value="none" {if $user->getFlirtStatus()=="none"}selected="selected"{/if}>k.A.</option>
</select>
[[help.user_change.user_flirt_status]]<br />

<label for="nationality_id">Nationalit&auml;t:</label>
<select name="nationality_id" id="nationality_id">
{foreach item=country from=$countries name=countries}
<option value="{$country->id}" {if !$smarty.foreach.countries.first}style="background: url('/images/flags/{$country->getIsoCode()|lower}.png') left no-repeat; padding-left: 20px; min-height: 16px;"{/if} {if $user->getNationalityId()==$country->id}selected="selected"{/if}>{$country->getNationality()}</option>
{/foreach}
</select>
[[help.user_change.nation]]<br/>

{if $central_errors.missingFieldsObj.homepage}<span class="missing">{/if}
<label for="homepage">Private Homepage:</label>
<input id="homepage" name="homepage" type="text" value="{$user->getHomepage()}" class="wide" />[[help.user_change.homepage]]<br />
{if $central_errors.missingFieldsObj.homepage}</span>{/if}

{if $central_errors.missingFieldsObj.signature}<span class="missing">{/if}
<label for="signature">Signatur:</label>
{* must escape signature here, because we edit raw version (due to smileys) *}
<textarea rows="3" cols="65" id="signature" name="signature">{$user->getSignatureRaw()|escape:"html"}</textarea>
{if $central_errors.missingFieldsObj.signature}</span>{/if}
[[help.user_change.signature]]<br />

{if $central_errors.missingFieldsObj.description}<span class="missing">{/if}
<label for="description">Eigene Beschreibung:</label>
{* must escape description here, because we edit raw version (due to smileys) *}
<textarea rows="6" cols="65" id="description" name="description">{if $user->hasDescription()}{$user->getDescriptionRaw()|escape:"html"}{/if}</textarea>
{if $central_errors.missingFieldsObj.description}</span>{/if}
[[help.user_change.description]]<br />

<label for="user_picture">&nbsp;</label>
<img src="{userpic_url big=$user}" alt="Userpic von {$user->getUsername()}" />
<img src="{userpic_url fancy=$user}" alt="Userpic von {$user->getUsername()}" />
<img src="{userpic_url tiny=$user}" alt="Userpic von {$user->getUsername()}" /><br />
{if $central_errors.missingFieldsObj.user_picture}<span class="missing">{/if}
<label for="user_picture">Userbild:</label>
<input name="user_picture" id="user_picture" size="30" type="file" />
{if $central_errors.missingFieldsObj.user_picture}</span>{/if}
[[help.user_change.user_picture]]<br />
<label for="user_picture_delete">&nbsp;</label>
<input name="user_picture_delete" id="user_picture_delete" type="checkbox" /> Userbild l&ouml;schen
<br /><input name="changeprofile_form" type="submit" value="Speichern" />
</fieldset>

{if !$ie6}
<input name="changeprofile_form" type="submit" value="Alles speichern" /><br />
{/if}
</form>

</div></div>

<div class="shadow"><div>
<h3>wichtige Links</h3>
<ul class="bulleted">
<li>
<a href="{user_management_url delete=$user edit=$admin_mode}" title="Mitgliedschaft bei [[local.local.project_name]] beenden">Meinen Account auf [[local.local.project_name]] löschen</a><br />
</li>
{*
We put this outside the template becouse to many user want to change the username but we accept only 
some with good grounds - schnueptus (27.06.2007)
<li>
<a href="{mantis_url changeusername=1}" title="Usernamen ändern lassen">Usernamen Änderung beantragen</a>
</li>
*}
<li>
<a href="{mantis_url changebirthday=1}" title="Geburtstag ändern lassen">Geburtstag ist falsch und soll geändert werden</a>
</li><li>
<a href="{mantis_url changeuni=1}" title="Hochschule wechseln">Ich habe die Hochschule gewechselt, bitte ändern</a>
</li>
</ul>
</div></div>
<br/>
<br/>
