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

*}{* <h2 id="pagename">PN an User schreiben</h2> *}

<div class="shadow" ><div class="nopadding">
<div id="entry">
{include file="common/entry_options.tpl"}
<div id="entryfield">
<form enctype="multipart/form-data" action="{admin_url systemPm=1 targetuser=$user}" method="post">
 <fieldset> 
{if $user}
	<strong>An: {$user->getUsername()}</strong>
	<input type="hidden" name="receivers" id="receivers" value="{$user->getUsername()}" />	
{elseif $toAll}	<strong>An: Alle User</strong>	<input type="hidden" name="toAll" id="receivers" value="true" />	{elseif $toOnline}	<strong>An: Alle User, gerade online</strong>	<input type="hidden" name="toOnline" id="receivers" value="true" />{else}
	<label for="receivers">Empfänger</label><br />	<input type="text" name="receivers" id="receivers" size="60" {if $receivers != null}value="{$receivers}"{/if}/><br />	
{/if}
{if $mantisBug}
	<input type="hidden" name="mantisBug" id="mantisBug" value="{$mantisBug}" />
	<br/>wird zu Mantis Task {$mantisBug} zugeordnen!	
{/if}
		  
  {if $central_errors.missingFieldsObj.entryText}<span class="missing">{/if}
  <label for="entrytext">Dein Eintrag</label><br />
  <textarea name="entryText" id="entrytext" rows="10" cols="45">{strip}
  {if $pmToPreview != null}{$pmToPreview->getContentRaw()}{/if}
  {/strip}</textarea><br />
  {if $central_errors.missingFieldsObj.entryText}</span>{/if}
		
		
	<h5>Formatierungseinstellungen</h5><br />
      	<input name="enable_smileys" id="enable_smileys" {if $pmToPreview == null || ($pmToPreview != null && $pmToPreview->isParseAsSmileys())}checked="checked" {/if}type="checkbox" /><label for="enable_smileys">Smileys aktivieren</label><br />
       	<input name="enable_formatcode" id="enable_formatcode" {if $pmToPreview == null || ($pmToPreview != null && $pmToPreview->isParseAsFormatcode())}checked="checked" {/if}type="checkbox" /><label for="enable_formatcode">BBCode aktivieren</label><br />

      <input type="submit" name="save" value="Abschicken" accesskey="s" />
      {*
      {if $isPreview}<input type="hidden" name="preview_flag" value="1" />{/if}
      <input name="preview_submit" value="Vorschau" type="submit" />			  
      *}
 </fieldset> 
</form>
</div></div>
<br class="clear" />

</div></div>
