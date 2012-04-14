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

*}

  <p style="float: right">
  <a href="#anleitung" title="Wie schreibe ich eine News?" >
	<img src="/images/icons/help.png" alt="hilfe" /> Anleitung
  </a>
  </p>

    {errorbox caption="Fehler beim Erstellen der News"}

	{if $isPreview}
		<div id="newsPreview">
		{include file="modules/news/news_preview.tpl"}
		</div>
	{/if}	

	<br class="clear" id="insertNews" />
  <div id="tabNavigation">
  <ul>
      <li>
        <a href="/home#news">News Übersicht</a>
      </li>
	  <li>
	 	<a class="active" href="/home/news/add"> News hinzufügen </a>
	  </li>	  
  	  <li>
		<a href="/home/oldnews">News Archiv</a>
	  </li>    
  </ul>
  </div>
<div class="shadow" id="news"><div id="entry">
      
      {* include right box containing formatting options and smileys *}
      {include file="common/entry_options.tpl"}
      <a name="postanker"></a>
        <div id="entryfield">
          {* ----------------------------------------------------- *}
          {* Formular mit if/else aufbauen --> Gaestebuch/Tagebuch *}
          {* ----------------------------------------------------- *}
                  
                  {if $entryToEdit != null && !$isAdd}
                  <form enctype="multipart/form-data" action="/home/news/{$entryToEdit->id}/edit#post" method="post">
                  <fieldset><legend>News verfassen</legend>
                  	<input name="newsId" value="{$entryToEdit->id}" type="hidden" />
                  	<input name="method" value="editNewsEntry" type="hidden" />
                  {else}
                  <form enctype="multipart/form-data" action="/home/news/add#post" method="post">
                  <fieldset>
                  	<input name="method" value="addNewsEntry" type="hidden" />
                  {/if}  
                  
                  {* random id to avoid double postings *}
                  <input type="hidden" name="{$smarty.const.F_ENTRY_RANDID}" value="{$randid}" />
                  
                  {if $central_errors.missingFieldsObj.caption}<span class="missing">{/if}
                  <label for="caption">&Uuml;berschrift:</label>
                  <input type="text" name="caption" id="caption" size="60" {if $entryToEdit != null}value="{$entryToEdit->getCaption()}"{/if} /><br />
                  {if $central_errors.missingFieldsObj.caption}</span>{/if}
                  
                  {*<label for="isVisible">sichtbar</label>
                  <input type="checkbox" id="isVisible" name="isVisible" {if $entryToEdit != null && $entryToEdit->isVisible() == false}{else}checked="checked"{/if}/>
                  <br />*}
                  
                  {if ($entryToEdit==null && $visitor->hasGroupRight('NEWS_ENTRY_STICKY') ) || ($entryToEdit!=null && $visitor->hasGroupRight('NEWS_ENTRY_STICKY', $entryToEdit->getGroupId()))}
                  	<label for="isSticky">wichtig</label>
                  	<input type="checkbox" id="isSticky" name="isSticky" {if $entryToEdit == null || $entryToEdit->isSticky() == false}{else}checked="checked"{/if}/>
                  	<br />
                  	{if $entryToEdit==null}
						Nur für folgenden Gruppen:
	                  	{foreach from=$visitor->getGroupsByRight('NEWS_ENTRY_STICKY') item=fgroup}
	                  		{$fgroup->name},
	                  	{/foreach}
	                 {/if} 	
                  {/if}
                  {if $isAdd}
                  	<label for="groupId">Posten f&uuml;r Gruppe:</label>
                  	<select id="groupId" name="groupId">
                  		{foreach from=$visitor->getGroupsByRight('NEWS_ENTRY_ADD') item=group}
                  			<option value="{$group->id}" {if $entryToEdit!= null && $group->id == $entryToEdit->getGroupId()}selected="selected"{/if} >{$group->name}</option>
                  		{/foreach}
                  	</select><br />
                  {/if}
                  {if $central_errors.startDate}<span class="missing">{/if}
	                  <label>Anzeigen von:</label>
	                  {if $entryToEdit!=null}
	                  	{html_select_date end_year=+1 field_order="DMY" time=$entryToEdit->getStartDate() prefix=start day_extra="id='eventStartDateDay'" month_extra="id='eventStartDateMonth'" year_extra="id='eventStartDateYear'"}
	                  {else}
	                  	{html_select_date end_year=+1 field_order="DMY" prefix=start day_extra="id='eventStartDateDay'" month_extra="id='eventStartDateMonth'" year_extra="id='eventStartDateYear'"}
	                  {/if}		
				  {if $central_errors.startDate}</span>{/if}
      			  {if $central_errors.endDate}<span class="missing">{/if}
		                 <br/><label>Anzeigen bis:</label>                  		 
                  		 {if $entryToEdit!=null}
		                  	{html_select_date end_year=+1 field_order="DMY" time=$entryToEdit->getEndDate() prefix=end day_extra="id='eventEndDateDay'" month_extra="id='eventEndDateMonth'" year_extra="id='eventEndDateYear'"} 
		                  {else}
		                  	{html_select_date end_year=+1 field_order="DMY" prefix=end day_extra="id='eventEndDateDay'" month_extra="id='eventEndDateMonth'" year_extra="id='eventEndDateYear'"} 
		                  {/if}
                   {if $central_errors.endDate}</span>{/if}		 
				  <br />
				  {if $central_errors.missingFieldsObj.openerText}<span class="missing">{/if}
				  <label for="opener">Opener-Text:</label>
				  <textarea name="openerText" id="opener" rows="5" cols="45">{if $entryToEdit != null}{$entryToEdit->getOpenerRaw()}{/if}</textarea><br />
				  {if $central_errors.missingFieldsObj.openerText}</span>{/if}
				  {if $central_errors.missingFieldsObj.entryText}<span class="missing">{/if}
                  <label for="entrytext">News-Text:</label>
				  <textarea name="entryText" id="entrytext" rows="10" cols="45">{if $entryToEdit != null}{$entryToEdit->getContentRaw()}{/if}</textarea><br />
				  {if $central_errors.missingFieldsObj.entryText}</span>{/if}
                  <p class="note">Du kannst eine Datei anh&auml;ngen. Bilder werden automatisch in den Beitrag eingef&uuml;gt. Maximal {$maxAttachmentSize/1024} KB, optional.</p>
				  <label for="file_attachment1">Anhang:</label>
                  <input name="file_attachment1" id="file_attachment1" maxlength="{$maxAttachmentSize}" size="30" type="file" />
                  <input type="submit" name="upload_submit" value="Hochladen"  />
                  <br />
                  {* display current attachments *}
                  {if $entryToEdit != null}
                    <ul class="clear">
                    {foreach from=$entryToEdit->getAttachments() item=atm}
                        <li><em>{$atm->getFilename()}</em> 
                        	<a href="#postenanker" title="Anhang im Opener-Text positionieren" onclick="inlineAtmOpener('{$atm->getTempId()}');">
                        		<img src="/images/icons/layout_add.png" alt="Bild einfügen" />
                        	</a> 
                        	<a href="#postenanker" title="Anhang im News-Text positionieren" onclick="inlineAtm('{$atm->getTempId()}');">
                        		<img src="/images/icons/image_add.png" alt="Bild einfügen" />
                        	</a>
                        	<input name="delattach{$atm->id}" type="submit" value="löschen" class="nofloat" />
                        	</li>
                    {/foreach}
                    </ul><br />
                   {/if}
                  
                  {* activate smileys and bbcode by default for new entries (!$editguestbookdata.content_raw) *}
                    	<label for="enable_smileys">Smileys aktivieren</label>
                    	<input name="enable_smileys" id="enable_smileys" {if $entryToEdit == null || ($entryToEdit != null && $entryToEdit->isParseAsSmileys())}checked="checked" {/if} type="checkbox" />
						<br />
                    	<label for="enable_formatcode">BBCode aktivieren</label>
                       	<input name="enable_formatcode" id="enable_formatcode" {if $entryToEdit == null || ($entryToEdit != null && $entryToEdit->isParseAsFormatcode())}checked="checked" {/if}type="checkbox" />
						<br />
 
                  <input type="submit" name="save" value="Speichern"  />
                  {if $isPreview}<input type="hidden" name="preview_flag" value="1" />{/if}
                  <input name="preview_submit" value="Vorschau" type="submit" id="news_preview_submit" />
                  
                  {if $entryToEdit == null || $entryToEdit->isVisible() == false}
                  	<br />
                  	<label for=newsForenId">Thread in folgenden Forum erzeugen:</label>
                  	<select id="newsForenId" name="newsForenId">
	                  	<option value="-1">Gruppen-Forum</option>
	                  	{foreach from=$newsForen item="forum"}
	                  		<option value="{$forum->id}" {if $targetForum != null && $targetForum->id == $forum->id}selected="selected"{/if}>{$forum->getName()}</option>
	                  	{/foreach}
                  	</select>
                  	
                  	<input name="makeVisible" value="News veröffentlichen" type="submit" />
                  {else}
                  	{if $visitor->hasRight('NEWS_ENTRY_ADMIN')}
                  		<input name="removeVisible" value="News verstecken" type="submit" />
                  	{/if}
                  {/if}
                                   
                  </fieldset>		
                  </form>
          </div>
          <br style="clear: both" />
          {if $entryToEdit != null && $entryToEdit->isVisible() == false}
          <ul class="bulleted"><li>
	          <a href="/home/news/{$entryToEdit->id}/del" id="NewsDel">
					diesen News Vorschlag löschen
			  </a>
		  </li></ul>
		  {/if}
</div></div>

<div class="shadow"><div style="font-size: 1.2em; text-align: justify; padding: 15px;">
<h3>News Anleitung!</h3>
<a name="anleitung"></a>
<p>
Die News sind ein System, damit die an 
der Hochschule aktiven Organisationen und 
Vereinen den Usern wichtige Informationen über ihre 
Aktivitäten zur Verfügung stellen können.
</p><br/><p>
Deshalb kann jede Gruppe Verantwortliche bestimmen, die 
im Namen dieser News veröffentlichen können. 
Der Username des Verfassers ist uns zwar bekannt,
wird aber nicht öffentlich angezeigt. Zu jeder Ankündigung
gibt es ein Diskussionsthread, der automatisch erzeugt wird,
was auch nicht verhindert werden kann.
</p><br/><p>
Jeder Eintrag besteht aus 3 Teilen.
Der Überschrift, dem Opener und dem Newstext.
Name und eine kurze Einführung des 
Artikels stellt die Überschrift dar.
Im Opener wird den Usern eine kleine Einleitung geboten.
Diese wird dann mit auf der Startseite angezeigt und in 
der Komplettansicht fett hervorgehoben. Der ganze Newstext 
hingegen erscheint erst nach dem anklicken des Artikels.
Die Formatierung von Text ist im Opener und
Newstext möglich, wobei die Buttons zur Textformatierung 
nur für letzteres sind.
Da der Opener nur eine kleine Einleitung darstellt, sollte hier 
von aufwendigen Formatierungen abgesehen werden.
</p><br/><p>
Es ist auch möglich Dateianhänge und Bilder an 
eine Ankündigung anzuhängen. So kann man sein Newseintrag 
durch ein nettes Bild optisch aufwerten. Falls ein 
Bild schon im Opener, also auf der Startseite 
angezeigt werden soll, muss dieses extra hinzugefügt werden. 
Dafür stehen nach dem Upload zwei Links hinter dem Bild
zur Verfügung. Einen der das Bild in den Opener und einen 
der es in den Newstext einfügt. 
ACHTUNG: Hierfür muss Javascript aktiviert sein.
Da die Startseite eine eigene Formatierung mit 
sich bringt, empfehlen wir ein Bild am Anfang des Openers einzufügen.
</p><br/><p>
Die Anzeigedauer der News kann frei gewählt 
werden. Die Zeit ist jedoch auf maximal 7 Tage zu beschränken!!! 
Auch dürfen keine News später ein zweites Mal eröffnet werden,
um z.B. die News noch einmal ganz oben zu 
platzieren. Sollte eure News von größtem Interesse 
für die Studierenden sein, was eine Platzierung 
ganz oben rechtfertigt, wendet euch bitte an 
unseren Support, welcher gerne weiterhelfen wird.
</p><br/><p>
Ein sehr nützliches Feature haben wir noch für 
euch. Wenn ihr mehrere Newsschreiber habt, oder 
z.b. die News noch einmal von einen zweiten aus eurem 
Verein/Organisation gegengelesen werden soll, 
dann kann die News gespeichert werden ohne sie zu veröffentlichen. 
Die Personen aus euerer Gruppe die zum News schreiben berechtigt
sind, haben dann die Möglichkeit die News zu lesen und zu editieren, bis sie in 
Finaler Version veröffentlicht werden kann. Die 
Ankündigungen sind in diesem Stadium nur von eurer Gruppe 
einsehbar. Die Vorabversion kann auch komplett 
wieder gelöscht werden, solange sie noch nicht veröffentlicht wurde.
</p><br/><p>
<i>Viel Spaß beim News schreiben wünscht ihr [[local.local.project_name]] Team!</i>
</p>

</div></div>
    
