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

if($_SERVER['REQUEST_METHOD'] == 'POST') {

// Dati utente
$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$nome = $_POST['nome'];
$cognome = $_POST['cognome'];
$genere = $_POST['genere'];
$data_nascita = $_POST['data_nascita'];
$telefono = $_POST['telefono'];

// Query inserimento utente
$sql = "INSERT INTO utente (username, email, pw, nome, cognome, genere, data_nascita, telefono)
        VALUES ('$username', '$email', '$password', '$nome', '$cognome', '$genere', '$data_nascita', '$telefono')";

// Esecuzione query
mysqli_query($conn, $sql);

// Dati premium c
if(isset($_POST['premium'])) {

  $intestatario = $_POST['intestatario'];
  $data_scadenza = $_POST['data_scadenza'];
  $numero_carta = $_POST['numero_carta'];

  // Query inserimento dati premium
  $sql = "INSERT INTO premium (intestatario, data_scadenza, numero_carta, utente_id)
          VALUES ('$intestatario', '$data_scadenza', '$numero_carta', {$conn->insert_id})";
  
  // Esecuzione query
  mysqli_query($conn, $sql);

}

// Conferma e reindirizzamento
echo "Registrazione completata!";
header("Location: home.php");
exit;

}
?>

<form method="post">
<div>
  <label for="username">Username:</label>
  <input type="text" id="username" name="username" required>
</div>
<div>
  <label for="email">Email:</label>  
  <input type="email" id="email" name="email" required>
</div>
<div>
  <label for="password">Password:</label>
  <input type="password" id="password" name="password" required minlength="8">
</div>
<div>
  <label for="nome">Nome:</label>
  <input type="text" id="nome" name="nome" required>
</div>
<div>
  <label for="cognome">Cognome:</label>
  <input type="text" id="cognome" name="cognome" required>
</div>
<div>
  <label for="genere">Genere:</label>
  <select id="genere" name="genere" required>
    <option value="M">Maschio</option>  
    <option value="F">Femmina</option>
    <option value="A">Altro</option>
    <option value="C">Croissant</option>
  </select>
  </div>

  <div>
  <label for="data_nascita">Data di Nascita:</label>
  <input type="date" id="data_nascita" name="data_nascita" required>
</div>
<div>
  <label for="telefono">Numero di Telefono:</label>
  <input type="tel" id="telefono" name="telefono" pattern="[0-9]{10}" required>
</div>
<div>
  <input type="checkbox" id="premium" name="premium">
  <label for="premium">Scegli account Premium</label>
</div>
  <div id="premium_fields" style="display:none;">

    <label for="intestatario">Intestatario:</label>
    <input type="text" id="intestatario" name="intestatario">

    <label for="data_scadenza">Data di Scadenza:</label>
    <input type="date" id="data_scadenza" name="data_scadenza"> 

    <label for="numero_carta">Numero Carta:</label>
    <input type="text" id="numero_carta" name="numero_carta">

  </div>

  <input type="submit" value="Registrati">

</form>

<script>
const premiumCheckbox = document.getElementById('premium');
const premiumFields = document.getElementById('premium_fields');

premiumCheckbox.addEventListener('change', function() {
  if(this.checked) {
    premiumFields.style.display = 'block'; 
  } else {
    premiumFields.style.display = 'none';
  }
})  
</script>
