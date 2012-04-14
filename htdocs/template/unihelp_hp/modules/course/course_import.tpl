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

*}
<div class="shadow" class="box" style="float: left; width: 100%;"><div>
    <h3>Faecher</h3>
<form method="post">
<ul class="import">
{foreach from=$courses item="c" key="key"}
    {counter assign="cid"}
    <li><label for="course{$cid}">{$c->name}</label><input type="checkbox" name="course-{$key}" id="course{$cid}" checked="checked" /></li>
{foreachelse}
    <li>Keine Faecher zum Importieren gefunden</li>
{/foreach}
</ul>
<input type="submit" value="Speichern" name="save" />
</form>
</div></div>