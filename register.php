<?php
// Connessione al database
require_once('connessione.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Preleva i dati dal form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Controlla se l'email è valida
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Formato email non valido";
        exit;
    }

    // Crea una password sicura
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Inserisci l'utente nel database
    $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    $stmt->execute();

    echo "Registrazione completata con successo!";
}
?>
