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

*}{* $Id: courses_merge.tpl 5807 2008-04-12 21:23:22Z trehn $
   $HeadURL$
   *}

{errorbox}

<div class="shadow"><div>
<h3>##courses_merge_merge##</h3>

{if $success}
<p>##courses_merge_success##</p>
{/if}

<form action="{admin_url coursesMerge=true}" onsubmit="return confirm('##courses_merge_merge##');" method="post">

<label for="course1">##course## 1</label>
<select id="course1" name="course1">
{capture name="courses"}
<option value="0">-- ##pleaseChoose## --</option>
{foreach item=course from=$courses}
    <option value="{$course->id}">{$course->getName()|escape:html|truncate:70:"..."}</option>
{/foreach}
{/capture}
{$smarty.capture.courses}
</select>

<br />

<label for="course2">##course## 2</label>
<select id="course2" name="course2">
{$smarty.capture.courses}
</select>

<br />

<strong>##courses_merge_warning##</strong>

<input type="submit" value="##courses_merge_merge##" name="merge" />
<br class="clear" />

</div></div>
