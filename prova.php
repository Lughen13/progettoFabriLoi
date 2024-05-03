<?php
// Connessione DB
$host = "localhost";
$user = "root";
$pass = "";
$db = "progettoFabriLoi";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connessione fallita: " . mysqli_connect_error());
}

// Invio form  
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Dati utente
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $genere = $_POST['genere'];
    $data_nascita = $_POST['data_nascita'];
    $numero_telefono = $_POST['numero_telefono'];
    $premium = $_POST['premium'];
    $confirmPassword = $_POST['confirmPassword'];
    $check = false;

    if ($confirmPassword == $password){
        $check = true;
    }

    // Query inserimento utente
    $sql = "INSERT INTO utente (username, email, pw, nome, cognome, genere, data_nascita, numero_telefono, premium)
          VALUES ('$username', '$email', '$password', '$nome', '$cognome', '$genere', '$data_nascita', '$numero_telefono', '$premium')";

    if (mysqli_query($conn, $sql)) {

        // Dati premium 
        if (isset($_POST['premium'])) {

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
<!-- ciao -->
<h2>Registrazione</h2>

<form method="post">
    <section>
        <div>
            <input type="text" name="nome" placeholder="Nome" required>
        </div>
        <div>
            <input type="text" name="cognome" placeholder="Cognome" required>
        </div>
        <div>
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div>
            <input type="text" name="username" placeholder="Username" required>
        </div>
        <div>
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <div>
            <input type="password" name="confirmPassword" placeholder="Conferma Password" required>
        </div>
    </section>
    <div>
        <label for="genere">Genere:</label>
        <select name="genere">
            <option value="M">Maschio</option>
            <option value="F">Femmina</option>
            <option value="C"> Croissant</option>
            <option value="D"> Ascensore</option>
            <option value="E"> alzabandiera </option>
        </select>
    </div>
    <div>
        <label for="data_nascita">Data di nascita:</label>
        <input type="date" name="data_nascita" required>
    </div>
    <section>
        <label for="numero_telefono">Numero di Telefono:</label>
        <input type="tel" id="telefono" name="telefono" pattern="[0-9]{10}" required>
    </section>
    <section>
        <label for="premium">Sottoscrivi Premium:</label>
        <input type="checkbox" id="premium" name="premium">

        <div id="premium-data" style="display:none;">
            <input type="text" name="intestatario" placeholder="Intestatario carta">
            <input type="text" name="numero_carta" placeholder="Numero carta">
            <label for="data_scadenza">Data di scadenza:</label>
            <input type="date" name="data_scadenza">
        </div>
    </section>
    <input type="submit" value="Registrati">

</form>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

    $(document).ready(function () {
        $('#premium').change(function () {
            if (this.checked) {
                $('#premium-data').show();
            }
            else {
                $('#premium-data').hide();
            }
        });
    });

</script>