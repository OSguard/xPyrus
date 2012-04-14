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

*}{* $Id: confirmation.tpl 5807 2008-04-12 21:23:22Z trehn $
   $HeadURL: https://svn.unihelp.de:7823/unihelp_dev/v2/branches/xpyrus/htdocs/template/unihelp_hp/common/confirmation.tpl $ *}
<div class="shadow"><div>
<fieldset style="border: solid red 3px;">
	<legend style="color:darkred">Bestätigung '{$confirmationCause}'</legend> 

	<p>
	Du musst die Operation '{$confirmationCause}' bestätigen, weil sie 
	weitreichende Konsequenzen hat. Solltest Du Dir nicht sicher sein, was Du
	hier tust, drücke einfach Abbrechen. Wenn Du Dir sicher bist, klicke
	auf den 'Ok' Button.
	</p>
	
	<p style="color:red;"><strong>
	Konsequenzen: {$confirmationConsequences}
	</strong></p>
	<br/><br/>
	<p>
	<a href="{$confirmationOkLink}">Ok</a>
	<a href="{$confirmationCancelLink}">Abbrechen</a>
	</p>	
</fieldset>
</div></div>
