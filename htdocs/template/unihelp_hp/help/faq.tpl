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

*}<a id="topofpage" name="topofpage"></a>

<div class="shadow"><div id="faq">
<h3>Häufig gestellte Fragen/Frequently asked Questions</h3>
  <ul>
	<li><a href="#faq3">Allgemeine Fragen zur [[local.local.project_name]]-Benutzeroberfl&auml;che</a>
	  <ul><li><a href="#faq39"> Wie finde ich meine richtigen F&auml;cher? </a></li>
		<li><a href="#faq32a"> Wie funktioniert das Punktsystem? </a></li>

		<li><a href="#faq32"> Was bringen mir die Punkte? </a></li>
		<li><a href="#faq32b"> Wie bekomme ich welche Punkte?</a></li>
		<li><a href="#faq38"> Warum kann ich mein Geburtsdatum nicht &auml;ndern? </a></li>
		<li><a href="#faq310"> Wie kann ich meinen User-Namen &auml;ndern? </a></li>

		<li><a href="#faq311"> Wie kann ich meinen Account l&ouml;schen? </a></li>
		<li><a href="#faq36"> Wie nehme ich jemanden in meine Freundesliste auf und was bringt mir das? </a></li>
		<li><a href="#faq37"> Wie kann ich jemanden wieder aus meiner Freundesliste entfernen? </a></li>
		<li><a href="#faq37a"> Was ist die Ignoreliste? </a></li>

		<li><a href="#faq35"> Wozu ein Tagebuch? </a></li>
		<li><a href="#faq31"> Wieso sind alle Fotos und mein Userbild auf 100 kb  beschr&auml;nkt? </a></li>
		<li><a href="#faq314"> Was ist das Kartensystem?</a></li>
		<li><a href="#faq312"> Wie kann ich Texte formatieren? </a></li>

	    <li><a href="#faq313"> Wie nutze ich die Beitragsbewertung im Forum? </a></li>
	    <li><a href="#faq320"> Wie kann ich einer Organisation beitreten? </a></li>
	    <li><a href="#faq321"> Wie kann ich eine eigene Organisation gründen? </a></li>
	    <li><a href="#faq322"> Wie funktioniert die Selbstverwaltung in den Organisationen?</a></li>
	    <li><a href="#faq323"> Was ist die Prüfsumme einer Datei? </a></li>

	    <li><a href="#faq315"> Wozu ist der <abbr title="secure hash algorithm (engl. für sicherer Hash-Algorithmus)">SHA</abbr>-1-Zeichensalat einer Unterlage gut?</a></li>
	    <li><a href="#faq316"> Wie kann ich (unter Windows) die Prüfsumme einer Datei bestimmen?</a></li>
	  </ul>
	</li>
	<li><a href="#faq4">Erweiterungen zu [[local.local.project_domain]]</a>

	  <ul>
	    <li><a href="#faq402">Was ist die Vernetzung?</a></li>
	    <li><a href="#faq401">Was ist die [[local.local.project_name]]-Toolbar?</a></li>
	</ul></li>
  </ul>

  <h4><a name="faq3" id="faq3"></a>Allgemeine Fragen zur [[local.local.project_name]]-Benutzeroberfl&auml;che </h4>
  <dl>
  <dt><a name="faq39" id="faq39"></a>Wie finde ich meine richtigen F&auml;cher?</dt>

	  <dd>
	    <p>
	    {dynamic}
	    {if $visitor->isLoggedIn()}
	    <a href="{user_management_url courses=$visitor}">Hier</a> kannst Du Deine Fächer verwalten.
	    {else}
	    Unter Einstellungen -> Fächer/Studium kannst Du Deine Fächer verwalten.
	    {/if}
	    {/dynamic}
	    <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
	  </dd>
  <dt><a name="faq32a" id="faq32a"></a>Wie funktioniert das Punktesystem?</dt>
	  <dd>
	    <p>
	    Das Punktesystem wird in Levelpunkte und Wirtschaftspunkte unterschieden:		</p><p>
		Die Levelpunkte (kurz „Punkte“) können nur steigen und nicht sinken. Sie repräsentieren die Aktivitäten des Users in der Community. So erkennt man auch sofort die "Power User". Wie der Name schon sagt, sind Levelpunkte dazu da ein neues "Level" zu erreichen. Pro Level kann der User bestimmte Features für sich freischalten. Je höher das Level desto interessanter die Features! Beim Freischalten neuer Features werden dem User aber keine Levelpunkte abgezogen.
		</p><p>
		Die Wirtschaftspunkte (kurz: „W-Punkte“) dienen als Währung bei [[local.local.project_name]]. Diese kommen vor allem im Unterlagensystem zum Einsatz, aber auch beim Verfassen von Privaten Nachrichten oder anonymen Postings im Forum. Das Wichtigste bei W-Punkte ist in erster Linie der Kauf von Unterlagen. Dazu muss man die geforderte Anzahl von W-Punkten auf dem Konto haben. Außerdem kann man sich mit W-Punkten einen kleinen Luxus leisten: das Versenden von Privaten Nachrichten, die für andere User nicht sichtbar sind. Im Forum kann man mit Hilfe der W-Punkte anonyme Beiträge posten, wobei der Username und alle weiteren persönlichen Informationen ausgeblendet werden.
	    <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
	  </dd>
  <dt><a name="faq32" id="faq32"></a>Was bringen mir die Punkte?</dt>

	  <dd>
	    <p>
	    Die Punkte sollen einen Anreiz darstellen, Dich aktiv am Geschehen zu beteiligen, z.B. indem Du Deine Unterlagen für andere hochlädst oder mit anderen Usern kommunizierst. Außerdem benötigst Du die Punkte, um selber Unterlagen downloaden zu können. Somit ist eine gewisse Aktivität unentbehrlich und niemand kann sich ausschließlich zum Unterlagendownload bei [[local.local.project_name]] anmelden. Letztendlich stellen die Punkte auch Deine Beteiligung am Kollektiv dar.
	    <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
		</dd>
  <dt><a name="faq32b" id="faq32b"></a>Wie bekomme ich welche Punkte?</dt>
	  <dd>
	    <p>	
		Die Punkte, die bei Gästebucheinträgen vergeben werden, sind Levelpunkte und können nur auf diese Weise gesammelt werden. Pro Levelpunkt bekommt der User gleichzeitig 0,1 W-Punkte.		</p><p>

		Ganze W-Punkte werden nur durch die Beteiligung des Users am Unterlagensystem vergeben. Hier gibt es allerdings keine Levelpunkte.
	    <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
	  </dd>
  <dt><a name="faq38" id="faq38"></a>Warum kann ich mein Geburtsdatum nicht &auml;ndern?</dt>
	  <dd>
	    <p>
	    Wir gehen davon aus, dass Du Dein Geburtstag nicht verlegen willst, wenn er ausversehen falsch ist, dann wende Dich an den 
	    	    <a href="/support/birthday" title="Geburtstag ändern lassen">Support</a>.
	    		<a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a> </p>
	  </dd>
  <dt><a name="faq310" id="faq310"></a>Wie kann ich meinen User-Namen &auml;ndern?</dt>
	  <dd>
	    <p>
	    Normalerweise ist es nicht üblich und gewünscht, dass man seinen Usernamen ändert. Dein Name ist schließlich ein wichtiges Identifikationsmerkmal. Anhand Deines Namens kann man nachvollziehen, wie und wo Du Dich geäußert hast.		</p><p>
    	Okay, ganz so streng ist es dann allerdings nicht, wer absolut unglücklich mit seinem Namen ist, dem kann durch eine nette 
    		    <a href="/support/changeusername" title="Usernamen ändern lassen">Nachricht </a> an das [[local.local.project_name]]-Team eventuell geholfen werden.
	    <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
	  </dd>
  <dt><a name="faq311" id="faq311"></a>Wie kann ich meinen Account l&ouml;schen?</dt>
	  <dd>
	    <p>
	    In Deinen Einstellungen gibt es ein Link um die Mitgliedschaft bei [[local.local.project_name]] zu beenden. Deine Daten werden selbstverständlich gelöscht; eingestellte Unterlagen bleiben aber im System. Bitte bedenke, dass wir nach dem Löschen eines Accounts keine Neuanmdeldung zulassen können!
	    <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a> </p>
	  </dd>

  <dt><a name="faq36" id="faq36"></a>Wie nehme ich jemanden in meine Freundesliste auf und was bringt mir das?</dt>
	  <dd>
	    <p>
	    Wenn Du eine(n) Freund(in) in Deiner Freundesliste hast, brauchst Du sie nicht mehr aufwendig suchen und kannst immer sehen, ob sie/er gerade online ist.		</p><p>
    	Du klickst ihn/sie einfach in Deiner Freundesliste an und schon bist Du auf ihrer/seiner Seite. Wenn Du jemanden in Deine Freundesliste aufnehmen willst, klickst Du einfach rechts im Kasten unter dem Userbild der Person auf den Link mit dem Satz: "XY in meiner Freundesliste hinzufügen".
		<a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
    </dd>
	<dt><a name="faq37" id="faq37"></a>Wie kann ich jemanden wieder aus meiner Freundesliste entfernen?</dt>

	  <dd>
	    <p>	    	    
	    Hinter dem Usernamen gibt es einen Link mit dem Du Deine Freundin/Deinen Freund von Deiner Freundesliste löschen kannst. Hast Du das Feature "Erweiterte Freundesliste" freigeschaltet, kannst Du durch einfaches Ziehen mit der Maus Deine Freundin/Deinen Freund in den "Mülleimer stecken", auf "Speichern" klicken und sie/ihn so von Deiner Freundesliste entfernen.		</p><p>
		{dynamic}
		    {if $visitor->isLoggedIn()}
		    <a href="{user_management_url friendlist=$visitor}">Hier</a> kannst Du Deine Freunde verwalten.
		    {else}
		    Wenn Du eingelogged bist kannst Du das unter Einstellung und dann den Reiter "Beziehung" ändern.
		    {/if}
	     {/dynamic}
	    	     	    <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
    </dd>
  <dt><a name="faq37a" id="faq37a"></a> Was ist die Ignoreliste? </dt>

      <dd>
		<p>
		Es ist möglich, andere Nutzer auf eine Ignoreliste zu setzen. User, die Du auf Deiner Ignoreliste hast, können		</p>
		<ul class="bulleted">
		 <li> Dir keine PN schreiben,</li>
         <li>keine Einträge in Deinem Gästebuch verfassen und</li>
         <li>Dich nicht auf ihre Freundesliste setzen.</li>
         </ul>
         <p>
		{dynamic}
		    {if $visitor->isLoggedIn()}
		    <a href="{user_management_url friendlist=$visitor}">Hier</a> findest Du diese Funktion.
		    {else}
		    Wenn Du eingelogged bist kannst Du das unter Einstellung und dann den Reiter "Beziehung" ändern.
		    {/if}
	     {/dynamic}
		<a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
	  </dd>
  <dt><a name="faq35" id="faq35"></a>Wozu ein Tagebuch?</dt>
	  <dd>
	    <p>

	    Dort kannst Du ganz individuell Deine Lieblingsfotos, eine Beschreibung über Dich oder was auch immer einstellen. Deiner Kreativität sind (fast) keine Grenzen gesetzt! Du kannst Dein Tagebuch so gestalten wie Du möchtest und jederzeit wieder verändern. Hier wird Deiner Persönlichkeit quasi keinerlei Einhalt geboten.
	    <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
	  </dd>
  <dt><a name="faq31" id="faq31"></a>Wieso sind alle Fotos und mein Userbild auf 100 kb  beschr&auml;nkt?</dt>
		<dd>
	    <p>
	    Damit Leute mit langsameren Internetverbindungen auch in einer halbwegs erträglichen Geschwindigkeit [[local.local.project_name]] genießen können, da größere Bilder eine längere Aufbauzeit brauchen.
	    <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
	  </dd>
  <dt><a id="faq314" name="faq314"></a>Was ist das Kartensystem?</dt>
		<dd>
		    <p>Um Fehlverhalten von Usern zu verringern, wurde ein Karten-System eingeführt.Als 
Fehlverhalten zählen z.B. Beschimpfungen, Beleidigungen und Bedrohungen anderer User, sowie dem Veröffentlichen gesetzeswidriger Inhalte. Für weitere Informationen bezüglich nicht erwünschten Verhaltens siehe auch unsere <a href="{index_url termsOfUse=1}">Nutzungsbedingungen</a>.</p>

		<p><strong>Gelbe Karte:</strong><br /> 
		    Sie entspricht einer ersten Verwarnung. Solltest Du eine gelbe Karte bekommen, achte bitte darauf, dass es nicht mehr vorkommt.		</p><p><strong>Gelbrote Karte:</strong><br /> 
		    Bei nochmaligem Auffallen wird Dein Account für einige Tage eingeschränkt. Du kannst keine Einträge und keine Foren-Beiträge mehr verfassen. Du kannst Dich lediglich weiter einloggen und Unterlagen austauschen.
		</p><p><strong>
		    Rote Karte:</strong><br /> 
		    Du bekommst eine Rote Karte, wenn Dein Verhalten sich nach Ablauf der Gelben oder Gelbroten Karte nicht verbessert oder noch weiter verschlechtert hat. Dein Account wird vorübergehend gesperrt und Dir werden 50% der Wirtschaftspunkte abgezogen, mit welchen Du z.B. Unterlagen herunterladen kannst. 
		</p><p>
		    Als Sanktion für ein wiederholtes und andauerndes Stören der Community behalten wir uns eine komplette Sperrung, bzw. Löschung des Accounts vor.
		<a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
	  </dd>
	<dt><a name="faq312" id="faq312"></a>Wie kann ich Texte formatieren?</dt>
	  <dd>
		<p>Eine extra <a href="/help/formatcode" title="Formatcode Regeln"><strong>Anleitung</strong></a> findest Du hinter dem Link.
		<a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
	  </dd>
	<dt><a id="faq313" name="faq313"></a>Wie nutze ich die Beitragsbewertung im Forum?</dt>
		<dd>
		  <p>In den Foren findest Du neben den Beitr&auml;gen in dem Feld, das die Autorinformationen enth&auml;lt, eine Grafik mit nach oben bzw. unten gerichteten Daumen. Mit einem Klick auf den entsprechenden Teil der Grafik kannst Du den Beitrag des Autors in diesem Forum bewerten. Hat der User einen &quot;guten&quot; Beitrag verfa&szlig;t klickst Du auf den nach oben gerichteten, gr&uuml;nen, Daumen, andernfalls auf den roten. Daraus resultiert die Farbe des &quot;Stress-O-Meters&quot;: Ist es gr&uuml;n gef&auml;rbt, schreibt der User &uuml;berwiegend gute, hilfreiche Beitr&auml;ge, ist es rot gef&auml;rbt, sehen die anderen User wenig Sinn in seinen Beitr&auml;gen.
		  <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
	 </dd>
	 
	<dt><a name="faq320" id="faq320"></a> Wie kann ich einer Organisation beitreten? </dt>
		<dd>
		  <p>
		  Einer Organisation kann beigetreten werden, indem man auf der Seite der Organisation in der Box "Mitglieder" auf den Link "Mitglied werden" klickt. Der Verwalter der Gruppe bearbeitet dann die Anfrage. 
		  <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
		</dd>

	<dt><a name="faq321" id="faq321"></a> Wie kann ich eine eigene Organisation gründen? </dt>
	   <dd>
		  <p>
		  Eine Organisation kann nicht selbständig gegründet werden.
		  Willst Du eine Organisation auf [[local.local.project_name]] gründen, benutze das 
		  {dynamic}
		  {if $visitor->isLoggedIn()}
		  <a href="{mantis_url addgroup=1}">Support-Formular</a>. 
		  {else}
		  Support-Formular. 
		  {/if}{/dynamic}
		  <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a> </p>
		</dd>
	<dt><a name="faq322" id="faq322"></a> Wie funktioniert die Selbstverwaltung in den Organisationen?</dt>

		<dd>
		  <p>
		  Jede Organisation kann einen Verwalter festlegen. Dieser hat bestimmte Rechte und kann anderen Mitgliedern ebenfalls Rechte zuteilen. So kann beispielsweise bestimmt werden, wer News schreiben darf oder wer Moderator des Organisationsforums ist.
		  <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a> </p>
		</dd>
	<dt><a name="faq323" id="faq323"></a> Was ist die Prüfsumme einer Datei? </dt>
		<dd>
		<p>
		Was mit der Prüfsumme einer Datei gemeint ist, findest Du unter <a href="http://de.wikipedia.org/wiki/Pr%C3%BCfsumme" target="_blank">http://de.wikipedia.org/wiki/Prüfsumme</a>
		  <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a> </p>
		</dd>
	<dt><a id="faq315" name="faq315"></a>Wozu ist der komische <abbr title="secure hash algorithm (engl. für sicherer Hash-Algorithmus)">SHA</abbr>-1-Zeichensalat einer Unterlage gut?</dt>

		<dd>
		  <p>
		  Die <abbr title="secure hash algorithm (engl. für sicherer Hash-Algorithmus)">SHA</abbr>-1-Zeichenkette ist eine sogenannte Prüfsumme (engl. checksum). Das [[local.local.project_name]]-System berechnet automatisch für jede hochgeladene Datei ihre Prüfsumme. Prüfsummen für Dateien sind so einmalig wie der menschliche Fingerabdruck.
		  </p><p>
		  Nach dem Download einer Datei kannst Du lokal erneut die Prüfsumme berechnen (nach dem gleichen Verfahren, wie [[local.local.project_name]] das tut, nämlich <abbr title="secure hash algorithm (engl. für sicherer Hash-Algorithmus)">SHA</abbr>-1. Ist die lokal berechnete Prüfsummer identisch mit der auf [[local.local.project_domain]] für die betreffende Datei genannte, so kannst Du sicher sein, dass die Datei den Download heil überstanden hat.
		  </p><p>
		  In den meisten Fällen, ist es nicht notwendig, die Prüfsumme zu berechnen. Jedoch ist es hilfreich bei der Klärung mancher Support-Anfragen zu Unterlagen (<q lang="de" title="Häufige Fehlermeldung im Zusammenhang mit Unterlagen">Hilfe, das gedownloadete ZIP-Archiv ist fehlerhaft!</q>). Außerdem sollten gedownloadete Programme vor der Ausführung getestet werden.
		  <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
		  <p>Weiterführende links:</p>
		  <ul class="bulleted">
		  <li><a href="http://de.wikipedia.org/wiki/Sicherer_Hash-Algorithmus" target="_blank">http://de.wikipedia.org/wiki/Sicherer_Hash-Algorithmus</a></li>
		</ul>
		</dd>
	<dt><a id="faq316" name="faq316"></a>Wie kann ich (unter Windows) die Prüfsumme einer Datei bestimmen?</dt>
		<dd>
		  <p>
		  Windows selbst enthält keine Funktion, um Prüfsummen zu bilden. Jedoch gibt es freie Software für diesen Zweck. Ein einfaches Programm, welches nur entpackt, nicht installiert werden muss, ist <a href="http://www.paehl.de/german.php#dpasha" target="_blank">http://www.paehl.de/german.php#dpasha</a>.	Frei und plattformunabhängig ist <a href="http://www.jonelo.de/java/jacksum/index_de.html" target="_blank">Jacksum</a>.  <a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
		</dd>
  </dl>
  
  <h4><a name="faq4" id="faq4"></a>Erweiterungen zu [[local.local.project_domain]]</h4>
  <dl>
     <dt><a name="faq402" id="faq402"></a>Was ist die Vernetzung</dt>
	  <dd>
	    <p>
	    Die Funktion "Vernetzung" zeigt Dir, in welcher Verbindung Du zu anderen Usern stehst. Wenn Ihr Euch auch nicht direkt kennt, gibt es möglicherweise andere User auf Deiner Freundesliste, die diese Person kennen.
		</p><p>Um eine Anfrage zu starten muss der User also nicht auf der eigenen Freundesliste stehen. Mit einem Klick auf "Vernetzung" siehst Du, wen Du über wen kennst. Du musst dieses Feature erst freischalten.
		<a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
	  </dd>

    <dt><a name="faq401" id="faq401"></a>Was ist die [[local.local.project_name]]-Toolbar</dt>
	  <dd>
		<p>Die <a title="Zur Download-Seite der Toolbar" href="/toolbar">[[local.local.project_name]]-Toolbar</a> ist eine Erweiterung für den Firefox-Browser, die Dir auch dann Zugriff auf [[local.local.project_domain]] erlaubt, wenn Du gerade nicht auf [[local.local.project_domain]] surfst. In jedem Fall profitierst Du von einer verbesserten Navigation durch die Toolbar und Du siehst sofort, ob es neue Forenbeiträge oder Einträge in Deinem Gästebuch gibt.
		<a href="#topofpage" title="Zur&uuml;ck zum Anfang" class="totopofpage">Zum Anfang</a></p>
	  </dd>

 </dl>	
</div></div>
