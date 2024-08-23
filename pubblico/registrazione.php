<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

// Connessione al database
require_once '../configurazione/conn.php';

$conn = new mysqli($host, $user, $password, $database);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Funzione per ripulire i dati in input prima che entrino nel db
function validateInput($data)
{
    if (is_string($data)) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
    } else {
        $data = "";
    }
    return $data;
}
// Gestione delle richieste AJAX
if (isset($_POST['action']) && $_POST['action'] == 'validate') {
    $response = array();

    // Validazione nome
    if (isset($_POST["nome"])) {
        $nome = validateInput($_POST["nome"]);
        if (empty($nome)) {
            $response['nome'] = "Inserisci il tuo nome.";
        } elseif (strlen($nome) < 3) {
            $response['nome'] = "Il nome deve contenere almeno 3 caratteri.";
        } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $nome)) {
            $response['nome'] = "Il nome può contenere solo lettere e spazi.";
        }
    }

    // Validazione cognome
    if (isset($_POST["cognome"])) {
        $cognome = validateInput($_POST["cognome"]);
        if (empty($cognome)) {
            $response['cognome'] = "Inserisci il tuo cognome.";
        } elseif (strlen($cognome) < 3) {
            $response['cognome'] = "Il cognome deve contenere almeno 3 caratteri.";
        } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $cognome)) {
            $response['cognome'] = "Il cognome può contenere solo lettere e spazi.";
        }
    }

    // Validazione username
    if (isset($_POST["username"])) {
        $username = validateInput($_POST["username"]);
        if (empty($username)) {
            $response['username'] = "Inserisci un username.";
        } elseif (!preg_match("/^[a-zA-Z0-9_]*$/", $username)) {
            $response['username'] = "Il username può contenere solo lettere, numeri e underscore.";
        } else {
            // Controllo se l'username esiste già nel database
            $sql = "SELECT id_utente FROM utente WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $response['username'] = "Questo username è già in utilizzo.";
            }
            $stmt->close();
        }
    }

    // Validazione email
    if (isset($_POST["email"])) {
        $email = validateInput($_POST["email"]);
        if (empty($email)) {
            $response['email'] = "Inserisci un'email.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['email'] = "Formato email non valido.";
        } else {
            // Controllo se l'email esiste già nel database
            $sql = "SELECT id_utente FROM utente WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $response['email'] = "Questa email è già stata utilizzata.";
            }
            $stmt->close();
        }
    }

    // Validazione data di nascita
    if (isset($_POST["data_nascita"])) {
        $data_nascita = validateInput($_POST["data_nascita"]);
        if (empty($data_nascita)) {
            $response['data_nascita'] = "Inserisci la tua data di nascita.";
        } else {
            $data_nascita_obj = DateTime::createFromFormat('Y-m-d', $data_nascita);
            $min_data = new DateTime("1910-01-01");
            $max_data = (new DateTime())->sub(new DateInterval('P16Y'));

            if (!$data_nascita_obj || $data_nascita_obj->format('Y-m-d') != $data_nascita) {
                $response['data_nascita'] = "Formato data di nascita non valido.";
            } elseif ($data_nascita_obj < $min_data || $data_nascita_obj > $max_data) {
                $response['data_nascita'] = "La data di nascita deve essere dal primo gennaio 1910 in poi, oppure devi avere almeno 16 anni.";
            }
        }
    }

    // Validazione numero di telefono
    if (isset($_POST["numero_telefono"])) {
        $numero_telefono = validateInput($_POST["numero_telefono"]);
        if (!empty($numero_telefono) && !preg_match("/^[0-9]{10}$/", $numero_telefono)) {
            $response['numero_telefono'] = "Il numero di telefono deve essere di 10 cifre.";
        }
    }


    // Validazione password
    if (isset($_POST["password"])) {
        $password = validateInput($_POST["password"]);
        if (empty($password)) {
            $response['password'] = "Inserisci una password.";
        } elseif (strlen($password) < 8) {
            $response['password'] = "La password deve essere di minimo 8 caratteri.";
        } elseif (strlen($password) > 60) {
            $response['password'] = "La password non deve superare i 60 caratteri.";
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $response['password'] = "La password deve contenere almeno una lettera maiuscola.";
        } elseif (!preg_match('/[\W]/', $password)) { // \W = /[!@#$%^&*(),.?":{}|<>]/
            $response['password'] = "La password deve contenere almeno un carattere speciale.";
        }
    }

    // Validazione conferma password
    if (isset($_POST["confirm_password"])) {
        $confirm_password = validateInput($_POST["confirm_password"]);
        if (empty($confirm_password)) {
            $response['confirm_password'] = "Conferma la password.";
        } elseif ($confirm_password != $password) {
            $response['confirm_password'] = "Le password non corrispondono.";
        }
    }

    echo json_encode($response);
    exit();
}
// Inizializzazione delle variabili
$nome = $cognome = $username = $password = $confirm_password = $email = $data_nascita = $genere = $numero_telefono = $intestatario = $carta = $data_scadenza = "";
$nome_err = $cognome_err = $username_err = $password_err = $email_err = $data_nascita_err = $genere_err = $numero_telefono_err = $intestatario_err = $carta_err = $data_scadenza_err = $confirm_password_err =  "";
$premium = 0; // premium è di default 0 fino a quando non si fa check

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
    } elseif (strlen($password) < 8) {
        $password_err = "La password deve essere di minimo 8 caratteri.";
    } elseif (strlen($password) > 60) {
        $password_err = "La password non deve superare i 60 caratteri.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $password_err = "La password deve contenere almeno una lettera maiuscola.";
    } elseif (!preg_match('/[\W]/', $password)) {
        $password_err = "La password deve contenere almeno un carattere speciale.";
    }

    // Conferma password
    $confirm_password = validateInput($_POST["confirm_password"]);
    if (empty($confirm_password)) {
        $confirm_password_err = "Conferma la password.";
    } elseif ($confirm_password != $password) {
        $confirm_password_err = "Le password non corrispondono.";
    }

    // Validazione email
    $email = validateInput($_POST["email"]);
    if (empty($email)) {
        $email_err = "Inserisci un'email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Formato email non valido.";
    } else {

        // controllo per verificare che la mail non sia già stata utilizzata da altri utenti 
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
        $min_data = new DateTime("1910-01-01"); // la data minima da poter inserire è l primo gennaio 1910
        $max_data = (new DateTime())->sub(new DateInterval('P16Y')); // ad oggi (data d'iscrizione) l'utente abbia almeno 16 anni 

        if (!$data_nascita_obj || $data_nascita_obj->format('Y-m-d') != $data_nascita) {
            $data_nascita_err = "Formato data di nascita non valido.";
        } elseif ($data_nascita_obj < $min_data || $data_nascita_obj > $max_data) {
            $data_nascita_err = "La data di nascita deve essere dal primo gennaio 1910 in poi, oppure devi avere almeno 16 anni.";
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
                $carta_err = "Questo numero di carta è già stato utilizzato da un altro utente.";
            }
            $stmt->close();
        }

        // Validazione data di scadenza carta
        $data_scadenza = validateInput($_POST["data_scadenza"]);
        if (empty($data_scadenza)) {
            $data_scadenza_err = "Inserisci la data di scadenza della carta.";
        } else {
            $data_scadenza_obj = DateTime::createFromFormat('Y-m-d', $data_scadenza);
            if (!$data_scadenza_obj || $data_scadenza_obj->format('Y-m-d') != $data_scadenza) {
                $data_scadenza_err = "Formato data di scadenza non valido.";
            } else {
                $current_date = new DateTime();
                if ($data_scadenza_obj < $current_date) {
                    $data_scadenza_err = "La data di scadenza deve essere nel futuro.";
                }
            }
        }
    }

    $imgprofilo = '../uploads/predefinita.jpeg';

        // se tutto è stato inserito correttamente si procede con l'inserimento dell'utente nella tabella del DB
        if (empty($nome_err) && empty($cognome_err) && empty($username_err) && empty($password_err) && empty($email_err) && empty($data_nascita_err) && empty($genere_err) && empty($numero_telefono_err) && empty($intestatario_err) && empty($carta_err) && empty($data_scadenza_err)) {

            $sql = "INSERT INTO utente (username, email, pw, img_profilo, nome, cognome, genere, data_nascita, numero_telefono, premium) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            // Binding dei parametri per la tabella utente
            $param_username = $username;
            $param_email = $email;
            $password_crypt = md5($password);
            $param_nome = $nome;
            $param_cognome = $cognome;
            $param_genere = $genere;
            $param_data_nascita = $data_nascita;
            $param_numero_telefono = $numero_telefono;
            $param_premium = $premium;
            $param_imgprofilo = $imgprofilo;

            $stmt->bind_param("sssssssssi", $param_username, $param_email, $password_crypt, $param_imgprofilo, $param_nome, $param_cognome, $param_genere, $param_data_nascita, $param_numero_telefono, $param_premium);

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
                header("location: ../pubblico/login.php");
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
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            text-align: center;
        }

        .container {
            width: 50%;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
        }

        .container h2 {
            color: #333333;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .form-group select {
            width: calc(100%);
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group input[type="checkbox"] {
            width: auto;
        }

        button{
            padding: 10px;
            background-color: #4CAF50;
            border: none;
            color: white;
            border: 1px solid #cccccc;
            border-radius: 5px;
            width: calc(100%);
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2>Registrazione</h2>
        <p>Compila i seguenti campi per registrarti</p>
        <form id="registration-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome); ?>" aria-describedby="nome_err">
                <span class="error" id="nome_err"><?php echo htmlspecialchars($nome_err); ?></span>
            </div>
            <div class="form-group">
                <label for="cognome">Cognome:</label>
                <input type="text" id="cognome" name="cognome" value="<?php echo htmlspecialchars($cognome); ?>" aria-describedby="cognome_err">
                <span class="error" id="cognome_err"><?php echo htmlspecialchars($cognome_err); ?></span>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" aria-describedby="username_err">
                <span class="error" id="username_err"><?php echo htmlspecialchars($username_err); ?></span>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" aria-describedby="password_err">
                <span class="error" id="password_err"><?php echo htmlspecialchars($password_err); ?></span>
            </div>
            <div class="form-group">
                <label for="confirm_password">Conferma Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>" aria-describedby="confirm_password_err">
                <span class="error" id="confirm_password_err"><?php echo htmlspecialchars($confirm_password_err); ?></span>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" aria-describedby="email_err">
                <span class="error" id="email_err"><?php echo htmlspecialchars($email_err); ?></span>
            </div>
            <div class="form-group">
                <label for="data_nascita">Data di nascita:</label>
                <input type="date" id="data_nascita" name="data_nascita" value="<?php echo htmlspecialchars($data_nascita); ?>" aria-describedby="data_nascita_err">
                <span class="error" id="data_nascita_err"><?php echo htmlspecialchars($data_nascita_err); ?></span>
            </div>
            <div class="form-group">
                <label for="genere">Genere:</label>
                <select id="genere" name="genere" aria-describedby="genere_err">
                    <option value="" disabled <?php if (empty($genere)) echo 'selected'; ?>>Seleziona il tuo genere</option>
                    <option value="Maschio" <?php if ($genere == "Maschio") echo "selected"; ?>>Maschio</option>
                    <option value="Femmina" <?php if ($genere == "Femmina") echo "selected"; ?>>Femmina</option>
                    <option value="Altro" <?php if ($genere == "Altro") echo "selected"; ?>>Altro</option>
                </select>
                <span class="error" id="genere_err"><?php echo htmlspecialchars($genere_err); ?></span>
            </div>
            <div class="form-group">
                <label for="numero_telefono">Numero di telefono:</label>
                <input type="tel" id="numero_telefono" name="numero_telefono" value="<?php echo htmlspecialchars($numero_telefono); ?>" aria-describedby="numero_telefono_err">
                <span class="error" id="numero_telefono_err"><?php echo htmlspecialchars($numero_telefono_err); ?></span>
            </div>
            <div class="form-group">
                <label for="premium">Premium:</label>
                <input type="checkbox" id="premium" name="premium" value="1" <?php if ($premium == 1) echo "checked"; ?> aria-describedby="premium_fields">
            </div>
            <div id="premium-fields" style="display: <?php echo ($premium == 1) ? 'block' : 'none'; ?>;">
                <div class="form-group">
                    <label for="intestatario">Intestatario:</label>
                    <input type="text" id="intestatario" name="intestatario" value="<?php echo htmlspecialchars($intestatario); ?>" aria-describedby="intestatario_err">
                    <span class="error" id="intestatario_err"><?php echo htmlspecialchars($intestatario_err); ?></span>
                </div>
                <div class="form-group">
                    <label for="carta">Numero di carta di credito:</label>
                    <input type="text" id="carta" name="carta" value="<?php echo htmlspecialchars($carta); ?>" aria-describedby="carta_err">
                    <span class="error" id="carta_err"><?php echo htmlspecialchars($carta_err); ?></span>
                </div>
                <div class="form-group">
                    <label for="data_scadenza">Data di scadenza:</label>
                    <input type="date" id="data_scadenza" name="data_scadenza" value="<?php echo htmlspecialchars($data_scadenza); ?>" aria-describedby="data_scadenza_err">
                    <span class="error" id="data_scadenza_err"><?php echo htmlspecialchars($data_scadenza_err); ?></span>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" value="Registrati"> Registrati </button>
                <button type="reset" value="Reimposta"> Reimposta </button>
            </div>
        </form>
    </div>
    <script>
        function validateField(field, value) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        document.getElementById(field + '_err').textContent = response[field] || '';
                    } catch (e) {
                        console.error('Invalid JSON response:', e);
                    }
                }
            };
            xhr.send('action=validate&' + encodeURIComponent(field) + '=' + encodeURIComponent(value));
        }

        function validatePasswords() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errorElement = document.getElementById('confirm_password_err');

            if (password !== confirmPassword) {
                errorElement.textContent = 'Le password non coincidono.';
            } else {
                errorElement.textContent = '';
            }
        }


        function validateDateOfExpiry() {
            const input = document.getElementById('data_scadenza');
            const value = input.value;
            const today = new Date();
            const expiryDate = new Date(value);
            const errorElement = document.getElementById('data_scadenza_err');

            if (!value) {
                errorElement.textContent = 'La data di scadenza è obbligatoria.';
                return;
            }

            if (expiryDate <= today) {
                errorElement.textContent = 'La data di scadenza deve essere nel futuro.';
            } else {
                errorElement.textContent = '';
            }
        }

        function validateCreditCard() {
            const input = document.getElementById('carta');
            const value = input.value;
            const errorElement = document.getElementById('carta_err');
            const cleanedValue = value.replace(/\D/g, ''); // Rimuove caratteri non numerici

            if (cleanedValue.length !== 16) {
                errorElement.textContent = 'Il numero di carta deve contenere esattamente 16 cifre.';
            } else if (!/^\d+$/.test(cleanedValue)) { // Controlla se contiene solo numeri
                errorElement.textContent = 'Il numero di carta può contenere solo cifre.';
            } else {
                errorElement.textContent = '';
            }
        }
        // Gestione del cambio di valore dei campi
        document.getElementById('data_scadenza').addEventListener('input', validateDateOfExpiry);

        // Gestione del cambio di valore degli altri campi
        document.getElementById('nome').addEventListener('input', function() {
            const value = this.value;
            if (value.length < 3) {
                document.getElementById('nome_err').textContent = "Il nome deve contenere almeno 3 caratteri.";
            } else {
                validateField('nome', value);
            }
        });

        document.getElementById('cognome').addEventListener('input', function() {
            const value = this.value;
            if (value.length < 3) {
                document.getElementById('cognome_err').textContent = "Il cognome deve contenere almeno 3 caratteri.";
            } else {
                validateField('cognome', value);
            }
        });

        document.getElementById('username').addEventListener('input', function() {
            validateField('username', this.value);
        });

        document.getElementById('email').addEventListener('input', function() {
            validateField('email', this.value);
        });

        document.getElementById('password').addEventListener('input', function() {
            validateField('password', this.value);
            validatePasswords();
        });

        document.getElementById('confirm_password').addEventListener('input', function() {
            validatePasswords();
        });

        document.getElementById('data_nascita').addEventListener('input', function() {
            validateField('data_nascita', this.value);
        });

        document.getElementById('numero_telefono').addEventListener('input', function() {
            validateField('numero_telefono', this.value);
        });

        document.getElementById('data_scadenza').addEventListener('input', validateDateOfExpiry);

        document.getElementById('carta').addEventListener('input', validateCreditCard);

        document.getElementById('premium').addEventListener('change', function() {
            document.getElementById('premium-fields').style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>

</html>