<?php
require_once '../configurazione/conn.php';
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['id'];
$postId = isset($_POST['post_id']) ? $_POST['post_id'] : null;
$action = isset($_POST['action']) ? $_POST['action'] : null;

if ($action === 'like') {
    // Aggiungi Mi Piace
    $query = "INSERT INTO likes (id_post, id_utente) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    $stmt->close();
} elseif ($action === 'unlike') {
    // Rimuovi Mi Piace
    $query = "DELETE FROM likes WHERE id_post = ? AND id_utente = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    $stmt->close();
}

$queryLikes = "SELECT COUNT(*) AS like_count FROM likes WHERE id_post = ?";
$stmt = $conn->prepare($queryLikes);
$stmt->bind_param("i", $postId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$likeCount = $row['like_count'];
$stmt->close();

echo $likeCount;
$conn->close();


?>
