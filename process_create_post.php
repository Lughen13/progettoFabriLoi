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

// immagine ancora da gestire 
$imageName = '';
if (!empty($_FILES['image']['name'])) {
    $imageTmp = $_FILES['image']['tmp_name'];
    $imageName = 'uploads/' . uniqid() . '_' . $_FILES['image']['name'];
    move_uploaded_file($imageTmp, $imageName);
}

// inserisco il nuovo post all'interno del db
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