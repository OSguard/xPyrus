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

<div id="fussball">
<a id="topofpage" name="topofpage"></a>

<div class="shadow"><div>

<h3>EM-Tippspiel</h3>
<img src="/images/tippspiel/graphics/tippspiel.png" alt="Logo zum Tippspiel" style="float:right;"/>
      <p>Zur {$tournament->getDescription()} gibt es ein grandioses <a href="{sports_url soccerBet=1 tournament=$tournament}">Tippspiel</a> in UniHelp. Auf dieser Seite siehst Du die Rangliste der besten Tipper bisher. Sie freuen sich bestimmt &uuml;ber anerkennende Eintr&auml;ge in ihren G&auml;steb&uuml;chern ;-). </p>
      <br class="clear" />
</div></div>

<div class="shadow">

<div class="counter">
<b>Seite:</b> &nbsp;
	    {if $rank_page>1}
		<a href="{sports_url soccerBetRanking=1 page=1}">	&lt;	&lt;</a>&nbsp;&nbsp;
		<a href="{sports_url soccerBetRanking=1 page=$rank_page-1}">	&lt;</a>&nbsp;&nbsp;
		{/if}
		{foreach item=p from=$page_numbers}
		{if $p==$rank_page}
		  <b><font size="3">{$p}</font></b>
		{else}
		  <a href="{sports_url soccerBetRanking=1 page=$p}">{$p}</a>
		{/if}
		&nbsp;&nbsp;
		{/foreach}
		{if $rank_page < sizeof($page_numbers)}
		<a href="{sports_url soccerBetRanking=1 page=$rank_page+1}">	&gt;</a>&nbsp;&nbsp;
		<a href="{sports_url soccerBetRanking=1 page=$max_page}">	&gt;	&gt;</a>&nbsp;
		{/if}
		 <b> von {$max_page} Seiten</b>
</div>

<div class="nopadding">

<h3>Rangliste</h3>


  <table class="centralTable" cellspacing="0" cellpadding="0">
  <thead>
		    <tr>
			  <th id="rang">Rang</th>
			  <th id="user">User</th>
			  <th id="points">Punkte</th>
			</tr>
		  </thead>
		  <tbody>
          {foreach from=$ranking item="r"}
			  <tr {if $ranking[g].rank <= 3}class="top-rang"{/if}>
			  <td headers="rang">
       		    {if $r.rank == 1}
					{if $r.user->getGender() == 'f'}Fu&szlig;ballg&ouml;ttin
					{else}Fu&szlig;ballgott
					{/if}
				{elseif $r.rank == 2}Blutgr&auml;tsche
				{elseif $r.rank == 3}Libero
				{else}
			      {$r.rank}. 
			    {/if}
			  </td>
			  <td headers="user">{user_info_link user=$r.user} &nbsp; (<a href="{sports_url soccerBetUser=$r.user tournament=$tournament}">Tipps</a>) </td>
			  <td headers="points">{$r.points}</td>
			</tr>
			{/foreach}
		  </tbody>
		  </table>
</div></div>

<div class="shadow"><div>
<h3>Wer gewinnt die {$tournament->getName()}?</h3>
<p>{$countUserVoted} UniHelp-User, die am {$tournament->getName()}-Tippspiel teilnehmen, haben auch eine Mannschaft zum Sieger getippt.
 {assign var="mostTeam" value=$rankingList[0][0]}
 Die Mehrheit glaubt an die Mannschaft {$mostTeam->getName()}. Aber es gibt auch ein paar Au&szlig;enseiter. Die Tabelle zeigt die Anzahl der Tipps pro Mannschaft absolut und in Prozent.</p>
  <table class="centralTable" cellspacing="0" cellpadding="0"><caption>
  Wir fragten die UniHelp-User, wer die {$tournament->getName()} gewinnen wird, und so haben sie getippt.
  </caption>
  <thead>
		    <tr>
			  <th id="nation">Mannschaft</th>
			  <th id="numberoftipps">Tipps absolut</th>
			  <th id="numberoftippsr">in Prozent </th>
		    </tr>
		  </thead>
		  <tbody>
		  {foreach item="rankingListItem" from=$rankingList}
		  {assign var="team" value=$rankingListItem[0]}
		  <tr>
			  <td headers="nation"><img src="/images/tippspiel/{$team->getNameShort()|lower}3.png" alt="" />{$team->getName()}</td>
			  <td headers="numberoftipps">{$rankingListItem[1]}</td>
			  <td headers="numberoftippsr">{$rankingListItem[2]|truncate:5:""}</td>
		  </tr>
		  {/foreach}
		  </tbody>
		  </table>
</div></div>
</div>
