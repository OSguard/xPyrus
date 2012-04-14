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

*}{literal}
<style type="text/css">
<!--
#fussball h4 {
  font-size: 1.6em;
}
#fussball h5 {
  font-size: 1.2em;
  margin-bottom: 5px;
}
#fussball table.heading {
  border: 0;
  margin: 0;
}
#fussball table.heading td{
  padding-left: 1.5em;
  padding-right: 1.5em;
}
#fussball table.heading td a {
  color: #FFFFFF;
}
#fussball table.content {
  border: 2px solid #2D4669;
  background-color: #F4FFFF;
  width: 100%;
  margin-bottom: 20px;
  padding: 10px;
}
#fussball table.content td {
  vertical-align: top;
  line-height: 15pt;
  font-family: Arial;
  font-size: 9pt;
  padding-right: 2px;
  padding-left: 2px;
}
#fussball table.content table {
  border-top: 1px solid #000000;
  border-bottom: 1px solid #000000;
  width: 80%;
  margin: 0 auto;
}
#fussball table.content table tfoot td {
  text-align: right;
}
#fussball table.content table td {
  text-align: left;
  padding: 1px 3px;
}
#fussball table.content table td[headers=points] {text-align: center;}
#fussball table.content table tr.top-rang td {
  font-weight: bold;
}
#fussball table.content table td.right {
  text-align: right;
  width: 12em;
}
#fussball table.content table td.right:after {
  content: " :";
}
#fussball ul {
  max-width: 43em;
  margin-left: 3em;
}
#fussball table.content table thead th {
  border-bottom: 1px solid #2D4669;
  padding: 4px;
  text-align: left;
}
#fussball table.content table thead th[id=points] {text-align: center;}
#fussball table.content td[headers=numberoftipps], #fussball table.content td[headers=numberoftippsr] {text-align: right; padding-right: 3em;}
#fussball a.soccerolink {width: 40px; height: 50px; display:block;}
#fussball a.soccerolink img.socceroimgsmall {
  width: 40px;
  height: 50px;
  border: 0;
}
#fussball a.soccerolink img.socceroimgbig {
  display: none;
  border: 0;
  z-index: 10;
  position: absolute;
}
#fussball a.soccerolink:hover img.socceroimgbig {
  display: block;
}
#fussball a.soccerolink:hover img.socceroimgsmall {
  display: none;
}
-->
  </style>
  {/literal} </p>
<div id="fussball">
<a id="topofpage" name="topofpage"></a>
<h4 align="center">Unihelp &ndash; Fu&szlig;ball-Tippspiel &ndash; Rangliste</h4>
<table class="heading" id="table_rahmen" cellspacing="0" cellpadding="2">
  <tr><td><span id="table_ueberschrift">WM-Tippspiel</span></td></tr>
</table>
<table class="content" id="table_rahmen" cellspacing="0" cellpadding="10">
  <tr style="border: 0;">
    <td><img src="wm2006/images/posting.gif" alt="Logo zum Tippspiel" style="float:right;"/>
      <p>Zur Fu&szlig;ball-Weltmeisterschaft gibt es ein grandioses <a href="wm2006.php">Tippspiel</a> in UniHelp. Auf dieser Seite siehst Du die Rangliste der besten Tipper bisher. Sie freuen sich bestimmt &uuml;ber anerkennende Eintr&auml;ge in ihren G&auml;steb&uuml;chern ;-). </p></td>
  </tr>
</table>

<table class="heading" id="table_rahmen" cellspacing="0" cellpadding="2">
 <tr>
 	<td>&nbsp;&nbsp;<span id="table_ueberschrift">Rangliste</span>&nbsp;&nbsp;</td>

	<td align=right><span id="table_ueberschrift"><b>Seite: [</b> &nbsp;
	    {if $rank_page>1}
		<a href="wm2006-rank.php?page=1"><img src="le2.gif" border="0" title="Erste Seite" alt="1"></a>&nbsp;&nbsp;
		<a href="wm2006-rank.php?page={$rank_page-1}"><img src="l2.gif" border="0" title="1 Seite zur&uuml;ck" alt="zur&uuml;"></a>&nbsp;&nbsp;
		{/if}
		{foreach item=p from=$page_numbers}
		{if $p==$rank_page}
		  <b><font size="3">{$p}</font></b>
		{else}
		  <a href="wm2006-rank.php?page={$p}">{$p}</a>
		{/if}
		&nbsp;&nbsp;
		{/foreach}
		{if $rank_page<$max_page}
		<a href="wm2006-rank.php?page={$rank_page+1}"><img src="r2.gif" border="0" title="1 Seite weiter" alt="weiter"></a>&nbsp;&nbsp;
		<a href="wm2006-rank.php?page={$max_page}"><img src="re2.gif" border="0" title="Letzte Seite" alt="Letzte Seite"></a>&nbsp;
		{/if}
		 <b>] von {$max_page} Seiten</b></span></td>

 </tr>
</table>
<table class="content" id="table_rahmen" cellspacing="0" cellpadding="0">
<tr><td>
  <table cellspacing="0" cellpadding="0">
  <thead>
		    <tr>
			  <th id="rang">Rang</th>
			  <th id="user">User</th>
			  <th id="points">Punkte</th>
			</tr>
		  </thead>
		  <tbody>
		  {section name=g loop=$ranking}
		    <tr {if $ranking[g].rank <= 3}class="top-rang"{/if}>
			  <td headers="rang">
       		    {if $ranking[g].rank == 1}
					{if $ranking[g].username|strtolower == engel
					 or $ranking[g].username|strtolower == bine85
					 or $ranking[g].username|strtolower == jeany
					 or $ranking[g].username|strtolower == berlinerschnauze
					 or $ranking[g].username|strtolower == quietscheentchen
					 or $ranking[g].username|strtolower == mellimaus
					 or $ranking[g].username|strtolower == amicelli}
					Fu&szlig;ballg&ouml;ttin
					{else}Fu&szlig;ballgott
					{/if}
					{* if $losmintos *}
					<a href="http://www.unihelp.de/user_info.php?username={$ranking[g].username|escape:"url"}" class="soccerolink"><img src="http://www.unihelp.de/upload/images/user/big/{$ranking[g].username|escape:"url"}" class="socceroimgsmall" /><img src="http://www.unihelp.de/upload/images/user/big/{$ranking[g].username|escape:"url"}" class="socceroimgbig" /></a>
					{* /if *}
				{elseif $ranking[g].rank == 2}Blutgr&auml;tsche
				{elseif $ranking[g].rank == 3}Libero
				{else}
			      {$ranking[g].rank}. 
			    {/if}
			  </td>
			  <td headers="user"><a href="/user_info.php?username={$ranking[g].username|escape:"url"}">{$ranking[g].username|escape:"htmlall"}</a> &nbsp; (<a href="/wm2006.php?show_user={$ranking[g].username|escape:"url"}">Tipps</a>) </td>
			  <td headers="points">{$ranking[g].points_ges}</td>
			</tr>
			{/section}
		  </tbody>
		  </table>
</td></tr>
</table>
<table class="heading" id="table_rahmen" cellspacing="0" cellpadding="2">
 <tr><td>&nbsp;&nbsp;<span id="table_ueberschrift">Wer wird Weltmeister?</span>&nbsp;&nbsp;</td></tr>
</table>
<table class="content" id="table_rahmen" cellspacing="0" cellpadding="0">
<tr><td><p>803 UniHelp-User, die am WM-Tippspiel teilnehmen, haben auch eine Mannschaft zum Weltmeister getippt. Die Mehrheit glaubt an die deutsche Mannschaft, auch der Favorit Brasilien behauptet sich. Aber es gibt auch ein paar Au&szlig;enseiter. Die Tabelle zeigt die Anzahl der Tipps por Mannschaft absolut und in Prozent.</p>
  <table cellspacing="0" cellpadding="0"><caption>Wir fragten die UniHelp-User, wer Weltmeister wird und so haben sie getippt.</caption>
  <thead>
		    <tr>
			  <th id="nation">Mannschaft/Land</th>
			  <th id="numberoftipps">WM-Tipps absolut</th>
			  <th id="numberoftippsr">in Prozent </th>
		    </tr>
		  </thead>
		  <tbody>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/ger3.gif" alt="" />Deutschland</td>
			  <td headers="numberoftipps">290</td>
			  <td headers="numberoftippsr">36,07</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/bra2.gif" alt="" />Brasilien </td>
			  <td headers="numberoftipps">212</td>
			  <td headers="numberoftippsr">26,37</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/arg2.gif" alt="" />Argentinien </td>
			  <td headers="numberoftipps">57</td>
			  <td headers="numberoftippsr">7,09</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/ita2.gif" alt="" />Italien</td>
			  <td headers="numberoftipps">52</td>
			  <td headers="numberoftippsr">6,47</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/eng2.gif" alt="" />England</td>
			  <td headers="numberoftipps">49</td>
			  <td headers="numberoftippsr">6,09</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/ned2.gif" alt="" />Niederlande</td>
			  <td headers="numberoftipps">39</td>
			  <td headers="numberoftippsr">4,85</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/fra2.gif" alt="" />Frankreich</td>
			  <td headers="numberoftipps">25</td>
			  <td headers="numberoftippsr">3,11</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/por2.gif" alt="" />Portugal</td>
			  <td headers="numberoftipps">21</td>
			  <td headers="numberoftippsr">2,61</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/cze2.gif" alt="" />Tschechische Republik</td>
			  <td headers="numberoftipps">19</td>
			  <td headers="numberoftippsr">2,36</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/swe2.gif" alt="" />Schweden</td>
			  <td headers="numberoftipps">9</td>
			  <td headers="numberoftippsr">1,12</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/civ2.gif" alt="" />Elfenbeink&uuml;ste</td>
			  <td headers="numberoftipps">7</td>
			  <td headers="numberoftippsr">0,87</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/mex2.gif" alt="" />Mexiko</td>
			  <td headers="numberoftipps">5</td>
			  <td headers="numberoftippsr">0,62</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/esp2.gif" alt="" />Spanien</td>
			  <td headers="numberoftipps">5</td>
			  <td headers="numberoftippsr">0,62</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/tri2.gif" alt="" />Trinida und Tobago</td>
			  <td headers="numberoftipps">3</td>
			  <td headers="numberoftippsr">0,37</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/jpn2.gif" alt="" />Japan</td>
			  <td headers="numberoftipps">3</td>
			  <td headers="numberoftippsr">0,37</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/pol2.gif" alt="" />Polen</td>
			  <td headers="numberoftipps">2</td>
			  <td headers="numberoftippsr">0,25</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/ksa2.gif" alt="" />Saudiarabien</td>
			  <td headers="numberoftipps">1</td>
			  <td headers="numberoftippsr">0,12</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/tog2.gif" alt="" />Togo</td>
			  <td headers="numberoftipps">1</td>
			  <td headers="numberoftippsr">0,12</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/ang2.gif" alt="" />Angola</td>
			  <td headers="numberoftipps">1</td>
			  <td headers="numberoftippsr">0,12</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/sui2.gif" alt="" />Schweiz</td>
			  <td headers="numberoftipps">1</td>
			  <td headers="numberoftippsr">0,12</td>
		  </tr>
		  <tr>
			  <td headers="nation"><img src="wm2006/images/aus2.gif" alt="" />Australien</td>
			  <td headers="numberoftipps">1</td>
			  <td headers="numberoftippsr">0,12</td>
		  </tr>
		  <tr>
		    <td headers="nation"><img src="wm2006/images/gha2.gif" alt="" />Ghana</td>
		    <td headers="numberoftipps">1</td>
		    <td headers="numberoftippsr">0,12</td>
		    </tr>
		  </tbody>
		  </table>
</td></tr>
</table>
</div>