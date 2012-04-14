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

*}{* $Id: mantis_interface.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{* $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/mantis/mantis_interface.tpl $ *}

{* define some variables here... *}
{assign var="loggedIn" value=$visitor->isLoggedIn()}
{if $loggedIn}
	{assign var="username" value=$visitor->getUsername()}
	{assign var="oldBirthday" value=$user->getBirthdate()}
{else}
	{assign var="username" value="unbekannt"}
	{assign var="oldBirthday" value="unbekannt"}
{/if}

{* <h2 id="pagename">Kontaktformular</h2> *}
{if $ackNeeded}
	<div class="shadow"><div>Vielen Dank. Die Meldung wurde registriert.
	{if $directlink}
		<a href="{$directlink}">Hier geht es zur&uuml;ck zur Ausgangsseite.</a>
	{/if}
	<br />
	</div></div>

{* show form only if not yet sucessfully submited *}
{else}
	
	<div class="shadow"><div>
		<ul class="bulleted">
			<li>&dagger; Makierte Felder sind Pflicht</li>
			{if $showFAQNote}<li><span class="adminNote">Bitte schau <strong>vor</strong> einer Anfrage in die <a href="/faq">Hilfe</a>, ob Deine Frage dort geklärt werden kann. Ist dies der Fall, befolge bitte die dort beschriebene Vorgehensweise. Vielen Dank.</span></li>{/if}
		</ul>
	</div></div>
	
	{errorbox}

    <div class="shadow"><div>
	<h3>{translate mantis=$source_cat}</h3>
	
	{if !$hideSubmit}{* show submit buttons and therefore print form only if they should be shown/printed *}
    {if $source_cat == $smarty.const.F_SOURCE_ANWSER}
    <form action="{mantis_url type="to"|cat:$mantisId}" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mantisId" value="{$mantisId}" />
    {else}
    <form action="{mantis_url}" method="post" enctype="multipart/form-data">
    {/if}
	<fieldset id="support">
    <input type="hidden" id="{$smarty.const.F_MANTIS_ID}" name="{$smarty.const.F_MANTIS_ID}" value="{$_mantis_id}" />
	{/if}

        {if $mailNeeded}
            {if $central_errors.missingFieldsObj.mail}<span class="missing">{/if}
            <label for="mail">E-Mail-Adresse&dagger;</label>
            {if $loggedIn}
                {if $visitor->getPrivateEmail() != ''}
                    <input name="mail" id="mail" type="text" maxlength="100" value="{$visitor->getPrivateEmail()}" readonly="readonly" />
                {elseif $visitor->getUniEmail() != ''}
                    {if $central_errors.missingFieldsObj.mail}<span class="missing">{/if}
                    <input name="mail" id="mail" type="text" maxlength="100" value="{$visitor->getUniEmail()}" readonly="readonly" />
                {else}
                <input name="mail" id="mail" type="text" maxlength="100" {if $_mail}value="{$_mail}"{/if}/>   
                {/if}
            {else}{*-- falls nicht eingeloggt, wird Mailfeld leer angezeigt *}
                <input name="mail" id="mail" type="text" maxlength="100" {if $_mail}value="{$_mail}"{/if}/>
            {/if}
            {if $central_errors.missingFieldsObj.mail}</span>{/if}
            <br />
        {/if}

        {if $phoneNeeded}
            <label for="phone">Ihre Telefonnummer</label>
            <input name="phone" id="phone" type="text" value="{$_phone}" maxlength="50" />
			<br />
        {/if}

        {if $companyNeeded}
            {if $central_errors.missingFieldsObj.company}<span class="missing">{/if}
                <label for="company">Name/Firma&dagger;</label>
                <input name="company" id="company" type="text" value="{$_company}" maxlength="70" />
            {if $central_errors.missingFieldsObj.company}</span>{/if}
            <br />
        {/if}

        {if $adressNeeded}
            <label for="adress">Postanschrift</label>
            <input name="adress" id="adress" type="text" value="{$_adress}" maxlength="100" />
			<br />
        {/if}

        {if $titleNeeded}
            {if $central_errors.missingFieldsObj.title}<span class="missing">{/if}
            <label for="title">Kurzbeschreibung/Betreff&dagger;</label>
            <input name="title" id="title" type="text" value="{$_title}" maxlength="70" {if $titleReadonly}readonly="readonly"{/if} />
            {if $central_errors.missingFieldsObj.title}</span>{/if}
            <br />
        {/if}
		
		{if $showTitleSelect}
			{if $central_errors.missingFieldsObj.title}<span class="missing">{/if}
			<label for="title">Kurzbeschreibung/Betreff&dagger;</label>
			<select name="title" id="title" size="1">
				<option value="0" disabled="disabled">Betreff ausw&auml;hlen</option>
				{* {html_options options=$selectContent selected=$_title} *}
				{foreach from=$selectContent item="opt"}
					<option value="{$opt}" >{translate mantis=$opt}</option>
				{/foreach}
			</select>
            {if $central_errors.missingFieldsObj.title}</span>{/if}
            <br />
		{/if}
		
		{if $groupNameNeeded}
			{if $central_errors.missingFieldsObj.groupName}<span class="missing">{/if}
			<label for="groupName">Organisationsname&dagger;</label>
            <input name="groupName" id="groupName" type="text" value="{$_groupName}" maxlength="70" />
            {if $central_errors.missingFieldsObj.groupName}</span>{/if}
            <br />
		{/if}

		{if $newUsernameNeeded}
			<label for="oldUsername">Bisheriger Username</label>
            <input name="oldUsername" id="oldUsername" type="text" readonly="readonly" value="{$username}" />
			{if $central_errors.missingFieldsObj.newUsername}<span class="missing">{/if}
			<label for="newUsername">Neuer Username&dagger;</label>
            <input name="newUsername" id="newUsername" type="text" value="{$_newUsername}" maxlength="70" />
            {if $central_errors.missingFieldsObj.newUsername}</span>{/if}
			<br />
		{/if}
		
		{if $newUniNeeded}
			<label for="oldUni">Bisheriger Hochschule</label>
            <input name="oldUni" id="oldUni" type="text" readonly="readonly" value="{$uni}" />
			{if $central_errors.missingFieldsObj.newUni}<span class="missing">{/if}
			<label for="newUni">Neue Hochschule&dagger;</label>
            {*<input name="newUni" id="newUni" type="text" value="{$_newUni}" maxlength="70" />*}
            <select name="newUni" id="newUni">
               {foreach from=$allUnis item="selUni"}
               	<option value="{$selUni->id}" >{$selUni->getName()}</option>
               {/foreach}
            </select>
            {if $central_errors.missingFieldsObj.newUni}</span>{/if}
			<br />
		{/if}
		
		{if $newBirthdayNeeded}
			<label for="oldBirthday">Bisheriges Geburtsdatum</label>
			<input type="text" id="oldBirthday" value="{$oldBirthday}" readonly="readonly" />
			<br class="clear" />
			{if $central_errors.missingFieldsObj.birthdate}<span class="missing">{/if}
			<label class="left" for="newBirthday">Neues Geburtsdatum (TT-MM-JJJJ)&dagger;</label>
			{*html_select_date start_year="-50" end_year="-16" field_order="DMY" reverse_years="true" day_extra='id="dayOfBirthday"'
				month_extra='id="monthOfBirthday"' year_extra='id="yearOfBirthday"'*}
			<select name="dayOfBirthday" id="dayOfBirthday" style="width: 3em">
				{section name=i loop=$days}
					<option value="{$days[i]}" {if intval($days[i]) == intval($_newBirthday_Day)}selected="selected"{/if}>{$days[i]}</option>
				{/section}
			</select>
			<select name="monthOfBirthday" id="monthOfBirthday" style="width: 3em">
				{section name=i loop=$months}
					<option value="{$months[i]}" {if intval($months[i]) == intval($_newBirthday_Month)}selected="selected"{/if}>{$months[i]}</option>
				{/section}
			</select>
			<select name="yearOfBirthday" id="yearOfBirthday" style="width: 4em">
				{assign var="start" value=$yearMin}
				{assign var="max" value=$yearMax}			
				{section name=i loop=$years}
					{if $years[i]<=$max && $years[i]>=$start}
						<option value="{$years[i]}" {if $years[i] == $_newBirthday_Year}selected="selected"{/if}>{$years[i]}</option>
					{/if}
				{/section}
			</select>
			{if $central_errors.missingFieldsObj.birthdate}</span>{/if}
			<br />
		{/if}
		
		{if $newCourseNameNeeded}
			{if $central_errors.missingFieldsObj.newCourseName}<span class="missing">{/if}
			<label for="newCourseName">Name des Faches&dagger;</label>
			<input name="newCourseName" id="newCourseName" type="text" value="{$_newCourseName}" />
			{if $central_errors.missingFieldsObj.newCourseName}</span>{/if}
		{/if}
		
        {if $entryNeeded}
            <label for="entry">Zitierter Beitrag</label>
            <textarea name="entry" id="entry" rows="20" readonly="readonly">{$_entry}</textarea>
			<br />
        {/if}

        {if $reasonNeeded}
            {if $central_errors.missingFieldsObj.reason}<span class="missing">{/if}
            <label for="reason">Begr&uuml;ndung&dagger;</label>
            <textarea name="reason" id="reason"" rows="10">{$_reason}</textarea>
            {if $central_errors.missingFieldsObj.reason}</span>{/if}
            <br />
        {/if}

        {if $descriptionNeeded}
            {if $central_errors.missingFieldsObj.description}<span class="missing">{/if}
            <label for="description">Ausf&uuml;hrliche Beschreibung&dagger;</label>
            <textarea name="description" id="description" rows="20">{$_description}</textarea>
            {if $central_errors.missingFieldsObj.description}</span>{/if}
            <br />
        {/if}

        {if $browserNeeded}
            {if $central_errors.missingFieldsObj.browser}<span class="missing">{/if}
            <label for="browser">Welchen Browser verwendest du? Welche Version?&dagger;</label>
            <input name="browser" id="browser" type="text" value="{$_browser}" />
            {if $central_errors.missingFieldsObj.browser}</span>{/if}
            <br />
        {/if}

        {if $technicalDataNeeded}
            <label for="technicalData">Weitere technische Daten, z. B. Betriebssystem.</label>
            <textarea name="technicalData" id="technicalData" cols="35" rows="10">{$_technicalData}</textarea>
			<br />
        {/if}

        {if $queryNeeded}
            {if $central_errors.missingFieldsObj.query}<span class="missing">{/if}
            <label for="query">Ihr Anliegen&dagger;</label>
            <textarea name="query" id="query" rows="20">{$_query}</textarea>
            {if $central_errors.missingFieldsObj.query}</span>{/if}
            <br />
        {/if}
		
		{if $fileNeeded}
			{if $central_errors.missingFieldsObj.query}<span class="missing">{/if}
            <label for="file">Datei hochladen, z.B. Bildschirmfotografie (max. 300KB)</label>
            <input name="file" id="file" type="file" accept="*" maxlength="102400" />
			{if $central_errors.missingFieldsObj.query}</span>{/if}
			<br />
		{/if}

        {if $responseNeeded}
            <label for="response">R&uuml;ckmeldung erw&uuml;nscht?</label>
            {if $_response}
                <input type="checkbox" name="response" id="response" value="Rueckmeldung" checked="checked" />
            {else}
                <input type="checkbox" name="response" id="response" value="Rueckmeldung" />
            {/if}
			<br />
        {/if}
        
        {if $captchaNeeded}
            {if $central_errors.missingFieldsObj.comment_captcha}<span class="missing">{/if}
            <label for="comment_captcha">{$comment_captcha->render()} &dagger;</label>
            <input type="text" name="comment_captcha" id="comment_captcha" /><br />
            {if $central_errors.missingFieldsObj.comment_captcha}</span>{/if}
        {/if}
        
		{if !$hideSubmit}
            <input name="save" value="Abschicken" type="submit" accesskey="s" />
            <input name="reset" value="&Auml;nderungen zur&uuml;cknehmen" type="reset" />
			<br />
		{/if}
    </fieldset>
    </form>
    </div></div>
{/if}


