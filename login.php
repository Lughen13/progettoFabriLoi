<?php
// Mostra tutti gli errori
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

// Connessione al database
require_once 'conn.php';

// Inizia la sessione
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recupera i dati dal modulo di login
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepara una dichiarazione SQL per evitare attacchi di SQL injection
    $sql = "SELECT id_utente, username, password FROM utenti WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        // Verifica se l'utente esiste, se sì, verifica la password
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id_utente, $db_username, $db_password);
            $stmt->fetch();

            if (password_verify($password, $db_password)) {
                // Imposta le variabili di sessione
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $id_utente; // Cambia 'id_utente' in 'id'
                $_SESSION['username'] = $db_username;

                // Reindirizza l'utente alla pagina principale
                header("Location: home.php");
                exit();
            } else {
                // Password non valida
                echo "La password non è valida.";
            }
        } else {
            // Username non trovato
            echo "Nessun account trovato con questo username.";
        }

        $stmt->close();
    } else {
        echo "Errore: Impossibile preparare la query SQL.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form method="post" action="login.php">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br><br>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>