<?php
ob_start(); // Output-Buffering starten

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

require_once(__DIR__ . '/../../includes/config.php');

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Formulardaten holen
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $kommen = $_POST['Kommen'] ?? '';
    $gehen = $_POST['Gehen'] ?? '';
    $p1b = $_POST['Pause1Beginn'] ?? null;
    $p1e = $_POST['Pause1Ende'] ?? null;
    $p2b = $_POST['Pause2Beginn'] ?? null;
    $p2e = $_POST['Pause2Ende'] ?? null;

    if ($id <= 0 || !$kommen || !$gehen) {
        throw new Exception('Ungültige Eingabedaten');
    }

    // Hilfsfunktionen
    function hhmm2min($hhmm) {
        if (!$hhmm) return 0;
        list($h, $m) = explode(':', $hhmm);
        return ((int)$h)*60 + ((int)$m);
    }
    function min2hhmm($min) {
        $h = floor($min / 60);
        $m = $min % 60;
        return sprintf('%02d:%02d', $h, $m);
    }
		function showAlert($message) {
    echo "<script>alert('$message');</script>";
		}

    // Daten aus DB zur Berechnung holen
    $stmt = $pdo->prepare('SELECT Sollstunden, Bezahlt, Faktor, Feiertag FROM time_buchungen ze_B LEFT OUTER  JOIN time_Fehltag ze_ft ON ze_ft.Kuerzel = ze_B.Fehltag WHERE ze_B.ID = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception('Buchung nicht gefunden');

    $sollMin = hhmm2min(substr($row['Sollstunden'], 0, 5));
    $bezahlt = (int)$row['Bezahlt'];
    $faktor = (int)$row['Faktor'];
    $feiertagFlag = (int)$row['Feiertag'];

    $fehltagMin = 0;
    $feiertagMin = 0;

    if ($bezahlt === 1 && $faktor === 1) {
        $fehltagMin = $sollMin;
    } elseif ($bezahlt === 1 && $faktor === 2) {
        $fehltagMin = (int)round($sollMin / 2);
    }
    if ($feiertagFlag === 1) {
        $feiertagMin = $sollMin;
    } elseif ($feiertagFlag === 2) {
        $feiertagMin = (int)round($sollMin / 2);
    }
    if ($fehltagMin === $feiertagMin && $feiertagMin === 480) {
        $fehltagMin = 0;
    }


    // Minuten für Zeiten berechnen
    $kommenMin = hhmm2min($kommen);
    $gehenMin = hhmm2min($gehen);

		$recordDate = date('Y-m-d');
		// Check if $recordDate is today
		if ($recordDate === date('Y-m-d')) {
				
				if ($kommenMin > 0 && $gehenMin == 0) {
						// Set current time in HH:MM format for $gehen
						$gehen = date('H:i');
						$gehenMin = hhmm2min($gehen);
						$gehen = min2hhmm(0);
				}
		}



    $arbeitszeit = max(0, $gehenMin - $kommenMin);
    $pause1 = ($p1b && $p1e) ? max(0, hhmm2min($p1e) - hhmm2min($p1b)) : 0;
    $pause2 = ($p2b && $p2e) ? max(0, hhmm2min($p2e) - hhmm2min($p2b)) : 0;
    $pauseGes = $pause1 + $pause2;
    $netto = max(0, $arbeitszeit - $pauseGes);
    $mehrarbeit = $arbeitszeit - $sollMin - $pauseGes + $fehltagMin + $feiertagMin;
		// DB update vorbereiten

		
		// Check if $recordDate is today

    $stmtUpdate = $pdo->prepare('UPDATE time_buchungen SET
        Kommen = :kommen,
        Gehen = :gehen,
        Pause1Beginn = :p1b,
        Pause1Ende = :p1e,
        Pause2Beginn = :p2b,
        Pause2Ende = :p2e,
        SollstundenI = :soll,
				ArbeitszeitI = :nettoi,
				Arbeitszeit = :netto,
        MehrarbeitI = :mehrarbeitInt,
        Mehrarbeit = :mehrarbeitStr,
        PausenI = :pausen,
        FeiertagI = :FeiertagI 
        WHERE ID = :id');
    $stmtUpdate->execute([
        ':kommen' => $kommen,
        ':gehen' => $gehen,
        ':p1b' => $p1b,
        ':p1e' => $p1e,
        ':p2b' => $p2b,
        ':p2e' => $p2e,
				':soll' => $sollMin,
				':nettoi' => $netto,
        ':netto' => min2hhmm($netto),
        ':mehrarbeitInt' => $mehrarbeit,
        ':mehrarbeitStr' => ($mehrarbeit < 0 ? '-' : '') . min2hhmm(abs($mehrarbeit)),
        ':pausen' => $pauseGes,
        ':FeiertagI' => $feiertagMin,
        
        ':id' => $id
    ]);

    // Alles clean ausgeben ohne störenden Output davor
    ob_end_clean();
    echo json_encode(['status'=>'success','message'=>'Buchung erfolgreich aktualisiert.']);

} catch (Exception $ex) {
    ob_end_clean();
    echo json_encode(['status'=>'error','message'=>'Fehler: '.$ex->getMessage()]);
}