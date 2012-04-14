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

*}{* $Id: user_change_common.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{* Formularfeld fuer die noetigstens User-Daten. Wird verwendet in user_change und in activate_success *}

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
{if !$active}
	<p class="note">Die Sichtbarkeitsstufe für Deinen Namen ist <em>{translate privacy=$user->detailVisibleName('real_name')}</em>. Das kannst Du im <a href="{user_management_url privacy=$user}#real_name">Reiter Privatsphäre</a> ändern.</p>
{/if}

{if $central_errors.missingFieldsObj.birthdate}<span class="missing">{/if}
<label class="left" for="birthdate_day">Geburtstag<sup>*</sup>:</label>
{html_select_date start_year="-50" end_year="-16" field_order="DMY" reverse_years="true" time=$user->getBirthdate('iso') day_extra='id="birthdate_day"'}[[help.user_change.birthday]]<br />
{if $central_errors.missingFieldsObj.birthdate}</span>{/if}
{if !$active}
	<p class="note">Die Sichtbarkeitsstufe für Deinen Geburtstag ist <em>{translate privacy=$user->detailVisibleName('birthdate')}</em>. Das kannst Du im <a href="{user_management_url privacy=$user}#birthdate">Reiter Privatsphäre</a> ändern.</p>
{/if}

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
    <label class="left" for="study_path{$sp_counter}">Hauptstudiengang:</label>
    {* initialize counter for secondary study_paths *}
  {else}
    <label class="left" for="study_path{$sp_counter}">Nebenstudiengang {$sp_counter}:</label>
  {/if}
  <select name="study_path{$sp_counter}" id="study_path{$sp_counter}">
  {if $sp_counter > 0}<option label="-- Eintrag löschen --" value="0">-- Eintrag löschen --</option>{/if}
  {strip}
  {foreach item=path from=$study_paths}
    {if $user_path->id == $path->id}
      <option value="{$path->id}" selected="selected">{$path->getName()}</option>
    {elseif $path->isAvailable()}
      <option value="{$path->id}">{$path->getName()}</option>
    {/if}
  {/foreach}
  {/strip}</select>
  
  {if $smarty.foreach.user_paths.first}
    {if $central_errors.missingFieldsObj.study_path0}</span>{/if}
  {/if}
  
  {counter assign="sp_counter"}
  
  [[help.user_change.study_path]]
{foreachelse}
  {* in case user has not selected any study path yet, give one list *}
  
  {if $central_errors.missingFieldsObj.study_path0}<span class="missing">{/if}
  
  <label class="left" for="study_path0">Hauptstudiengang:</label>
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
	<label class="left" for="study_path{$sp_counter}">Nebenstudiengang {$sp_counter}:</label>
	<select name="study_path{$sp_counter}" id="study_path{$sp_counter}">
	<option label="-- Bitte Eintrag auswählen --" value="0">-- Bitte Eintrag auswählen --</option>
	{foreach item=path from=$study_paths}
    	<option label="{$path->getName|escape:"html"}" value="{$path->id}">{$path->getName()|escape:"html"}</option>
	{/foreach}
	</select><br />
{/if}

{* button must not be displayed in activation profile because it doesn't work there yet *}
<input name="add_studypath_form" type="submit" value="Studiengang hinzufügen" />
</fieldset>
