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
$blogId = $_POST['id_blog'];
$title = $_POST['title'];
$description = $_POST['description'];
$subcategoryId = $_POST['subcategory'];
$photopost = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['immagine'])) {
    $immagineFile = $_FILES['immagine'];
    $immagineTmpName = $immagineFile['tmp_name'];
    $immagineFileName = $immagineFile['name'];
    $immagineSize = $immagineFile['size'];
    $immagineError = $immagineFile['error'];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $immagineExtension = strtolower(pathinfo($immagineFileName, PATHINFO_EXTENSION));

    // validazione dell'immagine in base alle estensioni indicate in precedenza 
    if (in_array($immagineExtension, $allowedExtensions) && $immagineError === 0 && $immagineSize > 0) {
        $photopost = uniqid() . '_' . $title . '.' . $immagineExtension;
        $immagineDestination = '../photo_post/' . $photopost;

        // crea la cartella per le foto dei post, se non esiste 
        if (!is_dir('../photo_post')) {
            mkdir('../photo_post', 0775, true);
        }

        if (move_uploaded_file($immagineTmpName, $immagineDestination)) {
            echo "Immagine caricata con successo.";
        } else {
            echo "Errore durante il caricamento dell'immagine.";
        }
    } else {
        echo "Formato dell'immagine non valido o errore durante il caricamento.";
    }
}


// inserisci il post nel database 
$sql = "INSERT INTO post (titolo_post, descrizione_post, img_post, id_autore, id_sottocat, id_blog, data_post) VALUES (?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiii", $title, $description, $photopost, $userId, $subcategoryId, $blogId);

if ($stmt->execute()) {
    echo "<p>Post creato con successo!</p>";
    echo "<script>
        setTimeout(function() {
            window.location.href = '../pubblico/my_profile.php';
        }, 2000);
    </script>";
} else {
    echo "Errore durante la creazione del post: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>