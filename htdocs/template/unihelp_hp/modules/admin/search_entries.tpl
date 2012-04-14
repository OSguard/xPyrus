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

*}<form action="{admin_url searchEntries=1}" method="post">
        {if $userinfo_filtererrors.filterauthor}
        <span style="color: red; font-weight:bold;">Ein Fehler ist aufgetreten</span>
        {/if}
    <table border="0" style="text-align: center;">
    <tr style="border: 0px">
    <td valign="top" align="center" style="text-align: center;" width="150">
        <label for="filterauthor" style="{if $filtererrors.filterauthor}color: red; font-weight:bold; {/if}text-align: center; width: 100%;">Autor(en):</label><br />
        <input type="text" id="filterauthor" name="filterauthor" size="20" value="{foreach from=$filter.author item=author}{$author->getUsername()} {/foreach}" style="width: 150px; margin-left: 10% !important;" /><br />
    <input name="search" type="submit" value="Suchen" class="filterbutton" />
    </td>
    <td width="350" valign="top" align="center" class="select">
        <label for="filterdateto_Day" style="text-align: center; width: 100%">Obere Datumsgrenze:</label><br />
        {html_select_date start_year="2000" time=$filter.entrydate.to|default:"--" field_order="DMY" reverse_years="true" year_empty="" month_empty="" day_empty="" prefix="filterdateto_" day_extra='id="filterdateto_Day"'}<br />

        <label for="filterdatefrom_Day" style="text-align: center; width: 100%">Untere Datumsgrenze:</label><br />
        {html_select_date start_year="2000" time=$filter.entrydate.from|default:"--" field_order="DMY" reverse_years="true" year_empty="" month_empty="" day_empty="" prefix="filterdatefrom_" day_extra='id="filterdatefrom_Day"'}<br />
    </td>
    <td valign="top" align="center">
    <label for="filtertext">Volltext:</label><input type="text" name="filtertext" id="filtertext" value="{$filter.text}" />
    </td>
    </tr>
    </table>
    </form>

{if $gbentries}
<h3>Gästebuch</h3>
<table id="userguestbook" summary="Die Tabelle enth&auml;lt das G&auml;stebuch-Eintr&auml;ge">
    <tbody id="guestbookbody">
    {foreach from=$gbentries item="guestbookentry" name="guestbook"}
        {assign var="guestbookentryAuthor" value=$guestbookentry->getAuthor()}
        {assign var="guestbookentryId" value=$guestbookentry->id}
        {include file="modules/userinfo/part_guestbook_entry.tpl" showRecipient=true showIP=true}
    {/foreach}
    {if $gbentries_remaining}
    <tr class="entrylast"><td colspan="2" style="text-align: center;">und {$gbentries_remaining} weitere Einträge</td></tr>
    {/if}
    </tbody>
</table>
{else}
Keine Gästebucheinträge gefunden
{/if}

{if $threadentries}
<h3>Forum</h3>
<table  class="centralTable" summary="Die Tabelle enth&auml;lt Foren-Beitr&auml;ge">
	<colgroup>
		<col width="150px" />
		<col />
    </colgroup>
    <tbody>
    {foreach from=$threadentries item=threadentry"}
        <tr class="thread-entry-body">
          <td style="background: #F7EDBE">
          	{assign var="author" value=$threadentry->getAuthor()}
				<p>
				{if $threadentry->isForGroup()}
				    Wurde gepostet als:{group_info_link group=$threadentry->getGroup()}
				    <br />
				{elseif $threadentry->isAnonymous()}
				    Wurde gepostet als:<strong>anonym</strong>
				    <br />
				{/if}
			    Eintrag verfasst von:
			    {user_info_link user=$author}
			    <br />
			    <a href="{user_info_url user=$author}" title="{$author->getUsername()}">
			    	<img src="{userpic_url tiny=$author}" alt="UserBild" style="margin-top: 3px;" />
			    </a>	
			    <br />
				IP: {$threadentry->getPostIP()}<br />
				Zeit: {$threadentry->getTimeLastUpdate()|unihelp_strftime}
				</p>
          </td>
          <td style="text-align: justify; padding: 0.3em;" class="entry">
            <h5 style="font-weight: bold; margin: .5em;"><a href="{forum_url entryId=$threadentry->id}">{$threadentry->getCaption()|default:"(Kein Titel)"}</a></h5>
            
            <div class="entrycontent">
            {$threadentry->getContentParsed()}            
            </div>
          </td>
        </tr>
    {/foreach}
    {if $threadentries_remaining}
    <tr class="entrylast"><td colspan="2" style="text-align: center;">und {$threadentries_remaining} weitere Einträge</td></tr>
    {/if}
    </tbody>
</table>
{else}
Keine Foreneinträge gefunden
{/if}