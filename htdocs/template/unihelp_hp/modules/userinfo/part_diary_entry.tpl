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

*}    <div class="entry {if $smarty.foreach.diary.last}entrylast{/if} {if $mark_diary_entry}highlighted{/if}">
      <span class="date" ><a name="diaryentry{$diaryentryId}">Eintrag</a> von {user_info_link user=$userinfo_user} {$diaryentry->getTimeEntry()|unihelp_strftime}</span>
      <span class="button">
{dynamic}
	      {* show entry edit/delete buttons, if and only if viewer has appropriate permissons *}
	      {if $userinfo_permissions.diary_edit || $userinfo_permissions.diary_admin}
	        <a href="{user_info_url user=$userinfo_user diarypage=$userinfo_diarypage prepDiaryEntryId=$diaryentryId}#postenanker" title="diesen Beitrag editieren">
	        	<img src="{$TEMPLATE_DIR}/images/edit.png" alt="editieren" />
	        </a>
	      {/if}
       {if 1} {/if}{* useless space that will not be stripped in order to have a gap here *}
	      <a href="{user_info_url user=$userinfo_user linkDiaryEntryId=$diaryentryId}#diaryentry{$diaryentryId}" title="direkter Link zu diesem Beitrag">
	      	<img src="{$TEMPLATE_DIR}/images/link.png" alt="Link" />
	      </a>
       {if 1} {/if}{* useless space that will not be stripped in order to have a gap here *}
	      {if $userinfo_permissions.diary_delete || $userinfo_permissions.diary_admin} 
			<a href="{user_info_url user=$userinfo_user diarypage=$userinfo_diarypage delDiaryEntryId=$diaryentryId}" title="diesen Beitrag löschen">
				<img src="{$TEMPLATE_DIR}/images/delete.png" alt="löschen" />
			</a>
	      {/if}
{/dynamic}
       </span>
	  <div class="entrycontent">
      {* via parameter true we prepare the entry to save the parsed content into DB if this has not happened yet *}
      {$diaryentry->getContentParsed(true)}
      </div>
      <br class="clear" />
    </div>
