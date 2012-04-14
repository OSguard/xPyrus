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

*}{* {include file="banner.tpl"} *}

  
  {*<h2>{$tag->getName()}</h2>*}
  <p style="float: right">
  <a href="{forum_url latest=true}" title="Zu den letzten Beiträgen" >
	<img src="/images/icons/user_comment.png" alt="Zu den letzten Beiträgen" />Zu den letzten Beiträgen
  </a>
  </p>
  {*<h4 style="margin-top: 10px;">{forum_link name="Forum" title="Zur Foren Übersicht"} 
  </h4>*}
  
<div class="shadow"><div class="nopadding">
  <h3>Alle Threads des virtuellen Forums {$tag->getName()|escape:html}</h3>
  
  {* TODO: rename counter id *}
  <div id="blog_counter" class="counter">{strip}
  Seitennummer: 
  {foreach from=$page_counter item=bc name=threadCounter}
    
    {if $bc==$page}
      <strong>{$bc}</strong>
    {else}
      {forum_link forum=$forum page=$bc name=$bc }
    {/if}      
    
    {if !$smarty.foreach.threadCounter.last}
      {* if not last loop, output whitespace to separate entries *}
      &nbsp;
    {/if}
  {/foreach}
  {/strip}
  </div>
  
  
    <table class="centralTable" summary="Die Tabelle enth&auml;lt die Threads zum virtuellen Forum '{$tag->getName()|escape:html}'">
	  <thead>
	    <tr>
		  <th>Status</th>
		  <th>Thema</th>
		  <th>Forum</th>
		  <th>Views</th>
		  <th>Beitr&auml;ge</th>
		  <th>letzter Beitrag</th>
	    </tr>
      </thead>
      <tbody>
        {foreach from=$threads item=thread}
        <tr>
            <td>
            	{if $thread->getLinkToThread() != null}
            		{assign var="oldThread" value=$thread}
            		{assign var="thread" value=$thread->getLinkToThread()}
				{else}
            		{assign var="oldThread" value=null}
            	{/if}
            	{forum_new thread=$thread}
              	{if !$thread->isVisible()}
            		<img src="/images/icons/eye_none.png" alt="unsichtbar" title="Thread ist nicht sichtbar" />
            	{/if}
				{if $oldThread != null }
        			<img src="/images/icons/arrow_right.png" alt="verschoben" title="Thread ist verschoben" />
				{/if}

        		{if $thread->isSticky()}
        			<img src="/images/icons/lightning.png" alt="wichtig" title="Thread ist wichtig" />
        		{/if}
             </td><td>		
            	{forum_link thread=$thread title="Zum Thread"}
            		{if $thread->isClosed()}
            		<img src="/images/icons/lock.png" alt="geschlossen" title="Thread ist geschlossen" />
            		{/if}
            	{* start thread pages *}
					<p>(Seite: 
					  {foreach from=$thread->getCounter() item=bc name=threadEntryCounter}
				     	{if $smarty.foreach.threadEntryCounter.last}
				   			{forum_link thread=$thread name="letzte" page=$bc}
				   		{else}
				    		{forum_link thread=$thread name=$bc page=$bc }
				    	{/if}					    	
					  {/foreach})
					</p>
            	{* end thread pages *}
            	
            		
            </td>
            <td>            	
            	{forum_link forum=$thread->getForum()}
            </td>
            <td>{$thread->getNumberOfViews()}</td>
            <td>{$thread->getNumberOfEntries()}</td>
            <td>
            	{assign var="lastEntry" value=$thread->getLastEntry()}
                <a href="{forum_url entryId=$lastEntry->id}">{$lastEntry->getCaption()|default:"(kein Titel)"}</a><br />
                {$thread->getTimeLastEntry()|unihelp_strftime:"NOTODAY"}von 
                {if $lastEntry->isAnonymous()}
                    anonym
                {else}  
                    {user_info_link user=$lastEntry->getAuthor()}
                {/if}
            </td>
        </tr>
        {/foreach}
     </tbody>
   </table>
   </div></div>
   
   <div class="shadow"><div id="entry">

   <p>In diesen Forum dürfen keine Threads erstellt werden. Bitte wähle ein geeignetes <a href="{forum_url anker="course"}">Fächer-Forum</a> dazu aus.</p>
</div> </div>
