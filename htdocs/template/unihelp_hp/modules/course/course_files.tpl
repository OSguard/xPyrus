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

*}{*	$Id: course_files.tpl 5807 2008-04-12 21:23:22Z trehn $
	$HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/course/course_files.tpl $	*}

<div class="shadow"><div>
<h3>Erkl&auml;rung</h3>
    <ul class="bulleted">
    <li>Auf dieser Seite findest Du alle Unterlagen, die Kommilitonen von Dir für dieses Fach hochgeladen haben. Hilf Deinen Kommilitonen, indem Du die Unterlagen kommentierst und bewertest.
    </li>
    </ul>
</div></div>

{dynamic}
{if $coursefiles_unrated}
	<div style="border: 3px solid red; color: red; font-weight: bold; margin: 10px; padding:10px;">
	<ol>
	{foreach item=file from=$coursefiles_unrated}
		<li>{$file->getFileName()} noch nicht bewertet! <a href="{course_url rateFile=$file}">Klick hier</a>, um das zu ändern.</li>
	{/foreach}
	</ol>
	</div>
{/if}

{* show error box on upload twice,
   so that user really will notice his error *}
{errorbox caption="Fehler beim Upload"}
{/dynamic}

<div class="shadow"><div>
<h3>Sortieren &amp; Filtern</h3>
		
		<form class="file-info3" action="{course_url course=$course showFiles=1}" method="post">
		    <label for="category_filter">Filter nach Kategorie</label>
		    <select id="category_filter" name="category_filter">
		        <option value="0">Kein Filter</option>
		        {foreach item=category from=$coursefilescategories}
		        <option value="{$category->id}" {if $coursefiles_categoryfilter->id==$category->id}selected="selected"{/if}>{$category->getName()|escape:html}</option>
		        {/foreach}
		    </select>
		    <br class="clear" />
		    <label for="semester_filter">Filter nach Semester</label>
		    <select id="semester_filter" name="semester_filter">
		        <option value="0">Kein Filter</option>
		        {foreach item=semester from=$coursefilessemesters}
		        <option value="{$semester->id}" {if $coursefiles_semesterfilter->id==$semester->id}selected="selected"{/if}>{$semester->getName()|escape:html}</option>
		        {/foreach}
		    </select>
		    <br class="clear" />
		    <label for="rating_filter">Filter nach Bewertung</label>
		    <select id="rating_filter" name="rating_filter">
		        <option value="0">Keine Einschränkung</option>
				<option value="3.5" {if $coursefiles_ratingfilter==3.5}selected="selected"{/if}>mittelmäßig</option>
				<option value="5" {if $coursefiles_ratingfilter==5}selected="selected"{/if}>sehr gut</option>
		    </select>
		    <br class="clear" />
		    
		    <label for="order_filter">sortieren</label>
		    <select id="order_filter" name="order">
		        <option value="0">Kein Kriterium</option>
		        <option value="costs" {if $coursefiles_orderstring == 'costs'}selected="selected"{/if}>Preis</option>
		        <option value="time" {if $coursefiles_orderstring == 'time'}selected="selected"{/if}>Aktualität</option>
		        <option value="downloads" {if $coursefiles_orderstring == 'downloads'}selected="selected"{/if}>Downloads</option>
		    </select>
		    <select id="order_by" name="orderDir">
		        <option value="asc" {if $coursefiles_orderDirstring == 'asc'}selected="selected"{/if}>aufsteigend</option>
		        <option value="desc" {if $coursefiles_orderDirstring == 'desc'}selected="selected"{/if}>absteigend</option>
		    </select>
		    <input type="submit" name="file_filter" value="Jetzt Filtern" />
		    <input type="submit" name="file_filter_reset" value="Filter zurücksetzen" />
		</form>
	<br class="clear" />
</div></div>


<div class="shadow"><div class="nopadding">
<h3>Unterlagen</h3>
<div id="coursefile_counter" class="counter">{strip}
  Seitenauswahl:
  {foreach from=$coursefiles_counter item=bc name=coursefilecounter}
    {if $bc==$coursefiles_page}
      <strong>
    {else}
      <a href="{course_url course=$course showFiles=1 page=$bc order=$coursefiles_orderstring}">
    {/if}
      {$bc}
    {if $bc==$coursefiles_page}
      </strong>
    {else}
      </a>
    {/if}
    {if !$smarty.foreach.coursefilecounter.last}
      {* if not last loop, output whitespace to separate entries *}
      &nbsp;
    {/if}
  {/foreach}
  {/strip}
  </div>


<table class="centralTable nopadding clear">
	<thead>
	    <tr>
	    <th style="width: 15%">Punkte</th>
	    <th style="width: 75%">Name</th>
	    <th style="width: 10%">Info</th>
	    </tr>
	</thead>
	<tbody>
		{foreach item=coursefile from=$coursefiles_files}
			<tr>
				<td>
					{if $coursefile->downloaded}
						<span style="font-weight: bold; color: navy;">bezahlt</span>
					{else}
						<strong>{$coursefile->getCosts()}</strong> Punkt{if $coursefile->getCosts() > 1}e{/if}
					{/if}
					<br />
					{$coursefile->getDownloadNumber()} Download{if $coursefile->getDownloadNumber() > 1}s{/if}
				</td>
				<td class="file-name">
					<span style="float: right">
						{if $coursefile->getRatingQuickvoteInt()}
							<img src="/images/bewertungen/course_{$coursefile->getRatingQuickvoteInt()}.png"  title="{course_rating_desc rating=$coursefile->getRatingQuickvoteInt()}" alt="positive Bewertung" />
						{else}
							&nbsp;
						{/if}</span>
					<p class="{$coursefile->getFileType()} file"><a class="file" href="{course_url courseFile=$coursefile}">
						{$coursefile->getFileName()}
					</a> ({$coursefile->getFileSize(true)} KB, {foreach from=$coursefile->getRevisions() item="rev" name="rev"}{if $smarty.foreach.rev.last}<a title="Mit einem Klick saugen und sofort bezahlen!" href="{course_url getFile=$rev}">Sofort-Download</a>{/if}{/foreach})
					<br />
					{user_info_link user=$coursefile->getAuthor()}, {$coursefile->getInsertAt()|unihelp_strftime}<br />
					<span style="font-weight: bold;">Beschreibung: </span>{$coursefile->getDescription()}</p>
				</td>
				<td>
                    {assign var="category" value=$coursefile->getCategory()}
                    {assign var="semester" value=$coursefile->getSemester()}
					{$semester->getName()}<br />
					{$category->getName()}
				</td>
			</tr>
			{foreachelse}
    		<tr>
    			<td colspan="3" class="emptyTable">
    				Für dieses Fach existieren noch keine Unterlagen.
    			</td>
    		</tr>
		{/foreach}
	</tbody>
</table>
</div>

	<div class="counter counterbottom">{strip}
	  Seitenauswahl:
	  {foreach from=$coursefiles_counter item=bc name=coursefilecounter}
	    {if $bc==$coursefiles_page}
	      <strong>
	    {else}
	      <a href="{course_url course=$course showFiles=1 page=$bc order=$coursefiles_orderstring}">
	    {/if}
	      {$bc}
	    {if $bc==$coursefiles_page}
	      </strong>
	    {else}
	      </a>
	    {/if}
	    {if !$smarty.foreach.coursefilecounter.last}
	      {* if not last loop, output whitespace to separate entries *}
	      &nbsp;
	    {/if}
	  {/foreach}
	  {/strip}
	  </div>

</div>

{* file upload *}

{dynamic}

{* show error box on upload twice,
   so that user really will notice his error *}
{errorbox caption="Fehler beim Upload"}

{if $visitor->hasRight('COURSES_FILE_UPLOAD')}
	<div class="shadow"><div>
	<h3><a name="post">Datei ver&ouml;ffentlichen</a></h3>
	<form action="{course_url addCourseFile=$course->id}" method="post" enctype="multipart/form-data">
	{if !$ie6}
		{include file="modules/course/course_file_add_form.tpl"}
	{else}
		{include file="modules/course/course_file_add_form_ie.tpl"}
	{/if}
	</form>
	</div></div>
{/if} {* may upload *}

{/dynamic}
