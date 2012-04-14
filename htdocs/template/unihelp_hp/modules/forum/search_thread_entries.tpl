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

*}{* $Id: search_thread_entries.tpl 5807 2008-04-12 21:23:22Z trehn $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/forum/search_thread_entries.tpl $ *}

<div class="shadow"><div style="text-align: center; padding: 20px;">
<h3>Suchen</h3>
<form action="{forum_url search=1}" method="post">
<input type="text" name="{$smarty.const.F_SEARCH_QUERY}" value="{$query}" style="width: 70%; float: none;" />
<input type="submit" name="{$smarty.const.F_SEARCH_SUBMIT}" value="Suchen" style="float: none;" />
{* wir koennten auch ueber eine dropdown-box die max. anzahl der resultate regulieren *}
{* wir koennten auch ueber zusaetzlich nach autor filtern *}
</form>
<br class="clear" />
</div></div>

{if $query != '' && $threadEntries}
 <div class="shadow"><div class="nopadding">
 <table  class="centralTable">
 		    <colgroup>
	    	<col width="150px" />
			<col />
	  		</colgroup>
	  		<thead>
	  			<tr>
			      <th>Autoren</th>
			      <th>Beiträge</th>
			    </tr>
			</thead>
			<tbody>
				{foreach from=$threadEntries item="entry"}
					<tr>
						<td colspan="2" style="font-size: 1.4em; padding: 5px;">
							{assign var="thread" value=$entry->getThread()}
							gefunden im Thread: <a href="{forum_url thread=$thread}" >{$thread->getCaption()}</a>
						</td>
					</tr>
					<tr>
						<td style="background: #F7EDBE">
							{assign var="author" value=$entry->getAuthor()}
							{include file="modules/forum/user_entry_info/simple.tpl"}
							<br />
							Zeit: {$entry->getTimeLastUpdate()|unihelp_strftime}<br />
							<a href="{forum_url entryId=$entry->id}">zu diesem Beitrag gehen</a>
						</td>
						<td class="entry">
							<h5 style="font-weight: bold; margin: .5em;">
				          		{$entry->getCaption()}
				          	</h5>
				          	{$entry->getContentParsed()}
						</td>
					</tr>
				{/foreach}
			</tbody>      
 </table>
 </div></div>
{elseif $query != ''}
	Keine Ergebnisse
{/if}
