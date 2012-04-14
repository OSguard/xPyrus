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

*}{* $Id: part_thread_entry.tpl 5810 2008-04-13 15:44:59Z schnueptus $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/forum/internal/part_thread_entry.tpl $ *}
        	{if !$entry->isAnonymous() && !$entry->isForGroup()}
			    {assign var="authorDisplay" value=$author->getUsername()}	
			{elseif $entry->isForGroup()}
			    {assign var=group value=$entry->getGroup()}
			    {assign var="authorDisplay" value=$group->getName()}
			{else}
			    {assign var="authorDisplay" value="anonym"}
			{/if}
        
        <tr class="thread-entry-title" style="border-bottom: 0px !important;">
        	<td style="background: #eee; border-top: 3px solid #7F7F7F !important; border-bottom: 0px !important;">
        		<a name="entry{$entryId}" href="{forum_url entryId=$entryId}">
        			Eintrag #{dynamic}{counter}{/dynamic}
        		</a>
        	</td>
        	        	
        	<td colspan="2" style="background: #eee; border-top: 3px solid #7F7F7F !important; border-bottom: 0px !important;">
		          {* if it is not preview set anker to last post *}
		          {* FIXME *}
		          {*
		          {if $smarty.foreach.entryLoop.last && $entryToEdit == null}
		          	<a name="post"></a>
		          {/if}  
		          *}
        		<span class="thread-entry-date">geschrieben {$entry->getTimeEntry()|unihelp_strftime}</span>
	        	<span class="thread-entry-edit" title="{$authorDisplay}">
	        		{dynamic}
			          {* entrys can not edit if thread is closed *}
			          {if !$thread->isClosed()}
			          	{* only yourself can edit entry *}
			          	{if ($visitor->equals($author) && $visitor->hasRight('FORUM_THREAD_ENTRY_EDIT')) || $forum->isModerator($visitor)}
			          		<a href="{forum_url editEntryId=$entryId anker=postanker }" title="Beitrag editieren">
			          			<img src="{$TEMPLATE_DIR}/images/edit.png" alt="editieren" />
			          		</a>
			          	{/if}
			          	{if $visitor->hasRight('FORUM_THREAD_ENTRY_ADD') }
			          	<a href="{forum_url quoteEntryId=$entryId anker=postanker }" title="Beitrag zitieren" class="forumQuote">
			          			<img src="{$TEMPLATE_DIR}/images/quote.png" alt="zitieren" />
			          	</a>						
			          	<a href="{forum_url thread=$thread page=$thread->getPage()}#postanker" title="antworten">
			          			<img src="{$TEMPLATE_DIR}/images/reply.png" alt="antworten" />
			          	</a>
			          	{/if}			          	
			          	<a href="{forum_url entryId=$entryId}" title="direkter Link zu diesem Beitrag">
			          			<img src="{$TEMPLATE_DIR}/images/link.png" alt="Link" />
			          	</a>
			          	<a href="{forum_url reportEntryId=$entryId}" title="diesen Beitrag melden">
			          			<img src="{$TEMPLATE_DIR}/images/report.png" alt="Diesen Beitrag melden" />
			          	</a>
			          	{if $visitor->hasRight('FORUM_CATEGORY_ADMIN')}          		
			          		<a href="{forum_url delEntryId=$entryId }" title="Beitrag löschen">
			          			<img src="{$TEMPLATE_DIR}/images/delete.png" alt="löschen" />
			          		</a>
			          	{/if}
			          	{if $forum->isModerator($visitor)}				          	
				          	<a href="{forum_url historyEntryId=$entryId}" title="Historie anschauen">
				          		<img src="{$TEMPLATE_DIR}/images/history.png" alt="Historie" />
				          	</a>
			          	{/if}
			          {/if}	
	        		{/dynamic}
			   </span>
		   </td>
        </tr>
        <tr class="thread-entry-body" style="border-top-width: 0px !important;">
          <td class="author">         
	          {if $forum->getForumTemplate() == 'default'}
	          	{include file="modules/forum/user_entry_info/default.tpl"}
	          {else}
	          	{assign var="tpl" value=$forum->getForumTemplate()}
	          	{include file="modules/forum/user_entry_info/$tpl"}
	          {/if}  
          </td>
          <td colspan="2" class="entry">            
	          <h5>{* do not escape caption because it should already be escaped *}
          		{$entry->getCaption()}
          	</h5>
          	
            {* via parameter true we prepare the entry to save the parsed content into DB if this has not happened yet *} 
            {$entry->getContentParsed(true)}
            {if !$entry->isAnonymous() && !$entry->isForGroup()}
                {if $author->getSignature() != ''}
                    <hr class="signature" />
                    <div class="signature">{$author->getSignature()}</div>
                {/if}
            {/if}
          </td>
        </tr>
