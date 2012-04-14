
{*<center>
<a href="/MrMrsCampus/score">
<img src="http://magdeburg.unihelp.de/userfiles/users/2995/17c30a356860b7a4_logo%20mister%20&%20miss%20campus%2009.jpg" alt="logo" style="border: 0 !important"/>
</a>
<br/><br/>
<a href="#miss">Top Miss Campus</a> - <a href="#mister">Top Mister Campus</a> - {if $smarty.const.MR_MRSCAPUS_RATING_ENABLED}<a href="/MrMrsCampus">Kandidaten bewerten</a>{/if} - 
<a href="/MrMrsCampus/candidates">alle Teilnehmer</a>
</center>*}

<div>
<a href="/MrMrsCampus/score">
<img height="150px" src="http://magdeburg.unihelp.de/userfiles/users/2995/17c30a356860b7a4_logo%20mister%20&%20miss%20campus%2009.jpg" alt="logo" style="float:right; border: 0 !important"/>
</a>
<ul class="bulleted">
<li><a href="#miss">Top Miss Campus</a> - <a href="#mister">Top Mister Campus</a></li>
{if $smarty.const.MR_MRSCAPUS_RATING_ENABLED}<li><a href="/MrMrsCampus">Kandidaten bewerten</a></li>{/if}
<li><a href="/MrMrsCampus/candidates">alle Teilnehmer</a></li>
</ul>
</div>

<br class="clear" />

<div class="shadow"><div>
<h3>Highscore - Miss Campus - OnlineVoting</h3>
  <a name="miss"></a> 
<table cellspacing="0" cellpadding="10" border="1" id="table_rahmen" width="100%">
	{counter start=0 skip=1 print=false}
	{foreach from=$canidates_w item="canidate"}
	<tr><td id="table_inhalt_zwei">


	<div style="font-size: 150%">{counter}. {$canidate[2]} {$canidate[3]}
	{if $canidate[4] == "f"}
			<img src="http://www.unihelp.de/images/female_green.gif" alt="">
	{else}
				<img src="http://www.unihelp.de/images/male_green.gif" alt="">
	{/if}
	</div>
	<div style="float: right">
	{if $canidate[8]}
				<img src="{$canidate[8]}" alt="Bild des Teilnehmers">		 
	{else}
				Kein Bild vorhanden!
	{/if}
	</div>
	<br/>
	<div style="font-size: 150%">So sehe ich mich selber:</div>
	<table>
	<tr>
			<td>
					Ich bin ein/e Entertainer/in: 
			</td>
			<td>
					<img src="/images/bewertungen/course_{$canidate[9]}.png" alt="{$canidate[9]}">	
			</td>
	</tr>		
	<tr>
			<td>
					Ich bin eine Sportskanone: 
			</td>
			<td>
					<img src="/images/bewertungen/course_{$canidate[10]}.png" alt="{$canidate[10]}">	
			</td>
	</tr>	
	<tr>
			<td>
						Ich bin Supersexy:
			</td>
			<td>
					<img src="/images/bewertungen/course_{$canidate[11]}.png" alt="{$canidate[11]}">	
			</td>
	</tr>	
	<tr>
			<td>
					Ich bin ein Superhirn: 
			</td>
			<td>
					<img src="/images/bewertungen/course_{$canidate[12]}.png" alt="{$canidate[12]}">	
			</td>
	</tr>			
	</table>	
	<br/>
	<div style="font-size:200%">
			 Bewertung: {$canidate[13]|truncate:4:"":true}
	</div>
	
	</td></tr>
	{/foreach}
	</table>
	
{* ---------------------------------------- *}	
	
	<br/><br/>0
</div></div>	
<div class="shadow"><div>
<h3>Highscore - Mister Campus - OnlineVoting</h3>
  <a name="mister"></a> 
<table cellspacing="0" cellpadding="10" border="1" id="table_rahmen" width="100%">
	{counter start=0 skip=1 print=false}
	{foreach from=$canidates_m item="canidate"}
	<tr><td id="table_inhalt_zwei">


	<div style="font-size: 150%">{counter}. {$canidate[2]} {$canidate[3]}
	{if $canidate[4] == "f"}
			<img src="/template/unihelp_hp/css/images/female_green.gif" alt="">
	{else}
				<img src="/template/unihelp_hp/css//images/male_green.gif" alt="">
	{/if}
	</div>
	<div style="float: right">
	{if $canidate[8]}
				<img src="{$canidate[8]}" alt="Bild des Teilnehmers">		 
	{else}
				Kein Bild vorhanden!
	{/if}
	</div>
	<br/>
	<div style="font-size: 150%">So sehe ich mich selber:</div>
	<table>
	<tr>
			<td>
					Ich bin ein/e Entertainer/in: 
			</td>
			<td>
					<img src="/images/bewertungen/course_{$canidate[9]}.png" alt="{$canidate[9]}">	
			</td>
	</tr>		
	<tr>
			<td>
					Ich bin eine Sportskanone: 
			</td>
			<td>
					<img src="/images/bewertungen/course_{$canidate[10]}.png" alt="{$canidate[10]}">	
			</td>
	</tr>	
	<tr>
			<td>
						Ich bin Supersexy:
			</td>
			<td>
					<img src="/images/bewertungen/course_{$canidate[11]}.png" alt="{$canidate[11]}">	
			</td>
	</tr>	
	<tr>
			<td>
					Ich bin ein Superhirn: 
			</td>
			<td>
					<img src="/images/bewertungen/course_{$canidate[12]}.png" alt="{$canidate[12]}">	
			</td>
	</tr>			
	</table>	
	<br/>
	<div style="font-size:200%">
			 Bewertung: {$canidate[13]|truncate:4:"":true}
	</div>
	
	</td></tr>
	{/foreach}
	</table>
</div></div>	