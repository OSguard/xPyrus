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

*}{* $Id: blog_entry_core.tpl 5807 2008-04-12 21:23:22Z trehn $ *}

{* do NO html-escape here, because all escapement has been done in PHP *}

<h4>{blog_link entry=$blog_entry owner=$blog_entry->getOwner() content=$blog_entry->getTitle()}</h4>
<div class="entry">{$blog_entry->getContentParsed(true)}</div>
<p class="additionalinfo">
	{if $blog_entry->isForGroup()}für {group_info_link group=$blog_entry->getGroup()}{/if} geschrieben von {user_info_link user=$blog_entry->getAuthor()} um <em>{$blog_entry->timeEntry|unihelp_strftime:'%H:%M'}</em>, 
	{$blog_entry->getCommentsNumber()} {blog_link entry=$blog_entry owner=$blog_entry->getOwner() content="Kommentare" anchor="comments"}, 
	{$blog_entry->getTrackbacksNumber()} {blog_link entry=$blog_entry owner=$blog_entry->getOwner() content="Trackbacks" anchor="trackbacks"},
	{if $blog_entry->getCategories()}
		 in 
			{foreach from=$blog_entry->getCategories() item=entry_cat}
				{blog_link category=$entry_cat owner=$blog_entry->getOwner() content=$entry_cat->name},
			{/foreach} {* end category *}
	{/if}
	{if $visitor->equals($blog_entry->getAuthor())}
	<a href="{blog_url entry=$blog_entry owner=$blog_entry->getOwner() edit=true}">bearbeiten</a>
	{/if}
</p>
