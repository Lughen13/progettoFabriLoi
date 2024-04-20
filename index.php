<?php
// Connessione al DB
$host = "localhost";
$user = "root";
$password = "";
$db = "progettoFabriLoi";

$conn = mysqli_connect($host, $user, $password, $db);

if(!$conn){
    die("Connessione fallita: " . mysqli_connect_error());
}

// Logica di registrazione
if($_SERVER['REQUEST_METHOD'] == 'POST'){

  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  
  $password_hash = password_hash($password, PASSWORD_DEFAULT);

  $sql = "INSERT INTO utente (username, email, pw) VALUES ('$username', '$email', '$password_hash')";

  if(mysqli_query($conn, $sql)){
    echo "Registrazione avvenuta con successo!";
  } else {
    echo "Errore: " . mysqli_error($conn);
  }

}

?>

<!DOCTYPE html>
<html>
<head>
  <title>Registrazione</title>
</head>
<body>

  <h1>Registrazione</h1>
  
  <form method="post">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username"><br><br>

    <label for="email">Email:</label>    
    <input type="email" id="email" name="email"><br><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password"><br><br>

    <input type="submit" value="Registrati">
  </form>

</body>
</html>
