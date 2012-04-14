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

*}<ul class="boxcontent vertical list-user" id="user-online-list">
			{foreach from=$box_user_online_users item=user}
			{if $user->getGender() == 'm'}
				{assign var="gender" value="male"}
			{elseif $user->getGender() == 'f'}
				{assign var="gender" value="female"}
			{else}
				{assign var="gender" value="gray"}
			{/if}
	      	{if $gender != 'gray'}
				{assign var="flirt_status" value=$user->getFlirtStatus()}
			{else}
				{assign var="flirt_status" value=""}
			{/if}
			<li class="{$gender|cat:$flirt_status}">{user_info_link user=$user truncate=16}
              {if $user->getStudyPathsObj() != null}
                {assign var="studyPath" value=$user->getStudyPathsObj()}
			     (<abbr title="{$studyPath[0]->getName()}">{$studyPath[0]->getNameShort()}</abbr>)
			  {/if}
			</li>  
		{/foreach}
	</ul>
