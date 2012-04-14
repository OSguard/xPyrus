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

*}{* $Id: infopage.tpl 6210 2008-07-25 17:29:44Z trehn $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/groups/infopage.tpl $ *}

{* default title will be translated *}
{if $group->title != 'group'}{assign var="grouptitle" value=$group->title}{else}{assign var="grouptitle" value="Organisation"}{/if}

  
  {* we need no dynamic here, because in this if-branch we are hopefully in non-cache mode *}
  {if $application}
  <div class="shadow">
	  <div>
	  <h3>Bewerbung</h3>
	  {if $successApplication}
	  		Deine Bewerbung wurde erfolgreich abgeschickt!
	  {else}
	  		<span style="color: red; font-size: 1.5em;">Bewerbung leider nicht möglich. Wende Dich bitte an den <a href="{mantis_url addgroup=1}">Support</a>.</span>
	  {/if}
	  </div>
  </div>
  {/if}
  {* example for tiny picture <img src="{$group->getPictureFile('tiny')|default:"/images/kegel_group.png"}" alt="Logo" /> *}
	  <div class="shadow">
	  <div id="group-description" class="entry entrylast">
	  	<h3>Beschreibung</h3>
	    <div id="logo_ext"><img id="logo" src="{$group->getPictureFile()|default:"/images/kegel_group.png"}" alt="Logo" /></div>
	    {assign var=infopage value=$group->getInfopage()}
	    {$infopage->content}
{dynamic}
         {if $visitor->hasGroupRight('GROUP_INFOPAGE_EDIT', $group->id) || $visitor->hasRight('GROUP_INFOPAGE_ADMIN')}
		  	<p><a href="{group_info_url editInfo=$group->id}#entryfield">edit</a> </p>
		 {/if}
{/dynamic}
      <br class="clear" />
	  </div></div>
	  
	  <div class="shadow" id="course-forum"><div>
	    <h3>Mitglieder</h3>
	    <table>
	      <tr>
	        <td style="vertical-align: top">Aktionen:</td>
	        <td style="vertical-align: top">
			 {assign var=caption value="["|cat:$group->name|cat:"]"}
			   {assign var=receivers value="["|cat:$group->getName()|cat:"]"}
			   <a href="{pm_url new=true caption=$caption receivers=$receivers}" title="Eine PN an alle Mitglieder senden" class="boxlink">Rundmail schreiben</a>
			{dynamic}
			  {if $visitor->hasGroupRight('GROUP_OWN_ADMIN', $group->id) || $visitor->hasRight('GROUP_ADMIN')}
			  	 <br /><a href="{group_info_url groupToEdit=$group->id}" class="boxlink">Adminbereich</a>
			  {/if}
			  {if !$group->hasMember($visitor) && !$visitor->isExternal() && $visitor->isLoggedIn()}
			  	<br /><a title="Ich möchte Mitglied in der {if $group->title!='group'}{$group->title} {else}Organisation {/if}werden" href="{group_info_url applicationId=$group->id}" class="boxlink">Mitglied werden</a>
			  {/if}
			  {if $group->hasMember($visitor)}
			  	<br /><a title="Ich möchte die {if $group->title!='group'}{$group->title} {else}Organisation {/if}verlassen" href="{group_info_url leaveId=$group->id}" class="boxlink">Austreten</a>
			  {/if}
			{/dynamic}
	        </td>
	      </tr><tr>
	        <td style="vertical-align: top">Mitglieder:</td>
	        <td style="vertical-align: top">
	        	{foreach from=$group->getMembers() item=groupMember name=members}					
					{user_info_link user=$groupMember}{if !$smarty.foreach.members.last }{* display comma *},{/if}						
				{/foreach}
	        </td>
	      </tr><tr>  
	        <td style="vertical-align: top">davon sind Verwalter:</td>
	        <td style="vertical-align: top">
	        	{foreach from=$group->getAdmins() item=groupMember name=members}					
					{user_info_link user=$groupMember}{if !$smarty.foreach.members.last }{* display comma *},{/if}						
				{/foreach}
	        </td>
	      </tr>
	    </table>
	    <br class="clear" />
	  </div>
	</div>
    
    <div class="shadow" id="course-files"><div>
    <h3>Forenbeiträge in der Organisation</h3>
    <p>
    	  {assign var=forum value=$group->getForum()}
  		<a href="{forum_url forumId=$forum->id}" title="Zum Forum von {$group->name}" class="boxlink">zum Forum</a>
    </p>
    <ul>
	{foreach from=$threads item=thread}
		<li class="course-thread">{forum_link thread=$thread title="Zu dem Thread"}
			{forum_new thread=$thread}
			<p>
	        {assign var="lastEntry" value=$thread->getLastEntry()}
			{$thread->getTimeLastEntry()|unihelp_strftime:"NOTODAY"} von 
	            	{if $lastEntry->isAnonymous()}
	        			Anonymous
	                {elseif $lastEntry->isForGroup()}
	                    {group_info_link group=$lastEntry->getGroup()}
	        		{else}	
	        			{user_info_link user=$lastEntry->getAuthor()}
	        		{/if}
			(Seite: 
				{foreach from=$thread->getCounter() item=bc name=threadEntryCounter}
			    	{if $smarty.foreach.threadEntryCounter.last}
			   			{forum_link thread=$thread name="letzte" page=$bc})
			   		{else}
			    		{forum_link thread=$thread name=$bc" page=$bc}
			    	{/if}
			  	{/foreach}
			</p>
		</li>
	{foreachelse}
		<li>Kein Beiträge in dem Forum dieser Organistation vorhanden</li>		
	{/foreach}
	</ul>
    
    </div></div>
    <br class="clear" />
    
    {errorbox caption="Fehler beim Speichern"}    
  
  {* we need no dynamic here, because in this if-branch we are hopefully in non-cache mode *}
  {* TODO: rights: *}
  {if $editMode && ($visitor->hasGroupRight('GROUP_INFOPAGE_EDIT', $group->id) || $visitor->hasRight('GROUP_INFOPAGE_ADMIN'))}
    <div class="shadow"<div>
        <h3>Die Infopage von {$group->name} editieren</h3>
      
      {* include right box containing formatting options and smileys *}
      {include file="common/entry_options.tpl"}
      
          <div id="entryfield">
          {* ----------------------------------------------------- *}
          {* Formular mit if/else aufbauen --> Gaestebuch/Tagebuch *}
          {* ----------------------------------------------------- *}
                  <form enctype="multipart/form-data" action="{group_info_url editInfo=$group->id}#post" method="post">
                  <fieldset>
                  	<input name="groupId" value="{$group->id}" type="hidden" />
                  	<input name="method" value="editGroupInfopage" type="hidden" />
                                    
				  <label for="entrytext">Infopage-Text</label>
				  <textarea name="entryText" id="entrytext" rows="30" cols="60">{$infopage->getContentRaw()}</textarea><br />
				  {if $central_errors.missingFieldsObj.logo_picture}<span class="missing">{/if}
                  {*<label class="left" for="logo_picture">Organisations Logo</label>
				  <input name="logo_picture" id="logo_picture" size="30" type="file" />
				  {if $central_errors.missingFieldsObj.logo_picture}</span>{/if}
				  <br />
				  <label class="left" for="logo_delete">Logo l&ouml;schen</label>
				  <input name="logo_delete" id="logo_delete" type="checkbox" />
				  <br />*}
				  
            	<input name="enable_smileys" id="enable_smileys" {if $infopage == null || ($infopage != null && $infopage->isParseAsSmileys())}checked="checked" {/if} type="checkbox" />
				<label for="enable_smileys">Smileys aktivieren</label><br />

                 <input name="enable_formatcode" id="enable_formatcode" {if $infopage == null || ($infopage != null && $infopage->isParseAsFormatcode())}checked="checked" {/if}type="checkbox" />
				<label for="enable_formatcode">BBCode aktivieren</label><br />

                  <input type="submit" name="save" value="Abschicken"  />
                  <input type="submit" name="preview" value="Vorschau"  />
                  {* linap kicked nosave-button due to semantics is text and logo one unit/one transaction ?? *}
{*                  <input type="submit" name="nosave" value="Änderung verwerfen"  /> *}
                  </fieldset>		
                  </form>
          </div>
          <br class="clear" />
    </div></div>
    
  {/if} {* end editModus *}	
