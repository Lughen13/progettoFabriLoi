<?php
include 'conn.php';
session_start();

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Recupera l'id dell'utente dalla sessione
$userId = $_SESSION['id'];

// Recupera l'id del blog dalla query string
$id_blog = isset($_GET['id_blog']) ? $_GET['id_blog'] : null;

// Recupera i dettagli del blog
$query = "SELECT b.titolo_blog, b.descrizione, b.img_logo, p.id_post, p.titolo_post, p.descrizione_post, p.img_post
          FROM blog b
          LEFT JOIN post p ON b.id_blog = p.id_blog
          WHERE b.id_blog = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_blog);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();
$stmt->close();

// Gestione del commento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'comment') {
        if (isset($_POST['comment']) && !empty($_POST['comment']) && isset($_POST['post_id'])) {
            $comment = $_POST['comment'];
            $postId = $_POST['post_id'];

            // Inserisci il commento nel database
            $insertCommentQuery = "INSERT INTO commento (data_comm, contenuto, id_utente, id_post) VALUES (NOW(), ?, ?, ?)";
            $stmt = $conn->prepare($insertCommentQuery);
            $stmt->bind_param("sii", $comment, $userId, $postId);
            if ($stmt->execute()) {
                // Commento inserito con successo
                header("Location: view_blog.php?id_blog={$id_blog}");
                exit;
            } else {
                echo "Errore durante l'inserimento del commento: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'like' || $_POST['action'] === 'unlike') {
        $postId = $_POST['post_id'];

        if ($_POST['action'] === 'like') {
            // Inserisci il Mi Piace nel database
            $insertLikeQuery = "INSERT INTO likes (id_utente, id_post) VALUES (?, ?)";
            $stmt = $conn->prepare($insertLikeQuery);
            $stmt->bind_param("ii", $userId, $postId);
            if ($stmt->execute()) {
                // Mi Piace inserito con successo
                header("Location: view_blog.php?id_blog={$id_blog}");
                exit;
            } else {
                echo "Errore durante l'inserimento del Mi Piace: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'unlike') {
            // Rimuovi il Mi Piace dal database
            $deleteLikeQuery = "DELETE FROM likes WHERE id_utente = ? AND id_post = ?";
            $stmt = $conn->prepare($deleteLikeQuery);
            $stmt->bind_param("ii", $userId, $postId);
            if ($stmt->execute()) {
                // Mi Piace rimosso con successo
                header("Location: view_blog.php?id_blog={$id_blog}");
                exit;
            } else {
                echo "Errore durante la rimozione del Mi Piace: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Recupera i post con i relativi commenti e il conteggio dei Mi Piace
$queryPosts = "SELECT p.id_post, p.titolo_post, p.descrizione_post, p.img_post, 
                     COUNT(l.id_like) AS total_likes
              FROM post p
              LEFT JOIN likes l ON p.id_post = l.id_post
              WHERE p.id_blog = ?
              GROUP BY p.id_post";
$stmt = $conn->prepare($queryPosts);
$stmt->bind_param("i", $id_blog);
$stmt->execute();
$resultPosts = $stmt->get_result();
$stmt->close();

// Preparazione per la visualizzazione dei commenti
$queryComments = "SELECT c.id_comm, c.data_comm, c.contenuto, u.username, p.id_post
                  FROM commento c
                  INNER JOIN utente u ON c.id_utente = u.id_utente
                  INNER JOIN post p ON c.id_post = p.id_post
                  WHERE p.id_blog = ?";
$stmt = $conn->prepare($queryComments);
$stmt->bind_param("i", $id_blog);
$stmt->execute();
$resultComments = $stmt->get_result();
$comments = [];
while ($row = $resultComments->fetch_assoc()) {
    $comments[$row['id_post']][] = $row;
}
$stmt->close();

// Preparazione per la visualizzazione dei Mi Piace
$likeCounts = [];
$queryLikes = "SELECT id_post, COUNT(*) AS like_count FROM likes GROUP BY id_post";
$resultLikes = $conn->query($queryLikes);
while ($row = $resultLikes->fetch_assoc()) {
    $likeCounts[$row['id_post']] = $row['like_count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizza Blog</title>
</head>
<body>
<style>
        .blog_logo {
            max-width: 75px;
            max-height: 75px;
        }
    </style>
    <nav>
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="my_profile.php">Il mio profilo</a></li>
            <li><a href="account_settings.php">Impostazioni profilo</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
        <form action="search.php" method="GET">
            <input type="text" name="query" placeholder="Cerca blog o post">
            <button type="submit">Cerca</button>
        </form>
    </nav>

    <h1>Benvenuto nella home di <?php echo htmlspecialchars($blog['titolo_blog']); ?></h1>
    <p><?php echo htmlspecialchars($blog['descrizione']); ?></p>
    <img src="blog_logo/<?php echo basename($blog['img_logo']); ?>" alt="Logo del Blog" width="100">

    <?php if ($resultPosts->num_rows > 0): ?>
        <h2>Post:</h2>
        <ul>
            <?php while ($post = $resultPosts->fetch_assoc()): ?>
                <li>
                    <h3><?php echo htmlspecialchars($post['titolo_post']); ?></h3>
                    <p><?php echo htmlspecialchars($post['descrizione_post']); ?></p>
                    <?php if (!empty($post['img_post'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($post['img_post']); ?>" alt="Immagine del Post" width="100">
                    <?php endif; ?>

                    <!-- Visualizzazione dei commenti -->
                    <?php if (isset($comments[$post['id_post']])): ?>
                        <h4>Commenti:</h4>
                        <?php foreach ($comments[$post['id_post']] as $comment): ?>
                            <p><strong><?php echo htmlspecialchars($comment['username']); ?></strong> (<?php echo htmlspecialchars($comment['data_comm']); ?>): <?php echo htmlspecialchars($comment['contenuto']); ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Form per inserire un commento -->
                    <form method="post">
                        <input type="hidden" name="post_id" value="<?php echo $post['id_post']; ?>">
                        <textarea name="comment" placeholder="Inserisci il tuo commento"></textarea>
                        <button type="submit" name="action" value="comment">Inserisci Commento</button>
                    </form>

                    <!-- Form per mettere e togliere Mi Piace -->
                    <form method="post">
                        <input type="hidden" name="post_id" value="<?php echo $post['id_post']; ?>">
                        <?php if (isset($likeCounts[$post['id_post']])): ?>
                            <button type="submit" name="action" value="unlike">Togli Mi Piace</button>
                        <?php else: ?>
                            <button type="submit" name="action" value="like">Mi Piace</button>
                        <?php endif; ?>
                        <span><?php echo isset($likeCounts[$post['id_post']]) ? $likeCounts[$post['id_post']] : 0; ?> Mi Piace</span>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>Questo blog non ha ancora post.</p>
    <?php endif; ?>
</body>
</html>