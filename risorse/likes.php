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
if ($_SESSION['loggedin'] === true && !empty($action)) {
    $postId = $_POST['post_id'];
    $userId = $_SESSION['id'];

    if ($action === 'like') {
        // Aggiungi Mi Piace se non esiste già
        $query = "INSERT INTO likes (id_post, id_utente) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $postId, $userId);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'unlike') {
        // Rimuovi Mi Piace se esiste
        $query = "DELETE FROM likes WHERE id_post = ? AND id_utente = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $postId, $userId);
        $stmt->execute();
        $stmt->close();
    }

    // Conteggio totale dei Mi Piace per il post
    $queryCount = "SELECT COUNT(*) AS like_count FROM likes WHERE id_post = ?";
    $stmtCount = $conn->prepare($queryCount);
    $stmtCount->bind_param("i", $postId);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $likeCount = $resultCount->fetch_assoc()['like_count'];
    $stmtCount->close();

    // Verifica se l'utente ha messo Mi Piace a questo post
    $queryCheckLike = "SELECT * FROM likes WHERE id_post = ? AND id_utente = ?";
    $stmtCheckLike = $conn->prepare($queryCheckLike);
    $stmtCheckLike->bind_param("ii", $postId, $userId);
    $stmtCheckLike->execute();
    $resultCheckLike = $stmtCheckLike->get_result();

    if ($resultCheckLike->num_rows > 0) {
        // L'utente ha messo Mi Piace
        echo $likeCount;
    } else {
        // L'utente non ha messo Mi Piace
        echo $likeCount;
    }

    $stmtCheckLike->close();
} else {
    // Gestione dell'errore o caso in cui l'utente non è autenticato
    echo 'error';
}

// Chiudi la connessione al database
$conn->close();
?>