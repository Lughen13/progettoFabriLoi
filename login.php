<?php
<<<<<<< HEAD
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
=======
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

>>>>>>> afc9fdd65011cc7f2102c70ad9074f882caf4561

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $username, $hashed_password);
            if ($stmt->fetch()) {
                $password_hash = md5($password);
                if ($password_hash === $hashed_password){
                  //  session_start();
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = $username;
                    header("location: home.php");
                } else {
                    $login_err = "Username o password non validi.";
                }
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
<<<<<<< HEAD
            $login_err = "Username o password non validi.";
        }

        $stmt->close();
=======
            echo "Password errata.";
        }
    } else {
        echo "Nessun utente trovato con questo username.";
>>>>>>> afc9fdd65011cc7f2102c70ad9074f882caf4561
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
</head>
<body>
    <h2>Login</h2>
<<<<<<< HEAD
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
=======
    <form method="post" action="login.php">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Login</button>
>>>>>>> afc9fdd65011cc7f2102c70ad9074f882caf4561
    </form>
</body>
</html>