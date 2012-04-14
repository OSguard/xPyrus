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

*}<table class="clear" id="guestbookStatsPoints" summary="Die Tabelle enth&auml;lt eine &Uuml;bersicht &uuml;ber die Top5-Punktespender im G&auml;stebuch von User {$userinfo.username|escape:"html"}">
  <caption>Top10-Punktespender</caption>
  <thead>
    <tr>
  <th id="col-position">Platz</th>
  <th id="col-username">Username</th>
  <th id="col-number">Eintr&auml;ge</th>
  <th id="col-points">Punkte</th>
  <th id="col-points-plus">Punkte <em>+</em></th>
  <th id="col-points-neutral">Punkte <em>o</em></th>
  <th id="col-points-minus">Punkte <em>-</em></th>
  <th id="col-points-efficiency">Punkteeffizienz</th>
    </tr>
  </thead>
  <tbody>
  {counter start=0 print=false}
  {section name="gb" loop=$guestbookstats}
    <tr>
      <td headers="col-position">{counter}.</td>
      <td headers="col-username">{user_info_link user=$guestbookstats[gb].author}</td>          
      <td headers="col-number">{$guestbookstats[gb].number|default:0}</td>
      <td headers="col-points">{$guestbookstats[gb].points|default:0}</td>
      <td headers="col-points-plus">{$guestbookstats[gb].weighting2|default:0}</td>
      <td headers="col-points-neutral">{$guestbookstats[gb].weighting1|default:0}</td>
      <td headers="col-points-minus">{$guestbookstats[gb].weighting0|default:0}</td>
      <td headers="col-points-efficiency">{$guestbookstats[gb].efficiency|default:"?"}</td>
    </tr>    
  {/section}
 </tbody>
</table>
   {*
    nice pie-chart visualization to come ;)
    *}
