<?php
// Connessione al database
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'conn.php';

$conn = new mysqli($host, $user, $password, $database);


// Avvia la sessione
session_start();

// Cancella tutte le variabili di sessione
session_unset();

// Distrugge la sessione
session_destroy();

// Reindirizza l'utente alla pagina di home
header("Location: home.php");
exit();
?>
