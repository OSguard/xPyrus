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

*}{* $Id: view_entry.tpl 5829 2008-04-18 16:14:51Z schnueptus $ *}
{* $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/pm/view_entry.tpl $ *}
{*
 this variable are available
 	
 $pm PmEntryModel

*}
 
{*  {include file="banner.tpl"} *}
 
{*   <h2 id="pagename">Deine Nachricht: {$pm->getCaption()|default:"(kein Betreff)"|escape:"html"}</h2> *}
  
  <br class="clear" />
  <div id="tabNavigation" style="margin-bottom: -37px;">
  <ul style="margin: 0px;">
      <li>
        <a href="{pm_url new=true}" title="hier kannst Du eine neue PN verfassen" >neue Nachricht schreiben</a>
      </li>
      <li>
        <a {if !$pm->isSenderView()}class="active"{/if} href="{pm_url}">Posteingang ( {$visitor->getPMsUnread()} / {$visitor->getPMs()} )</a>
      </li>
      <li>
      	<a {if $pm->isSenderView()}class="active"{/if} href="{pm_url out=true}" title="Zum Postausgang">Postausgang ( {$visitor->getPMsSent()} )</a>
      </li>
      
  </ul>
  <br style="clear: both" />
  </div>  
  
  
  <div class="shadow"><div id="pmtext">
	
	<p>
	   {if !$pm->isSenderView() && !$systemPm}
	   	<a href="{pm_url quote=$pm}" title="als PN antworten">
	   		<img src="/images/icons/email.png" alt="als PN antworten">
	   	</a>
	   	<a href="{pm_url quote=$pm}all" title="allen als PN antworten">
	   		<img src="/images/icons/email_goback.gif" alt="allen als PN antworten">
	   	</a>
	   {/if} 
	   {if !$pm->isSenderView() && !$systemPm}<a href="{user_info_url user=$pm->getAuthor()}#postenanker" title="ins GB antworten"><img src="/images/icons/note_go.png" alt="ins GB antworten"></a>{/if} 
	   {if !$systemPm}<a href="{pm_url fwd=$pm}" title="als PN weiterleiten"><img src="/images/icons/email_go.png" alt="als PN weiterleiten"></a>{/if}
	   {if !$pm->isSenderView()}<a href="{pm_url del=$pm}" title="Nachricht löschen"><img src="/images/icons/email_delete.png" alt="Nachricht löschen"></a>
	   {else}
	    <a href="{pm_url dels=$pm}" title="Nachricht löschen"><img src="/images/icons/email_delete.png" alt="Nachricht löschen"></a>
	   {/if}
	   
	   {if $pm->getBeforePMId() || $pm->getNextPMId()}
	   <span id="PM-Nav">
	    {if $pm->getBeforePMId()}
	  	 <a href="{pm_url pmId=$pm->getBeforePMId()}" title="vorherige Nachricht lesen"><img src="/images/icons/pm_left.png" alt="vorherige Nachricht" /></a>
	  	{else}
	  	 <img src="/images/icons/pm_left_disable.png"/>
	    {/if}
	    {if $pm->getNextPMId()}
	  	 <a href="{pm_url pmId=$pm->getNextPMId() }" title="nächste Nachricht lesen"><img src="/images/icons/pm_right.png" alt="nächste Nachricht" /></a>
	  	{else}
	  	 <img src="/images/icons/pm_right_disable.png"/>
	    {/if}
	   </span>
	   {/if}
	</p>
	 
    <table summary="Verkehrsdaten zur Nachricht">
	<tr><td>Absender</td><td>{user_info_link user=$pm->getAuthor()}</td></tr>
	<tr><td>Empfänger</td>
		<td>{if $pm->isSenderView()}{$pm->getRecipientString()}{else}{user_info_link user=$pm->getReceiver()} ({$pm->getRecipientString()}){/if}</td></tr> 	
	<tr><td>Betreff</td>
		<td><strong>{$pm->getCaption()|default:"(kein Betreff)"}</strong></td></tr>
	<tr><td>Datum</td><td>{$pm->getTimeEntry()|unihelp_strftime}</td></tr>	
	</table>
	 

	<div id="pmcontent" class="entry">
		{$pm->getContentParsed()}
	</div>	
   </div></div>
   
