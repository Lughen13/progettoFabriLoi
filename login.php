<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

session_start();

require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query per selezionare l'utente con la colonna della password corretta
    $sql = "SELECT id_utente, username, pw FROM utente WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();


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
=======
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // Verifica la password
        if (password_verify($password, $row['pw'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $row['id_utente'];
            $_SESSION['username'] = $row['username'];
            header("Location: my_profile.php");
            exit();
>>>>>>> 4e872e2 (tes5t)
        } else {
            echo "Password errata.";
        }
    } else {
        echo "Nessun utente trovato con questo username.";
    }

    $stmt->close();
}

$conn->close();
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
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>