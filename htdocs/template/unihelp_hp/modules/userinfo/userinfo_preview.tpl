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

*}  <div class="shadow" id="preview">
  <div class="nopadding">
  <div class="entry entrylast nopadding">
  <a name="post"></a><a name="postdiary"></a>
   <h3>Vorschau</h3>
    {if $isGuestbook}
    	<table id="userguestbookpre" summary="Vorschau" class="nomargin">
    		<tbody >
    			 <tr>
		            <td class="entry-value">
						{assign var="guestbookentryAuthor" value=$userinfo_editentry->getAuthor()}
			            <p class="info">Autor<br />
			            {user_info_link user=$userinfo_editentry->getAuthor() truncate=17}<br />
			            <a href="{user_info_url  user=$guestbookentryAuthor}" title="{$guestbookentryAuthor->getUsername()}">
			            	<img src="{userpic_url tiny=$guestbookentryAuthor}" alt="Userpic von {$guestbookentryAuthor->getUsername()}" />
			            </a>
			            </p>
			            <p class="points">
			             {if $userinfo_editentry->getValue()>=1}
			                <span class="symbol">+
			             {elseif $userinfo_editentry->getValue()==0}
			                <span class="symbol">o
			             {elseif $userinfo_editentry->getValue()<=-1}
			                <span class="symbol" style="color:red">-
			             {/if}
			            </span><br />
			            <span class="description">
			             {if $userinfo_editentry->getValue()==1}{$userinfo_editentry->getValue()} Punkt
			             {elseif $userinfo_editentry->getValue()>1}{$userinfo_editentry->getValue()} Punkte
			             {elseif $userinfo_editentry->getValue()==0}{$userinfo_editentry->getValue()} Punkte
			             {elseif $userinfo_editentry->getValue()<=-1}{$userinfo_editentry->getValue()} Punkte{/if}
			            </span>
			            </p>
			            <br style="clear: both" />
		        </td>
		        <td class="entry">
			      <span class="date">geschrieben {$userinfo_editentry->getTimeEntry()|unihelp_strftime}</span>
			     
			      <div class="entrycontent">
			        {* via parameter true we prepare the entry to save the parsed content into DB if this has not happened yet *}
			        {$userinfo_editentry->getContentParsed()}
			      </div>
		        </td>
		        </tr>
		    			
    		</tbody>
    	</table>	
    {else}
        <span class="date" >Eintrag von {user_info_link user=$userinfo_editentry->getAuthor()} {$smarty.now|unihelp_strftime}</span>
        <br />
        {$userinfo_editentry->getContentParsed()}
        <br class="clear" />
    {/if}
  
  </div>
  </div>
  </div>