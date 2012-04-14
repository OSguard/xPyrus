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

*}{* $Id: new_user_pm.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{* $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/pm/new_user_pm.tpl $ *}
{*
 this variable are available
 	
 $pmToPreview    PmEntryModel - the Model if we are preview
 $pmToQuote      PmEntryModel - the Model we quote or forward
 
 $isQuote		 boolean      - we quote a message
 $isFwd          boolean      - we forward a message
 
 $receivers      string       - given string to will send this message
 $capitionValue  string       - given string for catption
 
 $sendsuccessful boolean      - message was successful send
 
 $errortyp       string       - error have been detectet (and switch to previewMode)
    - filesize: upload file to big
    - receivers
    - messageempty
*}
    
    {errorbox caption="Fehler beim Senden"}
  
    <br class="clear" />
  <div id="tabNavigation" style="margin-bottom: -37px;">
  <ul style="margin: 0px;">
      <li>
        <a class="active" href="{pm_url new=true}" title="hier kannst Du eine neue PM verfassen" >neue Nachricht schreiben</a>
      </li>
      <li>
        <a href="{pm_url}">Posteingang ( {$visitor->getPMsUnread()}/{$visitor->getPMs()} )</a>
      </li>
      <li>
      	<a href="{pm_url out=true}" title="Zum Postausgang">Postausgang ( {$visitor->getPMsSent()} )</a>
      </li>
  </ul>
  <br style="clear: both;" />
  </div>
<div class="shadow">
<div class="nopadding">
  
  {if $sendsuccessful}
   <fieldset>
     <legend>Info</legend>
     <strong class="firstElement">Deine Nachricht wurde erfolgreich verschickt.</strong>
   </fieldset>
  {else}   
  
  {* prview Mode *}
  {if $isPreview}
  	<fieldset id="pmtext">
		{include file="modules/pm/pm_preview.tpl"}
  	</fieldset>  	
  {/if}
  
  <div id="entry">
  {* include right box containing formatting options and smileys *}
  {include file="common/entry_options.tpl"}
  	  <a name="postanker"></a>	
      <div id="entryfield">
      {* ----------------------------------------------------- *}
      {* Formular mit if/else aufbauen --> Gaestebuch/Tagebuch *}
      {* ----------------------------------------------------- *}
              {if $courseName}
              <form enctype="multipart/form-data" action="{pm_url newcourse=$courseId}" method="post">
              {else}
              <form enctype="multipart/form-data" action="{pm_url new=true}" method="post">
              {/if}
              <fieldset> 
              {if $central_errors.missingFieldsObj.receivers || $central_errors.receivers}<span class="missing">{/if}
              <label for="receivers">Empfänger:</label><br />
			  {if $limit_receivers && $receivers != null}
			  	<input type="text" name="receivers_fake" id="receivers" size="60" value="{$courseName}" readonly="disable"/><br />
			  	<input type="hidden" name="receivers" size="60" value="{$receivers}" /><br />
			  {else}
			  	<input type="text" name="receivers" id="receivers" size="60" {if $receivers != null}value="{$receivers}"{else}{if $isQuote}{assign var="pmAuthor" value=$pmToQuote->getAuthor()} value="{$pmAuthor->getUsername()}" {/if}{/if}/><br />
			  {/if}
			  {if $central_errors.missingFieldsObj.receivers || $central_errors.receivers}</span>{/if}
              <label for="caption">Betreff:</label><br />
			  {strip}
			  <input type="text" name="caption" id="caption" size="60" value="
				{if $central_errors == null}
					{$capitionValue}
				{/if}
				{if $pmToPreview != null}
					{$pmToPreview->getCaption()}
				{else}
					{if $pmToQuote!=null}
						{if $isQuote}Re: {/if}
						{if $isFwd}Fwd: {/if}
						{$pmToQuote->getCaption()}
					{/if}
				{/if}" 
				{if $limit_receivers}
					readonly="disable"
				{/if} />
				<br />
			  {/strip}
              {if $limit_receivers}
              	<h5>Anzahl begrenzen</h5><br />
			  	<input type="text" name="limit_receivers" size="4" value="{$limit_receivers}" />
			  	{if $limit_max}Maximale Anzahl: {$limit_max}
			  	<input type="hidden" name="limit_max" value="{$limit_max}" />
			  	{/if}<br />
              {/if}    
              {if $central_errors.missingFieldsObj.entryText}<span class="missing">{/if}
              <label for="entrytext">Dein Eintrag:</label><br />
              {if $pmToQuote!=null}
                {* need to use temp variable because smarty doesn't seem to work with deep-hierarchical object-method calls
                   like $foo->bar()->go() *}
                {assign var="pmQuoteAuthor" value=$pmToQuote->getAuthor()}
              {/if}
              <textarea name="entryText" id="entrytext" rows="10" cols="45">{strip}
              {if $pmToPreview != null}{$pmToPreview->getContentRaw()}{/if}
              {if $pmToQuote!=null}{$pmToQuote->getQuote()}{/if}
              {/strip}</textarea><br />
              {if $central_errors.missingFieldsObj.entryText}</span>{/if}
              
              {if $pmToQuote!=null}
              <input type="hidden" name="replyId" value="{$pmToQuote->id}" />
              {/if}
              
              {if $visitor->hasRight('PM_ADD_ATTACHMENT')}
	              <h5>Anhang</h5>
	              <p class="note">Du kannst eine Datei anh&auml;ngen. Bilder werden automatisch in den Beitrag eingef&uuml;gt. Maximal 100 KB, optional.</p>
	              <input name="file_attachment1" id="file_attachment1" maxlength="102400" size="30" type="file" />
	              <input type="submit" name="upload_submit" value="Hochladen"  />
	              {*<input type="submit" name="addmc" value="Aus Media Center hinzufügen (PoC)"  />*}
	              <br />                                    
	              
	              {* display current attachments *}
                  {if $pmToPreview != null}
                    <ul class="clear">
                    {foreach from=$pmToPreview->getAttachments() item=atm}
                        <li><em>{$atm->getFilename()}</em> <a href="#postenanker" title="Anhang hier positionieren" onclick="inlineAtm('{$atm->getTempId()}');"><img src="/images/symbols/img.gif" alt="Bild einfügen" /></a> <input name="delattach{$atm->id}" type="submit" value="löschen" class="nofloat" /></li>
                    {/foreach}
                    </ul><br />
                  {/if}
               {/if}
          
              <h5>Formatierungseinstellungen</h5><br />
              	<input name="enable_smileys" id="enable_smileys" {if $pmToPreview == null || ($pmToPreview != null && $pmToPreview->isParseAsSmileys())}checked="checked" {/if}type="checkbox" /><label for="enable_smileys">Smileys aktivieren</label><br />
               	<input name="enable_formatcode" id="enable_formatcode" {if $pmToPreview == null || ($pmToPreview != null && $pmToPreview->isParseAsFormatcode())}checked="checked" {/if}type="checkbox" /><label for="enable_formatcode">BBCode aktivieren</label><br />

              <input type="submit" name="save" value="Abschicken" accesskey="s" />
              {if $isPreview}<input type="hidden" name="preview_flag" value="1" />{/if}
              <input name="preview_submit" value="Vorschau" type="submit" id="pm_preview_submit" />
              </fieldset>
              </form>
      </div>
      <br class="clear" />
</div>
<br class="clear" />
    {/if} {* end of else $sendsuccessful *}
   
</div></div>
