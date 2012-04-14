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
      <p>Zur {$tournament->getDescription()} gibt es ein grandioses <a href="{sports_url soccerBet=$tournament}">Tippspiel</a> in UniHelp. Auf dieser Seite siehst Du die Tipps von {$user->getUsername()}. {user_info_link user=$user} freut sich sicherlich &uuml;ber ein paar Ratschl&auml;ge :-) </p>
  <br class="clear">
</div></div>    

<div class="shadow"><div>
<h3>{$tournament->getName()}-Tipp</h3>
       {if !$tipp_winner}
        {$user->getUsername()} hat keine Ahnung, wer {$tournament->getName()}-Meister wird :D.
       {else}
        {assign var="teamBet" value=$tipp_winner->getWinnerIs()}
        {foreach from=$teams item="team"}

           {if $team->id==$teamBet->id}

              {$user->getUsername()} sagt: <strong>{$team->getName()}</strong> <img src="/images/tippspiel/{$team->getNameShort()|lower}3.png" alt="" /> gewinnt die {$tournament->getName()}! 
           {/if}
        {/foreach}
       {/if}
       Insgesamt hat {$user->getUsername()} schon {$ranking.points|default:0} Punkte w&auml;hrend des Tippspiels erreicht und liegt damit auf Platz {$ranking.rank|default:"&#8734;"} der <a href="{sports_url soccerBetRanking=$tournament}">Rangliste aller UniHelper</a>! Gut so!
</div></div>


{if $games_group_stage}
<div class="shadow"><div>
  <h3>Vorrunde</h3>
  {foreach from=$games_group_stage item="matchday"}
    <h4>Gruppe {$matchday.group}</h4>
    <a id="vote{$matchday.group}"></a>
    {include file="modules/sports/part_soccer_bet_table.tpl" readonly=1}
  {/foreach}
</div></div>
{/if}

{if count($game_types) > 7}
<div class="shadow"><div>
  <h3>Anzeige-Auswahl</h3>
    <label for="selectGameType">Zeige </label>
    <select id="selectGameType" onchange="soccerGameTypeChange()">
    {foreach from=$game_types item="gt"}
        <option value="{$gt->id}" {if $gt->id == $upcoming_game_type->id}selected="selected"{/if}>{$gt->getName()}</option>
    {/foreach}
    </select>
</div></div>
{/if}

{foreach from=$games item="matchday"}
    {assign var="gameType" value=$matchday.game_type}
    <div class="shadow gameType" id="gameType{$game_types[$gameType]->id}"><div>
        <h3>{$game_types[$gameType]->getName()}</h3>
        <a id="vote{$game_types[$gameType]->id}"></a>
        {include file="modules/sports/part_soccer_bet_table.tpl" readonly=1}
    </div></div>
{/foreach}

</div>

