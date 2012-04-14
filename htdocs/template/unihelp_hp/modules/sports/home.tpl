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
<a id="topofpage" name="topofpage"></a>

<div class="shadow bet"><div>
<h3>Fußball-Tippspiele</h3>
<ul class="gamelist">
{foreach from=$tournaments item="t"}
	<li>
	<h4>{$t->getName()}</h4>
	<p>{$t->getDescription()}</p>
	<ul class="sports-action">
        {if $visitor->isRegularLocalUser()}
            <li><a href="{sports_url soccerBet=$t}">tippen</a></li>
        {/if}
		<li><a href="{sports_url soccerBetRanking=$t}">Rangliste</a></li>
        {if $visitor->hasRight('SOCCER_BET_ADMIN')}
            <li><a href="{sports_url soccerBetAdmin=$t}">administrieren</a></li>
        {/if}
	</ul>
    <br class="clear" />
	</li>
{/foreach}
</ul>
<img src="/images/tippspiel/graphics/tippspiel.png" />
<br class="clear" />
</div></div>


