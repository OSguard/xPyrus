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
<h3>Bewertung schreiben (mit Punktgewinn)</h3>
<form action="{course_url courseFile=$coursefile}" method="post" id="courserating">
<fieldset>
<input name="id" id="id" value="{$coursefile->id}" type="hidden" />
<input name="method" value="addCourseFileRating" type="hidden" />
<input name="mod" value="courses" type="hidden" />
<table id="course-rating-all">
<thead>
 <tr>
 <th>Beschreibung</th>
 <th><img src="/images/bewertungen/course_1.png" alt="1" title="{course_rating_desc rating=1}" /></th>
 <th><img src="/images/bewertungen/course_2.png" alt="2" title="{course_rating_desc rating=2}" /></th>
 <th><img src="/images/bewertungen/course_3.png" alt="3" title="{course_rating_desc rating=3}" /></th>
 <th><img src="/images/bewertungen/course_4.png" alt="4" title="{course_rating_desc rating=4}" /></th>
 <th><img src="/images/bewertungen/course_5.png" alt="5" title="{course_rating_desc rating=5}" /></th>
 <th><img src="/images/bewertungen/course_6.png" alt="6" title="{course_rating_desc rating=6}" /></th>
 </tr>
</thead>
<tbody>
{foreach item=cat from=$coursefileratingcategories}
	{assign var="cat_id" value=$cat->id} {* use variable here to save costly object access *}
	<tr>
	{if $cat->getType() == $smarty.const.FORM_ELEMENT_RANGE}
		<td><label for="rating{$cat_id}" title="{course_rating_desc rating=$val}" >{translate rating_cat=$cat->name}:</label></td>
		{section start=$cat->getTypeParameter(0) loop=$cat->getTypeParameter(1)+1 name=val}
		{assign var="val" value=$smarty.section.val.index}
		<td><input type="radio" name="rating{$cat_id}" value="{$val}" title="{course_rating_desc rating=$val}" /></td>
		{/section}
	{elseif $cat->getType() == $smarty.const.FORM_ELEMENT_TEXT}
		<td><label for="rating{$cat_id}">{translate rating_cat=$cat->name}:</label></td>
		<td colspan="6"><textarea name="rating{$cat_id}" rows="4" cols="60" style="width:90%"></textarea></td>
	{/if}
	</tr>
{/foreach}
</tbody>
</table>
<input type="submit" name="rate" value="Bewerten" />
</fieldset>
</form>
</div></div>
