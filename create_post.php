<?php
require_once 'conn.php';
session_start();

// Verifica se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
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

// Gestione dell'invio del form per la creazione di un nuovo post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blogId = $_POST['blog_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    // Carica l'immagine del post, se presente
    $imageName = '';
    if (!empty($_FILES['image']['name'])) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = 'uploads/' . uniqid() . '_' . $_FILES['image']['name'];
        move_uploaded_file($imageTmp, $imageName);
    }

    $createPostQuery = "INSERT INTO post (titolo_post, descrizione_post, img_post, id_autore, id_sotcat, id_blog)
                        VALUES ('$title', '$description', '$imageName', $userId, $category, $blogId)";
    $conn->query($createPostQuery);
    header("Location: my_blogs.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crea un nuovo post</title>
</head>
<body>
    <h1>Crea un nuovo post</h1>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
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
            <?php
            // Recupera le categorie dal database e genera le opzioni
            $categoriesQuery = "SELECT id_categoria, descrizione FROM categoria";
            $categoriesResult = $conn->query($categoriesQuery);
            while ($category = $categoriesResult->fetch_assoc()) {
                echo "<option value='" . $category['id_categoria'] . "'>" . $category['descrizione'] . "</option>";
            }
            ?>
        </select>

        <label for="image">Immagine del post (opzionale):</label>
        <input type="file" name="image" id="image">

        <button type="submit">Crea post</button>
    </form>
</body>
</html>
