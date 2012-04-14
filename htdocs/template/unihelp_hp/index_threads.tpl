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

*}<div id="course-forum" class="course-left, shadow">
<div class="nopadding">
<h4 class="someHead">Neue allgemeine Eintr&auml;ge</h4>
<ul class="margin">
	{foreach from=$threads_overview item=thread}
		<li class="course-thread">
		  <span class="caption">{forum_link thread=$thread title="Zum Thread"}
			{forum_new thread=$thread}</span>
          <span class="page">(Seite: 
                {foreach from=$thread->getCounter() item=bc name=threadEntryCounter}
                    {if $smarty.foreach.threadEntryCounter.last}
						{forum_link thread=$thread name="letzte" page=$bc})
                    {else}
                        {forum_link thread=$thread name=$bc" page=$bc}
                    {/if}
                {/foreach}</span>
			<p class="clear">
	        {assign var="lastEntry" value=$thread->getLastEntry()}
	        {assign var="forum" value=$thread->getForum()}
			{$thread->getTimeLastEntry()|unihelp_strftime:"NOTODAY"} von 
	            	{if $lastEntry->isAnonymous()}
	        			Anonymous
	        		{elseif $lastEntry->isForGroup()}
                        {group_info_link group=$lastEntry->getGroup()}
                    {else}
	        			{user_info_link user=$lastEntry->getAuthor()}
	        		{/if}
	        		in <a href="{forum_url forum=$forum}"><em>{$forum->getName()}</em></a>
			</p>
		</li>
	{foreachelse}
		<li>Im Forum ist nix los.</li>		
	{/foreach}
	</ul>
	<p class="margin">
		<a href="{forum_url latest=true}?show=community">neueste Beiträge</a>
	</p>
</div>
</div>
