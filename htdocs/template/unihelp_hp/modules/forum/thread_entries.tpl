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

*}{* $Id: thread_entries.tpl 6210 2008-07-25 17:29:44Z trehn $
	$HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/forum/thread_entries.tpl $ *}

  <a id="topofpage" name="topofpage"></a>
  <p style="float: right">
  <a href="{forum_url latest=true}?show=community" title="Zu den letzten Beiträgen" >
	<img src="/images/icons/user_comment.png" alt="Zu den letzten Beiträgen" />Zu den letzten Beiträgen
  </a>
  {dynamic}
   {if $visitor->isRegularLocalUser()}
     <br />
	  {if $thread->hasAbo($visitor->id)}
	 	<img src="/images/icons/basket_remove.png" alt="deabo-icon" />{forum_link name="Thread-Abo abbestellen" threadAboId=$thread->id remove=true}
	 {else}
	 	<img src="/images/icons/basket_put.png" alt="abo-icon" />{forum_link name="Thread abonnieren" threadAboId=$thread->id}
	 {/if}
	 <br />
 {/if}
 {/dynamic}
  </p>

 
 
 <div class="shadow"><div class="nopadding">
    <table  class="centralTable" summary="Die Tabelle enth&auml;lt Beitr&auml;ge verschiedener Autoren zum Thema '{$thread->getCaption()}'">    
	      	<colgroup>
	    	<col width="150px" />
			<col />
			<col width="250px" />
	  		</colgroup>
	  <thead>

	      <tr>
	      <th></th>
	      <th class="th-page"> Seiten: </th>
	      <th class="counter">
	      {foreach from=$thread->getCounter() item=bc}
		    {if $bc==$thread->getPage()}
		      <strong>{$bc}</strong>
		    {else}      
		      {forum_link thread=$thread page=$bc name=$bc }
		    {/if}
		      &nbsp;
		  {/foreach}
	      </th>
	    </tr>
      </thead>
      <tfoot>
   		 <tr>
	   		 <th></th>
		      <th class="th-page"> Seiten: </th>
		      <th class="counter">
		      {foreach from=$thread->getCounter() item=bc}
			    {if $bc==$thread->getPage()}
			      <strong>{$bc}</strong>
			    {else}      
			      {forum_link thread=$thread page=$bc name=$bc }
			    {/if}
			      &nbsp;
			  {/foreach}
		      </th>
		 </tr>
  	  </tfoot>
      <tbody>
      {dynamic}
        {math equation="(x-1) * y" x=$thread->getPage() y=$smarty.const.V_FORUM_THREAD_ENTRIES_PER_PAGE assign=start}
        {counter start=$start skip=1 print=false} {* print=false direction=down*}
        {foreach from=$threadEntries item=entry}
           {$entry->getContent()}
        {/foreach}
      {/dynamic}
     </tbody>
   </table>
  </div></div>
  {* after forum entries insert google ads *}
  {include file='boxes/ads_google_Board.tpl'}
  {* closing google ads *}
  
{dynamic}
  <div id="previewdiv">
  {include file="modules/forum/internal/preview.tpl"}
  </div>
  {errorbox caption="Fehler beim Senden" prestring='<a name="post"></a>'}

  <div class="shadow">
  <div id="entry" class="nopadding">
  {if $thread->isClosed()}
  	<p>Der Thread ist geschlossen, es darf nicht mehr geantwortet werden</p></div></div>
  {else}
  {if !$visitor->hasRight('FORUM_THREAD_ENTRY_ADD')}
    <p>Es d&uuml;rfen nur registrierte Benutzer Foren-Beitr&auml;ge schreiben! <a href="/newuser" title="Als neuer User registrieren">Registriere Dich jetzt erstmalig</a> oder <a href="#topofpage" title="nach oben">melde Dich an</a>!</p></div></div>
  {else} {* if user may write a thread entry -- start *}
      
      {if $entryToEdit != null && !$isQuote && !$isAdd}
	      <h3>Deinen Beitrag editieren</h3>
      {else}
    	  <h3>Deinen Beitrag hinzuf&uuml;gen</h3>
      {/if}  
      
      {* include right box containing formatting options and smileys *}
      {include file="common/entry_options.tpl"}
      	  <a name="postanker"></a>	
      	  
      	  {if !$isPreview && $entryToEdit != null && !$isQuote && $central_errors == null}
          		<a name="post"></a>
          {/if}
      	  
          <div id="entryfield">
          {* ----------------------------------------------------- *}
          {* Formular mit if/else aufbauen --> Gaestebuch/Tagebuch *}
          {* ----------------------------------------------------- *}
                  
                  {if $entryToEdit != null && !$isQuote && !$isAdd}
                  	<form enctype="multipart/form-data" action="{forum_url editEntryId=$entryToEdit->id anker="post"}" method="post">  
                  	<fieldset>	
                  	<input name="entryId" value="{$entryToEdit->id}" type="hidden" />
                  	<input name="method" value="editThreadEntry" type="hidden" />
                  {else}
                  	<form enctype="multipart/form-data" action="{forum_url thread=$thread anker="post"}" method="post">
                  	<fieldset>	
                  	<input name="method" value="addThreadEntry" type="hidden" />
                  {/if}  
                  <input name="threadId" value="{$thread->id}" type="hidden" />
                  
                  {* random id to avoid double postings *}
                  <input type="hidden" name="{$smarty.const.F_ENTRY_RANDID}" value="{$randid}" />
                  
                  <input name="page" value="{$thread->getPage()}" type="hidden" />
                  <label for="caption">Überschrift</label><br />
                  {if $central_errors.missingFieldsObj.caption}<span class="missing">{/if}
                  <input type="text" name="caption" id="caption" size="60" style="width: 98%" {if $entryToEdit != null}value="{if $isQuote && $entryToEdit->getCaption()}RE: {/if}{$entryToEdit->getCaption()}"{/if} {if $entryToEdit && $entryToEdit->id != null && $entryToEdit->getNrInThread() == 1}readonly="disable"{/if}/><br />
                  {if $central_errors.missingFieldsObj.caption}</span>{/if}
                  
                  {if $addMode}
	                  <label for="entryorginal">Dein Orginal Eintrag</label><br />
	                  <p style="border: 1px solid black;">
	                    {if $raw}
	                    	{$raw|nl2br}
	                    {else}
	                        {$entryToEdit->getContentRaw()|nl2br}
	                    {/if}
	                   </p><br />
                  
                   <label for="entrytext">Text an Eintrag anhängen:</label><br />
	                  <textarea name="entryText" id="entrytext" rows="5" cols="45" style="width: 98%">{strip}
	                   {$comment}
	                   {/strip}</textarea><br />
                  
                  {else} {* else addMode *}
                  
                  
                  {if $central_errors.missingFieldsObj.entryText}<span class="missing">{/if}
                  <label for="entrytext">Dein Eintrag</label><br />
                  
                  <textarea name="entryText" id="entrytext" rows="10" cols="45" style="width: 98%">{strip}
                    {if $entryToEdit != null}
                        {assign var="editAuthor" value=$entryToEdit->getAuthor()}
                        {if $isQuote}	
                            {$entryToEdit->getQuote()}
                         {else}{* of $isQuote *}
                         	{$entryToEdit->getContentRaw()}
                         {/if}{* of $isQuote *}
                    {/if}{* $entryToEdit != null *}
                   {/strip}</textarea><br />
                  {if $central_errors.missingFieldsObj.entryText}</span>{/if}
                  
                  {/if} {* closing addMode *}
                  
                  
                  {if $entryToEdit == null || $isQuote || $isAdd}
                  <p>
                  Du schreibst:
                  <select name="for_group">
                  <option value="0" {if !$entryToEdit || !($entryToEdit->isForGroup() || $entryToEdit->isAnonymous())}selected="selected"{/if}>als User {$visitor->getUsername()}</option>
                  <option value="-1" {if $entryToEdit != null && $entryToEdit->isAnonymous() && !$isQuote}selected="selected"{/if}>anonym ( {$pointsAnonymous->getPointsFlow(true)} W-Punkt)</option>
                  {foreach from=$visitor->getGroupsByRight('FORUM_GROUP_THREAD_ENTRY_ADD') item="ug"}
                    {if $entryToEdit}
                        {assign var="entryGroup" value=$entryToEdit->getGroup()}
                    {/if}
                    <option value="{$ug->id}" {if $entryToEdit && $entryToEdit->isForGroup() && $entryGroup->id == $ug->id}selected="selected"{/if}>als Gruppe {$ug->name}</option>
                  {/foreach}
                  </select>
                  </p>
                  {/if}
                  
                  <h5>Anhang</h5>
                  <p class="note">Du kannst eine Datei anh&auml;ngen. Bilder werden automatisch in den Beitrag eingef&uuml;gt. Maximal 100 KB, optional.</p>
                  <input name="file_attachment1" id="file_attachment1" maxlength="102400" size="30" type="file" />
                  <input type="submit" name="upload_submit" value="Hochladen" id="upload_submit" /><br />
                  
                  {* display current attachments *}
				  {if $entryToEdit != null && !$isQuote }
				  <ul class="clear">
                    {foreach from=$entryToEdit->getAttachments() item=atm}
                        <li><em>{$atm->originalFilename}</em> 
                        <a href="#postenanker" title="Anhang hier positionieren" onclick="inlineAtm('{$atm->getTempId()}');">
                        	<img src="/images/icons/image_add.png" alt="Bild einfügen" />
                        </a> 
                        <input name="delattach{$atm->id}" type="submit" value="löschen" class="nofloat" /></li>
                    {/foreach}
                    </ul><br />
				  {/if} 
                  
                  {if $entryToEdit != null && !$isQuote}
                  	  {if $forum->isModerator($visitor)}	
		                  <label for="hidden_changes">Änderung verbergen</label>
		                  <input type="checkbox" name="hidden_changes" id="hidden_changes" />
		                  <br />
		              {/if}    
                  {/if}                                   
                  
                  <h5>Formatierungseinstellungen</h5><br />
                    {* activate smileys and bbcode by default for new entries (!$editguestbookdata.content_raw) *}
                    {if $forum->hasEnabledSmileys()}
                    	<label for="enable_smileys">Smileys aktivieren</label><input name="enable_smileys" id="enable_smileys" {if $entryToEdit == null || ($entryToEdit != null && $entryToEdit->isParseAsSmileys()) || $isQuote}checked="checked" {/if}type="checkbox" /><br />
                    {/if}
                    {if $forum->hasEnabledFormatcode()}
                    	<label for="enable_formatcode">Formatcode aktivieren</label><input name="enable_formatcode" id="enable_formatcode" {if $entryToEdit == null || ($entryToEdit != null && $entryToEdit->isParseAsFormatcode()) || $isQuote}checked="checked" {/if}type="checkbox" /><br />
                  	{/if}
                  <input type="submit" name="save" value="Abschicken" accesskey="s" />
                  
                  {if $isPreview}<input type="hidden" name="preview_flag" value="1" />{/if}
                  <input name="preview_submit" value="Vorschau" type="submit" id="forum_preview_submit" />
                  </fieldset>
                  </form>
          </div>
    <br style="clear: both" />
    </div></div>
    
  {/if} {* if user may write a thread entry -- end *}
  {/if} {* if thread is close -- end *}

{/dynamic}
