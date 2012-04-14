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

*}{* $Id: courses_files.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{if !$box_courses_files_ajax}
<div class="box box-coursefiles" id="box_courses_files:{$instance}">
<h3>Unterlagen</h3>
{dynamic}
{if $visitor->hasRight('FEATURE_BOX_REARRANGEMENT')}
<a href="{box_functions box=courses_files instance=$instance close=true}{*/index.php?dest=box&amp;bname=courses_files&amp;instance={$instance}&amp;method=close*}" class="icon iconClose" title="Box schließen" id="courses_files:{$instance}_close"><span>x</span></a>
{if !$box_courses_files_minimized}
<a href="{box_functions box=courses_files instance=$instance minimize=true}{*/index.php?dest=box&amp;bname=courses_files&amp;instance={$instance}&amp;method=minimize*}" class="icon iconMinimize" id="courses_files:{$instance}_collapse" title="Box minimieren"><span>_</span></a>
{else}
<a href="{box_functions box=courses_files instance=$instance maximize=true}{*/index.php?dest=box&amp;bname=courses_files&amp;instance={$instance}&amp;method=maximize*}" class="icon iconMaximize" id="courses_files:{$instance}_collapse" title="Box maximieren"><span>O</span></a>
{/if}{* end minimized *}
{/if}{* end rights check *}
{/dynamic}
{/if}{* end ajax *}

{if !$box_courses_files_minimized}
{if $spezialCourse}
 <h4 class="boxcontent">Neue Unterlagen des Faches <em><a href="{course_url course=$spezialCourse}" title="{$spezialCourse->getName()}">{$spezialCourse->getNameShortSafe()|truncate:22:"...":true}</a></em></h4>
 <ul class="boxcontent vertical">
 {foreach from=$spezialCourseFile item=file key=name}
            <li>
            {assign var="course" value=$file->getCourse()}
            <a class="file-link" 
            href="{course_url courseFile=$file}"  title="{$file->getFileName()}">
                    {$file->getFileName()|truncate:22:"...":true}
            </a>
            {$file->getInsertAt()|unihelp_strftime} von {user_info_link user=$file->getAuthor() truncate=18}
            </li>
 {/foreach}
 </ul>
{else}
    {if $personal}
    <h4 class="boxcontent">Die letzten Unterlagen Deiner Fächer</h4>
    {else}
    <p  class="boxcontent"><strong>Die letzten von insgesamt {$box_courses_files_total_number} Unterlagen.</strong></p>
    {/if}
    <ul class="boxcontent vertical">
    {foreach item=file from=$box_courses_files_latest_files}
            <li style="overflow: hidden">
            {assign var="course" value=$file->getCourse()}
            <a class="file-link"
            		href="{course_url courseFile=$file}" title="{$file->getFileName()}">
                    {$file->getFileName()|truncate:22:"...":true}
            </a>
            {assign var="course" value=$file->getCourse()}
            in <em><a href="{course_url course=$course}">{$course->getNameShortSafe()|truncate:18:"...":true}</a></em>,
            {$file->getInsertAt()|unihelp_strftime} von {user_info_link user=$file->getAuthor() truncate=18}
            </li>
    {/foreach}
    </ul>
{/if}

{if $visitor->isLoggedin() && !$visitor->isExternal()}
	<form class="boxcontent" action="/index.php?dest=box&amp;method=setCourse&amp;bname=courses_files&amp;instance={$instance}" method="post">
  	  <select name="course" >
  	     <option value="all" title="Alle Fächer" {if !$personal && !$spezialCourse}selected="selected"{/if}>Alle Fächer</option>
  	     <option value="allOwn" title="Alle meine Fächer" {if $personal && !$spezialCourse}selected="selected"{/if}>Alle meine Fächer</option>  	     
	  	  {foreach from=$visitor->getCourses() item=course}
	        <option value="{$course->id}" {if $course->id == $spezialCourse->id}selected="selected"{/if} title="{$course->getName()}">
	        	{$course->getName()|truncate:22:"...":true}
	        </option>
	      {/foreach}
      </select>
	  <input type="submit" name="submit" value="Abschicken"/>
	  <br class="clear" />
  </form>
 
{/if}

{/if}{* box minimized *}

{if !$box_courses_files_ajax}
</div>
{/if}
