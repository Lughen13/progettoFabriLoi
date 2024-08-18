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

$userId = $_SESSION['id'];  // ID dell'utente autenticato
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Gestione dell'eliminazione del blog
if ($action == 'delete_blog') {
    $id_blog = $_GET['id_blog'];

    // Prima eliminare i post associati al blog
    $deletePostsQuery = "DELETE FROM post WHERE id_blog = ?";
    $stmt = $conn->prepare($deletePostsQuery);
    $stmt->bind_param("i", $id_blog);
    $stmt->execute();
    $stmt->close();

    // Ora eliminare il blog
    $deleteBlogQuery = "DELETE FROM blog WHERE id_blog = ? AND id_proprietario = ?";
    $stmt = $conn->prepare($deleteBlogQuery);
    $stmt->bind_param("ii", $id_blog, $userId);
    if ($stmt->execute()) {
        // Eliminazione del blog eseguita con successo
        header("Location: ../pubblico/my_profile.php");  
        exit();
    } else {
        echo "Errore durante l'eliminazione del blog: " . $stmt->error;
    }
    $stmt->close();
}

// Gestione dell'eliminazione del post
if ($action == 'delete_post') {
    $id_post = $_GET['id_post'];

    // Recupera l'ID del blog a cui appartiene il post
    $getBlogIdQuery = "SELECT id_blog FROM post WHERE id_post = ?";
    $stmt = $conn->prepare($getBlogIdQuery);
    $stmt->bind_param("i", $id_post);
    $stmt->execute();
    $stmt->bind_result($id_blog);
    $stmt->fetch();
    $stmt->close();

    if ($id_blog) {
        $deletePostQuery = "DELETE FROM post WHERE id_post = ? AND id_blog IN (SELECT id_blog FROM blog WHERE id_proprietario = ?)";
        $stmt = $conn->prepare($deletePostQuery);
        $stmt->bind_param("ii", $id_post, $userId);
        if ($stmt->execute()) {
            // Eliminazione del post eseguita con successo
            header("Location: ../pubblico/my_blog.php?id_blog=" . $id_blog);  // Reindirizza alla pagina del blog con id_blog
            exit();
        } else {
            echo "Errore durante l'eliminazione del post: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Errore: Post non trovato o accesso non autorizzato.";
    }
}

// Eliminazione del commento
if ($action == 'delete_comment') {
    $id_comm = $_GET['id_comm'];

    // Recupero dell'ID del blog associato al commento
    $getBlogIdQuery = "SELECT p.id_blog FROM commento c 
                       INNER JOIN post p ON c.id_post = p.id_post
                       WHERE c.id_comm = ?";
    $stmt_blog = $conn->prepare($getBlogIdQuery);
    $stmt_blog->bind_param("i", $id_comm);
    $stmt_blog->execute();
    $stmt_blog->bind_result($id_blog);
    $stmt_blog->fetch();
    $stmt_blog->close();

    if (!$id_blog) {
        echo "Errore: l'ID del blog non è stato recuperato correttamente.";
        exit();
    }

    // Eliminazione effettiva del commento
    $deleteCommentQuery = "DELETE FROM commento WHERE id_comm = ?";
    $stmt = $conn->prepare($deleteCommentQuery);
    $stmt->bind_param("i", $id_comm);
    if ($stmt->execute()) {
        // Redirect alla pagina del blog
        header("Location: ../pubblico/my_blog.php?id_blog=$id_blog");
        exit();
    } else {
        echo "Errore durante l'eliminazione del commento: " . $stmt->error;
    }
    $stmt->close();
}

$id_blog = isset($_GET['id_blog']) ? intval($_GET['id_blog']) : 0;

// Recupero dei blog dell'utente
$blogsQuery = "SELECT id_blog, titolo_blog, descrizione, img_logo FROM blog WHERE id_blog = ? AND id_proprietario = ?";
$stmt = $conn->prepare($blogsQuery);
$stmt->bind_param("ii", $id_blog, $userId);
$stmt->execute();
$result = $stmt->get_result();
$blogs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Gestione dell'aggiornamento del post
if ($action == 'edit_post') {
    $postId = $_POST['post_id'];
    $newTitle = $_POST['edit_post_title'];
    $newDescription = $_POST['edit_post_description'];

    // Caricamento delle immagini del post, se fornite
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


    // Aggiornamento dell'immagine del post nel database, se nuove immagini sono state caricate
    if (!empty($newImgFileNames)) {
        // Converti l'array in JSON per salvarlo nel database, se necessario
        $newImgFileNamesJSON = json_encode($newImgFileNames);

        $updatePostImgQuery = "UPDATE post SET img_post = ? WHERE id_post = ?";
        $stmt = $conn->prepare($updatePostImgQuery);
        $stmt->bind_param('si', $newImgFileNamesJSON, $postId);
        if ($stmt->execute()) {
            // Aggiornamento dell'immagine del post nel database eseguito con successo
        } else {
            echo "Errore durante l'aggiornamento dell'immagine del post: " . $stmt->error;
        }
        $stmt->close();
    }

    // Aggiornamento del resto delle informazioni del post
    $updatePostQuery = "UPDATE post SET titolo_post = ?, descrizione_post = ? WHERE id_post = ? AND id_blog IN (SELECT id_blog FROM blog WHERE id_proprietario = ?)";
    $stmt = $conn->prepare($updatePostQuery);
    $stmt->bind_param('sssi', $newTitle, $newDescription, $postId, $userId);
    if ($stmt->execute()) {
        echo "Post aggiornato con successo!";
    } else {
        echo "Errore durante l'aggiornamento del post: " . $stmt->error;
    }
    $stmt->close();
    exit(); // Assicurati di terminare l'esecuzione dopo l'aggiornamento
}


$likeCounts = [];
$queryLikes = "SELECT id_post, COUNT(*) AS like_count FROM likes GROUP BY id_post";
$resultLikes = $conn->query($queryLikes);
while ($row = $resultLikes->fetch_assoc()) {
    $likeCounts[$row['id_post']] = $row['like_count'];
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


        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }        
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
        .form-control {
            border-radius: 20px;
        }
        .profile-picture {
            max-width: 150px;
            max-height: 150px;
            border-radius: 50%;
            border: 5px solid #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .btn-outline-success {
            border-color: #1da1f2;
            color: #1da1f2;
        }
        .btn-outline-success:hover {
            background-color: #1da1f2;
            color: #fff;
        }
        .btn-primary {
            background-color: #1da1f2;
            border-color: #1da1f2;
        }
        .btn-primary:hover {
            background-color: #0e71a1;
            border-color: #0e71a1;
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
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .modal-title {
            color: #1da1f2;
        }
        .form-group label {
            color: #333;
        }
        .list-unstyled {
            padding-left: 0;
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
    
    <div class="my-4">
        <?php if (!empty($blogs)): ?>
            <?php foreach ($blogs as $blog): ?>
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $blog['titolo_blog']; ?></h3>
                        <p class="card-text"><?php echo $blog['descrizione']; ?></p>
                        <?php if (!empty($blog['img_logo'])): ?>
                            <img src="../blog_logo/<?php echo basename($blog['img_logo']); ?>" class="img-fluid mb-2" alt="Logo del blog" width="200">
                            <?php endif; ?>
                            <div>
                                <a href="../risorse/process_update_blog.php?id=<?php echo $blog['id_blog']; ?>" class="btn btn-primary">Modifica Blog</a>
                                <a href="../pubblico/my_blog.php?action=delete_blog&id_blog=<?php echo $blog['id_blog']; ?>" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo blog?')">Elimina Blog</a>
                            </div>
                            <!-- Post del blog -->
                             <?php
                            // recupero informazioni dei post (mi serve soprattutto per il recupero della sottocategoria)
                            $postsQuery = "SELECT p.id_post, p.titolo_post, p.descrizione_post, p.img_post, s.nome_sottocat, u.username
                                        FROM post p
                                        JOIN utente u ON u.id_utente = p.id_autore
                                        JOIN sottocat s ON p.id_sottocat = s.id_sottocat
                                        WHERE p.id_blog = ?";
                            $stmt = $conn->prepare($postsQuery);
                            $stmt->bind_param("i", $blog['id_blog']);
                            $stmt = $conn->prepare($postsQuery);
                            $stmt->bind_param("i", $blog['id_blog']);
                            $stmt->execute();
                            $postsResult = $stmt->get_result();
                            $posts = $postsResult->fetch_all(MYSQLI_ASSOC);
                            $stmt->close();
                            ?>
                            
                            <?php if (!empty($posts)): ?>
                                <div class="posts-container">
                                    <!-- <h4 class="mt-3">I post di questo blog:</h4> -->
                                    <?php foreach ($posts as $post): ?>
                                        <div class="card mb-3">
                                            <div class="card-body">
                                            <h4 class="card-title"><?php echo htmlspecialchars($post['titolo_post']); ?></h4>
                                            <h6 class="card-text">Autore: <?php echo htmlspecialchars($post['username']) ?> </h6>
                                            <h6 class="card-text">Sottocategoria: <?php echo htmlspecialchars($post['nome_sottocat']); ?></h6>
                                            <p class="card-text"><?php echo htmlspecialchars($post['descrizione_post']); ?></p>
                                            
                                            <?php
                                            // Decodifica le immagini dal formato JSON
                                            $images = json_decode($post['img_post'], true);
                                            if (is_array($images) && count($images) > 0): 
                                            ?>
                                                <div class="post-images">
                                                    <?php foreach ($images as $image): ?>
                                                        <img src="../photo_post/<?php echo htmlspecialchars($image); ?>" class="img-fluid mb-2" alt="Immagine del Post" width="200">
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                            <div>
                                                <button class="btn btn-primary mr-2" onclick="showEditPostModal(<?php echo $post['id_post']; ?>)">Modifica Post</button>
                                                <a href="../pubblico/my_blog.php?action=delete_post&id_post=<?php echo $post['id_post']; ?>" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo post?')">Elimina Post</a>

                                            </div>

                                            <div class="mt-3">
                                            <form class="like-form">
                                                <input type="hidden" class="post-id" value="<?php echo $post['id_post']; ?>">
                                                <input type="hidden" class="id-blog" value="<?php echo $id_blog; ?>">
                                                <?php
                                                $likeAction = 'like'; // Default to 'like' if user has not liked the post yet
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

                                            <!-- Recupero dei commenti per questo post -->
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
                                                                    <img src="../uploads/<?php echo htmlspecialchars($comment['img_profilo']); ?>" class="rounded-circle mr-2" width="40" height="40" alt="Immagine profilo">
                                                                <?php endif; ?>
                                                                <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                                                <span class="ml-auto text-muted"><?php echo htmlspecialchars($comment['data_comm']); ?></span>
                                                                <!-- Aggiungi un'icona per eliminare il commento -->
                                                                <a href="../pubblico/my_blog.php?action=delete_comment&id_comm=<?php echo $comment['id_comm']; ?>" class="ml-2 text-danger" onclick="return confirm('Sei sicuro di voler eliminare questo commento?')">
                                                                    <i class="fas fa-times"></i>
                                                                </a>
                                                            </div>
                                                            <div class="comment-content mt-2">
                                                                <?php echo htmlspecialchars($comment['contenuto']); ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <p class="mt-3">Nessun commento disponibile.</p>
                                                <?php endif; ?>

                                                <!-- Modale per la modifica del post -->
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
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="mt-3">Non ci sono post per questo blog.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Non hai ancora creato blog.</p>
            <?php endif; ?>
        </div>

        <div class="my-4">
        <a href="../pubblico/create_post.php" class="btn btn-primary">Crea Post</a>
        </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function showEditBlogModal(blogId) {
        $('#editBlogModal_' + blogId).modal('show');
    }
    
    function editBlog(blogId) {
        var formData = new FormData($('#edit_blog_form_' + blogId)[0]);
        
        $.ajax({
            url: '../pubblico/my_blog.php?action=edit_blog',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                alert('Blog aggiornato con successo!');
                $('#editBlogModal_' + blogId).modal('hide');
                location.reload();
            },
            error: function() {
                alert('Errore durante l\'aggiornamento del blog.');
            }
        });
    }
    
    function showEditPostModal(postId) {
        $('#editPostModal_' + postId).modal('show');
    }

    function editPost(postId) {
        var formData = new FormData($('#edit_post_form_' + postId)[0]);

        $.ajax({
            url: '../pubblico/my_blog.php?action=edit_post',
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

    $(document).ready(function() {
    // Funzione per aggiornare il testo del pulsante Mi Piace
    function updateLikeButton(button, action, likeCount) {
        if (action === 'like') {
            button.data('action', 'unlike');
            button.text('Togli Mi Piace (' + likeCount + ')');
        } else {
            button.data('action', 'like');
            button.text('Mi Piace (' + likeCount + ')');
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



});

</script>
</body>
</html>