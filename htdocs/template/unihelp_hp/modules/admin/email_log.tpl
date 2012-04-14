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

*}<form action="/index.php?mod=i_am_god&method=showEmailLog" method="post" >
<input type="text" size="40" name="emailsearch" />
<input type="submit" value="suchen" name="search" />
</form>
<br />

<div class="shadow"><div class="nopadding">
<h3>Versendete E-Mails</h3>
<table class="centralTable">
<thead>
	<tr>
		<th>
			Empfänger
		</th>
		<th>
			Betreff
		</th>
		<th>
			Typ
		</th>
		<th>
			Status
		</th>	
	</tr>	
</thead>
<tbody>
{foreach from=$mails item="mail"}
  <tr>	
	<td>
	{$mail->getMailTo()}		
	</td>
	<td>
		<a href="/index.php?mod=i_am_god&amp;method=showEmailLog&amp;mailId={$mail->id}">{$mail->getMailSubject()}</a>
	</td>
	<td>
		{if $mail->getMailType() == 1}
			Regestrierung
		{elseif $mail->getMailType() == 2}
			Passwort vergessen
		{else}
			Unbekannt
		{/if}			
	</td>
	<td>
		{if $mail->isSent()}
			gesendet
		{else}
			in Warteschlange
		{/if}
	</td>
  </tr>	
{/foreach}
</tbody>
</table>
</div>
	{if $nextPage || $page>1}
		<div class="counter counterbottom">
			{if $page>1}
			<a href="/index.php?mod=i_am_god&amp;method=showEmailLog&amp;page={$page-1}" >vorherige Seite</a>
			{/if}
			{if $nextPage}
			<a href="/index.php?mod=i_am_god&amp;method=showEmailLog&amp;page={$page+1}" >nächste Seite</a>
			{/if}
		</div>
	{/if}
</div>

{if $showMail}
<div class="shadow"><div>
	{$showMail->getMailTo()} <br /><hr />
	{$showMail->getMailBody()|nl2br}
	<hr />
	<a href="/index.php?mod=i_am_god&amp;method=showEmailLog&amp;sendId={$showMail->id}">nochmal senden</a>
	<br /><br />
	<form action="/index.php?mod=i_am_god&amp;method=showEmailLog&amp;sendId={$showMail->id}" method="post">
		<input type="text" name="newEmail" />
		<input type="submit" name="send" value="an diese E-Mail schicken" />
	</form>
	<br class="clear" />
</div></div>
{/if}