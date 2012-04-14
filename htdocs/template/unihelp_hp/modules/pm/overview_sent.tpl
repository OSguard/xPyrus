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

*}{* $Id: overview_sent.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{* $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/pm/overview_sent.tpl $ *}
{*
 this variable are available
 	
 $pms   array of PmEntryModel - all Models we want to display

*}

{* {include file="banner.tpl"} *}

{* <h2 id="pagename">Nachrichten&uuml;bersicht &ndash; gesendete Nachrichten</h2> *}
  <br class="clear" />
  <div id="tabNavigation" style="margin-bottom: -37px;">
  <ul style="margin: 0px;">
      <li>
        <a href="{pm_url new=true}" title="hier kannst Du eine neue PN verfassen" >neue Nachricht schreiben</a>
      </li>
      <li>
        <a href="{pm_url}">Posteingang ( {$visitor->getPMsUnread()} / {$visitor->getPMs()} )</a>
      </li>
      <li>
      	<a class="active">Postausgang ( {$visitor->getPMsSent()} )</a>
      </li>
  </ul>
  <br style="clear: both" />
  </div>
  
<form method="post" action="{pm_url out=true page=$page}" >   
  
<div class="shadow">
<div class="nopadding">
<table class="centralTable" summary="Deine gesendeten Nachrichten">
<thead>
    <tr>
    <th></th>
    <th id="pm-topic">Betreff</th>
    <th id="pm-sender">Empfänger</th>
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
            <a href="{pm_url pm=$pm sent=1}">
                {$pm->getCaption()|default:"(kein Betreff)"}
            </a>
            {if $pm->isUnread()}
                </strong>
            {/if}
        </td>
        <td headers="pm-sender">{$pm->getRecipientString()}</td>
        <td headers="pm-date">
            {$pm->getTimeEntry()|unihelp_strftime}
        </td>
        <td class="center" headers="pm-action">
        <a href="{pm_url fwd=$pm}" title="als PN weiterleiten"><img src="/images/icons/email_go.png" alt="als PN weiterleiten"></a>
        <a href="{pm_url dels=$pm}" title="Nachricht löschen"><img src="/images/icons/email_delete.png" alt="Nachricht löschen"></a>
        </td>
    </tr>
    {foreachelse}
    <tr>
    	<td colspan="5" class="emptyTable">
    		Der Postausgang ist leer.
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
	        <a href="{pm_url out=true page=$bc}">
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
	<input type="submit" name="del" value="löschen" />
</div>
</form>
