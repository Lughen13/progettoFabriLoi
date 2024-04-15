<?php
require_once('connessione.php');
// Inizia la sessione
session_start();
sfdgfhgjkj
// Controlla se l'utente è loggato
if (!isset($_SESSION['username'])) {
    echo "Devi effettuare il login per visualizzare il tuo profilo";
    exit;
}

// Connessione al database
$db = new mysqli('localhost', 'username', 'password', 'database');

// Preleva i dati dell'utente dal database
$stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_object();

// Mostra i dati dell'utente
echo "Nome utente: " . $user->username . "<br>";
echo "Email: " . $user->email . "<br>";

// Preleva i blog dell'utente dal database
$stmt = $db->prepare("SELECT * FROM blogs WHERE author = ?");
$stmt->bind_param("s", $user->username);
$stmt->execute();
$result = $stmt->get_result();

// Mostra i blog dell'utente
echo "I tuoi blog:<br>";
while ($blog = $result->fetch_object()) {
    echo "Titolo: " . $blog->title . "<br>";
    echo "Descrizione: " . $blog->description . "<br>";
    echo "<hr>";
}
?>
