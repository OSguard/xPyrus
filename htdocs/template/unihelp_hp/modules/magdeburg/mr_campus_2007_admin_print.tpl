<html>
<head>
<title>UniHelp.de - Mr. &amp; Mrs. Campus 2007</title>
</head>
<body onLoad="window.print()">
<table style="font-size: 150%; font-family: Arial;">
<tr>
		<td colspan="2">
		Mr. &amp; Mrs. Campus 2007 <br />
		UniHelp.de
		<br/>
		<br/>
		<br/>
		<br/>
		</td>
</tr>
<tr>
		<td>
		Name:
		</td>
		<td>
		{$vorname} {$nachname}
		</td>
		<tr>
		<td>
				Teilnehmer:
		</td>
		<td>
				{$pic_id}
		</td>
		<tr>
		<td>
				Geschlecht:
		</td>
		<td>
				{if $gender=="m"}männlich{else}weiblich{/if}
		</td>
		<tr>
		<td>
				Telefon:
		</td>
		<td>
				{$telefon}
		</td>
		<tr>
		<td>
		   Email:
		</td>
		<td>
				{$email}
		</td>
</tr>

</table>
<br/><br/>
<div style="font-family: Arial;">
Ich habe die Teilnehmerbedingungen gelesen und erkläre mich damit einverstanden.
</div>

<br/><br/><br/>
<div  style="font-size: 130%; font-family: Arial;">
Magdeburg, den {$aktuellesdatum}
</div>


<br/><br/><br/>
<br/><br/><br/>
<br/><br/><br/>
<a href="/MrMrsCampus/admin271828">nächsten Teilnehmer eingeben</a>
</body>
