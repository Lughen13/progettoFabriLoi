<?php
// Connessione al database
$host = "localhost";
$user = "root";
$password = ""; // Inserisci la password di root per MAMP
$database = "progettoFabriLoi";

$conn = new mysqli($host, $user, $password, $database);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Funzione per la validazione dei dati di input
function validateInput($data) {
    if (is_string($data)) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
    } else {
        $data = "";
    }
    return $data;
}


// Inizializzazione delle variabili
$nome = $cognome = $username = $password = $email = $data_nascita = $genere = $numero_telefono = $intestatario = $carta = $data_scadenza = "";
$nome_err = $cognome_err = $username_err = $password_err = $email_err = $data_nascita_err = $genere_err = $numero_telefono_err = $intestatario_err = $carta_err = $data_scadenza_err = "";
$premium = 0; // Imposta il valore predefinito di $premium a 0 (non premium)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validazione nome
    $nome = validateInput($_POST["nome"]);
    if (empty($nome)) {
        $nome_err = "Inserisci il tuo nome.";
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $nome)) {
        $nome_err = "Il nome può contenere solo lettere e spazi.";
    }

    // Validazione cognome
    $cognome = validateInput($_POST["cognome"]);
    if (empty($cognome)) {
        $cognome_err = "Inserisci il tuo cognome.";
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $cognome)) {
        $cognome_err = "Il cognome può contenere solo lettere e spazi.";
    }

    // Validazione username
    $username = validateInput($_POST["username"]);
    if (empty($username)) {
        $username_err = "Inserisci un username.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]*$/", $username)) {
        $username_err = "Il username può contenere solo lettere, numeri e underscore.";
    } else {
        // Controllo se l'username esiste già nel database
        $sql = "SELECT id_utente FROM utente WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $username_err = "Questo username è già in utilizzo.";
        }
        $stmt->close();
    }

    // Validazione password
    $password = validateInput($_POST["password"]);
    if (empty($password)) {
        $password_err = "Inserisci una password.";
    } elseif (strlen($password) < 6) {
        $password_err = "La password deve essere lunga almeno 6 caratteri.";
    }
// Validazione conferma password
$confirm_password = validateInput($_POST["confirm_password"]);
if (empty($confirm_password)) {
    $password_err = "Conferma la password.";
} elseif ($confirm_password != $password) {
    $password_err = "Le password non corrispondono.";
}
    // Validazione email
    $email = validateInput($_POST["email"]);
    if (empty($email)) {
        $email_err = "Inserisci un'email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Formato email non valido.";
    } else {
        // Controllo se l'email esiste già nel database
        $sql = "SELECT id_utente FROM utente WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $email_err = "Questa email è già stata utilizzata.";
        }
        $stmt->close();
    }

    // Validazione data di nascita
    $data_nascita = validateInput($_POST["data_nascita"]);
    if (empty($data_nascita)) {
        $data_nascita_err = "Inserisci la tua data di nascita.";
    } else {
        $data_nascita_obj = DateTime::createFromFormat('Y-m-d', $data_nascita);
        if (!$data_nascita_obj || $data_nascita_obj->format('Y-m-d') != $data_nascita) {
            $data_nascita_err = "Formato data di nascita non valido.";
        }
    }

    // Validazione genere
    $genere = isset($_POST["genere"]) ? validateInput($_POST["genere"]) : "";

    if (empty($genere)) {
        $genere_err = "Seleziona il tuo genere.";
    } elseif (!in_array($genere, array("Maschio", "Femmina", "Altro"))) {
        $genere_err = "Valore genere non valido.";
    }

    // Validazione numero di telefono
    $numero_telefono = validateInput($_POST["numero_telefono"]);
    if (!empty($numero_telefono) && !preg_match("/^[0-9]{10}$/", $numero_telefono)) {
        $numero_telefono_err = "Il numero di telefono deve essere di 10 cifre.";
    }

    // Validazione premium
    $premium = isset($_POST["premium"]) ? 1 : 0;
    if ($premium == 1) {
        // Validazione intestatario
        $intestatario = validateInput($_POST["intestatario"]);
        if (empty($intestatario)) {
            $intestatario_err = "Inserisci l'intestatario della carta.";
        } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $intestatario)) {
            $intestatario_err = "L'intestatario può contenere solo lettere e spazi.";
        }

        // Validazione numero di carta
        $carta = validateInput($_POST["carta"]);
        if (empty($carta)) {
            $carta_err = "Inserisci il numero della carta.";
        } elseif (!preg_match("/^[0-9]{16}$/", $carta)) {
            $carta_err = "Il numero della carta deve essere di 16 cifre.";
        } else {
            // Controllo se il numero di carta esiste già nel database
            $sql = "SELECT id_utente FROM premium WHERE numero_carta = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $carta);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $carta_err = "Questo numero di carta è già stato registrato da un altro utente.";
            }
            $stmt->close();
        }

        // Validazione data di scadenza
        $data_scadenza = validateInput($_POST["data_scadenza"]);
        if (empty($data_scadenza)) {
            $data_scadenza_err = "Inserisci la data di scadenza della carta.";
        } else {
            $data_scadenza_obj = DateTime::createFromFormat('Y-m-d', $data_scadenza);
            if (!$data_scadenza_obj || $data_scadenza_obj->format('Y-m-d') != $data_scadenza) {
                $data_scadenza_err = "Formato data di scadenza non valido.";
            } elseif ($data_scadenza_obj < new DateTime()) {
                $data_scadenza_err = "La data di scadenza non può essere nel passato.";
            }
        }
    }

    // Se non ci sono errori, procedi con l'inserimento dei dati nel database
    if (empty($nome_err) && empty($cognome_err) && empty($username_err) && empty($password_err) && empty($email_err) && empty($data_nascita_err) && empty($genere_err) && empty($numero_telefono_err) && empty($intestatario_err) && empty($carta_err) && empty($data_scadenza_err)) {
        // Preparazione dell'istruzione SQL per l'inserimento nella tabella utente
        $sql = "INSERT INTO utente (username, email, pw, nome, cognome, genere, data_nascita, numero_telefono, premium) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Binding dei parametri per la tabella utente
        $param_username = $username;
        $param_email = $email;
        $param_password = password_hash($password, PASSWORD_DEFAULT);
        $param_nome = $nome;
        $param_cognome = $cognome;
        $param_genere = $genere;
        $param_data_nascita = $data_nascita;
        $param_numero_telefono = $numero_telefono;
        $param_premium = $premium;

        $stmt->bind_param("ssssssssi", $param_username, $param_email, $param_password, $param_nome, $param_cognome, $param_genere, $param_data_nascita, $param_numero_telefono, $param_premium);

        if ($stmt->execute()) {
            $ultimo_id = $stmt->insert_id;

            if ($premium == 1) {
                // Preparazione dell'istruzione SQL per l'inserimento nella tabella premium
                $sql_premium = "INSERT INTO premium (id_utente, intestatario, numero_carta, data_scadenza) VALUES (?, ?, ?, ?)";
                $stmt_premium = $conn->prepare($sql_premium);

                // Binding dei parametri per la tabella premium
                $param_intestatario = $intestatario;
                $param_carta = $carta;
                $param_data_scadenza = $data_scadenza;

                $stmt_premium->bind_param("isss", $ultimo_id, $param_intestatario, $param_carta, $param_data_scadenza);
                $stmt_premium->execute();
                $stmt_premium->close();
            }

            // Inserimento riuscito
            header("location: login.php");
            exit();
        } else {
            echo "Errore durante l'inserimento dei dati dell'utente: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione</title>
    <style>
        .error {color: red;}
    </style>
</head>
<body>
    <h2>Registrazione</h2>
    <p> Compila i seguenti campi per registrarti </p>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        <div>
            <label>Nome:</label>
            <input type="text" name="nome" value="<?php echo $nome;?>">
            <span class="error"><?php echo $nome_err;?></span>
        </div>
        <div>
            <label>Cognome:</label>
            <input type="text" name="cognome" value="<?php echo $cognome;?>">
            <span class="error"><?php echo $cognome_err;?></span>
        </div>
        <div>
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo $username;?>">
            <span class="error"><?php echo $username_err;?></span>
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="password" value="<?php echo $password;?>">
            <span class="error"><?php echo $password_err;?></span>
        </div>
        <div>
            <label for="confirm_password">Conferma Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" >
        </div>
        <div>
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo $email;?>">
            <span class="error"><?php echo $email_err;?></span>
        </div>
        <div>
            <label>Data di nascita:</label>
            <input type="date" name="data_nascita" value="<?php echo $data_nascita;?>">
            <span class="error"><?php echo $data_nascita_err;?></span>
        </div>
        <div>
            <label>Genere:</label>
            <select name="genere">
                <option value="" disabled selected>Seleziona il tuo genere</option>
                <option value="Maschio" <?php if($genere=="Maschio") echo "selected";?>>Maschio</option>
                <option value="Femmina" <?php if($genere=="Femmina") echo "selected";?>>Femmina</option>
                <option value="Altro" <?php if($genere=="Altro") echo "selected";?>>Altro</option>
            </select>
            <span class="error"><?php echo $genere_err;?></span>
        </div>
        <div>
            <label>Numero di telefono:</label>
            <input type="tel" name="numero_telefono" value="<?php echo $numero_telefono;?>">
            <span class="error"><?php echo $numero_telefono_err;?></span>
        </div>
        <div>
            <label>Premium:</label>
            <input type="checkbox" name="premium" value="1" <?php if($premium==1) echo "checked";?>>
        </div>
        <div id="premium-fields" style="display: <?php echo ($premium==1) ? 'block' : 'none';?>;">
            <div>
                <label>Intestatario:</label>
                <input type="text" name="intestatario" value="<?php echo $intestatario;?>">
                <span class="error"><?php echo $intestatario_err;?></span>
            </div>
            <div>
                <label>Numero di carta:</label>
                <input type="text" name="carta" value="<?php echo $carta;?>">
                <span class="error"><?php echo $carta_err;?></span>
            </div>
            <div>
                <label>Data di scadenza:</label>
                <input type="date" name="data_scadenza" value="<?php echo $data_scadenza;?>">
                <span class="error"><?php echo $data_scadenza_err;?></span>
            </div>
        </div>
        <div>
            <input type="submit" value="Registrati">
            <input type="reset" value="Reimposta">
        </div>
    </form>

    <script>
        // Mostra/nascondi i campi per il premium
        var premiumCheckbox = document.querySelector('input[name="premium"]');
        var premiumFields = document.getElementById('premium-fields');

        premiumCheckbox.addEventListener('change', function() {
            if (this.checked) {
                premiumFields.style.display = 'block';
            } else {
                premiumFields.style.display = 'none';
            }
        });
    </script>
</body>
</html>