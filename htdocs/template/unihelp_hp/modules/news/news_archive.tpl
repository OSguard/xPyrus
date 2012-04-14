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

*}<br class="clear" />
<div id="tabNavigation">
	<ul>
	    <li>
	        <a href="/home#news">News</a>
	    </li>
	    {if $visitor->hasGroupRight('NEWS_ENTRY_ADD')}
		<li>
		    <a href="/home/news/add"> News hinzufügen </a>
		</li>	
	    {/if}      
	    <li>
			<a class="active">News Archiv</a>
	    </li>
	</ul>
	</div>
<div class="shadow" id="news"><div>
{dynamic}
{assign var="newsArchive" value="1"}
{foreach from=$news item=new name=news}
    {$new->getContent(true)}
{foreachelse}
    Es gibt keine News, so was aber auch ...       
{/foreach}
{/dynamic}
</div>
	<div class="counter counterbottom">{strip}
	  Seitenauswahl: 
	  {foreach from=$counter item=bc name=counter}
	    {if $bc==$page}
	      <strong>
	    {else}
	      <a href="/home/oldnews/{$bc}">
	    {/if}
	      {$bc}
	    {if $bc==$page}
	      </strong>
	    {else}
	      </a>
	    {/if}
	    {if !$smarty.foreach.counter.last}
	      {* if not last loop, output whitespace to separate entries *}
	      &nbsp;
	    {/if}
	  {/foreach}
	  {/strip}
	  </div>
</div>
<br class="clear" />

