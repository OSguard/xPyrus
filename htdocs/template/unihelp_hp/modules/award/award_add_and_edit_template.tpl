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

*}{errorbox caption="Fehler beim Senden"}

<form onsubmit="/index.php?mod=award&amp;method=modAward&amp;name={$name}&amp;zahl={$zahl}" method="post">
<input type="text" name='name' class="textfeld" value={$name}>
<input value="OK" type="submit" title="Aenderung uebernehmen">
</form>