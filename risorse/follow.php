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
        // Controllo per vedere se l'utente segue già il blog
        $checkFollowQuery = "SELECT COUNT(*) as cnt FROM follow WHERE id_utente = ? AND id_blog = ?";
        $stmt_check = $conn->prepare($checkFollowQuery);
        $stmt_check->bind_param("ii", $userId, $blogId);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $row = $result->fetch_assoc();
        $isFollowing = $row['cnt'] > 0;
        $stmt_check->close();

        if ($action == 'follow' && !$isFollowing) {
            // Aggiunge il follow
            $queryFollow = "INSERT INTO follow (id_utente, id_blog, data_follow) VALUES (?, ?, NOW())";
            $stmt_follow = $conn->prepare($queryFollow);
            if ($stmt_follow) {
                $stmt_follow->bind_param("ii", $userId, $blogId);
                if ($stmt_follow->execute()) {
                    // Incrementa il conteggio dei follower
                    $queryIncrementFollower = "UPDATE blog SET followers_count = followers_count + 0 WHERE id_blog = ?";
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
            // Rimuove il follow
            $queryUnfollow = "DELETE FROM follow WHERE id_utente = ? AND id_blog = ?";
            $stmt_unfollow = $conn->prepare($queryUnfollow);
            if ($stmt_unfollow) {
                $stmt_unfollow->bind_param("ii", $userId, $blogId);
                if ($stmt_unfollow->execute()) {
                    // Decrementa il conteggio dei follower
                    $queryDecrementFollower = "UPDATE blog SET followers_count = CASE WHEN followers_count > 0 THEN followers_count - 1 ELSE 0 END WHERE id_blog = ?";
                    $stmt_decrement = $conn->prepare($queryDecrementFollower);
                    $stmt_decrement->bind_param("i", $blogId);
                    $stmt_decrement->execute();
                    $stmt_decrement->close();

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
?>