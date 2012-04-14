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

*}{* $Id: activate_success.tpl 5894 2008-05-03 15:23:26Z schnueptus $ *}

{errorbox caption="Fehler bei der Aktivierung"}

<div class="shadow">
<div>
<h3>Willkommen bei [[local.local.project_domain]]!</h3>
<p class="firstElement">Deine Anmeldung ist erfolgreich abgeschlossen. Um Dein Profil zu vervollst&auml;ndigen, solltest Du hier noch einige Angaben zu Deiner Person machen.</p>
<form method="post" action="/index.php?mod=usermanagement&amp;method=activateProfile">
    
<fieldset><legend>Allgemeines</legend>
  
  {if $central_errors.missingFieldsObj.first_name}<span class="missing">{/if}
  <label class="left" for="first_name">Vorname<sup>*</sup>:</label>
  <input type="text" name="first_name" id="first_name" value="{$user->getFirstName()}" />
  {if $central_errors.missingFieldsObj.first_name}</span>{/if}
  [[help.user_change.first_name]]<br />
  
  {if $central_errors.missingFieldsObj.last_name}<span class="missing">{/if}
  <label class="left" for="last_name">Nachname<sup>*</sup>:</label>
  <input type="text" name="last_name" id="last_name" value="{$user->getLastName()}" />
  {if $central_errors.missingFieldsObj.last_name}</span>{/if}
  [[help.user_change.last_name]]<br />
  <p class="note">Die Sichtbarkeitsstufe für Deinen Namen ist <em>{translate privacy=$user->detailVisibleName('real_name')}</em>. Das kannst Du später ändern.</p>
  
  {if $central_errors.missingFieldsObj.birthdate}<span class="missing">{/if}
  <label class="left" for="birthdate_day">Geburtstag:</label>
  {html_select_date start_year="-50" end_year="-16" field_order="DMY" reverse_years="true" time=$user->getBirthdate() day_extra='id="birthdate_day"'}[[help.user_change.birthday]]<br />
  {if $central_errors.missingFieldsObj.birthdate}</span>{/if}
  <p class="note">Die Sichtbarkeitsstufe für Deinen Geburtstag ist <em>{translate privacy=$user->detailVisibleName('birthdate')}</em>. Das kannst Du später ändern.</p>
  
  
  {if $central_errors.missingFieldsObj.gender}<span class="missing">{/if}
  <label class="left" for="gender">Geschlecht:</label>
  <select name="gender" id="gender">
    <option value="m" {if $user->getGender()=='m'}selected="selected"{/if}>m&auml;nnlich</option>
    <option value="f" {if $user->getGender()=='f'}selected="selected"{/if}>weiblich</option>
    <option value="" {if $user->getGender()==''}selected="selected"{/if}>indifferent</option>
  </select>
  {if $central_errors.missingFieldsObj.gender}</span>{/if}
  <br />
  
  {if $central_errors.missingFieldsObj.study_path0}<span class="missing">{/if}
    <label class="left" for="study_path0">Studiengang:</label>
    <select name="study_path0" id="study_path0">
    <option value="0">-- bitte auswählen --</option>
    {foreach item=path from=$study_paths}
      {if $user_path->id == $path->id}
        <option value="{$path->id}" selected="selected">{$path->getName()}</option>
      {elseif $path->isAvailable()}
        <option value="{$path->id}">{$path->getName()}</option>
      {/if}
    {/foreach}
    </select>
    
    {if $smarty.foreach.user_paths.first}
      {if $central_errors.missingFieldsObj.study_path0}</span>{/if}
    {/if}
    
    
    [[help.user_change.study_path]]
  {* end - selection of study paths *}
    
  <input type="hidden" name="change_profile" value="1" />
  <input type="submit" value="Abschicken" />
</form>
</div>
</div>
