<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
require_once 'conn.php';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id']; // Assicurati che questo corrisponda al nome della variabile di sessione corretta

// Recupera i blog di cui l'utente è proprietario o co-autore
$blogsQuery = "SELECT b.id_blog, b.titolo_blog, b.id_categoria, c.nome_categoria
               FROM blog b
               LEFT JOIN co_autore ca ON b.id_blog = ca.id_blog AND ca.id_utente = ?
               LEFT JOIN categoria c ON b.id_categoria = c.id_categoria
               WHERE b.id_proprietario = ? OR ca.id_utente = ?";
$stmt = $conn->prepare($blogsQuery);
$stmt->bind_param("iii", $userId, $userId, $userId);
$stmt->execute();
$blogsResult = $stmt->get_result();

// Recupera le sottocategorie dal database
$sqlSottocategorie = "SELECT id_sottocat, nome_sottocat, id_categoria FROM sottocat";
$sottocategorieResult = $conn->query($sqlSottocategorie);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Crea un nuovo post</title>
</head>
<body>
    <h1>Crea un nuovo post</h1>
    <form method="post" action="process_create_post.php" enctype="multipart/form-data">
        <label for="blog">Seleziona il blog:</label>
        <select name="blog_id" id="blog" required>
            <?php while ($blog = $blogsResult->fetch_assoc()): ?>
                <option value="<?php echo $blog['id_blog']; ?>"><?php echo $blog['titolo_blog']; ?> (<?php echo $blog['nome_categoria']; ?>)</option>
            <?php endwhile; ?>
        </select>

        <label for="title">Titolo del post:</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Descrizione del post:</label>
        <textarea name="description" id="description" required></textarea>

        <label for="category">Sottocategoria:</label>
        <select name="sottocat" id="sottocat" required>
            <?php while ($sottocategoria = $sottocategorieResult->fetch_assoc()): ?>
                <option value="<?php echo $sottocategoria['id_sottocat']; ?>"><?php echo $sottocategoria['nome_sottocat']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="image">Immagine del post (opzionale):</label>
        <input type="file" name="image" id="image">

        <input type="submit" value="Crea post">
    </form>
</body>
</html>