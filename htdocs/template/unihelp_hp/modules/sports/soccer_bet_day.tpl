<div class="shadow"><div><h3>Top 5 of the day</h3>
<table class="tinyTable">
<thead>
<tr><th>Rang</th><th>User</th><th>Punkte</th><th>AVG-ID</th><th>PN</th></tr></thead>
<tbody>
{foreach from=$winners item="w"}
<tr>
<td>{counter assign="rank" print=true}</td>
<td>{$w.user->getUsername()}</td><td>{$w.points}</td><td>{$w.time}</td>
<td><a target="_blank" href="{sports_url soccerBetNotifyWinner=1 user=$w.user rank=$rank}">benachrichtigen</a></td>
{*<td><a href="/index.php?mod=i_am_god&method=writeSystemPM&user={$w.user->getUsername()}" title="Private Nachricht an {$w.user->getUsername()} versenden">
        <img src="{$TEMPLATE_DIR}/images/message.gif" alt="PN senden" />
    </a></td>
*}    
</tr>
{/foreach}</tbody>
</table>
</div></div>
