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

// Recupera i blog di cui l'utente è proprietario o co-autore
$blogsQuery = "SELECT b.id_blog, b.titolo_blog
               FROM blog b
               LEFT JOIN co_autore ca ON b.id_blog = ca.id_blog AND ca.id_utente = $userId
               WHERE b.id_proprietario = $userId OR ca.id_utente = $userId";
$blogsResult = $conn->query($blogsQuery);

// Recupera le categorie dal database
require_once 'categories.php';
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
                <option value="<?php echo $blog['id_blog']; ?>"><?php echo $blog['titolo_blog']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="title">Titolo del post:</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Descrizione del post:</label>
        <textarea name="description" id="description" required></textarea>

        <label for="category">Categoria:</label>
        <select name="category" id="category" required>
            <?php while ($category = $categoriesResult->fetch_assoc()): ?>
                <option value="<?php echo $category['id_categoria']; ?>"><?php echo $category['descrizione']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="image">Immagine del post (opzionale):</label>
        <input type="file" name="image" id="image">

        <input type="submit" value="Crea post">
    </form>
</body>
</html>
