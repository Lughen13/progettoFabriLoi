<?php
// Includi il file di connessione al DB
include 'connessione.php';

// Definisci variabili e impostale a valori vuoti
$username = $email = $password = $nome = $cognome = $genere = $data_nascita = $numero_telefono = "";
$username_err = $email_err = $password_err = $nome_err = $cognome_err = $genere_err = $data_nascita_err = $numero_telefono_err = "";

// Validazione dell'input e inserimento nel DB alla submit del form  
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validazione username
    if(empty(trim($_POST["username"]))){
        $username_err = "Inserire username";     
    } else{
        $username = trim($_POST["username"]);
    }

    // Validazione email
    if(empty(trim($_POST["email"]))){
        $email_err = "Inserire email";     
    } else{
        $email = trim($_POST["email"]);
    }

    // Validazione password
    if(empty(trim($_POST["password"]))){
        $password_err = "Inserire password";     
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validazione nome
    if(empty(trim($_POST["nome"]))){
        $nome_err = "Inserire nome";     
    } else{
        $nome = trim($_POST["nome"]);
    }

    // Validazione cognome 
    if(empty(trim($_POST["cognome"]))){
        $cognome_err = "Inserire cognome";     
    } else{
        $cognome = trim($_POST["cognome"]);
    }

    // Validazione genere
    if(empty(trim($_POST["genere"]))){
        $genere_err = "Inserire genere";     
    } else{
        $genere = trim($_POST["genere"]);
    }

    // Validazione data di nascita
    if(empty(trim($_POST["data_nascita"]))){
        $data_nascita_err = "Inserire data di nascita";     
    } else{
        $data_nascita = trim($_POST["data_nascita"]);
    }

    // Validazione numero di telefono
    if(empty(trim($_POST["numero_telefono"]))){
        $numero_telefono_err = "Inserire numero di telefono";     
    } else{
        $numero_telefono = trim($_POST["numero_telefono"]);
    }

    // Campi per iscrizione premium
    $intestatario = $carta = $data_scadenza = "";
    $intestatario_err = $carta_err = $data_scadenza_err = "";

    // Validazione intestatario
    if(empty(trim($_POST["intestatario"]))){
        $intestatario_err = "Inserire intestatario";
    } else{
        $intestatario = trim($_POST["intestatario"]); 
    }

    // Validazione numero carta
    if(empty(trim($_POST["carta"]))){
        $carta_err = "Inserire numero carta";
    } else {
        $carta = trim($_POST["carta"]);
    }

    // Validazione data scadenza  
    if(empty(trim($_POST["data_scadenza"]))){
        $data_scadenza_err = "Inserire data di scadenza";
    } else{
        $data_scadenza = trim($_POST["data_scadenza"]);
    }


    // Nella query SQL
    $sql = "INSERT INTO utente (username, email, pw, nome, cognome, genere, data_nascita, numero_telefono, intestatario, carta, data_scadenza) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Nel bind dei parametri
    $stmt->bind_param("ssssssissss", $username, $email, $password_hash, $nome, $cognome, $genere, $data_nascita, $numero_telefono, $intestatario, $carta, $data_scadenza);

    // Controlla gli errori prima di inserire nel DB
    if(empty($username_err) && empty($email_err) && empty($password_err) && empty($nome_err) && empty($cognome_err) && empty($genere_err) && empty($data_nascita_err) && empty($numero_telefono_err)){
        
        // Prepara la query
        $sql = "INSERT INTO utente (username, email, pw, nome, cognome, genere, data_nascita, numero_telefono) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
         
        if($stmt = $connessione->prepare($sql)){
            // Cripta la password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Associa le variabili alla query 
            $stmt->bind_param("ssssssis", $username, $email, $password_hash, $nome, $cognome, $genere, $data_nascita, $numero_telefono);
            
            // Esegui la query
            if($stmt->execute()){
                // Registrazione avvenuta con successo, reindirizza l'utente
                header("location: login.php");
            } else{
                echo "Errore nell'inserimento dei dati: " . $connessione->error;
            }

            // Chiudi statement
            $stmt->close();
        }
    }

    // Chiudi connessione
    $connessione->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registrazione</title>
</head>
<body>
    <div>
        <h2>Registrazione</h2>
        <p>Compila il form per creare un account</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div>
                <label>Username</label>
                <input type="text" name="username" value="<?php echo $username; ?>">
                <span><?php echo $username_err; ?></span>
            </div>    
            <div>
                <label>Email</label>
                <input type="text" name="email" value="<?php echo $email; ?>">
                <span><?php echo $email_err; ?></span>
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" value="<?php echo $password; ?>">
                <span><?php echo $password_err; ?></span>
            </div>
            <div>
                <label>Nome</label>
                <input type="text" name="nome" value="<?php echo $nome; ?>">
                <span><?php echo $nome_err; ?></span>
            </div>
            <div>
                <label>Cognome</label>
                <input type="text" name="cognome" value="<?php echo $cognome; ?>">
                <span><?php echo $cognome_err; ?></span>
            </div>
            <div>
                <label>Genere</label>
                <input type="text" name="genere" value="<?php echo $genere; ?>">
                <span><?php echo $genere_err; ?></span>
            </div>
            <div>
                <label>Data di nascita</label>
                <input type="date" name="data_nascita" value="<?php echo $data_nascita; ?>">
                <span><?php echo $data_nascita_err; ?></span>
            </div>
            <div>
                <label>Numero di telefono</label>
                <input type="text" name="numero_telefono" value="<?php echo $numero_telefono; ?>">
                <span><?php echo $numero_telefono_err; ?></span>
            </div>

            <div>
            <label>Intestatario</label>
            <input type="text" name="intestatario" value="<?php echo $intestatario; ?>">
            <span><?php echo $intestatario_err; ?></span>
            </div>

            <div>
            <label>Numero carta</label>
            <input type="text" name="carta" value="<?php echo $carta; ?>">
            <span><?php echo $carta_err; ?></span>  
            </div>

            <div>
            <label>Data di scadenza</label>
            <input type="date" name="data_scadenza" value="<?php echo $data_scadenza; ?>">
            <span><?php echo $data_scadenza_err; ?></span>
            </div>

            <input type="submit" value="Invia">
            <p>Hai già un account? <a href="login.php">Accedi</a></p>
        </form>
    </div>    
</body>
</html>
