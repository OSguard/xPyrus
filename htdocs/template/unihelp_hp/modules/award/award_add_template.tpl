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

*}<div class="shadow"><div>
<h3>Award hinzufuegen</h3>
{errorbox caption="Fehler beim Senden"}
<form action="/index.php?mod=award&amp;method=addAward" method="post">
<label for='name'>Name</label>
<input type="text" id='name' name='name' class="textfeld" />
<br>
<label for='icon'>Icon-Link</label><input id='icon' type="text" name='icon' class="textfeld" />
<br>
<label for='link'>Event-Link</label><input type="text" id='link' name='link' class="textfeld" />
<br>
<br>
<input value="OK" type="submit" title="Aenderung uebernehmen" />
</form>
<br>
<br>
</div></div>
<a href="javascript:history.back()">Zurueck</a>