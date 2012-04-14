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

*}﻿
{* {include file="banner.tpl"} *}

{* <h2 id="pagename">Die letzten Foren Beiträge</h2> *}

<br />
{if $show == "all"}
 <p style="float: right">
  <a href="{forum_url rss=true}" title="RSS-Feed der letzten Beiträge" >
	<img src="/images/icons/feed.gif" alt="Feed" /> RSS-Feed
  </a>
  </p>
{/if}


<br class="clear" />
  <div id="tabNavigation">
  <ul>
      <li>
        <a {if $show == "all"}class="active"{/if} href="/forum/latest?show=all">Alle Threads</a>
      </li>
	  <li>
	 	<a {if $show == "community"}class="active"{/if} href="/forum/latest?show=community">Gemeinschafts-Threads </a>
	  </li>	  
  	  <li>
		<a {if $show == "studies"}class="active"{/if} href="/forum/latest?show=studies">Studium-Threads</a>
	  </li>    
	 {if $visitor->isRegularLocalUser()}
	  	  <li>
		<a {if $show == "abo"}class="active"{/if} href="/forum/latest?show=abo">Abo-Threads</a>
	  </li>    
	 {/if} 
  </ul>
  </div>
<br class="clear" />

<div class="border"><div class="nopadding">
 	<a id="latest"></a>
 	
    <table class="centralTable" summary="Die Tabelle enth&auml;lt die Threads mit den letzten Eintr&auml;gen">
	  <thead>
	    <tr>
		  <th>Thema</th>
		  <th>Views</th>
		  <th>Beitr&auml;ge</th>
		  <th>letzter Beitrag</th>
		  
	    </tr>
      </thead>
      <tbody>
      	{assign var="oldForumId" value="x"}
        {foreach from=$threads item=thread}
        <tr>
            <td>
            	{if $thread->getForumId() != $oldForumId}
			      	{assign var="oldForumId" value=$thread->getForumId()}            		
        			{assign var=forum value=$thread->getForum() }
    				<a href="{forum_url forum=$forum}" title="Zum Forum von {$forum->getName()}" style="color: #000;"><strong>{$forum->getName()}</strong></a>
    				<br />
    			{/if}
			
			
				{if !$thread->isVisible()}
            		<img src="/images/icons/eye_none.png" alt="unsichtbar" title="Thread ist nicht sichtbar" />
            	{/if}
    			{forum_new thread=$thread}
              	{if $thread->isSticky()}
        			<img src="/images/icons/lightning.png" alt="wichtig" title="Thread ist wichtig" />
        		{/if}
            	{forum_link thread=$thread title="Zu dem Thread"}
        		{if $thread->isClosed()}
        		<img src="/images/icons/lock.png" alt="geschlossen" title="Thread ist geschlossen" />
        		{/if}
        		
        		{* start thread pages *}
				<p class="pages">(Seite: 
					{foreach from=$thread->getCounter() item=bc name=threadEntryCounter}
				    	{if $smarty.foreach.threadEntryCounter.last}
				   			{forum_link thread=$thread name="letzte" page=$bc}
				   		{else}
				    		{forum_link thread=$thread name=$bc" page=$bc}
				    	{/if}
				  	{/foreach})
				</p>
            	{* end thread pages *}	
            	
            </td>
            <td class="center">{$thread->getNumberOfViews()}</td>
            <td class="center">{$thread->getNumberOfEntries()}</td>
            <td>
                {assign var="lastEntry" value=$thread->getLastEntry()}
                <a href="{forum_url entryId=$lastEntry->id}">{$lastEntry->getCaption()|default:"zum Eintrag"|truncate:30:"...":true}</a><br />
                {$thread->getTimeLastEntry()|unihelp_strftime} von 
                {if $lastEntry->isAnonymous()}
	                anonym
	            {else}
	            	{if $lastEntry->isForGroup()}
	            		{group_info_link group=$lastEntry->getGroup()}
	            	{else}
	                	{user_info_link user=$lastEntry->getAuthor()}
	                {/if}	
	            {/if}
            </td>
            
        </tr>
        {foreachelse}
        	<tr>
        		<td colspan="4" class="emptyTable">
        			Keine Threads gefunden
        		</td>
        	</tr>
        {/foreach}
     </tbody>
   </table>
   </div></div>
