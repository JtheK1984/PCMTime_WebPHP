<?php
session_start();
	$username = $_SESSION['username'];
	$password = $_SESSION['password_hash'];    
	$id = $_SESSION['id'];
require_once __DIR__ . '/../../includes/config.php';
// Daten aus POST empfangen (z.B. JSON Body)
$input = json_decode(file_get_contents('php://input'), true);
$monat = $input['monat'] ?? null;
$jahr = $input['jahr'] ?? null;
$url = $url = BaseUrl . 'CalcMonth?ID_User=1&Month='.$monat.'&Year='.$jahr;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
// Optional Basic Auth
// curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
// curl_setopt($ch, CURLOPT_USERPWD, 'username:password');

$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);
?>