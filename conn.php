<?php

$host = "localhost";
$user = "root";
$password = "root"; // Inserisci la password di root per MAMP
$database = "progettoFabriLoi";
$connessione = new mysqli($host, $user, $password, $db);

if($connessione->connect_error){
    die("Errore in fase di connessione: " . $connessione->connect_error);
}
?>