<?php
	// Datenbank-Verbindungsdaten
	define('DB_HOST', 'pcm-dev');
	define('DB_PORT', 3307); 
	define('DB_USER', 'root');
	define('DB_PASS', 'pcm');
	define('DB_NAME', 'pcm');

	// Verbindung herstellen (mysqli)
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

	// Verbindungsfehler prüfen
	if ($conn->connect_error) {
			die("Verbindung fehlgeschlagen: " . $conn->connect_error);
	}
?>