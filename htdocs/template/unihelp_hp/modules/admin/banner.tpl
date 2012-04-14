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

{* new banner administration *}
<div class="shadow box"><div><h3>Hilfe</h3>
<p>Auf dieser Seite k&ouml;nnen die Banner, wie sie im oberen Bereich der mittleren Spalte auf den meisten Seiten eingeblendet werden, verwaltet werden. Vorgesehen ist der Einsatz von <em>Fullsize Bannern</em> (468&thinsp;px &times; 68&thinsp;px) in den Formaten <abbr title="Portable Network Graphics">PNG</abbr>, JPG, Gif (interne und externe Dateien) sowie <abbr title="Flash">swf</abbr> (nur interne Dateien). Die Einbindung beliebigen Codes (bspw. Google-Ads) ist momentan nicht ohne weiteres m&ouml;glich.</p>
<p>Alle eingetragenen Banner, die <em>sichtbar</em> sind und deren <em>Rotationsfaktor gr&ouml;&szlig;er ist als null</em>, werden regelm&auml;&szlig;ig alle 24 Stunden (derzeit um 1.42&nbsp;Uhr) in eine Tabelle geschrieben, aus der zuf&auml;llig ein Banner f&uuml;r die Anzeige in einer Seite gew&auml;hlt wird. Die H&ouml;he des Rotationsfaktors bestimmt die H&auml;ufigkeit, mit der ein Banner in dieser Tabelle auftaucht: <em>Ein Banner wird dem Rotationsfaktor x wird x-mal in die Tabelle eingetragen</em>. Die Relation der Rotationsfaktoren der (sichtbaren) Banner untereinander bestimmt also die H&auml;ufigkeit der Einblendung. Die Anzahl der Eintr&auml;ge in der Tabelle entspricht der Summer der Rotationsfaktoren aller sichtbaren Banner.</p>
</div></div><!-- shadow -->
<div class="shadow"><div>
    {if !$bannerToEdit}
        <h3>##banner_newAdvertisements##</h3>
        <form enctype="multipart/form-data" action="/index.php?mod=i_am_god&dest=module&method=editBanner" method="post">
    {else}
        <h3>##banner_editAdvertisements##</h3>
        <form enctype="multipart/form-data" action="/index.php?mod=i_am_god&dest=module&method=editBanner" method="post">
            <input name="bannerId" value="{$bannerToEdit->id}" type="hidden" />
    {/if}
        <fieldset><legend>Bannereinstellungen</legend>
            <label for="banner_name">##banner_name##</label>
            <input type="text" name="banner_name" id="banner_name" {if $bannerToEdit}value="{$bannerToEdit->getName()}"{/if}/>(gib dem Banner einen eindeutigen Namen!)<br />
            <label for="eventStartDateDay">##banner_displayFrom##</label>
            {if $bannerToEdit}
                {html_select_date end_year=+1 field_order="DMY" time=$bannerToEdit->getStartDate() prefix=start day_extra="id='eventStartDateDay'" month_extra="id='eventStartDateMonth'" year_extra="id='eventStartDateYear'"}
                {* Zeit: {html_select_time display_seconds=false minute_interval=15 prefix=target} *}
            {else}
                {html_select_date end_year=+1 field_order="DMY" prefix=start day_extra="id='eventStartDateDay'" month_extra="id='eventStartDateMonth'" year_extra="id='eventStartDateYear'"}
            {/if}<br />
            <label for="eventEndDateDay">##banner_displayUntil##</label>
            {if $bannerToEdit}
                {html_select_date end_year=+1 field_order="DMY" time=$bannerToEdit->getEndDate() prefix=end day_extra="id='eventEndDateDay'" month_extra="id='eventEndDateMonth'" year_extra="id='eventEndDateYear'"}
                {* Zeit: {html_select_time display_seconds=false minute_interval=15 prefix=target} *}
            {else}
               	{html_select_date end_year=+1 field_order="DMY" prefix=end day_extra="id='eventEndDateDay'" month_extra="id='eventEndDateMonth'" year_extra="id='eventEndDateYear'"}
            {/if}<br />
            <input name="isVisible" id="isVisible" type="checkbox" {if $bannerToEdit != null && $bannerToEdit->isVisible() == false}{else}checked="checked"{/if} />
            <label for="isVisible">##visible##</label><br />
            <label for="banner_url">##banner_destURL##</label>
            <input name="banner_url" id="banner_url" type="text" {if $bannerToEdit}value="{$bannerToEdit->getDestURL()|escape:"html"}"{/if} /><br />
            <label for="banner_rot">##banner_rate##</label>
            <input name="banner_rot" size"5" type="text" {if $bannerToEdit}value="{$bannerToEdit->getRandomRate()}"{/if} />
            (Keine Einblendung wenn null. F&uuml;r alle Werte &uuml;ber null gilt vereinfacht: Je h&ouml;her der Wert, desto mehr Einblendungen.)
      </fieldset>
        <fieldset><legend>Bannerdatei</legend>
            {if !$bannerToEdit}
                <label for="file_attachment1">##banner_file##</label>
                <input name="file_attachment1" id="file_attachment1" maxlength="102400" size="30" type="file" /><br />
            {else}
         	    {if !$bannerToEdit->isFlash()}
                    <img src="{$bannerToEdit->getFilePath()}" alt=""/><br />
	            {else}
                    <object data="{$bannerToEdit->getFilePath()}" type="application/x-shockwave-flash" style="-moz-user-focus: ignore;" height="60" width="468">
                        <param value="transparent" name="wmode">
		                <param value="default" name="salign">
		                <param name="movie" value="{$bannerToEdit->getFilePath()}">
		            </object><br />
	            {/if}
                {if $bannerToEdit == null || $bannerToEdit->isExtern()}
                    <label for="banner_path">##banner_path##</label>
                    <input name="banner_path" size"30" type="text" {if $bannerToEdit && $bannerToEdit->isExtern()}value="{$bannerToEdit->getFilePath()|escape:"html"}"{/if} /><br />
                {/if}
            {/if}
        <p style="padding:0;margin:0;text-align:center;"><em>oder</em></p>
            <label for="banner_path">Externes Banner einbinden</label>
            <input name="banner_path" size="" type="text" />
            <!-- hier smarty-Kommentar einfügen, bis Implentierung erfolgt {* -->
            <!-- <br /><p style="padding:0;margin:0;text-align:center;"><em>oder</em></p>
            <label for="banner_code">Vollständigen Code definieren</label>
            <textarea></textarea> *} -->
        </fieldset>
        <fieldset><legend>Banner einstellen</legend>
            <input name="save" value="##submit##" type="submit" />
        </fieldset>
    </form>
    <br class="clear"/>
</div></div><!-- shadow -->
<div class="shadow"><div><h3>##banner_allAdvertisements##</h3>
<ul>
    <li><a href="#" title="Abgelaufene, gegenwärtige, zukünftige, sichtbare und nicht sichtbare">Zeige alle Banner</a></li>
    <li><a href="#" title="Nur zukünftige, gegenwärtige, sichtbare und nicht sichtbare">Zeige aktuelle und zuk&uuml;nftige Banner</a></li>
    <li><a href="#" title="Alle momentan rotierenden Banner">Zeige sichtbare Banner</a> (voreingestellt)</li>
</ul>
<table class="sortable tinyTable" width="100%"><caption>Sortierbare Auflistung der eingestellten Banner</caption>
    <colgroup span="8" />
    <thead>
        <tr>
            <th>Name</th>                        
            <th>##banner_displayedFrom##</th>                        
            <th>##banner_until##</th>
            <th>Sichtbar</th>
            <th>##banner_rate##</th>
            <th>##banner_destURL##</th>
            <th>Banner/Code</th>
            <th>Optionen</th>
        </tr>
    </thead>
    <tbody>
    {* Evalution of sum of banner wights in table as displayed *}
    {foreach from=$banners item=banner}
    	{assign var=foo value=$banner->getRandomRate()}
    	{assign var="sumofweights" value="`$sumofweights+$foo`"}
        <tr>
            <td>{$banner->getName()}</td>
            {if $banner->isVisible()}                        
                <td>{$banner->getStartDate()}</td>                        
                <td>{$banner->getEndDate()}</td>
                <td>Ja</td>
            {else}
                <td>n/a</td>                        
                <td>n/a</td>
                <td>Nein</td>
            {/if}
            <td>{$banner->getRandomRate()}</td>
            <td><a href="{$banner->getDestURL()}">Ziel-URL</a></td>
            {if !$banner->isFlash()}
	            <td><a href="{$banner->getFilePath()}" target="_blank" title="Klicken um den Banner in einem neuen Fenster zu sehen"><img src="{$banner->getFilePath()}" alt="{$banner->getName()}" width="150" /></a></td>
            {else}
                <td>
                {* do not show a flash banner resized
                    <object data="{$banner->getFilePath()}" type="application/x-shockwave-flash" style="-moz-user-focus: ignore;" width="150">
                    <param value="transparent" name="wmode" />
                    <param value="default" name="salign" />
                    <param name="movie" value="{$banner->getFilePath()}" />
                    </object>
                 *}{* show just a hyperlink instead *}
                 <a href="{$banner->getFilePath()}" title="Klicken um das Flash-Banner in einem neuen Fenster zu sehen" target="_blank" >{$banner->getFileName()}</a>
                </td>
		    {/if}
            <td><a href="/index.php?mod=i_am_god&dest=module&method=editBanner&amp;bannerId={$banner->id}&amp;showValue=true">##edit##</a> oder <a href="#">l&ouml;schen</a></td>
        </tr>
    {/foreach}
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" align="right">Summe der Rotationsfaktoren</td>
            <td>{$sumofweights}</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </tfoot>
</table>
</div></div><!-- shadow -->
{* end new banner administration *}

