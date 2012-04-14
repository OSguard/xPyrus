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

*}{*	$Id: course_file_rating.tpl 5807 2008-04-12 21:23:22Z trehn $
	$HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/course/course_file_rating.tpl $	*}

{errorbox caption="Fehler beim Upload"}

{assign var="comments_read" value=true}
<div class="shadow" style="position: static"><div style="position: static">
{include file="modules/course/course_file_info.tpl"}
</div></div>

{dynamic}
{if $coursefile_edit}
	<div class="shadow"><div>
	<h3><a name="post">Datei bearbeiten/neue Version hochladen</a></h3>
	<form action="{course_url courseFile=$coursefile}" method="post" enctype="multipart/form-data">
	{if !$ie6}
		{include file="modules/course/course_file_add_form.tpl"}
	{else}
		{include file="modules/course/course_file_add_form_ie.tpl"}
	{/if}
	</form>
	</div></div>
{/if}
{/dynamic}

<div class="shadow" id="fileComments"><div>
<h3>Kommentare</h3>
 {foreach item=rating from=$coursefile->getRatings()}
 <p><strong>Eintrag von {user_info_link user=$rating->getAuthor()}, {$rating->getTime()}</strong></p>
 <ul class="file-comments">
    {foreach item=r key=cat from=$rating->getRatingsSingle()}
        <li>
        <em>{translate rating_cat=$cat}:</em>
       	 {if $r.category->getType() == 'range'}
           <img src="/images/bewertungen/course_{$r.rating}.png" alt="{$r.rating}" title="{course_rating_desc rating=$r.rating}" />
         {else}
           {$r.rating}&nbsp;
         {/if}
         
        </li>
    {/foreach}
    </ul>
 {foreachelse}
 <p>Bisher hat noch keiner seine Meinung gesagt.</p>
 {/foreach}
</div></div>

{dynamic}
{if $coursefile_may_rate}
	{include file="modules/course/course_file_rating_add_all.tpl"}
{/if}
{/dynamic}
