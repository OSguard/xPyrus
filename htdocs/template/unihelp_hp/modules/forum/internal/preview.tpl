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

*}	
  {* previewMode -- beginn *}
  {if $entryToEdit != null && $isPreview}
  <a name="post"></a>	
    
  
  <div class="shadow"><div class="nopadding">
  <h3>Vorschau</h3>
  <table class="centralTable" summary="Die Vorschau">  		
	  <colgroup>
	    <col width="150px" />
		<col />
	  </colgroup>
	  <thead>
	    <tr>
		  <th>Information</th>
		  <th>Beitrag</th>
	    </tr>
      </thead>
      <tbody>        
                
          <tr class="thread-entry-body">
          <td style="background: #F7EDBE">
            {assign var=entry value=$entryToEdit}
            {assign var="author" value=$entry->getAuthor()}
            {include file="modules/forum/user_entry_info/default.tpl"}
          </td>
          <td style="text-align: justify; padding: 0.3em;" class="entry">
            <h5 style="font-weight: bold; margin: .5em;">{$entryToEdit->getCaption()}</h5>
          		
            {$entryToEdit->getContentParsed()}
            
            {assign var="editEntryAuthor" value=$entryToEdit->getAuthor()}
            
            {if $editEntryAuthor->getSignature() != '' && !$entryToEdit->isAnonymous() && !$entryToEdit->isForGroup()}
	            <hr style="width: 15px; height: 1px; border: solid 1px #cfcfcf; margin: 0.7em auto 0.2em 0;" />
                <em>{$editEntryAuthor->getSignature()}</em>
            {/if}
            
          </td>
        </tr>
     </tbody>
   </table>
   </div></div>
   {/if}
   {* previewMode -- beginn *}
