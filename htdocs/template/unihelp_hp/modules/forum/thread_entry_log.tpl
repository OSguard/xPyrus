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

*}{* $Id: thread_entry_log.tpl 5807 2008-04-12 21:23:22Z trehn $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/forum/thread_entry_log.tpl $ *}
<br />

{* {include file="banner.tpl"}
 *}

<h2>Historie des Forum-Eintrags</h2>

<a href="{forum_url entryId=$entryNow->id}">zu diesem Beitrag gehen</a>
<br />

<div class="shadow"><div class="nopadding">
<table class="centralTable">
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
				{foreach from=$entryHistory item="entry"}
					<tr>
						<td colspan="2" style="font-size: 1.4em; padding: 5px;">
							
						</td>
					</tr>
					<tr>
						<td style="background: #F7EDBE">
							{assign var="author" value=$entry->getAuthor()}
							<p>
							{assign var="show" value=true}
							{if $entry->isForGroup()}
							    Wurde gepostet als:{group_info_link group=$entry->getGroup()}
							    <br />
							    {assign var="show" value=false}
							{elseif $entry->isAnonymous()}
							    Wurde gepostet als:<strong>anonym</strong>
							    <br />
							    {assign var="show" value=false}
							{/if}
							{if $show || $forum->isModerator($visitor)}    
							    Eintrag verfasst von:
							    {user_info_link user=$author}
							    <br />
							    <a href="{user_info_url user=$author}" title="{$author->getUsername()}">
							    	<img src="{userpic_url tiny=$author}" alt="UserBild" style="margin-top: 3px;" />
							    </a>	
							    <br />
							{/if}
							{if $forum->isModerator($visitor)} 
								IP: {$entry->getPostIP()}<br />
							{/if}
							Zeit: {$entry->getTimeLastUpdate()|unihelp_strftime}
							</p>
							
							<br />
							
						</td>
						<td class="entry">
							<h5 style="font-weight: bold; margin: .5em;">
				          		{$entry->getCaption()}
				          	</h5>
				          	{$entry->getContentParsed(true)}
						</td>
					</tr>
				{/foreach}
			</tbody>    

</table>


</div></div>
