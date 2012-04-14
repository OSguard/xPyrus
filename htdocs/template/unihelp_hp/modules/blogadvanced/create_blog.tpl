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

*}{* $Id: create_blog.tpl 5898 2008-05-04 19:32:32Z schnueptus $ *}
<div id="content">

<form action="/index.php?mod=blogadvanced&amp;method=createUserBlog" method="post">
Ein Blog auf [[local.local.project_domain]] zu bekommen ist ganz einfach.

{errorbox var="blog_errors" caption="Fehler beim Erstellen"}

<ol id="create_steps">
<li><h4>Titel f&uuml;r Dein Blog festlegen</h4>
	Diese Einstellung kannst Du sp&auml;ter noch &auml;ndern.<br />
	{assign var="defaultTitle" value="[[local.local.project_name]]-Blog von "|cat:$visitor->getUsername()}
	<label for="blog_title" {if $blog_errors.missingFieldsObj.blog_title}style="color: red; font-weight:bold;"{/if}>Titel</label>
		<input id="blog_title" name="blog_title" value="{if $blog_model}{$blog_model->getTitle()}{else}{$defaultTitle}{/if}" size="60" /><br />
	<label for="blog_subtitle" {if $blog_errors.missingFieldsObj.blog_subtitle}style="color: red; font-weight:bold;"{/if}>Untertitel</label>
		<input id="blog_subtitle" name="blog_subtitle" value="{if $blog_model}{$blog_model->getSubtitle()}{/if}" size="60" />
	<br class="clear" />
</li>
<li>Formular <input type="submit" name="create_submit" value="abschicken" class="nomargin" /></li>
</ol>
</form>

</div>
