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
<h3>Einstellung der Unterlagen-Subvention</h3>
<form action="/index.php?dest=module&amp;mod=courses&amp;method=adminSubsidies" method="post">
<fieldset><legend>Unterlagen-Subvention</legend>
<input type="checkbox" id="subvention_active" {if $subsidies.enabled}checked="checked"{/if}/>
<label for="subvention_active">Subventionen sind aktiv</label><br />
<label for="subvention">H&ouml;he der Subvention</label>
<input id="subvention" type="text" value="{$subsidies.subsidy}" />
(für den Käufer ergeben sich Kosten von max([Preis der Unterlage] -[H&ouml;he der Subvention],0) Einheiten beim Download)
<br />
<label for="dlnumber">Bis zu dieser Download-Zahl einer Unterlage</label>
<input id="dlnumber" type="text" value="{$subsidies.maxDownloadNumber}" /><br />
<label for="unumber">Bis zu dieser User-Zahl im Fach</label>
<input id="unumber" type="text" value="{$subsidies.maxUserNumber}" /><br />
<label for="fnumber">Bis zu dieser Unterlagen-Zahl im Fach</label>
<input id="fnumber" type="text" value="{$subsidies.maxFileNumber}" /><br />
<input type="submit" name="subsidy_submit" value="Subvention festlegen" />
</fieldset>
</form>
</div></div>