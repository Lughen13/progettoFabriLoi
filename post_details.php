<?php
require_once 'conn.php';
session_start();

// Verifica se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$postId = $_GET['post_id'];

// Recupera i dettagli del post
$postQuery = "SELECT p.*, u.username, b.titolo_blog
              FROM post p
              JOIN utente u ON p.id_autore = u.id_utente
              JOIN blog b ON p.id_blog = b.id_blog
              WHERE p.id_post = $postId";
$postResult = $conn->query($postQuery);
$post = $postResult->fetch_assoc();

// Gestione dell'invio del form per aggiungere un commento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $comment = $_POST['comment'];
    $addCommentQuery = "INSERT INTO commento (contenuto, id_utente, id_post, data_comm)
                        VALUES ('$comment', $userId, $postId, NOW())";
    $conn->query($addCommentQuery);

    // Aggiorna il conteggio dei commenti per il post
    $updateCommentsCountQuery = "UPDATE post SET comments_count = comments_count + 1 WHERE id_post = $postId";
    $conn->query($updateCommentsCountQuery);
}

// Gestione dell'invio del form per aggiungere un "mi piace"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_like'])) {
    $addLikeQuery = "INSERT INTO like (id_utente, id_post) VALUES ($userId, $postId)";
    $conn->query($addLikeQuery);

    // Aggiorna il conteggio dei "mi piace" per il post
    $updateLikesCountQuery = "UPDATE post SET likes_count = likes_count + 1 WHERE id_post = $postId";
    $conn->query($updateLikesCountQuery);
}

// Recupera i commenti per il post
$commentsQuery = "SELECT c.contenuto, c.data_comm, u.username
                  FROM commento c
                  JOIN utente u ON c.id_utente = u.id_utente
                  WHERE c.id_post = $postId
                  ORDER BY c.data_comm DESC";
$commentsResult = $conn->query($commentsQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $post['titolo_post']; ?></title>
</head>
<body>
    <h1><?php echo $post['titolo_post']; ?></h1>
    <p><?php echo $post['descrizione_post']; ?></p>
    <p>Autore: <?php echo $post['username']; ?></p>
    <p>Blog: <?php echo $post['titolo_blog']; ?></p>
    <?php if (!empty($post['img_post'])): ?>
        <img src="<?php echo $post['img_post']; ?>" alt="Immagine del post">
    <?php endif; ?>
    <p>Commenti: <?php echo $post['comments_count']; ?></p>
    <p>Mi piace: <?php echo $post['likes_count']; ?></p>

    <h2>Aggiungi un commento</h2>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?post_id=<?php echo $postId; ?>">
        <textarea name="comment" required></textarea>
        <button type="submit" name="add_comment">Aggiungi commento</button>
    </form>

    <h2>Mi piace</h2>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?post_id=<?php echo $postId; ?>">
        <button type="submit" name="add_like">Mi piace</button>
    </form>

    <h2>Commenti</h2>
    <?php while ($comment = $commentsResult->fetch_assoc()): ?>
        <div>
            <p><?php echo $comment['contenuto']; ?></p>
            <p>Autore: <?php echo $comment['username']; ?></p>
            <p>Data: <?php echo $comment['data_comm']; ?></p>
        </div>
    <?php endwhile; ?>
</body>
</html>
