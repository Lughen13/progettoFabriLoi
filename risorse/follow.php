<?php
session_start();
require_once '../configurazione/conn.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../pubblico/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['id'];
    $blogId = isset($_POST['blog_id']) ? intval($_POST['blog_id']) : null;
    $action = isset($_POST['action']) ? $_POST['action'] : null;

    if ($blogId && ($action == 'follow' || $action == 'unfollow')) {

        $checkFollowQuery = "SELECT COUNT(*) as cnt FROM follow WHERE id_utente = ? AND id_blog = ?";
        $stmt_check = $conn->prepare($checkFollowQuery);
        $stmt_check->bind_param("ii", $userId, $blogId);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $row = $result->fetch_assoc();
        $isFollowing = $row['cnt'] > 0;
        $stmt_check->close();

        if ($action == 'follow' && !$isFollowing) {
            // aggiungo il follow alla tabella 
            $queryFollow = "INSERT INTO follow (id_utente, id_blog, data_follow) VALUES (?, ?, NOW())";
            $stmt_follow = $conn->prepare($queryFollow);
            if ($stmt_follow) {
                $stmt_follow->bind_param("ii", $userId, $blogId);
                if ($stmt_follow->execute()) {

                    // recupero il proprietario del blog 
                    $queryGetBlogOwner = "SELECT id_proprietario FROM blog WHERE id_blog = ?";
                    $stmtBlogOwner = $conn->prepare($queryGetBlogOwner);
                    $stmtBlogOwner->bind_param("i", $blogId);
                    $stmtBlogOwner->execute();
                    $resultBlogOwner = $stmtBlogOwner->get_result();
                    if ($resultBlogOwner->num_rows > 0) {
                        $blogOwner = $resultBlogOwner->fetch_assoc();
                        $id_blogOwner = $blogOwner['id_proprietario'];

                        // ed aggiungo il follow alla tabella notifiche
                        $queryInsertNotifica = "INSERT INTO notifiche (user_id, sender_id, tipo, contenuto_id) VALUES (?, ?, 'follow', ?)";
                        $stmtInsertNotifica = $conn->prepare($queryInsertNotifica);
                        $stmtInsertNotifica->bind_param("iii", $id_blogOwner, $userId, $blogId);
                        $stmtInsertNotifica->execute();
                        $stmtInsertNotifica->close();
                    }
                    $stmtBlogOwner->close();

                    // incremento il numero di follower 
                    $queryIncrementFollower = "UPDATE blog SET followers_count = + 1 WHERE id_blog = ?";
                    $stmt_increment = $conn->prepare($queryIncrementFollower);
                    $stmt_increment->bind_param("i", $blogId);
                    $stmt_increment->execute();
                    $stmt_increment->close();

                    $stmt_follow->close();
                    header("Location: ../pubblico/view_blog.php?id_blog=$blogId");
                    exit;
                } else {
                    echo "Errore nell'esecuzione della query: " . $stmt_follow->error;
                }
            } else {
                echo "Errore nella preparazione della query: " . $conn->error;
            }
        } elseif ($action == 'unfollow' && $isFollowing) {
            // rimuovo il follow dalla tabella
            $queryUnfollow = "DELETE FROM follow WHERE id_utente = ? AND id_blog = ?";
            $stmt_unfollow = $conn->prepare($queryUnfollow);
            if ($stmt_unfollow) {
                $stmt_unfollow->bind_param("ii", $userId, $blogId);
                if ($stmt_unfollow->execute()) {
                    // decremento il numero dei follower
                    $queryDecrementFollower = "UPDATE blog SET followers_count = CASE WHEN followers_count > 0 THEN followers_count - 1 ELSE 0 END WHERE id_blog = ?";
                    $stmt_decrement = $conn->prepare($queryDecrementFollower);
                    $stmt_decrement->bind_param("i", $blogId);
                    $stmt_decrement->execute();
                    $stmt_decrement->close();

                    // elimino la notifica di follow
                    $queryDeleteNotifica = "DELETE FROM notifiche WHERE sender_id = ? AND contenuto_id = ? AND tipo = 'follow'";
                    $stmt_delete_notifica = $conn->prepare($queryDeleteNotifica);
                    $stmt_delete_notifica->bind_param("ii", $userId, $blogId);
                    $stmt_delete_notifica->execute();
                    $stmt_delete_notifica->close();

                    $stmt_unfollow->close();
                    header("Location: ../pubblico/view_blog.php?id_blog=$blogId");
                    exit;
                } else {
                    echo "Errore nell'esecuzione della query: " . $stmt_unfollow->error;
                }
            } else {
                echo "Errore nella preparazione della query: " . $conn->error;
            }
        } else {
            echo "Azione non valida o l'utente già segue/non segue questo blog.";
        }
    } else {
        echo "Parametri non validi.";
    }
} else {
    echo "Metodo non supportato.";
}

$conn->close();
?>
