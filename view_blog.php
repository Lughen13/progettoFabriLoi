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
$query = "SELECT b.titolo_blog, b.descrizione, b.img_logo
          FROM blog b
          WHERE b.id_blog = ?";
$stmt_blog = $conn->prepare($query);
$stmt_blog->bind_param("i", $id_blog);
$stmt_blog->execute();
$result_blog = $stmt_blog->get_result();
$blog = $result_blog->fetch_assoc();
$stmt_blog->close();

// Gestione del commento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'comment') {
        if (isset($_POST['comment']) && !empty($_POST['comment']) && isset($_POST['post_id'])) {
            $comment = $_POST['comment'];
            $postId = $_POST['post_id'];

            // Inserisci il commento nel database
            $insertCommentQuery = "INSERT INTO commento (data_comm, contenuto, id_utente, id_post) VALUES (NOW(), ?, ?, ?)";
            $stmt_comment = $conn->prepare($insertCommentQuery);
            $stmt_comment->bind_param("sii", $comment, $userId, $postId);
            if ($stmt_comment->execute()) {
                // Commento inserito con successo
                header("Location: view_blog.php?id_blog={$id_blog}");
                exit;
            } else {
                echo "Errore durante l'inserimento del commento: " . $stmt_comment->error;
            }
            $stmt_comment->close();
        }
    } elseif ($_POST['action'] === 'like' || $_POST['action'] === 'unlike') {
        $postId = $_POST['post_id'];

        if ($_POST['action'] === 'like') {
            // Inserisci il Mi Piace nel database
            $insertLikeQuery = "INSERT INTO likes (id_utente, id_post) VALUES (?, ?)";
            $stmt_like = $conn->prepare($insertLikeQuery);
            $stmt_like->bind_param("ii", $userId, $postId);
            if ($stmt_like->execute()) {
                // Mi Piace inserito con successo
                header("Location: view_blog.php?id_blog={$id_blog}");
                exit;
            } else {
                echo "Errore durante l'inserimento del Mi Piace: " . $stmt_like->error;
            }
            $stmt_like->close();
        } elseif ($_POST['action'] === 'unlike') {
            // Rimuovi il Mi Piace dal database
            $deleteLikeQuery = "DELETE FROM likes WHERE id_utente = ? AND id_post = ?";
            $stmt_unlike = $conn->prepare($deleteLikeQuery);
            $stmt_unlike->bind_param("ii", $userId, $postId);
            if ($stmt_unlike->execute()) {
                // Mi Piace rimosso con successo
                header("Location: view_blog.php?id_blog={$id_blog}");
                exit;
            } else {
                echo "Errore durante la rimozione del Mi Piace: " . $stmt_unlike->error;
            }
            $stmt_unlike->close();
        }
    }
}

// Recupera i post con i relativi commenti e il conteggio dei Mi Piace
$queryPosts = "SELECT p.id_post, p.titolo_post, p.descrizione_post, p.img_post, 
                     COUNT(l.id_like) AS total_likes
              FROM post p
              LEFT JOIN likes l ON p.id_post = l.id_post AND l.id_utente = ?
              WHERE p.id_blog = ?
              GROUP BY p.id_post";
$stmt_posts = $conn->prepare($queryPosts);
$stmt_posts->bind_param("ii", $userId, $id_blog);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();

// Preparazione per la visualizzazione dei commenti
$queryComments = "SELECT c.id_comm, c.data_comm, c.contenuto, u.username, p.id_post
                  FROM commento c
                  INNER JOIN utente u ON c.id_utente = u.id_utente
                  INNER JOIN post p ON c.id_post = p.id_post
                  WHERE p.id_blog = ?";
$stmt_comments = $conn->prepare($queryComments);
$stmt_comments->bind_param("i", $id_blog);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();
$comments = [];
while ($row = $result_comments->fetch_assoc()) {
    $comments[$row['id_post']][] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizza Blog</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        .like-section {
            display: flex;
            align-items: center;
        }

        .like-section button {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Visualizza Blog</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="home.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="my_profile.php">Il mio profilo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="account_settings.php">Impostazioni profilo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0" action="search.php" method="GET">
                <input class="form-control mr-sm-2" type="text" name="query" placeholder="Cerca blog o post">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Cerca</button>
            </form>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Benvenuto nella home di <?php echo htmlspecialchars($blog['titolo_blog']); ?></h1>
        <p><?php echo htmlspecialchars($blog['descrizione']); ?></p>
        <img src="uploads/<?php echo htmlspecialchars($blog['img_logo']); ?>" alt="Logo del Blog" width="100">

        <?php if ($result_posts->num_rows > 0): ?>
            <h2 class="mt-5">Post:</h2>
            <?php while ($post = $result_posts->fetch_assoc()): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($post['titolo_post']); ?></h3>
                        <p class="card-text"><?php echo htmlspecialchars($post['descrizione_post']); ?></p>
                        <?php if (!empty($post['img_post'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($post['img_post']); ?>" class="card-img-top" alt="Immagine del Post">
                        <?php endif; ?>

                        <div class="like-section mt-3">
                            <?php
                            $likeStatus = false;
                            $likeAction = 'like';

                            // Verifica se l'utente ha messo Mi Piace al post
                            $checkLikeQuery = "SELECT * FROM likes WHERE id_utente = ? AND id_post = ?";
                            $stmt_like_check = $conn->prepare($checkLikeQuery);
                            $stmt_like_check->bind_param("ii", $userId, $post['id_post']);
                            $stmt_like_check->execute();
                            $result_like_check =$stmt_like_check->get_result();
                            if ($result_like_check->num_rows > 0) {
                            $likeStatus = true;
                            $likeAction = Unlike;
                            }
                            $stmt_like_check->close();
                                                    // Recupera il conteggio totale dei Mi Piace per il post
                        $totalLikesQuery = "SELECT COUNT(*) AS total_likes FROM likes WHERE id_post = ?";
                        $stmt_total_likes = $conn->prepare($totalLikesQuery);
                        $stmt_total_likes->bind_param("i", $post['id_post']);
                        $stmt_total_likes->execute();
                        $result_total_likes = $stmt_total_likes->get_result();
                        $totalLikes = $result_total_likes->fetch_assoc()['total_likes'];
                        $stmt_total_likes->close();
                        ?>
                        <form method="post">
                            <input type="hidden" name="post_id" value="<?php echo $post['id_post']; ?>">
                            <button type="submit" name="action" value="<?php echo $likeAction; ?>" class="btn btn-primary">
                                <?php echo $likeStatus ? 'Unlike' : 'Like'; ?>
                            </button>
                            <span class="like-count"><?php echo $totalLikes; ?> Mi Piace</span>
                        </form>
                    </div>

                    <h5 class="mt-3">Commenti:</h5>
                    <?php if (isset($comments[$post['id_post']])): ?>
                        <?php foreach ($comments[$post['id_post']] as $comment): ?>
                            <div class="card mt-2">
                                <div class="card-body">
                                    <p class="card-text"><?php echo htmlspecialchars($comment['contenuto']); ?></p>
                                    <p class="card-text">Di: <?php echo htmlspecialchars($comment['username']); ?></p>
                                    <p class="card-text">Il: <?php echo htmlspecialchars($comment['data_comm']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Non ci sono commenti per questo post.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Non ci sono post da visualizzare.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>