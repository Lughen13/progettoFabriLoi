<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
require_once '../configurazione/conn.php';
session_start();

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../pubblico/login.php');
    exit;
}

$userId = $_SESSION['id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : null;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
$id_comm = isset($_POST['id_comm']) ? intval($_POST['id_comm']) : null;
$id_blog = isset($_POST['id_blog']) ? intval($_POST['id_blog']) : null;

if ($action === 'insert' && $postId && !empty($comment)) {
    // Inserisci il nuovo commento
    $insertCommentQuery = "INSERT INTO commento (data_comm, contenuto, id_utente, id_post) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($insertCommentQuery);
    $stmt->bind_param("sii", $comment, $userId, $postId);
    if ($stmt->execute()) {
        header("Location: ../pubblico/view_blog.php?id_blog=$id_blog");
        exit;
    } else {
        echo "Errore durante l'inserimento del commento: " . $stmt->error;
    }
    $stmt->close();
} elseif ($action === 'update' && $id_comm && !empty($comment)) {
    // Aggiorna il commento esistente
    $updateCommentQuery = "UPDATE commento SET contenuto = ? WHERE id_comm = ? AND id_utente = ?";
    $stmt = $conn->prepare($updateCommentQuery);
    $stmt->bind_param("sii", $comment, $id_comm, $userId);
    if ($stmt->execute()) {
        header("Location: ../pubblico/view_blog.php?id_blog=$id_blog");
        exit;
    } else {
        echo "Errore durante l'aggiornamento del commento: " . $stmt->error;
    }
    $stmt->close();
} elseif ($action === 'delete' && $id_comm) {
    // Elimina il commento
    $deleteCommentQuery = "DELETE FROM commento WHERE id_comm = ? AND id_utente = ?";
    $stmt = $conn->prepare($deleteCommentQuery);
    $stmt->bind_param("ii", $id_comm, $userId);
    if ($stmt->execute()) {
        header("Location: ../pubblico/view_blog.php?id_blog=$id_blog");
        exit;
    } else {
        echo "Errore durante l'eliminazione del commento: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Parametri non validi.";
}
$conn->close();
?>