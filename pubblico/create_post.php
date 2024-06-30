<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

require_once '../configurazione/conn.php';


// e verifico l'utente di cui ho la sessione aperta è loggato
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../pubblico/login.php");
    exit();
}

// id dell'utente dalla sessione
$userId = $_SESSION['id'];

// essendo che l'utente può creare post in blog di cui è sia proprietario che coautore, recupero gli id di entrambi i tipi di blog per inseriri nella select in cui l'utente può decidere a quale blog apparterrà il post
$blogsQuery = "SELECT b.id_blog, b.titolo_blog, b.id_categoria
               FROM blog b
               LEFT JOIN co_autore ca ON b.id_blog = ca.id_blog
               WHERE b.id_proprietario = ? OR ca.id_utente = ?";
$stmt = $conn->prepare($blogsQuery);
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$blogsResult = $stmt->get_result();
$stmt->close();


?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea un nuovo post</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
        }
        h1 {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin: 10px 0;
            font-weight: bold;
        }
        input[type="text"], textarea, select, input[type="file"], input[type="submit"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        select {
            appearance: auto;
            -webkit-appearance: menulist;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
    <h1>Crea un nuovo post</h1>
    <form method="post" action="../risorse/process_create_post.php" enctype="multipart/form-data">
        <label for="blog">Seleziona il blog:</label>
        <select name="id_blog" id="blog" required>
            <option value="">Seleziona un blog</option>
            <?php while ($blog = $blogsResult->fetch_assoc()): ?>
                <option value="<?php echo $blog['id_blog']; ?>"><?php echo htmlspecialchars($blog['titolo_blog']); ?></option>
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
        <input type="file" name="immagine" id="image">

        <input type="submit" value="Crea post">
    </form>

    <script>
    $(document).ready(function() {
        $('#blog').on('change', function() {
            var blogId = $(this).val();
            if (blogId) {
                $.ajax({
                    url: '../risorse/get_subcategories_by_blog.php',
                    type: 'POST',
                    data: {id_blog: blogId},
                    success: function(response) {
                        $('#subcategory').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.log('Errore AJAX: ' + status + ' - ' + error);
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