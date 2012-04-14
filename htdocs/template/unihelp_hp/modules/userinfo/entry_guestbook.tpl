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

*}{* $Id: entry_guestbook.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
 {* first check, if we want to add or edit an entry *}
 {if $userinfo_state == 2}  {* state==1: add mode ; state==2: edit mode; state==3: quote mode *}
	<form enctype="multipart/form-data" action="{user_info_url user=$userinfo_user gbpage=$userinfo_guestbookpage editGBEntry=$userinfo_editentry->id}#post" method="post">
	<input type="hidden" name="method" value="editGBEntry" />
 {elseif $userinfo_state == 1 or $userinfo_state == 3}
	<form enctype="multipart/form-data" action="{user_info_url user=$userinfo_user gbpage=$userinfo_guestbookpage addGBEntry=1}#post" method="post">
	<input type="hidden" name="method" value="addGBEntry" />
 {/if}
  {* random id to avoid double postings *}
  <input type="hidden" name="{$smarty.const.F_ENTRY_RANDID}" value="{$randid}" />

  {* note: anchor post does only exist on preview; currently I (linap) don't know a better solution than referencing the anchor on every request *}
  <fieldset>	
  <a name="postenanker" id="postenanker"></a>
  <h5>Bewertung</h5>
  {if $userinfo_state != 2}
  	{if $userinfo_permissions.guestbook_point}
    <input value="1" name="bewertung" id="plus" {if $userinfo_editentry && $userinfo_editentry->getValue()==1}checked="checked"{/if} type="radio" />
    <label for="plus">(+) Respekt!</label>
    {/if}
    <input value="0" name="bewertung" id="neutral" {if !$userinfo_editentry || $userinfo_editentry->getValue()==0}checked="checked"{/if} type="radio" />
    <label for="neutral">(0) neutral</label>
    {if $userinfo_permissions.guestbook_point}
    <input value="-1" name="bewertung" id="minus" {if $userinfo_editentry && $userinfo_editentry->getValue()==-1}checked="checked"{/if} type="radio" />
    <label for="minus" style="color: red">(-) Loser!</label><br />
    {/if}
        
    {if $userinfo_permissions.guestbook_give_multiple_points}<p>
      <label for="pluspunkt">Admin-Punkte: &plusmn;&nbsp;</label>
      <input name="pluspunkt" id="pluspunkt" value="0" type="text" size="1" /></p>
    {/if}
  {else}
    {$userinfo_editentry->getValue()} Punkte
    <input type="hidden" name="bewertung" value="{$userinfo_editentry->getValue()}" />
  {/if}
  
  

  <p class="note">Bei einem positiven Eintrag wird {$userinfo_user->getUsername()|escape:"html"} ein Punkt gut geschrieben. Bei einem negativen wird {$userinfo.username|escape:"html"} ein Punkt abgezogen.</p>
  
  <h5 {if $userinfo_errors.missingFieldsObj.comment}class="missing"{/if}>Dein Eintrag</h5><br />
  <textarea name="{$smarty.const.F_GUESTBOOK_CONTENT_RAW}" id="entrytext" rows="10" cols="45">{if $userinfo_editentry}{$userinfo_editentry->getContentRaw()}{/if}</textarea><br />

  <h5 {if $userinfo_errors.missingFieldsObj.file_attachment1}class="missing"{/if}>Anhang</h5>
  <p class="note">Du kannst eine Datei anh&auml;ngen. Bilder werden automatisch in den Beitrag eingef&uuml;gt.<br />
  Maximal {$userinfo_maxattachmentsize_kb} KB, optional.</p>
  <input name="gbid" id="gbid" value="{$userinfo_editentry->id}" type="hidden" />
  <input name="file_attachment1" id="file_attachment1" size="30" type="file" maxlength="{$userinfo_maxattachmentsize}" />
  <input type="submit" name="upload_submit" value="Hochladen" class="nomargin" /><br />
  
  {* display current attachments *}
  {if $userinfo_editentry}
  <ul class="clear">
  {foreach from=$userinfo_editentry->getAttachments() item=atm}
    <li><em>{$atm->getFilename()}</em> <a href="#postenanker" title="Anhang hier positionieren" onclick="inlineAtm('{$atm->getTempId()}');"><img src="/images/icons/image_add.png" alt="Bild einfügen" /></a> <input name="delattach{$atm->id}" type="submit" value="löschen" class="nofloat" /></li>
  {/foreach}
  </ul><br />
  {/if}
  
  <h5>Formatierungseinstellungen</h5><br />
  {* activate smileys and bbcode by default for new entries ($userinfo_state == 1) *}
  <label for="enable_smileys">Smileys aktivieren</label><input name="enable_smileys" id="enable_smileys" {if !$userinfo_editentry || $userinfo_editentry->isParseAsSmileys()}checked="checked" {/if}type="checkbox" /><br />
  <label for="enable_formatcode">BBCode aktivieren</label><input name="enable_formatcode" id="enable_formatcode" {if !$userinfo_editentry || $userinfo_editentry->isParseAsFormatcode()}checked="checked" {/if}type="checkbox" /><br />
  {if $userinfo_state == 2 && $userinfo_permissions.guestbook_edit_without_notice}
    <label for="enable_update_notice">Auf &Auml;nderung hinweisen</label><input name="enable_update_notice" id="enable_update_notice" type="checkbox" /> [[help.entry_options.update_notice]]
    <br />
  {/if}
  
  <input name="save" value="Abschicken" type="submit" accesskey="s" />
  {if $isPreview}<input type="hidden" name="preview_flag" value="1" />{/if}
  <input name="preview_submit" id="gb_preview_submit" value="Vorschau" type="submit" />
  
  </fieldset>		
  </form>
