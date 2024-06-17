<?php
// process_create_post.php
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

$userId = $_SESSION['id']; // Aggiornato

$blogId = $_POST['blog_id'];
$title = $_POST['title'];
$description = $_POST['description'];
$category = $_POST['category'];

$imageName = '';
if (!empty($_FILES['image']['name'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Crea la directory se non esiste
    }

    $imageTmp = $_FILES['image']['tmp_name'];
    $imageName = $uploadDir . uniqid() . '_' . $_FILES['image']['name'];
    if (!move_uploaded_file($imageTmp, $imageName)) {
        die('Errore durante il caricamento dell\'immagine.');
    }
}

$sql = "INSERT INTO post (titolo_post, descrizione_post, img_post, id_autore, id_sotcat, id_blog) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiis", $title, $description, $imageName, $userId, $category, $blogId);

if ($stmt->execute()) {
    echo "Post creato con successo!";
} else {
    echo "Errore durante la creazione del post: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>