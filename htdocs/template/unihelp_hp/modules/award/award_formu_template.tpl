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

*}<form onsubmit="/index.php?mod=award&amp;method=formu&amp;person={$person}&amp;rank={$rank}&amp;name={$name}&amp;zahl={$zahl}&amp;personid={$personid}&amp;id={$id}" method="post">
<input type="text" name='name' class="textfeld" value='{$name}' />
<input type="text" name='person' class="textfeld" value='{$person}' />
<input type="text" name='rank' class="textfeld" value='{$rank}' />
<input value="OK" type="submit" title="Aenderung uebernehmen" />
</form>