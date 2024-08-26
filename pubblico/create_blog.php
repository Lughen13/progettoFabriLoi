<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

require_once '../configurazione/conn.php';
session_start();

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../pubblico/login.php");
    exit();
}

// Recupera le categorie dal database
$categoriesQuery = "SELECT id_categoria, nome_categoria FROM categoria";
$categoriesResult = $conn->query($categoriesQuery);
if (!$categoriesResult) {
    die("Errore nella query delle categorie: " . $conn->error);
}

// Recupera tutti gli utenti tranne il proprietario corrente per poter creare la select del coautore
$current_user_id = $_SESSION['id'];
$usersQuery = "SELECT id_utente, username FROM utente WHERE id_utente != ?";
$stmt = $conn->prepare($usersQuery);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$usersResult = $stmt->get_result();
if (!$usersResult) {
    die("Errore nella query degli utenti: " . $stmt->error);
}

$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['error_msg']);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea un nuovo blog</title>
    <style>
                body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin: 10px 0;
            font-weight: bold;
        }
        input[type="text"], textarea, select, input[type="file"], button {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50; 
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        button:disabled {
            background-color: #ccc; 
            cursor: not-allowed;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
    <script>
        function validateForm() {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            document.getElementById('submit-button').disabled = !(title && description);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('title').addEventListener('input', validateForm);
            document.getElementById('description').addEventListener('input', validateForm);
            validateForm();
        });
    </script>
</head>

<body>    
    <h1>Crea un nuovo blog</h1>

    <form method="post" action="../risorse/process_create_blog.php" enctype="multipart/form-data">
        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <label for="title">Titolo del blog:</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Descrizione del blog:</label>
        <textarea name="description" id="description" required></textarea>

        <label for="category">Categoria:</label>
        <select name="category" id="category" required>
            <option value="">Seleziona una sottocategoria</option>
            <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($category['id_categoria']); ?>"><?php echo htmlspecialchars($category['nome_categoria']); ?></option>
            <?php endwhile; ?>
        </select>

        <label for="logo">Logo del blog (opzionale):</label>
        <input type="file" name="logo" id="logo">

        <label for="co_autore">Seleziona il co-autore (opzionale):</label>
        <select name="co_autore" id="co_autore">
            <option value="">Nessun co-autore</option>
            <?php while ($user = $usersResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($user['id_utente']); ?>"><?php echo htmlspecialchars($user['username']); ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit" id="submit-button" disabled>Crea blog</button>
        <button type="button" onclick="location.href='../pubblico/my_profile.php'">Torna indietro</button>
    </form>
</body>
</html>
