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

*}{* $Id: forum_thread_line.tpl 6210 2008-07-25 17:29:44Z trehn $
    $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/forum/internal/forum_thread_line.tpl $ *}
    {* included in ../overview.tpl *}

<tr>
    <td>
        <p>
		{dynamic}
       {if $forumRead->hasNewEntry($f)}
       <img src="/images/icons/new.png" alt="NEU" title="neuer Eintrag in diesen Forum"/>
       {/if}
       {/dynamic}
        <strong>{forum_link forum=$f title="Zum Forum gehen" }</strong><br />
        {$f->getDescriptionParsed()}<br/>
        {if $f->getTags()}
        (Tags: {foreach name=tags from=$f->getTags() item=tag}{forum_link tag=$tag}{if !$smarty.foreach.tags.last}, {/if}
               {/foreach})
         {/if}</p>
        </td>
    <td class="center">{$f->getNumberOfThreads()}</td>
    <td class="center">{$f->getNumberOfEntries()}</td>
    <td>{if $f->getLastEntry()}
        {assign var="lastEntry" value=$f->getLastEntry()}        
        <a href="{forum_url entryId=$lastEntry->id}">
        {if $lastEntry->getCaption()}
        	{$lastEntry->getCaption()|truncate:30:"...":true}
        {else}
	        {assign var="lastThread" value=$lastEntry->getThread()}
	        {$lastThread->getCaption()|truncate:30:"...":true}
	    {/if}    
        </a><br />
        {$lastEntry->getTimeEntry()|unihelp_strftime} von 
            {if $lastEntry->isAnonymous()}
                anonym
            {else}
            	{if $lastEntry->isForGroup()}
            		{group_info_link group=$lastEntry->getGroup()}
            	{else}
                	{user_info_link user=$lastEntry->getAuthor()}
                {/if}	
            {/if}
        {else}
            nicht vorhanden
        {/if}</td>
    <td>
    	{foreach name=mods from=$f->getModerators() item=moderator}
    		{user_info_link user=$moderator}{if !$smarty.foreach.mods.last}, {/if}
    	{/foreach}
    	{dynamic}
    	{if $cat->isModerator($visitor) || ($f != null && $f->isModerator($visitor))}
	        <br />
			<a href="{forum_url editForumId=$f->id}#editforum" title="editieren">
				<img src="/images/icons/table_edit.png" alt="Forum bearbeiten" />
			</a>
	        <a href="{forum_url delForumId=$f->id}" title="Forum löschen">
				<img src="/images/icons/table_delete.png" alt="Forum löschen" />
		    </a>
            {if !$smarty.foreach.fora.first && $show_order=='default'}
                <a href="{forum_url rePosForumId=$f->id position=up}" title="höher verschieben">
					<img src="/images/icons/arrow_up.png" alt="höher verschieben" />
                </a>
            {/if}
            {if !$smarty.foreach.fora.last && $show_order=='default' }
                <a href="{forum_url rePosForumId=$f->id position=down}" title="runter verschieben">
					<img src="/images/icons/arrow_down.png" alt="runter verschieben" />
                </a>
            {/if}
            {if $visitor->hasRight('TAG_MAP_ADMIN')}
            <a href="{forum_url editTagsForumId=$f->id}" title="Tags bearbeiten">
				<img src="/images/icons/tag_blue_edit.png" alt="Tags bearbeiten" />
		    </a>
		    {/if}
        {/if}
    	{/dynamic}
    </td>
</tr>
