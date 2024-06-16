<?php
// process_create_post.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
// Connessione al database
require_once 'conn.php';

// Verifica se l'utente è autenticato
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Recupera i dati dal modulo
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

// Inserisci il nuovo post nel database
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
