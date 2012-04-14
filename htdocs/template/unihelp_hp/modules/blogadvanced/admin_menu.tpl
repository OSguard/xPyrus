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

*}{* $Id: admin_menu.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
<div id="tabNavigation" style="margin-bottom: -37px;">
  <ul>
	<li><a{if $admin_mode=='post'} class="active"{/if} href="{blog_url owner=$blog_owner admin="post"}">Eintrag erstellen</a></li>
	<li><a{if $admin_mode=='category'} class="active"{/if} href="{blog_url owner=$blog_owner admin="category"}">Kategorien verwalten</a></li>
	<li><a{if $admin_mode=='misc'} class="active"{/if} href="{blog_url owner=$blog_owner admin="misc"}">Dies &amp; Das</a></li>
	<li><a href="{blog_url owner=$blog_owner}">Mein Blog</a></li>
  </ul>
  <br class="clear" />
</div>
