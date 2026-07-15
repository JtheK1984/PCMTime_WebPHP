		<?php
			require_once 'includes/config.php'; 
			$username = $_SESSION['username'];
			$password = $_SESSION['password_hash'];    
			$id = $_SESSION['id'];
			$url = BaseUrl . 'GetEmployee?ID_User=' . urlencode($id);

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
			
			$responsePersonal = curl_exec($ch);
			echo '<table class="tablehf">';
			echo '<thead>';
			echo '<tr>';
			echo '<th class="thLeftCell">Personalnummer</th>';
			echo '<th class="thLeftCell">Mitarbeiter </th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			if (curl_errno($ch)) 
			{
				$error_message = 'Curl error: ' . curl_error($ch);
			} 
			else 
			{
				$data = json_decode($responsePersonal, true);
				if ($data === null) 
				{
					 echo '<tr><td colspan="2">Keine Daten gefunden</td></tr>';
				} 
				else 
				{
					if (isset($data['HasError']) && $data['HasError'] === true) 
					{
            if (isset($data['ErrorCode']) && $data['ErrorCode'] === 1)
            {
                echo '<tr><td colspan="2">Keine Daten gefunden</td></tr>';
            }
            if (isset($data['ErrorCode']) && $data['ErrorCode'] === 2)
            {
                $error_message = "Fehler von API: " . $data['Errormessage'];
                echo '<tr><td colspan="2">' . htmlspecialchars($error_message) . '</td></tr>';
            }
					} 
					else 
					{
						echo "<tr>";
						echo '<td class="LeftCellBold">' . htmlspecialchars($data['Personalnumber']) . '</td>';
						echo '<td class="LeftCellBold">' . htmlspecialchars($data['Lastname']) . ', ' . htmlspecialchars($data['Name']) . '</td>';
						echo "</tr>";
					}
				}
			}
			curl_close($ch);
			echo '</tbody>';
			echo '</table>';
			$activeTab = $_GET['tab'] ?? 'TabW1';
			?>
		<div class="tab1">
			<button type="button" <?php if ($activeTab == 'TabW1') echo 'class="active"'; ?> onclick="openTab1(event, 'TabW1')">Aktuelle Werte</button>
			<button type="button" <?php if ($activeTab == 'TabW2') echo 'class="active"'; ?> onclick="openTab1(event, 'TabW2')">Fehltage</button>
			<button type="button" <?php if ($activeTab == 'TabW3') echo 'class="active"'; ?> onclick="openTab1(event, 'TabW3')">Feiertage</button>
		</div>	
		<!-- aktuelle Werte -->
		<?php
			$url = BaseUrl . 'GetBookingData?ID_User=' . urlencode($id);
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);

			$response = curl_exec($ch);
			
			echo '<div id="TabW1" class="tabcontent1" style="display: block;">';
			echo '<table  class="tablehf" style="border-radius: 0px 0px 8px 8px; margin: -20px auto;" > <!-- nächste Abwesenheit -->';
			
			if (curl_errno($ch)) 
			{
				$error_message = 'Curl error: ' . curl_error($ch);
			} 
			else 
			{
				$data = json_decode($response, true);
				if ($data === null) 
				{
						echo '<thead>';
						echo '<tr>';
						echo '<th class="thLeftCell">nächste Abwesenheit</th>';
						echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
						echo '<tr>';
						echo '<tr><td colspan="1">Keine Daten gefunden</td></tr>';
						echo '</tr>';
						echo '</tbody>';
						echo '<thead>';
						echo '<tr>';
						echo '<th class="LeftCell">nächster Feiertag</th>';
						echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
						echo '<tr>';
						echo '<tr><td colspan="1">Keine Daten gefunden</td></tr>';
						echo '</tr>';	
						echo '</tbody>';
						echo '<thead>';
						echo '<tr>';
						echo '<th class="LeftCell">Letzte Buchung</th>';
						echo '</tr>';
						echo '</thead>';
						echo '<tbody>';	
						echo '<tr>';
						echo '<tr><td colspan="1">Keine Daten gefunden</td></tr>';
						echo '</tr>';
				} 
				else 
				{
					if (isset($data['HasError']) && $data['HasError'] === true) 
					{
            if (isset($data['ErrorCode']) && $data['ErrorCode'] === 1)
            {
							echo '<thead>';
							echo '<tr>';
							echo '<th class="thLeftCell">nächste Abwesenheit</th>';
							echo '</tr>';
							echo '</thead>';
							echo '<tbody>';
							echo '<tr>';
							echo '<tr><td colspan="2">Keine Daten gefunden</td></tr>';
							echo '</tr>';
							echo '</tbody>';
							echo '<thead>';
							echo '<tr>';
							echo '<th class="LeftCell">nächster Feiertag</th>';
							echo '</tr>';
							echo '</thead>';
							echo '<tbody>';
							echo '<tr>';
							echo '<tr><td colspan="2">Keine Daten gefunden</td></tr>';
							echo '</tr>';	
							echo '</tbody>';
							echo '<thead>';
							echo '<tr>';
							echo '<th class="LeftCell">Letzte Buchung</th>';
							echo '</tr>';
							echo '</thead>';
							echo '<tbody>';	
							echo '<tr>';
							echo '<tr><td colspan="2">Keine Daten gefunden</td></tr>';
							echo '</tr>';
							echo '</tbody>';
								
            }
            if (isset($data['ErrorCode']) && $data['ErrorCode'] === 2)
            {
              $error_message = "Fehler von API: " . $data['Errormessage'];
              echo '<thead>';
							echo '<tr>';
							echo '<th class="thLeftCell">nächste Abwesenheit</th>';
							echo '</tr>';
							echo '</thead>';
							echo '<tbody>';
							echo '<tr>';
							echo '<tr><td colspan="1">' . htmlspecialchars($error_message) . '</td></tr>';
							echo '</tr>';
							echo '</tbody>';
							echo '<thead>';
							echo '<tr>';
							echo '<th class="LeftCell">nächster Feiertag</th>';
							echo '</tr>';
							echo '</thead>';
							echo '<tbody>';
							echo '<tr>';
							echo '<tr><td colspan="1">' . htmlspecialchars($error_message) . '</td></tr>';
							echo '</tr>';	
							echo '</tbody>';
							echo '<thead>';
							echo '<tr>';
							echo '<th class="LeftCell">Letzte Buchung</th>';
							echo '</tr>';
							echo '</thead>';
							echo '<tbody>';	
							echo '<tr>';
							echo '<tr><td colspan="1">' . htmlspecialchars($error_message) . '</td></tr>';
							echo '</tr>';
							echo '</tbody>';
								
            }
					} 
					else 
					{
  					echo '<thead>';
						echo '<tr>';
						echo '<th class="thLeftCell">nächste Abwesenheit</th>';
						echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
						echo '<tr>';
						echo '<td class="LeftCell">' . htmlspecialchars($data['NextAbsence']) . '</td>';
						echo '</tr>';
						echo '</tbody>';
						echo '<thead>';
						echo '<tr>';
						echo '<th class="LeftCell">nächster Feiertag</th>';
						echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
						echo '<tr>';
						echo '<td class="LeftCell">' . htmlspecialchars($data['NextHoliDay']) . '</td>';
						echo '</tr>';	
						echo '</tbody>';
						echo '<thead>';
						echo '<tr>';
						echo '<th class="LeftCell">Letzte Buchung</th>';
						echo '</tr>';
						echo '</thead>';
						echo '<tbody>';	
						echo '<tr>';
						echo '<td class="LeftCell">' . htmlspecialchars($data['LastBooking']) . '</td>';
						echo '</tr>';
						echo '</tbody>';
					}
				}
			}
			curl_close($ch);
			echo '</table>';
			$activeTab = $_GET['tab'] ?? 'Tab1';
		?>
		<div class="tab">
			<button type="button" <?php if ($activeTab == 'Tab1') echo 'class="active"'; ?> onclick="openTab(event, 'Tab1')">Aktueller Monat</button>
			<button type="button" <?php if ($activeTab == 'Tab2') echo 'class="active"'; ?>  onclick="openTab(event, 'Tab2')">Jahreswerte</button>
		</div>
		<!-- Monatsswerte -->
		<?php
			
			function decimalHoursToTime($decimal) 
			{
				$hours = floor($decimal);
				$minutes = floor(($decimal - $hours) * 60);
				$seconds = round((($decimal - $hours) * 60 - $minutes) * 60);
				return sprintf('%02d:%02d', $hours, $minutes);
			}
			$year = date('Y');
			$month =  date('n');
			
			$url = BaseUrl . 'GetMonthYearValues?ID_User=' . urlencode($id) . '&Month=' . $month . '&Year=' . $year;
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
			//curl_setopt($ch, CURLOPT_POST, true);

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
							echo '<div id="Tab1" class="tabcontent" style="display: block;">';									
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
						$UL =  $RULVJ + $ULUser - ($UL + $ULGenomen); 
						echo '<div id="Tab1" class="tabcontent" style="display: block;">';									
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
						echo "			<tr>";
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
						echo '<div id="Tab2" class="tabcontent" >';
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
						echo "			</tr>";	
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
						echo "			<tr>";	
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
		<!-- Fehltage -->
		<?php	
			$url = BaseUrl . 'GetFehltage?ID_User=' . urlencode($id);

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
			curl_setopt($ch, CURLOPT_POST, true);

			$responseFT = curl_exec($ch);
			echo '<div id="TabW2" class="tabcontent1" >';
			echo '<table class="tablehf" style="border-radius: 0px 0px 8px 8px; margin: -20px auto; ">';
			echo '<thead>';
			echo '<tr>';
			echo '<th class="LeftCellmin">Kürzel</th>';
			echo '<th class="LeftCellHidden">Beschreibung</th>';
			echo '<th class="LeftCellmin">Von</th>';
			echo '<th class="LeftCellmin">Bis</th>';
			echo '<th class="LeftCellmin">Tage</th>';			
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			if (curl_errno($ch)) 
			{
				$error_message = 'Curl error: ' . curl_error($ch);
			} 
			else 
			{
				
				$data = json_decode($responseFT, true);
				if ($data === null) 
				{
						printf('3');
						echo "<tr><td colspan='5'>Keine Daten gefunden.</td></tr>";
				} 
				else 
				{
					
					if (isset($data['HasError']) && $data['HasError'] === true) 
					{
					
					  if (isset($data['ErrorCode']) && $data['ErrorCode'] === 1)
            {
          		echo '<tr><td colspan="2">Keine Daten gefunden</td></tr>';
            }
            if (isset($data['ErrorCode']) && $data['ErrorCode'] === 2)
            {
							$error_message = "Fehler von API: " . $data['Errormessage'];
               echo '<tr><td colspan="2">' . htmlspecialchars($error_message) . '</td></tr>';
            }
					} 
					else 
					{
											
						foreach ($data['Fehltage'] as $item) 
						{
							echo '<tr>';
							echo '<td class="LeftCellmin">' . htmlspecialchars($item['Kuerzel']) . '</td>';
							echo '<td class="LeftCellHidden">' . htmlspecialchars($item['Beschreibung']) . '</td>';
							echo '<td class="LeftCellmin">' . htmlspecialchars($item['Von']) . '</td>';
							echo '<td class="LeftCellmin">' . htmlspecialchars($item['Bis']) . '</td>';
							echo '<td class="LeftCellmin">' . htmlspecialchars($item['Tage']) . '</td>';
							echo '</tr>';
						}
			
					}
				}
			}
			curl_close($ch);
			echo '</tbody>';
			echo '</table>';
			echo '</div>';
		?>
		<!-- Feiertage -->
		<?php
			echo '<div id="TabW3" class="tabcontent1" >';
			echo '<table class="tablehf" style="border-radius: 0px 0px 8px 8px; margin: -20px auto">';
			echo '<thead>';
			echo '<tr>';
			echo '<th class="LeftCellmin">Datum</th>';
			echo '<th class="LeftCell">Feiertag</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
				
			$url = BaseUrl . 'GetFeiertage?ID_User=' . urlencode($id);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
			curl_setopt($ch, CURLOPT_POST, true);
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
						echo "<tr><td colspan='2'>Keine Daten gefunden.</td></tr>";
				} 
				else 
				{
					if (isset($data['HasError']) && $data['HasError'] === true) 
					{
					
					  if (isset($data['ErrorCode']) && $data['ErrorCode'] === 1)
            {
          		echo '<tr><td colspan="2">Keine Daten gefunden</td></tr>';
            }
            if (isset($data['ErrorCode']) && $data['ErrorCode'] === 2)
            {
							$error_message = "Fehler von API: " . $data['Errormessage'];
               echo '<tr><td colspan="2">' . htmlspecialchars($error_message) . '</td></tr>';
            }
					} 
					else 
					{
						foreach ($data['Feiertage'] as $item) 
						{
							echo '<tr>';
							echo '<td class="LeftCellmin">' . htmlspecialchars($item['Datum']) . '</td>';
							echo '<td class="LeftCell">' . htmlspecialchars($item['Bezeichnung']) . '</td>';
							echo '</tr>';
						}
						
					}
				}
			}
			curl_close($ch);				
			echo '</tbody>';
			echo '</table>';
			echo '</div>';
						
		?>