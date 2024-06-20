<?php
include 'conn.php';  // Connessione al database
session_start();

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Verifica se l'id del blog è stato passato correttamente
if (!isset($_POST['blog_id'])) {
    exit('ID del blog non specificato.');
}

$userId = $_SESSION['id'];
$blogId = $_POST['blog_id'];
$action = $_POST['action'];

if ($action === 'follow') {
    // Controlla se l'utente sta già seguendo questo blog
    $queryCheckFollow = "SELECT * FROM follow WHERE id_utente = ? AND id_blog = ?";
    $stmt_check_follow = $conn->prepare($queryCheckFollow);
    $stmt_check_follow->bind_param("ii", $userId, $blogId);
    $stmt_check_follow->execute();
    $result_check_follow = $stmt_check_follow->get_result();
    
    if ($result_check_follow->num_rows > 0) {
        // L'utente sta già seguendo questo blog
        echo "Already following this blog.";
    } else {
        // Inserisci il follow nel database
        $insertFollowQuery = "INSERT INTO follow (id_utente, id_blog, data_follow) VALUES (?, ?, NOW())";
        $stmt_insert_follow = $conn->prepare($insertFollowQuery);
        $stmt_insert_follow->bind_param("ii", $userId, $blogId);
        if ($stmt_insert_follow->execute()) {
            echo "Followed successfully.";
        } else {
            echo "Errore durante il follow: " . $stmt_insert_follow->error;
        }
        $stmt_insert_follow->close();
    }
    $stmt_check_follow->close();
} elseif ($action === 'unfollow') {
    // Rimuovi il follow dal database
    $deleteFollowQuery = "DELETE FROM follow WHERE id_utente = ? AND id_blog = ?";
    $stmt_delete_follow = $conn->prepare($deleteFollowQuery);
    $stmt_delete_follow->bind_param("ii", $userId, $blogId);
    if ($stmt_delete_follow->execute()) {
        echo "Unfollowed successfully.";
    } else {
        echo "Errore durante l'unfollow: " . $stmt_delete_follow->error;
    }
    $stmt_delete_follow->close();
}

$conn->close();
?>