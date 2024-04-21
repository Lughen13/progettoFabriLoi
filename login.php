<?php
// Avvia sessione
session_start();
if(isset($_SESSION['utente'])) {
    header("Location: area_protetta.php");
    exit;
} 
// Include file di connessione db
include 'connessione.php';

// Definisci variabili errore
$usernameErr = $emailErr = $passwordErr = "";
$username = $email = $password = "";

// Validazione form
if($_SERVER['REQUEST_METHOD'] == 'POST') {

  // Validazione username
  if(empty($_POST['username'])) {
    $usernameErr = "Inserisci username";
  } else {
    $username = test_input($_POST['username']);
  }

  // Validazione email
  if(empty($_POST['email'])) {
    $emailErr = "Inserisci email";
  } else {
    $email = test_input($_POST['email']);
  }
  // Validazione password
  if(empty($_POST['password'])) {
    $passwordErr = "Inserisci password";
  } else {
    $password = test_input($_POST['password']); 
  }

  // Se non ci sono errori
  if($usernameErr == '' && $passwordErr == '') {

    // Query db per recuperare utente
  $sql = "SELECT * FROM utenti WHERE username = ? AND email = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "ss", $username, $email);
  

// Recupera utente
    $user = mysqli_fetch_assoc($result);
    
// Verifica password
if(password_verify($password, $user['password'])) {

    // Salva dati utente in sessione
    $_SESSION['utente'] = $user;

    // Reindirizza alla pagina protetta
    header("Location: area_protetta.php");
    exit();

} else {
    $passwordErr = "Password errata";
}

}

}

// Pulisce input
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
?>

<!-- HTML form -->

<h2>Login</h2>

<p>Inserisci le tue credenziali per accedere:</p>

<p style="color: red;"><?php echo $usernameErr; ?></p>
<label>Username:</label>
<input type="text" name="username" value="<?php echo $username; ?>">

<p style="color: red;"><?php echo $emailErr; ?></p> 
<label>Email:</label>
<input type="email" name="email" value="<?php echo $email; ?>">

<p style="color: red;"><?php echo $passwordErr; ?></p>
<label>Password:</label>
<input type="password" name="password" value="<?php echo $password; ?>">

<input type="submit" value="Login">
</form>
