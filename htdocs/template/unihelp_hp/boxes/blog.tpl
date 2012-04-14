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

*}{* $Id: blog.tpl 6210 2008-07-25 17:29:44Z trehn $ *}
{if !$box_blog_ajax}
<div class="box" id="box_blog:1">
<h3>Blogosphäre</h3>
{dynamic}
{if $visitor->hasRight('FEATURE_BOX_REARRANGEMENT')}
<a href="{box_functions box=blog close=true}{*/index.php?dest=box&amp;bname=blog&amp;method=close*}" class="icon iconClose" title="Box schließen" id="blog:1_close"><span>x</span></a>
{if !$box_blog_minimized}
<a href="{box_functions box=blog minimize=true}{*/index.php?dest=box&amp;bname=blog&amp;method=minimize*}" class="icon iconMinimize" id="blog:1_collapse" title="Box minimieren"><span>_</span></a>
{else}
<a href="{box_functions box=blog maximize=true}{*/index.php?dest=box&amp;bname=blog&amp;method=maximize*}" class="icon iconMaximize" id="blog:1_collapse" title="Box maximieren"><span>O</span></a>
{/if}{* end minimized *}
{/if}{* end rights check *}
{/dynamic}
{/if}{* end ajax *}

{if !$box_blog_minimized}
{if $box_blog_entries}
<div class="boxcontent">
<div>
{foreach from=$box_blog_entries item=entry}
    <div style="border-bottom: 1px solid #cfcfcf; overflow:hidden; padding-bottom: 0px;">
        {if $entry->isForGroup()}
        	{assign var="group" value=$entry->getOwner()}
        	<a href="{group_info_url group=$group}" >
    			<img src="{$group->getPictureFile('tiny')|default:"/images/kegel_group_tiny.png"}" alt="Logo von {$group->name}"/>
    		</a>	
        	<span>
        		 <strong>{group_info_link group=$group}</strong>, <span style="font-size: xx-small;">{$entry->getTimeEntry()|date_format:"%d.%m.%y"}</span>: 
        		<a href="{blog_url owner=$entry->getOwner() entry=$entry}">{$entry->getTitle()}</a>
        	</span>
        {else}        
	        {assign var="author" value=$entry->getAuthor()}
	        <a style="margin:2px; margin-left:5px; float:left;" href="{user_info_url user=$author}">
	            <img width="24" src="{userpic_url tiny=$author}" alt="{$author->getUsername()|escape:"html"}" style="margin: 2px; border: #cfcfcf 1px solid; padding: 1px; margin-right: .5em;" /> 
	        </a>
	        <span>
	            <strong>{user_info_link user=$author}</strong>, <span style="font-size: xx-small;">{$entry->getTimeEntry()|date_format:"%d.%m.%y"}</span>: 
	            <a href="{blog_url owner=$entry->getOwner() entry=$entry}">{$entry->getTitle()}</a>
	        </span>
        {/if}
    </div>
{/foreach}
</div></div>
{/if}
{/if}

{if !$box_blog_ajax}
</div>
{/if}
