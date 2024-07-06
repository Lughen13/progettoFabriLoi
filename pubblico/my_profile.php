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
        header("Location: ../pubblico/my_profile.php");  // Reindirizza alla pagina del profilo
        exit();
    } else {
        echo "Errore durante l'eliminazione del blog: " . $stmt->error;
    }
    $stmt->close();
}

// Gestione dell'eliminazione del post
if ($action == 'delete_post') {
    $id_post = $_GET['id_post'];

    $deletePostQuery = "DELETE FROM post WHERE id_post = ? AND id_blog IN (SELECT id_blog FROM blog WHERE id_proprietario = ?)";
    $stmt = $conn->prepare($deletePostQuery);
    $stmt->bind_param("ii", $id_post, $userId);
    if ($stmt->execute()) {
        // Eliminazione del post eseguita con successo
        header("Location: ../pubblico/my_profile.php");  // Reindirizza alla pagina del profilo
        exit();
    } else {
        echo "Errore durante l'eliminazione del post: " . $stmt->error;
    }
    $stmt->close();
}

if ($action == 'delete_comment') {
    $id_comm = $_GET['id_comm'];

    $deleteCommentQuery = "DELETE FROM commento WHERE id_comm = ? AND id_post IN (SELECT id_post FROM post WHERE id_blog IN (SELECT id_blog FROM blog WHERE id_proprietario = ?))";
    $stmt = $conn->prepare($deleteCommentQuery);
    $stmt->bind_param("ii", $id_comm, $userId);
    if ($stmt->execute()) {
        // Eliminazione del commento eseguita con successo
        header("Location: ../pubblico/my_profile.php");  // Reindirizza alla pagina del profilo
        exit();
    } else {
        echo "Errore durante l'eliminazione del commento: " . $stmt->error;
    }
    $stmt->close();
}
// Gestione dell'aggiornamento della bio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bio'])) {
    $newBio = $_POST['bio'];

    $updateBioQuery = "UPDATE utente SET bio = ? WHERE id_utente = ?";
    $stmt = $conn->prepare($updateBioQuery);
    $stmt->bind_param('si', $newBio, $userId);
    if ($stmt->execute()) {
        // Aggiornamento della bio eseguito con successo
        $bioUpdateSuccess = true;
    } else {
        echo "Errore durante l'aggiornamento della bio: " . $stmt->error;
    }
    $stmt->close();
}

// Recupero informazioni utente
$userQuery = "SELECT username, email, nome, cognome, data_nascita, genere, bio, img_profilo, numero_telefono FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Caricamento dell'immagine del profilo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['img_profilo'])) {
    $imgFile = $_FILES['img_profilo'];
    $imgFileName = $imgFile['name'];
    $imgTmpName = $imgFile['tmp_name'];
    $imgSize = $imgFile['size'];
    $imgError = $imgFile['error'];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $imgExtension = strtolower(pathinfo($imgFileName, PATHINFO_EXTENSION));

    if (in_array($imgExtension, $allowedExtensions) && $imgError === 0) {
        $newImgFileName = $user['username'] . '_profilo.' . $imgExtension;
        $imgDestination = '../uploads/' . $newImgFileName;

        if (move_uploaded_file($imgTmpName, $imgDestination)) {
            // Aggiorna l'immagine del profilo nel database
            $updateImgQuery = "UPDATE utente SET img_profilo = ? WHERE id_utente = ?";
            $stmt = $conn->prepare($updateImgQuery);
            $stmt->bind_param('si', $newImgFileName, $userId);
            if ($stmt->execute()) {
                // Aggiornamento dell'immagine del profilo eseguito con successo
                $user['img_profilo'] = $newImgFileName;
            } else {
                echo "Errore durante l'aggiornamento dell'immagine del profilo: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Errore durante il caricamento dell'immagine.";
        }
    } else {
        echo "Formato dell'immagine non valido o errore durante il caricamento.";
    }
}
// Preparazione della query per recuperare i dettagli dello stile scelto dall'utente
$query = "SELECT s.nome AS nome_stile, s.font, s.colore_testo, s.background
          FROM blog b
          INNER JOIN stile s ON b.id_stile = s.id_stile
          WHERE b.id_proprietario = ?";

// Preparazione dello statement
$stmt = $conn->prepare($query);

// Verifica se la preparazione della query è avvenuta con successo
if ($stmt === false) {
    die("Errore nella preparazione della query: " . $conn->error);
}

// Associazione del parametro (ID dell'utente autenticato)
$userId = $_SESSION['id'];
$stmt->bind_param("i", $userId);

// Esecuzione della query
$stmt->execute();

// Ottenimento del risultato della query
$result = $stmt->get_result();

// Verifica se sono stati trovati risultati
if ($result->num_rows > 0) {
    // Estrai il risultato (una sola riga perché si suppone che ci sia un solo stile per utente)
    $row = $result->fetch_assoc();
    $nomeStile = $row['nome_stile'];
    $fontFamily = $row['font'];
    $textColor = $row['colore_testo'];
    $backgroundColor = $row['background'];

    // Puoi utilizzare queste variabili per personalizzare dinamicamente il CSS o qualsiasi altra operazione
    // Ad esempio, puoi utilizzare $fontFamily, $textColor, $backgroundColor per applicare stili CSS dinamici
} else {
    echo "Nessun risultato trovato per lo stile scelto dall'utente.";
    // Gestire il caso in cui non è stato trovato alcuno stile per l'utente
}

// Recupero dei blog dell'utente
$blogsQuery = "SELECT id_blog, titolo_blog, descrizione, img_logo FROM blog WHERE id_proprietario = ?";
$stmt = $conn->prepare($blogsQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$blogs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Gestione della modifica del blog
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_blog'])) {
    $id_blog = $_POST['id_blog'];
    $titolo_blog = $_POST['titolo_blog'];
    $descrizione = $_POST['descrizione'];

    $updateBlogQuery = "UPDATE blog SET titolo_blog = ?, descrizione = ? WHERE id_blog = ? AND id_proprietario = ?";
    $stmt = $conn->prepare($updateBlogQuery);
    $stmt->bind_param('ssii', $titolo_blog, $descrizione, $id_blog, $userId);
    if ($stmt->execute()) {
        // Aggiornamento del blog eseguito con successo
        header("Location: ../pubblico/my_profile.php");
        exit();
    } else {
        echo "Errore durante l'aggiornamento del blog: " . $stmt->error;
    }
    $stmt->close();
}


// Gestione dell'aggiornamento del blog
if ($action == 'edit_blog') {
    $blogId = $_POST['blog_id'];
    $newTitle = $_POST['edit_blog_title'];
    $newDescription = $_POST['edit_blog_description'];

    $updateBlogQuery = "UPDATE blog SET titolo_blog = ?, descrizione = ? WHERE id_blog = ? AND id_proprietario = ?";
    $stmt = $conn->prepare($updateBlogQuery);
    $stmt->bind_param('ssii', $newTitle, $newDescription, $blogId, $userId);
    if ($stmt->execute()) {
        echo "Blog aggiornato con successo!";
    } else {
        echo "Errore durante l'aggiornamento del blog: " . $stmt->error;
    }
    $stmt->close();
    exit(); // Assicurati di terminare l'esecuzione dopo l'aggiornamento
}


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
            font-family: "<?php echo $font; ?>", sans-serif;
            color: <?php echo $colore_testo; ?>;
            background-color: <?php echo $colore_sfondo; ?>;
        }
    .comments-container {
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-top: 20px;
    }

    .comment-item {
        background-color: #f9f9f9;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 8px;
    }

    .comment-item .comment-header {
        font-weight: bold;
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }

    .comment-item .comment-header img {
        border-radius: 50%;
        margin-right: 10px;
    }

    .comment-item .comment-content {
        margin-top: 8px;
    }
</style>
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
        <h1>Il Mio Profilo</h1>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="#">Il Mio Profilo</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link" href="../pubblico/home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pubblico/my_profile.php">Il mio profilo</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pubblico/account_settings.php">Impostazioni profilo</a></li>
                </ul>
                <form class="form-inline my-2 my-lg-0" action="search.php" method="GET">
                    <input class="form-control mr-sm-2" type="text" name="query" placeholder="Cerca blog o post">
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Cerca</button>
                </form>
            </div>
        </nav>

        <div class="info_personali text-center">
            <h2>Informazioni Personali</h2>
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <form method="post" enctype="multipart/form-data">
                        <img src="../uploads/<?php echo $user['img_profilo']; ?>" class="profile-picture mt-3" alt="Immagine del profilo">
                        <input type="file" name="img_profilo" id="img_profilo" class="form-control-file mt-2">
                        <button type="submit" class="btn btn-primary mt-2">Carica immagine</button>
                    </form>
                    <form method="post">
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" id="username" class="form-control" value="<?php echo $user['username']; ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="nome">Nome:</label>
                            <input type="text" id="nome" class="form-control" value="<?php echo $user['nome']; ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="cognome">Cognome:</label>
                            <input type="text" id="cognome" class="form-control" value="<?php echo $user['cognome']; ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="genere">Genere:</label>
                            <input type="text" id="genere" class="form-control" value="<?php echo $user['genere']; ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="numero_telefono">Numero di telefono:</label>
                            <input type="text" id="numero_telefono" class="form-control" value="<?php echo $user['numero_telefono']; ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="bio">Bio:</label>
                            <textarea name="bio" id="bio" class="form-control" rows="3"><?php echo $user['bio']; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" name="update_bio">Aggiorna Bio</button>
                        <?php if (isset($bioUpdateSuccess) && $bioUpdateSuccess): ?>
                            <small class="text-success ml-2">Bio aggiornata con successo.</small>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="my-4">
            <h2>I Miei Blog</h2>
            <?php if (!empty($blogs)): ?>
                <?php foreach ($blogs as $blog): ?>
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title"><?php echo $blog['titolo_blog']; ?></h3>
                            <p class="card-text"><?php echo $blog['descrizione']; ?></p>
                            <?php if (!empty($blog['img_logo'])): ?>
                                <img src="../blog_logo/<?php echo basename($blog['img_logo']); ?>" class="img-fluid mb-2" alt="Logo del blog">
                            <?php endif; ?>
                            <div>
                                <button class="btn btn-primary mr-2" onclick="showEditBlogModal(<?php echo $blog['id_blog']; ?>)">Modifica Blog</button>
                                <a href="../pubblico/my_profile.php?action=delete_blog&id_blog=<?php echo $blog['id_blog']; ?>" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo blog?')">Elimina Blog</a>
                            </div>

                            <!-- Modale per la modifica del blog -->
                            <div class="modal fade" id="editBlogModal_<?php echo $blog['id_blog']; ?>" tabindex="-1" role="dialog" aria-labelledby="editBlogModalLabel_<?php echo $blog['id_blog']; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editBlogModalLabel_<?php echo $blog['id_blog']; ?>">Modifica Blog</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="edit_blog_form_<?php echo $blog['id_blog']; ?>">
                                                <input type="hidden" name="blog_id" value="<?php echo $blog['id_blog']; ?>">
                                                <div class="form-group">
                                                    <label for="edit_blog_title_<?php echo $blog['id_blog'];
                                                    ?>">Nuovo titolo</label>
                                                    <input type="text" name="edit_blog_title" id="edit_blog_title_<?php echo $blog['id_blog']; ?>" class="form-control" value="<?php echo htmlspecialchars($blog['titolo_blog']); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="edit_blog_description_<?php echo $blog['id_blog']; ?>">Nuova descrizione</label>
                                                    <textarea name="edit_blog_description" id="edit_blog_description_<?php echo $blog['id_blog']; ?>" class="form-control"><?php echo htmlspecialchars($blog['descrizione']); ?></textarea>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                                            <button type="button" class="btn btn-primary" onclick="editBlog(<?php echo $blog['id_blog']; ?>)">Salva</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Post del blog -->
                            <?php
                            $postsQuery = "SELECT id_post, titolo_post, descrizione_post, img_post FROM post WHERE id_blog = ?";
                            $stmt = $conn->prepare($postsQuery);
                            $stmt->bind_param("i", $blog['id_blog']);
                            $stmt->execute();
                            $postsResult = $stmt->get_result();
                            $posts = $postsResult->fetch_all(MYSQLI_ASSOC);
                            $stmt->close();
                            ?>

                            <?php if (!empty($posts)): ?>
                                <div class="posts-container">
                                    <h4 class="mt-3">Post:</h4>
                                    <?php foreach ($posts as $post): ?>
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo $post['titolo_post']; ?></h5>
                                                <p class="card-text"><?php echo $post['descrizione_post']; ?></p>
                                                
                                                <?php
                                                // Decodifica le immagini dal formato JSON
                                                $images = json_decode($post['img_post'], true);
                                                if (is_array($images) && count($images) > 0): 
                                                ?>
                                                    <div class="post-images">
                                                        <?php foreach ($images as $image): ?>
                                                            <img src="../photo_post/<?php echo $image; ?>" class="img-fluid mb-2" alt="Immagine del Post">
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <img src="../photo_post/<?php echo $post['img_post']; ?>" class="img-fluid mb-2" alt="Immagine del Post">
                                                <?php endif; ?>

                                                <div>
                                                    <button class="btn btn-primary mr-2" onclick="showEditPostModal(<?php echo $post['id_post']; ?>)">Modifica Post</button>
                                                    <a href="../pubblico/my_profile.php?action=delete_post&id_post=<?php echo $post['id_post']; ?>" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo post?')">Elimina Post</a>
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
                                                                        <img src="../uploads/<?php echo $comment['img_profilo']; ?>" class="rounded-circle mr-2" width="40" height="40" alt="Immagine profilo">
                                                                    <?php endif; ?>
                                                                    <strong><?php echo $comment['username']; ?></strong>
                                                                    <span class="ml-auto text-muted"><?php echo $comment['data_comm']; ?></span>
                                                                    <!-- Aggiungi un'icona per eliminare il commento -->
                                                                    <a href="../pubblico/my_profile.php?action=delete_comment&id_comm=<?php echo $comment['id_comm']; ?>" class="ml-2 text-danger" onclick="return confirm('Sei sicuro di voler eliminare questo commento?')">
                                                                        <i class="fas fa-times"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="comment-content mt-2">
                                                                    <?php echo $comment['contenuto']; ?>
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
            <a href="../pubblico/create_blog.php" class="btn btn-primary mr-2">Crea Blog</a>
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
                url: '../pubblico/my_profile.php?action=edit_blog',
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
            url: '../pubblico/my_profile.php?action=edit_post',
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
</script>
</body>
</html>