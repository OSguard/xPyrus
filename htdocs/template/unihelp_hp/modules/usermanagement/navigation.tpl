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

*}<div id="tabNavigation" style="margin-bottom: -37px;">
  <ul style="margin: 0px;">
      <li>
        <a{if $usermanagement_tabpanemode=='general'} class="active"{/if} href="{user_management_url profile=$user edit=$admin_mode}">Allgemeines</a></li>
      <li>
        <a{if $usermanagement_tabpanemode=='contactData'} class="active"{/if} href="{user_management_url contactData=$user edit=$admin_mode}">Kontakt</a></li>
      <li>
        <a{if $usermanagement_tabpanemode=='privacy'} class="active"{/if} href="{user_management_url privacy=$user edit=$admin_mode}">Privatsphäre</a></li>
      <li>
        <a{if $usermanagement_tabpanemode=='courses'} class="active"{/if} href="{user_management_url courses=$user edit=$admin_mode}">F&auml;cher/Studium</a></li>
      <li>
        <a{if $usermanagement_tabpanemode=='friends'} class="active"{/if} href="{user_management_url friendlist=$user edit=$admin_mode}">Beziehungen</a></li>  
      <li>
        <a{if $usermanagement_tabpanemode=='features'} class="active"{/if} href="{user_management_url features=$user edit=$admin_mode}">Features</a></li>
      {if $visitor->hasRight('FEATURE_BOX_REARRANGEMENT') || $admin_mode}
      <li>
        <a{if $usermanagement_tabpanemode=='boxes'} class="active"{/if} href="{user_management_url boxes=$user edit=$admin_mode}">Boxen</a></li>
      {/if}{* end box available *}
  </ul>
  <br style="clear: both;" />
</div>
