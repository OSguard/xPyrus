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

{literal}
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
}
#fussball table.content table tfoot td {
  text-align: right;
}
#fussball table.content table td {
  text-align: center;
  padding-left: 3px;
  padding-right: 3px;
}
#fussball table.content table td.left {
  text-align: left;
  padding-left: 1px;
  width: 12em;
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
#fussball .tippgame input{
	float: none;
}  

-->
  </style>
  {/literal}
  <div id="fussball">
 
 <div class="shadow"><div>
 
<form method="post">
  <h3>Neues Spiel eintragen:</h3>

  <table id="table_rahmen" cellspacing="0" cellpadding="10">
	<tr>
      <td align="right">Land:</td>
      <td>
        <select name="team_1">
           {html_options options=$teams}
        </select>
      </td>
      <td align="right">gegen Land:</td>
      <td>
        <select name="team_2">
           {html_options options=$teams}
        </select>
      </td>
    </tr>
    <tr>
      <td align="right">in Stadion:</td>
      <td>
        <select name="stadion">
           {html_options options=$stadiums}
        </select>
      </td>
      <td align="right">Spieltyp:</td>
      <td>
        <select name="game_type">
           {html_options options=$game_types}
        </select>
      </td>
    </tr>
    <tr>
      <td align="right">Datum:</td>
      <td>
        {html_select_date prefix="startTime" start_year="2008" end_year="2010"}
      </td>
      <td align="right">Zeit:</td>
      <td>
        {html_select_time prefix="startTime" display_seconds=false display_meridian=false minute_interval=5}
      </td>
    </tr>
    <tr>
      <td align="left" colspan="2">
        <input type="submit" name="add-game" value="Spiel eintragen" />
      </td>
    </tr>
  </table>
</form>

</div></div>

<div class="shadow"><div>

<h3>Spiele:</h3>

  {section name=gamesloop loop=$wm_games}

      <h5>Gruppe {$wm_games[gamesloop].group}</h5>
      <form method="post">
          <table class="centralTable" border="0">
            <thead>
              <tr>
                <!--<th>Spiel-Nr.</th>-->
                <th>Datum, Zeit</th>
                <th colspan="2">Begegnung</th>
                <th>Ergebnis</th>
				<th>Zusatz</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <td colspan="7">
                  <input type="submit" name="save-result" value="Ergebnis speichern" title="Einmal dr&uuml;cken!" />
                </td>
              </tr>
            </tfoot>
            <tbody>
              {foreach from=$wm_games[gamesloop].data item="game"}
                {assign var="id" value=$game->id}
                {assign var="team1" value=$game->getTeam1()}
                {assign var="team2" value=$game->getTeam2()}
                <tr>
                  <td>{$game->getStartTime()|unihelp_strftime} </td>
                  <td class="right">{$team1->getName()}</td>
                  <td class="left">{$team2->getName()}</td>

					<td class="tippgame"><input size="1" type="text" name="game{$id}_team1" value="{$game->getGoalsTeam1()}" />&nbsp;:&nbsp;<input type="text" size="1" name="game{$id}_team2" value="{$game->getGoalsTeam2()}" /></td>
					<td><select name="game{$id}_result_type" size="1">
						<option {if $game->getAdditionalInfo()==""}selected="selected"{/if} value="">keine</option>
						<option {if $game->getAdditionalInfo()=="n.V."}selected="selected"{/if} value="n.V.">n.V.</option>
						<option {if $game->getAdditionalInfo()=="n.E."}selected="selected"{/if} value="n.E.">n.E.</option>
					</select>
                    </td>
                   </tr>
                  {/foreach}
                 </tbody>
               </table>
      </form>

  {/section}
</div></div>

<div class="shadow"><div>
<h3>Spiele:</h3>
  {section name=gamesloop loop=$wm_games2}

    {assign var="gameType" value=$wm_games2[gamesloop].game_type}
      <h5>Gruppe {$game_types[$gameType]}</h5>
      <form method="post">
          <table  class="centralTable" border="0">
            <thead>
              <tr>
                <!--<th>Spiel-Nr.</th>-->
                <th>Datum, Zeit</th>
                <th colspan="2">Begegnung</th>
                <th>Ergebnis</th>
                <th>Zusatz</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <td colspan="7">
                  <input type="submit" name="save-result" value="Ergebnis speichern" title="Einmal dr&uuml;cken!" />
                </td>
              </tr>
            </tfoot>
            <tbody>
              {foreach from=$wm_games2[gamesloop].data item="game"}
                {assign var="id" value=$game->id}
                {assign var="team1" value=$game->getTeam1()}
                {assign var="team2" value=$game->getTeam2()}
                <tr>
                  <td>{$game->getStartTime()|unihelp_strftime} </td>
                  <td class="right">{$team1->getName()}</td>
                  <td class="left">{$team2->getName()}</td>

                    <td class="tippgame"><input size="1" type="text" name="game{$id}_team1" value="{$game->getGoalsTeam1()}" />&nbsp;:&nbsp;<input type="text" size="1" name="game{$id}_team2" value="{$game->getGoalsTeam2()}" /></td>
                    <td><select name="game{$id}_result_type" size="1">
                        <option {if $game->getAdditionalInfo()==""}selected="selected"{/if} value="">keine</option>
                        <option {if $game->getAdditionalInfo()=="n.V."}selected="selected"{/if} value="n.V.">n.V.</option>
                        <option {if $game->getAdditionalInfo()=="n.E."}selected="selected"{/if} value="n.E.">n.E.</option>
                    </select>
                    </td>
                   </tr>
                  {/foreach}
                 </tbody>
               </table>
      </form>
  {/section}

</div></div>

<form method="post">
<label for="day-winners-day">Tagesgewinner fuer (DD.MM.YYYY)</label><input name="day-winners-day" id="day-winners-day" />
<input type="submit" name="day-winners" value="Tagesgewinner berechnen" />
</form>

<div class="shadow"><div>
<h3>Europameister</h3>
<form method="post">
<select name="winner_team">
  {html_options options=$teams}
</select>
<input type="submit" name="set_winner" value="Europameister setzen" />
</form>
<br class="clear" />
</div></div>

</div>



