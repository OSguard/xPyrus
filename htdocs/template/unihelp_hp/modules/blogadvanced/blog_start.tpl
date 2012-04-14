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

*}{*  $Id: blog_start.tpl 5807 2008-04-12 21:23:22Z trehn $
	$HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/blogadvanced/blog_start.tpl $ *}
{* zeigt Übersicht über ganze Blogosphäre *}
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
Keine Einträge vorhanden
{/foreach} {* end blog_entries *}

</div>
