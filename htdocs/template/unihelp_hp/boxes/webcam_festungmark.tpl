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

*}{* $Id: webcam_festungmark.tpl 5743 2008-03-25 19:48:14Z ads $ *}
{if !$box_webcam_festungmark_ajax}
<div class="box" id="box_webcam_festungmark:1"><h3>Webcam FestungMark</h3>

{dynamic}
{if $visitor->hasRight('FEATURE_BOX_REARRANGEMENT')}
<a href="{box_functions box=webcam_festungmark close=true}{*/index.php?dest=box&amp;bname=webcam_festungmark&amp;method=close*}" class="icon iconClose" title="Box schließen" id="webcam_festungmark:{$instance}_close"><span>x</span></a>

{if !$box_webcam_festungmark_minimized}
<a href="{box_functions box=webcam_festungmark minimize=true}" class="icon iconMinimize" id="webcam_festungmark:1_collapse" title="Box minimieren"><span>_</span></a>
{else}
<a href="{box_functions box=webcam_festungmark maximize=true}" class="icon iconMaximize" id="webcam_festungmark:1_collapse" title="Box maximieren"><span>O</span></a>
{/if}{* end minimized *}


{/if}{* end rights check *}
{/dynamic}
{/if}{* end ajax *}

{if !$box_webcam_festungmark_minimized}
<div class="boxcontent" id="webcam_festungmark">
  {* Reload every n seconds? *}
  {* Link goes to page with webcam pictures *}
  <a href=""><img src="/webcam/current_small.jpg" border="0"></a>
</div>
{/if}{* box minimized *}

{if !$box_webcam_festungmark_ajax}
</div>
{/if}
