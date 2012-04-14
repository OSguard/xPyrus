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

*}{* $Id: entry_smileys.tpl 5807 2008-04-12 21:23:22Z trehn $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/common/entry_smileys.tpl $ *}
   {if !$smileys_only}
        <h5>Schriftfarbe</h5>
        <select name="color" onchange="showcolor(this.options[this.selectedIndex].value)">
		  <!-- new colors == 16 colors as defined by w3c: http://www.w3.org/TR/2003/CR-css3-color-20030514/#html4 -->
		  <option value="black" selected="selected" style="background-color: black;">Schwarz</option>
		  <option value="silver" style="background-color: silver;">Silber</option>
		  <option value="gray" style="background-color: gray;">Grau</option>
		  <option value="white" style="background-color: white;">Weiß</option>
		  <option value="maroon" style="background-color: maroon;">Kastanienbraun</option>
		  <option value="red" style="background-color: red;">Rot</option>
		  <option value="purple" style="background-color: purple;">Purpur</option>
		  <option value="fuchsia" style="background-color: fuchsia;">Fuchsie</option>
		  <option value="green" style="background-color: green;">Grün</option>
		  <option value="lime" style="background-color: lime;">Limone</option>
		  <option value="olive" style="background-color: olive;">Olive</option>
		  <option value="yellow" style="background-color: yellow;">Gelb</option>
		  <option value="navy" style="background-color: navy;">Marinblau</option>
		  <option value="blue" style="background-color: blue;">Blau</option>
		  <option value="teal" style="background-color: teal;">Teal</option>
		  <option value="aqua" style="background-color: aqua;">Aqua</option>
        </select>

    <h5>Schriftgröße</h5>
        <select name="color" onchange="showfontsize(this.options[this.selectedIndex].value)" style="height: 14pt;">
		  <option value="7" style="font-size: 7pt;">Klein</option>
		  <option value="10" selected="selected" style="font-size: 10pt;">Normal</option>
		  <option value="16" style="font-size: 16pt;">Groß</option>
		  <option value="20" style="font-size: 20pt;">Größer</option>
		  <option value="28" style="font-size: 28pt;">Riesig</option>
        </select>

    <h5>Schriftfamilie</h5>
	    <select name="font-family" onchange="showfontfamily(this.options[this.selectedIndex].value)">
		  <option value="Verdana" selected="selected" style="font-family: Verdana,sans-serif;">Verdana</option>
		  <option value="Helvetica" style="font-family: Helvetica,sans-serif;">Helvetica</option>
		  <option value="Impact" style="font-family: Impact,sans serif;">Impact</option>
		  <option value="LucidaGrande" style="font-family: 'Lucida Grande',sans serif;">Lucida Grande</option>
		  <option value="Tahoma" style="font-family: Tahoma,sans serif;">Tahoma</option>
		  <option value="Garamond" style="font-family: Garamond,serif;">Garamond</option>
		  <option value="Georgia" style="font-family: Georgia,serif;">Georgia</option>
		  <option value="TimesNewRoman" style="font-family: 'Times New Roman',serif;">Times New Roman</option>
		  <option value="CourierNew" style="font-family: 'Courier New',mono;">Courier New</option>
		  <option value="ComicSansMS" style="font-family: 'Comic Sans MS',sans serif;">Comic Sans</option>
		  <option value="ScriptC" style="font-family: ScriptC,serif;">ScriptC</option>
	    </select>
        
        <h5>Formatcode</h5>
		<a href="/help/formatcode" title="Wie benutze ich den Formatcode?">Hilfe zum Formatcode</a>
    {/if} {* !smileys_only *}
        <h5 class="distance-top">Smileys</h5>
        <p>
        {* mal einen gescheiten loop einbauen, der die smileys einfuegt, die der User dort haben will *}
        <a href="#postenanker" onclick="return AddTextInternal(':)');" title=":)">
        <img src="/images/smileys/smile.gif" alt="" /></a>
        <a href="#postenanker" onclick="return AddTextInternal(':D');" title=":D">
        <img src="/images/smileys/biggrin.gif" alt="" /></a>
        <a href="#postenanker" onclick="return AddTextInternal(':-D');" title=":-D">
        <img src="/images/smileys/smileD.gif" alt="" /></a>
        <a href="#postenanker" onclick="return AddTextInternal('8-)');" title="8-)">
        <img src="/images/smileys/cool.gif" alt="" /></a>
        <a href="#postenanker" onclick="return AddTextInternal(':))');" title=":))">
        <img src="/images/smileys/laugh.gif" alt="" /></a>
        <a href="#postenanker" onclick="return AddTextInternal(':O');" title=":0">
        <img src="/images/smileys/bubble.gif" alt="" /></a>
        <a href="#postenanker" onclick="return AddTextInternal('I)');" title="I)">
        <img src="/images/smileys/censored.gif" alt="" /></a>
        <a href="#postenanker" onclick="return AddTextInternal(':knuddel:');" title=":knuddel:">
        <img src="/images/smileys/knuddel.gif" alt="" /></a>
        <a href="#postenanker" onclick="return AddTextInternal(':wave:');" title=":wave:">
        <img src="/images/smileys/wave.gif" alt="" /></a>
        <a href="#postenanker" onclick="return AddTextInternal('?(');" title="?(">
        <img src="/images/smileys/confused.gif" alt="" /></a>
        <a href="#postenanker" onclick="return AddTextInternal('8o');" title="8o">
        <img src="/images/smileys/eek.gif" alt="" /></a>
        <a href="#postenanker" onclick="return AddTextInternal(':hug:');" title=":hug:">
        <img src="/images/smileys/hug.gif" alt="" /></a>
        </p>

        <a id="smiley_link" href="/smileys?nojs=1" target="smilies" title="Alle Smilies in einem neuen Fenster sehen">alle Smileys</a>