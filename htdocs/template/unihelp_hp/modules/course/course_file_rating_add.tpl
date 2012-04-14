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
{* {include file="banner.tpl"} *}

{if $coursefile_may_rate}

{include file="modules/course/course_file_rating_add_quick.tpl"}

{include file="modules/course/course_file_rating_add_all.tpl"}

{assign var="comments_read" value=true}
<div class="box shadow"><div>	
<h3>Datei Information</h3>
{include file="modules/course/course_file_info.tpl"}
</div></div>

{/if}
