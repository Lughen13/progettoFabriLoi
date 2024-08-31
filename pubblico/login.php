<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
// Connessione al database
require_once '../configurazione/conn.php';

// inizializzo le variabili delle variabili
$username = $password = "";
$username_err = $password_err = $login_err = "";

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // valido username
    $username = trim($_POST["username"]);
    if (empty($username)) {
        $username_err = "Inserisci un username.";
    }

    // valido password
    $password = trim($_POST["password"]);
    if (empty($password)) {
        $password_err = "Inserisci una password.";
    }

    // verifica delle credenziali d'accesso
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
                if ($password_hash === $hashed_password) {
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = $username;
                    header("location: ../pubblico/home.php");
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
    <link rel="stylesheet" href="../risorse/stile.css">
    <style>

        body {
            text-align: center;
        }
        form {
   max-width: 300px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
        }
        input[type="text"],  input[type="password"], 
        button {
            width: calc(100% - 22px);
            padding: 8px;
            margin: 10px 0;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
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
    <h1>
        <img src="../blog_logo/logo.png" alt="Logo ToteBlog" style="max-width: 25%; height: auto;">
    </h1>
    <h2>Login</h2>
    <p>Inserisci le tue credenziali per accedere alla Home.</p>
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
            <button type="submit">Accedi</button>
        </div>
        <p> Se non hai un account puoi <a href="../pubblico/registrazione.php"> registrarti qui</a>.</p>
    </form>
</body>

</html>