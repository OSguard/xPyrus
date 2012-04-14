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

{include file="modules/sports/part_soccer_rules.tpl"}

<div class="shadow"><div>
<h3>Tippspiel-Status {$visitor->getUsername()}</h3>
{$visitor->getUsername()}, Du hast bisher {$ranking.points|default:0} Punkte erreicht und liegst auf Platz {$ranking.rank|default:"&#8734;"} der <a href="{sports_url soccerBetRanking=$tournament}">Rangliste aller UniHelper</a>.{*
    {if $ranking.rank == 1}<strong>Du bist ein Fu&szlig;ballgott!</strong>
    {elseif $ranking.rank >= 2 and $ranking.rank <= 10}Du bist gut!
    {/if}*}
</div></div>

<div class="shadow"><div>
<h3>{$tournament->getName()}-Tipp</h3>
    {if !$tournament_started}
    <form method="post">
    <input type="hidden" name="bet_winner" value="1" />
    <label for="tipp-winner">{$tournament->getName()}-Meister wird:</label>
    <select name="tipp-winner" id="tipp-winner">
       {if !$tipp_winner}
        <option value="0">-- Hier Team ausw&auml;hlen --</option>
       {else}
        <option value="0">-- Tipp l&ouml;schen --</option>
        {assign var="teamBet" value=$tipp_winner->getWinnerIs()}
       {/if}
       {foreach from=$teams item="team"}
       <option style="background-image: url('/images/tippspiel/{$team->getNameShort()|lower}_small.png');" value="{$team->id}" {if $teamBet && $team->id==$teamBet->id}selected="selected"{/if}>{$team->getName()}</option>
       {/foreach}
    </select>
    <input type="submit" value="Tipp speichern" title="Einmal dr&uuml;cken!" />
    <span>{if $error_bet_wm}<img src="/images/tippspiel/graphics/kreuz.jpg" alt="Fehler" style="color:#FF0000; border:0;" />
    {elseif $just_bet_wm}<img src="/images/tippspiel/graphics/haken.jpg" alt="Fehler" style="color:#FF0000; border:0;" />{/if}</span>
    </form>
    {else}
       {if !$tipp_winner}
        {$visitor->getUsername()} hat keine Ahnung, wer {$tournament->getName()}-Meister wird :D.
       {else}
        {assign var="teamBet" value=$tipp_winner->getWinnerIs()}
        {foreach from=$teams item="team"}
           {if $team->id==$teamBet->id}
              {$visitor->getUsername()} sagt: <strong>{$team->getName()}</strong> <img src="/images/tippspiel/{$team->getNameShort()|lower}3.png" alt="" /> gewinnt die {$tournament->getName()}! 
           {/if}
        {/foreach}
       {/if}
    {/if}
    <br class="clear" />
</div></div>

{if $games_group_stage}
<div class="shadow"><div>
  <h3>Vorrunde</h3>
  {foreach from=$games_group_stage item="matchday"}
    <h4>Gruppe {$matchday.group}</h4>
    <a id="vote{$matchday.group}"></a>
    {include file="modules/sports/part_soccer_bet_table.tpl"}
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
        {include file="modules/sports/part_soccer_bet_table.tpl"}
    </div></div>
{/foreach}

</div>
