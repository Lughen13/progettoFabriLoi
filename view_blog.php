<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
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

// Verifica se l'utente sta seguendo questo blog
$isFollowing = false;
$queryCheckFollow = "SELECT * FROM follow WHERE id_utente = ? AND id_blog = ?";
$stmt_check_follow = $conn->prepare($queryCheckFollow);
$stmt_check_follow->bind_param("ii", $userId, $id_blog);
$stmt_check_follow->execute();
$result_check_follow = $stmt_check_follow->get_result();
if ($result_check_follow->num_rows > 0) {
    $isFollowing = true;
}
$stmt_check_follow->close();

// Gestione del commento e like
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
           // Controlla se l'utente ha già messo Mi Piace
           $checkLikeQuery = "SELECT * FROM likes WHERE id_utente = ? AND id_post = ?";
           $stmt = $conn->prepare($checkLikeQuery);
           $stmt->bind_param("ii", $userId, $postId);
           $stmt->execute();
           $result = $stmt->get_result();
           
           if ($result->num_rows === 0) {
               // Inserisci il Mi Piace nel database
               $insertLikeQuery = "INSERT INTO likes (id_utente, id_post) VALUES (?, ?)";
               $stmt = $conn->prepare($insertLikeQuery);
               $stmt->bind_param("ii", $userId, $postId);
               if ($stmt->execute()) {
                   // Incrementa il conteggio dei Mi Piace nel post
                   $updateLikesCountQuery = "UPDATE post SET likes_count = likes_count + 1 WHERE id_post = ?";
                   $stmt = $conn->prepare($updateLikesCountQuery);
                   $stmt->bind_param("i", $postId);
                   $stmt->execute();
                   $stmt->close();
               } else {
                   echo "Errore durante l'inserimento del Mi Piace: " . $stmt->error;
               }
           } else {
               echo "Hai già messo Mi Piace a questo post.";
           }
           
           
          
           header("Location: view_blog.php?id_blog={$id_blog}");
           exit;
       } elseif ($_POST['action'] === 'unlike') {
           // Rimuovi il Mi Piace dal database
           $deleteLikeQuery = "DELETE FROM likes WHERE id_utente = ? AND id_post = ?";
           $stmt = $conn->prepare($deleteLikeQuery);
           $stmt->bind_param("ii", $userId, $postId);
           if ($stmt->execute()) {
               // Decrementa il conteggio dei Mi Piace nel post
               $updateLikesCountQuery = "UPDATE post SET likes_count = CASE WHEN likes_count > 0 THEN likes_count - 1 ELSE 0 END WHERE id_post = ?";
               $stmt = $conn->prepare($updateLikesCountQuery);
               $stmt->bind_param("i", $postId);
               $stmt->execute();
               $stmt->close();
           } else {
               echo "Errore durante la rimozione del Mi Piace: " . $stmt->error;
           }
           
           // Redirect back to the same page after handling unlike action
           header("Location: view_blog.php?id_blog={$id_blog}");
           exit;
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

// Recupera il conteggio dei follower per questo blog
$queryFollowCount = "SELECT COUNT(*) AS followers_count FROM follow WHERE id_blog = ?";
$stmt_follow_count = $conn->prepare($queryFollowCount);
$stmt_follow_count->bind_param("i", $id_blog);
$stmt_follow_count->execute();
$result_follow_count = $stmt_follow_count->get_result();
$follow_count = $result_follow_count->fetch_assoc()['followers_count'];
$stmt_follow_count->close();


$conn->close();
?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizza Blog</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
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
        <!-- Mostra il conteggio dei follower -->
        <p><strong>Follower:</strong> <?php echo $follow_count; ?></p>
        
        <!-- Pulsanti per follow/unfollow -->
        <?php if (isset($isFollowing) && $isFollowing): ?>
            <form method="post" class="d-inline">
                <input type="hidden" name="blog_id" value="<?php echo $id_blog; ?>">
                <input type="hidden" name="action" value="unfollow">
                <button type="submit" class="btn btn-danger">Non Seguire più</button>
            </form>
        <?php else: ?>
            <form method="post" class="d-inline">
                <input type="hidden" name="blog_id" value="<?php echo $id_blog; ?>">
                <input type="hidden" name="action" value="follow">
                <button type="submit" class="btn btn-primary">Segui questo Blog</button>
            </form>
        <?php endif; ?>
        <img src="blog_logo/<?php echo htmlspecialchars($blog['img_logo']); ?>" alt="Logo del Blog" width="100">

        <?php if ($resultPosts->num_rows > 0): ?>
            <h2 class="mt-5">Post:</h2>
            <?php while ($post = $resultPosts->fetch_assoc()): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($post['titolo_post']); ?></h3>
                        <p class="card-text"><?php echo htmlspecialchars($post['descrizione_post']); ?></p>
                        <?php if (!empty($post['img_post'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($post['img_post']); ?>" class="card-img-top" alt="Immagine del Post" width="100">
                        <?php endif; ?>

                        <!-- Visualizzazione dei commenti -->
                        <?php if (isset($comments[$post['id_post']])): ?>
                            <h4>Commenti:</h4>
                            <?php foreach ($comments[$post['id_post']] as $comment): ?>
                                <div class="card">
                                    <div class="card-body">
                                        <p class="card-text"><strong><?php echo htmlspecialchars($comment['username']); ?></strong> (<?php echo htmlspecialchars($comment['data_comm']); ?>): <?php echo htmlspecialchars($comment['contenuto']); ?></p>
                                        </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Form per inserire un commento -->
                    <form method="post" class="mt-3">
                        <input type="hidden" name="post_id" value="<?php echo $post['id_post']; ?>">
                        <div class="form-group">
                            <textarea class="form-control" name="comment" placeholder="Inserisci il tuo commento"></textarea>
                        </div>
                        <button type="submit" name="action" value="comment" class="btn btn-primary">Commenta</button>
                    </form>

                    <!-- Gestione Mi Piace -->
                    <div class="mt-3">
                    <?php
$totalLikes = isset($likeCounts[$post['id_post']]) ? $likeCounts[$post['id_post']] : 0;
$likeAction = 'like';
if ($_SESSION['loggedin'] === true) {
    // Riapri la connessione se è stata chiusa
    include 'conn.php';

    $likeQuery = "SELECT * FROM likes WHERE id_post = ? AND id_utente = ?";
    $stmt = $conn->prepare($likeQuery);
    $stmt->bind_param("ii", $post['id_post'], $userId);
    $stmt->execute();
    $likeResult = $stmt->get_result();
    if ($likeResult->num_rows > 0) {
        $likeAction = 'unlike';
    }
    $stmt->close();
}
?>
<form method="post">
    <input type="hidden" name="post_id" value="<?php echo $post['id_post']; ?>">
    <button type="submit" name="action" value="<?php echo $likeAction; ?>" class="btn btn-success">
        Mi Piace <?php echo "($totalLikes)"; ?>
    </button>
</form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Non ci sono post da mostrare.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>

