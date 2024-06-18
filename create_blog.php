<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

// Connessione al database
require_once 'conn.php';

// Verifica se l'utente è autenticato
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Recupera le categorie dal database
$categoriesQuery = "SELECT id_categoria, nome_categoria FROM categoria";
$categoriesResult = $conn->query($categoriesQuery);
if (!$categoriesResult) {
    die("Errore nella query delle categorie: " . $conn->error);
}

// Recupera gli stili dal file manage_styles.php
require_once 'manage_styles.php';
$stylesResult = getStyles();
if (!$stylesResult) {
    die("Errore nel recupero degli stili");
}

// Recupera tutti gli utenti tranne il proprietario corrente
$current_user_id = $_SESSION['id'];
$usersQuery = "SELECT id_utente, username FROM utente WHERE id_utente != ?";
$stmt = $conn->prepare($usersQuery);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$usersResult = $stmt->get_result();
if (!$usersResult) {
    die("Errore nella query degli utenti: " . $stmt->error);
}
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
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            margin-bottom: 20px;
        }
        header h1 {
            margin: 0;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
            text-align: center;
            background-color: #444;
            margin: 0;
        }
        nav ul li {
            display: inline;
            padding: 10px;
        }
        nav ul li a {
            color: #fff;
            text-decoration: none;
        }
        nav ul li a:hover {
            background-color: #555;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type=text], input[type=file], textarea, select {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type=submit] {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type=submit]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <h1>Crea un nuovo blog</h1>
    </header>
    <nav>        
        <ul>
            <li><a href="home.php"> Home </a></li>
            <li><a href="my_profile.php">Il mio profilo </a></li>
            <li><a href="account_settings.php">Impostazioni profilo</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <form method="post" action="process_create_blog.php" enctype="multipart/form-data">
        <label for="title">Titolo del blog:</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Descrizione del blog:</label>
        <textarea name="description" id="description" required></textarea>

        <label for="category">Categoria:</label>
        <select name="category" id="category" required>
            <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($category['id_categoria']); ?>"><?php echo htmlspecialchars($category['nome_categoria']); ?></option>
            <?php endwhile; ?>
        </select>

        <label for="style">Stile:</label>
        <select name="style" id="style" required>
            <?php while ($style = $stylesResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($style['id_stile']); ?>"><?php echo htmlspecialchars($style['nome']); ?></option>
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

        <input type="submit" value="Crea blog">
    </form>
</body>
</html>