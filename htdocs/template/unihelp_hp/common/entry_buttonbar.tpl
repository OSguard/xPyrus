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

*}{* $Id: entry_buttonbar.tpl 5807 2008-04-12 21:23:22Z trehn $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/common/entry_buttonbar.tpl $ *}
  <h4>Formatierungen</h4>
    <h5>Schriftoptionen</h5>
        <p>
        <a href="#postenanker" onclick="bold()" title="Fettdruck">
        <img src="/images/symbols/bold.gif" alt="Fett" /></a>
        <a href="#postenanker" onclick="italicize()" title="Kursiv">
        <img src="/images/symbols/italicize.gif" alt="Kursiv" /></a>
        <a href="#postenanker" onclick="underline()" title="Unterstrichen">
        <img src="/images/symbols/underline.gif" alt="Unterstrichen" /></a>
        <a href="#postenanker" onclick="hr()" title="Horizontale Linie">
        <img src="/images/symbols/hr.gif" alt="Horizontale Linie" /></a>
        <a href="#postenanker" onclick="size()" title="Schriftgr&ouml;&szlig;e">
        <img src="/images/symbols/size.gif" alt="Schriftgr&ouml;&szlig;e" /></a>
        <a href="#postenanker" onclick="showcode()" title="Vorformatierter Text">
        <img src="/images/symbols/pre.gif" alt="Vorformatierter Text" /></a>
        </p>
    <h5>Ausrichtung</h5>
    	<p>
    	<a href="#postanker" onclick="align('left')" title="Text Links ausrichten">
    		<img src="/images/symbols/align_left.png" alt="Text Links ausrichten" />
    	</a>
    	<a href="#postanker" onclick="align('center')" title="Text center ausrichten">
    		<img src="/images/symbols/align_center.png" alt="Text center ausrichten" />
    	</a>
    	<a href="#postanker" onclick="align('right')" title="Text Rechts ausrichten">
    		<img src="/images/symbols/align_right.png" alt="Text Rechts ausrichten" />
    	</a>
    	<a href="#postanker" onclick="align('justify')" title="Text als Blocksatz ausrichten">
    		<img src="/images/symbols/align_justify.png" alt="Blocksatz" />
    	</a>
    	</p>   
    <h5>Zusätze</h5>
         <p>
          <a href="#postenanker" onclick="image()" title="Bild einf&uuml;gen">
          <img src="/images/symbols/img.gif" alt="Bild einf&uuml;gen" /></a>
          {*<a href="#postenanker" onclick="emai1()" title="E-Mail einf&uuml;gen">
          <img src="/images/symbols/email2.gif" alt="E-Mail einf&uuml;gen" /></a>*}
          <a href="#postenanker" onclick="quote()" title="Zitat einf&uuml;gen">
          <img src="/images/symbols/quote2.gif" alt="Zitat einf&uuml;gen" /></a>
          <a href="#postenanker" onclick="list1()" title="Liste einf&uuml;gen">
          <img src="/images/symbols/list.gif" alt="Liste einf&uuml;gen" /></a>
          <a href="#postenanker" onclick="list2()" title="nummerierte Liste einf&uuml;gen">
          <img src="/images/symbols/list2.png" alt="nummerierte Liste einf&uuml;gen" /></a>
          <a href="#postenanker" onclick="showcode2()" title="Programm Code">
        	<img src="/images/symbols/code.png" alt="Programm Code" /></a>
          <a href="#postenanker" onclick="tex()" title="LateX einf&uuml;gen">
           <img src="/images/symbols/tex.png" alt="LaTeX einf&uuml;gen" /></a>
          </p>
    {* [[help.entry_options.font-family]] *}