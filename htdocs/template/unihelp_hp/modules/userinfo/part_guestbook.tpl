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

*}{* $Id: part_guestbook.tpl 5807 2008-04-12 21:23:22Z trehn $
     $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/userinfo/part_guestbook.tpl $ *}

<div class="shadow" style="margin-bottom: 50px">

{if $guestbook_available}

<div class="counter">{strip}
  Seitenauswahl: {*[*}
  {foreach from=$userinfo_guestbookcounter item=bc name=guestbookcounter}
    {if $bc==$userinfo_guestbookpage}
      <strong>
    {else}
      <a href="{user_info_url user=$userinfo_user gbpage=$bc}#guestbook_anchor">
    {/if}
      {$bc}
    {if $bc==$userinfo_guestbookpage}
      </strong>
    {else}
      </a>
    {/if}
    {if $smarty.foreach.guestbookcounter.last}
      {* save total number of guestbook pages *}
      {assign var="guestbook_page_number" value=$bc}
    {else}
      {* if not last loop, output whitespace to separate entries *}
      &nbsp;
    {/if}
  {/foreach}
  {*] von {$guestbook_page_number} Seiten*}
  {/strip}
  </div>
<div class="nopadding">
{dynamic}
	  {if $central_errors == null && $userinfo_editentry == null && !$isPreview}
	  	<a name="post"></a>
	  {/if}
{/dynamic}
<h3><a id="guestbook_anchor" name="guestbook_anchor">G&auml;stebuch</a> {if $userinfo_guestbookfilters}<em class="adminNote">gefiltert</em>{/if}</h3>
<!--[if IE 7]>
<br style="clear: both" />
<![endif]-->
<table id="userguestbook" summary="Die Tabelle enth&auml;lt das G&auml;stebuch von User {$userinfo_user->getUsername()}">
    <tbody id="guestbookbody">

{dynamic}
      {if $userinfo_permissions.guestbook_filter}
		<tr>
        {if $userinfo_guestbookfilters_show}
    		<td class="entry-value"><span class="info">Eintragsfilter</span><a id="guestbookFilterToggle" href="{user_info_url user=$userinfo_user gbpage=$userinfo_guestbookpage gbfilter=0}#guestbook_anchor" title="Filter ausblenden"><img alt="ausblenden" src="/images/icons/delete.png" /></a></td>
    		<td class="entry">
            <div id="guestbookfilter">
        {else} {* else gb filter show *}
            <td class="entry-value"><span class="info">Eintragsfilter</span><a id="guestbookFilterToggle" href="{user_info_url user=$userinfo_user gbpage=$userinfo_guestbookpage gbfilter=1}#guestbook_anchor" title="Filter einblenden"><img alt="einblenden" src="/images/icons/add.png" /></a></td>
    		<td class="entry">
            <div id="guestbookfilter" style="display: none">
        {/if} {* end gb filter show *}
		  <form action="{user_info_url user=$userinfo_user gbpage=$userinfo_guestbookpage }#guestbook_anchor" method="post">
		      {if $userinfo_filtererrors.gbfilterauthor}
		      <span style="color: red; font-weight:bold;">Ein Fehler ist aufgetreten</span>
		      {/if}
			<table border="0" style="text-align: center;">
			<tr style="border: 0px">
			<td valign="top" align="center" style="text-align: center;" width="150">
		      <label for="gbfilterauthor" style="{if $userinfo_filtererrors.gbfilterauthor}color: red; font-weight:bold; {/if}text-align: center; width: 100%;">Autor(en):</label><br />
		      <input type="text" id="gbfilterauthor" name="gbfilterauthor" size="20" value="{foreach from=$userinfo_guestbookfilters.author item=author}{$author->getUsername()} {/foreach}" style="width: 150px; margin-left: 10% !important;" /><br />
		    <input name="gb_submittype" type="submit" value="Filtern" class="filterbutton" />
		    <input name="gb_submittype_reset" type="submit" value="Filter-Reset" class="filterbutton" />
			</td>
			<td width="350" valign="top" align="center" class="select">
		      <label for="gbfilterdateto_Day" style="text-align: center; width: 100%">Obere Datumsgrenze:</label><br />
		      {html_select_date start_year="2000" time=$userinfo_guestbookfilters.entrydate.to|default:"--" field_order="DMY" reverse_years="true" year_empty="" month_empty="" day_empty="" prefix="gbfilterdateto_" day_extra='id="gbfilterdateto_Day"'}<br />

              <label for="gbfilterdatefrom_Day" style="text-align: center; width: 100%">Untere Datumsgrenze:</label><br />
		      {html_select_date start_year="2000" time=$userinfo_guestbookfilters.entrydate.from|default:"--" field_order="DMY" reverse_years="true" year_empty="" month_empty="" day_empty="" prefix="gbfilterdatefrom_" day_extra='id="gbfilterdatefrom_Day"'}<br />		      
			</td>
		    </tr>
		    </table>
		  </form>
          </div>
		 </td>
		 </tr>
	  {/if}
{/dynamic}

{dynamic}
    {foreach from=$userinfo_guestbook item=guestbookentry name="guestbook"}
		{$guestbookentry->getContent()}
	{foreachelse}
		{$userinfo_user->getUsername()} hat noch keine Gästebucheinträge.
    {/foreach}
{/dynamic}

    </tbody>
</table>
</div>

<div class="counter counterbottom">{strip}
  Seitenauswahl G&auml;stebuch: {*[*}
  {foreach from=$userinfo_guestbookcounter item=bc name=guestbookcounter}
    {if $bc==$userinfo_guestbookpage}
      <strong>
    {else}
      <a href="{user_info_url user=$userinfo_user gbpage=$bc}#guestbook_anchor">
    {/if}
      {$bc}
    {if $bc==$userinfo_guestbookpage}
      </strong>
    {else}
      </a>
    {/if}
    {if $smarty.foreach.guestbookcounter.last}
      {* save total number of guestbook pages *}
      {assign var="guestbook_page_number" value=$bc}
    {else}
      {* if not last loop, output whitespace to separate entries *}
      &nbsp;
    {/if}
  {/foreach}
  {*] von {$guestbook_page_number} Seiten*}
  {/strip}
  </div>
  
{else} {* if guestbook not available *}
  <div>Das G&auml;stebuch steht Dir leider nicht zur Verf&uuml;gung.</div>
{/if}
</div>
