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

*}{* $Id: part_guestbook_alternative_icons.tpl 5807 2008-04-12 21:23:22Z trehn $
     $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/userinfo/part_guestbook_alternative_icons.tpl $ *}
    {foreach from=$userinfo_guestbook item=guestbookentry name="guestbook"}
      {assign var="guestbookentryAuthor" value=$guestbookentry->getAuthor()}
        {strip}
          <tr id="gbentryrow{$guestbookentry->id}" {if $smarty.foreach.guestbook.last || $guestbookentry->id == $userinfo_markids.guestbook}class="{if $guestbookentry->id == $userinfo_markids.guestbook}highlighted{/if} {if $smarty.foreach.guestbook.last}entrylast{/if}"{/if}>
            <td class="entry-value">
            	<a name="gbentry{$guestbookentry->id}"></a>
            	{if $guestbookentry->isUnread() && !$userinfo_is_external_view}<strong>Neu</strong><br/>{/if}
	            <p class="info">Autor<br />
	            {user_info_link user=$guestbookentryAuthor truncate=17}<br />
	            <a href="{user_info_url  user=$guestbookentryAuthor}" title="{$guestbookentryAuthor->getUsername()}">
	            	<img src="{userpic_url tiny=$guestbookentryAuthor}" alt="Userpic von {$guestbookentryAuthor->getUsername()}" />
	            </a>
	            </p>
	            <p class="points">
	             {if $guestbookentry->getValue()>=1}
	                <span class="symbol">+
	             {elseif $guestbookentry->getValue()==0}
	                <span class="symbol">o
	             {elseif $guestbookentry->getValue()<=-1}
	                <span class="symbol" style="color:red">-
	             {/if}
	            </span><br />
	            <span class="description">
	             {if $guestbookentry->getValue()==1}{$guestbookentry->getValue()} Punkt
	             {elseif $guestbookentry->getValue()>1}{$guestbookentry->getValue()} Punkte
	             {elseif $guestbookentry->getValue()==0}{$guestbookentry->getValue()} Punkte
	             {elseif $guestbookentry->getValue()<=-1}{$guestbookentry->getValue()} Punkte{/if}
	            </span>
	            </p>
        </td>
        <td class="entry">
      <span class="date">geschrieben {$guestbookentry->getTimeEntry()|unihelp_strftime}</span>
      <span class="button">

      

      {if $userinfo_permissions.guestbook_edit && $guestbookentryAuthor->equals($visitor)
            || $userinfo_permissions.guestbook_admin} {* right to modify self-written entries or administrate *}
        <a href="{user_info_url user=$userinfo_user gbpage=$bc prepGBEntryId=$guestbookentry->id}#postenanker" title="Eintrag bearbeiten">
          <img src="/images/icons/note_edit.png" alt="bearbeiten" /></a>
      {/if}

      {if $userinfo_permissions.guestbook_quote && $userinfo_user->equals($visitor)} {* right to quote on entries in own guestbook*}
        <a href="{user_info_url user=$guestbookentryAuthor quoteGBEntryId=$guestbookentry->id}#postenanker" title="zitierend antworten">
        <img src="/images/icons/note_quote.png" alt="zitieren" /></a>
      {/if}

      {if $userinfo_permissions.guestbook_quote && $userinfo_user->equals($visitor)} {* right to answer on entries in own guestbook*}
        <a href="{user_info_url user=$guestbookentryAuthor}#postenanker" title="auf Eintrag antworten">
          <img src="/images/icons/note_go.png" alt="antworten" /></a>
      {/if}

      {if $userinfo_permissions.guestbook_comment && $userinfo_user->equals($visitor)} {* right to comment on entries in own guestbook*}
        <a href="{user_info_url user=$userinfo_user gbpage=$bc prepCommentGBEntryId=$guestbookentry->id}#postenanker" title="Eintrag kommentieren">
          <img src="/images/icons/note_comment.png" alt="kommentieren" /></a>
      {/if}
      {if 1} {/if}{* useless space that will not be stripped in order to have a gap here *}
      <a href="{user_info_url user=$userinfo_user linkGBEntryId=$guestbookentry->id}#gbentry{$guestbookentry->id}" title="direkter Link zu diesem Beitrag">
        <img src="/images/icons/note_link.png" alt="Link setzen" /></a>

      {if $userinfo_permissions.guestbook_delete && $userinfo_user->equals($visitor)
            || $userinfo_permissions.guestbook_admin} {* right to delete entries in own guestbook or administrate *}
        <a href="{user_info_url user=$userinfo_user gbpage=$bc delGBEntryId=$guestbookentry->id}" title="Eintrag löschen">
          <img src="/images/icons/note_delete.png" alt="löschen" /></a>
      {/if}
      {if 1} {/if}{* useless space that will not be stripped in order to have a gap here *}
      	<a href="{user_info_url user=$userinfo_user reportGBEntryId=$guestbookentry->id}" title="Diesen Beitrag melden">
      	<img src="/images/icons/note_error.png" alt="Diesen Beitrag melden" /></a>
      </span>
      <div class="entrycontent">
        {* via parameter true we prepare the entry to save the parsed content into DB if this has not happened yet *}
        {$guestbookentry->getContentParsed(true)}
      </div>
        </td>
        </tr>
        {/strip}
	{foreachelse}
		{$userinfo_user->getUsername()} hat noch keine Gästebucheinträge.
    {/foreach}
