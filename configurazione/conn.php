<?php
$host = "localhost";
$user = "root";
$password = ""; // Inserisci la password di root per MAMP
$database = "progettoFabriLoi";

$conn = new mysqli($host, $user, $password, $database);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
?>
