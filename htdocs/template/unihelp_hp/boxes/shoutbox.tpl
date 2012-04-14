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

*}{* $Id: shoutbox.tpl 5807 2008-04-12 21:23:22Z trehn $
	 $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/boxes/shoutbox.tpl $ *}
<!--<div class="box">
<h3>Shoutbox</h3>-->
{if !$box_shoutbox_ajax}
<div class="box" id="box_shoutbox:1">
<h3>Shoutbox</h3>
{dynamic}
{if $visitor->hasRight('FEATURE_BOX_REARRANGEMENT')}
<a href="{box_functions box=shoutbox close=true}{*/index.php?dest=box&amp;bname=shoutbox&amp;method=close*}" class="icon iconClose" title="Box schließen" id="shoutbox:1_close"><span>x</span></a>
{if !$box_shoutbox_minimized}
<a href="{box_functions box=shoutbox minimize=true}{*/index.php?dest=box&amp;bname=shoutbox&amp;method=minimize*}" class="icon iconMinimize" id="shoutbox:1_collapse" title="Box minimieren"><span>_</span></a>
{else}
<a href="{box_functions box=shoutbox maximize=true}{*/index.php?dest=box&amp;bname=shoutbox&amp;method=maximize*}" class="icon iconMaximize" id="shoutbox:1_collapse" title="Box maximieren"><span>O</span></a>
{/if}{* end minimized *}
{/if}{* end rights check *}
{/dynamic}
{/if}{* end ajax *}

{if !$box_shoutbox_minimized}

<div class="boxcontent">
<div id="shoutbox">
  {* copied by ajax *}
  <div style="border-bottom: 1px solid #cfcfcf; overflow:hidden; display:none; padding-bottom: 0px;" id="empty_shout_message">
     <a style="margin:2px; margin-left:5px;  float:left;" href="/user/">
     	<img width="24" src="/images/kegel-u_tiny.png" alt="" style="margin: 2px; border: #cfcfcf 1px solid; padding: 1px; margin-right: .5em;" /> 
     </a>
     
   	<span id="empty_shout_text"></span>
  </div>

{capture name=shoutbox}
{foreach from=$shout_items item=shout_item}
  <div style="border-bottom: 1px solid #cfcfcf; overflow:hidden; padding-bottom: 0px;">
     <a style="margin:2px; margin-left:5px; float:left;" href="/user/{$shout_item->user->username|escape:"url"}">
     	<img width="24" src="{userpic_url tiny=$shout_item->user}" alt="{$shout_item->user->username|escape:"html"}" style="margin: 2px; border: #cfcfcf 1px solid; padding: 1px; margin-right: .5em;" /> 
     </a>
     
   {if $shout_item->isMeMessage}
   		<span style="font-size: x-small;">({$shout_item->entryTime|date_format:"%H:%M:%S"})</span> <strong>{user_info_link user=$shout_item->user}</strong> {$shout_item->text}
   {else}
   		<strong>{user_info_link user=$shout_item->user}</strong>, <span style="font-size: x-small;">{$shout_item->entryTime|date_format:"%H:%M:%S"}</span>: {$shout_item->text}
   {/if}		
  </div>
{/foreach}
{/capture}
{$smarty.capture.shoutbox}
</div>

{dynamic}
{if !$visitor->isExternal() && $visitor->isLoggedIn()}
{if !$visitor->hasRight('POST_SHOUTBOX')}
	Funktion gesperrt.
{else}
<form action="/index.php?dest=box&amp;method=addToShoutbox&amp;bname=shoutbox" method="post" id="shbox_submit">
	<input type="text" id="shbox_text" name="shout_text" size="20" maxlength="160"/>
	<input type="submit" name="submit" value="Abschicken"/><span style="font-size: xx-small;">(noch <span id="shbox_char_count">160</span> Zeichen)</span>
</form>
<span id="shbox_status"></span>
{/if}
{/if}
{/dynamic}
</div>
<script type="text/javascript" src="{$TEMPLATE_DIR}/javascript/shoutbox.js"></script>
{/if}{* box minimized *}

{if !$box_shoutbox_ajax}
</div>
{/if}
