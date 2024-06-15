<?php
// process_create_blog.php

// Connessione al database
require_once 'conn.php';

// Verifica se l'utente è autenticato
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

//$userId = $_SESSION['user_id'];

// Recupera i dati dal modulo
$title = $_POST['title'];
$description = $_POST['description'];
$category_id = $_POST['category'];
$style_id = $_POST['style'];

// Carica l'immagine del logo
$logo_name = '';
$logo_destination = '';
if (!empty($_FILES['logo']['name'])) {
    $logo_name = $_FILES['logo']['name'];
    $logo_tmp = $_FILES['logo']['tmp_name'];
    $logo_destination = 'uploads/' . $logo_name;
    move_uploaded_file($logo_tmp, $logo_destination);
} else {
    $logo_destination = 'default.png';
}

// Inserisci il nuovo blog nel database
$sql = "INSERT INTO blog (data_blog, titolo_blog, descrizione, img_logo, id_categoria, id_stile, id_proprietario, followers_count) VALUES (NOW(), ?, ?, ?, ?, ?, ?, 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiii", $title, $description, $logo_destination, $category_id, $style_id, $userId);

if ($stmt->execute()) {
    echo "Blog creato con successo!";
} else {
    echo "Errore durante la creazione del blog: " . $stmt->error;
}

$stmt->close();
$conn->close();


