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

<!-- TODO: ability to edit POINT_SOURCES_FLOW_MULTIPLICATOR property -->

<div class="shadow"><div>
<h3>##edit_point_sources_editChange##</h3>
<fieldset>
<legend>##edit_point_sources_manage##</legend>
 <form action="/index.php?mod=i_am_god&amp;method=editPointSources" method="post">
    {foreach from=$pointsources item=ps}
    <p>{$ps->getName()}: <br />
    <label for="pssum{$ps->id}">##edit_point_sources_levelPoints##</label><input type="text" id="pssum{$ps->id}" name="pssum{$ps->id}" value="{$ps->getPointsSum()}" /><br />
    <label for="psflow{$ps->id}">##edit_point_sources_economyPoints##</label><input type="text" id="psflow{$ps->id}" name="psflow{$ps->id}" value="{$ps->getPointsFlow()}" /><br />
    </p>
    {/foreach}
    <input type="submit" name="changePointSource" value="##change##" />
 </form>

</fieldset>

</div>
</div>
