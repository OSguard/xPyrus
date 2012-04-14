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

*}{* $Id: entry_comment.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
  <form enctype="multipart/form-data" action="{user_info_url user=$userinfo_user gbpage=$userinfo_guestbookpage}#gbentry{$userinfo_editentry->id}" method="post">
  <input type="hidden" name="method" value="commentGBEntry" />
  {* random id to avoid double postings *}
  <input type="hidden" name="{$smarty.const.F_ENTRY_RANDID}" value="{$randid}" />
  
  <fieldset>	
  <a name="postenanker" id="postenanker"></a>
    
  <h5 {if $userinfo_errors.missingFieldsObj.comment}class="missing"{/if}>Dein Kommentar</h5><br />
  <textarea name="entry_text" id="entrytext" rows="10" cols="45">{$userinfo_editentry->getComment()}</textarea><br />
  <input name="gbid" id="gbid" value="{$userinfo_editentry->id}" type="hidden" />
  
  <p class="note">Wenn Du das Kommentarfeld leer l&auml;sst, wird ein bestehender Kommentar gel&ouml;scht.</p>
  
  <button name="submit" value="submit" type="submit">Abschicken</button>
  </fieldset>		
  </form>
