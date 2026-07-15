<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';

$username = $_SESSION['username'];
$password = $_SESSION['password_hash'];    
$id = $_SESSION['id'];
$url = BaseUrl . 'GetOnlineBook?ID_User=' . urlencode($id);

$ch = curl_init($url);
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
foreach ($data['Booking'] as $item) {
    $kommen =  $item['Work_Begin'];
    $gehen = $item['Work_End'];
    $pause1begin = $item['Break1_Begin'];
    $pause1end = $item['Break1_End'];
    $pause2begin = $item['Break2_Begin'];
    $pause2end = $item['Break3_End'];
}
curl_close($ch);

function isNotZero($t) {
    return $t && $t !== '00:00:00';
}

$result = [
    'btn_BookingWorkBegin' => !isNotZero($kommen),
    'btn_BookingWorkEnd' => false,
    'btn_BookingBreakBegin' => false,
    'btn_BookingBreakend' => false,
];

// Wenn 'Kommen' gebucht wurde
if (isNotZero($kommen)) {

    // Wenn Pause 2 Ende gebucht - nur Gehen aktiv
    if (isNotZero($pause2end)) {
        $result['btn_BookingWorkEnd'] = !isNotZero($gehen);
    }
    // Wenn Pause 2 Beginn gebucht - nur Pause Ende aktiv
    else if (isNotZero($pause2begin) && !isNotZero($pause2end)) {
        $result['btn_BookingBreakend'] = true;
    }
    // Wenn Pause 1 Ende gebucht - Gehen und Pause Beginn aktiv
    else if (isNotZero($pause1end)) {
        $result['btn_BookingWorkEnd'] = !isNotZero($gehen);
        $result['btn_BookingBreakBegin'] = true;
    }
    // Wenn Pause 1 Beginn gebucht - nur Pause Ende aktiv
    else if (isNotZero($pause1begin) && !isNotZero($pause1end)) {
        $result['btn_BookingBreakend'] = true;
    }
    // Wenn keine Pause läuft und Gehen noch nicht gebucht - Gehen und Pause Beginn aktiv
    else if (!isNotZero($gehen)) {
        $result['btn_BookingWorkEnd'] = true;
        $result['btn_BookingBreakBegin'] = true;
    }
}

// Wenn Arbeitsende gebucht, Pause Beginn deaktivieren
if (isNotZero($gehen)) {
    $result['btn_BookingBreakBegin'] = false;
}

echo json_encode($result);
?>
