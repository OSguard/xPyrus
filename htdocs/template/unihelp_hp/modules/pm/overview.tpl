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

*}{* $Id: overview.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{* $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/pm/overview.tpl $ *}
{*
 these variables are available
 	
 $pms   array of PmEntryModel - all models we want to display

*}

<br class="clear" />
<div id="tabNavigation" style="margin-bottom: -37px;">
  <ul style="margin: 0px;">
      <li>
        <a href="{pm_url new=true}" title="hier kannst Du eine neue PN verfassen" >neue Nachricht schreiben</a>
      </li>
      <li>
        <a class="active">Posteingang ( {$visitor->getPMsUnread()} / {$visitor->getPMs()} )</a>
      </li>
      <li>
      	<a href="{pm_url out=true}" title="Zum Postausgang">Postausgang ( {$visitor->getPMsSent()} )</a>
      </li>
  </ul>
  <br class="clear" />
</div>
  
<form method="post" action="{pm_url page=$page}" >  
  
<div class="shadow">
<div class="nopadding">

<table class="centralTable clear" summary="Deine Nachrichten">
<thead>
    <tr>
    <th></th>
    <th id="pm-topic">Betreff</th>
    <th id="pm-sender">Absender</th>
    <th id="pm-date">Datum</th>
    <th id="pm-action"></th>
    </tr>
</thead>
<tbody>
    {foreach from=$pms item=pm}
    <tr>
    	<td class="nopadding">
    		<input type="checkbox" name="pmSelected[]" value="{$pm->id}" />
    	</td>
        <td headers="pm-topic">
            {if $pm->isUnread()}
                <strong> 
            {/if}
            <a href="{pm_url pm=$pm}">
                {$pm->getCaption()|default:"(kein Betreff)"}
            </a>
            {if $pm->isUnread()}
                </strong>
            {/if}
        </td>
        <td headers="pm-sender">{user_info_link user=$pm->getAuthor()}</td>
        <td headers="pm-date">
            {$pm->getTimeEntry()|unihelp_strftime}
        </td>
        <td class="center" headers="pm-action">
        {if !$pm->isSystemPM()}
        <a href="{pm_url quote=$pm}" title="dem Autor als PM antworten"><img src="/images/icons/email.png" alt="dem Autor als PN antworten"></a> 
        <a href="{pm_url fwd=$pm}" title="als PM weiterleiten"><img src="/images/icons/email_go.png" alt="als PN weiterleiten"></a>
        <a href="{user_info_url user=$pm->getAuthor()}#postenanker" title="dem Autor ins GB antworten"><img src="/images/icons/note_go.png" alt="dem Autor ins GB antworten"></a> 
        {/if}
        <a href="{pm_url del=$pm}" title="Nachricht l&ouml;schen"><img src="/images/icons/email_delete.png" alt="Nachricht l&ouml;schen"></a>
        </td>
    </tr>
    {foreachelse}
    <tr>
    	<td colspan="5" class="emptyTable">
    		Der Posteingang ist leer.
    	</td>
    </tr>
{/foreach}
</tbody>
</table>

</div>
<div class="counter counterbottom">
	    Seiten: 
	    {foreach from=$pm_counter item=bc name=pmcounter}
	    {if $bc==$page}
	        <strong>
	    {else}
	        <a href="{pm_url page=$bc}">
	    {/if}
	        {$bc}
	    {if $bc==$page}
	        </strong>
	    {else}
	        </a>
	    {/if}
	    {if !$smarty.foreach.pmcounter.last}
	        {* if not last loop, output whitespace to separate entries *}
	        &nbsp;
	    {/if}
	    {/foreach}
</div>
</div>
<div class="quickbutton">
	<input type="submit" name="read" value="gelesen" />
	<input type="submit" name="unread" value="nicht gelesen" />
	<input type="submit" name="del" value="löschen" />
</div>
</form>
