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

*}{* $Id: edit_categories.tpl 5807 2008-04-12 21:23:22Z trehn $ *}

{errorbox var="blog_errors" caption="Fehler beim Speichern"}

<div id="content" class="fullcontent">

{include file="modules/blogadvanced/admin_menu.tpl"}
<div class="shadow"><div>

<ul>
  {foreach from=$blog_categories item=cat}
  	<li><a href="{blog_url owner=$blog_owner admin="category"}/{$cat->id}">{$cat->name}</a> bearbeiten</li>
  {/foreach}
  <li><a href="{blog_url owner=$blog_owner admin="category"}">Neue Kategorie</a></li>
</ul>

<form action="{blog_url owner=$blog_owner admin="category"}" method="post">
{if $blog_editcategory}<input name="cat_id" type="hidden" value="{$blog_editcategory->id}" />{/if}

{if $blog_errors.missingFieldsObj.cat_name}<span class="missing">{/if}
<label for="cat_name">Kategorie-Name</label><input type="text" id="cat_name" name="cat_name" value="{$blog_editcategory->name}" />
{if $blog_errors.missingFieldsObj.cat_name}</span>{/if}

  <input name="cat_submit" value="Abschicken" type="submit" />
</form>
<br class="clear" />
</div></div>{* end shadow *}

</div>{* end div content *}
