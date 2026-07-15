<?php 
	$session_id_global = $_SESSION['id'];
?>
<?php
	require_once 'includes/config.php';
	$pdo = new PDO("mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4",
									DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

	$monat = (int)($_GET['monat'] ?? date('m'));
	$jahr = (int)($_GET['jahr'] ?? date('Y'));
	$monatsnamen = [
		1 => "Januar", 2 => "Februar", 3 => "März", 4 => "April", 5 => "Mai", 6 => "Juni",
		7 => "Juli", 8 => "August", 9 => "September", 10 => "Oktober", 11 => "November", 12 => "Dezember"
	];
	function delphiColorToHtml($color)
	{
	    if ($color < 0) {
        	$color &= 0xFFFFFFFF;
    	}

	    $r = $color & 0xFF;
	    $g = ($color >> 8) & 0xFF;
	    $b = ($color >> 16) & 0xFF;

	    return sprintf("#%02X%02X%02X", $r, $g, $b);
	}	
//			echo "<h2 class=\"h2Off\">Kalender für {$monatsnamen[$monat]} $jahr</h2>";




echo "<table class=\"tablehf\">";
echo "<thead>";
echo "<tr><th class=\"LeftCell\">Buchungen für {$monatsnamen[$monat]} $jahr</th></tr>";
echo "</thead>";
echo "<tbody>";
echo "<tr><td>";
echo '<form method="get" class="button-container1" style="display: flex; align-items: center; gap: 15px;">';

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

echo '</form>';
echo "</td></tr>";
echo "</tbody>";
echo "</table>";




			// Daten laden
			$stmt = $pdo->prepare("SELECT 
				b.ID, b.Datum, b.Tag, b.Kommen, b.Gehen, b.Pause1Beginn, b.Pause1Ende, b.Pause2Beginn, b.Pause2Ende,
				if(b.Datum > NOW(),'00:00:00',b.Sollstunden)  Sollstunden, SEC_TO_TIME((b.ArbeitszeitI + b.PausenI) * 60) AS Brutto,
				SEC_TO_TIME((b.PausenI) * 60) AS Pausen, b.Arbeitszeit AS Netto, b.PausenI, b.Arbeitszeit,
				IF(b.Feiertag = 1,'FT1',IF(b.Feiertag = 2,IF(b.Fehltag <> ' ',CONCAT(b.Fehltag,'-FT2'),'FT2'),b.Fehltag)) AS AbW,
				b.Feiertag, b.Mehrarbeit,b.MehrarbeitI, IF(b.Fehltag = ' ','',b.Fehltag) AS Fehltag,ft.color,ft.colorFont,
				b.Buchungsart, IF(b.Buchungsart=0,'B','HO') AS Ba, ft.Typ, ft.Bezahlt,
				SEC_TO_TIME(
				(SELECT aktuelleMehrarbeit FROM time_Monatswerte WHERE Jahr = if(? - 1 =0, ?-1,?) AND MONaT= If(? -1 = 0,12,?-1))*60) AS Vormonat
				FROM time_buchungen b 
				Left Outer JOIN time_fehltag ft ON ft.Kuerzel = b.Fehltag
				WHERE
				ID_Benutzer = ? AND
				YEAR(Datum)=? AND MONTH(Datum)=?");
			$stmt->execute([$monat,$jahr,$jahr,$monat,$monat,$session_id_global, $jahr, $monat]);
			$entries = $stmt->fetchAll();

			$map = [];
			foreach ($entries as $e) {
				$map[date('j', strtotime($e['Datum']))] = $e;
			}

			$heute = date('Y-m-d');
			$totalSoll = 0; 
			$totalNetto = 0;

			function min2hhmm($min) {
				$h = floor($min / 60);
				$m = $min % 60;
				return sprintf("%02d:%02d", $h, $m);
			}

			$firstDay = mktime(0, 0, 0, $monat, 1, $jahr);
			$weekday = date('N', $firstDay);
			$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monat, $jahr);

			echo "<div class='calendar'>";
			$tage = ['Mo','Di','Mi','Do','Fr','Sa','So'];
			foreach($tage as $t) echo "<div class='day-header'>$t</div>";

			// Leere Anfangstage
			for ($i=1; $i < $weekday; $i++) {
				echo "<div class='day empty'></div>";
			}
			$sNetto= '00:00';
			$weekSoll = 0; 
			$weekNetto = 0;
			$Mehrarbeit = 0;
			$tagText ="";
			for ($d = 1; $d <= $daysInMonth; $d++) {
				$timestamp = mktime(0, 0, 0, $monat, $d, $jahr);
				$datumTag = date('Y-m-d', $timestamp);
				$wday = date('N', $timestamp);
				$class = "day";
				if ($wday >= 6) $class .= " weekend";
				if ($datumTag == $heute) $class .= " today";
				$icon = "";
				$style = "";
				$content = "";
				if (isset($map[$d])) {
					$e = $map[$d];
					$tagText =$e['Tag'];
					$FT =$e['AbW'];
					if ($e['Feiertag']) {
						$class .= " Feiertag";
						$icon = "🎉";
					} elseif (!empty($e['Fehltag'])) {
						//$class .= " Fehltag";
						$style = "background:" . delphiColorToHtml($e['color']) . ";color:#fff;".
         						 ";color:" . delphiColorToHtml($e['colorFont']) . ";";
						$icon = "💼";
					} elseif ($e['Buchungsart'] == 1) {
						$class .= " Home";
						$icon = "🏠";
					}
					if (strtotime($e['Datum']) > strtotime(date('Y-m-d'))) {
						$soll = 0;
						$netto = 0;
						$sNetto = '00:00';
						$Mehrarbeit = 0;
					} else {
						$netto=(int)substr($e['Arbeitszeit'],0,2)*60+(int)substr($e['Arbeitszeit'],3,2);
						$sNetto = substr($e['Netto'], 0, 5);
						$Mehrarbeit = $e['MehrarbeitI'];
					if ($e['Typ'] > 0 && $e['Bezahlt'] == 0) {
						$soll = 0;
						}
						else 
						{
							$soll = (int)substr($e['Sollstunden'],0,2)*60 + (int)substr($e['Sollstunden'],3,2);
						}
					}
					$Vormonat=(int)substr($e['Vormonat'],0,2)*60+(int)substr($e['Vormonat'],3,2);
					
					$totalSoll+=$soll; 
					$totalNetto+=$netto; 
					$weekSoll+=$soll; 
					$weekNetto+=$netto;
					$eJS=json_encode($e);
					$diffClass = $Mehrarbeit >= 0 ? "plus" : "minus";




$hint = "";

if (isset($e)) {
    $hint = "Kommen: " . substr($e['Kommen'], 0, 5) .
            " | Gehen: " . substr($e['Gehen'], 0, 5) .
            " | Abw.: " . $FT;
}



					$content = "
					
					<div style='display: grid; grid-template-columns: 1fr 1fr; font-size: 13px; gap: 8px;'>
						<div style='text-align: left; font-weight: 600; padding: 2px;'>Kommen:</div>
						<div style='text-align: right; padding: 2px; word-wrap: break-word;'>" . substr($e['Kommen'], 0, 5) . "</div>
						<div style='text-align: left; font-weight: 600; padding: 2px;'>Gehen:</div>
						<div style='text-align: right; padding: 2px; word-wrap: break-word;'>" . substr($e['Gehen'], 0, 5) . "</div>
						<div style='text-align: left; font-weight: 600; padding: 2px;'>1. PA:</div>
						<div style='text-align: right; padding: 2px; word-wrap: break-word;'>" . substr($e['Pause1Beginn'], 0, 5) . "</div>
						<div style='text-align: left; font-weight: 600; padding: 2px;'>1. PE:</div>
						<div style='text-align: right; padding: 2px; word-wrap: break-word;'>" . substr($e['Pause1Ende'], 0, 5) . "</div>
						<div style='text-align: left; font-weight: 600; padding: 2px;'>2. PA:</div>
						<div style='text-align: right; padding: 2px; word-wrap: break-word;'>" . substr($e['Pause2Beginn'], 0, 5) . "</div>
						<div style='text-align: left; font-weight: 600; padding: 2px;'>2. PE:</div>
						<div style='text-align: right; padding: 2px; word-wrap: break-word;'>" . substr($e['Pause2Ende'], 0, 5) . "</div>
						<div style='text-align: left; font-weight: 600; padding: 2px;'>Abw.:</div>
						<div style='text-align: right; padding: 2px; word-wrap: break-word;'>" . $FT  . "</div>
					</div>
					<div style='text-align:center; margin-top:6px;'>
						<button type='button' onclick='openModal(" . json_encode($e) . ")' aria-label='Bearbeiten' style='background: #2a5d84; border-radius: 4px;  color: white;  box-shadow: 0 2px 8px rgba(0,0,0,0.08);  overflow: hidden;  border: none;  padding: 4px 8px;  font-size: 13px;  display: inline-flex;   align-items: center;  justify-content: center;  gap: 4px;'><img src=\"/time/images/EditData.svg\" style=\"width: 20px; height: 20px; \" alt=\"Gehen Icon\">Bearbeiten</button>
						<div style='display: grid; grid-template-columns: 1fr 1fr; font-size: 12px;  margin-top: 4px;'>			
							<div style='text-align: left;  font-weight: 600; padding: 2px;'>Soll:</div>
							<div style='text-align: right; font-weight: 600; padding: 2px;'>". substr($e['Sollstunden'], 0, 5) ."</div> 
							<div style='text-align: left;  font-weight: 600; padding: 2px;'>Ist:</div>
							<div style='text-align: right; font-weight: 600; padding: 2px;'>". $sNetto ."</div> 
							<div style='text-align: left;  font-weight: 600; padding: 2px;'>Glz:</div>
							<div style='text-align: right; font-weight: 600; padding: 2px;'><span class='week-diff $diffClass'>" . ($Mehrarbeit>=0?"+":"-") . min2hhmm(abs($Mehrarbeit)) . "</span></div>
						</div>  
					</div>
					";
				}
			echo "<div class='$class' style='$style'><div class='date'>$icon $d. $tagText</div><div class='info'>$content</div></div>";
			
  			//echo "<div class='$class'><div class='date'>$icon $d. $tagText</div><div class='info'>$content</div></div>";
  			if ($wday == 7 || $d == $daysInMonth) {
					$weekDiff = $weekNetto - $weekSoll;
					$diffClass = $weekDiff >= 0 ? "plus" : "minus";
					echo "<div class='week-summary'><strong>Woche " . date('W', $timestamp) . ":</strong> Soll: " . min2hhmm($weekSoll) . " | Ist: " . min2hhmm($weekNetto) . " | Glz: <span class='week-diff $diffClass'>" . ($weekDiff>=0?"+":"-") . min2hhmm(abs($weekDiff)) . "</span></div>";
					$weekSoll = 0; 
					$weekNetto = 0;
				}
			}

			$endweekday = date('N', mktime(0, 0, 0, $monat, $daysInMonth, $jahr));
			for ($i = $endweekday; $i < 7; $i++) 
					echo "<div class='day empty'></div>";
			echo "</div>";
			$diff = $totalNetto - $totalSoll; 
			$diffakt = $diff + $Vormonat; 
			echo $Vormonat ."<br>";
			echo $diff."<br>";
			echo $totalNetto."<br>";
			echo $totalSoll."<br>";
			$diffClass = $diff >= 0 ? "plus" : "minus";
			$diffClassVM = $Vormonat >= 0 ? "plus" : "minus";
			$diffClassAkt = $Vormonat >= 0 ? "plus" : "minus";
			echo "<div class='summary'><strong>Monatssummen</strong> Soll: " . min2hhmm($totalSoll) . " | Ist: " . min2hhmm($totalNetto) . 
			" | Glz: <span class='summary-diff $diffClass'>" . ($diff>=0?"+":"-") . min2hhmm(abs($diff)) ."</span> ". 
			" | Vormonat: <span class='summary-diff $diffClassVM'>" . ($Vormonat>=0?"+":"-") . min2hhmm(abs($Vormonat)) ."</span> ". 
			" | Aktuell: <span class='summary-diff $diffClassAkt'>" . ($diffakt>=0?"+":"-") . min2hhmm(abs($diffakt)) ."</span> ". 
			 "</div>";
		?>
		<div class="modal-overlay" id="editModal">
			<div class="modal">
				<h3>Buchung bearbeiten für <span id="info_datum"></span></h3>
				<form id="editForm">
					<input type="hidden" name="id" id="edit_id">
					<label>Kommen:</label><input type="time" name="Kommen" id="edit_kommen" required>
					<label>Gehen:</label><input type="time" name="Gehen" id="edit_gehen" required>
					<label>Pause 1 Beginn:</label><input type="time" name="Pause1Beginn" id="edit_p1b">
					<label>Pause 1 Ende:</label><input type="time" name="Pause1Ende" id="edit_p1e">
					<label>Pause 2 Beginn:</label><input type="time" name="Pause2Beginn" id="edit_p2b">
					<label>Pause 2 Ende:</label><input type="time" name="Pause2Ende" id="edit_p2e">
					<div class="modal-info">
						<div><strong>Sollzeit:</strong> <span id="info_soll"></span></div>
						<div><strong>Brutto:</strong> <span id="info_brutto"></span></div>
						<div><strong>Pause:</strong> <span id="info_pause"></span></div>
						<div><strong>Netto:</strong> <span id="info_netto"></span></div>
						<div><strong>Gleitzeit:</strong> <span id="info_diff"></span></div>
					</div>
					<div class="modal-buttons">
						<button type="button" class="btn-cancel" onclick="closeModal()"><img src="/time/images/Action_Cancel.svg" alt="Abbrechen">Abbrechen</button>
						<button type="submit" class="btn-save"><img src="/time/images/Action_Save.svg" alt="Speichern">Speichern</button>
					</div>
					<div id="saveMessage"></div>
					<div id="spinner"><div class="spinner-icon"></div></div>
				</form>
			</div>
		</div>
		<script>
			function openModal(entry) {
				document.getElementById('edit_id').value = entry.ID;
				let date = entry.Datum;
				let parts = date.split('-');
				let formattedDate = parts[2] + '.' + parts[1] + '.' + parts[0];
				document.getElementById('info_datum').textContent = formattedDate;
				document.getElementById('edit_kommen').value = entry.Kommen.substr(0,5);
				document.getElementById('edit_gehen').value = entry.Gehen.substr(0,5);
				document.getElementById('edit_p1b').value = entry.Pause1Beginn.substr(0,5);
				document.getElementById('edit_p1e').value = entry.Pause1Ende.substr(0,5);
				document.getElementById('edit_p2b').value = entry.Pause2Beginn.substr(0,5);
				document.getElementById('edit_p2e').value = entry.Pause2Ende.substr(0,5);
				document.getElementById('info_soll').textContent = entry.Sollstunden.substr(0,5);
				document.getElementById('info_netto').textContent = entry.Arbeitszeit.substr(0,5);
				document.getElementById('info_pause').textContent = entry.Pausen.substr(0,5);
				document.getElementById('info_brutto').textContent = entry.Brutto.substr(0,5);
				

				recalculate();

				document.getElementById('saveMessage').textContent = "";
				document.getElementById('spinner').style.display = "none";
				document.getElementById('editModal').style.display = "flex";
			}
			function closeModal() {
				document.getElementById('editModal').style.display = "none";
			}
			function recalculate() {
				function hhmm2min(hhmm) {
					if (!hhmm) return 0;
					var p = hhmm.split(":");
					return parseInt(p[0],10) * 60 + parseInt(p[1],10);
				}
				function min2hhmm(min) {
					var h = Math.floor(min / 60);
					var m = min % 60;
					return ("0" + h).slice(-2) + ":" + ("0" + m).slice(-2);
				}
				function isToday(dateToCheck) {
					let today = new Date();
					return today.getFullYear() === dateToCheck.getFullYear() &&
						today.getMonth() === dateToCheck.getMonth() &&
						today.getDate() === dateToCheck.getDate();
				}
				let Datum = document.getElementById('info_datum').textContent; // z.B. "23.09.2025"
				let parts = Datum.split('.');
				let tag = parseInt(parts[0], 10);
				let monat = parseInt(parts[1], 10) - 1; // Monate sind 0-basiert in JS
				let jahr = parseInt(parts[2], 10);
				let datumObj = new Date(jahr, monat, tag);
				let sollText = document.getElementById('info_soll').textContent;
				let sollMin = hhmm2min(sollText);
				let kommen = document.getElementById('edit_kommen').value;
				let gehen = document.getElementById('edit_gehen').value;
				let p1b = document.getElementById('edit_p1b').value;
				let p1e = document.getElementById('edit_p1e').value;
				let p2b = document.getElementById('edit_p2b').value;
				let p2e = document.getElementById('edit_p2e').value;
				let today = new Date();
				let nowTime = new Date();
				
				
				
					
				let kommenMin = hhmm2min(kommen);
				let gehenMin = hhmm2min(gehen);
				let brutto = Math.max(0, gehenMin - kommenMin);

				let pause1 = 0, pause2 = 0;
				if (p1b && p1e) pause1 = Math.max(0, hhmm2min(p1e) - hhmm2min(p1b));
				if (p2b && p2e) pause2 = Math.max(0, hhmm2min(p2e) - hhmm2min(p2b));
				let pauseGes = pause1 + pause2;

				let netto = Math.max(0, brutto - pauseGes);

				let diff = netto - sollMin;	
				
				

				
				
				
				let recordDate = datumObj; // Replace this with the actual date input
				if (isToday(recordDate)) {
					if (kommen) {
						kommenMin = hhmm2min(kommen);
						gehenMin = hhmm2min(gehen);
						if (kommenMin > 0 && gehenMin <= 0) {
							let nowTime = new Date();
							let currentHours = nowTime.getHours();
							let currentMinutes = nowTime.getMinutes();
							let currentTimeStr = ("0" + currentHours).slice(-2) + ":" + ("0" + currentMinutes).slice(-2);
							//gehen = currentTimeStr; // Use for calculation only
							kommenMin = hhmm2min(kommen);
							gehenMin = hhmm2min(currentTimeStr);
							brutto = Math.max(0, gehenMin - kommenMin);

							pause1 = 0, pause2 = 0;
							if (p1b && p1e) pause1 = Math.max(0, hhmm2min(p1e) - hhmm2min(p1b));
							if (p2b && p2e) pause2 = Math.max(0, hhmm2min(p2e) - hhmm2min(p2b));
							pauseGes = pause1 + pause2;
							netto = Math.max(0, brutto - pauseGes);
							diff = netto - sollMin;
						}
					}
				}
				
				


				
				
				
				
				
				
				
				
				
				
				

				

				document.getElementById('info_brutto').textContent = min2hhmm(brutto);
				document.getElementById('info_pause').textContent = min2hhmm(pauseGes);
				document.getElementById('info_netto').textContent = min2hhmm(netto);

				let h = Math.floor(Math.abs(diff) / 60);
				let m = Math.abs(diff) % 60;
				let diffText = (diff >= 0 ? "+" : "-") + ("0" + h).slice(-2) + ":" + ("0" + m).slice(-2);
				let diffClass = (diff >= 0 ? "plus" : "minus");
				document.getElementById('info_diff').innerHTML = `<span class="diff ${diffClass}">${diffText}</span>`;
			}

			// Alle Inputs mit Eventlistener für automatische Berechnung
			['edit_kommen','edit_gehen','edit_p1b','edit_p1e','edit_p2b','edit_p2e'].forEach(id => {
				document.getElementById(id).addEventListener('input', recalculate);
			});

			document.getElementById('editForm').addEventListener('submit', async function(e) {
				e.preventDefault();
				let formData = new FormData(this);
				let msg = document.getElementById('saveMessage');
				let spinner = document.getElementById('spinner');
				msg.textContent = "";
				spinner.style.display = "block";
				try {
					let resp = await fetch("modules/booking/update.php", { method:"POST", body: formData });
					if (!resp.ok) throw new Error('Netzwerkfehler: ' + resp.status);
					let result = await resp.json();
					spinner.style.display = "none";
					if(result.status == "success") {
						msg.style.color = "green"; 
						msg.textContent = result.message;
						setTimeout(() => { closeModal(); location.reload(); }, 800);
					} else {
						msg.style.color = "red"; 
						msg.textContent = result.message;
					}
				} catch(err) {
					spinner.style.display = "none";
					msg.style.color = "red";
					msg.textContent = "Fehler: " + err.message;
				}
			});
		</script>
