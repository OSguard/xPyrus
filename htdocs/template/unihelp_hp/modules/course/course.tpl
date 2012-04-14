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
<div class="shadow" class="box" id="course-forum"><div>
<h3>Diskussionsthemen</h3>
<p class="boxlink">
	<a href="{forum_url forum=$course->getForum()}">mehr Themen</a>
</p>
<ul>
{foreach from=$threads item=thread}
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
	<li>Es gibt noch keinen Thread in diesem Fachforum.</li>		
{/foreach}
</ul>

</div></div>

<div class="shadow" id="course-files"><div>
<h3>Material</h3>
<p class="boxlink">
	<a href="{course_url course=$course showFiles=true}">mehr Unterlagen</a>
</p>
{if $coursefiles_files}
<dl>
{foreach from=$coursefiles_files item=coursefile}
	<dt class="course-file"><a class="courseFileDownload {$coursefile->getFileType()}" href="{course_url courseFile=$coursefile}">{$coursefile->getFileName()}</a></dt>
		<dd>
		{$coursefile->getInsertAt()|date_format:"%d %m %Y, %H:%M"} von {user_info_link user=$coursefile->getAuthor()} (Größe: {$coursefile->getFileSize(true)} KB)
		</dd>
{/foreach}
</dl>
{else}
<p>Bis jetzt waren alle so faul und haben keine Unterlagen hochgeladen. Du kannst das ändern.</p>	
{/if}


</div></div>

<div class="shadow" class="box" style="float: left; width: 100%;"><div>
	<h3>Teilnehmer</h3>
    <p class="boxlink">
    	{pm_link newcourse=$course->id name="eine Rundmail schreiben" title="Nachricht schreiben an alle die das Fach belegen" }
    </p>
    <p>
    <strong>Insgesamt belegen {$course->getSubscriptorsNumber()} Unihelp-User dieses Fach.</strong>
    </p>
    <p>
    {foreach from=$course->getSubscriptors() item=member name=memb}
    	{user_info_link user=$member}{if !$smarty.foreach.memb.last},{/if}
    {foreachelse}
    	Keiner stellt sich der Herausforderung dieses Fach zu besuchen.
    {/foreach}
    </p>
    
</div></div>
