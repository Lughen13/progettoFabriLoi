<?php
// Connessione al database
include 'conn.php';

// Inizializzazione delle variabili
$username = $password = "";
$username_err = $password_err = $login_err = "";

session_start();

// Elaborazione dei dati del modulo quando viene inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validazione username
    $username = trim($_POST["username"]);
    if (empty($username)) {
        $username_err = "Inserisci un username.";
    }

    // Validazione password
    $password = trim($_POST["password"]);
    if (empty($password)) {
        $password_err = "Inserisci una password.";
    }

    // Verifica delle credenziali
    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id_utente, username, pw FROM utente WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $username, $hashed_password);
            if ($stmt->fetch()) {
                $password_crypt = md5($password);
                if ($password_crypt === $hashed_password){
                  //  session_start();
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = $username;
                    header("location: home.php");
                } else {
                    $login_err = "Username o password non validi.";
                }
            }
        } else {
            $login_err = "Username o password non validi.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        .error {color: red;}
    </style>
</head>
<body style=" text-align: center;">
    <h2>Login</h2>
    <p>Per favore inserisci le tue credenziali per accedere alla Home.</p>
    <?php if (!empty($login_err)) { ?>
        <div class="error"><?php echo $login_err; ?></div>
    <?php } ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div>
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo $username; ?>">
            <span class="error"><?php echo $username_err; ?></span>
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="password">
            <span class="error"><?php echo $password_err; ?></span>
        </div>
        <div>
            <input type="submit" value="Accedi">
        </div>
        <p> Se non hai un account puoi <a href="registrazione.php"> registrarti qui</a>.</p>
    </form>
</body>
</html>

