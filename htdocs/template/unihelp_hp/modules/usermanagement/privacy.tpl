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

*}{* $Id: privacy.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{* $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/usermanagement/privacy.tpl $ *}

{* <h2 id="pagename">{$user->getUsername()} &ndash; Persönliche Einstellungen {if $admin_mode}<span class="adminNote">(ADMIN)</span>{/if}</h2>
 *}

{if $admin_mode}
<span class="adminNote">(ADMIN)</span>
<a href="{admin_url user=$user}">zurück zum Adminbereich</a>
{else}
<div class="shadow"><div><h3>Hilfe</h3>
      <ul class="bulleted">
        <li>Auf dieser Seite kannst Du regulieren, wer Deine Daten einsehen darf.</li>
      </ul>
</div></div>
{/if} {* end if admin mode *}

{errorbox caption="Fehler bei der Profiländerung"}

{* mode internal tab navigation *}
{include file="modules/usermanagement/navigation.tpl" usermanagement_tabpanemode="privacy"}
<div class="shadow"><div>
<br class="clear" />
<form action="{user_management_url privacy=$user edit=$admin_mode}" method="post">
<fieldset><legend>Datenschutz</legend>
Für die Auswahl beim Geburtsdatum gilt:
<ul class="note">
<li>Bei <em>{translate privacy="no one"}</em> wird Dein Alter nirgends angezeigt und Du erscheinst nicht in der Geburtstagsliste.</li>
<li>Bei <em>{translate privacy="all"}</em> erscheint Dein Name in der Geburtstagsliste.</li>
<li>Bei <em>{translate privacy="on friendlist"}</em> erscheint Dein Name in der Geburtstagsliste und Deinen Freunden wird zusätzlich Dein Geburtsdatum angezeigt.</li>
</ul>
<label for="birthdate">Geburtstag:</label>
<select id="birthdate" name="birthdate">
{foreach from=$details_visible item=det}
{if $det->name == 'no one' || $det->name == 'all' || $det->name == 'on friendlist'}
<option {if $user->detailVisibleName('birthdate') == $det->name}selected="selected"{/if} value="{$det->id}">{translate privacy=$det->name}</option>
{/if}
{/foreach}
</select><br />

<p class="note">
Bei den folgenden Einstellungen kannst Du genau auswählen, wer Deine Daten sehen kann
</p>

<label for="telephone">Telefon:</label>
<select id="telephone" name="telephone">
{foreach from=$details_visible item=det}
<option {if $user->detailVisibleName('telephone') == $det->name}selected="selected"{/if}  value="{$det->id}">{translate privacy=$det->name}</option>
{/foreach}
</select><br />

<label for="instant_messanger">Instant Messaging:</label>
<select id="instant_messanger" name="instant_messanger">
{foreach from=$details_visible item=det}
<option {if $user->detailVisibleName('instant_messanger') == $det->name}selected="selected"{/if}  value="{$det->id}">{translate privacy=$det->name}</option>
{/foreach}
</select><br />

<label for="mail_address">E-Mail-Adresse:</label>
<select id="mail_address" name="mail_address">
{foreach from=$details_visible item=det}
<option {if $user->detailVisibleName('mail_address') == $det->name}selected="selected"{/if} value="{$det->id}">{translate privacy=$det->name}</option>
{/foreach}
</select><br />

<label for="address">Anschrift:</label>
<select id="address" name="address">
{foreach from=$details_visible item=det}
<option {if $user->detailVisibleName('address') == $det->name}selected="selected"{/if} value="{$det->id}">{translate privacy=$det->name}</option>
{/foreach}
</select><br />

<label for="real_name">Name:</label>
<select id="real_name" name="real_name">
{foreach from=$details_visible item=det}
<option {if $user->detailVisibleName('real_name') == $det->name}selected="selected"{/if} value="{$det->id}">{translate privacy=$det->name}</option>
{/foreach}
</select><br />

<label for="guestbook_public">Gästebuch öffentlich</label>
<input type="checkbox" name="guestbook_public" id="guestbook_public" {if $user->isGBpublic()}checked="checked"{/if} /> [[help.user_change.guestbook]]
<br />

<label for="friendlist_public">Freundesliste öffentlich</label>
<input type="checkbox" name="friendlist_public" id="friendlist_public" {if $user->isFriendListpublic()}checked="checked"{/if} />
<br />

<label for="diary_public">Tagebuch öffentlich</label>
<input type="checkbox" name="diary_public" id="diary_public" {if $user->isDiarypublic()}checked="checked"{/if} />
<br />

<input name="changeprofile_form" type="submit" value="Speichern" /><br />
</fieldset>
</form>

</div></div>
