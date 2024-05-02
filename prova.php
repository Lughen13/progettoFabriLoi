<?php
// Connessione DB
$host = "localhost";
$user = "root";
$pass = "";  
$db = "progettoFabriLoi";

$conn = mysqli_connect($host, $user, $pass, $db);

if(!$conn){
  die("Connessione fallita: " . mysqli_connect_error());
}

// Invio form  
if($_SERVER['REQUEST_METHOD'] == 'POST') {

  // Dati utente
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $nome = $_POST['nome'];
  $cognome = $_POST['cognome'];
  $genere = $_POST['genere'];
  $data_nascita = $_POST['data_nascita'];

  

  // Query inserimento utente
  $sql = "INSERT INTO utente (username, email, pw, nome, cognome, genere, data_nascita)
          VALUES ('$username', '$email', '$password', '$nome', '$cognome', '$genere', '$data_nascita')";

  if(mysqli_query($conn, $sql)){

// Dati premium 
if(isset($_POST['premium'])) {

      // Dati premium
      $intestatario = $_POST['intestatario'];
      $numero_carta = $_POST['numero_carta'];
      $data_scadenza = $_POST['data_scadenza'];

      // Query inserimento dati premium
      $sqlPremium = "INSERT INTO premium (intestatario, numero_carta, data_scadenza, id_utente)
                     VALUES ('$intestatario', '$numero_carta', '$data_scadenza', {$conn->insert_id})";

      mysqli_query($conn, $sqlPremium);

    }
    
    echo "Registrazione completata!";

  } else {
    echo "Errore: " . mysqli_error($conn);
  }

}

?>

<!-- Form HTML -->

<h2>Registrazione</h2>

<form method="post">

  <input type="text" name="username" placeholder="Username" required>

  <input type="email" name="email" placeholder="Email" required>

  <input type="password" name="password" placeholder="Password" required>

  <input type="text" name="nome" placeholder="Nome" required>

  <input type="text" name="cognome" placeholder="Cognome" required>

  <label for="genere">Genere:</label>
  <select name="genere">
    <option value="M">Maschio</option>
    <option value="F">Femmina</option> 
  </select>

  <label for="data_nascita">Data di nascita:</label>
  <input type="date" name="data_nascita" required>

  <label for="premium">Sottoscrivi Premium:</label>
  <input type="checkbox" id="premium" name="premium">

  <div id="premium-data" style="display:none;">

  <div id="premium-data">
    <input type="text" name="intestatario" placeholder="Intestatario carta">
    <input type="text" name="numero_carta" placeholder="Numero carta">
    <label for="data_scadenza">Data di scadenza:</label>
    <input type="date" name="data_scadenza">
  </div>

  <input type="submit" value="Registrati">
  
</form>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

$(document).ready(function() {

  $('#premium').change(function() {
    if(this.checked) {
      $('#premium-data').show();
    }
    else {
      $('#premium-data').hide();
    }
  });

});

</script>