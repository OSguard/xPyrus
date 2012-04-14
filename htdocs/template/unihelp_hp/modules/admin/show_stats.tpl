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
<h3>Statistik</h3>
<fieldset>
<legend>Sucheigenschaften</legend>
<form action="/i_am_god?method=overview&method=showStats" method="post">
<p>
<label for="username_search2">Username</label>
{* #username_search is used for JS Behaviour *}
{* cant use #username_search because it is used in the search box *}
<input type="text" name="username" id="username_search2" size="15" value="{$searchValues.username}" />
</p><br />
        <p><label for="study_place">Hochschule</label>
          <select id="study_place" name="study_place" class="w200">
            <option {*selected="selected"*}>alle in {$local_city->getName()}</option>
            {foreach from=$unis item=uni}
            	<option value="{$uni->id}" {if $searchValues.study_place == $uni->id}selected="selected"{/if}>{$uni->getName()}</option>
            {/foreach}
          </select>
        </p>
<p>
<label for="study_path">Studiengang</label>
  <select name="study_path" id="study_path">
  <option label="-- egal --" value="0" {if $searchValues.study_path == null or $searchValues.study_path ==0}selected="selected"{/if}>-- egal --</option>
  {strip}
  {foreach item=path from=$study_paths}
    <option label="{$path->getName()|escape:"html"}" value="{$path->id}" {if $searchValues.study_path == $path->id}selected="selected"{/if}>{$path->getName()|escape:"html"}</option>
  {/foreach}
  {/strip}</select>
</p>
<p>
<label class="left" for="flirt_status">Status</label>
<select name="flirt_status" id="flirt_status" class="w100">
  <option value="0" {if $searchValues.flirt_status === null or $searchValues.flirt_status==='0'}selected="selected"{/if}>-- egal --</option>
  <option value="none" {if $searchValues.flirt_status=='none'}selected="selected"{/if}>Geheim</option>
  <option value="red" {if $searchValues.flirt_status=='red'}selected="selected"{/if}>Rot</option>
  <option value="yellow" {if $searchValues.flirt_status=='yellow'}selected="selected"{/if}>Gelb</option>
  <option value="green" {if $searchValues.flirt_status=='green'}selected="selected"{/if}>Grün</option>
</select>
</p>
<p>
<label class="left" for="gender">Geschlecht</label>
<select name="gender" id="gender" class="w100">
  <option value="0" {if $searchValues.gender === null or $searchValues.gender==='0'}selected="selected"{/if}>-- egal --</option>
  <option value="m" {if $searchValues.gender=='m'}selected="selected"{/if}>m&auml;nnlich</option>
  <option value="f" {if $searchValues.gender=='f'}selected="selected"{/if}>weiblich</option>
  <option value="" {if $searchValues.gender===''}selected="selected"{/if}>indifferent</option>
</select>
</p>
<p>
<label for="picture">UserBild</label>
<select name="picture" id="picture" class="w100">
	<option value='0'>-- egal --</option>
	<option value='yes' {if $searchValues.picture=='yes'}selected="selected"{/if}>nur mit Bild </option>
	<option value='no' {if $searchValues.picture=='no'}selected="selected"{/if}>nur ohne Bild </option>
</select>
</p>
<p>
<input type="submit" value="Suche starten" />
<input type="reset" value="Zur&uuml;cksetzen" />
</p>
</form>
</fieldset>

{if $searchCount}
<hr />
	<strong>User mit diesen Eigenschaften gefunden: {$searchCount}</strong>
<hr />
{/if}

<ul class="bulleted">
	<li>Angemeldete User: {$latest_user_number}</li>
	<li>davon weiblich: {$user_number_female}</li>
	<li>davon männlich: {$user_number_male}</li>
	<li>Geschlecht unbekannt: {$latest_user_number-$user_number_female-$user_number_male}</li>
</ul>

</div></div>