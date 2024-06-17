<?php
// Connessione al database
require_once 'conn.php';

// Verifica se l'utente è autenticato
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Recupera l'id dell'utente dalla sessione
$userId = $_SESSION['id'];

// Query per recuperare i blog di cui l'utente è proprietario o co-autore
$blogsQuery = "SELECT b.id_blog, b.titolo_blog
               FROM blog b
               LEFT JOIN co_autore ca ON b.id_blog = ca.id_blog
               WHERE b.id_proprietario = $userId OR ca.id_utente = $userId";

$blogsResult = $conn->query($blogsQuery);

// Funzione per ottenere le categorie
function getCategories($conn) {
    $sql = "SELECT id_categoria, nome_categoria FROM categoria ORDER BY nome_categoria";
    $result = $conn->query($sql);
    $categories = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

// Chiudi la connessione
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea un nuovo post</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        .error { color: red; }
        .success { color: green; }
    </style>
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
            <option value="">Seleziona una categoria</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id_categoria']; ?>"><?php echo $category['nome_categoria']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="subcategory">Sottocategoria:</label>
        <select name="subcategory" id="subcategory" required>
            <option value="">Seleziona una sottocategoria</option>
        </select>

        <label for="image">Immagine del post (opzionale):</label>
        <input type="file" name="image" id="image">

        <input type="submit" value="Crea post">
    </form>

    <script>
        $(document).ready(function() {
            $('#category').change(function() {
                var categoria = $(this).val();
                $.ajax({
                    url: 'get_sottocategorie.php',
                    method: 'POST',
                    data: {categoria: categoria},
                    success: function(response) {
                        $('#subcategory').html(response);
                    }
                });
            });
        });
    </script>
</body>
</html>