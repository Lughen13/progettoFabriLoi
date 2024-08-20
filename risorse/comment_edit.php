<?php
require_once '../configurazione/conn.php';
session_start();

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../pubblico/login.php');
    exit;
}

$userId = $_SESSION['id'];
$commentId = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : null;
$newComment = isset($_POST['new_comment']) ? trim($_POST['new_comment']) : '';

// Verifica se l'utente è autorizzato a modificare il commento
$checkQuery = "
    SELECT c.id_comment
    FROM commento c
    JOIN post p ON c.id_post = p.id_post
    JOIN blog b ON p.id_blog = b.id_blog
    LEFT JOIN coautore ca ON b.id_blog = ca.id_blog
    WHERE c.id_comment = ? AND (c.id_utente = ? OR p.id_autore = ? OR b.id_proprietario = ? OR ca.id_utente = ?)
";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("iiiii", $commentId, $userId, $userId, $userId, $userId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0 && !empty($newComment)) {
    $updateQuery = "UPDATE commento SET contenuto = ? WHERE id_comment = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $newComment, $commentId);
    if ($stmt->execute()) {
        // Commento modificato con successo
        header("Location: ../pubblico/view_blog.php?id_blog={$_POST['blog_id']}");
        exit;
    } else {
        echo "Errore durante la modifica del commento.";
    }
} else {
    echo "Non sei autorizzato a modificare questo commento.";
}
?>
