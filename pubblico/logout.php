<?php
// Connessione al database
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../configurazione/conn.php';

$conn = new mysqli($host, $user, $password, $database);

session_start();
session_unset();
// Distrugge la sessione corrente
session_destroy();

// torna alla pagina di login per fare un nuovo accesso
header("Location: ../pubblico/login.php");
exit();
?>
