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

*}	<div class="daymarker" id="news{$newsentryId}"><span>{$newsentry->getStartDate()|date_format:"%A, %d. %B %Y"}</span></div>
	<br class="clear" />
	<div class="news">

	<h4>
	{if $newsentry->getThreadId()}
		<a href="{forum_url threadId=$newsentry->getThreadId()}">
			{$newsentry->getCaption()}
		</a>
	{else}
		{$newsentry->getCaption()}
	{/if}	
	</h4>
	{dynamic}
	{if $visitor->hasGroupRight('NEWS_ENTRY_EDIT',$newsentryGroupId) || $visitor->hasRight('NEWS_ENTRY_ADMIN')}
		<p style="float:right; margin-right: 20px">
			<a href="/home/news/{$newsentryId}/edit" title="bearbeiten">
				<img src="/images/icons/newspaper_edit.png" alt="bearbeiten" />
			</a>
			{if !$newsArchive}
			<a href="/home/news/{$newsentryId}/move" title="ins Archiv verschieben">
				<img src="/images/icons/newspaper_go.png" alt="Archiv verschieben" />
			</a>
			{/if}
			{if $visitor->hasRight('NEWS_ENTRY_ADMIN')}
				<a href="/home/news/{$newsentryId}/del" title="löschen">
					<img src="/images/icons/newspaper_delete.png" alt="löschen" />
				</a>
			{/if}
		</p>
	{/if}
	{/dynamic}
	<p style="margin-top:2px;" class="newssub">
		geschrieben von der {group_info_link group=$newsentry->getGroup() show_group_title=true} {$newsentry->getTimeEntry()|unihelp_strftime}</p>
	<div class="entry"  style="margin:5px 0 10px; text-align: justify;">
		{$newsentry->getOpener(true)}
	</div>
	<br class="clear" />
	{if $newsentry->getThreadId()}
		<p><a href="{forum_url threadId=$newsentry->getThreadId()}" style="font-weight: bold;">Vollständiger Artikel und Diskussion</a> 
			{assign var=thread value=$newsentry->getThread()}
			({$thread->getNumberOfEntries()-1} Kommentar{if $thread->getNumberOfEntries() != 2}e{/if})</p>
	{/if}
	<p style="width: 90%; text-align: right;">
		</p>
	
	</div>