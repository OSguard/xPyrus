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

*}{* $Id: user_search.tpl 5807 2008-04-12 21:23:22Z trehn $ *}
{if !$box_user_search_ajax}
<div class="box" id="box_user_search:1">
<h3>Suche</h3>
{dynamic}
{if $visitor->hasRight('FEATURE_BOX_REARRANGEMENT')}
<a href="{box_functions box=user_search close=true}{*/index.php?dest=box&amp;bname=user_search&amp;method=close*}" class="icon iconClose" title="Box schließen" id="user_search:{$instance}_close"><span>x</span></a>
{if !$box_user_search_minimized}
<a href="{box_functions box=user_search minimize=true}{*/index.php?dest=box&amp;bname=user_search&amp;method=minimize*}" class="icon iconMinimize" id="user_search:1_collapse" title="Box minimieren"><span>_</span></a>
{else}
<a href="{box_functions box=user_search maximize=true}{*/index.php?dest=box&amp;bname=user_search&amp;method=maximize*}" class="icon iconMaximize" id="user_search:1_collapse" title="Box maximieren"><span>O</span></a>
{/if}{* end minimized *}
{/if}{* end rights check *}
{/dynamic}
{/if}{* end ajax *}

{if !$box_user_search_minimized}
    <form id="usersearch_form" class="boxcontent" action="/userquicksearch" method="post">
    {* #username_search is used for JS Behaviour *}
    {* .suggestUser is used for JS Behaviour *}
    <input type="text" name="username_search" id="username_search" class="suggestUser" size="10" value="User" />
    
    <input type="submit" value="Suchen" />
    
    <br /><input type="radio" name="search_in" value="user" id="box_user_search_search_user" checked="checked" /><label for="box_user_search_search_user">nach&nbsp;User</label> <a class="small_link" href="/usersearch">(mehr)</a>
    <br /><input type="radio" name="search_in" value="forum" id="box_user_search_search_forum" /><label for="box_user_search_search_forum">im Forum</label> <a class="small_link" href="/forum/search">(mehr)</a>
    <br /><input type="radio" name="search_in" value="files" id="box_user_search_search_files" /><label for="box_user_search_search_files">nach&nbsp;Unterlagen</label>
    </form>
    <div id="username_search_choices" class="autocomplete"></div>
	<p class="boxcontent">
    
    </p>
{/if}{* box minimized *}

{if !$box_user_search_ajax}
</div>
{/if}
