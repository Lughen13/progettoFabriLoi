<?php
// Includi il file di connessione al database
require_once 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Inizializza le variabili
$username = $password = "";
$username_err = $password_err = "";

// Elaborazione dei dati del modulo quando viene inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validazione username
    $username = trim($_POST["username"]);
    if (empty($username)) {
        $username_err = "Per favore inserisci il tuo username.";
    }

    // Validazione password
    $password = trim($_POST["password"]);
    if (empty($password)) {
        $password_err = "Per favore inserisci la tua password.";
    }

    // Verifica le credenziali
    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id_utente, username, pw FROM utente WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $param_username);
        $param_username = $username;

        if ($stmt->execute()) {
            $stmt->store_result();

            // Verifica se l'username esiste
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $username, $hashed_password);
                if ($stmt->fetch()) {
                    if (password_verify($password, $hashed_password)) {
                        // Credenziali corrette, avvia una nuova sessione
                        session_start();
                        $_SESSION["loggedin"] = true;
                        $_SESSION["user_id"] = $id; // Imposta l'ID dell'utente nella sessione
                        $_SESSION["username"] = $username;
                    
                        // Reindirizza l'utente alla dashboard dopo il login
                        header("location: dashboard.php");
                        exit();
                    };
                    } else {
                        // Password non corretta
                        $password_err = "Password non corretta.";
                    }
                }
            } else {
                // Username non trovato
                $username_err = "Username non trovato.";
            }
        } else {
            echo "Ops! Qualcosa è andato storto. Riprova più tardi.";
        }

        $stmt->close();
    }

    $conn->close();

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        .error {color: red;}
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#login-form').submit(function(e) {
            e.preventDefault(); // Previene il comportamento di default del form

            // Invia la richiesta AJAX
            $.ajax({
                type: 'POST',
                url: 'login.php',
                data: $(this).serialize(), // Serializza i dati del form
                success: function(response) {
    if (response === 'success') {
        console.log('Login riuscito');
        // Reindirizza l'utente a dashboard.php
        window.location.href = 'dashboard.php';
    } else {
        // Mostra un messaggio di errore
        $('#error-message').text(response);
    }
}

            });
        });
    });
    </script>
</head>
<body>
    <h2>Login</h2>
    <p>Per favore inserisci le tue credenziali per accedere.</p>
    <div id="error-message" class="error"></div>
    <form id="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div>
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo $username; ?>">
            <span class="error">* <?php echo $username_err; ?></span>
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="password">
            <span class="error">* <?php echo $password_err; ?></span>
        </div>
        <div>
            <input type="submit" value="Accedi">
        </div>
        <p>Non hai un account? <a href="registrazione.php">Registrati qui</a>.</p>
    </form>
</body>
</html>
