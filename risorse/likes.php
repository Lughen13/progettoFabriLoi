<?php
// likes.php

// Connessione al database
require_once '../configurazione/conn.php';
session_start();

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    exit("Non autorizzato");
}

// Recupera l'id dell'utente dalla sessione
$userId = $_SESSION['id'];

// Recupera l'id del post e l'azione dall'input POST
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : null;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($postId && ($action === 'like' || $action === 'unlike')) {
    if ($action === 'like') {
        // Aggiungi Mi Piace
        $query = "INSERT INTO likes (id_utente, id_post) VALUES (?, ?)";
    } elseif ($action === 'unlike') {
        // Rimuovi Mi Piace
        $query = "DELETE FROM likes WHERE id_utente = ? AND id_post = ?";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $postId);
    $stmt->execute();
    $stmt->close();

    if ($action === 'like') {
        // Recupera l'ID del proprietario del post
        $queryGetPostOwner = "SELECT id_autore FROM post WHERE id_post = ?";
        $stmtPostOwner = $conn->prepare($queryGetPostOwner);
        $stmtPostOwner->bind_param("i", $postId);
        $stmtPostOwner->execute();
        $resultPostOwner = $stmtPostOwner->get_result();
        if ($resultPostOwner->num_rows > 0) {
            $postOwner = $resultPostOwner->fetch_assoc();
            $id_postOwner = $postOwner['id_autore'];

            // Aggiungi la notifica
            $queryInsertNotifica = "INSERT INTO notifiche (user_id, sender_id, tipo, contenuto_id) VALUES (?, ?, 'like', ?)";
            $stmtInsertNotifica = $conn->prepare($queryInsertNotifica);
            $stmtInsertNotifica->bind_param("iii", $id_postOwner, $userId, $postId);
            $stmtInsertNotifica->execute();
            $stmtInsertNotifica->close();
        }
        $stmtPostOwner->close();
    }

    // Ottieni il nuovo conteggio dei Mi Piace per questo post
    $queryCount = "SELECT COUNT(*) AS like_count FROM likes WHERE id_post = ?";
    $stmtCount = $conn->prepare($queryCount);
    $stmtCount->bind_param("i", $postId);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $likeCount = $resultCount->fetch_assoc()['like_count'];

    echo $likeCount;

    $stmtCount->close();
} else {
    http_response_code(400); // Richiesta non valida
    exit("Richiesta non valida");
}

$conn->close();
?>