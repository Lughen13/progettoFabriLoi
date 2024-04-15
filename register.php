<?php
// Connessione al database
require_once('connessione.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Preleva i dati dal form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $premium = isset($_POST['premium']) ? 1 : 0; // Aggiunto per la registrazione premium

    // Aggiunto per la registrazione premium
    $numero_carta = $_POST['numero_carta'];
    $data_di_scadenza = $_POST['data_di_scadenza'];
    $cvv = $_POST['cvv'];

    // Controlla se l'email è valida
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Formato email non valido";
        exit;
    }

    // Crea una password sicura
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Inserisci l'utente nel database
    $stmt = $db->prepare("INSERT INTO users (username, email, password, premium, numero_carta, data_di_scadenza, cvv) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisss", $username, $email, $hashed_password, $premium, $numero_carta, $data_di_scadenza, $cvv);
    $stmt->execute();

    echo "Registrazione completata con successo!";
}
?>
