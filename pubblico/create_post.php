<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

require_once '../configurazione/conn.php';

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../pubblico/login.php");
    exit();
}

$userId = $_SESSION['id'];

// Query per determinare se l'utente è premium
$premiumQuery = "SELECT premium FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($premiumQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($isPremium);
$stmt->fetch();
$stmt->close();

// Query per recuperare i blog dell'utente
$blogsQuery = "SELECT b.id_blog, b.titolo_blog, b.id_categoria
               FROM blog b
               LEFT JOIN co_autore ca ON b.id_blog = ca.id_blog
               WHERE b.id_proprietario = ? OR ca.id_utente = ?";
$stmt = $conn->prepare($blogsQuery);
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$blogsResult = $stmt->get_result();
$stmt->close();

// Recupera eventuali messaggi di errore da process_create_post.php
$errorMsg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';
unset($_SESSION['error_msg']);

// Determina il numero massimo di immagini consentite in base allo stato premium dell'utente
$maxImages = $isPremium ? 3 : 1;
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
        input[type="submit"]:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        .premium-only {
            display: none; /* Nasconde il blocco per utenti premium */
        }
        .standard-only {
            display: none; /* Nasconde il blocco per utenti standard */
        }

        button {
        background-color: #4CAF50; 
        color: white;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
        margin: 10px 0;
        }
        button:disabled {
            background-color: #ccc; 
            cursor: not-allowed;
        }
    </style>
    <script>
        function validateForm() {
            const blog = document.getElementById('blog').value.trim();
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const subcategory = document.getElementById('subcategory').value.trim();
            const submitButton = document.getElementById('submit-button');
            
            if (blog.length > 0 && title.length > 0 && description.length > 0 && subcategory.length > 0) {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const blogField = document.getElementById('blog');
            const titleField = document.getElementById('title');
            const descriptionField = document.getElementById('description');
            const subcategoryField = document.getElementById('subcategory');
            const submitButton = document.getElementById('submit-button');

            blogField.addEventListener('change', validateForm);
            titleField.addEventListener('input', validateForm);
            descriptionField.addEventListener('input', validateForm);
            subcategoryField.addEventListener('change', validateForm);

            validateForm();

            // Mostra/nascondi i campi immagine in base allo stato premium dell'utente
            const maxImages = <?php echo $maxImages; ?>;
            if (maxImages === 1) {
                $('.standard-only').show();
            } else {
                $('.premium-only').show();
            }
        });
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
</head>
<body>
    <h1>Crea un nuovo post</h1>

    <?php if ($errorMsg): ?>
        <p class="error"><?php echo $errorMsg; ?></p>
    <?php endif; ?>
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

            <div class="premium-only">
                <label for="image1">Immagine 1:</label>
                <input type="file" name="immagini[]" id="image1" accept="image/*">
                <label for="image2">Immagine 2:</label>
                <input type="file" name="immagini[]" id="image2" accept="image/*">
                <label for="image3">Immagine 3:</label>
                <input type="file" name="immagini[]" id="image3" accept="image/*">
            </div>

            <div class="standard-only">
                <label for="image1">Immagine:</label>
                <input type="file" name="immagini[]" id="image" accept="image/*">
            </div>

            <button type="submit" id="submit-button" disabled>Crea post</button>
            <button type="button"onclick="location.href='../pubblico/my_profile.php'">Torna indietro</button>
    </form>

</body>
</html>

