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

*}{errorbox caption="Fehler beim Erstellen des Threads"}

<div class="shadow">
<div id="entry" class="nopadding">
  {if !$visitor->hasRight('FORUM_THREAD_ENTRY_ADD')}
    <p>Es d&uuml;rfen nur registrierte Benutzer Foren-Beitr&auml;ge schreiben! <a href="user_new.php" title="Als neuer User registrieren">Registriere Dich jetzt erstmalig</a> oder <a href="#topofpage" title="nach oben">melde Dich an</a>!</p>
  {else} {* if user may write a thread entry -- start *}
   {if !($forum->hasEnabledPostings()) && ($threadToEdit == null) && !$isPreview}
    <p>In diesen Forum dürfen keine Threads erstellt werden. Bitte wähle ein gegenfalls ein Subforum aus</p>
   {else}
      <h3>Deinen Beitrag hinzuf&uuml;gen</h3>
      
      {* include right box containing formatting options and smileys *}
      {if $threadToEdit == null || $isPreview}
      	{include file="common/entry_options.tpl"}
      {/if}	
      
          <div id="entryfield">
          {* ----------------------------------------------------- *}
          {* Formular mit if/else aufbauen 						   *}
          {* ----------------------------------------------------- *}
                  <form enctype="multipart/form-data" action="{forum_url forum=$forum anker=post}" method="post">
                  <fieldset>	

						{if $threadToEdit == null || $isPreview}
							<input type="hidden" name="method" value="addThread"/>
							<input type="hidden" name="forumId" value="{$forum->id}" />
						{else}
							<input type="hidden" name="method" value="editThread"/>
							<input type="hidden" name="threadId" value="{$threadToEdit->id}"/>
						{/if}
						
					  {* random id to avoid double postings *}
                      <input type="hidden" name="{$smarty.const.F_ENTRY_RANDID}" value="{$randid}" />
						
					  {if $central_errors.missingFieldsObj.caption}<span class="missing">{/if}
	                  <label for="caption">Thread Überschrift:</label><br />	                  
	                  <input type="text" name="caption" id="caption" size="60" {if $threadToEdit != null}value="{$threadToEdit->getCaption()}" {/if} /><br />
					  {if $central_errors.missingFieldsObj.caption}</span>{/if}
					  	                  
	                  {if $forum->isModerator($visitor)}
	                  	  <label for="isSticky"><b>Sticky Thread</b>:</label>
	                  	  <input name="isSticky" id="isSticky" {if $threadToEdit != null && $threadToEdit->isSticky()}checked="checked" {/if}type="checkbox" />
		              {/if}

		                  <br/>
					  {if $threadToEdit != null && !$isPreview}
						  <input type="checkbox" name="linkThread" id="linkThread" /> <label for="linkThread">Thread verschieben:</label>
						  
						  <select name="linkThreadDest" size="12">
						  	{foreach from=$categories item=cat}
						  		<optgroup label="Kategorie: {$cat->getName()}">
						  			{foreach from=$cat->forums item=f}
									    <option value="{$f->id}">{$f->getName()}</option>
									{/foreach}
								 </optgroup>
  							 {/foreach}
						  </select>
  
					  {else}
					  	  {if $central_errors.missingFieldsObj.entryText}<span class="missing">{/if}	
		                  <label for="entrytext">Dein Eintrag:</label><br />
		                  <textarea name="entryText" id="entrytext" rows="10" cols="45" style="width: 100%">{strip}
		                  {if $entryToEdit != null}
		                  	{$entryToEdit->getContentRaw()}
		                  {/if}
		                  {/strip}</textarea><br />
   					  	  {if $central_errors.missingFieldsObj.entryText}</span>{/if}	
		                  
		                   <p>
		                  Du schreibst:
		                  <select name="for_group">
		                  <option value="0" {if !$entryToEdit || !($entryToEdit->isForGroup() || $entryToEdit->isAnonymous())}selected="selected"{/if}>als User {$visitor->getUsername()}</option>
		                  <option value="-1" {if $entryToEdit != null && $entryToEdit->isAnonymous() && !$isQuote}selected="selected"{/if}>anonym ( {$pointsAnonymous->getPointsFlow()} W-Punkt)</option>
		                  {foreach from=$visitor->getGroupsByRight('FORUM_GROUP_THREAD_ENTRY_ADD') item="ug"}
		                    <option value="{$ug->id}" {if $entryToEdit && $entryToEdit->isForGroup() && $entryToEdit->equals($ug)}selected="selected"{/if}>als Gruppe {$ug->name}</option>
		                  {/foreach}
		                  </select>
		                  </p>
		                  
		                  <br/>
		                  <h5>Anhang</h5>
		                  <p class="note">Du kannst eine Datei anh&auml;ngen. Bilder werden automatisch in den Beitrag eingef&uuml;gt. Maximal 100 KB, optional.</p>
		                  <input name="file_attachment1" id="file_attachment1" maxlength="102400" size="30" type="file" />
		                  <input type="submit" name="upload_submit" value="Hochladen" id="upload_submit" /><br />
		                  
		                   {* display current attachments *}
						  {if $entryToEdit != null && !$isQuote }
						  	<ul class="clear">
						  {foreach from=$entryToEdit->getAttachments() item=atm}
						    <li><em>{$atm->getFilename()}</em> <a href="#postenanker" title="Anhang hier positionieren" onclick="inlineAtm('{$atm->getTempId()}');"><img src="/images/icons/image_add.png" alt="Bild einfügen" /></a> <input name="delattach{$atm->id}" type="submit" value="löschen" class="nofloat" /></li>
						  {/foreach}
						  	</ul><br />
						  {/if}
		                  
                 

		                    <br /><h5>Formatierungseinstellungen</h5><br />
		                    {* activate smileys and bbcode by default for new entries *}
		                    {if $forum->hasEnabledSmileys()}
		                    	<label for="enableSmileys">Smileys aktivieren</label>
		                    	<input name="enable_smileys" id="enableSmileys" {if $entryToEdit == null || ($entryToEdit != null && $entryToEdit->isParseAsSmileys())}checked="checked" {/if} type="checkbox" />
		                    	<br />
		                    {/if}
		                    {if $forum->hasEnabledFormatcode()}	
		                    	<label for="enableFormatcode">Formatcode aktivieren</label>
		                    	<input name="enable_formatcode" id="enableFormatcode" {if $entryToEdit == null || ($entryToEdit != null && $entryToEdit->isParseAsFormatcode())}checked="checked" {/if} type="checkbox" />
		                    	<br />
							{/if}

					  {/if}						  
	                  <input type="submit" name="save" value="Abschicken"  />
	                  {if ($threadToEdit == null || $isPreview)} {* no preview vor Thread edit *}
	                    <input type="submit" name="preview_submit" value="Vorschau" id="forum_preview_submit" />
	                  {/if}	
                  </fieldset>		
                  </form>    
          </div>    
          <br class="clear" />
   {* after complete entry field insert google ads *}
  {include file='boxes/ads_google_Board.tpl'}
  {* closing google ads *}
          
	  {/if} {* if forum dont have postings  -- end *}  
	  {/if} {* if user may write a thread entry -- end *}
  	</div></div>
