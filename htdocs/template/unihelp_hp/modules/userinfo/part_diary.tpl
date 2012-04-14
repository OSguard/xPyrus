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

*}{* $Id: part_diary.tpl 5895 2008-05-03 15:38:20Z schnueptus $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/userinfo/part_diary.tpl $ *}

   <div class="shadow" id="userdiary">
 
{if $diary_available}   
   
    <div class="counter">{strip}
  Seitenauswahl: {*[*}
  {foreach from=$userinfo_diarycounter item=bc name=diarycounter}
    {if $bc==$userinfo_diarypage}
      <strong>
    {else}
          <a href="{user_info_url user=$userinfo_user diarypage=$bc}#diary_anchor">
    {/if}
      {$bc}
    {if $bc==$userinfo_diarypage}
      </strong>
    {else}
      </a>
    {/if}
    {if $smarty.foreach.diarycounter.last}
      {* save total number of guestbook pages *}
      {assign var="diary_page_number" value=$bc}
    {else}
      {* if not last loop, output whitespace to separate entries *}
      &nbsp;
    {/if}
  {/foreach}
  {*] von {$diary_page_number} Seiten*}
  {/strip}
  </div>
  
  <div id="userdiaryentries" class="nopadding">
{dynamic}
  {if $central_errors == null && $userinfo_editentry == null && !$isPreview}
	  	<a name="postdiary"></a>
  {/if}
{/dynamic}
  <h3><a id="diary_anchor" name="diary_anchor">Tagebuch</a></h3>
{dynamic}
{if $userinfo_permissions.diary_filter}
    <div class="entry ie6entrytop">
  {if $userinfo_diaryfilters_show}
   Eintragsfilter <a id="diaryFilterToggle" href="{user_info_url user=$userinfo_user diarypage=$userinfo_diarypage diaryfilter=0}#diary_anchor" title="Filter ausblenden"><img alt="ausblenden" src="/images/icons/delete.png" /></a><div id="diaryfilter">
  {else} {* else diary filter *}
   Eintragsfilter <a id="diaryFilterToggle" href="{user_info_url user=$userinfo_user diarypage=$userinfo_diarypage diaryfilter=1}#diary_anchor" title="Filter einblenden"><img alt="einblenden" src="/images/icons/add.png" /></a><div id="diaryfilter" style="display: none;">
  {/if} {* end diary filter *}
    <form action="{user_info_url user=$userinfo_user diarypage=$userinfo_diarypage}#diary_anchor" method="post">
    <table width="100%">
    <tr>
    <td width="400" valign="top" align="center" class="select">
          <label for="diaryfilterdateto_Day" style="width: 100%; text-align: center;">Obere Datumsgrenze:</label><br />
          {html_select_date start_year="2000" time=$userinfo_diaryfilters.entrydate.to|escape:"html"|default:"--" field_order="DMY" reverse_years="true" year_empty="" month_empty="" day_empty="" prefix="diaryfilterdateto_" day_extra='id="diaryfilterdateto_Day"'}<br />

          <label for="diaryfilterdatefrom_Day" style="width: 100%; text-align: center;">Untere Datumsgrenze:</label><br />
          {html_select_date start_year="2000" time=$userinfo_diaryfilters.entrydate.from|default:"--"|escape:"html" field_order="DMY" reverse_years="true" year_empty="" month_empty="" day_empty="" prefix="diaryfilterdatefrom_" day_extra='id="diaryfilterdatefrom_Day"'}<br />
    </td>
    <td valign="top" align="center">
        <input name="diary_submittype" type="submit" value="Filtern" class="filterbutton" /><br />
        <input name="diary_submittype_reset" type="submit" value="Filter-Reset" class="filterbutton"/>
    </td>
    </tr>
    </table>
    </form>
    </div>
    </div>
{/if}
{/dynamic}

{dynamic}
  {foreach from=$userinfo_diary item="diaryentry" name="diary"}
    {$diaryentry->getContent()}
  {foreachelse}
    {$userinfo_user->getUsername()} hat noch keine Tagebucheintr&auml;ge verfasst.
  {/foreach}
{/dynamic}
{if $userinfo_user->hasBlog()}
<div class="entry entrylast" style="text-align: center; font-size: 110%;">
Besuche auch <a href="{blog_url owner=$userinfo_user}">{$userinfo_user->getUsername()|genitiv} Blog auf [[local.local.project_name]].</a>
</div>
{/if}
  </div>
  
      <div class="counter counterbottom">{strip}
  Tagebuch Seitenauswahl: {*[*}
  {foreach from=$userinfo_diarycounter item=bc name=diarycounter}
    {if $bc==$userinfo_diarypage}
      <strong>
    {else}
          <a href="{user_info_url user=$userinfo_user diarypage=$bc}#diary_anchor">
    {/if}
      {$bc}
    {if $bc==$userinfo_diarypage}
      </strong>
    {else}
      </a>
    {/if}
    {if $smarty.foreach.diarycounter.last}
      {* save total number of guestbook pages *}
      {assign var="diary_page_number" value=$bc}
    {else}
      {* if not last loop, output whitespace to separate entries *}
      &nbsp;
    {/if}
  {/foreach}
  {*] von {$diary_page_number} Seiten*}
  {/strip}
  </div>
 
{else} {* if diary not available *}
  <div>
  	Das interne Tagebuch steht Dir leider nicht zur Verf&uuml;gung.
  	
  {if $userinfo_user->hasBlog()}
<div class="entry entrylast" style="text-align: center; font-size: 110%;"><br />
<a href="{blog_url owner=$userinfo_user}">Zu {$userinfo_user->getUsername()|genitiv} öffentlichem Blog auf [[local.local.project_name]].</a>
</div>
{/if}	
  </div>
{/if} 
  
  </div><!-- /diary -->
  
