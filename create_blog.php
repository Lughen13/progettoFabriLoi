<?php
// Connessione al database
require_once 'conn.php';

// Verifica se l'utente è autenticato
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Recupera le categorie dal database
$categoriesQuery = "SELECT id_categoria, descrizione FROM categoria";
$categoriesResult = $conn->query($categoriesQuery);

// Recupera gli stili dal file manage_styles.php
require_once 'manage_styles.php';
$stylesResult = getStyles();
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
                <option value="<?php echo $category['id_categoria']; ?>"><?php echo $category['descrizione']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="style">Stile:</label>
        <select name="style" id="style" required>
            <?php while ($style = $stylesResult->fetch_assoc()): ?>
                <option value="<?php echo $style['id_stile']; ?>"><?php echo $style['nome']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="logo">Logo del blog (opzionale):</label>
        <input type="file" name="logo" id="logo">

        <input type="submit" value="Crea blog">
    </form>
</body>
</html>
