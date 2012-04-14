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

*}{*	$Id: course_file_info.tpl 5807 2008-04-12 21:23:22Z trehn $
	$HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/course/course_file_info.tpl $	*}
		<ul class="file-info">
			<li>
				<span>Autor: </span>{user_info_link user=$coursefile->getAuthor()}&nbsp;
			</li>
			<li>
				<span>Upload: </span>{$coursefile->getInsertAt()|unihelp_strftime}&nbsp;
			</li>
			<li>
				<span>File-Size: </span>{$coursefile->getFileSize(true)} KB
			</li>
			<li>
			{if $coursefile_downloaded}
				<span id="couseFileIsDownloaded">Kosten: </span>
				bezahlt
			{else}
				<span>Kosten: </span>
				{$coursefile->getCosts()} W-Punkt{if $coursefile->getCosts() > 1}e{/if}				
			{/if}
			</li>
		</ul>
		<ul class="file-info">	
			<li>
                {assign var="course" value=$coursefile->getCourse()}
				<span>Fach: </span>{$course->getName()}
			</li>
			<li>
                {assign var="semester" value=$coursefile->getSemester()}
				<span>Semester: </span>{$semester->getName()}
			</li>
			<li>
                {assign var="category" value=$coursefile->getCategory()}
				<span>Rubrik: </span>{$category->getName()}
			</li>
			<li>
				<span>Downloads: </span>{$coursefile->getDownloadNumber()} Download{if $coursefile->getDownloadNumber() > 1}s{/if}
			</li>
		</ul>
		<br class="clear" />
		
		<p class="file-info3">
				<span>Beschreibung: </span>{$coursefile->getDescription()}
		</p>

		<p class="file-info3" style="margin-top: 10px">
			<span>Bewertungen: </span>
		</p>
        <dl class="file-rate">
        {foreach item=median from=$coursefile->getRatingsMedians() key=rating_cat}
			<dt>{translate rating_cat=$rating_cat}</dt><dd>{$median[0]} {course_rating_desc rating=$median[0]} ({$median[1]})</dd>
		{/foreach}
		</dl>
		<br style="clear: both" />
		
		{if $comments_read}
		<br style="clear: both" />
		<div>
		<p class="file-info3">
			<span>Versionen: </span>
		</p>	
		{dynamic}
		{if $visitor->equals($coursefile->getAuthor()) || $visitor->hasRight('COURSE_FILE_ADMIN')}
   			<a href="{course_url courseFileEdit=$coursefile}#post">Bearbeiten/neue Version hochladen</a>
   			{if $visitor->hasRight('COURSE_FILE_ADMIN')}
   				<a onClick="a = confirm('Datei {$coursefile->getFileName()} wirklich löschen? Achtung: die Löschung ist ENDGÜLTIG.'); if (a) window.location.href='{admin_url deleteFile=$coursefile->id}';return false;" href="#">Unterlage <strong>komplett</strong> l&ouml;schen</a>
			{/if}	
    	{/if}
    	

		
		<br style="clear: both" />
		{if !($visitor->isLoggedIn() && $visitor->hasRight('COURSES_FILE_DOWNLOAD'))}
			<p class="file-logoff">Nur eingeloggte User dürfen Unterlagen herunterladen.</p>
		{else}
			{if !$visitor->hasEnoughPoints('COURSE_FILE_BOUGHT', -$coursefile->getCosts()) && !$coursefile->hasAlreadyDownloaded($visitor) && !$visitor->equals($coursefile->getAuthor())}
					<span class="file-logoff">Du hast zu wenig Punkte</span>
			{/if}			
		{/if}
		
		<ol id="course-files-revisions">
		{foreach from=$coursefile->getRevisions() item="rev"}
			<li class="course-file" title="{$coursefile->id}">
			{if $visitor->isLoggedIn() && $visitor->hasRight('COURSES_FILE_DOWNLOAD')}
				
				{if !$visitor->hasEnoughPoints('COURSE_FILE_BOUGHT', -$coursefile->getCosts()) && !$coursefile->hasAlreadyDownloaded($visitor) && !$visitor->equals($coursefile->getAuthor())}
					<span class="file-logoff">{$rev->getFileName()|escape:html}</span>
				{else}
					<a class="courseFileDownload courseFileDownloadJS {$rev->getType()}" href="{course_url getFile=$rev}" title="jetzt herunterladen">
					{$rev->getFileName()|escape:html} (download)
					</a> 
					({$rev->getFileSize(true)} KB), hochgeladen am {$rev->uploadTime|unihelp_strftime}
					<br />SHA1: {$rev->getHash()} [[help.course.faq315]]
				{/if}
				
			{else}
				<span class="file-logoff">{$rev->getFileName()|escape:html}</span>
			{/if}
				{dynamic}
				{if $visitor->hasRight('COURSE_FILE_ADMIN')}
					<br /><a href="{admin_url deleteFileVersion=$rev->id}">Version löschen</a>
				{/if}
				{/dynamic}
				</li>
		{/foreach}
		</ol>
		{/dynamic}
		<br style="clear: both" />
		</div>
		<br style="clear: both" />
		{/if}
