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

*}{* <h2 id="pagename">{$user->getUsername()} &ndash; Persönliche Einstellungen {if $admin_mode}<span class="adminNote">(ADMIN)</span>{/if}</h2>
 *}

{if $admin_mode}
<span class="adminNote">(ADMIN)</span>
<a href="{admin_url user=$user}">zurück zum Adminbereich</a>
{else}
<div class="shadow">
<div><h3>Hilfe</h3>
  <ul class="bulleted">
	<li>Je mehr Punkte Du hast, desto mehr Features kannst Du freischalten.</li>
	<li> Klicke beim Feature Deiner Wahl auf "hinzufügen". Dafür werden Dir keine Punkte abgezogen.</li>
	<li>Einmal aktivierte Features können nicht umgetauscht werden.</li>
	<li>Du musst warten, bis Du wieder Wahlmöglichkeiten hast, um mehr Features zu aktivieren. Diese sind von der Höhe Deiner Punkte abhängig.</li>
  </ul>	
</div></div>
{/if} {* end if admin mode *}

{errorbox caption="Fehler bei der Featureauswahl"}

{* mode internal tab navigation *}
{include file="modules/usermanagement/navigation.tpl" usermanagement_tabpanemode="features"}
<div class="shadow"><div>
{if $countEnableFeatures == $countFeatures}
Du hast alle Features freigeschalten!
{elseif $user->getConfigFeatureSlots() > $countAvailableFeatures}
Du hast noch {$countAvailableFeatures} freie Wahlmöglichkeiten für neue Features.
{else}
Du hast noch <strong>{$user->getConfigFeatureSlots()}</strong> freie Wahlmöglichkeiten für neue Features.
Der nächste freie Feature-Slot wird Dir ab {$user->getNextFeaturePointLimit()} Punkten freigeschalten.

Die Anzeige für freie Feature-Slots wird nur einmal pro Stunde automatisch generiert. Hast Du also neue Slots freigeschaltet, wird dies nicht sofort angezeigt.
{/if}

{foreach from=$features item=feat}
	<fieldset>
	<legend>{translate right=$feat->getName()} (ab {$feat->getPointLevel()} Punkten)</legend>
	<form action="{user_management_url features=$user edit=$admin_mode}" method="post">
		<img src="{$feat->getPictureURL()|default:"/template/unihelp_de/css/images/sunflower.gif"}" alt="{$feat->getName}"  style="float: right;"/>
		<p style="float: left">
		{$feat->getDescription()}<br />
		<input type="hidden" id="feat{$feat->id}" name="feat{$feat->id}" />
		 {if !$feat->isAvailable()}
		 <input name="save" type="submit" disabled="disabled" value="Nicht verfügbar!" />
		 {else}
		 {if ($feat->isEnabled() && $feat->isSaved())}
		 	<input name="save" type="submit" disabled="disabled" value="Schon aktiviert!" />
		 	{else}
		 	<input name="save" type="submit" value="Hinzufügen" />
		 {/if}{/if}
		
		
		</p>
	</form>
	</fieldset>
{/foreach}

</div></div>