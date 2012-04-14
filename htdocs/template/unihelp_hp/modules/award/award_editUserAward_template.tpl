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
<h3>Rang editieren</h3>
{errorbox caption="Fehler beim Senden"}
<b>Award: </b><i>{$name}</i> 
<br><br>


<form action="/index.php?mod=award&amp;method=editUserAward&amp;id={$UserAward->id}" method="post">
Person: <input type="text" name='person' class="textfeld" value={$UserAward->getUserName()}> <br>
Rang: <input type="text" name='rank' class="textfeld" value={$UserAward->getRank()}>
<br />
<br />
<input value="OK" type="submit" title="Aenderung uebernehmen">
</form>
<br />
<br />
</div></div>
<a href="javascript:history.back()">Zurueck</a>