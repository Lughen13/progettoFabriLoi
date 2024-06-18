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
                $password_hash = md5($password);
                if ($password_hash === $hashed_password){
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = $username;
                    // $_SESSION['genere'] = $db_genere; // Not sure where $db_genere comes from
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
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }
        h2 {
            margin-bottom: 10px;
        }
        form {
            max-width: 300px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin: 10px 0;
            font-weight: bold;
        }
        input[type="text"], input[type="password"], input[type="submit"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        a {
            text-decoration: none;
            color: #007bff;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>Login</h2>
    <p>Per favore inserisci le tue credenziali per accedere alla Home.</p>
    <?php if (!empty($login_err)) { ?>
        <div class="error"><?php echo $login_err; ?></div>
    <?php } ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div>
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>">
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