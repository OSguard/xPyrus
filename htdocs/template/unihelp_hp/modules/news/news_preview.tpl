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

*}		<div class="shadow"><div>
			<a name="post"></a>
			<h3>News Vorschau</h3>
			<h4>{$entryToEdit->getCaption()}</h4>
			<p title="  wird angezeigt von {$entryToEdit->getStartDate()|unihelp_strftime} bis {$entryToEdit->getEndDate()|unihelp_strftime}">
				eingetragen von der {group_info_link group=$entryToEdit->getGroup() show_group_title=true}, {$entryToEdit->getTimeEntry()|unihelp_strftime} </p>			
			<div class="entry"  style="margin:5px 0 10px; text-align: justify;">{$entryToEdit->getContentParsed()}</div>
		</div></div>
