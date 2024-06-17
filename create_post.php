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
               WHERE b.id_proprietario = ? OR ca.id_utente = ?";
$stmt = $conn->prepare($blogsQuery);
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$blogsResult = $stmt->get_result();

// Chiudi la connessione al database
$stmt->close();
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
            <option value="">Seleziona un blog</option>
            <?php while ($blog = $blogsResult->fetch_assoc()): ?>
                <option value="<?php echo $blog['id_blog']; ?>"><?php echo $blog['titolo_blog']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="title">Titolo del post:</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Descrizione del post:</label>
        <textarea name="description" id="description" required></textarea>

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
            $('#blog').change(function() {
                var blogId = $(this).val();
                if (blogId) {
                    $.ajax({
                        url: 'get_subcategories_by_blog.php',
                        method: 'POST',
                        data: {blog_id: blogId},
                        success: function(response) {
                            $('#subcategory').html(response);
                        },
                        error: function(xhr, status, error) {
                            alert('Errore AJAX: ' + status + ' - ' + error);
                        }
                    });
                } else {
                    $('#subcategory').html('<option value="">Seleziona una sottocategoria</option>');
                }
            });
        });
    </script>
</body>
</html>