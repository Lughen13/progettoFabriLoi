<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
session_start();
require_once '../configurazione/conn.php';

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../pubblico/login.php');
    exit;
}

$userId = $_SESSION['id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Gestione della modifica del blog
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_blog'])) {
    $id_blog = $_POST['id_blog'];
    $newTitle = $_POST['new_title'];
    $newDescription = $_POST['new_description'];

    // Verifica se l'utente è autorizzato a modificare il blog
    $checkBlogQuery = "SELECT id_blog FROM blog WHERE id_blog = ? AND id_proprietario = ?";
    $stmt_check_blog = $conn->prepare($checkBlogQuery);
    $stmt_check_blog->bind_param('ii', $id_blog, $userId);
    $stmt_check_blog->execute();
    $stmt_check_blog->store_result();

    if ($stmt_check_blog->num_rows > 0) {
        $updateBlogQuery = "UPDATE blog SET titolo_blog = ?, descrizione = ? WHERE id_blog = ?";
        $stmt_update_blog = $conn->prepare($updateBlogQuery);
        $stmt_update_blog->bind_param('ssi', $newTitle, $newDescription, $id_blog);
        if ($stmt_update_blog->execute()) {
            // Aggiornamento del blog eseguito con successo
            header("Location: ../pubblico/my_profile.php");  // Reindirizza alla pagina del profilo
            exit();
        } else {
            echo "Errore durante l'aggiornamento del blog: " . $stmt_update_blog->error;
        }
        $stmt_update_blog->close();
    } else {
        echo "Non sei autorizzato a modificare questo blog.";
    }
    $stmt_check_blog->close();
}

// Gestione della modifica del post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_post'])) {
    $id_post = $_POST['id_post'];
    $newTitle = $_POST['new_title'];
    $newDescription = $_POST['new_description'];

    // Verifica se l'utente è autorizzato a modificare il post
    $checkPostQuery = "SELECT p.id_post
                        FROM post p
                        JOIN blog b ON p.id_blog = b.id_blog
                        WHERE p.id_post = ? AND (p.id_autore = ? OR b.id_proprietario = ?)";
    $stmt_check_post = $conn->prepare($checkPostQuery);
    $stmt_check_post->bind_param('iii', $id_post, $userId, $userId);
    $stmt_check_post->execute();
    $stmt_check_post->store_result();

    if ($stmt_check_post->num_rows > 0) {
        $updatePostQuery = "UPDATE post SET titolo_post = ?, descrizione_post = ? WHERE id_post = ?";
        $stmt_update_post = $conn->prepare($updatePostQuery);
        $stmt_update_post->bind_param('ssi', $newTitle, $newDescription, $id_post);
        if ($stmt_update_post->execute()) {
            // Aggiornamento del post eseguito con successo
            header("Location: ../pubblico/my_profile.php");  // Reindirizza alla pagina del profilo
            exit();
        } else {
            echo "Errore durante l'aggiornamento del post: " . $stmt_update_post->error;
        }
        $stmt_update_post->close();
    } else {
        echo "Non sei autorizzato a modificare questo post.";
    }
    $stmt_check_post->close();
}

$conn->close();
?>