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

*}<div id="sidebar">

<div class="box" id="calendar">
<h4>Kalender</h4>

<div id="calendarhead" class="boxcontent">
{if $blog_calendar_date.previous}<a href="{blog_url owner=$blog_model->getOwner() date=$blog_calendar_date.previous}" id="cback" class="hidden"><span>vorher</span></a>{/if}
{if $blog_calendar_date.next}<a href="{blog_url owner=$blog_model->getOwner() date=$blog_calendar_date.next}" id="cforward" class="hidden"><span>nachher</span></a>{/if}
<a href="{blog_url owner=$blog_model->getOwner() archive_date=$blog_calendar_date.current}" id="cmonth">{$blog_calendar_date.current|unihelp_strftime:'%B %Y'}</a>
</div>

<table class="calendar boxcontent">
<tr><td>Mo</td><td>Di</td><td>Mi</td><td>Do</td><td>Fr</td><td>Sa</td><td>So</td></tr>

{foreach from=$blog_calendar item=week}
<tr>
{foreach from=$week item=day}
<td {if $day.today}class="today"{/if}>{if $day.day && $day.hasEntries}
{blog_link owner=$blog_model->getOwner() date=$day.dateForLink content=$day.day}
{elseif $day.day}{$day.day}
{/if}</td>
{/foreach}
</tr>
{/foreach}
</table>
</div>

<div class="box">
<h4>Kategorien</h4>
<ul class="boxcontent">
{foreach from=$blog_categories item=cat}
<li>{blog_link category=$cat owner=$blog_model->getOwner() content=$cat->name}</li>
{/foreach}
<li>{blog_link owner=$blog_model->getOwner() content="Alle Kategorien"}</li>
</ul>
</div>

<div class="box">
<h4>Archiv</h4>
<ul class="boxcontent">
{foreach from=$blog_archives_months item=month}
<li>
{blog_link owner=$blog_model->getOwner() date=$month.dateForLink content=$month.name}
</li>
{/foreach}
<li>{blog_link owner=$blog_model->getOwner() content="Aktuell"}</li>
</ul>
</div>

<div class="box">
<h4>Sonstiges</h4>
<ul class="boxcontent">
<li><img src="/images/xml.gif" alt="xml" /><a href="{blog_url feed="rss2" owner=$blog_model->getOwner()}">RSS 2.0</a></li>
<li><img src="/images/xml.gif" alt="xml" /><a href="{blog_url commentFeed="rss2" owner=$blog_model->getOwner()}">RSS 2.0 (Kommentare)</a></li>
{dynamic}
{if $visitor->hasRight('BLOG_ADVANCED_OWN_ADMIN') && $blog_model->isAdministrativeAuthority($visitor)}
<li><a href="{blog_url admin="start" owner=$blog_model->getOwner()}">Administrieren</a></li>
{/if}
{/dynamic}
<li><a href="{blog_url}">[[local.local.project_name]] Blogosphäre</a></li>
</ul>
</div>

</div>
