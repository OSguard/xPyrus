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

*}{* $Id: config_boxes_nojs.tpl 5807 2008-04-12 21:23:22Z trehn $ *}

{* <h2 id="pagename">{$user->getUsername()} &ndash; Persönliche Einstellungen</h2> *}

{if $admin_mode}
<span class="adminNote">(ADMIN)</span>
	<a href="{admin_url user=$user}">zurück zum Adminbereich</a>

{else}
<div class="shadow"><div>
<h3>Hilfe</h3>
  <ul class="bulleted">
    <li>Ordne die einzelnen Boxen so an, wie Du sie sehen willst.</li>
    <li>Die Boxen können am linken und rechten Rand beliebig verschoben, ganz ausgeblendet oder mehrfach angezeigt werden.</li>
  </ul>
</div></div>
{/if} {* end if admin mode *}

{* mode internal tab navigation *}
{include file="modules/usermanagement/navigation.tpl" usermanagement_tabpanemode="boxes"}
<div class="shadow"><div>
<br class="class" />
<form action="{user_management_url boxes=$user}" method="post">
<input type="hidden" name="nojs" value="1" />

<fieldset>
<input type="submit" name="restoreBoxes" value="Orginal Boxen-Anordnung wieder herstellen" />
</fieldset>

<fieldset><legend>Boxen links</legend>

{* display fixed login box *}
<label class="left" for="box_left_1">Box 1:</label>
<input type="text" readonly="readonly" id="box_left_1" value="Login-Box" /><br />

{counter assign="number" start=2} {* start at 2 here because of login box *}
{foreach from=$user_boxes_left item=box name=boxes_left}
<label class="left" for="box_left_{$number}">Box {$number}:</label>
<select name="boxes_left[]" id="box_left_{$number}">
    <option value="" {if '' == $box.0}selected="selected"{/if}>-- nicht belegt --</option>
    {foreach from=$all_boxes item=boxOrig}
    <option value="{$boxOrig}" {if $boxOrig == $box[0]}selected="selected"{/if}>{translate box=$boxOrig}</option>
    {/foreach}
</select> {if $box[1] > 1} {$box[1]}. Instanz{/if}<br />
{counter assign="number"}
{/foreach}
<br /><input type="submit" value="Speichern" />
</fieldset>

<fieldset><legend>Boxen rechts</legend>

{counter assign="number" start=1} {* restart counter *}
{foreach from=$user_boxes_right item=box name=boxes_left}
<label class="left" for="box_right_{$number}">Box {$number}:</label>
<select name="boxes_right[]" id="box_right_{$number}">
    <option value="" {if '' == $box.0}selected="selected"{/if}>-- nicht belegt --</option>
    {foreach from=$all_boxes item=boxOrig}
    <option value="{$boxOrig}" {if $boxOrig == $box[0]}selected="selected"{/if}>{translate box=$boxOrig}</option>
    {/foreach}
</select> {if $box[1] > 1} {$box[1]}. Instanz{/if}<br />
{counter assign="number"}
{/foreach}
<br /><input type="submit" value="Speichern" />
</fieldset>

</form>
<br class="clear" />
</div></div>
