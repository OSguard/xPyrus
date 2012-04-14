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

*}<div class="shadow" style="float: left; width: 99%; margin-bottom: -20px">
<div style="min-height:100px;">
<img src="template/unihelp_hp/css/images/weblogo.png" alt="[[local.local.project_name]] Logo" style="margin:15px;float:left;"/>

<h2 id="h2home">##welcome##</h2>
<strong>{if $newUser}
	##index_newestUser##: {user_info_link user=$newUser} ({$newUser->getFirstLogin()|unihelp_strftime}) <br />
	{/if}
##index_registeredStudents##</strong>
{if !$visitor->isLoggedIn()}
<p class="clear margin">[[local.local.welcome_text]]
</p><p class="clear margin"><i>##index_haveFun##</i>
</p>
{/if}
</div>
</div>

{dynamic}
	{foreach from=$threadsBoxes item="box"}
		{$box->getContent()}
	{/foreach}
{/dynamic}

<br class="clear" />

{dynamic}{* CACHEME ?? *}
{if $invisibleNews}
<div class="shadow"><div class="nopadding">
<h3>##index_newsUnpublished##</h3>
		<table class="centralTable">
			<tr style="border-bottom:1px solid #cfcfcf; width:100%;">
				<th style="padding-left:5px; padding-right:5px; width:60%">##heading##</th>
				<th style="padding-left:5px; padding-right:5px;">##organization##</th>
				<th style="padding-left:5px; padding-right:5px;">##author##</th>
				<th style="padding-left:5px; padding-right:5px; width:50px;"></th>
			</tr>
		{foreach from=$invisibleNews item=inews}
			<tr>
				<td style="padding-left:5px; padding-right:5px;">
					<a href="/home/news/{$inews->id}/edit">{$inews->getCaption()}</a>
				</td>
				<td style="padding-left:5px; padding-right:5px;">
					{assign var="group" value=$inews->getGroup()}
					{group_info_link group=$group}
				</td>
				<td style="padding-left:5px; padding-right:5px;">{user_info_link user=$inews->getAuthor()}</td>
				<td style="padding-left:5px; padding-right:5px;" >
					<a href="/home/news/{$inews->id}/edit" title="##edit##">
						<img src="/images/icons/newspaper_edit.png" alt="##edit##" />
					</a>
					<a href="/home/news/{$inews->id}/del" title="##delete##">
						<img src="/images/icons/newspaper_delete.png" alt="##delete##" />
					</a>
				</td>
			</tr>	
		{/foreach}
		</table>
</div>
</div>
{/if}
{/dynamic}

<div id="tabNavigation">
	<ul>
	    <li>
	        <a class="active">##news##</a>
	    </li>
	    {dynamic}
	    {if $visitor->hasGroupRight('NEWS_ENTRY_ADD')}
		<li>
		    <a href="/home/news/add">##index_addNews##</a>
		</li>	
	    {/if}
	    {/dynamic}
	    <li>
		<a href="/home/oldnews">##newsArchive##</a>
	   </li>	
	</ul>
</div>
<div class="shadow" id="news"><div style="background: #fff;">
{dynamic}
{foreach from=$news item=new name=news}
    {$new->getContent()}
{foreachelse}
    ##index_noNews##
{/foreach}
{/dynamic}
</div></div>

<br class="clear" />
