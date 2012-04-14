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

*}{* $Id: user_new.tpl 5898 2008-05-04 19:32:32Z schnueptus $ *}


{errorbox caption="Fehler bei der Registrierung"}

<div class="shadow"><div>
<h3>Anmelden</h3>
<form action="/newuser" method="post">

{if $canvassCode}<input type="hidden" name="ccode" value="{$canvassCode}" />{/if}

{if $lazyCheck}<div style="color: red; font-weight:bold; font-size: 130%;">ADMIN-Mode, kein Mail-Check, keine Aktivierung</div><input type="hidden" name="lazyCheck" value="1" />{/if}

<fieldset><legend>Username und Passwort</legend>
<p>Den Usernamen kannst Du frei w&auml;hlen. Bei (fast) allem, was Du auf [[local.local.project_domain]] tust, wirst Du mit diesem Namen verewigt! Erlaubt sind nur Buchstaben, Zahlen sowie der Bindestrich &quot;-&quot; und der Unterstrich &quot;_&quot;, jedoch keine Umlaute und keine Leerzeichen!</p>

{* #username_register is used for JS *}
{if $central_errors.username}<span class="missing">{/if}
<label for="username_register">Username:</label>
<input type="text" id="username_register" name="username" title="Gib einen Namen ein" {if $username != null}value="{$username}"{/if} />
{if $central_errors.username}</span>{/if}

<span id="username_check"></span><br />
<p>W&auml;hle f&uuml;r Dein Passwort m&ouml;glichst eine Kombination aus Buchstaben und Zahlen. Aus technischen Gr&uuml;nden sind keine Leerstellen m&ouml;glich. Damit Tippfehler vermieden werden, gib Dein Passwort zweimal ein.</p>

{if $central_errors.password}<span class="missing">{/if}
<label for="password_register">Passwort:</label>
<input type="password" name="password" id="password_register" title="W&auml;hle ein Passwort" /><br />

<label for="password_check">Wiederholung:</label>
<input type="password" name="passwordCheck" id="password_check" title="Gib das gleiche Passwort erneut ein" /><br />
{if $central_errors.password}</span>{/if}

<p>Bei wichtigen Updates oder Ereignissen wirst Du automatisch mit dem (sehr seltenen) [[local.local.project_domain]]-Newsletter informiert. Au&szlig;erdem k&ouml;nnen andere User Dir private Nachrichten schicken, wenn Du diese Funktion sp&auml;ter aktivierst.</p>

{if $central_errors.email}<span class="missing">{/if}
<label for="privateEmail">E-Mail:</label>
<input type="text" name="privateEmail" id="privateEmail" title="Deine private E-Mail-Adresse" {if $privateEmail != null}value="{$privateEmail}"{else}value=""{/if} /><br />
{if $central_errors.email}</span>{/if}

<p>Alle pers&ouml;nlichen Angaben werden vertraulich behandelt und durch [[local.local.project_name]] nicht an Dritte weitergegeben.</p>
</fieldset>	

<fieldset><legend>Studieninformationen</legend>
<p>Bitte w&auml;hle Deine Hochschule</p>
<label for="uniId">Hochschule:</label>
<select name="uniId" id="uniId">
	{foreach from=$universities item=uni}
		{if $uniId != null && $uniId == $uni->id}
			<option value="{$uni->id}" selected="selected">{$uni->getName()}</option>
		{else}
			<option value="{$uni->id}">{$uni->getName()}</option>
		{/if}
	{/foreach}
</select>
</fieldset>	

<fieldset><legend>Nutzerbedingungen</legend>
<input type="checkbox" name="accept_terms_of_use" id="accept_terms_of_use" value="accepted"/> <label class="wide" for="accept_terms_of_use">Ich bin mit den <a href="/terms_of_use" target="_blank">Nutzungsbedingungen</a> einverstanden.</label>
</fieldset>	

<fieldset><legend>Anmeldung abschlie&szlig;en</legend>
<p>Zur &Uuml;berpr&uuml;fung Deines Studenten-Status musst Du hier Deine Studenten-E-Mail-Adresse eingeben. An diese Adresse wird dann die Aktivierungs-Mail f&uuml;r Deinen [[local.local.project_name]]-Account geschickt.</p>
<p style="margin-bottom:1em"><strong>Bitte achte darauf die richtige Domain f&uuml;r Deinen Fachbereich ausw&auml;hlen.</strong></p>

{if $central_errors.uniEmail}<span class="missing">{/if}
<label for="uniEmail">E-Mail:</label>
<input type="text" name="uniEmail" id="uniEmail" size="25" {if $uniEmail != null}value="{$uniEmail}"{else}value="vorname.nachname"{/if} />@
{if $central_errors.uniEmail}</span>{/if}

{if $central_errors.uniEmailDomain}<span class="missing">{/if}
{strip}
<select name="uniEmailDomain" id="uniEmailDomain">
	{foreach from=$emailRegexps item=email}
		{assign var=uniId value=$email->uniId}
		<option value="{$email->id}" title="{$universities[$uniId]->getName()}"
    {if $uniEmailDomain != null && $uniEmailDomain == $email->id}
        selected="selected"
    {/if}
    >{$email->displayedDomainPart}</option>
	{/foreach}
</select>
{/strip}
{if $central_errors.uniEmailDomain}</span>{/if}

<input type="submit" name="save" value="Anmelden" title="Bitte einmal dr&uuml;cken!" /><br />
<p>
Falls Probleme bei der Registierung auftreten, kannst Du Dich vertrauensvoll <a href="{mantis_url}">an den Support</a> wenden.
</p>
<p class="note">[[local.local.help_registration]]</p>
</fieldset>

</form>
</div></div>
