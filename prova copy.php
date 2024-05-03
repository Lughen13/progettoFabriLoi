<!DOCTYPE html>
<html>
<head>
    <title>Registrazione Utente</title>
    <style>
        #premium-fields {
            display: none;
        }
    </style>
</head>
<body>
    <h2>Registrazione Utente</h2>
    <form method="post" action="/progettoFabriLoi/prova.php">
        Username: <input type="text" name="username" required><br>
        Email: <input type="email" name="email" required><br>
        Password: <input type="password" name="password" required><br>
        Nome: <input type="text" name="nome" required><br>
        Cognome: <input type="text" name="cognome" required><br>
        Genere: <input type="text" name="genere" required><br>
        Data di nascita: <input type="date" name="data_nascita" required><br>
        Numero di telefono: <input type="text" name="numero_telefono"><br>
        <input type="checkbox" id="premium-checkbox" name="premium" value="1"> Diventa Premium<br>
        <div id="premium-fields">
            Intestatario: <input type="text" name="intestatario"><br>
            Numero carta: <input type="text" name="numero_carta"><br>
            Data scadenza: <input type="date" name="data_scadenza"><br>
        </div>
        <input type="submit" name="submit" value="Registrati">
    </form>

    <script>
        // Mostra/nascondi i campi per i dati premium
        var premiumCheckbox = document.getElementById("premium-checkbox");
        var premiumFields = document.getElementById("premium-fields");

        premiumCheckbox.addEventListener("change", function() {
            if (this.checked) {
                premiumFields.style.display = "block";
            } else {
                premiumFields.style.display = "none";
            }
        });
    </script>
</body>
</html>
