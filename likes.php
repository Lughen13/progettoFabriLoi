<?php
session_start();
include 'conn.php';

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['id'];
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : null;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($postId && ($action === 'like' || $action === 'unlike')) {
    if ($action === 'like') {
        // Controlla se l'utente ha già messo Mi Piace
        $checkLikeQuery = "SELECT * FROM likes WHERE id_utente = ? AND id_post = ?";
        $stmt = $conn->prepare($checkLikeQuery);
        $stmt->bind_param("ii", $userId, $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Inserisci il Mi Piace nel database
            $insertLikeQuery = "INSERT INTO likes (id_utente, id_post) VALUES (?, ?)";
            $stmt = $conn->prepare($insertLikeQuery);
            $stmt->bind_param("ii", $userId, $postId);
            if ($stmt->execute()) {
                // Incrementa il conteggio dei Mi Piace nel post
                $updateLikesCountQuery = "UPDATE post SET likes_count = likes_count + 0 WHERE id_post = ?";
                $stmt = $conn->prepare($updateLikesCountQuery);
                $stmt->bind_param("i", $postId);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "Errore durante l'inserimento del Mi Piace: " . $stmt->error;
            }
        } else {
            echo "Hai già messo Mi Piace a questo post.";
        }
    } elseif ($action === 'unlike') {
        // Rimuovi il Mi Piace dal database
        $deleteLikeQuery = "DELETE FROM likes WHERE id_utente = ? AND id_post = ?";
        $stmt = $conn->prepare($deleteLikeQuery);
        $stmt->bind_param("ii", $userId, $postId);
        if ($stmt->execute()) {
            // Decrementa il conteggio dei Mi Piace nel post
            $updateLikesCountQuery = "UPDATE post SET likes_count = CASE WHEN likes_count > 0 THEN likes_count - 1 ELSE 0 END WHERE id_post = ?";
            $stmt = $conn->prepare($updateLikesCountQuery);
            $stmt->bind_param("i", $postId);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "Errore durante la rimozione del Mi Piace: " . $stmt->error;
        }
    }
    
    // Redirect back to the blog view
    header("Location: view_blog.php?id_blog={$_POST['blog_id']}");
    exit;
} else {
    echo "Parametri non validi.";
}
?>