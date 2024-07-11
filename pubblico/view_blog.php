<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
require_once '../configurazione/conn.php';
session_start();

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../pubblico/login.php');
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

// Assumendo che ci sia una colonna 'is_premium' nella tabella 'utente'
$queryCheckPremium = "SELECT premium FROM utente WHERE id_utente = ?";
$stmt_check_premium = $conn->prepare($queryCheckPremium);
$stmt_check_premium->bind_param("i", $userId);  // $userId è l'ID dell'utente attualmente autenticato
$stmt_check_premium->execute();
$result_check_premium = $stmt_check_premium->get_result();

if ($result_check_premium->num_rows > 0) {
    $premium_status = $result_check_premium->fetch_assoc()['premium'];
    // Verifica se l'utente è premium
    if ($premium_status == 1) {
        $_SESSION['premium'] = true;
    } else {
        $_SESSION['premium'] = false;
    }
} else {
    // Gestione dell'errore o del caso in cui l'utente non esiste
    $_SESSION['premium'] = false;  // Ad esempio, considera l'utente non premium
}

$stmt_check_premium->close();

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
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .navbar {
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

    </style>
</head>
<body>
<div class="container mt-4">
        <h1>ToteBlog</h1>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
         <!-- <a class="navbar-brand" href="#">Il Mio Profilo</a> -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link" href="../pubblico/home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pubblico/my_profile.php">Il mio profilo</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pubblico/account_settings.php">Impostazioni profilo</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pubblico/logout.php">Logout</a></li>
                </ul>
                <form class="form-inline my-2 my-lg-0" action="search.php" method="GET">
                    <input class="form-control mr-sm-2" type="text" name="query" placeholder="Cerca blog o post">
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Cerca</button>
                </form>
            </div>
        </nav>
        
    <div class="container mt-5">
        <h1>Questo è il blog: <?php echo htmlspecialchars(isset($blog['titolo_blog']) ? $blog['titolo_blog'] : ''); ?></h1>
        <p><?php echo htmlspecialchars(isset($blog['descrizione']) ? $blog['descrizione'] : ''); ?></p>
        <p>Follower: <?php echo $follow_count; ?></p>

        <!-- Bottone per stampare il blog (visualizzato solo per utenti premium) -->
         
        <?php if ($_SESSION['premium'] == 1): ?>
            <button class="btn btn-primary mb-3" onclick="window.print();">Stampa Blog</button>
        <?php endif; ?>


        <!-- Mostra il messaggio di errore se presente -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'limite_superato'): ?>
            <div class="alert alert-danger" role="alert">
                Hai superato il limite massimo di commenti giornalieri consentiti (20).
            </div>
        <?php endif; ?>

        <!-- Form per seguire o smettere di seguire il blog -->
        <?php if ($isFollowing): ?>
            <form method="post" action="../risorse/follow.php" class="d-inline">
                <input type="hidden" name="blog_id" value="<?php echo $id_blog; ?>">
                <input type="hidden" name="action" value="unfollow">
                <button type="submit" class="btn btn-danger">Non Seguire più</button>
            </form>
        <?php else: ?>
            <form method="post" action="../risorse/follow.php" class="d-inline">
                <input type="hidden" name="blog_id" value="<?php echo $id_blog; ?>">
                <input type="hidden" name="action" value="follow">
                <button type="submit" class="btn btn-primary">Segui questo Blog</button>
            </form>
        <?php endif; ?>

        <!-- Visualizzazione del logo del blog -->
        <img src="../blog_logo/<?php echo htmlspecialchars($blog['img_logo']); ?>" alt="Logo del Blog" width="100">

        <!-- Visualizzazione dei post -->
        <?php if ($resultPosts->num_rows > 0): ?>
            <h2 class="mt-5">Post:</h2>
            <?php while ($post = $resultPosts->fetch_assoc()): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo htmlspecialchars($post['titolo_post']); ?></h3>
                        <p class="card-text"><?php echo htmlspecialchars($post['descrizione_post']); ?></p>

                        <!-- Visualizzazione delle immagini del post -->
                        <?php 
                        $images = json_decode($post['img_post'], true);
                        if (!empty($images)): ?>
                            <div class="post-images">
                                <?php foreach ($images as $image): ?>
                                    <img src="../photo_post/<?php echo htmlspecialchars($image); ?>" class="img-thumbnail mr-2 mb-2" alt="Immagine del Post" width="100">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                                               <!-- Visualizzazione dei commenti -->
                                               <?php if (isset($comments[$post['id_post']])): ?>
                            <h4>Commenti:</h4>
                            <?php foreach ($comments[$post['id_post']] as $comment): ?>
                                <div class="card">
                                    <div class="card-body">
                                        <p class="card-text">
                                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong> (<?php echo htmlspecialchars($comment['data_comm']); ?>): 
                                            <span class="comment-text"><?php echo htmlspecialchars($comment['contenuto']); ?></span>
                                            <?php if ($comment['username'] == $_SESSION['username']): ?>
                                                <!-- Pulsante Modifica -->
                                                <button class="btn btn-warning btn-sm edit-comment-btn" data-comment-id="<?php echo $comment['id_comm']; ?>">Modifica</button>
                                                <!-- Pulsante Elimina -->
                                                <form method="post" action="../risorse/comment.php" class="d-inline">
                                                    <input type="hidden" name="id_comm" value="<?php echo $comment['id_comm']; ?>">
                                                    <input type="hidden" name="id_blog" value="<?php echo $id_blog; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="btn btn-danger btn-sm">Elimina</button>
                                                </form>
                                                <!-- Modifica commento -->
                                                <div class="edit-comment-form d-none">
                                                    <form method="post" action="../risorse/comment.php">
                                                        <input type="hidden" name="id_comm" value="<?php echo $comment['id_comm']; ?>">
                                                        <input type="hidden" name="id_blog" value="<?php echo $id_blog; ?>">
                                                        <input type="hidden" name="action" value="update">
                                                        <div class="form-group">
                                                            <textarea class="form-control" name="comment"><?php echo htmlspecialchars($comment['contenuto']); ?></textarea>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary btn-sm">Salva</button>
                                                        <button type="button" class="btn btn-secondary btn-sm cancel-edit-btn">Annulla</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Form per inserire un commento -->
                        <form method="post" action="../risorse/comment.php" class="mt-3">
                            <input type="hidden" name="post_id" value="<?php echo $post['id_post']; ?>">
                            <input type="hidden" name="id_blog" value="<?php echo $id_blog; ?>">
                            <input type="hidden" name="action" value="insert">
                            <div class="form-group">
                                <textarea class="form-control" name="comment" placeholder="Inserisci il tuo commento"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Commenta</button>
                        </form>

                        <!-- Gestione Mi Piace -->
                        <div class="mt-3">
                            <?php
                            $totalLikes = isset($likeCounts[$post['id_post']]) ? $likeCounts[$post['id_post']] : 0;
                            $likeAction = 'like';
                            if ($_SESSION['loggedin'] === true) {
                                include '../configurazione/conn.php';

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
                            <!-- Form per Mi Piace -->
                            <form class="like-form">
                                <input type="hidden" class="post-id" value="<?php echo $post['id_post']; ?>">
                                <input type="hidden" class="id-blog" value="<?php echo $id_blog; ?>">
                                <button type="button" class="btn btn-success like-btn" data-action="<?php echo $likeAction; ?>">
                                    Mi Piace (<span class="like-count"><?php echo $totalLikes; ?></span>)
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.edit-comment-btn').click(function() {
                var commentId = $(this).data('comment-id');
                $(this).closest('.card-body').find('.comment-text').hide();
                $(this).closest('.card-body').find('.edit-comment-form').removeClass('d-none');
            });

            $('.cancel-edit-btn').click(function() {
                $(this).closest('.edit-comment-form').addClass('d-none');
                $(this).closest('.card-body').find('.comment-text').show();
            });

            $('.like-btn').click(function() {
                var postId = $(this).closest('.like-form').find('.post-id').val();
                var blogId = $(this).closest('.like-form').find('.id-blog').val();
                var action = $(this).data('action');

                $.ajax({
                    url: '../risorse/likes.php',
                    type: 'POST',
                    data: {
                        post_id: postId,
                        id_blog: blogId,
                        action: action
                    },
                    success: function(response) {
                        // Ricarica la pagina dopo aver aggiornato i Mi Piace
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error('Errore durante l\'invio della richiesta AJAX: ' + error);
                    }
                });
            });
        });
    </script>
</body>
</html>