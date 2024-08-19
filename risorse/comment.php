<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
require_once '../configurazione/conn.php';
session_start();

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
$referer = $_SERVER['HTTP_REFERER'];  // Ottieni l'URL della pagina precedente

$premium_status = $_SESSION['premium'] ?? false;

if ($action === 'insert' && $postId && !empty($comment)) {
    if (!$premium_status) {
        $queryCountComments = "SELECT COUNT(*) AS num_comments FROM commento WHERE id_utente = ? AND DATE(data_comm) = CURDATE()";
        $stmt_count_comments = $conn->prepare($queryCountComments);
        $stmt_count_comments->bind_param("i", $userId);
        $stmt_count_comments->execute();
        $result_count_comments = $stmt_count_comments->get_result();
        $num_comments_today = $result_count_comments->fetch_assoc()['num_comments'];
        $stmt_count_comments->close();

        if ($num_comments_today >= 20) {
            header("Location: " . $referer . "?error=limite_superato");
            exit;
        }
    }

    $insertCommentQuery = "INSERT INTO commento (data_comm, contenuto, id_utente, id_post) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($insertCommentQuery);
    $stmt->bind_param("sii", $comment, $userId, $postId);
    if ($stmt->execute()) {
        $queryBlogOwner = "SELECT b.id_proprietario FROM post p JOIN blog b ON p.id_blog = b.id_blog WHERE p.id_post = ?";
        $stmt_blog_owner = $conn->prepare($queryBlogOwner);
        $stmt_blog_owner->bind_param("i", $postId);
        $stmt_blog_owner->execute();
        $result_blog_owner = $stmt_blog_owner->get_result();
        if ($result_blog_owner->num_rows > 0) {
            $blogOwner = $result_blog_owner->fetch_assoc();
            $id_blogOwner = $blogOwner['id_proprietario'];
            if ($userId != $id_blogOwner) {
                $queryInsertNotifica = "INSERT INTO notifiche (user_id, sender_id, tipo, contenuto_id) VALUES (?, ?, 'comment', ?)";
                $stmt_insert_notifica = $conn->prepare($queryInsertNotifica);
                $stmt_insert_notifica->bind_param("iii", $id_blogOwner, $userId, $postId);
                $stmt_insert_notifica->execute();
            }
        }
        $stmt_blog_owner->close();
        header("Location: " . $referer);   // referer serve per tornare alla pagina in cui si trova l'utente quando inserisce il commento 
        exit;
    } else {
        echo "Errore durante l'inserimento del commento: " . $stmt->error;
    }
    $stmt->close();
} elseif ($action === 'update' && $id_comm && !empty($comment)) {
    $updateCommentQuery = "UPDATE commento SET contenuto = ? WHERE id_comm = ? AND id_utente = ?";
    $stmt = $conn->prepare($updateCommentQuery);
    $stmt->bind_param("sii", $comment, $id_comm, $userId);
    if ($stmt->execute()) {
        header("Location: " . $referer); // referer serve per tornare alla pagina in cui si trova l'utente quando modifica il commento
        exit;
    } else {
        echo "Errore durante l'aggiornamento del commento: " . $stmt->error;
    }
    $stmt->close();
} elseif ($action === 'delete' && $id_comm) {
    $deleteCommentQuery = "DELETE FROM commento WHERE id_comm = ? AND id_utente = ?";
    $stmt = $conn->prepare($deleteCommentQuery);
    $stmt->bind_param("ii", $id_comm, $userId);
    if ($stmt->execute()) {
        header("Location: " . $referer); // referer serve per tornare alla pagina in cui si trova l'utente quando  elimina il commento 
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
