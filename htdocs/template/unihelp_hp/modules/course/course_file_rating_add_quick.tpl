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

*}<div class="box shadow"><div>
<h3>Schnell-Bewertung</h3>
Wie hilfreich fandest Du die Unterlage?
{* use form here due to PHP convention to submit some parameter via POST *}
	<form action="/index.php?mod=courses&amp;dest=module&amp;method=addCourseFileRating" method="post">
		<fieldset>
		<input name="id" id="id" value="{$coursefile->id}" type="hidden" />
		<input type="submit" name="quickvote" value="1" title="{course_rating_desc rating=1}" style="background: url('/images/bewertungen/course_1.png') center no-repeat; width: 60px; height: 20px; font-size: 0px;"/>
		<input type="submit" name="quickvote" value="2" title="{course_rating_desc rating=2}" style="background: url('/images/bewertungen/course_2.png') center no-repeat; width: 60px; height: 20px; font-size: 0px;"/>
		<input type="submit" name="quickvote" value="3" title="{course_rating_desc rating=3}" style="background: url('/images/bewertungen/course_3.png') center no-repeat; width: 60px; height: 20px; font-size: 0px;"/>
		<input type="submit" name="quickvote" value="4" title="{course_rating_desc rating=4}" style="background: url('/images/bewertungen/course_4.png') center no-repeat; width: 60px; height: 20px; font-size: 0px;"/>
		<input type="submit" name="quickvote" value="5" title="{course_rating_desc rating=5}" style="background: url('/images/bewertungen/course_5.png') center no-repeat; width: 60px; height: 20px; font-size: 0px;"/>
		<input type="submit" name="quickvote" value="6" title="{course_rating_desc rating=6}" style="background: url('/images/bewertungen/course_6.png') center no-repeat; width: 60px; height: 20px; font-size: 0px;"/>
		</fieldset>
	</form>
</div></div>
