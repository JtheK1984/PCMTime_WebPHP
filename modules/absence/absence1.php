<?php
	$username = $_SESSION['username'];
	$password = $_SESSION['password_hash'];    
	$id = $_SESSION['id'];
?>
<?php $activeTab = $_GET['tab'] ?? 'Tab1'; ?>
	<div class="tab">
		<button type="button" <?php if ($activeTab == 'Tab1') echo 'class="active"'; ?> onclick="openTab(event, 'Tab1')">Abwesenheit Monat</button>
		<button type="button" <?php if ($activeTab == 'Tab2') echo 'class="active"'; ?>  onclick="openTab(event, 'Tab2')">Abwesenheit  Jahr</button>
	</div>
<?php
	require_once 'includes/config.php';
	$monat = (int)($_GET['monat'] ?? date('m'));
	$jahr = (int)($_GET['jahr'] ?? date('Y'));
	$monatsnamen = [
			1 => "Januar", 2 => "Februar", 3 => "März", 4 => "April", 5 => "Mai", 6 => "Juni",
			7 => "Juli", 8 => "August", 9 => "September", 10 => "Oktober", 11 => "November", 12 => "Dezember"
	];
	$url = BaseUrl . 'GetAbsencedays?ID_User=' . urlencode($id) . '&Month=' . $monat . '&Year=' . $jahr;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
	curl_setopt($ch, CURLOPT_POST, true);
	$response = curl_exec($ch);
	curl_close($ch);
	$entries1 = json_decode($response, true);
  $entries2 = $entries1['Absence_Day'];

	echo '<div id="Tab1" class="tabcontent" style="display: ' . ($activeTab == 'Tab1' ? 'block':'none') . ';">';			
	echo "<table style=\"border-radius: 0px 0px 0px 0px;\" class=\"tablehf\">";
	echo "<thead>";
	echo "<tr><th class=\"LeftCell\">Abwesenheiten für {$monatsnamen[$monat]} $jahr</th></tr>";
	echo "</thead>";
	echo "<tbody>";
	echo "<tr><td>";
	echo '<form method="get" class="button-container2" style="display: flex; align-items: center; gap: 15px;">';

	echo '<select name="monat">';
	for ($m = 1; $m <= 12; $m++) {
			echo "<option value='$m'" . ($m == $monat ? ' selected' : '') . ">" . $monatsnamen[$m] . "</option>";
	}
	echo '</select>';

	echo '<select name="jahr">';
	for ($y = date('Y') - 5; $y <= date('Y') + 1; $y++) {
			echo "<option value='$y'" . ($y == $jahr ? ' selected' : '') . ">$y</option>";
	}
	echo '</select>';

	echo '<button type="submit" class="btn-show" style="min-width: 120px; padding: 4px 8px; ">';
	echo '<img src="/time/images/ShowFilter.svg" alt="Gehen Icon" style="vertical-align: middle; margin-right: 5px;">Anzeigen</button>';
	echo '<button type="submit" class="btn-show" style="min-width: 120px; padding: 4px 8px; ">';
	echo '<img src="/time/images/EditData.svg" alt="Gehen Icon" style="vertical-align: middle; margin-right: 5px;">Eintragen</button>';
	echo '</form>';
	echo "</td></tr>";
	echo "</tbody>";
	echo '</table>';	
	$map = [];
	foreach ($entries2 as $e) {
		$map[date('j', strtotime($e['Datum']))] = $e;
	}

	$heute = date('Y-m-d');
	$totalSoll = 0; 
	$totalNetto = 0;
	$colfont = "";
	$colback = "";

	function min2hhmm($min) {
		$h = floor($min / 60);
		$m = $min % 60;
		return sprintf("%02d:%02d", $h, $m);
	}

	$firstDay = mktime(0, 0, 0, $monat, 1, $jahr);
	$weekday = date('N', $firstDay);
	$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monat, $jahr);

	echo "<div class='calendarFT'>";
	$tage = ['Mo','Di','Mi','Do','Fr','Sa','So'];
	foreach($tage as $t) echo "<div class='day-headerFT'>$t</div>";

	// Leere Anfangstage
	for ($i=1; $i < $weekday; $i++) {
		echo "<div class='dayft empty'></div>";
	}
	$weekSoll = 0; 
	$weekNetto = 0;
	$tagText ="";
	for ($d = 1; $d <= $daysInMonth; $d++) {
		$timestamp = mktime(0, 0, 0, $monat, $d, $jahr);
		$datumTag = date('Y-m-d', $timestamp);
		$wday = date('N', $timestamp);
		$class = "dayFT";
		if ($wday >= 6) {
			$class .= " weekend";
/*					$colfont = "black";
			$colback = "#f0f0f0";*/
		} else {
			/*$colback = "white";		*/		
		}
		
		if ($datumTag == $heute) $class .= " today";
		$icon = "";
		$content = "";

		if (isset($map[$d])) {
			$e = $map[$d];
			$tagText =$e['Day'];
			$FT =$e['Abs'];
			if ($e['Holiday']) {
				$class .= " Feiertag";
				$colfont = 'black';
				$colback = '#00FF80';
			} elseif (!empty($e['Absence'])) {
				//$class .= " Fehltag";
				$colfont = delphiColorToHtml($e['ColorFont']);
				$colback = delphiColorToHtml($e['Color']);
			}

			if (strtotime($e['Datum']) > strtotime(date('Y-m-d'))) {
				$soll = 0;
			} else {
				$soll = (int)substr($e['Target_time'],0,2)*60 + (int)substr($e['Target_time'],3,2);
			}
			$content = "<div style='text-align: left; padding: 2px; '>" . $FT  . "</div>";
		}
		if ($colfont !== '') 
		{
			//echo 'if-'.$colfont .'-'.$colback.'-'.$class.'-'.$icon.'-'.$d.'-'.$tagText.'-'.$content;
			echo "<div style='color: $colfont; background: $colback' class='$class'><div style='color: $colfont; '>$icon $d.<span class='mobile-br'><br>$tagText</span><span class='mobile-nobr'> $tagText</span> </div><div class='info'>$content</div></div>";

		}
		else 
		{
		//	echo 'else-'.$colfont .'-'.$colback.'-'.$class.'-'.$icon.'-'.$d.'-'.$tagText.'-'.$content;
			//echo "<div class='$class'><div class='date'>$icon $d. $tagText</div><div class='info'>$content</div></div>";
			
			echo "<div class='$class'><div class='date'>$icon $d.<span class='mobile-br'><br>$tagText</span><span class='mobile-nobr'> $tagText</span> </div><div class='info'>$content</div></div>";
			
			
		}	
		$colfont = "";
		$colback = "";


	
	}

	$endweekday = date('N', mktime(0, 0, 0, $monat, $daysInMonth, $jahr));
	for ($i = $endweekday; $i < 7; $i++) 
			echo "<div class='dayFT empty'></div>";
	echo "</div>";
	echo '</div>';






	echo '<div id="Tab2" class="tabcontent" style="display: ' . ($activeTab == 'Tab2' ? 'block':'none') . ';">';
	echo "<table style=\"border-radius: 0px 0px 0px 0px;\" class=\"tablehf\">";
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
?>
<?php		
	function delphiColorToHtml($delphiColor) {
			$red = $delphiColor & 0xFF;
			$green = ($delphiColor >> 8) & 0xFF;
			$blue = ($delphiColor >> 16) & 0xFF;
			return sprintf("#%02X%02X%02X", $red, $green, $blue);
	}


	for ($monat = 1; $monat <= 12; $monat++) {
		// Daten laden pro Monat
		$url = BaseUrl . 'GetAbsencedays?ID_User=' . urlencode($id) . '&Month=' . $monat . '&Year=' . $jahr;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
		curl_setopt($ch, CURLOPT_POST, true);
		$response = curl_exec($ch);
		curl_close($ch);
		$entries1 = json_decode($response, true);
		$entries2 = $entries1['Absence_Day'];

		$map = [];
		foreach ($entries2 as $e) {
				$map[date('j', strtotime($e['Datum']))] = $e;
		}

		$heute = date('Y-m-d');

		$firstDay = mktime(0, 0, 0, $monat, 1, $jahr);
		$weekday = date('N', $firstDay);
		$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monat, $jahr);
		//echo "<div class=\"h2abs\">{$monatsnamen[$monat]} $jahr</div>";
		
		
			
			echo "<table class=\"tableabs\">";
			echo "<thead>";
			echo "<tr><th class=\"LeftCell\"> {$monatsnamen[$monat]} $jahr</th></tr>";
			echo "</thead>";
			echo "</table>";
		
		
		echo "<div class='calendarFT'>";
		
		
		$tage = ['Mo','Di','Mi','Do','Fr','Sa','So'];
		foreach($tage as $t) echo "<div class='day-headerFT'>$t</div>";

		// Leere Anfangstage
		for ($i = 1; $i < $weekday; $i++) {
				echo "<div class='dayFT empty'></div>";
		}

		for ($d = 1; $d <= $daysInMonth; $d++) {
				$timestamp = mktime(0, 0, 0, $monat, $d, $jahr);
				$datumTag = date('Y-m-d', $timestamp);
				$wday = date('N', $timestamp);
				$class = "dayFT";
				if ($wday >= 6) {
						$class .= " weekend";
				}
				if ($datumTag == $heute) $class .= " today";
				$icon = "";
				$content = "";

				$colfont = "";
				$colback = "";

				if (isset($map[$d])) {
						$e = $map[$d];
						$tagText = $e['Day'];
						$FT = $e['Abs'];
						if ($e['Holiday']) {
								$class .= " Feiertag";
								$colfont = 'black';
								$colback = '#00FF80';
						} elseif (!empty($e['Absence'])) {
								$colfont = delphiColorToHtml($e['ColorFont']);
								$colback = delphiColorToHtml($e['Color']);
						}

						if (strtotime($e['Datum']) > strtotime($heute)) {
								$soll = 0;
						} else {
								$soll = (int)substr($e['Target_time'],0,2) * 60 + (int)substr($e['Target_time'],3,2);
						}
						$content = "<div style='text-align: left; padding: 2px; '>" . $FT . "</div>";
				}

				if ($colfont !== '') {
						echo "<div style='color: $colfont; background: $colback' class='$class'><div style='color: $colfont;'>$icon $d.<span class='mobile-br'><br>$tagText</span><span class='mobile-nobr'> $tagText</span></div><div class='info'>$content</div></div>";
				} else {
						echo "<div class='$class'><div class='date'>$icon $d.<span class='mobile-br'><br>$tagText</span><span class='mobile-nobr'> $tagText</span></div><div class='info'>$content</div></div>";
				}
		}

		$endweekday = date('N', mktime(0, 0, 0, $monat, $daysInMonth, $jahr));
		for ($i = $endweekday; $i < 7; $i++) {
				echo "<div class='dayFT empty'></div>";
		}
		echo "</div>";
	}
	echo '</div>';
?>

