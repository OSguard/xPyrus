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

*}{* $Id: bbcode.tpl 5895 2008-05-03 15:38:20Z schnueptus $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/help/bbcode.tpl $ *}
<a id="topofpage" name="topofpage"></a>

<div class="shadow"><div id="faq">{* TODO: don't use id to emulate CSS of FAQ-box *}
  <h3>Wie benutze ich den Formatcode?</h3>
  <ul>
    <li><a href="#part-general">Generelles</a></li>
    <li><a href="#part-font">Schriften/Formatierungen</a></li>
    <li><a href="#part-pic">Bilder, Links und andere Inhalte</a></li>
    <li><a href="#part-other">Was in keine andere Kategorie passt</a></li>
  </ul>

  <h4><a id="part-general" name="part-general"></a>Generelles</h4>
    <p>
      Ähnlich wie bei HTML ist es wichtig, dass die Formatierungs-Umgebungen korrekt geschachtelt und abgeschlossen sind.
    </p>
    <ul class="bulleted">
    <li><samp>[b][u]Hallo[/u][/b]</samp> ist richtig, wird zu <b><u>Hallo</u></b></li>
    <li><samp>[b][u]Hallo[/b]</samp> ist <em>falsch</em>, die <code>[u]</code>-Umgebung wird nicht geschlossen
    <li><samp>[b][u]Hallo[/b][/u]</samp> ist <em>falsch</em>, die <code>[u]</code>-Umgebung wird innerhalb von <code>[b]</code> geöffnet, 
    		aber außerhalb davon geschlossen
    </ul>
    <p>
      Wer unsere Formatierungs-Buttons oben benutzt oder etwas HTML kann, wird keine Probleme haben. Kaputte Gästebücher wie bei
      der alten [[local.local.project_name]]-Version sollten damit aber ausgeschlossen sein.
    </p>
    <p>
      <em>Vertikaler Abstand</em> ist nur in Form von einzelnen Leerzeilen m&ouml;glich. Dazu ist einfach eine Zeile frei zu lassen (also zweimal <kbd>Enter</kbd>).
    </p>

  <h4><a name="part-font" id="part-font"></a>Schriften/Formatierungen</h4>

  <dl>
    <dt>Fett, Kursiv, Unterstrichen</dt>
	<dd><p>Diese Standardformatierungen kann man auf Text anwenden, indem man sie in eine <code>[b][/b]</code>-, 
		<code>[i][/i]</code>- oder <code>[u][/u]</code>-Umgebung einbaut.</p>
		<p>Beispiel: <samp>[b]fett[/b], [i]kursiv[/i], [u]unterstrichen[/u]</samp> wird zu <samp><b>fett</b>, <i>kursiv</i>, <u>unterstrichen</u></samp></p>
		<p>Diese Umgebungen können beliebig kombiniert werden. Allerdings ist es dabei wichtig, die <a href="#part-general">korrekte Reihenfolge</a> zu beachten.</p>
	</dd>
	
	<dt>Farbe</dt>
	<dd><p>Text kann man einfärben, indem man ihn in eine <code>[color][/color]</code>-Umgebung stellt.
		Die Farbe kann per Name angegeben werden, wobei die <a href="http://www.w3.org/TR/html4/types.html#h-6.5" target="_blank">16 HTML-Standardfarben</a> möglich sind,
			oder als hexadezimaler RGB-Farbwert.</p>
		<p>Beispiel: <samp>[color=purple]Gallia est omnis[/color] [color=lime]divisa in[/color] [color=#dead00]partes tres[/color]</samp> wird zu 
		   <samp><span style="color : purple;">Gallia est omnis</span> <span style="color : lime;">divisa in</span> <span style="color : #dead00;">partes tres</span></samp></p>
	</dd>
	
	<dt>Größe</dt>
	<dd><p>Die Schriftgröße kann man über eine <code>[color][/color]</code>-Umgebung festlegen.
		Die Größe wird in Punkt angegeben.</p>
		<p>Beispiel: <samp>[size=6]Gallia est omnis[/size] [size=20]divisa in[/size] partes tres</samp> wird zu 
		   <samp><span style="font-size : 6pt;">Gallia est omnis</span> <span style="font-size : 20pt;">divisa in</span> partes tres</samp></p>
	</dd>
	
	<dt>Schriftart</dt>
	<dd><p>Über eine <code>[font][/font]</code>-Umgebung kann man die Schriftart spezifizieren.</p>
		<p>Beispiel: <samp>[font=verdana]Gallia est omnis[/font] [font=couriernew]divisa in[/font] [font=comicsansms]partes tres[/font]</samp> wird zu 
		   <span style="font-family : 'Verdana';">Gallia est omnis</span> <span style="font-family : 'Courier New',mono;">divisa in</span> <span style="font-family : 'Comic Sans MS';">partes tres</span>
		   </p>
		<p><strong>Bitte beachte,</strong> dass nicht alle Nutzer alle Schriften auf ihrem PC installiert haben und der Text somit von Rechner
			zu Rechner unterschiedlich aussehen kann.</p>
	</dd>
	<dt>Ausrichtung</dt>
	<dd><p>Die Schriftausrichtung kann man über eine <code>[align][/align]</code>-Umgebung festlegen.
		Dabei sind "left" (links), "right" (rechts), "center" (mitte) und "justify" (Blocksatz) möglich.</p>
		<p>Beispiel: <samp>[align=center]Gallia est omnis[/align] divisa in partes tres</samp> wird zu 
		   <div style="text-align: center">Gallia est omnis divisa in partes tres</div>
		   </p>
	</dd>
  </dl>
  
  <h4><a name="part-pic" id="part-pic"></a>Bilder, Links und andere Inhalte</h4>
  
  <dl>
    <dt>Bilder</dt>
	<dd><p>Bilder kann man mit Hilfe einer <code>[img][/img]</code>-Umgebung einbinden. Die URL des Bildes steht dabei innerhalb der Klammern.</p>
		<p>Beispiel: <samp>[img]http://www.uni-magdeburg.de/skin/unimagdeburg/img/otto.png[/img]</samp> wird zu <img src="http://www.uni-magdeburg.de/skin/unimagdeburg/img/otto.png" alt="ein externes Bild" /></p>
		<p>Es ist <strong>wichtig</strong>, dass die URL korrekt mit http:// angeführt wird.</p>
	</dd>

    <dt>Links</dt>
	<dd><p>Um Links auf andere Seiten einzubinden, gibt es eine <code>[url][/url]</code>-Umgebung. In der einfachsten Version setzt sie nur einen 
		   Link auf eine andere Webseite; es ist jedoch auch möglich, den angezeigten Linktext zu verändern.</p>
		<p>Beispiel: <samp>[url]http://www.google.de/[/url] und [url=http://en.wikipedia.org]Wikipedia[/url]</samp> wird zu <samp>
		<a target="_blank" title="ein externer Link, der sich in einem neuen Fenster &ouml;ffnet" href="http://www.google.de/">http://www.google.de/</a> und <a target="_blank" title="ein externer Link, der sich in einem neuen Fenster &ouml;ffnet" href="http://en.wikipedia.org">Wikipedia</a></samp></p>
		<p>Es ist <strong>wichtig</strong>, dass die URL korrekt mit http:// oder https:// angeführt wird.</p>
	</dd>
	
    <dt>Zitate</dt>
	<dd><p>Zitate jeder Art kann man durch <code>[quote][/quote]</code> kenntlich machen. Dabei kann der Autor mitangegeben werden.</p>
		<p>Beispiel: <samp>[quote]Sapere aude[/quote] sowie [quote=Caesar]Gallia est omnis divisa in partes tres[/quote]</samp>
			wird zu </p><div class="entry"><blockquote><p>Sapere aude</p></blockquote><p> sowie <span class="quoteAuthor"><cite>Caesar</cite> schrieb:</span></p><blockquote><p>Gallia est omnis divisa in partes tres</p></blockquote></div>
	</dd>
	
	<dt>Listen</dt>
	<dd><p>Listen können mit einer <code>[li][*][*][/li]</code>-Struktur eingefügt werden. Auch nummerierte Listen können erstellt werden</p>
		<p>Beispiel: <pre>[list][*]Eine
  [*]lange Liste
  [*]mit [list=1]
         [*]einer
         [*]Unterliste
         [/list]
[/list]</pre> wird zu</p>
<div class="entry"><ul><li>Eine</li><li>lange Liste</li><li>mit </p><ol type="1"><li>einer</li><li>Unterliste</li></ol><p></li></ul></div>
		</dd>
	
	<dt>Horizontale Linien</dt>
	<dd><p>Eine horizontale Linie kann mit <code>[hr]</code> eingefügt werden.</p></dd>
  </dl>
  
  
  <h4><a name="part-other" id="part-other"></a></a>Was in keine andere Kategorie passt</h4>

  <dl>
    <dt>LaTeX</dt>
	<dd><p>Es ist möglich, komplizierte Formeln mit LaTeX zu setzen und in den Text einzubinden. 
		   Der Formel-Teil wird dazu in eine <code>[tex][/tex]</code>-Umgebung eingebaut.</p>
		<p>Beispiel: <samp>{literal}[tex]\arctan(\infty) = \frac{\pi}{2}[/tex]{/literal}</samp> wird zu BILD</p>
	</dd>
	
	<dt>Quelltext</dt>
	<dd><p>Um Quelltexte von Programmiersprachen einzubinden, steht eine <code>[code][/code]</code>-Umgebung zur Verfügung. Sie bietet optional auch rudimentäres Syntax-Highlighting an.</p>
		<p>Beispiel: {literal}<pre>[code=c]#include <stdio.h>

int main() {
	if (3<2<1) {
		printf("Hello, world!\n");
	}
	return 0;
}
[/code]</pre>{/literal} wird zu 
{literal}<table class="source"><tr><td class="ln"><pre>1
2
3
4
5
6
7
8
</pre></td><td class="source"><pre><span class="keyword">#include</span> &lt;stdio.h&gt;

<span class="keyword">int</span> main() {
	<span class="keyword">if</span> (3&lt;2&lt;1) {
		printf(&quot;Hello, world!\n&quot;);
	}
	<span class="keyword">return</span> 0;
}</pre></td></tr></table>{/literal}
		   </p>
		<p>Es stehen die Highlighter <abbr>c</abbr>, <abbr>c++</abbr>, <abbr>c89</abbr>, <abbr>perl</abbr>,
     		<abbr>php</abbr>, <abbr>java</abbr>, <abbr>vb</abbr>, <abbr>c#</abbr>, <abbr>ruby</abbr>,
     		<abbr>python</abbr>, <abbr>pascal</abbr>, <abbr>sql</abbr> und <abbr>mIRC</abbr> zur Verfügung.</p>
	</dd>

	<!-- an easteregg tag is hidden here, a l33t experimenter will easily find ;) -->
{*	
    <dt>1337</dt>
	<dd><p>Um besonders cool/albern/<a href="http://de.wikipedia.org/wiki/leet" target="_blank">1337</a> zu wirken, 
	       kann man Textteile in eine <code>[l33t][/l33t]</code>-Umgebung einbauen.
     	   <p>Beispiel: <samp>{literal}[l33t]Fear my leet hacker skills![/l33t]{/literal}</samp> wird zu <samp>ph34r my 133† H4xX0rz $ki11$!!!11</samp></p>
	</dd>
*}
  </dl>
</div></div>

