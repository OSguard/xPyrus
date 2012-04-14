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

*}	  	<legend>Vorschau</legend>  	
	  		
		<table>
		<tr><td>Absender</td><td>{user_info_link user=$visitor}</td></tr>
		<tr><td>Empfänger</dt>
			<td>{$pmToPreview->getRecipientString()}</td></tr> 	
		<tr><td>Betreff</td>
			<td><strong>{$pmToPreview->getCaption()|default:"(kein Betreff)"}</strong></td></tr>
		<tr><td>Datum</td><td>{$pmToPreview->getTimeEntry()}</td></tr>	
		</table>
		<div id="pmcontent" class="entry">
			{$pmToPreview->getContentParsed()}
		</div>
