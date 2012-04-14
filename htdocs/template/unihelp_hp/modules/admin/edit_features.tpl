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

*}
{if $editFeat}
<div class="shadow"><div>
<h3>##edit_features_edit##</h3>
<fieldset>
	<form action="/index.php?mod=i_am_god&amp;dest=module&amp;method=editFeatures" method="post">
	
	##edit_features_editFeature##: {$editFeat->getName()}<br />
	<input type="hidden" name="featId" value="{$editFeat->id}" />
    <label for="pointLevel">##edit_features_pointLevel##</label><input type="text" name="pointLevel" value="{$editFeat->getPointLevel()}" size="5" /><br />
	<label for="desc">##description##</label><input type="text" name="desc" value="{$editFeat->getDescription()}" size="50"/><br />
	<label for="desc_eng">##description## ##english##</label><input type="text" name="desc_eng" value="{$editFeat->getDescriptionEnglish()}" size="50" /><br />
	<label for="pic_url">##edit_features_iconURL##</label><input type="text" name="pic_url" value="{$editFeat->getPictureUrl()}" size="50" /><br /><br />
	<input type="submit" name="save" value="##submit##"/>
	</form>
	
</fieldset>
</div></div>
{/if}

{if $newFeat}
<div class="shadow"><div>
<h3>##edit_features_add##</h3>
<fieldset>


	<form action="/index.php?mod=i_am_god&amp;dest=module&amp;method=editFeatures" method="post">
	
	##edit_features_addFeature##: {$newFeat->getName()}<br />
	<b>##edit_features_rights##</b><br />
	<input type="hidden" name="newFeatId" value="{$newFeat->id}" />
    <label for="pointLevel">##edit_features_pointLevel##</label><input type="text" name="pointLevel" value="1" size="5" /><br />
	<label for="desc">##description##</label><input type="text" name="desc" value="{$newFeat->getDescription()}" size="50"/><br />
	<label for="desc_eng">##description## ##english##</label><input type="text" name="desc_eng" value="{$newFeat->getDescription()}" size="50" /><br />
	<label for="pic_url">##edit_features_iconURL##</label><input type="text" name="pic_url" value="" size="50" /><br /><br />
	<input type="submit" name="save" value="##submit##"/>
	</form>
	
</fieldset>
</div></div>
{/if}

<div class="shadow"><div>
<h3>##edit_features_choose##</h3>
<fieldset>


  <ul>
    {foreach from=$features item=feat}
    <li><strong>{translate right=$feat->getName()}</strong> ( {$feat->getName()} )
    <br />##edit_features_requiredPoints##: {$feat->getPointLevel()}
    <br/>{$feat->getDescription()}
    <br /><a href="/index.php?mod=i_am_god&amp;dest=module&amp;method=editFeatures&amp;featId={$feat->id}">##edit##</a>
    <br />
    </li>
    {/foreach}
  </ul>
  
<a href="/index.php?mod=i_am_god&amp;dest=module&amp;method=editFeatures&amp;addFeat=true">##edit_features_addFeatureIfPossible##</a>

</fieldset>
</div></div>

{if $nonFeatures}
<div class="shadow"><div>
<h3>##edit_features_add##</h3>
<fieldset>


  <ul>
    {foreach from=$nonFeatures item=feat}
    <li>{$feat->getName()}
    <br/>{$feat->getDescription()}
    <br /><a href="/index.php?mod=i_am_god&amp;dest=module&amp;method=editFeatures&amp;newFeatId={$feat->id}">##add##</a>
    <br />
    </li>
    {/foreach}
  </ul>
  </fieldset>
  </div></div>
{/if}  

