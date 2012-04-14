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

*}{if $visitor->equals($targetuser)}
  <div style="color: #ff0000; font-size: 200%; font-weight: bold;">
  ΓΝΩθΙ ΣΑΥΤΟΝ {* know yourself ... *}
  </div>
{else}
  <table class="clear" id="smallworld"><tr>
  {foreach item=swuser from=$sUserModels name=swusertable}
    <td class="user">
      <a href="{user_info_url user=$swuser}"><img src="{userpic_url tiny=$swuser}" alt="{$swuser->getUsername()}" /></a>
      <br />
      <span style="vertical-align:bottom;">{user_info_link user=$swuser}</span>
    </td>
    {if !$smarty.foreach.swusertable.last}
    <td class="arrow">
      {* &lt; = &gt; *}
      <img src="/images/icons/arrow_refresh.png" alt="Verbindung">
    </td>
    {/if}
  {foreachelse}
    <strong>Es existiert keine Verbindung.</strong>
  {/foreach}
  </tr></table>
{/if}
