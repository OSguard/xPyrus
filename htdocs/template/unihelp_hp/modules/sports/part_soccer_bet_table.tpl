{if !$readonly}
       <form method="post" action="{sports_url soccerBet=$tournament}#vote{$game_types[$gameType]->id}">
{/if}
	    <input type="hidden" name="bet" value="{$game_types[$gameType]->id}" />
	    <table class="centralTable" border="0">
          <thead>
            <tr>
              <!--<th>Spiel-Nr.</th>-->
              <th>Datum, Zeit</th>
              <th colspan="2">Begegnung</th>
              <th>Ergebnis</th>
              <th>User-Tipp</th>
              <th>Quote</th>
              <th>Status</th>
              <th>Punkte</th>
            </tr>
          </thead>
          {if !$readonly}
          <tfoot>
            <tr>
              <td colspan="8"><input type="submit" value="Tipp(s) speichern" title="Einmal dr&uuml;cken! Speichert alle Tipps f&uuml;r dieser Runde." /></td>
            </tr>
          </tfoot>
          {/if}
          <tbody>
           {foreach from=$matchday.data item="game"}
            {assign var="id" value=$game->id}
            {assign var="team1" value=$game->getTeam1()}
            {assign var="team2" value=$game->getTeam2()}
            <tr {if $game->isNearFuture()} class="nearFuture"{/if}>
              <td>{if $game->isNearFuture()}<strong>{/if} 
                    {$game->getStartTime()|unihelp_strftime} 
                  {if $game->isNearFuture()}</strong>{/if} </td>
              <td class="right"><span>{$team1->getName()}</span> <img src="/images/tippspiel/{$team1->getNameShort()|lower}2.png" alt="" /></td>
              <td class="left"><img src="/images/tippspiel/{$team2->getNameShort()|lower}2.png" alt="" />
               <span>{$team2->getName()}</span></td>
              {if !$game->isBetOpen()}
                <td>{$game->getGoalsTeam1()} : {$game->getGoalsTeam2()} {$game->getAdditionalInfo()}</td>
                <td>{if $tipps[$id] && $tipps[$id]->getGoalsTeam1()!==NULL} {$tipps[$id]->getGoalsTeam1()} : {$tipps[$id]->getGoalsTeam2()}{else}&mdash;{/if}</td>
                <td>
                    {assign var="quote" value=$game->getBetQuote()}
                    <a href="{sports_url soccerBetStats=$game tournament=$tournament}" target="_blank" title="Erweiterte Statistiken">{$quote[0]}:{$quote[1]}:{$quote[2]}</a>
                    </td>
                <td></td>
                <td>{if $tipps[$id]}{$tipps[$id]->getPoints()}{/if}</td>
              {else}
                 <td>&mdash;</td>
                 <td class="tippgame">
                 {if $readonly}
                    {if $tipps[$id] && $tipps[$id]->getGoalsTeam1()!==NULL} {$tipps[$id]->getGoalsTeam1()} : {$tipps[$id]->getGoalsTeam2()}{else}&mdash;{/if}
                 {else}
                    <input name="game{$id}_team1" size="1" type="text" value="{if $tipps[$id]}{$tipps[$id]->getGoalsTeam1()}{/if}" />:
                    <input name="game{$id}_team2" size="1" type="text" value="{if $tipps[$id]}{$tipps[$id]->getGoalsTeam2()}{/if}" />
                 {/if}
                 </td>
                 <td>
			     {assign var="quote" value=$game->getBetQuote()}
			     <a href="{sports_url soccerBetStats=$game}" target="_blank" title="Erweiterte Statistiken">{$quote[0]}:{$quote[1]}:{$quote[2]}</a>
			     </td>
                 <td>{if $error_bet[$id]}<img src="/images/tippspiel/graphics/kreuz.jpg" alt="Fehler" style="color:#FF0000; border:0;" />
              {elseif $just_bet[$id]}<img src="/images/tippspiel/graphics/haken.jpg" alt="Okay" style="color:#00CC00; border:0;" />{/if}</td>
                 <td></td>
              {/if}
            </tr>
            {/foreach}
          </tbody>
        </table>
{if !$readonly}
  </form>
{/if}