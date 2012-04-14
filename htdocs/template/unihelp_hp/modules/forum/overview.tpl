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

*}{* $Id: overview.tpl 6210 2008-07-25 17:29:44Z trehn $
    $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/forum/overview.tpl $ *}
{*   {include file="banner.tpl"} *}
  
  <p style="float: right">
  <a href="{forum_url latest=true}?show=community" title="Zu den letzten Beiträgen" >
	<img src="/images/icons/user_comment.png" alt="Zu den letzten Beiträgen" />Zu den letzten Beiträgen
  </a>
  </p>
  
  {foreach from=$forum item=cat name="cats"}
  	{* Anker *}
  	{if $cat->getType() == 'course'}
  		<a name="course" id="course"></a>
  	{elseif $cat->getType() == 'group'}
  		<a name="orgas" id="orgas"></a>
  	{elseif $cat->getName() == 'Marktplatz'}
  		<a name="marketplace" id="marketplace"></a>
    {/if} 
  	<a name="cat{$cat->id}" id="cat{$cat->id}"></a>
        {* for each category a table *}
        <div class="shadow"><div class="nopadding">
        	{if $cat->getType() == 'course'}
		  		<h3>Alle Foren zu Deinen Fächern </h3>
		  	{elseif $cat->getType() == 'group'}
		  		<h3>Alle Foren zu Deinen Organisationen </h3>
		  	{else}
		  		<h3>Alle Foren der Kategorie {$cat->getName()} </h3>	
		    {/if} 
		
         <table class="centralTable" summary="Enth&auml;lt eine &Uuml;bersicht aller Foren in der Kategorie {$cat->getName()} mit wesentlichen Merkmalen">
            <thead>
                <tr>
                    <th>Forum</th>
                    <th>Themen</th>
                    <th>Beitr&auml;ge</th>
                    <th>Letzter Beitrag</th>
                    <th>Moderatoren</th>
                </tr>
            </thead>
            {if $cat->isModerator($visitor) && $cat->getType() == 'default' && $showAddForum == $cat->id}
            <tfoot>
                <a name="addforum" id="addforum"></a>
                {include file="modules/forum/internal/add_forum.tpl"}
            </tfoot>
            {/if}
            {if $cat->isModerator($visitor) && ($forumToEdit != null && $forumCategory->id == $cat->id)}
            <tfoot>
                <a name="editforum" id="editforum"></a>
                {include file="modules/forum/internal/add_forum.tpl"}
            </tfoot>
            {/if}
            <tbody>
             
                <tr>
                	<td colspan="7">
                		<span style="float:left"><strong>Beschreibung:</strong> {$cat->getDescriptionParsed()}
                		<br />
                		{if $cat->getModerators()}
                			(Moderatoren: {foreach name=mods from=$cat->getModerators() item=moderator}
                							{user_info_link user=$moderator}{if !$smarty.foreach.mods.last}, {/if}
                						  {/foreach})
               			{/if}       
                		
                		</span>
           		        {if ($cat->isModerator($visitor) ) && $cat->getType() == 'default'}
                		<span style="float:right">
                        	<a href="{forum_url addForum=$cat->id}" title="neues Forum anlegen">
								<img src="/images/icons/table_add.png" alt="neues Forum" />
							</a>
	                        <a href="{forum_url editCategoryId=$cat->id}#showCat" title="Kategorie bearbeiten">
								<img src="/images/icons/table_edit.png" alt="Bearbeiten" />
							</a>
	                        <a href="{forum_url delCategoryId=$cat->id}" title="Kategorie löschen">
								<img src="/images/icons/table_delete.png" alt="Löschen" />
							</a>
	                        {if !$smarty.foreach.cats.first && $cat->getType()=='default'}
	                        	<a href="{forum_url rePosCategoryId=$cat->id position=up}" title="Nach oben setzen">
									<img src="/images/icons/arrow_up.png" alt="Nach oben setzen" />
								</a>
	                        {/if}
	                        {if !$smarty.foreach.cats.last && $cat->getType()=='default' }
	                        	<a href="{forum_url rePosCategoryId=$cat->id position=down}" title="Nach unten setzen">
									<img src="/images/icons/arrow_down.png" alt="Nach unten setzen" />
								</a>
	                        {/if}
                		</span>
                        {/if}{* end if category mod *}
                	</td>
                </tr>
                {foreach from=$cat->forums item=f2 name=fora}
                    {*assign var="show_order"  value=$cat->getType() *}
                    {*include file="modules/forum/internal/forum_thread_line.tpl"*}
                    {$f2->getContent()}
	       {foreachelse}
			<tr>
				<td colspan="5" class="emptyTable">
						{if $cat->getType() == 'course'}
							Es gibt noch keine Foren zu Deinen Fächern
						{elseif $cat->getType() == 'group'}
							Es gibt noch keine Foren zu Deinen Organisationen
						{else}
							Es gibt noch keine Foren in der Kategorie {$cat->getName()}	
						{/if}
				</td>
			</tr>		
                {/foreach}
            </tbody>
        </table>
        </div>
        </div>
  {/foreach}
{* end - foreach over all Categories *}
  
{* start - virtuelle Foren *}
{if $linkedTags}
<div class="shadow"><div class="nopadding">
	<h3>Alle Foren die zu Deinem Studiengang passen</h3>
	<table class="centralTable" summary="Die Tabelle enth&auml;lt alle virtuellen Foren'">
 	
	  <thead>
	    <tr>
		  <th>Forum</th>
		  <th>Threads</th>
		  <th>Beitr&auml;ge</th>
		  <th>letzter Beitrag</th>
	    </tr>
      </thead>
      <tbody>
        
        {foreach from=$linkedTags item=tag}
        {assign var="tagid" value=$tag->id}
        <tr>
	   <td> 
            {forum_link tag=$tag title="Zu allen Threads, die mit diesen Tag verknüpft sind"}
            </td>
            <td class="center">{$tagStats[$tagid].number_of_threads}</td>
            <td class="center">{$tagStats[$tagid].number_of_entries}</td>
            <td>
            	{if $tagThreads[$tagid]}
                    {assign var="lastEntry" value=$tagThreads[$tagid]->getLastEntry()}
                    <a href="{forum_url entryId=$lastEntry->id}">{$lastEntry->getCaption()|default:"(zum Beitrag)"}</a><br />
            		{$tagThreads[$tagid]->getTimeEntry()|unihelp_strftime} von 
            		{if $lastEntry->isAnonymous()}
		                Anonymous
		            {else}
		            	{if $lastEntry->isForGroup()}
		            		{group_info_link group=$lastEntry->getGroup()}
		            	{else}
		                	{user_info_link user=$lastEntry->getAuthor()}
		                {/if}	
		            {/if}
            		</td>
        		{else}
        			nicht vorhanden
        		{/if}
        </tr>
         {foreachelse}
        <tr>
        	<td colspan="4" class="emptyTable">
        		Es gibt noch keine Tags zu Deinem Studiengang
        	</td>
        </tr>
        {/foreach}
        
      </tbody>
   </table>
</div></div>   
{/if}   
{* end - virtuelle Foren  *}


  
{if $visitor->hasRight('FORUM_CATEGORY_ADMIN')}
<div class="shadow">
<div>
{if $categoryToEdit}
	<a href="/forum?addForum=0#edit">neue Kategorie</a>
	{include file="modules/forum/internal/add_category.tpl"}
{else}
	{if !isset($showAddForum) || $showAddForum  != 0}
	<a href="/forum?addForum=0#edit">neue Kategorie</a> 
	{else}
	<a href="{forum_url}">keine neue Kategorie</a>		
		<a name="edit" id="edit"></a>
		{include file="modules/forum/internal/add_category.tpl"}
	{/if}
{/if}	
</div></div> 	
{/if}
{* end add Categorie forumlar *}  

