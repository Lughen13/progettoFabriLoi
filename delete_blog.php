<?php
// delete_blog.php

// Connessione al database
require_once 'conn.php';

// Recupera l'ID del blog da eliminare
$blog_id = $_GET['id'];

// Elimina il blog dal database
$sql = "DELETE FROM blog WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $blog_id);

if ($stmt->execute()) {
    echo "Blog eliminato con successo!";
} else {
    echo "Errore durante l'eliminazione del blog: " . $stmt->error;
}

$stmt->close();
$conn->close();