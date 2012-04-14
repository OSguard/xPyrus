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

*}{* $Id: overview.tpl 5807 2008-04-12 21:23:22Z trehn $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/groups/overview.tpl $ *}

{breadcrumbs method=$requested_method pagename='<img src="'|cat:$TEMPLATE_DIR|cat:'/images/organisationen.png" alt="Organisationen - Engagement für Studenten" />'}

<div class="shadow"><div>
<h3>Hilfe</h3>
  <ul class="bulleted">
	<li>[[local.local.help_organisation]]</li>
	<li> Durch Ausfüllen des <a href="{mantis_url foundgroup=true}" title="Organisation beantragen">Support-Formulars</a> wird unter Angabe einer kurzen Umschreibung administrativ eine Organisation erstellt, die anschließend durch einen Organisation-Administrator selbst verwaltet wird.</li>
	<li>Organisationen können nicht von Usern selbst erstellt werden.</li>
  </ul>	
</div></div>

<div class="shadow">
<div>
<table id="groups">
{foreach from=$groups item=group}
  <tr>
    <td class="image" style="padding: 5px; text-align: center; vertical-align: center">
    	<a href="{group_info_url group=$group}" >
    		<img src="{$group->getPictureFile('tiny')|default:"/images/kegel_group.png"}" alt="Logo von {$group->name}" />
    	</a>	
    </td>
    <td class="name">
		{group_info_link group=$group}
	</td>
    <td class="description">
      <span class="text">{$group->description}</span>
      <ul class="links">
        <li>
        	<a href="{group_info_url group=$group}" title="Zur Info-Seite von {$group-name}">Information</a>
        </li>
        {assign var=forum value=$group->getForum()}
        <li>| <a href="{forum_url forumId=$forum->id}">{$group->name}-Forum</a>
        </li>
      </ul>
    </td>
  </tr>
{/foreach}
</table>
</div>
</div>
<!--</div>-->
<!--</div>-->

