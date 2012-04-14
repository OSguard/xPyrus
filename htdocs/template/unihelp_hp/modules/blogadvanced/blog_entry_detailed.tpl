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

*}{* $Id: blog_entry_detailed.tpl 5807 2008-04-12 21:23:22Z trehn $ 
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/blogadvanced/blog_entry_detailed.tpl $ *}

<div id="content">

<div class="blogbox shadow"><div>
	<h3>{$blog_entry->timeEntry|unihelp_strftime:"%A, %e. %B %Y"}</h3>
	
	{* show blog entry *}
	{include file="modules/blogadvanced/blog_entry_core.tpl"}
</div></div>{* end div blogbox *}

<div class="bloginfobox shadow"><div>
	<h3><a name="trackbacks">Trackbacks</a></h3>
	<p id="trackback_uri"> 
	<a href="{blog_url entry=$blog_entry owner=$blog_model->getOwner() trackback="yes"}">Trackback-URI für diesen Eintrag</a>
	</p>
	
	<ul id="trackbacklist">
	{foreach from=$blog_entry->getTrackbacks() item=trackback}
	<li>
		<h5><a href="{$trackback->weblogURL}">{$trackback->title|truncate:255:"..."}</a></h5>
		<p>{$trackback->body|truncate:255:"..."}</p>
		<dl>
		<dt>Weblog</dt><dd>{$trackback->weblogName}</dd>
		<dt>Aufgezeichnet</dt><dd>{$trackback->timeEntry|unihelp_strftime:"%d.%m, %H:%M"}</dd>
		</dl>
		{if $visitor->equals($blog_entry->getAuthor()) && $visitor->hasRight('BLOG_ADVANCED_OWN_ADMIN')}
			(<a href="{blog_url owner=$blog_model->getOwner() entry=$blog_entry delTrackback=$trackback}" onclick="return confirm('Bist Du sicher, dass Du diesen Trackback löschen möchtest?');">Trackback löschen</a>)
		{/if}
	</li>
	{foreachelse}
	<li>Keine Trackbacks</li>
	{/foreach} {* end trackback *}
	</ul>
</div></div>

<div class="bloginfobox shadow"><div>
	<h3><a name="comments">Kommentare</a></h3>
	<ul id="commentlist">
	{foreach from=$blog_entry->getComments() item=comment name="comment"}
	{* do NO html-escape here, because all escapement has been done in PHP *}
	<li {if $smarty.foreach.comment.last}class="last"{/if}>
		#{counter}
		<em>
		{if $comment->authorUnihelp != null}
			(intern) {user_info_link user=$comment->authorUnihelp}
		{else}
			(extern) 
				{if $comment->email}<a href="mailto:{$comment->email|escape:"hex"}">{$comment->authorName}</a>
				{else}{$comment->authorName}
				{/if}
		{/if}
		<a name="c{$comment->id}" class="comment">scripsit, {$comment->timeEntry|unihelp_strftime:"%d.%m.%Y, %H:%M"}:</a>
		</em>
		{if ($visitor->equals($blog_entry->getAuthor()) && $visitor->hasRight('BLOG_ADVANCED_OWN_ADMIN')) || $visitor->hasRight('BLOG_ADVANCED_ADMIN')}
			<a href="{blog_url owner=$blog_model->getOwner() entry=$blog_entry delComment=$comment}" onclick="return confirm('Bist Du sicher, dass Du diesen Kommentar löschen möchtest?');">Kommentar löschen</a>
		{/if}
		
		<p>
        {$comment->getComment()}
        </p>
								
	</li>
	{foreachelse}
	<li class="last">Keine Kommentare</li>
	{/foreach} {* end comment *}
	</ul>
	{if $visitor->isRegularLocalUser() && $blog_entry->isAllowComments()}
	   {assign var="type" value=$blog_entry->getSubscription($visitor)}
	   <form action="{blog_url owner=$blog_model->getOwner() entry=$blog_entry}" method="post">
	      <label for="entry_notify_comments">Bei Kommentaren mich benachrichtigen:</label>
		  <select name="entry_notify_comments" id="entry_notify_comments">
		    <option value="none" {if $type == "none"}selected="selected"{/if}>gar nicht</option>
		    <option value="pm" {if $type == "pm"}selected="selected"{/if}>per PN</option>
		  {if $visitor->getPrivateEmail() != ''}
		    <option value="email" {if $type == "email"}selected="selected"{/if}>per E-Mail</option>
		  {/if}
		  </select>
		  <input type="submit" name="change_notification" value="Ändern" id="change-notification" />
	   </form><br class="clear" />
	{/if}
</div></div>
	
	{errorbox var="comment_errors" caption="Fehler beim Kommentieren"}
	
	{if $blog_entry->isAllowComments()}
<div class="bloginfobox shadow"><div>
	<h3><a name="addcomment">Kommentar hinzufügen</a></h3>
		
	<form action="{blog_url owner=$blog_model->getOwner() entry=$blog_entry}#addcomment" method="post">
{if $visitor->isAnonymous()}
	{if $comment_errors.missingFieldsObj.comment_name}<span class="missing">{/if}
	<label for="comment_name">Name:</label>
		<input type="text" name="comment_name" id="comment_name" value="{$comment_edit->authorName}" /><br class="clear" />
    {if $comment_errors.missingFieldsObj.comment_name}</span>{/if}
    {if $comment_errors.missingFieldsObj.comment_email}<span class="missing">{/if}
	<label for="comment_email">E-Mail:</label>
		<input type="text" name="comment_email" id="comment_email" value="{$comment_edit->email}" /><br />
	{if $comment_errors.missingFieldsObj.comment_email}</span>{/if}
	{if $comment_errors.missingFieldsObj.comment_captcha}<span class="missing">{/if}
	<label for="comment_captcha">{$comment_captcha->render()}</label>
	   <input type="text" name="comment_captcha" id="comment_captcha" /><br />
	{if $comment_errors.missingFieldsObj.comment_captcha}</span>{/if}
{else}
	<label for="comment_name">Name:</label>
		<input type="text" name="comment_name" id="comment_name" value="{$visitor->getUsername()}" readonly="readonly" /><br />
	<input name="comment_email" value="" type="hidden" />
{/if}

    {if $comment_errors.missingFieldsObj.comment_comment}<span class="missing">{/if}	
	<label for="entrytext">Kommentar:</label>
		<textarea id="entrytext" name="comment_comment" rows="6" cols="52">{$comment_edit->comment}</textarea>
	{if $comment_errors.missingFieldsObj.comment_comment}</span>{/if}
	{* random string to avoid multiple not wanted post *}
	<input type="hidden" name="randomstring" value="{$blog_randomstring}" />
	<input name="comment_submit" class="filterbutton" type="submit" value="Abschicken" />
	</form>
	
	<br class="clear" />
	
	{include file="common/entry_smileys.tpl" smileys_only="true"}
	
	<br class="clear" />
</div></div>{* end div bloginfobox *}
	{/if} {* end if comments are allowed *}

</div> {* end div content *}
