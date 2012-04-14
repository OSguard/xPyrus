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

*}{*<h2>Studium00gl3 - beta!!</h2>*}

<div class="shadow"><div style="padding: 20px;">
<h3>Suchen</h3>
<form action="{course_url }" method="post">
<input type="text" name="{$smarty.const.F_SEARCH_QUERY}" value="{$query}" style="width: 70%; float: none;" />
<input type="submit" name="{$smarty.const.F_SEARCH_SUBMIT}" value="Suchen" style="float: none;" />
{* wir koennten auch ueber eine dropdown-box die max. anzahl der resultate regulieren *}
{* wir koennten auch ueber zusaetzlich nach autor filtern *}
</form>
<br class="clear" />
{if $findCourse}
<strong>Fächer gefunden:</strong>
<ul style="height: 6em; overflow: scroll">
	{foreach from=$findCourse item="course"}
		<li>
			<a href="{course_url course=$course}"  stlye="align: left">{$course->getName()}</a>
		</li>
	{/foreach}
</ul>	
{/if}
</div></div>

{if $query != '' && !$threadEntries && !$courseFiles}
...leider nix gefunden!
{/if}

{if $query != '' && $threadEntries}
<div class="shadow"><div>
<h3>passende Threads gefunden</h3>
<ul class="bulleted">
{foreach from=$threadEntries item="entry"}
<li style="padding: 10px; font-size: 1.3em;">
	{assign var="thread" value=$entry->getThread()}
	<a href="{forum_url thread=$thread}" >{$thread->getCaption()}</a> in
	{assign var="forum" value=$entry->getForum()}
	<a href="{forum_url forum=$forum}"  style="color: #000;">{$forum->getName()}</a>
</li>
{/foreach}
</ul>
</div></div>
{/if}

{if $query != '' && $courseFiles}
<div class="shadow"><div>
<h3>passende Unterlagen gefunden</h3>
<ul id="course-files-revisions">
{foreach from=$courseFiles item="coursefile"}
<li class="course-file">
	<a class="{$coursefile->getFileType()} file" href="{course_url courseFile=$coursefile}">
		{$coursefile->getFileName()}
	</a>	
	{if $coursefile->getRatingQuickvoteInt()}
		( <img src="/images/bewertungen/course_{$coursefile->getRatingQuickvoteInt()}.png"  title="{course_rating_desc rating=$coursefile->getRatingQuickvoteInt()}" alt="positive Bewertung" /> )
	{/if}
	<br />

	<span style="font-weight: bold;">Beschreibung: </span>{$coursefile->getDescription()}
</li>
{/foreach}
</ul>
</div></div>
{/if}


{if !$query}
	
	<div id="course-forum" class="course-left, shadow">
	<div class="nopadding">
	<h4 class="someHead">Foren Deiner Fächer</h4>
			<p class="boxlink">
			<a href="{forum_url latest=true}?show=studies">neuste Studien-Beiträge</a>
		</p>
	<ul class="margin">
		{foreach from=$courseThreads item=thread}
			<li class="course-thread">{forum_link thread=$thread title="Zu dem Thread"}
				{forum_new thread=$thread}
				<p>
		        {assign var="lastEntry" value=$thread->getLastEntry()}
				{$thread->getTimeLastEntry()|unihelp_strftime:"NOTODAY"} von 
		            	{if $lastEntry->isAnonymous()}
		        			Anonymous
		        		{elseif $lastEntry->isForGroup()}
	                        {group_info_link group=$lastEntry->getGroup()}
	                    {else}
		        			{user_info_link user=$lastEntry->getAuthor()}
		        		{/if}
				(Seite: 
					{foreach from=$thread->getCounter() item=bc name=threadEntryCounter}
				    	{if $smarty.foreach.threadEntryCounter.last}
				   			{forum_link thread=$thread name="letzte" page=$bc})
				   		{else}
				    		{forum_link thread=$thread name=$bc" page=$bc}
				    	{/if}
				  	{/foreach}
				</p>
			</li>
		{foreachelse}
			<li>Im Forum ist nix los.</li>		
		{/foreach}
		</ul>
	</div>
	</div>
	
	<div id="course-files" class="shadow">
	<div class="nopadding">
	<h4 class="someHead">Unterlagen Deiner Fächer</h4>
	<ul class="margin">
	<p class="boxlink">
			<a href="{course_url courseFileLatest=true}">mehr Neue Unterlagen</a>
		</p><br style="clear: both" />
	{dynamic}{* CACHEME ?? *}
		{if $courseFiles}
		<dl>
		{foreach from=$courseFiles item=coursefile}
			<dt class="course-file"><a class="{$coursefile->getFileType()}" href="{course_url courseFile=$coursefile}">{$coursefile->getFileName()}</a></dt>
				<dd>
				{$coursefile->getInsertAt()|date_format:"%d %m %Y, %H:%M"} von {user_info_link user=$coursefile->getAuthor()} 
				{assign var=fileCourse value=$coursefile->getCourse()}
				(<a href="{course_url courseId=$fileCourse->id}" 
						title="Direkt zum Fach '{$fileCourse->getName()}'">{$fileCourse->getName()|truncate:30:"..."}</a>)
				</dd>
		{/foreach}
		</dl>
		{else}
		<p>Bis jetzt waren alle so faul und haben keine Unterlagen hochgeladen. Du kannst das ändern.</p>	
		{/if}

	{/dynamic}
	</ul>
	
	</div>
	</div>

	<br class="clear" />

	<div class="shadow" id="courseList"><div>
	<h3>Fächerliste</h3>
	<p style="align: center">
		<a href="{course_url courseList=A}#courseList">A</a>
		<a href="{course_url courseList=B}#courseList">B</a>
		<a href="{course_url courseList=C}#courseList">C</a>
		<a href="{course_url courseList=D}#courseList">D</a>
		<a href="{course_url courseList=E}#courseList">E</a>
		<a href="{course_url courseList=F}#courseList">F</a>
		<a href="{course_url courseList=G}#courseList">G</a>
		<a href="{course_url courseList=H}#courseList">H</a>
		<a href="{course_url courseList=I}#courseList">I</a>
		<a href="{course_url courseList=J}#courseList">J</a>
		<a href="{course_url courseList=K}#courseList">K</a>
		<a href="{course_url courseList=L}#courseList">L</a>
		<a href="{course_url courseList=M}#courseList">M</a>
		<a href="{course_url courseList=N}#courseList">N</a>
		<a href="{course_url courseList=O}#courseList">O</a>
		<a href="{course_url courseList=P}#courseList">P</a>
		<a href="{course_url courseList=Q}#courseList">Q</a>
		<a href="{course_url courseList=R}#courseList">R</a>
		<a href="{course_url courseList=S}#courseList">S</a>
		<a href="{course_url courseList=T}#courseList">T</a>
		<a href="{course_url courseList=U}#courseList">U</a>
		<a href="{course_url courseList=V}#courseList">V</a>
		<a href="{course_url courseList=W}#courseList">W</a>
		<a href="{course_url courseList=X}#courseList">X</a>
		<a href="{course_url courseList=Y}#courseList">Y</a>
		<a href="{course_url courseList=Z}#courseList">Z</a>
	</p>
	{if $startCourse}
		<strong>Fächer gefunden:</strong>
		<ul class="bulleted">
			{foreach from=$startCourse item="course"}
				<li>
					<a href="{course_url course=$course}"  stlye="align: left">{$course->getName()}</a>
				</li>
			{/foreach}
		</ul>	
		{/if}
	
	</div>
	</div>

{/if}
