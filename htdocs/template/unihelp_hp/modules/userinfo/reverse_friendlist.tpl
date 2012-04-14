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

*}{* $Id: reverse_friendlist.tpl 5896 2008-05-03 15:59:07Z schnueptus $
     $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/modules/userinfo/reverse_friendlist.tpl $ *}

{* <h2 id="pagename">Umgekehrte Freundesliste</h2> *}

<div class="shadow"><div>
<h3>Hilfe</h3>
  <ul class="bulleted">
	<li>Hier kannst Du sehen, welche [[local.local.project_name]]-User Dich auf ihrer Freundesliste haben.</li>
  </ul>	
</div></div>

<div class="shadow"><div style="padding: 10px;">
<h3>
Meine Freunde, die mich auch auf ihrer Freundesliste haben
</h3>
{foreach item=friend from=$reversefriends}
{assign var=fid value=$friend->id} 
{if $myfriends[$fid]}
<p class="compact">
    <a href="{user_info_url user=$friend}">
        <img src="{userpic_url tiny=$friend}" alt="{$friend->getUsername()}" /><br />
        {$friend->getUsername()}
    </a>   
</p>
{assign var=friend1 value= true}
{/if}
{/foreach}
{if !$friend1}
<p>Du bist anonym</p>
{/if}
<br class="clear" />
</div></div>

<div class="shadow"><div style="padding: 10px;">
<h3>
User die mich auf der Freundesliste haben, aber ich sie nicht
</h3>
{foreach item=friend from=$reversefriends}
{assign var=fid value=$friend->id} 
{if !$myfriends[$fid]}
<p class="compact">
    <a href="{user_info_url user=$friend}">
        <img src="{userpic_url tiny=$friend}" alt="{$friend->getUsername()}" /> <br />
        {$friend->getUsername()}
    </a>
	{assign var=friend2 value= true}
</p>
{/if}
{/foreach}
{if !$friend2}
<p>Du kennst alle Deine Freunde</p>
{/if}
<br class="clear" />
</div></div>

<div class="shadow"><div style="padding: 10px;">
<h3>
Meine Freunde, die mich nicht auf ihrer Freundesliste haben</h3>
	{foreach item=friend from=$myfriends}
		{assign var=fid value=$friend->id}
		{if !$reversefriends[$fid]}
		<p class="compact">
		    <a href="{user_info_url user=$friend}">
		        <img src="{userpic_url tiny=$friend}" alt="{$friend->getUsername()}" /><br /> 
		        {$friend->getUsername()}
		    </a>
		</p>
		{assign var=friend3 value= true}
		{/if}
	{/foreach}
{if !$friend3}	
<p>Jeder Deiner Freunde hat Dich auch als Freund</p>
{/if}
<br class="clear" />
</div></div>
