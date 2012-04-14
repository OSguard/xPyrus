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

*}{* $Id: search_user_advanced.tpl 5895 2008-05-03 15:38:20Z schnueptus $ *}

	<div class="shadow"><div>
	<h3>Hilfe</h3>
		  <ul class="bulleted">
		     <li>Hier findest Du alle User, die bei [[local.local.project_name]] angemeldet sind.</li>
		     <li>Um einen User zu finden musst Du mindestens drei Buchstaben seines Usernamens angeben.</li>
		     <li>In die Suche werden nur die User einbezogen, welche die gesuchten Informationen öffentlich angeben.</li>
		  </ul>   
	</div></div>

<div class="shadow"><div>
<h3>Erweiterte Usersuche</h3>
<fieldset id="usersearch">
<legend>Sucheigenschaften</legend>
<form action="/usersearch#a-results" method="post">

<label for="username_search2">Username</label>
{* #username_search is used for JS Behaviour *}
{* cant use #username_search because it is used in the search box *}
<input type="text" name="username" id="username_search2" size="15" value="{$searchValues.username}" />
<br />

        <p><label for="study_place">Hochschule:</label>
          <select id="study_place" name="study_place" class="w200">
            <option {*selected="selected"*}>alle in {$local_city->getName()}</option>
            {foreach from=$unis item=uni}
            	<option value="{$uni->id}" {if $searchValues.study_place == $uni->id}selected="selected"{/if}>{$uni->getName()}</option>
            {/foreach}
          </select>
        </p>
<p>
<label for="study_path">Studiengang:</label>
  <select name="study_path" id="study_path">
  <option label="-- egal --" value="0" {if $searchValues.study_path == null or $searchValues.study_path ==0}selected="selected"{/if}>-- egal --</option>
  {strip}
  {foreach item=path from=$study_paths}
    <option label="{$path->getName()|escape:"html"}" value="{$path->id}" {if $searchValues.study_path == $path->id}selected="selected"{/if}>{$path->getName()|escape:"html"}</option>
  {/foreach}
  {/strip}</select>
</p>
<p>
<label class="left" for="flirt_status">Status:</label>
<select name="flirt_status" id="flirt_status" class="w100">
  <option value="0" {if $searchValues.flirt_status === null or $searchValues.flirt_status==='0'}selected="selected"{/if}>-- egal --</option>
  <option value="none" {if $searchValues.flirt_status=='none'}selected="selected"{/if}>Geheim</option>
  <option value="red" {if $searchValues.flirt_status=='red'}selected="selected"{/if}>Rot</option>
  <option value="yellow" {if $searchValues.flirt_status=='yellow'}selected="selected"{/if}>Gelb</option>
  <option value="green" {if $searchValues.flirt_status=='green'}selected="selected"{/if}>Grün</option>
</select>
</p>
<p>
<label class="left" for="gender">Geschlecht:</label>
<select name="gender" id="gender" class="w100">
  <option value="0" {if $searchValues.gender === null or $searchValues.gender==='0'}selected="selected"{/if}>-- egal --</option>
  <option value="m" {if $searchValues.gender=='m'}selected="selected"{/if}>m&auml;nnlich</option>
  <option value="f" {if $searchValues.gender=='f'}selected="selected"{/if}>weiblich</option>
  <option value="" {if $searchValues.gender===''}selected="selected"{/if}>indifferent</option>
</select>
</p>
<p>
<label for="picture">UserBild:</label>
<select name="picture" id="picture" class="w100">
	<option value='0'>-- egal --</option>
	<option value='yes' {if $searchValues.picture=='yes'}selected="selected"{/if}>nur mit Bild </option>
	<option value='no' {if $searchValues.picture=='no'}selected="selected"{/if}>nur ohne Bild </option>
</select>
</p>
<p>
<label for="order_by">Sortieren nach:</label>
<select name="order_by" id="order_by" class="w100">
	<option value='0'>-- egal --</option>
	<option value='username' {if $searchValues.order_by=='username'}selected="selected"{/if}>Username</option>
	<option value='points' {if $searchValues.order_by=='points'}selected="selected"{/if}>Punkte</option>
	<option value='activity_index' {if $searchValues.order_by=='activity_index'}selected="selected"{/if}>Aktivität</option>
	<option value='age' {if $searchValues.order_by=='age'}selected="selected"{/if}>Alter</option>
	<option value='random' {if $searchValues.order_by=='random'}selected="selected"{/if}>zufällig</option>
</select>
<select name="order" id="order" class="w100">
	<option value='ASC' {if $searchValues.order=='ASC'}selected="selected"{/if}>aufsteigend</option>
	<option value='DESC' {if $searchValues.order=='DESC'}selected="selected"{/if}>absteigend</option>
</select>
</p>
<p><label for="display">Ergebnisanzeige:</label>
          <select id="display" name="display" class="w100">
			<option value="compact" {if $display=="compact"}selected="selected"{/if}>kompakt</option> 
			<option value="detail"  {if $display=="detail"}selected="selected"{/if}>ausführlich</option>           
          </select>
          <select id="number" name="limit" style="width:40px;">
            <option value="8" {if $searchValues.limit==8}selected="selected"{/if}>8</option>
            <option value="16" {if $searchValues.limit==16 || !$searchValues}selected="selected"{/if}>16</option>
            <option value="32"{if $searchValues.limit==32}selected="selected"{/if}>32</option>
            <option value="64"{if $searchValues.limit==64}selected="selected"{/if}>64</option>
          </select>
          pro Seite
</p>
<p>
<input type="submit" value="Suche starten" />
<input type="reset" value="Zur&uuml;cksetzen" />
</p>
</form>
</fieldset>
</div></div>



{* has a search query been given? *}
{if $search_results != null}
<div class="shadow"><div>
    <h3>Suchergebnisse</h3>
<a name="a-results" id="a-results"></a>
    {if $warnings}
        {* TODO: enhance CSS and XHTML *}
        <h5>Achtung</h5>
        <ul class="note">
        {foreach from=$warnings item="warn"}
        <li>{$warn}</li>
        {/foreach}
        </ul>
    {/if}
<fieldset id="results1">

    {math equation="(x-1) * y" x=$page y=$searchValues.limit assign=start}
    {counter start=$start skip=1 print=false}
    

    
    {if $display=="detail"}
    
    <table>
    {foreach from=$search_results item=user name=results}
      <tr{if $smarty.foreach.results.last} class="entrylast"{/if}>
        <td>{counter}.</td>
        <td class="imgtd"><a href="{user_info_url user=$user}"><img src="{userpic_url tiny=$user}" alt="{$user->getUsername()}" /></a></td>
        <td>
          
          <dl>
            <dt>Username</dt>
            <dd><a href="{user_info_url user=$user}">{$user->getUsername()}</a></dd>
            <dt>Geschlecht</dt>
            {if $user->getGender() == 'm'}
				{assign var="gender" value="m&auml;nnlich"}
			{elseif $user->getGender() == 'f'}
				{assign var="gender" value="weiblich"}
			{else}
				{assign var="gender" value="?"}
			{/if}
            <dd>{$gender}</dd>
            <dt>Studiengang</dt>
            {assign var=studypath value=$user->getStudyPathsObj()}
            <dd>{if $studypath}{$studypath[0]->getName()|truncate:30}&nbsp;({$studypath[0]->getNameShort()}){else}<em>hat Dauerurlaub :P</em>{/if}</dd>
            {if $user->detailVisibleName('birthdate') != 'no one'}
	            <dt>Alter</dt>
	            <dd>{$user->getAge()}</dd>
            {/if}
            <dt>Status</dt>
            <dd>{user_status user=$user}</dd>
            <dt>Punkte</dt>
            <dd>{$user->getPoints()}</dd>
            <dt>Online-Aktivität</dt>
            <dd>{$user->getActivityIndex()|string_format:"%.2f"}</dd>
          </dl>
        </td>
      </tr>
	{/foreach}
    </table>
    
    {else} {* else if display style == detail*}
    	{foreach from=$search_results item=user name=results}
    	<p class="compact">
  	    	<span>{counter}.</span>
    		<a href="{user_info_url user=$user}"><img src="{userpic_url tiny=$user}" alt="{$user->getUsername()}" /></a>
    		<br /><a href="{user_info_url user=$user}">{$user->getUsername()}</a>
    	</p>
    	{/foreach}
    {/if}	
  </fieldset>

	</div>
		{if $nextPage || $page>1}
		<div class="counter counterbottom">
			{if $page>1}
			<a href="/usersearch?page={$page-1}" >vorherige Seite</a>
			{/if}
			{if $nextPage}
			<a href="/usersearch?page={$page+1}" >nächste Seite</a>
			{/if}
		</div>
		{/if}
	</div>

{elseif $search_results !== null} {* no search results here *}
 <div style="margin: 2em; font-weight: bold; font-size: 110%;">Niemanden gefunden, der auf Deine Beschreibung passt.</div>
{/if}



