{literal}
<script type="text/javascript"><!--
 function setCookie(name,value,days) {
 	if (days) {
 		var date = new Date();
 		date.setTime(date.getTime()+(days*24*60*60*1000));
 		var expires = "; expires="+date.toGMTString();
 	}
 	else var expires = "";
 	document.cookie = name+"="+value+expires+"; path=/";
 }
 
 function getCookie(name) {
 	var nameEQ = name + "=";
 	var ca = document.cookie.split(';');
 	for(var i=0;i < ca.length;i++) {
 		var c = ca[i];
 		while (c.charAt(0)==' ') c = c.substring(1,c.length);
 		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
 	}
 	return null;
 }
 
 function delCookie(name) {
 	setCookie(name,"",-1);
 }
 function swap() {
 			 Obj = document.getElementById("tipp-regeln");
 			 Link = document.getElementById("swap-link");
 			 if (Obj.style.display == "none") {
 				 Obj.style.display = "block";
 				 delCookie('swap-regeln');
 				 Link.innerHTML = "(ausblenden)";
 			 } else {
 				 Obj.style.display = "none"; 				  
 				  setCookie('swap-regeln','1','100');
 				  Link.innerHTML = "(einblenden)";
 			 }
 		 }
 		 
 function forbereiche() {
        Cookie = getCookie('swap-regeln');
        if (Cookie != null) {
         Obj = document.getElementById("tipp-regeln");
 			 Link = document.getElementById("swap-link");
 			 Obj.style.display = "none"; 				  
 				  Link.innerHTML = "(einblenden)";
        }

      }
 var oldOnload2 = window.onload;
window.onload = function() {
  forbereiche();
  if (oldOnload2) {
    oldOnload2();
  }
}     
 -->
 </script>
{/literal}

<div class="shadow"><div>
	<h3>Regeln <a href="#" onclick="swap()" id="swap-link">(ausblenden)</a></h3>
     <div id="tipp-regeln">
      <p>Beim UniHelp-Fu&szlig;ball-Tippspiel kannst Du Deinen Fu&szlig;ballsachverstand oder das pure Gl&uuml;ck in Punkte verwandeln :-) Au&szlig;erdem geht es nat&uuml;rlich um die Anerkennung der Gemeinschaft. Wie geht das im Einzelnen?</p>
 	  <ul class="bulleted">
	    <li>F&uuml;r jede Begegnung gibst Du Deine Erwartung an, wie viele Tore die jeweiligen Mannschaften erzielen werden.</li>
	    <li>Daraus wird automatisch der reine Spielausgang bestimmt, also Sieg, Niederlage oder Unentschieden (aus Sicht der Heimmannschaft).</li>
	    <li>Deinen Tipp kannst Du bis jeweils 60 Minuten vor Anpfiff abgeben. Ebenfalls kannst Du Deine Tipps &auml;ndern bis 60 Minuten vor Anpfiff.</li>
	    <li>Wenn Du ein Spiel nicht tippen m&ouml;chtest, la&szlig; das Feld einfach leer. </li>
	    <li>F&uuml;r das korrekte Ergebnis (Tore beider Mannschaften genau getroffen) bekommst Du 2 Punkte, die richtige Tordifferenz bringt Dir 1,5 Punkte und  der korrekte Spielausgang (nur Sieg, Niederlage oder Unentschieden, aber ohne korrekte Tore) wird mit 1 Punkt belohnt.</li>
		{if $tournament->hasGroupStage()}
	    <li><strong>Ergebnis ist, wenn der Schiri das Spiel beendet, das gilt nat&uuml;rlich auch f&uuml;r die KO-Spiele!</strong></li>
	    <li>Die Punkte aus dem Viertelfinale z&auml;hlen <strong>doppelt</strong>, danach, ab dem Halbfinale, die <strong>dreifache</strong> Anzahl. </li>
		{/if}
	    <li>Bis zum Anpfiff des Er&ouml;ffnungsspiels kannst Du den Sieger der {$tournament->getName()} tippen. Der richtige Tipp bringt <strong>{ $tournament->getPointsWinner()} Punkte</strong> und damit es nicht so langweilig wird, ziehen wir von Deinen anderen Punkten <strong>5 Prozent</strong> ab, wenn Du den Meister gar nicht oder falsch getippt hast.</li>
	    {*<li>Wer auf &Ouml;sterreich als Europameister tippt UND Recht beh&auml;lt, bekommt die Tippspielpunkte verdreifacht :-)</li>*}
	    <li>Am Ende gewinnt der User mit den meisten Punkten. F&uuml;r alle User werden die Punkte aus dem Tippspiel in UniHelp-W-Punkte &uuml;berf&uuml;hrt.</li>
        <li>Viel Spa&szlig; w&uuml;nscht das UniHelp-Team! </li>
	  </ul>
	  <p>Bei Fragen zum Tippspiel kannst Du dich an den <a href="/support">Support wenden</a>!</p>
  </div>

</div></div>