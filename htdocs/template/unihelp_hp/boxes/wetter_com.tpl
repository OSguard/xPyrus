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

*}{* $Id: user_search.tpl 4378 2007-05-11 10:32:49Z trehn $ *}
{if !$box_wetter_com_ajax}
<div class="box" id="box_wetter_com:1"><h3>Wetter</h3>

{dynamic}
{if $visitor->hasRight('FEATURE_BOX_REARRANGEMENT')}
<a href="{box_functions box=wetter_com close=true}{*/index.php?dest=box&amp;bname=wetter_com&amp;method=close*}" class="icon iconClose" title="Box schließen" id="wetter_com:{$instance}_close"><span>x</span></a>

{* minimize dont work with JS of wetter.com
  schnueptus (27.06.2007) *}
{* {if !$box_wetter_com_minimized}
<a href="{box_functions box=wetter_com minimize=true}" class="icon iconMinimize" id="wetter_com:1_collapse" title="Box minimieren"><span>_</span></a>
{else}
<a href="{box_functions box=wetter_com maximize=true}" class="icon iconMaximize" id="wetter_com:1_collapse" title="Box maximieren"><span>O</span></a>
{/if} *}{* end minimized *}


{/if}{* end rights check *}
{/dynamic}
{/if}{* end ajax *}

{if !$box_wetter_com_minimized}
<div class="boxcontent" id="wetter_com">
{if $smarty.const.WETTERCOM_URL != "WETTERCOM_URL"}{* smarty style check wether constant is defined *}
<script language="JavaScript" type="text/javascript" src="{$smarty.const.WETTERCOM_URL}"></script>
{/if}
</div>
{/if}{* box minimized *}

{if !$box_wetter_com_ajax}
</div>
{/if}