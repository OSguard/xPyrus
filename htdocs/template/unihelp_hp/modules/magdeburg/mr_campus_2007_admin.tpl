{literal}
<script type="text/javascript" language="javascript">
function checkRating(ref){
				 var sum = 0;
				 for(var i = 0; i < 5 ; i ++ ){
				 		if(ref.entertainer[i].checked)
						 sum += i+1;
				 }
				  for(var i = 0; i < 5 ; i ++ ){
				 		if(ref.sports[i].checked)
						 sum += i+1;
				 }
				  for(var i = 0; i < 5 ; i ++ ){
				 		if(ref.sexy[i].checked)
						 sum += i+1;
				 }
				  for(var i = 0; i < 5 ; i ++ ){
				 		if(ref.brain[i].checked)
						 sum += i+1;
				 }
				 if(sum > 10){
				 				alert('Summe der Bewerung ist groesser als 10');
				 				return false;
				 }
}
</script>
{/literal}

<span style='font-size: 200%; color: #ff0000 !important;' >{$my_error}</span>

<a id="topofpage" name="topofpage"></a>
<h4 align="center">Mr &amp; Mrs Campus 2007 - ADMIN</h4>
<table cellspacing="0" cellpadding="2" id="table_rahmen" border="0" LEFTMARGIN="0" TOPMARGIN="0" MARGINHEIGHT="0" MARGINWIDTH="0">

  <tr>

  <td>

  &nbsp;&nbsp;<span id='table_ueberschrift'>Eingabe</span> &nbsp;&nbsp;

  </td>

  </tr>

  </table><table cellspacing="0" cellpadding="10" border="1" id="table_rahmen" width="100%"><tr><td id="table_inhalt_zwei">

		<form method="post" onsubmit="return checkRating(this)" name="newCanidat" enctype="multipart/form-data" >
		<table style="vertical-align: top;">
		<tr><td>
	Foto-Id:</td><td>
	<input type="text" name="pic_id" size="5" /></td></tr>
	
	{*<tr><td>
	Foto:</td><td>
	<INPUT NAME="file" TYPE="file" SIZE="20"></td></tr>
	*}
	<tr><td>
	Vorname:</td><td>
	<input type="text" name="vorname" size="20" /></td></tr><tr><td>
	Nachname:</td><td>
	<input type="text" name="nachname" size="20" /></td></tr><tr><td>
	Geschlecht:</td><td>
	<input name="gender" type="radio" value="f" id="gender_w" checked /> <label for="gender_w">weiblich </label>
	<input name="gender" type="radio" value="m" id="gender_m"  /> <label for="gender_m">m&auml;nnlich  </label>
	</td></tr><tr><td>
	Studiengang:</td><td>
	<select name="study_path" >
					{foreach from=$study_path item="studi_row"}
									 <option value={$studi_row->id}> {$studi_row->getName()}</option>
					{/foreach}
	</select></td></tr><tr><td>
	Telefon:</td><td>
	<input type="text" name="telefon" size="20" /></td></tr><tr><td>
	Email:</td><td>
	<input type="text" name="email" size="20" />
	</td></tr><tr><td style="vertical-align: top;" colspan="2">
	<strong>Du kannst insgesamt 10 Punkte vergeben, wobei 1 am schlechtesten und 5 am besten ist!!</strong>
	</td></tr><tr><td style="vertical-align: top;">
	Ich bin ein/e Entertainer/in: </td><td>
	<input name="entertainer" type="radio" value="1" id="entertainer_1" checked /> <label for="entertainer_1">1</label> 
	<input name="entertainer" type="radio" value="2" id="entertainer_2" /> <label for="entertainer_2">2</label> 
	<input name="entertainer" type="radio" value="3" id="entertainer_3" /> <label for="entertainer_3">3</label> 
	<input name="entertainer" type="radio" value="4" id="entertainer_4" /> <label for="entertainer_4">4</label> 
	<input name="entertainer" type="radio" value="5" id="entertainer_5" /> <label for="entertainer_5">5</label> 
	</td></tr><tr><td style="vertical-align: top;">
	Ich bin eine Sportskanone: </td><td>
	<input name="sports" type="radio" value="1" id="sports_1" checked /> <label for="sports_1">1</label> 
	<input name="sports" type="radio" value="2" id="sports_2" /> <label for="sports_2">2</label> 
	<input name="sports" type="radio" value="3" id="sports_3" /> <label for="sports_3">3</label> 
	<input name="sports" type="radio" value="4" id="sports_4" /> <label for="sports_4">4</label> 
	<input name="sports" type="radio" value="5" id="sports_5" /> <label for="sports_5">5</label> 
	</td></tr><tr><td style="vertical-align: top;">
	Ich bin Supersexy:</td><td>
	<input name="sexy" type="radio" value="1" id="sexy_1" checked /> <label for="sexy_1">1</label>
	<input name="sexy" type="radio" value="2" id="sexy_2" /> <label for="sexy_2">2</label> 
	<input name="sexy" type="radio" value="3" id="sexy_3" /> <label for="sexy_3">3</label> 
	<input name="sexy" type="radio" value="4" id="sexy_4" /> <label for="sexy_4">4</label> 
	<input name="sexy" type="radio" value="5" id="sexy_5" /> <label for="sexy_5">5</label> 
	</td></tr><tr><td style="vertical-align: top;">
	Ich bin ein Superhirn: </td><td>
	<input name="brain" type="radio" value="1" id="brain_1" checked /> <label for="brain_1">1</label> 
	<input name="brain" type="radio" value="2" id="brain_2" /> <label for="brain_2">2</label> 
	<input name="brain" type="radio" value="3" id="brain_3" /> <label for="brain_3">3</label> 
	<input name="brain" type="radio" value="4" id="brain_4" /> <label for="brain_4">4</label> 
	<input name="brain" type="radio" value="5" id="brain_5" /> <label for="brain_5">5</label> 
	</td></tr><tr><td>
	{literal}
	<input type="submit" name="save" value="Speichern" />
	{/literal}
	</td></tr>
	</table>
	</form>

</td></tr></table>
