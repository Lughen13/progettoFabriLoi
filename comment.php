<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
include 'conn.php';
session_start();


// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['id'];
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : null;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($postId && !empty($comment)) {
    $insertCommentQuery = "INSERT INTO commento (data_comm, contenuto, id_utente, id_post) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($insertCommentQuery);
    $stmt->bind_param("sii", $comment, $userId, $postId);
    if ($stmt->execute()) {
        // Commento inserito con successo
        header("Location: view_blog.php?id_blog={$_POST['blog_id']}");
        exit;
    } else {
        echo "Errore durante l'inserimento del commento: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Parametri non validi.";
}
?>