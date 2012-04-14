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

*}<p>
{if !$entry->isAnonymous() && !$entry->isForGroup()}
    {user_info_link user=$author}
    <br />
    <a href="{user_info_url  user=$author}" title="{$author->getUsername()}">
    	<img src="{userpic_url tiny=$author}" alt="UserBild" style="margin-top: 3px;"/>
    </a>	
    <br />
    <span class="info"> 
    {* Stress-O-Meter: *} 
    {dynamic}
    {if $visitor->hasRight('FORUM_THREAD_RATING') && !$visitor->equals($author) && $thread!=null && $thread->isAuthor($visitor) &&  !$thread->hasUserRating($author, $visitor)}
    <p class="forumVoting">
    <a href="/index.php?mod=forum&amp;method=ForumRating&amp;threadId={$thread->id}&amp;page={$thread->getPage()}&amp;rateid={$authorId}&amp;rating=neg#entry{$entryId}" title="negativ bewerten"><img src="/images/icons/delete.png" alt="negativ" /></a>
    {/if}
    {/dynamic}
    	{if $author->hasForumRating()}
    		{if $author->getForumRating() == 0}
    			<img src="/images/bewertungen/bewertung_0.png" alt="0" />
    		{elseif $author->getForumRating() > 0 && $author->getForumRating() <= 2}
    			<img src="/images/bewertungen/bewertung_+0.png" alt="+1" />
    		{elseif $author->getForumRating() > 2 && $author->getForumRating() <= 3.1}
    			<img src="/images/bewertungen/bewertung_+1.png" alt="+2" />
    		{elseif $author->getForumRating() > 3.1 && $author->getForumRating() <= 4.2}
    			<img src="/images/bewertungen/bewertung_+2.png" alt="+3" />
    		{elseif $author->getForumRating() > 4.2 && $author->getForumRating() <= 5.3}
    		    <img src="/images/bewertungen/bewertung_+3.png" alt="+4" />
    		{elseif $author->getForumRating() > 5.3 && $author->getForumRating() <= 6.5}
    		    <img src="/images/bewertungen/bewertung_+4.png" alt="+5" />
    		{elseif $author->getForumRating() > 6.5} 
    			<img src="/images/bewertungen/bewertung_+5.png" alt="+6" />  
    		{elseif $author->getForumRating() < 0 && $author->getForumRating() >= -2}
    			<img src="/images/bewertungen/bewertung_-0.png" alt="-1" /> 
    		{elseif $author->getForumRating() < -2 && $author->getForumRating() >= -3.1}
    			<img src="/images/bewertungen/bewertung_-1.png" alt="-2" /> 
    		{elseif $author->getForumRating() < -3.1 && $author->getForumRating() >= -4.2}
    			<img src="/images/bewertungen/bewertung_-2.png" alt="-3" /> 
    		{elseif $author->getForumRating() < -4.2 && $author->getForumRating() >= -5.3}
    			<img src="/images/bewertungen/bewertung_-3.png" alt="-4" /> 
    		{elseif $author->getForumRating() < -5.3 && $author->getForumRating() >= -6.5}
    			<img src="/images/bewertungen/bewertung_-4.png" alt="-5" /> 
    		{elseif $author->getForumRating() < -6.5}
    			<img src="/images/bewertungen/bewertung_-5.png" alt="-6" /> 
    		{else}
    		{/if}  
    	{else}
    	  <img src="/images/bewertungen/bewertung_0.gif" alt="0" /> 
    	{/if} 
    	
    	{dynamic}
    	{if $visitor->hasRight('FORUM_THREAD_RATING') && !$visitor->equals($author) && $thread!=null && $thread->isAuthor($visitor) &&  !$thread->hasUserRating($author, $visitor)}
    	<a href="/index.php?mod=forum&amp;method=ForumRating&amp;threadId={$thread->id}&amp;page={$thread->getPage()}&amp;rateid={$authorId}&amp;rating=pos#entry{$entryId}" title="positiv bewerten"><img src="/images/icons/accept.png" alt="positiv" /></a>
    	</p>
    	{/if}
        {/dynamic}
        
    	{* need to call hasForumRating here, because we don't know if the DB has the value or not *}
    	<br />
    	Punkte: {$author->getPoints()}<br />
    	Beitr&auml;ge: {$author->getForumEntries()}<br />
    	Online-Aktivität: {$author->getActivityIndex()|string_format:"%.2f"}<br />
    </span>
{elseif $entry->isForGroup()}
    {group_info_link group=$entry->getGroup()}
    {assign var="group" value=$entry->getGroup()}
    <br />
    <a href="{group_info_url group=$group}" >
    		<img src="{$group->getPictureFile('tiny')|default:"/images/kegel_group.png"}" alt="Logo von {$group->name}" />
    </a>	
{else}
    <em>anonym</em>
{/if}

</p>
