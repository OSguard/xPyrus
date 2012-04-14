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

*}{* <h2 id="pagename">##edit_courses_edit##</h2> *}

{if $ackNeeded}
	<h2>##dataChanged##</h2>
	<br class="clear" />
	<p><a href="{admin_url}">##backToAdmin##</a>&nbsp;|&nbsp;<a href="{admin_url coursesEdit=true}">##backToEdit## "##edit_courses_edit##"</a></p>
{else}

<script type="text/javascript">
courses = new Array();
{foreach item=course from=$courses}
	{assign var="name" value=$course->getName()}
	courses[courses.length] = new Array({$course->id}, "{$name|escape:html|truncate:70:"..."}");
{/foreach}

filteredCourses = new Array();
{foreach item=course from=$filteredCourses}
	filteredCourses[filteredCourses.length] = {$course};
{/foreach}
</script>

<div class="shadow"><div>
<h3>##courses##</h3>
{errorbox}

<form action="{admin_url coursesEdit=true}" method="post">
<input type="hidden" name="id" value="{$id}" />

{*Filterkopf*}
{if $central_errors.missingFieldsObj.courseBox}<span class="missing">{/if}
<label for="courseBox">##course##</label>
<select name="courseBox[]" id="courseBox" size="5" multiple="multiple" class="wide">
	<option value="0">##noFilter##</option>
	<script type="text/javascript">
		setOptions("courseBox", courses, filteredCourses);
	</script>
</select>
{if $central_errors.missingFieldsObj.courseBox}</span>{/if}

<br class="clear" />
<strong>##or##</strong>
{if $central_errors.missingFieldsObj.description}<span class="missing">{/if}
<label for="courseDescription">##name## (##containsText##)</label>
<input type="text" name="courseDescription" id="courseDescription" size="30" maxlength="40" value="{$filteredDescription}" />
{if $central_errors.missingFieldsObj.description}</span>{/if}

<!--br class="clear" />
<label for="order_filter">##sort##</label>
<select id="order_filter" name="order">
	<option value="0">##noCriterion##</option>
	<option value="time" {if $coursefiles_orderstring == 'time'}selected="selected"{/if}>##upToDateness##</option-->
	<!--option value="author" {if $coursefiles_orderstring == 'author'}selected="selected"{/if}>##author##</option-->
	<!--option value="description" {if $coursefiles_orderstring == 'description'}selected="selected"{/if}>##description##</option>
	<option value="filename" {if $coursefiles_orderstring == 'filename'}selected="selected"{/if}>##filename##</option>
	<option value="downloads" {if $coursefiles_orderstring == 'downloads'}selected="selected"{/if}>##downloads##</option-->
	<!--option value="category" {if $coursefiles_orderstring == 'course'}selected="selected"{/if}>##course##</option-->
	<!--option value="category" {if $coursefiles_orderstring == 'category'}selected="selected"{/if}>##category##</option>
	<option value="costs" {if $coursefiles_orderstring == 'costs'}selected="selected"{/if}>##price##</option>
	<option value="semester" {if $coursefiles_orderstring == 'semester'}selected="selected"{/if}>##semester##</option>
</select>
<select id="order_by" name="orderDir">
	<option value="asc" {if $coursefiles_orderDirstring == 'asc'}selected="selected"{/if}>##ascending##</option>
	<option value="desc" {if $coursefiles_orderDirstring == 'desc'}selected="selected"{/if}>##descending##</option>
</select-->
<br class="clear" />
<input type="submit" name="filter" value="##applyFilter##" accesskey="f" />
<input type="submit" name="filter_reset" value="##resetFilter##" accesskey="z" />
</form>
<br class="clear" />
<form action="{admin_url coursesEdit=true}" method="post">
<input type="hidden" name="id" value="{$id}" />
{*Hier beginnt die Ausgabetabelle*}
{if $startOutput && count($coursesToShow)>0}
<table class="centralTable nopadding clear">
	<thead>
	    <tr>
	    <th>##edit_courses_courseName##</th>
	    <th>##edit_courses_courseName## (##english##)</th>
		<th>##edit_courses_courseName## (##short##)</th>
	    </tr>
	</thead>
	<tbody>
		{foreach item=course from=$coursesToShow}
			<tr>
				<td>
				<input type="text" name="course_{$course->id}" id="course_{$course->id}" size="30" maxlength="250" value="{$course->getName()}" />
				</td>
				<td>
				<input type="text" name="courseEng_{$course->id}" id="courseEng_{$course->id}" size="30" maxlength="250" 
					value="{$course->getNameEnglish()}" />
				</td>
				<td>
				<input type="text" name="courseShort_{$course->id}" id="courseShort_{$course->id}" size="30" maxlength="250" 
					value="{$course->getNameShort()}" />
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>
<br class="clear" />
<input type="submit" name="save" value="##edit_courses_save##" accesskey="s" />
<input type="reset" name="reset" value="##edit_courses_reset##" />
</form>
{/if}{*startOutput*}
<br class="clear" />
<p><a href="{admin_url}">##backToAdmin##</a>&nbsp;|&nbsp;<a href="{admin_url coursesEdit=true}">##backToEdit## "##edit_courses_edit##"</a></p>
</div></div>
{/if}
<div class="shadow"><div><h3>##edit_courses_newCourse##</h3>	<fieldset>	<form action="{admin_url coursesEdit=true}" method="post">	{if $courseToEdit != null}<input type="hidden" name="course_id" value="{$courseToEdit->id}" >{/if}	<input type="hidden" name="id" value="{$id}" />	<label for="course_name">##edit_courses_courseName##</label>	<input typ="text" name="course_name" {if $courseToEdit != null} value="{$courseToEdit->name}" {/if} size="15">	<br class="clear"/>	<label for="course_name_english">##edit_courses_courseName## (##english##)</label>	<input typ="text" name="course_name_english" {if $courseToEdit != null} value="{$courseToEdit->nameEnglish}" {/if} size="15">	<br class="clear"/>	<label for="course_name_short">##abbreviation##</label>	<input typ="text" name="course_name_short" {if $courseToEdit != null} value="{$courseToEdit->nameShort}" {/if} size="15">	<br class="clear"/>	<input type="submit" name="saveNew" value="##submit##"  />	</form>	</fieldset></div></div>
