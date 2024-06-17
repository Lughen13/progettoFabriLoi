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


?>

<!DOCTYPE html>
<html>
<head>
    <title>Crea un nuovo blog</title>
</head>
<body>
    <h1>Crea un nuovo blog</h1>
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