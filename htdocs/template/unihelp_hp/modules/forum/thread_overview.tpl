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

*}{* $Id: thread_overview.tpl 6210 2008-07-25 17:29:44Z trehn $ *}


  <a id="topofpage" name="topofpage"></a>
  <p style="float: right">
  <a href="{forum_url latest=true}?show=community" title="Zu den letzten Beiträgen" >
	<img src="/images/icons/user_comment.png" alt="Zu den letzten Beiträgen" />Zu den letzten Beiträgen
  </a>
  </p>	
 

 {if $subforums || $forum->isModerator($visitor)}	
 <div class="shadow"><div class="nopadding">
 <h3>Alle Unterforen von "{$forum->getName()}" </h3>
 <table class="centralTable" id="forum_threads" summary="Die Tabelle enth&auml;lt alle Foren der Kategorie '{$cat->getName()}'">


	  <thead>
	    <tr>
		  <th>Forum</th>
		  <th>Threads</th>
		  <th>Beitr&auml;ge</th>
		  <th>letzter Beitrag</th>
		  <th>Moderatoren</th>
	    </tr>
      </thead>
	  {if $forum->isModerator($visitor) && $addsub || $forumToEdit != null}
	    <tfoot>
	    	{assign var="subforum" value=true}
	        {include file="modules/forum/internal/add_forum.tpl"}
		 </tfoot>
      {/if}
      <tbody>
      <tr>{strip}
           <td colspan="7">
           <span style="float:left">
           (Mods: {foreach name=mods from=$cat->getModerators() item=moderator}{user_info_link user=$moderator}{if !$smarty.foreach.mods.last},{/if}{/foreach})
           </span>
           {if $forum->isModerator($visitor)}
           		<span style="float:right">
           		{if $addsub}
           			<a href="{forum_url forum=$forum}">kein Unterforen hinzufügen</a>
           		{else}
           			<a href="/forum/addsub/{$forum->id}" title="Unterforum hinzufügen"><img src="/images/icons/table_add.png" alt="neues Unterforum" /></a>
           		{/if}
           		</span>	
           {/if}           
           </td>
          {/strip}</tr> 
        {foreach from=$subforums item=f name="fora"}
        	{assign var="show_order" value='default'}
        	{include file="modules/forum/internal/forum_thread_line.tpl"}
	    {foreachelse}
        <tr>
        	<td colspan="5" class="emptyTable">
        		Es wurden keine Unterforen angelegt.
        	</td>
        </tr>	
        {/foreach}
     </tbody>
   </table>
   </div></div>
 {/if} {* end subforums *}

 
 {if $forum->hasEnabledPostings() || $threads != null}
  <div class="shadow"><div class="nopadding">
  <h3>Thread Übersicht</h3>
    
    <div class="counter">{strip}
  Seitennummer:
  {foreach from=$forum->getCounter() item=bc name=threadCounter}
    
    {if $bc==$forum->getPage()}
      <strong>{$bc}</strong>
    {else}
      {forum_link forum=$forum page=$bc name=$bc }
    {/if}      
    
    {if !$smarty.foreach.threadCounter.last}
      &nbsp;
    {/if}
  {/foreach}
  {/strip}
  </div>  
   <table class="centralTable" summary="Die Tabelle enth&auml;lt die Threads zum Forum '{$forum->getName()}'"style="width: 100%" >
	  <thead>
	    <tr>
		  <th>Thema</th>
		  <th>Views</th>
		  <th>Beitr&auml;ge</th>
		  <th>letzter Beitrag</th>
  		  {if $forum->isModerator($visitor)}	  
		  	<th>admin</th>
		  {/if}
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
            
            	{if !$thread->isVisible()}
            		<img src="/images/icons/eye_none.png" alt="unsichtbar" title="Thread ist nicht sichtbar" />
            	{/if}
            	{forum_new thread=$thread}
	            {if $oldThread != null }
					<img src="/images/icons/arrow_right.png" alt="verschoben" title="Thread ist verschoben" />
				{/if}
				{if $thread->isSticky()}
					<img src="/images/icons/lightning.png" alt="wichtig" title="Thread ist wichtig" />
				{/if}		
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
            <td class="center">{$thread->getNumberOfViews()}</td>
            <td class="center">{$thread->getNumberOfEntries()}</td>
            <td>
                {assign var="lastEntry" value=$thread->getLastEntry()}
            	<a href="{forum_url entryId=$lastEntry->id}">{$lastEntry->getCaption()|default:"zum Beitrag"|truncate:30:"...":true}</a><br />
            	{$thread->getTimeLastEntry()|unihelp_strftime:"NOTODAY"} von 
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

			{if $forum->isModerator($visitor) && $oldThread == null}
	        <td>
	            <a href="{forum_url editThreadId=$thread->id}" title="Thread bearbeiten" >
					<img src="/images/icons/table_edit.png" alt="Thread bearbeiten" />
				</a>
	            {if $thread->isClosed()}
		          <a href="{forum_url threadCloseStateId=$thread->id isClosed="false"}" title="Thread wiedereröffnen" >
					<img src="/images/icons/lock_open.png" alt="Thread wiedereröffnen" />
				  </a>	
		        {else}
		          <a href="{forum_url threadCloseStateId=$thread->id isClosed="true"}" title="Thread schliessen" >
					<img src="/images/icons/lock_add.png" alt="Thread schliessen" />
				  </a>	
		        {/if}
		        <br />
	            {if $thread->isSticky()}
		          <a href="{forum_url threadStickyStateId=$thread->id isSticky="false"}" title="Thread nicht wichtig" >
					<img src="/images/icons/lightning_delete.png" alt="Thread nicht wichtig" />
				  </a>		            		
		        {else}
				  <a href="{forum_url threadStickyStateId=$thread->id isSticky="true"}" title="Thread wichtig" >
					<img src="/images/icons/lightning_add.png" alt="Thread wichtig" />
				  </a>	
		        {/if}
	            {if $thread->isVisible()}
		          <a href="{forum_url threadVisibleStateId=$thread->id isVisible="false"}" title="Thread unsichtbar schalten" >
					<img src="/images/icons/eye_delete.png" alt="Thread sichtbar" />
				  </a>	
		        {else}
		          <a href="{forum_url threadVisibleStateId=$thread->id isVisible="true"}" title="Thread sichtbar schalten" >
					<img src="/images/icons/eye_add.png" alt="Thread unsichtbar" />
				  </a>	
			 	{/if}
			 	{if $visitor->hasRight('FORUM_CATEGORY_ADMIN')}
			 	  <a href="{forum_url delThreadId=$thread->id}" title="Thread löschen" >
					<img src="/images/icons/table_delete.png" alt="Thread löschen" />
				  </a>
				{/if}		
	        </td>
            {/if}
	    {if $forum->isModerator($visitor) && $oldThread != null}
		<td>
			{if $oldThread->isVisible()}
		        <a href="{forum_url threadVisibleStateId=$oldThread->id isVisible="false"}" title="Thread sichtbar" >
					<img src="/images/icons/error_delete.png" alt="Thread sichtbar" />
				</a>	
		     {else}
		        <a href="{forum_url threadVisibleStateId=$oldThread->id isVisible="true"}" title="Thread unsichtbar" >
					<img src="/images/icons/error_add.png" alt="Thread unsichtbar" />
				</a>	
			 {/if}	
			 {if $oldThread->isSticky()}
		        <a href="{forum_url threadStickyStateId=$oldThread->id isSticky="false"}" title="Thread nicht wichtig" >
					<img src="/images/icons/lightning_delete.png" alt="Thread nicht wichtig" />
				</a>		            		
		     {/if}
		</td>
	    {/if}
        </tr>
        {foreachelse}
        <tr>
        	<td colspan="6" class="emptyTable">
        		Es gibt noch keine Threads in diesen Forum.
        	</td>
        </tr>
        {/foreach}
     </tbody>
   </table>
   </div>
       <div class="counter counterbottom">{strip}
		  Seitennummer:
		  {foreach from=$forum->getCounter() item=bc name=threadCounter}
		    
		    {if $bc==$forum->getPage()}
		      <strong>{$bc}</strong>
		    {else}
		      {forum_link forum=$forum page=$bc name=$bc }
		    {/if}      
		    
		    {if !$smarty.foreach.threadCounter.last}
		      &nbsp;
		    {/if}
		  {/foreach}
		  {/strip}
	  </div>  
		   
   </div>
   {/if}
    <div id="previewdiv">
  		{include file="modules/forum/internal/preview.tpl"}
    </div>
  {include file="modules/forum/internal/add_thread.tpl"}
  

