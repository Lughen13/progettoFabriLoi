<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Se l'utente non è autenticato, reindirizza alla pagina di login
    header("Location: login.php");
    exit();
}

require_once 'conn.php';

// recupero i dati del'utente dal db, faccio la join perchè i dati degli utenti premium inseriscono i dati delle carte che vanno in una seconda tabella 
$userId = $_SESSION['id'];
$sql = "SELECT u.username, u.email, u.pw, u.nome, u.cognome, u.data_nascita, u.genere, u.numero_telefono, u.premium, p.intestatario, p.numero_carta, p.data_scadenza
        FROM utente u
        LEFT JOIN premium p ON u.id_utente = p.id_utente
        WHERE u.id_utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Variabili di errore
$passwordError = $emailError = $intestatario_err = $carta_err = $data_scadenza_err = "";

// Gestione dell'invio del modulo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oldPassword = $_POST['old_password'];
    $hashedPassword = $user['pw'];

    if (empty($oldPassword)) {
        $passwordError = "Inserisci la tua password per salvare le modifiche (in caso di modifica password insersici la precedente).";
    } elseif (md5($oldPassword) !== $hashedPassword) {
        $passwordError = "La password non è corretta.";
    } else {
        $passwordError = "";
        $updateFields = array();

        if (!empty($_POST["password"])) {
            $password = $_POST["password"];
            $password_hash = md5($password);
            $updateFields[] = "pw = '$password_hash'";
        }
  

            // Aggiorna le informazioni dell'utente
            if (isset($_POST['nome']) && !empty($_POST['nome'])) {
                $nome = $_POST['nome'];
                $updateFields[] = "nome = '$nome'";
            }

            if (isset($_POST['cognome']) && !empty($_POST['cognome'])) {
                $cognome = $_POST['cognome'];
                $updateFields[] = "cognome = '$cognome'";
            }

            if (isset($_POST['email']) && !empty($_POST['email'])) {
                $email = $_POST['email'];
                // Verifica se l'email è già in uso da un altro utente
                $checkEmailQuery = "SELECT id_utente FROM utente WHERE email = ? AND id_utente != ?";
                $stmt = $conn->prepare($checkEmailQuery);
                $stmt->bind_param("si", $email, $userId);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $emailError = "L'email inserita è già utilizzata da un altro utente.";
                } else {
                    $updateFields[] = "email = '$email'";
                }

                $stmt->close();
            }

            if (isset($_POST['data_nascita']) && !empty($_POST['data_nascita'])) {
                $data_nascita = $_POST['data_nascita'];
                $updateFields[] = "data_nascita = '$data_nascita'";
            }

            if (isset($_POST['genere']) && !empty($_POST['genere'])) {
                $genere = $_POST['genere'];
                $updateFields[] = "genere = '$genere'";
            }

            if (isset($_POST['numero_telefono']) && !empty($_POST['numero_telefono'])) {
                $numero_telefono = $_POST['numero_telefono'];
                $updateFields[] = "numero_telefono = '$numero_telefono'";
            }

            $premium = isset($_POST['premium']) ? 1 : 0;
            $updateFields[] = "premium = '$premium'";

            if ($premium == 1) {
                if (empty(trim($_POST["intestatario"]))) {
                    $intestatario_err = "Inserisci l'intestatario della carta.";
                } else {
                    $intestatario = trim($_POST["intestatario"]);
                }

                if (empty(trim($_POST["carta"]))) {
                    $carta_err = "Inserisci il numero della carta.";
                } elseif (!preg_match("/^[0-9]{16}$/", trim($_POST["carta"]))) {
                    $carta_err = "Il numero della carta deve essere di 16 cifre.";
                } else {
                    $carta = trim($_POST["carta"]);
                }

                if (empty(trim($_POST["data_scadenza"]))) {
                    $data_scadenza_err = "Inserisci la data di scadenza della carta.";
                } else {
                    $data_scadenza = trim($_POST["data_scadenza"]);
                }
            }

            // Procedi con l'aggiornamento del database solo se non ci sono errori
           if (empty($passwordError) && empty($emailError) && empty($intestatario_err) && empty($carta_err) && empty($data_scadenza_err)) {
                if (!empty($updateFields)) {
                    $updateQuery = "UPDATE utente SET " . implode(", ", $updateFields) . " WHERE id_utente = ?";
                    $stmt = $conn->prepare($updateQuery);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $stmt->close();
                }
                if ($premium == 1) {
                    if (empty(trim($_POST["intestatario"]))) {
                        $intestatario_err = "Inserisci l'intestatario della carta.";
                    } else {
                        $intestatario = trim($_POST["intestatario"]);
                    }
    
                    if (empty(trim($_POST["carta"]))) {
                        $carta_err = "Inserisci il numero della carta.";
                    } elseif (!preg_match("/^[0-9]{16}$/", trim($_POST["carta"]))) {
                        $carta_err = "Il numero della carta deve essere di 16 cifre.";
                    } else {
                        $carta = trim($_POST["carta"]);
                    }
    
                    if (empty(trim($_POST["data_scadenza"]))) {
                        $data_scadenza_err = "Inserisci la data di scadenza della carta.";
                    } else {
                        $data_scadenza = trim($_POST["data_scadenza"]);
                    }
    
                    if (empty($intestatario_err) && empty($carta_err) && empty($data_scadenza_err)) {
                        if (!isset($user['premium']) || ($user['premium'] == 0 && $premium == 1)) {
                            $insertCardQuery = "INSERT INTO premium (id_utente, intestatario, numero_carta, data_scadenza) VALUES (?, ?, ?, ?)";
                            $stmt = $conn->prepare($insertCardQuery);
                            $stmt->bind_param("isss", $userId, $intestatario, $carta, $data_scadenza);
                            $stmt->execute();
                            $stmt->close();
                        } else {
                            $updateCardQuery = "UPDATE premium SET intestatario = ?, numero_carta = ?, data_scadenza = ? WHERE id_utente = ?";
                            $stmt = $conn->prepare($updateCardQuery);
                            $stmt->bind_param("sssi", $intestatario, $carta, $data_scadenza, $userId);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                } elseif (isset($user['premium']) && $user['premium'] == 1) {
                    $deleteCardQuery = "DELETE FROM premium WHERE id_utente = ?";
                    $stmt = $conn->prepare($deleteCardQuery);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $stmt->close();
                }

                $successMessage = "Modifiche salvate con successo!";
                header("Location: home.php");
                exit();
            }
        }
    }

?>

<!DOCTYPE html>
<html>
<head>
    <title>Impostazioni Account</title>
</head>
<body style=" text-align: center;">
    <h1>Impostazioni Account</h1>
    <?php if (isset($successMessage) && !empty($successMessage)): ?>
        <?php echo $successMessage; ?>
    <?php endif; ?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <div>
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo $user['nome']; ?>">
    </div>

    <div> 
        <label for="cognome">Cognome:</label>
        <input type="text" id="cognome" name="cognome" value="<?php echo $user['cognome']; ?>">
    </div>
    
    <div> 
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>">
        <?php if (isset($emailError) && !empty($emailError)): ?>
            <p style="color: red;"><?php echo $emailError; ?></p>
        <?php endif; ?>
    </div>
    
    <div> 
        <label for="password">Nuova Password (lascia vuoto per non modificare):</label>
        <input type="password" id="password" name="password">
    </div>
    
    <div> 
        <label for="data_nascita">Data di Nascita:</label>
        <input type="date" id="data_nascita" name="data_nascita" value="<?php echo $user['data_nascita']; ?>">
    </div>
    
    <div> 
        <label for="genere">Genere:</label>
        <select id="genere" name="genere">
            <option value="">Seleziona il tuo genere</option>
            <option value="Maschio" <?php if($user['genere'] == "Maschio") echo "selected"; ?>>Maschio</option>
            <option value="Femmina" <?php if($user['genere'] == "Femmina") echo "selected"; ?>>Femmina</option>
            <option value="Altro" <?php if($user['genere'] == "Altro") echo "selected"; ?>>Altro</option>
        </select>
    </div>
    
    <div> 
        <label for="numero_telefono">Numero di Telefono:</label>
        <input type="text" id="numero_telefono" name="numero_telefono" value="<?php echo $user['numero_telefono']; ?>"> 
    </div>

    <div> 
        <label for="premium">Premium:</label>
        <input type="checkbox" id="premium" name="premium" value="1" <?php if (isset($user['premium']) && $user['premium'] == 1) echo 'checked'; ?>>
    </div>
    
    
    <div id="premiumInfo" style="display: <?php echo ($user['premium'] == 1) ? 'block' : 'none'; ?>;">
        <p>Intestatario, data di scadenza e numero di carta vanno inseriti tutti e tre anche solo per la modifica</p>
        <div>         
            <label for="intestatario">Intestatario:</label>
            <input type="text" id="intestatario" name="intestatario" value="<?php echo $user['intestatario']; ?>">
            <span class="error"><?php echo $intestatario_err; ?></span>
        </div>
        <div> 
            <label for="carta">Numero di Carta:</label>
            <input type="text" id="carta" name="carta" value="<?php echo $user['numero_carta']; ?>">
            <span class="error"><?php echo $carta_err; ?></span>
        </div>
        <div> 
            <label for="data_scadenza">Data di Scadenza:</label>
            <input type="date" id="data_scadenza" name="data_scadenza" value="<?php echo $user['data_scadenza']; ?>">
            <span class="error"><?php echo $data_scadenza_err; ?></span>
        </div>
    </div>

        <?php if (isset($passwordError) && !empty($passwordError)): ?>
            <p><?php echo $passwordError; ?></p>
        <?php endif; ?>
        <label for="old_password">Vecchia Password (richiesta solo per salvare le modifiche):</label>
        <input type="password" id="old_password" name="old_password">

        <button type="submit" name="save_changes" value="1">Salva Modifiche</button>
    </form>

    <script>
        // Mostra/nascondi i campi della carta di credito in base allo stato premium
        var premiumCheckbox = document.getElementById('premium');
        var premiuminfo = document.getElementById('premiumInfo');

        premiumCheckbox.addEventListener('change', function() {
            if (this.checked) {
                premiuminfo.style.display = 'block';
            } else {
                premiuminfo.style.display = 'none';
            }
        });
    </script>

<button onclick="window.location.href='home.php'">Torna alla Home</button>
</body>
</html>
