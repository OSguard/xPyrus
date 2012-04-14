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

*}{* $Id: contact_data.tpl 5895 2008-05-03 15:38:20Z schnueptus $ *}
{* $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/usermanagement/contact_data.tpl $ *}

{* <h2 id="pagename">{$user->getUsername()} &ndash; Persönliche Einstellungen {if $admin_mode}<span class="adminNote">(ADMIN)</span>{/if}</h2> *}

{if $admin_mode}
<span class="adminNote">(ADMIN)</span>
<a href="{admin_url user=$user}">zurück zum Adminbereich</a>

{else}
<div class="shadow"><div><h3>Hilfe</h3>
  <ul class="bulleted">
      <li>Auf dieser Seite kannst Du weitere Kontaktdaten eintragen.</li>
      <li>Alle Angaben sind <strong>freiwillig</strong>.</li>
      <li>Wenn Du Daten angibst, kannst Du selbst festlegen, wer diese Daten einsehen darf. {* TODO: Link Privacy / AGB? *}</li>
      <li>Du kannst beliebige Felder &auml;ndern. Mit einem Klick auf "Speichern" am Ende der Seite werden alle &Auml;nderungen &uuml;bernommen.</li>
    </ul>
</div></div>
{/if} {* end if admin mode *}

{errorbox caption="Fehler bei der Profiländerung"}

{* mode internal tab navigation *}
{include file="modules/usermanagement/navigation.tpl" usermanagement_tabpanemode="contactData"}
<div class="shadow"><div>
<br class="clear" />
<form action="{user_management_url contactData=$user edit=$admin_mode}" method="post">

<fieldset><legend>Telephon und IM</legend>

{if $central_errors.missingFieldsObj.telephone_mobil}<span class="missing">{/if}
<label for="telephone_mobil">Handynummer:</label>
<input id="telephone_mobil" name="telephone_mobil" type="text" value="{$user->getTelephoneMobil()}" />[[help.user_change.telephone_mobil]]<br />
{if $central_errors.missingFieldsObj.telephone_mobil}</span class="missing">{/if}

<p class="note">Die Sichtbarkeitsstufe für Telephon ist <em>{translate privacy=$user->detailVisibleName('telephone')}</em>. Das kannst Du im <a href="{user_management_url privacy=$user}#telephone">Reiter Privatsphäre</a> ändern.</p>

{if $central_errors.missingFieldsObj.im_icq}<span class="missing">{/if}
<label for="im_icq">ICQ-Nummer:</label>
<input id="im_icq" name="im_icq" type="text" value="{if $user->hasImICQ()}{$user->getImICQ()}{/if}" />[[help.user_change.icq]]<br />
{if $central_errors.missingFieldsObj.im_icq}</span>{/if}

{if $central_errors.missingFieldsObj.skype}<span class="missing">{/if}
<label for="skype">Skype-Nick:</label>
<input id="skype" name="skype" type="text" value="{if $user->hasSkype()}{$user->getSkype()}{/if}" />[[help.user_change.skype]]<br />
{if $central_errors.missingFieldsObj.skype}</span>{/if}

{if $central_errors.missingFieldsObj.im_yahoo}<span class="missing">{/if}
<label for="im_yahoo">Yahoo!-Nummer:</label>
<input id="im_yahoo" name="im_yahoo" type="text" value="{if $user->hasImYahoo()}{$user->getImYahoo()}{/if}" />[[help.user_change.yahoo]]<br />
{if $central_errors.missingFieldsObj.im_yahoo}</span>{/if}

{if $central_errors.missingFieldsObj.im_msn}<span class="missing">{/if}
<label for="im_msn">MSN-Nummer:</label>
<input id="im_msn" name="im_msn" type="text" value="{if $user->hasImMSN()}{$user->getImMSN()}{/if}" />[[help.user_change.msn]]<br />
{if $central_errors.missingFieldsObj.im_msn}</span>{/if}

{if $central_errors.missingFieldsObj.im_aim}<span class="missing">{/if}
<label for="im_aim">AIM-Nummer:</label>
<input id="im_aim" name="im_aim" type="text" value="{if $user->hasImAIM()}{$user->getImAIM()}{/if}" />[[help.user_change.aim]]<br />
{if $central_errors.missingFieldsObj.im_aim}</span>{/if}

{if $central_errors.missingFieldsObj.im_jabber}<span class="missing">{/if}
<label for="im_jabber">Jabber-ID:</label>
<input id="im_jabber" name="im_jabber" type="text" value="{if $user->hasImJabber()}{$user->getImJabber()}{/if}" />[[help.user_change.jabber]]<br />
{if $central_errors.missingFieldsObj.im_jabber}</span>{/if}

<p class="note">Die Sichtbarkeitsstufe für Instant Messanging ist <em>{translate privacy=$user->detailVisibleName('instant_messanger')}</em>. Das kannst Du im <a href="{user_management_url privacy=$user}#instant_messanger">Reiter Privatsphäre</a> ändern.</p>
<input name="changeprofile_form" type="submit" value="Speichern" />
</fieldset>

<fieldset><legend>E-Mail</legend>
{if $central_errors.missingFieldsObj.public_email}<span class="missing">{/if}
<label for="public_email">E-Mail:</label>
<input type="text" name="public_email" id="public_email" value="{$user->getPublicEmail()}" class="wide" />[[help.user_change.public_email]]<br />
{if $central_errors.missingFieldsObj.public_email}</span>{/if}

{if $central_errors.missingFieldsObj.pgp_key}<span class="missing">{/if}
<label for="pgp_key">PGP-Key:</label>
<input id="pgp_key" name="pgp_key" type="text" value="{if $user->hasPublicPGPKey()}{$user->getPublicPGPKey()}{/if}" />[[help.user_change.pgp_key]]<br />
{if $central_errors.missingFieldsObj.pgp_key}</span>{/if}

{if $central_errors.missingFieldsObj.private_email}<span class="missing">{/if}
<label for="private_email">Kontakt-E-Mail für [[local.local.project_name]]:</label>
<input type="text" name="private_email" id="private_email" value="{$user->getPrivateEmail()}" class="wide" />
[[help.user_change.private_email]]<br />
{if $central_errors.missingFieldsObj.private_email}</span>{/if}

<p class="note">Die Sichtbarkeitsstufe für E-Mail ist <em>{translate privacy=$user->detailVisibleName('mail_address')}</em>. Das kannst Du im <a href="{user_management_url privacy=$user}#mail_address">Reiter Privatsphäre</a> ändern.</p>
<input name="changeprofile_form" type="submit" value="Speichern" />
</fieldset>

<fieldset>
<legend>Anschrift</legend>
{if $central_errors.missingFieldsObj.street}<span class="missing">{/if}
<label for="street">Straße:</label>
<input type="text" name="street" id="street" value="{$user->getStreet()}" class="wide" /> <br />
{if $central_errors.missingFieldsObj.street}</span>{/if}
{if $central_errors.missingFieldsObj.zip_code}<span class="missing">{/if}
<label for="zip_code">Postleitzahl:</label>
<input type="text" name="zip_code" id="zip_code" value="{$user->getZipCode()}" /> <br />
{if $central_errors.missingFieldsObj.zip_code}</span>{/if}
{if $central_errors.missingFieldsObj.location}<span class="missing">{/if}
<label for="location">Ort:</label>
<input type="text" name="location" id="location" value="{$user->getLocation()}" /> <br />
{if $central_errors.missingFieldsObj.location}</span>{/if}

<p class="note">Die Sichtbarkeitsstufe für Deine Anschrift ist <em>{translate privacy=$user->detailVisibleName('address')}</em>. Das kannst Du im <a href="{user_management_url privacy=$user}#address">Reiter Privatsphäre</a> ändern.</p>
<input name="changeprofile_form" type="submit" value="Speichern" />
</fieldset>

</form>

</div></div>
