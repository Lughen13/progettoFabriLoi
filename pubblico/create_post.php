<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

require_once '../configurazione/conn.php';
session_start();

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../pubblico/login.php");
    exit();
}

$userId = $_SESSION['id'];

// query che determina che l'utente sia premium 
$premiumQuery = "SELECT premium FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($premiumQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($isPremium);
$stmt->fetch();
$stmt->close();

// recupero i blog dell'utente
$blogsQuery = "SELECT b.id_blog, b.titolo_blog
               FROM blog b
               LEFT JOIN co_autore ca ON b.id_blog = ca.id_blog
               WHERE b.id_proprietario = ? OR ca.id_utente = ?";
$stmt = $conn->prepare($blogsQuery);
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$blogsResult = $stmt->get_result();
$stmt->close();

$errorMsg = $_SESSION['error_msg'] ?? '';
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
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin: 10px 0;
            font-weight: bold;
        }
        input[type="text"], textarea, select, input[type="file"], button {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50; 
            color: white;
            border: none;
            cursor: pointer;
            margin: 10px 0;
        }
        button:hover {
            background-color: #45a049;
        }
        button:disabled {
            background-color: #ccc; 
            cursor: not-allowed;
        }
        .error {
            color: red;
        }
        .premium-only, .standard-only {
            display: none;
        }
    </style>
    <script>
        $(document).ready(function() {
            const maxImages = <?php echo $maxImages; ?>;
            const submitButton = $('#submit-button');

            function validateForm() {
                const blog = $('#blog').val().trim();
                const title = $('#title').val().trim();
                const description = $('#description').val().trim();
                const subcategory = $('#subcategory').val().trim();

                submitButton.prop('disabled', !(blog && title && description && subcategory));
            }

            $('#blog, #title, #description, #subcategory').on('input change', validateForm);
            validateForm();

            if (maxImages === 1) {
                $('.standard-only').show();
            } else {
                $('.premium-only').show();
            }

            $('#blog').on('change', function() {
                const blogId = $(this).val();
                if (blogId) {
                    $.ajax({
                        url: '../risorse/get_subcategories_by_blog.php',
                        type: 'POST',
                        data: { id_blog: blogId },
                        success: function(response) {
                            $('#subcategory').html(response);
                        },
                        error: function(xhr, status, error) {
                            console.error('Errore AJAX: ' + status + ' - ' + error);
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
        <p class="error"><?php echo htmlspecialchars($errorMsg); ?></p>
    <?php endif; ?>

    <form method="post" action="../risorse/process_create_post.php" enctype="multipart/form-data">
        <label for="blog">Seleziona il blog:</label>
        <select name="id_blog" id="blog" required>
            <option value="">Seleziona un blog</option>
            <?php while ($blog = $blogsResult->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($blog['id_blog']); ?>"><?php echo htmlspecialchars($blog['titolo_blog']); ?></option>
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
            <label for="image">Immagine:</label>
            <input type="file" name="immagini[]" id="image" accept="image/*">
        </div>

        <button type="submit" id="submit-button" disabled>Crea post</button>
        <button type="button" onclick="location.href='../pubblico/my_profile.php'">Torna indietro</button>
    </form>
</body>
</html>
