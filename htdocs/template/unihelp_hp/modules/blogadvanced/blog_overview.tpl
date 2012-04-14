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

*}{* $Id: blog_overview.tpl 5807 2008-04-12 21:23:22Z trehn $ 
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/blogadvanced/blog_overview.tpl $ *}
{* zeigt Blogübersicht eines Users *}
<div id="content">

{foreach from=$blog_entries item="entries" key="date"}
<div class="blogbox shadow"><div>
<h3>{$date|unihelp_strftime:"%A, %e. %B %Y"}</h3>
{foreach from=$entries item="blog_entry"}
	{* show blog entry *}
	{include file="modules/blogadvanced/blog_entry_core.tpl"}
{/foreach} {* end entries *}
</div></div>
{foreachelse}
Keine Einträge vorhanden.
{/foreach} {* end blog_entries *}

<p class="footer">
{* harmonize variable that contains archive date, if set *}
{if $blog_selected_archive}
{assign var="archive_date" value=$blog_selected_archive}
{elseif $blog_selected_archive_day}
{assign var="archive_date" value=$blog_selected_archive_day}
{/if}

{if $blog_pages_number > 1}
	{if $blog_page > 1}
	{blog_link owner=$blog_model->getOwner() category=$blog_selected_category archive_date=$blog_selected_archive archive_date_day=$blog_selected_archive_day page=$blog_page-1 content="vorherige Einträge"}
	{/if}
	(Seite {$blog_page} von {$blog_pages_number}, insgesamt {$blog_entries_number} Einträge)
	{if $blog_page < $blog_pages_number}
	{blog_link owner=$blog_model->getOwner() category=$blog_selected_category archive_date=$blog_selected_archive archive_date_day=$blog_selected_archive_day page=$blog_page+1 content="nächste Einträge"}
	{/if}
{/if}  {* if $blog_pages_number > 1 *}
</p>

</div> {* end div content *}
