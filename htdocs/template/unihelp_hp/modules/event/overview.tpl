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

*}<br/>
<br/>

Zeige <a href="{index_url events=true weeks=1}">1</a> <a href="{index_url events=true weeks=2}">2</a>
 <a href="{index_url events=true weeks=3}">3</a> <a href="{index_url events=true weeks=4}">4</a> Wochen an

<table id="eventcalendar">
  <colgroup width="14%" span="7" />
  {if !$weeksToShow||$weeksToShow<0}
  	{assign var="weeksToShow" value="1"}
  {/if}
  <tr>
  {section name=z start=0 loop=$weeksToShow step=1}
    {section name=i start=0 loop=7 step=1}
        {math assign="loopVar" equation="now + 3600 * 24 * y + 3600 * 24 * z * 7"  now=$smarty.now y=$smarty.section.i.index z=$smarty.section.z.index}
        {assign var="day" value=$loopVar|date_format:"%u"}
        
  		<th {if $day==7}class="sunday"{/if}> 
  			{$loopVar|date_format:"%a %d.%m"}
  			
  		</th>
	{/section}
  </tr>
  <tr>
   {section name=day start=0 loop=7 step=1}
   	   {math assign="loopVar" equation="now + 3600 * 24 * y + 3600 * 24 * z * 7"  now=$smarty.now y=$smarty.section.day.index z=$smarty.section.z.index}
       {assign var="day" value=$loopVar|date_format:"%u"}
       {assign var=index value=$loopVar|date_format:"%Y%m%d"}
	   <td {if $day==7}class="sunday"{/if}>
	   	<ul>
		   {foreach from=$events[$index] item="event"} 
					<li><a href="{index_url events=true eventId=$event->id weeks=$weeksToShow}#event_anchor">{$event->getCaption()}</a></li>
		   {/foreach}
		</ul>
	   </td>
   {/section}
  </tr>
  {/section}
</table>
<!--<br />-->
<p>
<a href="{index_url events=true ical=true}">Download im iCal (iCalendar)-Format</a>
</p>

{dynamic}
{if $eventToShow}

	<div class="shadow"><div class="nopadding">
  
		<h3><a id="event_anchor" name="event_anchor">Ausgew&auml;hlter Termin</a></h3>
		<table id="userguestbookpre" summary="Die Tabelle enth&auml;lt das G&auml;stebuch von User {$userinfo.username}">
    		<tbody id="guestbookbody">
			<tr>
				<td class="entry-value">
				    <p class="info">Autor<br />
				        {if $eventToShow->getGroupId() != null}
				        	    {group_info_link group=$eventToShow->getGroup()}
							    {assign var="group" value=$eventToShow->getGroup()}
							    <br />
							    <a href="{group_info_url group=$group}" >
							    		<img src="{$group->getPictureFile('tiny')|default:"/images/kegel_group.png"}" alt="Logo von {$group->name}" />
							    </a>
				        {else}
					        {assign var="author" value=$eventToShow->getAuthor()}
					        {user_info_link user=$author truncate=17}<br />
		            		<a href="{user_info_url  user=$author}" title="{$author->getUsername()}">
			            	<img src="{userpic_url tiny=$author}" alt="Userpic von {$author->getUsername()}" />
				            </a>
			            {/if}
	        	    </p>	
				</td>
				<td class="entry">
					
					<span class="button">
					
					{dynamic}
						{assign var="author" value=$eventToShow->getAuthor()}
						{if $visitor->hasGroupRight('CALENDAR_EVENT_GLOBAL_ADD',$eventToShow->getGroupId()) 
						 || $visitor->hasRight('CALENDAR_EVENT_ADMIN')
						 || $visitor->hasRight('CALENDAR_EVENT_GLOBAL_ADD_USER') && $visitor->equals($author)}
							<a href="{index_url events=1 eventId=$eventToShow->id edit=1 weeks=$weeksToShow}" title="Eintrag bearbeiten">
	          				<img src="{$TEMPLATE_DIR}/images/edit.png" alt="bearbeiten" /></a>
	          				
	          				<a href="{index_url events=1 eventId=$eventToShow->id delete=1 weeks=$weeksToShow}" title="Eintrag löschen">
	          				<img src="{$TEMPLATE_DIR}/images/delete.png" alt="löschen" /></a>
          				{/if}
      				{/dynamic}
      				</span>				
				
					<table class="event">
					<tr>
					 <td class="attribute">Zeitdauer:</td>
					 <td class="value">Von {$eventToShow->getStartDate()|unihelp_strftime} bis {$eventToShow->getEndDate()|unihelp_strftime}</td>
					</tr>
					<tr>
					 <td class="attribute">Termin&uuml;berschrift:</td>
					 <td class="value"><strong>{$eventToShow->getCaption()}</strong></td>
					</tr>
					<tr>
					 <td class="attribute">Terminbeschreibung:</td>
					 <td class="value">{$eventToShow->getContentParsed()}</td>
					</tr>
					</table>
					
				</td>
			</tr> 
			</tbody>
		</table>
	</div></div>
{/if}

{errorbox caption="Fehler beim Eintragen der Veranstaltung"}

	{if $visitor->hasRight('CALENDAR_EVENT_ADMIN') || 
	    $eventToEdit != null  ||
	    $visitor->hasGroupRight('CALENDAR_EVENT_GLOBAL_ADD') ||
	    $visitor->hasRight('CALENDAR_EVENT_GLOBAL_ADD_USER')}
						
<br />
<br />
<form enctype="multipart/form-data" action="{index_url events=1}" method="post">
	<fieldset>
		<input name="weeks" value="{$weeksToShow.index}" type="hidden" />
	 	
	 	{if $eventToEdit && $eventToEdit->id}
	 		<input name="EventId" value="{$eventToEdit->id}" type="hidden" />
	 		<input name="method" value="editEvents" type="hidden" />
	 	{else}
	 		<input name="method" value="addEvents" type="hidden" />
	 	{/if}
	                  
		{* random id to avoid double postings *}
		<input type="hidden" name="{$smarty.const.F_ENTRY_RANDID}" value="{$randid}" />
                  
		{if $central_errors.missingFieldsObj.caption}<span class="missing">{/if}
		<label for="caption">Termin&uuml;berschrift</label>
		<input type="text" name="caption" id="caption" size="60" {if $eventToEdit}value="{$eventToEdit->getCaption()}"{/if} /><br />
		{if $central_errors.missingFieldsObj.caption}</span>{/if}
                  
		<label for="groupId">Posten als</label>
 		<select id="groupId" name="groupId">
 		{if $eventToEdit!=null}
 			{if $eventToEdit->id != 0}
 				{if $eventToEdit->getGroupId()==null}
	 				<option value="0" selected="selected">User {$visitor->getUsername()}</option>
	 			{else}
	 			    <option value="{$group->id}">Organisation {$group->name}</option>
	 			{/if}
 			{else}
	 			{if $eventToEdit->getGroupId()==null}
	 				<option value="0" selected="selected">User {$visitor->getUsername()}</option>
	 			{else}
	 			    <option value="0">User {$visitor->getUsername()}</option>
	 			{/if}
	 			{foreach from=$visitor->getGroupsByRight('CALENDAR_EVENT_GLOBAL_ADD') item=group}
	 			    {if $eventToEdit->getGroupId()==$group->id}
						<option value="{$group->id}" selected="selected">Organisation {$group->name}</option>
					{else}
						<option value="{$group->id}">Organisation {$group->name}</option>
					{/if}		
				{/foreach}
			{/if}	
 		{else}
 			{if $visitor->hasRight('CALENDAR_EVENT_GLOBAL_ADD_USER')}
 			<option value="0">User {$visitor->getUsername()}</option>
 			{/if}
			{foreach from=$visitor->getGroupsByRight('CALENDAR_EVENT_GLOBAL_ADD') item=group}
				<option value="{$group->id}">Organisation {$group->name}</option>
			{/foreach}
		{/if}
		</select>
		
		{*Todo: Einstellung für wen angezeigt beim Editieren*}
 		{*<select id="Visible" name="Visible">
 		<option value="0">f&uuml;r mich privat </option>
 		<option value="-1">f&uuml;r meine Freunde </option>
 		{foreach from=$visitor->getGroupsByRight('CALENDAR_EVENT_GLOBAL_ADD') item=group}
		<option value="{$group->id}">f&uuml;r Organisation {$group->name}</option>
		{/foreach}
 		<option value="-2">f&uuml;r alle eingelockten User</option>
 		<option value="-3">f&uuml;r alle User</option>
		</select>*}
		<br />
		{if $central_errors.startDate}<span class="missing">{/if}
			<label>Termin von</label>
			{if $eventToEdit}
		    	{html_select_date end_year=+1 field_order="DMY" time=$eventToEdit->getStartDate() prefix=start day_extra="id='eventStartDateDay'" month_extra="id='eventStartDateMonth'" year_extra="id='eventStartDateYear'"} 
		    	{html_select_time prefix=start time=$eventToEdit->getStartDate() display_seconds=false minute_interval=15}
		    {else}
		       	{html_select_date end_year=+1 field_order="DMY" prefix=start day_extra="id='eventStartDateDay'" month_extra="id='eventStartDateMonth'" year_extra="id='eventStartDateYear'"} 
		       	{html_select_time prefix=start display_seconds=false minute_interval=15}
		    {/if}
		{if $central_errors.startDate}</span>{/if}
		{if $central_errors.endDate}<span class="missing">{/if}
			<br/><label>Termin bis</label>                  		 
			{if $eventToEdit}
		    	{html_select_date end_year=+1 field_order="DMY" time=$eventToEdit->getEndDate() prefix=end day_extra="id='eventEndDateDay'" month_extra="id='eventEndDateMonth'" year_extra="id='eventEndDateYear'"}
		    	{html_select_time prefix=end time=$eventToEdit->getEndDate() display_seconds=false minute_interval=15} 
		    {else}
		       	{html_select_date end_year=+1 field_order="DMY" prefix=end day_extra="id='eventEndDateDay'" month_extra="id='eventEndDateMonth'" year_extra="id='eventEndDateYear'"} 
		       	{html_select_time prefix=end display_seconds=false minute_interval=15}
		    {/if}
		{*
		<label for="whole_day">ganzt&auml;gig</label> 
		<input name="whole_day" id="whole_day" type="checkbox" />
		*}
		<br />
		{if $central_errors.endDate}</span>{/if}		 
		<br />
		{if $central_errors.missingFieldsObj.descriptionText}<span class="missing">{/if}
			<label for="description">Terminbeschreibung</label>
			<textarea name="descriptionText" id="opener" rows="5" cols="45">{if $eventToEdit}{$eventToEdit->getContentRaw()}{/if}</textarea><br />
		{if $central_errors.missingFieldsObj.descriptionText}</span>{/if}
		
		
		{* activate smileys and bbcode by default for new entries (!$editguestbookdata.content_raw) *}
		<label for="enable_smileys">Smileys aktivieren</label>
		<input name="enable_smileys" id="enable_smileys" {if $entryToEdit == null || ($entryToEdit != null && $entryToEdit->isParseAsSmileys())}checked="checked" {/if} type="checkbox" />
		<br />
		<label for="enable_formatcode">BBCode aktivieren</label>
		<input name="enable_formatcode" id="enable_formatcode" {if $entryToEdit == null || ($entryToEdit != null && $entryToEdit->isParseAsFormatcode())}checked="checked" {/if}type="checkbox" />
		<br />

 		
		<input type="submit" name="save" value="Termin speichern"  />
		{if $isPreview}<input type="hidden" name="preview_flag" value="1" />{/if}
		<input name="preview_submit" value="Vorschau" type="submit" id="news_preview_submit" />
                  

	</fieldset>		
	</form>
	{else}
		<p>Wenn Du auch einen Eintrag in den Kalender zu machen hast, der alle etwas angeht, dann <a href="{mantis_url calender=1}">melde Dich bei uns</a>
		</p>
	{/if}
{/dynamic}
