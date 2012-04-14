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

*}{* $Id: courses.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{if !$box_courses_ajax}
<div class="box" id="box_courses:1">
<h3>Deine F&auml;cher</h3>
{dynamic}
{if $visitor->hasRight('FEATURE_BOX_REARRANGEMENT')}
<a href="{box_functions box=courses close=true}{*/index.php?dest=box&amp;bname=courses&amp;method=close*}" class="icon iconClose" title="Box schließen" id="courses:{$instance}_close"><span>x</span></a>
{if !$box_courses_minimized}
<a href="{box_functions box=courses minimize=true}{*/index.php?dest=box&amp;bname=courses&amp;method=minimize*}" class="icon iconMinimize" id="courses:1_collapse" title="Box minimieren"><span>_</span></a>
{else}
<a href="{box_functions box=courses maximize=true}{*/index.php?dest=box&amp;bname=courses&amp;method=maximize*}" class="icon iconMaximize" id="courses:1_collapse" title="Box maximieren"><span>O</span></a>
{/if}{* end minimized *}
{/if}{* end rights check *}
{/dynamic}
{/if}{* end ajax *}

{if !$box_courses_minimized}
    <ul class="boxcontent vertical courseBox">
        {foreach from=$box_courses_courses item=course}
        <li><a href="{course_url course=$course}"
        	   title="Zur Homepage von {$course->getName()}">
            {$course->getNameShortSafe()|escape:"html"}</a></li>
        {foreachelse}
        <li>Du hast momentan keine F&auml;cher ausgew&auml;hlt. Das kannst Du in <a style="display: inline; margin: 0; padding: 0; text-decoration: underline;" href="{user_management_url courses=$visitor}">Deinem Profil</a> &auml;ndern</li>
        {assign var=nolink value=true}
        {/foreach}
    </ul>
    {if !$nolink}
        	<p class="boxcontent"><a href="{user_management_url courses=$visitor}">Fächer verwalten</a></p>
    {/if}	
{/if}

{if !$box_courses_ajax}
</div>
{/if}
