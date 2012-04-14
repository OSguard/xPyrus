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

*}{* $Id: edit_visibility.tpl 5807 2008-04-12 21:23:22Z trehn $ *}

<div id="content" class="fullcontent">
<h4>Blog bearbeiten</h4>

<form action="{blog_url admin="visibility" owner=$blog_owner}" method="post">
<label for="blog_visible">Blog von {$blog_owner->getName()} ist sichtbar</label>
	<input id="blog_visible" name="blog_visible" type="checkbox" {if !$blog_model->isInvisible()}checked="checked"{/if} /><br />
<input name="blog_submit" value="Abschicken" type="submit" />
</form>

</div>
