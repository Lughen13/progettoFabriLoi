<?php
    // Connessione al database
    require_once('connessione.php');

    // // // Controllo se il form è stato inviato
    //  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //  // Preparazione dei dati
    //     $username = $db->real_escape_string($_POST['username']);
    //     $email = $db->real_escape_string($_POST['email']);
    //     $password = $db->real_escape_string($_POST['password']);

    //    // Inserimento dei dati nel database
    //    $query = "INSERT INTO utenti (username, email, password) VALUES ('$username', '$email', '$password')";

    //      if ($db->query($query) === TRUE) {
    //         echo "Registrazione avvenuta con successo!";
    // } else {
    //        echo "Errore: " . $query . "<br>" . $db->error;
    //     }
    //  }

    // $db->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrazione</title>
    <style>
       
    </style>
</head>
<body>
    <h2>Registrazione</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <label for="username">Nome utente:</label><br>
        <input type="text" id="username" name="username" required><br>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br>
        <input type="submit" value="Registrati">
    </form>
</body>
</html>
