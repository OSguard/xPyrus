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

<div class="shadow"><div class="nopadding">
<h3>neuste Unterlagen</h3>

<table class="centralTable nopadding clear">
	<thead>
	    <tr>
	    <th>Punkte</th>
	    <th>Name</th>
	    <th>Info</th>
	    </tr>
	</thead>
	<tbody>
		{foreach item=coursefile from=$courseFiles}
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
				<td class="file-name" style="width: 100%; height: 62px">
					<span style="float: right">
						{if $coursefile->getRatingQuickvoteInt()}
							<img src="/images/bewertungen/course_{$coursefile->getRatingQuickvoteInt()}.png" title="{course_rating_desc rating=$coursefile->getRatingQuickvoteInt()}" alt="positive Bewertung" />
						{/if}</span>
					<p class="{$coursefile->getFileType()} file"><a class="file" href="{course_url courseFile=$coursefile}">
						{$coursefile->getFileName()}
					</a> ({$coursefile->getFileSize(true)} KB)
					<br />
					{user_info_link user=$coursefile->getAuthor()}, {$coursefile->getInsertAt()|unihelp_strftime}<br />
					<span style="font-weight: bold;">Beschreibung: </span>{$coursefile->getDescription()}</p>
				</td>
				<td>
                    {assign var="category" value=$coursefile->getCategory()}
                    {assign var="semester" value=$coursefile->getSemester()}
					{assign var="course" value=$coursefile->getCourse()}
					{$semester->getName()}<br />
					{$category->getName()}<br />
					<a href="{course_url courseId=$course->id}" 
						title="Direkt zum Fach '{$course->getName()}'">{$course->getName()|truncate:30:"..."}</a>
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>
</div></div>