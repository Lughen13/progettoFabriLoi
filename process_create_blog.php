<?php
// process_create_blog.php

// Connessione al database
require_once 'conn.php';

// Recupera i dati dal modulo
$title = $_POST['title'];
$description = $_POST['description'];
$category_id = $_POST['category'];
$style_id = $_POST['style'];
$user_id = $_SESSION['user_id']; // Supponiamo che l'ID dell'utente corrente sia memorizzato nella sessione

// Carica l'immagine del logo
$logo_name = $_FILES['logo']['name'];
$logo_tmp = $_FILES['logo']['tmp_name'];
$logo_destination = 'uploads/' . $logo_name;
move_uploaded_file($logo_tmp, $logo_destination);

// Inserisci il nuovo blog nel database
$sql = "INSERT INTO blog (title, description, logo, category_id, style_id, user_id) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiis", $title, $description, $logo_destination, $category_id, $style_id, $user_id);

if ($stmt->execute()) {
    echo "Blog creato con successo!";
} else {
    echo "Errore durante la creazione del blog: " . $stmt->error;
}

$stmt->close();
$conn->close();



?>