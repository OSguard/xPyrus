{*<center>
<a href="/MrMrsCampus/score">
<img src="http://magdeburg.unihelp.de/userfiles/users/2995/17c30a356860b7a4_logo%20mister%20&%20miss%20campus%2009.jpg" alt="logo" style="border: 0 !important"/>
</a>
<br/><br/>
<a href="/MrMrsCampus/score#miss">Miss Campus Highscore</a> - <a href="/MrMrsCampus/score#mister">Mister Campus Highscore</a> - {if $smarty.const.MR_MRSCAPUS_RATING_ENABLED}<a href="/MrMrsCampus">Kandidaten bewerten</a>{/if}
</center>*}

<div>
<a href="/MrMrsCampus/score">
<img height="150px" src="http://magdeburg.unihelp.de/userfiles/users/2995/17c30a356860b7a4_logo%20mister%20&%20miss%20campus%2009.jpg" alt="logo" style="float:right; border: 0 !important"/>
</a>
<ul class="bulleted">
<li><a href="/MrMrsCampus/score#miss">Top Miss Campus</a> - <a href="/MrMrsCampus/score#mister">Top Mister Campus</a></li>
{if $smarty.const.MR_MRSCAPUS_RATING_ENABLED}<li><a href="/MrMrsCampus">Kandidaten bewerten</a></li>{/if}
<li><a href="/MrMrsCampus/candidates">alle Teilnehmer</a></li>
</ul>
</div>

<br class="clear" />

<br/>

<div class="shadow"><div>
<h3>Alle Teilnehmer</h3>
  <a name="miss"></a> 
<table cellspacing="0" cellpadding="10" border="1" id="table_rahmen" width="100%">
	<tr>
	{assign var="i" value=1}
	{foreach from=$canidates item="canidate"}
	<td id="table_inhalt_zwei" style="width: 33%; overflow: hidden; padding: 5px">


	<div style="font-size: 150%">{$canidate[2]} {$canidate[3]}
	{if $canidate[4] == "f"}
			<img src="/template/unihelp_hp/css/images/female_green.gif" alt="">
	{else}
				<img src="/template/unihelp_hp/css/images/male_green.gif" alt="">
	{/if}
	</div>
	<div style="float: right">
	{if $canidate[8]}
				<img src="{$canidate[8]}" alt="Bild des Teilnehmers" style="width: 240px; height: 320px">		 
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
	{*<div style="font-size:200%">
			 Bewertung: {$canidate[13]|truncate:4:"":true}
	</div>*}
	
	</td>
	{if $i%2 == 0}
			</tr><tr>
  {/if}			
	{assign var="i" value=$i+1}
	{/foreach}
	</tr>
	</table>
</div></div>	
