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

*}{* $Id: edit_entry.tpl 5807 2008-04-12 21:23:22Z trehn $ *}

<div id="content" class="fullcontent">

{errorbox var="blog_errors" caption="Fehler beim Erstellen"}

{if $blog_editentry && $isPreview}
<div class="blogbox entry preview" id="blogPreview">
  {include file="modules/blogadvanced/blog_entry_preview.tpl"}
</div>
{/if}

{* need this for insert preview via AJAX
  schnueptus (09.06.2007) *}
<div id="insertPreview"></div>

{include file="modules/blogadvanced/admin_menu.tpl"}

<div class="shadow"><div class="nopadding">

<div id="entry">
{* include right box containing formatting options and smileys *}
{include file="common/entry_options.tpl"}
<div id="entryfield">


<a name="postenanker"></a>
<form enctype="multipart/form-data" action="{blog_url owner=$blog_owner admin="post"}" method="post">
  <fieldset>
  
  {if $blog_editentry->id}<input type="hidden" name="entry_id" value="{$blog_editentry->id}" />{/if}
  
  {* random string to avoid multiple not wanted post *}
  <input type="hidden" name="randomstring" value="{$blog_randomstring}" />
  
  {if $blog_errors.missingFieldsObj.entry_title}<span class="missing">{/if}
  <label for="entry_title">Titel:</label>
  	<input type="text" name="entry_title" id="entry_title" value="{if $blog_editentry}{$blog_editentry->getTitle()}{/if}" size="60" /><br />
  {if $blog_errors.missingFieldsObj.entry_title}</span>{/if}
  
  <label for="entry_category">Kategorie:</label>
  	<select name="entry_category[]" id="entry_category" multiple="multiple" size="4">
   	<option value="0" {if !$blog_editentry}selected="selected"{/if}>Keine Kategorie</option>
  {foreach from=$blog_categories item=cat}
  	<option value="{$cat->id}" {if $blog_editentry && array_key_exists($cat->id,$blog_editentry->getCategories())}selected="selected"{/if}>{$cat->name}</option>
  {/foreach}
  </select>
  
  {if $blog_errors.missingFieldsObj.entrytext}<span class="missing">{/if}
  <label for="entrytext">Text:</label>
  	<textarea name="entry_text" id="entrytext" rows="10" cols="62">{if $blog_editentry}{$blog_editentry->getContentRaw()}{/if}</textarea><br />
  {if $blog_errors.missingFieldsObj.entrytext}</span>{/if}
    
  <label for="entry_trackbacks">Trackback-URIs:</label>
  	<input type="text"  name="entry_trackbacks" id="entry_trackbacks" size="60" value="{$blog_entrytrackbacks}" /><br />
  
  <label for="entry_allow_comments">Kommentare zu diesem Eintrag erlauben:</label>
  	<input name="entry_allow_comments" id="entry_allow_comments" type="checkbox" {if !$blog_editentry || $blog_editentry->isAllowComments()}checked="checked"{/if} /><br />
  
  <label for="entry_notify_comments">Bei Kommentaren mich benachrichtigen:</label>
  <select name="entry_notify_comments" id="entry_notify_comments">
    <option value="none" {if $blog_editentry && $blog_editentry->getSubscription($visitor) == "none"}selected="selected"{/if}>gar nicht</option>
    <option value="pm" {if ($blog_editentry && $blog_editentry->getSubscription($visitor) == "pm") || !$blog_editentry}selected="selected"{/if}>per PN</option>
{if $visitor->getPrivateEmail() != ''}
    <option value="email" {if $blog_editentry && $blog_editentry->getSubscription($visitor) == "email"}selected="selected"{/if}>per E-Mail</option>
{/if}
  </select><br />

  <h5>Anhang</h5>
  <p class="note">Du kannst eine Datei anh&auml;ngen. Bilder werden automatisch in den Beitrag eingef&uuml;gt. Maximal 100 KB, optional.</p>
  <input name="file_attachment1" id="file_attachment1" maxlength="102400" size="30" type="file" />
  <input type="submit" name="upload_submit" value="Hochladen"  /><br />
  
  {* display current attachments *}
  {if $blog_editentry != null}
  <ul class="clear">
  {foreach from=$blog_editentry->getAttachments() item=atm}
    <li><em>{$atm->getFilename()}</em> <a href="#postenanker" title="Anhang hier positionieren" onclick="inlineAtm('{$atm->getTempId()}');"><img src="/images/icons/image_add.png" alt="Bild einfügen" /></a> <input name="delattach{$atm->id}" type="submit" value="löschen" class="nofloat" /></li>
  {/foreach}
  </ul><br />
  {/if}

  <h5>Formatierungseinstellungen</h5><br />
  {* activate smileys and bbcode by default for new entries ($userinfo_state == 1) *}
  <label for="enable_smileys">Smileys aktivieren:</label><input name="enable_smileys" id="enable_smileys" {if !$blog_editentry || $blog_editentry->enableSmileys}checked="checked" {/if}type="checkbox" /><br />
  <label for="enable_formatcode">BBCode aktivieren:</label><input name="enable_formatcode" id="enable_formatcode" {if !$blog_editentry || $blog_editentry->enableFormatCode}checked="checked" {/if}type="checkbox" /><br />
  
  <input name="save" value="Abschicken" type="submit" accesskey="s" />
  {if $isPreview}<input type="hidden" name="preview_flag" value="1" />{/if}
  <input name="preview_submit" value="Vorschau" type="submit" id="blogAdvancePreview"/>

{if $blog_editentry->id}
<div style="margin-top: 50px; font-color: red;"> 
  <input name="entry_delete" value="Diesen Eintrag löschen" type="submit" />
</div>
{/if}

  </fieldset>
</form>

</div></div>{* end entryfield *}

</div></div>{* end shadow *}

</div>{* end content *}
