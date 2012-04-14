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

*}{* $Id: create_guest.tpl 5807 2008-04-12 21:23:22Z trehn $
   $HeadURL$ *}

<form method="post" action="{admin_url newguest=true}">
<label for="validity-period">Gültigkeitsdauer:</label><input type="text" maxlength="3" id="validity-period" name="validity-period"/> Tage<br />
<input type="submit" name="createGuest-submit" value="Gast anlegen" />
</form>

{if $guest_username != ''}
<p>User <em>{$guest_username}</em> mit Passwort <em>{$guest_password}</em> angelegt.</p>
{/if}