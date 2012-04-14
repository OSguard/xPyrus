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

*}{* $Id: edit_misc.tpl 5807 2008-04-12 21:23:22Z trehn $ *}

<div id="content" class="fullcontent">

{errorbox var="blog_errors" caption="Fehler beim Speichern"}

{include file="modules/blogadvanced/admin_menu.tpl"}
<div class="shadow"><div>

<form action="{blog_url owner=$blog_owner admin="misc"}" method="post">
{if $blog_errors.missingFieldsObj.blog_title}<span class="missing">{/if}
<label for="blog_title">Titel</label>
	<input type="text" id="blog_title" name="blog_title" value="{$blog_model->getTitle()}" size="100" /><br />
{if $blog_errors.missingFieldsObj.blog_title}</span>{/if}

{if $blog_errors.missingFieldsObj.blog_subtitle}<span class="missing">{/if}
<label for="blog_subtitle">Untertitel</label>
	<input type="text" id="blog_subtitle" name="blog_subtitle" value="{$blog_model->getSubtitle()}" size="100" /><br />
{if $blog_errors.missingFieldsObj.blog_subtitle}</span>{/if}
<input name="misc_submit" value="Abschicken" type="submit" />
</form>

<br class="clear" />
</div></div>{* end shadow *}

</div>{* end div content *}
