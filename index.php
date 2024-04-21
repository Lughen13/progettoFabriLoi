<?php
// Connessione DB
$host = "localhost";
$user = "root"; 
$password = "";
$db = "progettoFabriLoi";

$conn = mysqli_connect($host, $user, $password, $db);

if(!$conn){
    die("Connessione fallita: " . mysqli_connect_error());
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  $nome = $_POST['nome'];
  $cognome = $_POST['cognome'];
  $genere = $_POST['genere'];
  $data_nascita = $_POST['data_nascita'];

 
  $password_hash = password_hash($password, PASSWORD_DEFAULT);  

  $premium = isset($_POST['premium']) ? 1 : 0;
  $intestatario = $_POST['intestatario'];
  $numero_carta = $_POST['numero_carta'];
  $data_scadenza = $_POST['data_scadenza'];
  $cvv = $_POST['cvv'];
  
  $sql = "INSERT INTO utente (username, email, pw, nome, cognome, genere, data_nascita, premium, intestatario, numero_carta, data_scadenza)
          VALUES ('$username', '$email', '$password_hash', '$nome', '$cognome', '$genere', '$data_nascita', '$premium', '$intestatario', '$numero_carta', '$data_scadenza')";
  
  echo "<script>console.log('ok');</script>";
  if(mysqli_query($conn, $sql)){
    echo "Registrazione avvenuta con successo!";
    // Reindirizza l'utente alla pagina di login
  header("Location: login.php"); 
  exit();
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

    <label for="nome">Nome:</label>
    <input type="text" id="nome" name="nome"><br><br>

    <label for="cognome">Cognome:</label>
    <input type="text" id="cognome" name="cognome"><br><br>

    <label for="genere">Genere:</label>
    <select id="genere" name="genere">
      <option value="M">Maschio</option>
      <option value="F">Femmina</option>
      <option value="A">Altro</option>
    </select><br><br>

    <label for="data_nascita">Data di nascita:</label>
    <input type="date" id="data_nascita" name="data_nascita"><br><br>

    <label for="premium">Utente Premium:</label>
    <input type="checkbox" id="premium" name="premium"><br><br>

    <label for="intestatario">Intestatario:</label>
    <input type="text" id="intestatario" name="intestatario"><br><br>

    <label for="numero_carta">Numero carta:</label>
    <input type="text" id="numero_carta" name="numero_carta"><br><br>

    <label for="data_scadenza">Data di scadenza:</label>
    <input type="date" id="data_scadenza" name="data_scadenza"><br><br>

    <label for="cvv">CVV:</label>
    <input type="text" id="cvv" name="cvv"><br><br>

    <input type="submit" value="Registrati">

  </form>

</body>
</html>
