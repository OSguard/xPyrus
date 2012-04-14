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

*}{* <h2 id="pagename">##edit_files_edit##</h2> *}

{if $ackNeeded}
	<h2>##dataChanged##</h2>
	<br class="clear" />
	<p><a href="{admin_url}">##backToAdmin##</a>&nbsp;|&nbsp;<a href="{admin_url editFiles=true}">##backToEdit## "##edit_files_edit##"</a></p>

{else}

<script type="text/javascript">
courses = new Array();
{foreach item=course from=$courses}
	{assign var="name" value=$course->getName()}
	courses[courses.length] = new Array({$course->id}, "{$name|escape:html|truncate:70:"..."}");
{/foreach}

semester = new Array();
{foreach item=semester from=$coursefilessemesters}
	{assign var="name" value=$semester->getName()}
	semester[semester.length] = new Array({$semester->id}, "{$name|escape:html}");
{/foreach}

category = new Array();
{foreach item=cat from=$coursefilescategories}
	{assign var="name" value=$cat->getName()}
	category[category.length] = new Array({$cat->id}, "{$name|escape:html}");
{/foreach}
	
filteredCourses = new Array();
{foreach item=course from=$filteredCourses}
	filteredCourses[filteredCourses.length] = {$course};
{/foreach}

filteredCategory = new Array();
filteredCategory[filteredCategory.length] = {$filteredCategory};

filteredSemester = new Array();
filteredSemester[filteredSemester.length] = {$filteredSemester};

</script>

<div class="shadow"><div>
<h3>##documents##</h3>
{errorbox}

<form action="{admin_url editFiles=true}" class="file-info3" method="post">
<input type="hidden" name="id" value="{$id}" />

{*Filterkopf*}
{if $central_errors.missingFieldsObj.course}<span class="missing">{/if}
<label for="courseBox">##course##</label>
<select name="courseBox[]" id="courseBox" size="5" multiple="multiple" class="wide">
	<option value="0">##noFilter##</option>
	<script type="text/javascript">
		setOptions("courseBox", courses, filteredCourses);
	</script>
</select>
{if $central_errors.missingFieldsObj.course}</span>{/if}
<br class="clear" />
<label for="category">##category##</label>
<select name="category" id="category" size="1">
	<option value="0">##noFilter##</option>
	<script type="text/javascript">
		setOptions("category", category, filteredCategory);
	</script>
</select>
<br class="clear" />
<label for="semester">##semester##</label>
<select name="semester" id="semester" size="1">
	<option value="0">##noFilter##</option>
	<script type="text/javascript">
		setOptions("semester", semester, filteredSemester);
	</script>
</select>
<br class="clear" />
<label for="description">##description## (##containsText##)</label>
<input type="text" name="description" id="description" size="30" maxlength="40" value="{$filteredDescription}" />
<br class="clear" />
<label for="filename">##filename## (##containsText##)</label>
<input type="text" name="filename" id="filename" size="30" maxlength="40" value="{$filteredFilename}" />
<br class="clear" />
<label for="order_filter">##sort##</label>
<select id="order_filter" name="order">
	<option value="0">##noCriterion##</option>
	<option value="time" {if $coursefiles_orderstring == 'time'}selected="selected"{/if}>##upToDateness##</option>
	<!--option value="author" {if $coursefiles_orderstring == 'author'}selected="selected"{/if}>##author##</option-->
	<option value="description" {if $coursefiles_orderstring == 'description'}selected="selected"{/if}>##description##</option>
	<option value="filename" {if $coursefiles_orderstring == 'filename'}selected="selected"{/if}>##filename##</option>
	<option value="downloads" {if $coursefiles_orderstring == 'downloads'}selected="selected"{/if}>##downloads##</option>
	<!--option value="category" {if $coursefiles_orderstring == 'course'}selected="selected"{/if}>##course##</option-->
	<option value="category" {if $coursefiles_orderstring == 'category'}selected="selected"{/if}>##category##</option>
	<option value="costs" {if $coursefiles_orderstring == 'costs'}selected="selected"{/if}>##price##</option>
	<option value="semester" {if $coursefiles_orderstring == 'semester'}selected="selected"{/if}>##semester##</option>
</select>
<select id="order_by" name="orderDir">
	<option value="asc" {if $coursefiles_orderDirstring == 'asc'}selected="selected"{/if}>##ascending##</option>
	<option value="desc" {if $coursefiles_orderDirstring == 'desc'}selected="selected"{/if}>##descending##</option>
</select>
<br class="clear" />
<input type="submit" name="filter" value="##applyFilter##" accesskey="f" />
<input type="submit" name="filter_reset" value="##resetFilter##" accesskey="z" />
</form>
<br class="clear" />
{*Hier beginnt die Ausgabetabelle*}
{if $startOutput && count($coursefiles)>0}
<table class="centralTable nopadding clear">
	<thead>
	    <tr>
	    <th>##points##</th>
	    <th>##file##/##description##/##rating##</th>
	    <th>##info##</th>
		<th>##editOptions##</th>
		<!--th>##delete##</th-->
	    </tr>
	</thead>
	<tbody>
		{foreach item=coursefile from=$coursefiles}
		{assign var="courseFileId" value=$coursefile->id}
			<tr>
				<td>
				{assign var="costs" value=$coursefile->getCosts()}
				{$costs} ##point(s)##
				{*<label for="costs_{$coursefile->id}">##points##</label>
				<select name="costs_{$coursefile->id}" id="costs_{$coursefile->id}" size="1" style="width: 100%">
					<option value="1" {if $costs==1}selected="selected"{/if}>1 ##point##</option>
				    <option value="2" {if $costs==2}selected="selected"{/if}>2 ##points##</option>
				    <option value="3" {if $costs==3}selected="selected"{/if}>3 ##points##</option>
				    <option value="4" {if $costs==4}selected="selected"{/if}>4 ##points##</option>
				    <option value="5" {if $costs==5}selected="selected"{/if}>5 ##points##</option>
				    <option value="6" {if $costs==6}selected="selected"{/if}>6 ##points##</option>
				    <option value="7" {if $costs==7}selected="selected"{/if}>7 ##points##</option>
				    <option value="8" {if $costs==8}selected="selected"{/if}>8 ##points##</option>
				    <option value="9" {if $costs==9}selected="selected"{/if}>9 ##points##</option>
				    <option value="10" {if $costs==10}selected="selected"{/if}>10 ##points##</option>
				</select>*}
					{$coursefile->getDownloadNumber()} Download{if $coursefile->getDownloadNumber() > 1 or $coursefile->getDownloadNumber() < 1}s{/if}
				</td>
				<td class="file-name" style="width: 100%; height: 200px">
					<p class="clear">
					<span style="float: right">
						{if $coursefile->getRatingQuickvoteInt()}
							<img src="/images/bewertungen/course_{$coursefile->getRatingQuickvoteInt()}.png" alt="##positiveRating##" />
						{/if}</span>
					<a class="{$coursefile->getFileType()} file" href="{course_url courseFile=$coursefile}">
						{$coursefile->getFileName()|escape:html}
					</a> ({$coursefile->getFileSize()} KB)
					</p>
					<p class="clear">
					<span style="font-weight: bold;">##description##: </span>{$coursefile->getDescription()|escape:html}</p>
				</td>
				<td>
					<select id="semester_{$courseFileId}" name="semester_{$courseFileId}" size="1" style="width: 100%" disabled="disabled">
					{assign var="semID" value=$coursefile->getSemesterId()}
					<script type="text/javascript">
						tmp = new Array();
						tmp[tmp.length] = {$semID};
						setOptions("semester_{$coursefile->id}", semester, tmp);
					</script>
					</select>
					
					<br class="clear" />
					<select id="category_{$courseFileId}" name="category_{$courseFileId}" size="1" style="width: 100%" disabled="disabled">
					{assign var="catID" value=$coursefile->getCategoryId()}
					<script type="text/javascript">
						tmp = new Array();
						tmp[tmp.length] = {$catID};
						setOptions("category_{$coursefile->id}", category, tmp);
					</script>
					</select>
					
					<br class="clear" />
					{assign var="tmp" value=$coursefile->getCourse()}
					{$tmp->getName()}
					<!--select id="course_{$courseFileId}" name="course_{$courseFileId}" style="width: 100%" disabled="disabled">
						<script type="text/javascript">
							{assign var="tmp" value=$coursefile->getCourse()}
							{assign var="courseID" value=$tmp->id}
							selectedCourse = new Array();
							selectedCourse[selectedCourse.length] = {$courseID};
							setOptions("course_{$coursefile->id}", courses, selectedCourse);
						</script>
					</select-->
				</td>
				<td>
					<a href="{admin_url freeDownloadFile=$courseFileId}">##edit_files_freeDownload##</a>
					<br class="clear" />					<a href="{course_url courseFileEdit=$coursefile}#post">##edit_files_editFile##</a>
					<br class="clear" />	
					<a onClick="a = confirm('{$coursefile->getFileName()}: ##edit_files_confirmDelete##'); if (a) window.location.href='{admin_url deleteFile=$courseFileId}';return false;" href="#">##edit_files_deleteCompleteFile##</a>
					<br class="clear" />
					<a href="{course_url courseFile=$coursefile}#course-files-revisions">##edit_files_deletePartFile##</a>
					<br class="clear" />
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>
{/if}{*startOutput*}
<br class="clear" />
<p><a href="{admin_url}">##backToAdmin##</a>&nbsp;|&nbsp;<a href="{admin_url editFiles=true}">##backToEdit## "##edit_files_edit##"</a></p>
</div></div>
{/if}{*ackNeeded*}
