<?php 
	$username = $_SESSION['username'];
	$password = $_SESSION['password_hash'];    
	$id = $_SESSION['id'];
?>
<?php $activeTab = $_GET['tab'] ?? 'Tab1'; ?>
<div class="tab">
	<button type="button" <?php if ($activeTab == 'Tab1') echo 'class="active"'; ?> onclick="openTab(event, 'Tab1')">Monatswerte</button>
	<button type="button" <?php if ($activeTab == 'Tab2') echo 'class="active"'; ?>  onclick="openTab(event, 'Tab2')">Jahreswerte</button>
</div>
<?php
	require_once 'includes/config.php'; 
	
	function decimalHoursToTime($decimal) 
	{
		$hours = floor($decimal);
		$minutes = floor(($decimal - $hours) * 60);
		$seconds = round((($decimal - $hours) * 60 - $minutes) * 60);
		return sprintf('%02d:%02d', $hours, $minutes);
	}
	$monat = (int)($_GET['monat'] ?? date('m'));
	$jahr = (int)($_GET['jahr'] ?? date('Y'));
	$url = BaseUrl . 'GetMonthYearValues?ID_User=' . urlencode($id) . '&Month=' . $monat . '&Year=' . $jahr;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
	curl_setopt($ch, CURLOPT_POST, true);
	$monatsnamen = [
		1 => "Januar", 2 => "Februar", 3 => "März", 4 => "April", 5 => "Mai", 6 => "Juni",
		7 => "Juli", 8 => "August", 9 => "September", 10 => "Oktober", 11 => "November", 12 => "Dezember"
	];
	echo '<div id="Tab1" class="tabcontent" style="display: ' . ($activeTab == 'Tab1' ? 'block':'none') . ';">';			
	echo "<table style=\"border-radius: 0px 0px 0px 0px;\" class=\"tablehf\">";
	echo "<thead>";
	echo "<tr><th class=\"LeftCell\">Monatswerte für {$monatsnamen[$monat]} $jahr</th></tr>";
	echo "</thead>";
	echo "<tbody>";
	echo "<tr><td>";
	echo '<form method="get" class="button-container2" style="display: flex; align-items: center; gap: 15px;">';
	echo '<input type="hidden" name="tab" value="Tab1">'; // Damit beim Submit Tab nicht verloren geht
	echo '<select id="selectMonat" name="monat">';
	for ($m = 1; $m <= 12; $m++) {
			echo "<option value='$m'" . ($m == $monat ? ' selected' : '') . ">" . $monatsnamen[$m] . "</option>";
	}
	echo '</select>';

	echo '<select id="selectJahr" name="jahr">';
	for ($y = date('Y') - 5; $y <= date('Y') + 1; $y++) {
			echo "<option value='$y'" . ($y == $jahr ? ' selected' : '') . ">$y</option>";
	}
	echo '</select>';

	echo '<button type="submit" id="btnAnzeigen" class="btn-show" style="min-width: 120px; padding: 4px 8px; ">';
	echo '<img src="/time/images/ShowFilter.svg" alt="Gehen Icon" style="vertical-align: middle; margin-right: 5px;">Anzeigen</button>';
	echo '<button type="submit" id="btnBerechnen" class="btn-show" style="min-width: 120px; padding: 4px 8px; ">';
	echo '<img src="/time/images/calc.svg" alt="Gehen Icon" style="vertical-align: middle; margin-right: 5px;">Berechnen</button>';
	echo '</form>';
	echo "</td></tr>";
	echo "</tbody>";
	echo '</table>';
	echo '<div id="spinner" class="loader"></div>';
	echo '<div id="result" style="display:none;"></div>';
	$response = curl_exec($ch);
	if (curl_errno($ch)) 
	{
		$error_message = 'Curl error: ' . curl_error($ch);
	} 
	else 
	{
		$data = json_decode($response, true);
		if ($data === null) 
		{
			echo '<tr><td colspan="3">Keine Daten gefunden</td></tr>';
		} 
		else 
		{
	
		if (isset($data['HasError']) && $data['HasError'] === true) 
			{
				if (isset($data['ErrorCode']) && $data['ErrorCode'] === 2)
				{
					$error_message = "Fehler von API: " . $data['Errormessage'];
					echo '	<table  class="tablehf" style="border-radius: 0px 0px 8px 8px; margin: -20px auto;">';
					echo '		<thead><tr><th class="LeftCell">Stundenwerte</th><th class="RightCellmin">hh:mm</th><th class="RightCellmin">Dezimal</th></tr></thead>';
					echo '		<tbody><tr><td colspan="3">' . htmlspecialchars($error_message) . '</td></tr></tbody>';
					echo '		<thead><tr><th class="LeftCell">An/Abwesenheit</th><th class="RightCellmin">Tage</th><th class="RightCellmin">hh:mm</th></tr></thead>';
					echo '		<tbody><tr><td colspan="3">' . htmlspecialchars($error_message) . '</td></tr></tbody>';
					echo '	</table>';
					echo '</div>';
					echo '<div id="Tab2" class="tabcontent" >';
					echo '	<table  class="tablehf" style="border-radius: 0px 0px 8px 8px; margin: -20px auto;">';
					echo '		<thead><tr><th class="LeftCell">An/Abwesenheit</th><th class="RightCellmin">Tage</th></tr></thead>';
					echo '		<tbody><tr><td colspan="3">' . htmlspecialchars($error_message) . '</td></tr></tbody>';
					echo '	</table>';
					echo '</div>';
					echo '</div>';							
				}
			} 
			else 
			{
				$UL = $data['UL'];
				$ULStd = $data['ULD'];
				$KR = $data['KR'];
				$KRStd = $data['KRD'];
				$FT = $data['FT'];
				$FTStd = $data['FTD'];
				$ULUser = $data['UL_Anspruch'];						
				$SollUser =$data['Target_time'] ;
				$RULVJ = $data['UL_Vorjahr'];
				$ULGenomen = $data['UL_genommenJahrD'];
				$UL =  $RULVJ + $ULUser - $ULGenomen; 
				echo '	<table  class="tablehf" style="border-radius: 0px 0px 8px 8px; margin: -20px auto;">';
				echo '		<thead><tr><th class="LeftCell">Stundenwerte</th><th class="RightCellmin">hh:mm</th><th class="RightCellmin">Dezimal</th></tr></thead>';
				echo '		<tbody>';
				echo "			<tr>";
				echo '				<td class="LeftCell">Iststunden brutto:</td>';
				echo '				<td class="RightCellmin">' . htmlspecialchars($data['BruttoT']) . '</td>';
				echo '				<td class="RightCellmin">' . number_format($data['BruttoD'] , 2, ',', '') . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCell">Pausen:</td>';
				echo '				<td class="RightCellmin">' . htmlspecialchars($data['PauseT']) . '</td>';
				echo '				<td class="RightCellmin">' . number_format($data['PauseD'] , 2, ',', '') . '</td>';
				echo "			</tr>";							
				echo "			<tr>";
				echo '				<td class="LeftCell">Iststunden Netto (inkl. Abwesend):</td>';
				echo '				<td class="RightCellmin">' . htmlspecialchars($data['IStzeitT']) . '</td>';
				echo '				<td class="RightCellmin">' . number_format($data['IStzeitD'] , 2, ',', '') . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCell">Abwesend:</td>';
				echo '				<td class="RightCellmin">' . decimalHoursToTime(number_format($ULStd + $KRStd + ($FTStd / 60), 2, '.', '')) . '</td>';
				echo '				<td class="RightCellmin">' .  number_format($ULStd + $KRStd + ($FTStd / 60), 2, ',', '') . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCell">Iststunden Netto:</td>';
				echo '				<td class="RightCellmin">' . decimalHoursToTime(number_format($data['IStzeitD'] - ($ULStd + $KRStd + ($FTStd / 60)), 2, '.', '')) . '</td>';
				echo '				<td class="RightCellmin">' .  number_format($data['IStzeitD'] - ($ULStd + $KRStd + ($FTStd / 60)), 2, ',', '') . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCell">Sollstunden:</td>';
				echo '				<td class="RightCellmin">' . htmlspecialchars($data['SollzeitT']) . '</td>';
				echo '				<td class="RightCellmin">' . number_format($data['SollzeitD'] , 2, ',', '') . '</td>';
				echo '			</tr>';
				echo '			<tr>';
				echo '				<td class="LeftCell">Minder/Mehrarbeit:</td>';
				echo '				<td class="RightCellmin">' . htmlspecialchars($data['MehrarbeitT']) . '</td>';
				echo '				<td class="RightCellmin">' . number_format($data['MehrarbeitD'] , 2, ',', '') . '</td>';
				echo '			</tr>';
				echo '			<tr>';
				echo '				<td class="LeftCell">Gleitzeitübertrag Vormonat:</td>';
				echo '				<td class="RightCellmin">' . htmlspecialchars($data['MehrarbeitVMT']) . '</td>';
				echo '				<td class="RightCellmin">' . number_format($data['MehrarbeitVMD'] , 2, ',', '') . '</td>';
				echo 			'</tr>';
				echo '			<tr>';
				echo '				<td class="LeftCellBold">Gleitzeitübertrag Folgemonat:</td>';
				echo '				<td class="RightCellBoldmin">' . htmlspecialchars($data['aktuelleMehrarbeitT']) . '</td>';
				echo '				<td class="RightCellBoldmin">' . number_format($data['aktuelleMehrarbeitD'] , 2, ',', '') . '</td>';
				echo '			</tr>';
				echo '		</tbody>';
				echo '		<thead>';
				echo '			<tr>';
				echo '				<th class="LeftCell">An/Abwesenheit</th>';
				echo '				<th class="RightCellmin">Tage</th>';
				echo '				<th class="RightCellmin">hh:mm</th>';
				echo '			</tr>';
				echo '		</thead>';
				echo '		<tbody>';
				echo "			<tr>";
				echo '				<td class="LeftCell">Urlaub genommen:</td>';
				echo '				<td class="RightCellmin">' . number_format($data['UL_genommenD'] , 2, ',', '') . '</td>';
				echo '				<td class="RightCellmin">' . htmlspecialchars($data['UL_genommen']) . '</td>';						
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCell">Urlaub geplant:</td>';
				echo '				<td class="RightCellmin">' . number_format($data['UL_geplantD'] , 2, ',', '') . '</td>';
				echo '				<td class="RightCellmin">' . htmlspecialchars($data['UL_geplant']) . '</td>';						
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCellBold">Resturlaub:</td>';
				echo '				<td class="RightCellBoldmin">' . htmlspecialchars(number_format($UL , 2, ',', '')) . '</td>';
				echo '				<td class="RightCellBoldmin">' . htmlspecialchars(decimalHoursToTime(number_format($UL , 2, '.', '') * number_format($SollUser , 2, '.', ''))) . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCell">Urlaub unbezahlt:</td>';
				echo '				<td class="RightCellmin">' . number_format($data['ULunbD'] , 2, ',', '') . '</td>';
				echo '				<td class="RightCellmin">' . htmlspecialchars($data['ULunbT']) . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCell">Feiertage:</td>';
				echo '				<td class="RightCellmin">' . number_format($FT , 2, ',', '') . '</td>';
				echo '				<td class="RightCellmin">' . decimalHoursToTime(number_format(($FTStd / 60), 2, '.', '')) . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCell">Kranktage bezahlt:</td>';
				echo '				<td class="RightCellmin">' . number_format($KR , 2, ',', '') . '</td>';
				echo '				<td class="RightCellmin">' . decimalHoursToTime(number_format($KRStd, 2, '.', '')) . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCell">Kranktage unbezahlt:</td>';
				echo '				<td class="RightCellmin">' . number_format($data['KRunbD'] , 2, ',', '') . '</td>';
				echo '				<td class="RightCellmin">' . htmlspecialchars($data['KRunbT']) . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCell">Bürotage:</td>';
				echo '				<td class="RightCellmin">' . number_format($data['Office'] , 2, ',', '') . '</td>';
				echo '				<td class="RightCellmin">' . htmlspecialchars(decimalHoursToTime(number_format($data['Office']  , 2, '.', '') * number_format($SollUser , 2, '.', ''))) . '</td>';
				echo "			</tr>";
				echo '				<td class="LeftCell">HomeOffice-Tage:</td>';
				echo '				<td class="RightCellmin">' . number_format($data['HomeOffice'] , 2, ',', '') . '</td>';
				echo '				<td class="RightCellmin">' . htmlspecialchars(decimalHoursToTime(number_format($data['HomeOffice']  , 2, '.', '') * number_format($SollUser , 2, '.', ''))) . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCellBold">Arbeitstage gesamt:</td>';
				echo '				<td class="RightCellBoldmin">' . number_format($data['Summary'] , 2, ',', '') . '</td>';
				echo '				<td class="RightCellBoldmin">' . htmlspecialchars(decimalHoursToTime(number_format($data['Summary']  , 2, '.', '') * number_format($SollUser , 2, '.', ''))) . '</td>';
				echo "			</tr>";
				echo '		</tbody>';
				echo '	</table>';
				echo '</div>';
										
			}
		}
	}

	if (curl_errno($ch)) 
	{
		$error_message = 'Curl error: ' . curl_error($ch);
	} 
	else 
	{
		
		$data = json_decode($response, true);
		if ($data === null) 
		{
			echo '<tr><td colspan="3">Keine Daten gefunden</td></tr>';
		} 
		else 
		{
	
		if (isset($data['HasError']) && $data['HasError'] === true) 
			{
				if (isset($data['ErrorCode']) && $data['ErrorCode'] === 2)
				{
					$error_message = "Fehler von API: " . $data['Errormessage'];


					
					echo '	<table  class="tablehf" style="border-radius: 0px 0px 8px 8px; margin: -20px auto;">';
					echo '		<thead><tr><th class="LeftCell">Stundenwerte</th><th class="RightCellmin">hh:mm</th><th class="RightCellmin">Dezimal</th></tr></thead>';
					echo '		<tbody><tr><td colspan="3">' . htmlspecialchars($error_message) . '</td></tr></tbody>';
					echo '		<thead><tr><th class="LeftCell">An/Abwesenheit</th><th class="RightCellmin">Tage</th><th class="RightCellmin">hh:mm</th></tr></thead>';
					echo '		<tbody><tr><td colspan="3">' . htmlspecialchars($error_message) . '</td></tr></tbody>';
					echo '	</table>';
					echo '</div>';
					echo '<div id="Tab2" class="tabcontent" >';
					echo '	<table  class="tablehf" style="border-radius: 0px 0px 8px 8px; margin: -20px auto;">';
					echo '		<thead><tr><th class="LeftCell">An/Abwesenheit</th><th class="RightCellmin">Tage</th></tr></thead>';
					echo '		<tbody><tr><td colspan="3">' . htmlspecialchars($error_message) . '</td></tr></tbody>';
					echo '	</table>';
					echo '</div>';
					echo '</div>';							
				}
			} 
			else 
			{
				$UL = $data['UL'];
				$ULStd = $data['ULD'];
				$KR = $data['KR'];
				$KRStd = $data['KRD'];
				$FT = $data['FT'];
				$FTStd = $data['FTD'];
				$ULUser = $data['UL_Anspruch'];						
				
				$SollUser =$data['Target_time'] ;
				$RULVJ = $data['UL_Vorjahr'];
				$ULGenomen = $data['UL_genommenJahrD'];
				$UL =  $RULVJ + $ULUser - $ULGenomen; 










				//echo '<div id="Tab2" class="tabcontent" >';//
				echo '<div id="Tab2" class="tabcontent" style="display: ' . ($activeTab == 'Tab2' ? 'block':'none') . ';">';
				echo "<table style=\"border-radius: 0px 0px 0px 0px;\"class=\"tablehf\">";
				echo "<thead>";
				echo "<tr><th class=\"LeftCell\">Jahreswerte für $jahr</th></tr>";
				echo "</thead>";
				echo "<tbody>";
				echo "<tr><td>";
				echo '<form method="get" class="button-container2" style="display: flex; align-items: center; gap: 15px;">';
				echo '<input type="hidden" name="tab" value="Tab2">'; // Damit beim Submit Tab nicht verloren geht					
				echo '<select name="jahr">';
				for ($y = date('Y') - 5; $y <= date('Y') + 1; $y++) {
						echo "<option value='$y'" . ($y == $jahr ? ' selected' : '') . ">$y</option>";
				}
				echo '</select>';

				echo '<button type="submit" class="btn-show" style="min-width: 120px; padding: 4px 8px; ">';
				echo '<img src="/time/images/ShowFilter.svg" alt="Gehen Icon" style="vertical-align: middle; margin-right: 5px;">Anzeigen</button>';
				echo '</form>';
				echo "</td></tr>";
				echo "</tbody>";
				echo '</table>';
				echo '	<table  class="tablehf" style="border-radius: 0px 0px 8px 8px; margin: -20px auto;">';
				echo '		<thead>';
				echo '			<tr>';
				echo '				<th class="LeftCell">An/Abwesenheit</th>';
				echo '				<th class="RightCellmin">Tage</th>';
				echo '			</tr>';
				echo '		</thead>';
				echo '		<tbody>';			
				echo "			<tr>";
				echo '				<td class="LeftCell">Resturlaub Vorjahr:</td>';
				echo '				<td class="RightCellmin">' . number_format($RULVJ , 1, ',', '') . '</td>';
				echo "			</tr>";	
				echo "			<tr>";
				echo '				<td class="LeftCell">Urlaubstage:</td>';
				echo '				<td class="RightCellmin">' . number_format($data['UL_genommenJahrD'], 1, ',', '') . '</td>';
				echo "			<tr>";
				echo '				<td class="LeftCell">Kranktage:</td>';
				echo '				<td class="RightCellmin">' . number_format($data['KRYear'] , 1, ',', '') . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCell">Feiertage:</td>';
				echo '				<td class="RightCellmin">' . number_format($data['FTYear'] , 1, ',', '') . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCell">Bürotage:</td>';
				echo '				<td class="RightCellmin">' . number_format($data['OfficeYear'] , 2, ',', '') . '</td>';
				echo "			</tr>";
				echo '				<td class="LeftCell">HomeOffice-Tage:</td>';
				echo '				<td class="RightCellmin">' . number_format($data['HomeOfficeYear'] , 2, ',', '') . '</td>';
				echo "			</tr>";
				echo "			<tr>";
				echo '				<td class="LeftCellBold">Arbeitstage gesamt:</td>';
				echo '				<td class="RightCellBoldmin">' . number_format($data['SummaryYear'] , 2, ',', '') . '</td>';
				echo "			</tr>";
				echo '		</tbody>';
				echo '	</table>';
				echo '</div>';	
				echo '</div>';	
			}
		}
	}
	curl_close($ch);
?>	
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function ladeDaten() {
  $('#spinner').show();
  $('#result').hide();

  var monat = $('#selectMonat').val();
  var jahr = $('#selectJahr').val();

  $.ajax({
    url: 'modules/monthvalues/monthvalues_get.php',
    type: 'POST',
    contentType: 'application/json',
    data: JSON.stringify({ monat: monat, jahr: jahr }),
    success: function(data) {
      $('#spinner').hide();
      $('#result').html(data).show();
			$('#btnAnzeigen').click();
    },
    error: function() {
      $('#spinner').hide();
      $('#result').html('Fehler beim Laden').show();
    }
  });
}
$(document).ready(function() {
  $('#btnBerechnen').on('click', function(event) {
    event.preventDefault(); // verhindert, dass das Formular abgesendet wird
    ladeDaten();            // Funktion wird nur hier ausgeführt
  });
});



</script>
					</tbody>
				</table>
		
