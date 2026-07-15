<?php 
	require_once 'includes/config.php'; 
	$username = $_SESSION['username'];
	$password = $_SESSION['password_hash'];    
	$id = $_SESSION['id'];
	$urlGetOnlineBook = BaseUrl . 'GetOnlineBook?ID_User=' . urlencode($id);
	$urlSetOnlineBook = BaseUrl . 'SetOnlineBook?ID_User=' . urlencode($id);
	$pause2EndeGebucht = false;
	if ($_SERVER['REQUEST_METHOD'] === 'POST') 
	{
		$ch = curl_init($urlGetOnlineBook);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
		curl_setopt($ch, CURLOPT_POST, true);

		$responsePersonal = curl_exec($ch);
		if (curl_errno($ch)) {
				exit(json_encode(['error' => 'Curl error: ' . curl_error($ch)]));
		}
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($httpCode >= 400) {
				exit(json_encode(['error' => 'HTTP error: ' . $httpCode]));
		}
		$data = json_decode($responsePersonal, true);

		$pause1begin = '00:00:00';
		$pause1end = '00:00:00';

		if (!empty($data['Booking'])) {
			foreach ($data['Booking'] as $item) 
			{
				if (isset($item['Break1_Begin'])) $pause1begin = $item['Break1_Begin'];
				if (isset($item['Break1_End'])) $pause1end = $item['Break1_End'];
				if (isset($item['Type']) && (int)$item['Type'] === 6) {
					$pause2EndeGebucht = true;
				}
			}
		}
		curl_close($ch);
		$data = [];
		if (isset($_POST['kommen'])) 
		{
			$data = [
				"OnlineBooking" => [[
					"Date" => date('d.m.Y'),
					"Time" => date('H:i'),
					"Type" => 1,
					"Booking_Type" => 0
				]]
			];    
		} 
		elseif (isset($_POST['gehen'])) 
		{
			$data = [
				"OnlineBooking" => [[
					"Date" => date('d.m.Y'),
					"Time" => date('H:i'),
					"Type" => 2,
					"Booking_Type" => 0
				]]
			];  
		} 
		elseif (isset($_POST['kommen1'])) 
		{
			$data = [
				"OnlineBooking" => [[
					"Date" => date('d.m.Y'),
					"Time" => date('H:i'),
					"Type" => 1,
					"Booking_Type" => 1
				]]
			];  
		} 
		elseif (isset($_POST['gehen1'])) // Korrigierte Bedingung
		{
			$data = [
				"OnlineBooking" => [[
					"Date" => date('d.m.Y'),
					"Time" => date('H:i'),
					"Type" => 2,
					"Booking_Type" => 1
				]]
			];  
		} 
		elseif (isset($_POST['pauseAnfang']))
		{
			// Pausen-Buttons nur erlauben, wenn Pause2 Ende noch nicht gebucht
			if (!$pause2EndeGebucht) {
				if ($pause1begin === '00:00:00') 
				{
					$data = [
						"OnlineBooking" => [[
							"Date" => date('d.m.Y'),
							"Time" => date('H:i'),
							"Type" => 3,
							"Booking_Type" => 0
						]]
					];
				} 
				else 
				{
					$data = [
						"OnlineBooking" => [[
							"Date" => date('d.m.Y'),
							"Time" => date('H:i'),
							"Type" => 5,
							"Booking_Type" => 0
						]]
					];
				}
			}
		}
		elseif (isset($_POST['pauseEnde'])) 
		{
			// Pausen-Buttons nur erlauben, wenn Pause2 Ende noch nicht gebucht
			if (!$pause2EndeGebucht) {
				if ($pause1end === '00:00:00') 
				{
					$data = [
						"OnlineBooking" => [[
							"Date" => date('d.m.Y'),
							"Time" => date('H:i'),
							"Type" => 4,
							"Booking_Type" => 0
						]]
					];
				} 
				else 
				{
					$data = [
						"OnlineBooking" => [[
							"Date" => date('d.m.Y'),
							"Time" => date('H:i'),
							"Type" => 6,
							"Booking_Type" => 0
						]]
					];
				}
			}
		} 

		// Buchung per PUT an API senden
		if (!empty($data)) 
		{
			$jsonData = json_encode($data);
			$ch = curl_init($urlSetOnlineBook);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($jsonData)]);
			
			$response = curl_exec($ch);
			
			if (curl_errno($ch)) {
				echo 'Curl error: ' . curl_error($ch);
			} 
			curl_close($ch);	
		}	
	}
	$urlLastBook = BaseUrl . 'GetLastBook?ID_User=' . urlencode($id);
	$ch = curl_init($urlLastBook);
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
			echo "<tr><td colspan='1'>Keine Daten gefunden.</td></tr>";
		} 
		else 
		{
			if (isset($data['HasError']) && $data['HasError'] === true) 
			{
				$error_message = "Fehler von API: " . $data['Errormessage'];
			} 
			else 
			{
				foreach ($data['LastBooking'] as $item) 
				{
					//echo '<table class="tablehf"><thead><tr><th id="datetime"></th><th class="RightCellmin">'. htmlspecialchars($item['LastBooking']).' </th></tr></thead></table>';
					
					
					
echo '<table class="tablehf">';
echo '<thead>';
echo '<tr><th id="datetime">Datum/Zeit</th><th class="RightCellmin">'.htmlspecialchars($item['LastBooking']) .'</th></tr>';
echo '</thead>';
echo '<tbody>';
echo '<tr>';
echo '<td colspan="2" >'; // colspan auf 2 Spalten
echo '<form method="get" class="button-container1" >';
echo '<select name="BuchungsArt" >'; // Klasse hinzugefügt
echo '<option value="option2">Büro</option>';
echo '<option value="option3">HomeOffice</option>';
echo '</select>';
echo '</form>';
echo '</td>';
echo '</tr>';
echo '</tbody>';
echo '</table>';
					
					
					
				}
			}
		}
	}
	curl_close($ch);

	$selectedBuchungsArt = $_POST['BuchungsArt'] ?? 'option3'; // Default z.B. Büro
	echo '<form method="post" class="button-container">';
	echo '<button id="btn_BookingWorkBegin" class="buttonOnl" type="submit" name="kommen"><img src="/time/images/in.svg" alt="Kommen Icon">Kommen</button>';
	echo '<button id="btn_BookingWorkEnd" class="buttonOnl" type="submit" name="gehen"><img src="/time/images/out.svg" alt="Gehen Icon">Gehen</button>';	
	echo '<button id="btn_BookingWorkBegin1" class="buttonOnl" type="submit" name="kommen1"><img src="/time/images/in.svg" alt="Kommen Icon">Ho - Kommen</button>';
	echo '<button id="btn_BookingWorkEnd1" class="buttonOnl" type="submit" name="gehen1"><img src="/time/images/out.svg" alt="Ho Gehen Icon">Ho - Gehen</button>';	
	echo '<button id="btn_BookingBreakBegin" class="buttonOnl" type="submit" name="pauseAnfang"><img src="/time/images/Clock.svg" alt="Pause Beginn Icon">Pause Beginn</button>';
	echo '<button id="btn_BookingBreakend" class="buttonOnl" type="submit" name="pauseEnde"><img src="/time/images/Clock.svg" alt="Pause Ende Icon">Pause Ende</button>';
	echo '</form>';

	if (!empty($error_message)) {
		echo '<div class="messagelogin">'.htmlspecialchars($error_message).'</div>';
	} 	

	// Übergabe, ob Pause2 Ende gebucht wurde an JavaScript
	echo "<script>const pause2EndeGebucht = " . ($pause2EndeGebucht ? 'true' : 'false') . ";</script>";

	
?>

<script>
function updateDateTime() {
    const now = new Date();

    const day = String(now.getDate()).padStart(2, '0');
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const year = now.getFullYear();

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    const formatted = `${day}.${month}.${year} ${hours}:${minutes}:${seconds}`;
    document.getElementById('datetime').textContent = formatted;
}









let lastDataHash = null;

// Einfacher Hash über JSON-String (für komplexere Daten kann man md5 o.ä. nehmen)
function hashData(data) {
  return JSON.stringify(data);
}

function pollStatus() {
  fetch('modules/booking/online_get.php', {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(response => {
    if (!response.ok) throw new Error('HTTP error: ' + response.status);
    return response.json();
  })
  .then(data => {
    const currentHash = hashData(data);
    if (currentHash !== lastDataHash) {
      lastDataHash = currentHash;
      for (const btn in data) {
        const button = document.getElementById(btn);
        if (button) {
          button.disabled = !data[btn];
          // HomeOffice Buttons synchronisieren wie im Original
          if (btn === 'btn_BookingWorkBegin' || btn === 'btn_BookingWorkEnd') {
            const hoBtnId = btn === 'btn_BookingWorkBegin' ? 'btn_BookingWorkBegin1' : 'btn_BookingWorkEnd1';
            const hoButton = document.getElementById(hoBtnId);
            if (hoButton) hoButton.disabled = !data[btn];
          }
          if (btn === 'btn_BookingWorkBegin1' || btn === 'btn_BookingWorkEnd1') {
            const regBtnId = btn === 'btn_BookingWorkBegin1' ? 'btn_BookingWorkBegin' : 'btn_BookingWorkEnd';
            const regButton = document.getElementById(regBtnId);
            if (regButton) regButton.disabled = !data[btn];
          }
        }
      }
    } else {
      // Keine Änderung, keine UI-Aktualisierung
      console.log('Keine Änderung beim Poll-Status, übersprungen.');
    }
  })
  .catch(error => console.error('Fehler beim Abruf:', error));
}

document.addEventListener("DOMContentLoaded", function() {
  updateDateTime();
  setInterval(updateDateTime, 1000);
  pollStatus();
  setInterval(pollStatus, 5000); // Intervall von 5 Sekunden statt 1 Sekunde

  // Pausenbuttons deaktivieren, wenn Pause2 Ende gebucht wurde
  if (pause2EndeGebucht) {
    document.getElementById('btn_BookingBreakBegin').disabled = true;
    document.getElementById('btn_BookingBreakend').disabled = true;
  }
});


document.addEventListener('DOMContentLoaded', function() {
    const combo = document.querySelector('select[name="BuchungsArt"]');
    const btnKommen = document.getElementById('btn_BookingWorkBegin');
    const btnGehen = document.getElementById('btn_BookingWorkEnd');
    const btnHoKommen = document.getElementById('btn_BookingWorkBegin1');
    const btnHoGehen = document.getElementById('btn_BookingWorkEnd1');

    function updateButtonsVisibility() {
        if (combo.value === 'option3') { // "HomeOffice"
            btnKommen.style.display = 'none';
            btnGehen.style.display = 'none';
            btnHoKommen.style.display = '';
            btnHoGehen.style.display = '';
        } else {
            btnKommen.style.display = '';  
            btnGehen.style.display = '';
            btnHoKommen.style.display = 'none';
            btnHoGehen.style.display = 'none';        
        }
    }

    combo.addEventListener('change', updateButtonsVisibility);
    updateButtonsVisibility();
});

document.addEventListener('DOMContentLoaded', function() {
    const combo = document.querySelector('select[name="BuchungsArt"]');
    combo.addEventListener('change', function() {
        localStorage.setItem('BuchungsArt', combo.value);
    });
    const storedValue = localStorage.getItem('BuchungsArt');
    if (storedValue) {
        combo.value = storedValue;
        combo.dispatchEvent(new Event('change'));
    }
});
</script>
