<center>
<a href="/MrMrsCampus/score">
<img src="http://magdeburg.unihelp.de/userfiles/users/2995/17c30a356860b7a4_logo%20mister%20&%20miss%20campus%2009.jpg" alt="logo" style="border: 0 !important"/>
</a>
<br/><br/>
</center>

{if $canidate}
<div class="shadow"><div class="nopadding">
<h3>Kandidaten</h3>
<div style="font-size: 150%">Name: {$canidate[2]} {$canidate[3]}
	{if $canidate[4] == "f"}
			<img src="/template/unihelp_hp/css/images/female_green.gif" alt="">
	{else}
				<img src="/template/unihelp_hp/css/images/male_green.gif" alt="">
	{/if}
	</div>
	<center>
	{if $canidate[8]}
				<img src="{$canidate[8]}" alt="Bild des Teilnehmers">					 
	{else}
				Kein Bild vorhanden!
	{/if}
	</center>
	<br/>
	<div style="font-size: 150%">So sehe ich mich selber:</div>
	<table >
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

</div></div>

<div class="shadow"><div>
<h3>Deine Bewertung</h3>

	{if !$visitor->isRegularLocalUser()}
			Nur UniHelp User k&ouml;nnen Bewertungen abgeben. Log Dich ein, oder besorg dir als Student <a href="/newuser">hier</a> einen Account.
	{elseif $countVotes[0]!=$countCanidates[0]}
				<center>
				{literal}
				<style type="text/css">
				* html input, * html label{
				float: none !important;
				}
				
				</style>
				{/literal}
				<form method="post" name="newCanidat" enctype="multipart/form-data" >
		
				<input type="hidden" name="canidateId" value="{$canidate[0]}" />
		
				<input name="entertainer" type="radio" value="1" id="entertainer_1" checked /> <label for="entertainer_1">1</label> 
      	<input name="entertainer" type="radio" value="2" id="entertainer_2" /> <label for="entertainer_2">2</label> 
      	<input name="entertainer" type="radio" value="3" id="entertainer_3" /> <label for="entertainer_3">3</label> 
      	<input name="entertainer" type="radio" value="4" id="entertainer_4" /> <label for="entertainer_4">4</label> 
      	<input name="entertainer" type="radio" value="5" id="entertainer_5" /> <label for="entertainer_5">5</label> 
				<input name="entertainer" type="radio" value="6" id="entertainer_6" /> <label for="entertainer_6">6</label> 
				<input name="entertainer" type="radio" value="7" id="entertainer_7" /> <label for="entertainer_7">7</label> 
				<input name="entertainer" type="radio" value="8" id="entertainer_8" /> <label for="entertainer_8">8</label> 
				<input name="entertainer" type="radio" value="9" id="entertainer_9" /> <label for="entertainer_9">9</label> 
				<input name="entertainer" type="radio" value="10" id="entertainer_10" /> <label for="entertainer_10">10</label> 
				<br />
				<input type="submit" name="save" value="jetzt bewerten" />
				
				<br class="clear" /><br />
				Du hast {$countVotes[0]} von {$countCanidates[0]} Kandidaten schon bewertet.
				
				</form>
				</center>
	{else}
		Du hast alle Kandidaten bewertet!			
	{/if}		

</div></div>


	
	<br />
	<div style="width=100%; text-align: right">
			 <a href="/MrMrsCampus/score#miss">Miss Campus Highscore</a> - 
			 <a href="/MrMrsCampus/score#mister">Mister Campus Highscore</a> - 
			 <a href="/MrMrsCampus">zuf&auml;llig n&auml;chsten Kandidaten w&auml;hlen</a> -
			 <a href="/MrMrsCampus/candidates">alle Teilnehmer</a>
	</div>
	
	{else}
				Du hast alle Kandidaten bewertet!
				<br>
	<div style="width=100%; text-align: right">
			 <a href="/MrMrsCampus/score#miss">Miss Campus Highscore</a> - 
             <a href="/MrMrsCampus/score#mister">Mister Campus Highscore</a> - 
             <a href="/MrMrsCampus/candidates">alle Teilnehmer</a>
	</div>
	{/if}