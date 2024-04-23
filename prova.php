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

// Invio form
if($_SERVER["REQUEST_METHOD"] == "POST") {

  // Dati utente
  $username = $_POST["username"];
  $email = $_POST["email"];
  $password = $_POST["password"];
  $nome = $_POST["nome"];
  $cognome = $_POST["cognome"];
  $genere = $_POST["genere"];
  $data_nascita = $_POST["data_nascita"];

  // Dati premium
  $premium = $_POST["premium"];
  $intestatario = "";
  $carta = "";
  $data_scadenza = "";

  if($premium == 1){
    $intestatario = $_POST["intestatario"];
    $carta = $_POST["carta"];
    $data_scadenza = $_POST["data_scadenza"];
  }

  // Query inserimento dati
  if($premium == 1){
    
    $sql = "INSERT INTO utente (username, email, pw, nome, cognome, genere, data_nascita, premium, intestatario, numero_carta, data_scadenza) 
            VALUES ('$username', '$email', '$password', '$nome', '$cognome', '$genere', '$data_nascita', $premium, '$intestatario', '$numero_carta', '$data_scadenza')";

  } else {

    $sql = "INSERT INTO utente (username, email, pw, nome, cognome, genere, data_nascita)
            VALUES ('$username', '$email', '$password', '$nome', '$cognome', '$genere', '$data_nascita')";
  
  }

  // Esecuzione e gestione errore
  if(mysqli_query($conn, $sql)){

    echo "Registrazione effettuata con successo!";

  } else {

    echo "Errore: " . mysqli_error($conn);
  
  }

}

?>


<!-- Form HTML -->

<h2>Registrazione</h2>

<form method="post">

  <div>
    <label for="username">Username</label>
    <input type="text" name="username" required>
  </div>

  <div>
    <label for="email">Email</label>
    <input type="email" name="email" required>
  </div>

  <div>
    <label for="password">Password</label>
    <input type="password" name="password" required>
  </div>

  <div>
    <label for="nome">Nome</label>
    <input type="text" name="nome" required>
  </div>

  <div>
    <label for="cognome">Cognome</label>
    <input type="text" name="cognome" required>
  </div>

  <div>
    <label for="genere">Genere</label>
    <select name="genere">
      <option value="M">Maschio</option>
      <option value="F">Femmina</option>
      <option value="A">Altro</option>
    </select>
  </div>

  <div>
    <label for="data_nascita">Data di nascita</label>
    <input type="date" name="data_nascita" required> 
  </div>

  <div>
    <input type="checkbox" name="premium" value="1"> Account Premium
  </div>

  <div id="payment-data" style="display:none">

    <h3>Dati di pagamento</h3>
  
    <div>
      <label for="intestatario">Intestatario</label>
      <input type="text" name="intestatario">
    </div>

    <div>
      <label for="carta">Numero Carta</label>
      <input type="text" name="numero_carta">
    </div>

    <div>
      <label for="data_scadenza">Data Scadenza</label>
      <input type="date" name="data_scadenza">
    </div>

  </div>

  <input type="submit" value="Registrati">

</form>

<script>
// Mostra/nascondi dati pagamento
const premiumCheck = document.querySelector('input[name="premium"]');
const paymentData = document.getElementById('payment-data');

premiumCheck.addEventListener('change', function() {
  if(this.checked) {
    paymentData.style.display = 'block';
  } else {
    paymentData.style.display = 'none';
  }
})
</script>