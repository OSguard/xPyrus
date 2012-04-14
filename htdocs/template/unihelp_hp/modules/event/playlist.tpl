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
{errorbox caption="Fehler beim Senden"}

<div class="shadow"><div>
<h3>Vote Deinen Song</h3>
{if $countSongs >= 3}
 <strong>Du hast bei der Abstimmung mitgemacht, vielen Dank!<br />
 Wir sehen uns am 05.12.2007 in der Festung!</strong>
{else}
<form enctype="multipart/form-data" action="?mod=event&amp;method=playlist" method="post" >
	<fieldset>
	{if $countSongs < 3}
	<label for="artist_1">Artist</label>
	<input type="text" size="30" name="artist_1" id="artist_1"/>
    <br />
	<label for="song_1">Song</label>
	<input type="text" size="30" name="song_1" id="song_1" />
    <br />
	<hr />
	{/if}
	{if $countSongs < 2}
	<label for="artist_2">Artist</label>
	<input type="text" size="30" name="artist_2" id="artist_2"/>
    <br />
	<label for="song_2">Song</label>
	<input type="text" size="30" name="song_2" id="song_2" />
    <br />
	<hr />
	{/if}
	{if $countSongs < 1}
	<label for="artist_3">Artist</label>
	<input type="text" size="30" name="artist_3" id="artist_3"/>
    <br />
	<label for="song_3">Song</label>
	<input type="text" size="30" name="song_3" id="song_3" />
    <br />
	<hr />
	{/if}
	<input type="submit" name="save" value="Meinen Wunsch abschicken" />
	</fieldset>
</form>
{/if}
<br class="clear" />
</div></div>
