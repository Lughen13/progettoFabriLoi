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

// Recupera i post con: sottocategoria, autore,commenti e mi piace
$queryPosts = "SELECT p.id_post, p.titolo_post, p.descrizione_post, p.img_post, s.nome_sottocat, u.username,
                     COUNT(l.id_like) AS total_likes
              FROM post p
              JOIN sottocat s ON p.id_sottocat = s.id_sottocat
              JOIN utente u ON u.id_utente = p.id_autore
              LEFT JOIN likes l ON p.id_post = l.id_post
              WHERE p.id_blog = ?
              GROUP BY p.id_post";


$stmt = $conn->prepare($queryPosts);
$stmt->bind_param("i", $id_blog);
$stmt->execute();
$resultPosts = $stmt->get_result();
$stmt->close();

// Preparazione per la visualizzazione dei commenti
$queryComments = "SELECT c.id_comm, c.data_comm, c.contenuto, u.username, p.id_post, u.img_profilo
                  FROM commento c
                  INNER JOIN utente u ON c.id_utente = u.id_utente
                  INNER JOIN post p ON c.id_post = p.id_post
                  WHERE p.id_blog = ?
                  ORDER BY c.data_comm ASC";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> 

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
        .card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-title {
            color: #1da1f2;
        }
        .card-text {
            color: #333;
        }
        .comment-item {
            background-color: #f0f0f0;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .comment-item strong {
            color: #1da1f2;
        }
        #notificationDropdown {
            position: relative;
        }
        #notificationCount {
            position: absolute;
            top: 0;
            right: 0;
            transform: translate(50%, -50%);
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }
        .dropdown-menu {
            width: 400px; /* Larghezza del dropdown delle notifiche */
            padding: 0;
            border: 2px solid #ddd; /* Bordo laterale */
            border-radius: 10px; /* Angoli arrotondati */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0); /* Ombra per il dropdown */
        }

        #notificationList {
            max-height: 300px; /* Altezza massima del contenitore delle notifiche */
            overflow-y: auto;
            padding: 10px;
        }
        .error-msg {
            color: red;
            margin-bottom: 15px;
            font-weight: bold;
        }
        


        
        .dropdown-item {
            padding: 10px 10px;
            text-decoration: none;
            pointer-events: none;
            border-top: 1px solid #ddd; /* Bordo superiore */
        }
        .category-container {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            justify-items: center;
            align-items: center;
        }
        .category-card {
            flex: 1 1 auto;
            margin: 5px;
            text-align: center;
        }
        .form-area {
            width: 100%;
            height: 100px; 
            resize: none; 
            border: 1px solid #ccc; 
        }

    </style>
</head>
<body>

<div class="container mt-4">
<h1>ToteBlog</h1>
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
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
            
            <div class="d-flex align-items-right">
                <form class="form-inline my-2 my-lg-0" action="search.php" method="GET">
                    <input class="form-control mr-sm-2" type="text" name="query" placeholder="Cerca blog o post">
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Cerca</button>
                </form>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell"></i> 
                            <!-- <span class="badge badge-danger" id="notificationCount">3</span> Numero notifiche -->
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown">
                            <h6 class="dropdown-header">Notifiche recenti</h6>
                            <div id="notificationList">
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
        
    <div class="container mt-5">
        <h1>Questo è il blog: <?php echo htmlspecialchars(isset($blog['titolo_blog']) ? $blog['titolo_blog'] : ''); ?></h1>
        <p><?php echo htmlspecialchars(isset($blog['descrizione']) ? $blog['descrizione'] : ''); ?></p>
        <h6>Follower: <?php echo $follow_count; ?></h6>
        <div class="form-group">
            
        <!-- Bottone per stampare il blog (visualizzato solo per utenti premium) -->
            <?php if ($_SESSION['premium'] == 1) : ?>
                <button class="btn btn-primary mb-3" onclick="window.print();">Stampa Blog</button>
            <?php endif; ?>

            <!-- Form per seguire o smettere di seguire il blog -->
            <?php if ($isFollowing): ?>
                <form method="post" action="../risorse/follow.php" class="d-inline">
                    <input type="hidden" name="blog_id" value="<?php echo $id_blog; ?>">
                    <input type="hidden" name="action" value="unfollow">
                    <button type="submit" class="btn btn-primary mb-3">Non Seguire più</button>
                </form>
            <?php else: ?>
                <form method="post" action="../risorse/follow.php" class="d-inline">
                    <input type="hidden" name="blog_id" value="<?php echo $id_blog; ?>">
                    <input type="hidden" name="action" value="follow">
                    <button type="submit" class="btn btn-primary mb-3">Segui questo Blog</button>
                </form>
            <?php endif; ?>
        </div>
        <img src="../blog_logo/<?php echo htmlspecialchars($blog['img_logo']); ?>" alt="Logo del blog <?php echo htmlspecialchars($blog['titolo_blog']); ?>" width="300">


            
            <!-- Visualizzazione dei post -->
            <?php if ($resultPosts->num_rows > 0): ?>
                <!-- <h2 class="mt-5">Post:</h2> -->
                <?php while ($post = $resultPosts->fetch_assoc()): ?>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h4 class="card-title"><?php echo htmlspecialchars($post['titolo_post']); ?></h4>
                            <h6 class="card-text">Autore: <?php echo htmlspecialchars($post['username']) ?> </h6>
                            <h6 class="card-text">Sottocategoria: <?php echo htmlspecialchars($post['nome_sottocat']); ?></h6>
                            <p class="card-text"><?php echo htmlspecialchars($post['descrizione_post']); ?></p>

                            <!-- Visualizzazione delle immagini del post -->
                            <?php 
                            $images = json_decode($post['img_post'], true);
                            if (!empty($images)): ?>
                                <div class="post-images">
                                    <?php foreach ($images as $image): ?>
                                        <img src="../photo_post/<?php echo htmlspecialchars($image); ?>" class="img-thumbnail mr-2 mb-2" alt="Immagine del post <?php echo htmlspecialchars($post['titolo_post']); ?> il cui autore è <?php echo htmlspecialchars($post['username']); ?>" width="300">                                    
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Visualizzazione dei commenti -->
                            <?php if (isset($comments[$post['id_post']])) : ?>
                                <div class="comments-container mt-4">
                                    <?php foreach ($comments[$post['id_post']] as $comment) : ?>
                                        <div class="comment-item mb-3 p-3 rounded">
                                            <div class="comment-header d-flex align-items-center">
                                                <?php if (!empty($comment['img_profilo'])): ?>
                                                    <img src="../uploads/<?php echo htmlspecialchars($comment['img_profilo']); ?>" class="rounded-circle mr-2" width="40" height="40" alt="Immagine profilo di <?php echo htmlspecialchars($comment['username']); ?> che ha commentato il post: <?php echo htmlspecialchars($post['titolo_post']); ?>">

                                                <?php endif; ?>
                                                <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                                <span class="ml-auto text-muted"><?php echo htmlspecialchars($comment['data_comm']); ?></span>

                                                <?php if ($comment['username'] == $_SESSION['username']) : ?>
                                                <div class="comment-actions mt-2">
                                                    <!-- Pulsante Modifica -->
                                                    <button class="btn btn-sm edit-comment-btn" data-comment-id="<?php echo $comment['id_comm']; ?>" style="border: none; background: none;">
                                                        <i class="fas fa-pen" style="color: red;"  alt="Tasto per modificare il commento" ></i> 
                                                    </button>
                                                    <!-- Pulsante Elimina -->
                                                    <form method="post" action="../risorse/comment.php" class="d-inline">
                                                        <input type="hidden" name="id_comm" value="<?php echo $comment['id_comm']; ?>">
                                                        <input type="hidden" name="id_blog" value="<?php echo $id_blog; ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-sm" style="border: none; background: none;"onclick="return confirm('Sei sicuro di voler eliminare questo commento?')">
                                                            <i class="fas fa-times" style="color: red;" alt="Tasto per eliminare il commento"></i> 
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                            </div>
                                            
                                            <div class="comment-content mt-2">
                                                <?php echo htmlspecialchars($comment['contenuto']); ?>
                                            </div>

                                            
                                            <!-- Modifica commento -->
                                            <div class="edit-comment-form d-none">
                                                <form method="post" action="../risorse/comment.php">
                                                    <input type="hidden" name="id_comm" value="<?php echo $comment['id_comm']; ?>">
                                                    <input type="hidden" name="id_blog" value="<?php echo $id_blog; ?>">
                                                    <input type="hidden" name="action" value="update">
                                                    <div class="form-group">
                                                        <textarea class="form-area" name="comment" id="edit-comment-textarea" rows="3"><?php echo htmlspecialchars($comment['contenuto']); ?></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary btn-sm" id="save-edit-btn" disabled>Salva</button>
                                                    <button type="button" class="btn btn-secondary btn-sm cancel-edit-btn">Annulla</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>


                        <?php
                        if (isset($_SESSION['comment_error'])) {
                            echo '<div class="error-msg">' . htmlspecialchars($_SESSION['comment_error']) . '</div>';
                            unset($_SESSION['comment_error']);
                        }
                        ?>

                        <!-- Form per inserire un commento -->
                        <form method="post" action="../risorse/comment.php" class="mt-3">
                            <input type="hidden" name="post_id" value="<?php echo $post['id_post']; ?>">
                            <input type="hidden" name="id_blog" value="<?php echo $id_blog; ?>">
                            <input type="hidden" name="action" value="insert">
                            <div class="form-group">
                                <textarea class="form-control comment-textarea" name="comment" placeholder="Inserisci il tuo commento"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary comment-submit-btn" disabled>Commenta</button>
                        </form>
                        

                        <!-- Gestione Mi Piace -->
                        <div class="mt-3">
                            <form class="like-form">
                                <input type="hidden" class="post-id" value="<?php echo $post['id_post']; ?>">
                                <input type="hidden" class="id-blog" value="<?php echo $id_blog; ?>">
                                <input type="hidden" name="action" value="insert">
                                <?php
                                $likeAction = 'like'; 
                                if ($_SESSION['loggedin'] === true) {
                                    include '../configurazione/conn.php';
                                    $likeQuery = "SELECT * FROM likes WHERE id_post = ? AND id_utente = ?";
                                    $stmt = $conn->prepare($likeQuery);
                                    $stmt->bind_param("ii", $post['id_post'], $userId);
                                    $stmt->execute();
                                    $likeResult = $stmt->get_result();
                                    $hasLiked = $likeResult->num_rows > 0;
                                    $stmt->close();
                                }
                                ?>
                                
                                <button type="button" class="btn btn-success like-btn" data-action="<?php echo $hasLiked ? 'unlike' : 'like'; ?>">
                                    <?php echo $hasLiked ? 'Togli Mi Piace' : 'Mi Piace'; ?>
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
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
$(document).ready(function() {
    // Funzione per aggiornare il testo del pulsante Mi Piace
    function updateLikeButton(button, action, likeCount) {
        if (action === 'like') {
            button.data('action', 'unlike');
            button.text('Togli Mi Piace');
        } else {
            button.data('action', 'like');
            button.text('Mi Piace');
        }
    }

    // Gestione del clic sul pulsante Mi Piace
    $('.like-btn').click(function() {
        var button = $(this);
        var postId = button.closest('.like-form').find('.post-id').val();
        var action = button.data('action');

        $.ajax({
            url: '../risorse/likes.php',
            type: 'POST',
            data: {
                post_id: postId,
                action: action
            },
            success: function(likeCount) {
                // Aggiorna il testo del pulsante e il conteggio dei Mi Piace dinamicamente
                updateLikeButton(button, action, likeCount);
            },
            error: function(xhr, status, error) {
                console.error('Errore durante l\'invio della richiesta AJAX: ' + error);
            }
        });
    });

    $('.comment-textarea').on('input', function() {
        var form = $(this).closest('form');
        var comment = $(this).val().trim();
        var submitButton = form.find('.comment-submit-btn');

        if (comment === '') {
            submitButton.prop('disabled', true);
        } else {
            submitButton.prop('disabled', false);
        }
    });

    $(document).on('click', '.edit-comment-btn', function() {
        var commentId = $(this).data('comment-id');
        var commentItem = $(this).closest('.comment-item');
        var editForm = commentItem.find('.edit-comment-form');
        var commentContent = commentItem.find('.comment-content');

        // Nascondi il contenuto attuale del commento e mostra il modulo di modifica
        commentContent.addClass('d-none');
        editForm.removeClass('d-none');
    });

    $(document).on('click', '.cancel-edit-btn', function() {
        var commentItem = $(this).closest('.comment-item');
        var editForm = commentItem.find('.edit-comment-form');
        var commentContent = commentItem.find('.comment-content');

        // Mostra il contenuto attuale del commento e nascondi il modulo di modifica
        commentContent.removeClass('d-none');
        editForm.addClass('d-none');
    });

    $(document).on('input', '.edit-comment-form textarea', function() {
        var saveButton = $(this).closest('.edit-comment-form').find('#save-edit-btn');
        if ($(this).val().trim() === '') {
            saveButton.prop('disabled', true);
        } else {
            saveButton.prop('disabled', false);
        }
    });

    $('.comment-textarea').each(function() {
        var form = $(this).closest('form');
        var comment = $(this).val().trim();
        var submitButton = form.find('.comment-submit-btn');

        if (comment === '') {
            submitButton.prop('disabled', true);
        } else {
            submitButton.prop('disabled', false);
        }
    });
});
$(document).ready(function() {
    function fetchNotifications() {
        $.ajax({
            url: '../risorse/get_notifications.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                var notificationList = $('#notificationList');
                notificationList.empty();

                if (data.length === 0) {
                    notificationList.append('<a class="dropdown-item">Non ci sono notifiche.</a>');
                } else {
                    data.forEach(function(notification) {
                        var listItem = '<a class="dropdown-item">';
                        listItem += '<strong>' + notification.sender_username + '</strong> ';
                        if (notification.tipo === 'comment') {
                            listItem += 'ha commentato il tuo post: ';
                            listItem += '<a href="my_post.php?id_post=' + notification.contenuto_id + '">' + notification.contenuto_titolo + '</a>';
                        } else if (notification.tipo === 'like') {
                            listItem += 'ha messo mi piace al tuo post: ';
                            listItem += '<a href="my_post.php?id_post=' +  notification.id_blog + '&id_post=' + notification.contenuto_id + '">' + notification.contenuto_titolo + '</a>';
                        } else if (notification.tipo === 'follow') {
                            listItem += 'ha iniziato a seguirti nel blog: ';
                            listItem += '<a href="my_blog.php?id_blog=' + notification.contenuto_id + '">' + notification.contenuto_titolo + '</a>';
                        } else {
                            listItem += 'ha eseguito un\'azione.';
                        }
                        listItem += '<br><small>' + notification.data + '</small>';
                        listItem += '</a>';
                        notificationList.append(listItem);
                    });
                }
            },
            error: function() {
                $('#notificationList').append('<a class="dropdown-item text-danger">Errore nel caricamento delle notifiche.</a>');
            }
        });
    }

    // Carica le notifiche all'apertura del dropdown
    $('#notificationDropdown').on('click', function() {
        fetchNotifications();
    });
});

</script>


</body>
</html>