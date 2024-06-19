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

$userId = $_SESSION['id'];

// Recupera i dati dal modulo
$blogId = $_POST['id_blog'];
$title = $_POST['title'];
$description = $_POST['description'];
$subcategoryId = $_POST['subcategory'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $imageFile = $_FILES['image'];
    $imageTmpName = $imageFile['tmp_name'];
    $imageFileName = $imageFile['name'];
    $imageSize = $imageFile['size'];
    $imageError = $imageFile['error'];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $imageExtension = strtolower(pathinfo($imageFileName, PATHINFO_EXTENSION));

    if (in_array($imageExtension, $allowedExtensions) && $imageError === 0) {
        $imageName = $title . '.' . $imageExtension; // Nuovo nome del file del logo
        $imageDestination = 'photo_post/' . $imageName;

        if (move_uploaded_file($imageTmpName, $imageDestination)) {
            echo "immagine caricata con successo.";
        } else {
            echo "Errore durante il caricamento dell'immagine.";
        }
    } else {
        echo "Formato dell'immagine non valido o errore durante il caricamento.";
    }
}

// Inserisci il nuovo post nel database
$sql = "INSERT INTO post (titolo_post, descrizione_post, img_post, id_autore, id_sottocat, id_blog, data_post) VALUES (?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiii", $title, $description, $imageName, $userId, $subcategoryId, $blogId);

if ($stmt->execute()) {
    echo "Post creato con successo!";
} else {
    echo "Errore durante la creazione del post: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>