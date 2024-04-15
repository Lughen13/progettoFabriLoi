<?php
require_once('connessione.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Preleva i dati dal form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cerca l'utente nel database
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_object();

    // Verifica la password
    if (password_verify($password, $user->password)) {
        // Login riuscito
        session_start();
        $_SESSION['username'] = $username;
        echo "Login riuscito!";
    } else {
        // Login fallito
        echo "Username o password errati";
    }
}
?>
        