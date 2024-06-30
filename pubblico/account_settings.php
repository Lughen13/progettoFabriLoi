<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../pubblico/login.php");
    exit();
}

require_once '../configurazione/conn.php';

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

$passwordError = $emailError = $intestatario_err = $carta_err = $data_scadenza_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_user']) && $_POST['delete_user'] == '1') {
          // Verifica la password
          $oldPassword = $_POST['old_password'];
          $hashedPassword = $user['pw'];
  
          if (empty($oldPassword)) {
              $passwordError = "Inserisci la tua password per eliminare l'account.";
          } elseif (md5($oldPassword) !== $hashedPassword) {
              $passwordError = "La password non è corretta.";
          } else {
              // Inizia una transazione
              $conn->begin_transaction();
  
              try {
                  // Elimina record correlati in altre tabelle
                  $deleteCoAutoreQuery = "DELETE FROM co_autore WHERE id_utente = ?";
                  $stmt = $conn->prepare($deleteCoAutoreQuery);
                  $stmt->bind_param("i", $userId);
                  $stmt->execute();
                  $stmt->close();
  
                  // Elimina record dalla tabella premium
                  $deletePremiumQuery = "DELETE FROM premium WHERE id_utente = ?";
                  $stmt = $conn->prepare($deletePremiumQuery);
                  $stmt->bind_param("i", $userId);
                  $stmt->execute();
                  $stmt->close();
  
                  // Elimina l'utente dalla tabella utente
                  $deleteUserQuery = "DELETE FROM utente WHERE id_utente = ?";
                  $stmt = $conn->prepare($deleteUserQuery);
                  $stmt->bind_param("i", $userId);
                  $stmt->execute();
                  $stmt->close();
  
                  // Commit della transazione
                  $conn->commit();
  
                  // Distruggi la sessione e reindirizza alla pagina di login
                  session_destroy();
                  header("Location: ../pubblico/login.php");
                  exit();
              } catch (Exception $e) {
                  // Rollback della transazione in caso di errore
                  $conn->rollback();
                  $passwordError = "Errore durante l'eliminazione dell'account. Per favore riprova.";
              }
          }
    } else {
        // Codice per salvare le modifiche all'account
        $oldPassword = $_POST['old_password'];
        $hashedPassword = $user['pw'];

        if (empty($oldPassword)) {
            $passwordError = "Inserisci la tua password per salvare le modifiche.";
        } elseif (md5($oldPassword) !== $hashedPassword) {
            $passwordError = "La password non è corretta.";
        } else {
            $passwordError = "";
            $updateFields = array();

            if (!empty($_POST["password"])) {
                $password = $_POST["password"];
                if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[\W]/', $password)) {
                    $passwordError = "La password deve essere di minimo 8 caratteri, contenere almeno una lettera maiuscola e un carattere speciale.";
                } else {
                    $password_hash = md5($password);
                    $updateFields[] = "pw = '$password_hash'";
                }
            }
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

            if (empty($passwordError) && empty($emailError) && empty($intestatario_err) && empty($carta_err) && empty($data_scadenza_err)) {
                if (!empty($updateFields)) {
                    $updateQuery = "UPDATE utente SET " . implode(", ", $updateFields) . " WHERE id_utente = ?";
                    $stmt = $conn->prepare($updateQuery);
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $stmt->close();
                }
                $successMessage = "Modifiche salvate con successo!";
                header("Location: ../pubblico/home.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impostazioni Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            text-align: center;
        }

        h1 {
            color: #333;
        }

        form {
            width: 50%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        form div {
            margin-bottom: 10px;
            text-align: left;
        }

        label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }

        input[type=text], input[type=email], input[type=password], select {
            width: calc(100% - 170px);
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            /*box-sizing: border-box;*/
        }

        input[type=checkbox] {
            margin-left: 5px;
            transform: scale(1.5);
        }

        input[type=submit], button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type=submit]:hover, button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
        }

        #premiumInfo {
            display: <?php echo ($user['premium'] == 1) ? 'block' : 'none'; ?>;
            margin-top: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        #premiumInfo div {
            margin-bottom: 10px;
        }

        #premiumInfo label {
            width: 150px;
        }

        button {
            margin-top: 10px;
            background-color: #007bff;
        }

        button:hover {
            background-color: #0056b3;
        }
        </style>
</head>
 <body>
    <h1>Impostazioni Account</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <?php if (!empty($passwordError)): ?>
            <p class="error"><?php echo $passwordError; ?></p>
        <?php endif; ?>
        
        <div>
            <label for="password">Nuova Password:</label>
            <input type="password" id="password" name="password">
        </div>
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
            <span class="error"><?php echo $emailError; ?></span>
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
            <input type="checkbox" id="premium" name="premium" value="1" <?php if ($user['premium'] == 1) echo 'checked'; ?>>
        </div>
        <div id="premiumInfo">
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

        <div>
            <label for="old_password">Per salvare le modifiche, inserisci la password:</label>
            <input type="password" id="old_password" name="old_password" required>  
            <p>  in caso tu avessi cambiato password, inserisci la nuova password</p>
        </div>

        <button type="submit" name="save_changes" value="1">Salva Modifiche</button>
        <button type="submit" name="delete_user" value="1" style="background-color: red;">Elimina Utente</button>
</form>
    </form>
    <button onclick="window.location.href='../pubblico/home.php'">Torna alla Home</button>
    <script>
        document.getElementById('premium').addEventListener('change', function () {
            document.getElementById('premiumInfo').style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>

