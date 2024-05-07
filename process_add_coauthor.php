<?php
// process_add_coauthor.php

// Connessione al database
require_once 'conn.php';

// Recupera i dati dal modulo
$blog_id = $_POST['blog_id'];
$coauthor_id = $_POST['coauthor_id'];

// Inserisci il nuovo co-autore nel database
$sql = "INSERT INTO co_autore (id_utente, id_blog) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $coauthor_id, $blog_id);

if ($stmt->execute()) {
    echo "Co-autore aggiunto con successo!";
} else {
    echo "Errore durante l'aggiunta del co-autore: " . $stmt->error;
}

$stmt->close();
$conn->close();