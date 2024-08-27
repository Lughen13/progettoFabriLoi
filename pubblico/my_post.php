<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
session_start();
require_once '../configurazione/conn.php';

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../pubblico/login.php");
    exit();
}

$userId = $_SESSION['id'];  
$action = isset($_GET['action']) ? $_GET['action'] : '';

$postId = isset($_GET['id_post']) ? (int) $_GET['id_post'] : 0;


$queryPost = "SELECT p.*, s.nome_sottocat, u.username
              FROM post p
              JOIN utente u ON p.id_autore = u.id_utente
              LEFT JOIN sottocat s ON p.id_sottocat = s.id_sottocat
              WHERE p.id_post = ?";
$stmtPost = $conn->prepare($queryPost);
$stmtPost->bind_param("i", $postId);
$stmtPost->execute();
$resultPost = $stmtPost->get_result();
$post = $resultPost->fetch_assoc();

  // funzione per l'eliminazione del post
if ($action == 'delete_post') {
    $id_post = $_GET['id_post'];
      // elimina le notifiche associate al post
      $deleteNotifiche = "DELETE FROM notifiche WHERE contenuto_id = ?";
      $stmt = $conn->prepare($deleteNotifiche);
      $stmt->bind_param("i", $postId);
      $stmt->execute();
      $stmt->close();

      $deletePostQuery = "DELETE FROM post WHERE id_post = ?";
      $stmt = $conn->prepare($deletePostQuery);
      $stmt->bind_param("i", $postId);
      if ($stmt->execute()) {
          header("Location: ../pubblico/home.php");
          exit();
      } else {
          echo "Errore durante l'eliminazione del post: " . $stmt->error;
      }
      $stmt->close();

}
// funzione per l'aggiornamento del post
if ($action == 'edit_post') {
    $postId = $_POST['post_id'];
    $newTitle = $_POST['edit_post_title'];
    $newDescription = $_POST['edit_post_description'];

    // caricamento delle immagini del post, se fornite
    $newImgFileNames = [];

    if (isset($_FILES['edit_post_img'])) {
        $uploadedFiles = $_FILES['edit_post_img'];

        foreach ($uploadedFiles['name'] as $key => $name) {
            if ($uploadedFiles['size'][$key] > 0 && $uploadedFiles['error'][$key] == 0) {
                $imgFileName = $uploadedFiles['name'][$key];
                $imgTmpName = $uploadedFiles['tmp_name'][$key];
                $imgSize = $uploadedFiles['size'][$key];
                $imgError = $uploadedFiles['error'][$key];

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $imgExtension = strtolower(pathinfo($imgFileName, PATHINFO_EXTENSION));

                if (in_array($imgExtension, $allowedExtensions)) {
                    $newImgFileName = 'post_' . uniqid('', true) . '.' . $imgExtension;
                    $imgDestination = '../photo_post/' . $newImgFileName;

                    if (move_uploaded_file($imgTmpName, $imgDestination)) {
                        // Aggiungi il nome del file all'array
                        $newImgFileNames[] = $newImgFileName;
                    } else {
                        echo "Errore durante il caricamento dell'immagine del post.";
                    }
                } else {
                    echo "Formato dell'immagine del post non valido.";
                }
            }
        }
    }

    // aggiornamento dell'immagine del post nel database, se nuove immagini sono state caricate
    if (!empty($newImgFileNames)) {
        $newImgFileNamesJSON = json_encode($newImgFileNames);

        $updatePostImgQuery = "UPDATE post SET img_post = ? WHERE id_post = ?";
        $stmt = $conn->prepare($updatePostImgQuery);
        $stmt->bind_param('si', $newImgFileNamesJSON, $postId);
        if ($stmt->execute()) {
        } else {
            echo "Errore durante l'aggiornamento dell'immagine del post: " . $stmt->error;
        }
        $stmt->close();
    }

    // aggiornamento del resto delle informazioni del post
    $updatePostQuery = "UPDATE post SET titolo_post = ?, descrizione_post = ? WHERE id_post = ? AND id_blog IN (SELECT id_blog FROM blog WHERE id_proprietario = ?)";
    $stmt = $conn->prepare($updatePostQuery);
    $stmt->bind_param('sssi', $newTitle, $newDescription, $postId, $userId);
    if ($stmt->execute()) {
        echo "Post aggiornato con successo!";
    } else {
        echo "Errore durante l'aggiornamento del post: " . $stmt->error;
    }
    $stmt->close();
    exit(); 
}

?>


<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToteBlog</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
            width: 400px;
            padding: 0;
            border: 2px solid #ddd; 
            border-radius: 10px; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0); 
        }

        #notificationList {
            max-height: 300px; 
            overflow-y: auto;
            padding: 10px;
        }

        .dropdown-item {
            padding: 10px 10px;
            text-decoration: none;
            pointer-events: none;
            border-top: 1px solid #ddd;
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
        .error-msg {
            color: red;
            margin-bottom: 15px;
            font-weight: bold;
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


    <div class="my-4">
        <div class="posts-container">
            <?php if ($post): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h4 class="card-title"><?php echo htmlspecialchars($post['titolo_post']); ?></h4>
                        <h6 classe="card-text">Autore: <?php echo htmlspecialchars($post['username']) ?> </h6>
                        <h6 class="card-text">Sottocategoria: <?php echo htmlspecialchars($post['nome_sottocat']); ?></h6>
                        <h6 class="card-text">Mi piace: <?php echo htmlspecialchars($post['likes_count']); ?></h6>
                        <p class="card-text"><?php echo htmlspecialchars($post['descrizione_post']); ?></p>
                            
                        <?php
                        // Decodifica le immagini utilizzando json
                        $images = json_decode($post['img_post'], true);
                        if (is_array($images) && count($images) > 0): 
                        ?>   
                        <div class="post-images">
                            <?php foreach ($images as $image): ?>
                                <img src="../photo_post/<?php echo htmlspecialchars($image); ?>" class="img-fluid mb-2" alt="Immagine del Post '<?php echo htmlspecialchars($post['titolo_post']);?>' di: '<?php echo htmlspecialchars($post['username']);?>'" width="400">
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <div>
                            <button class="btn btn-primary mr-2" onclick="showEditPostModal(<?php echo $post['id_post']; ?>)">Modifica Post</button>
                            <button class="btn btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo post?') ? window.location.href='../pubblico/my_post.php?action=delete_post&id_post=<?php echo $post['id_post']; ?>' : false;">Elimina Post</button>
                            </div>  
                        <div class="mt-3">
                            <form class="like-form">
                                <input type="hidden" class="post-id" value="<?php echo $post['id_post']; ?>">
                                <input type="hidden" class="id-blog" value="<?php echo $id_blog; ?>">
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

                        <!-- Recupero dei commenti per il post specifico -->
                        <?php
                        $commentsQuery = "SELECT c.id_comm, c.contenuto, c.data_comm, u.username, u.img_profilo
                        FROM commento c
                        INNER JOIN utente u ON c.id_utente = u.id_utente
                        WHERE c.id_post = ?";
                        $stmt_comments = $conn->prepare($commentsQuery);
                        $stmt_comments->bind_param("i", $post['id_post']);
                        $stmt_comments->execute();
                        $commentsResult = $stmt_comments->get_result();
                        $comments = $commentsResult->fetch_all(MYSQLI_ASSOC);
                        $stmt_comments->close();
                        ?>
                        
                        <?php if (!empty($comments)): ?>
                            <div class="comments-container mt-4">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment-item mb-3 p-3 rounded">
                                        <div class="comment-header d-flex align-items-center">
                                            <?php if (!empty($comment['img_profilo'])): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($comment['img_profilo']); ?>" class="rounded-circle mr-2" width="40" height="40" alt="Immagine profilo di: '<?php echo htmlspecialchars($comment['username']); ?>' che ha commentato il post: '<?php echo htmlspecialchars($post['titolo_post']); ?>'">
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                            <span class="ml-auto text-muted"><?php echo htmlspecialchars($comment['data_comm']); ?></span>
                                                                
                                            <?php if ($comment['username'] == $_SESSION['username']) : ?>
                                                <!-- icona per la modifica -->
                                                <button class="btn btn-sm edit-comment-btn" data-comment-id="<?php echo $comment['id_comm']; ?>" style="border: none; background: none;">
                                                    <i class="fas fa-pen" style="color: red;" alt="Tasto per modificare il commento"></i> 
                                                </button>
                                            <?php endif; ?>

                                            <!-- icona per eliminare il commento -->
                                            <form method="post" action="../risorse/comment.php" class="d-inline">
                                                <input type="hidden" name="id_comm" value="<?php echo $comment['id_comm']; ?>">
                                                <input type="hidden" name="id_blog" value="<?php echo $id_blog; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-sm" style="border: none; background: none;"onclick="return confirm('Sei sicuro di voler eliminare questo commento?')">
                                                    <i class="fas fa-times" style="color: red;" alt="Tasto per eliminare il commento"></i> 
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <div class="comment-content mt-2">
                                            <?php echo htmlspecialchars($comment['contenuto']); ?>
                                        </div>            
                                                            
                                        <!-- modale per la modifica del commento -->
                                        <div class="edit-comment-form d-none">
                                            <form method="post" action="../risorse/comment.php">
                                                <input type="hidden" name="id_comm" value="<?php echo $comment['id_comm']; ?>">
                                                <input type="hidden" name="id_blog" value="<?php echo $id_blog; ?>">
                                                <input type="hidden" name="action" value="update">
                                                <div class="form-group">
                                                    <textarea class="form-control" name="comment" id="edit-comment-textarea" rows="3"><?php echo htmlspecialchars($comment['contenuto']); ?></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm" id="save-edit-btn" disabled>Salva</button>
                                                <button type="button" class="btn btn-secondary btn-sm cancel-edit-btn">Annulla</button>
                                            </form>
                                        </div>

                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                        <p class="mt-3">Nessun commento disponibile.</p>
                        <?php endif; ?>


                        <?php
                        if (isset($_SESSION['comment_error'])) {
                            echo '<div class="error-msg">' . htmlspecialchars($_SESSION['comment_error']) . '</div>';
                            unset($_SESSION['comment_error']);
                        }
                        ?>
                        <!-- form per inserire un commento -->
                        <form method="post" action="../risorse/comment.php" class="mt-3">
                            <input type="hidden" name="post_id" value="<?php echo $post['id_post']; ?>">
                            <input type="hidden" name="id_blog" value="<?php echo $id_blog; ?>">
                            <input type="hidden" name="action" value="insert">
                            <div class="form-group">
                                <textarea class="form-control comment-textarea" name="comment" placeholder="Inserisci il tuo commento"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary comment-submit-btn" disabled>Commenta</button>
                        </form>

                            <!-- modale per la modifica del post -->
                            <div class="modal fade" id="editPostModal_<?php echo $post['id_post']; ?>" tabindex="-1" role="dialog" aria-labelledby="editPostModalLabel_<?php echo $post['id_post']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editPostModalLabel_<?php echo $post['id_post']; ?>">Modifica Post</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="edit_post_form_<?php echo $post['id_post']; ?>" enctype="multipart/form-data">
                                                <input type="hidden" name="post_id" value="<?php echo $post['id_post']; ?>">
                                                <div class="form-group">
                                                    <label for="edit_post_title_<?php echo $post['id_post']; ?>">Nuovo titolo</label>
                                                    <input type="text" name="edit_post_title" id="edit_post_title_<?php echo $post['id_post']; ?>" class="form-control" value="<?php echo htmlspecialchars($post['titolo_post']); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="edit_post_description_<?php echo $post['id_post']; ?>">Nuova descrizione</label>
                                                    <textarea name="edit_post_description" id="edit_post_description_<?php echo $post['id_post']; ?>" class="form-control"><?php echo htmlspecialchars($post['descrizione_post']); ?></textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label for="edit_post_img_<?php echo $post['id_post']; ?>">Nuove immagini</label>
                                                    <input type="file" name="edit_post_img[]" id="edit_post_img_<?php echo $post['id_post']; ?>" class="form-control-file" multiple>
                                                    <input type="file" name="edit_post_img[]" id="edit_post_img_<?php echo $post['id_post']; ?>" class="form-control-file" multiple>
                                                    <input type="file" name="edit_post_img[]" id="edit_post_img_<?php echo $post['id_post']; ?>" class="form-control-file" multiple>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                                            <button type="button" class="btn btn-primary" onclick="editPost(<?php echo $post['id_post']; ?>)">Salva</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>


    function editPost(postId) {
    var formData = new FormData($('#edit_post_form_' + postId)[0]);

        $.ajax({
            url: '../pubblico/my_post.php?action=edit_post',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                alert('Post aggiornato con successo!');
                $('#editPostModal_' + postId).modal('hide');
                location.reload();
            },
            error: function() {
                alert('Errore durante l\'aggiornamento del post.');
            }
        });
    }

    function showEditPostModal(postId) {
    $('#editPostModal_' + postId).modal('show');
    }

   

$(document).ready(function() {
    function updateLikeButton(button, action, likeCount) {
        if (action === 'like') {
            button.data('action', 'unlike');
            button.text('Togli Mi Piace');
        } else {
            button.data('action', 'like');
            button.text('Mi Piace');
        }
    }

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
                updateLikeButton(button, action, likeCount);
            },
            error: function(xhr, status, error) {
                console.error('Errore durante l\'invio della richiesta AJAX: ' + error);
            }
        });
    });
});
$('.delete-post-btn').click(function() {
            var postId = $(this).data('post-id');
            if (confirm('Sei sicuro di voler eliminare questo post?')) {
                window.location.href = '../pubblico/my_post.php?action=delete&id_post=' + postId;
            }
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

        commentContent.addClass('d-none');
        editForm.removeClass('d-none');
    });

    $(document).on('click', '.cancel-edit-btn', function() {
        var commentItem = $(this).closest('.comment-item');
        var editForm = commentItem.find('.edit-comment-form');
        var commentContent = commentItem.find('.comment-content');

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