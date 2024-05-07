<?php
// process_edit_blog.php

// Connessione al database
require_once 'conn.php';

// Recupera i dati dal modulo
$blog_id = $_POST['blog_id'];
$title = $_POST['title'];
$description = $_POST['description'];
$category_id = $_POST['category'];
$style_id = $_POST['style'];

// Carica la nuova immagine del logo, se presente
$logo_name = '';
$logo_destination = '';
if (!empty($_FILES['logo']['name'])) {
    $logo_name = $_FILES['logo']['name'];
    $logo_tmp = $_FILES['logo']['tmp_name'];
    $logo_destination = 'uploads/' . $logo_name;
    move_uploaded_file($logo_tmp, $logo_destination);
}

// Aggiorna il blog nel database
$sql = "UPDATE blog SET title = ?, description = ?, logo = ?, category_id = ?, style_id = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiis", $title, $description, $logo_destination, $category_id, $style_id, $blog_id);

if ($stmt->execute()) {
    echo "Blog aggiornato con successo!";
} else {
    echo "Errore durante l'aggiornamento del blog: " . $stmt->error;
}

$stmt->close();
$conn->close();