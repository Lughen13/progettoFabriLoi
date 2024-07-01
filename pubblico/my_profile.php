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

// Gestione della modifica del post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_post'])) {
    $id_post = $_POST['id_post'];
    $titolo_post = $_POST['titolo_post'];
    $descrizione_post = $_POST['descrizione_post'];

    $updatePostQuery = "UPDATE post SET titolo_post = ?, descrizione_post = ? WHERE id_post = ? AND id_blog IN (SELECT id_blog FROM blog WHERE id_proprietario = ?)";
    $stmt = $conn->prepare($updatePostQuery);
    $stmt->bind_param('ssii', $titolo_post, $descrizione_post, $id_post, $userId);
    if ($stmt->execute()) {
        // Aggiornamento del post eseguito con successo
        header("Location: ../pubblico/my_profile.php");
        exit();
    } else {
        echo "Errore durante l'aggiornamento del post: " . $stmt->error;
    }
    $stmt->close();
}

// Recupero dei dettagli del blog per la modifica
$blogEdit = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit_blog' && isset($_GET['id_blog'])) {
    $id_blog = $_GET['id_blog'];
    $blogQuery = "SELECT titolo_blog, descrizione FROM blog WHERE id_blog = ? AND id_proprietario = ?";
    $stmt = $conn->prepare($blogQuery);
    $stmt->bind_param('ii', $id_blog, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $blogEdit = $result->fetch_assoc();
    $stmt->close();
}

// Recupero dei dettagli del post per la modifica
$postEdit = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit_post' && isset($_GET['id_post'])) {
    $id_post = $_GET['id_post'];
    $postQuery = "SELECT titolo_post, descrizione_post FROM post WHERE id_post = ? AND id_blog IN (SELECT id_blog FROM blog WHERE id_proprietario = ?)";
    $stmt = $conn->prepare($postQuery);
    $stmt->bind_param('ii', $id_post, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $postEdit = $result->fetch_assoc();
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il Mio Profilo</title>
     <!-- Stili Bootstrap -->
     <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Stili personalizzati -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .info_personali {
            text-align: center;
        }
        .profile-picture {
            max-width: 200px;
            max-height: 200px;
            border-radius: 50%;
            margin-bottom: 20px;
        }
        .blog-logo {
            max-width: 75px;
            max-height: 75px;
        }
        .blog-section {
            background-color: #fff;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
        }
        .post-item {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            font-weight: bold;
        }
        .form-group input[type="file"] {
            margin-top: 10px;
        }
    </style>

</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Il Mio Profilo</h1>

        <!-- GESTIONE IMMAGINE DI PROFILO-->
        <div class="text-center">
            <form method="post" enctype="multipart/form-data">
                <label for="img_profilo">Immagine del profilo:</label>
                <input type="file" name="img_profilo" id="img_profilo" class="form-control-file">
                <button type="submit" class="btn btn-primary mt-2">Carica immagine</button>
            </form>
            <img src="../uploads/<?php echo $user['img_profilo']; ?>" class="profile-picture mt-3" alt="Immagine del profilo">
        </div>

        <div class="mt-4">
            <h2>Informazioni Personali</h2>
            <div class="row">
                <div class="col-md-6 offset-md-3">
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
    <div>

    <?php if ($blogEdit): ?>
            <h2>Modifica Blog</h2>
            <form method="post">
                <input type="hidden" name="id_blog" value="<?php echo $id_blog; ?>">
                <div class="form-group">
                    <label for="titolo_blog">Titolo del Blog:</label>
                    <input type="text" name="titolo_blog" id="titolo_blog" class="form-control" value="<?php echo $blogEdit['titolo_blog']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="descrizione">Descrizione:</label>
                    <textarea name="descrizione" id="descrizione" class="form-control" required><?php echo $blogEdit['descrizione']; ?></textarea>
                </div>
                <button type="submit" name="update_blog" class="btn btn-primary">Aggiorna Blog</button>
            </form>
        <?php endif; ?>

        <?php if ($postEdit): ?>
            <h2>Modifica Post</h2>
            <form method="post">
                <input type="hidden" name="id_post" value="<?php echo $id_post; ?>">
                <div class="form-group">
                    <label for="titolo_post">Titolo del Post:</label>
                    <input type="text" name="titolo_post" id="titolo_post" class="form-control" value="<?php echo $postEdit['titolo_post']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="descrizione_post">Descrizione:</label>
                    <textarea name="descrizione_post" id="descrizione_post" class="form-control" required><?php echo $postEdit['descrizione_post']; ?></textarea>
                </div>
                <button type="submit" name="update_post" class="btn btn-primary">Aggiorna Post</button>
            </form>
        <?php endif; ?>


        <h2>I Miei Blog</h2>
        <?php if (!empty($blogs)): ?>
            <?php foreach ($blogs as $blog): ?>
                <div class="blog-section">
                    <h3><?php echo $blog['titolo_blog']; ?></h3>
                    <p><?php echo $blog['descrizione']; ?></p>
                    <?php if (!empty($blog['img_logo'])): ?>
                        <img src="../blog_logo/<?php echo basename($blog['img_logo']); ?>" class="blog-logo" alt="Logo del blog">
                    <?php endif; ?>
                    
                    <div class="blog-actions">
                        <a href="../pubblico/my_profile.php?action=delete_blog&id_blog=<?php echo $blog['id_blog']; ?>" onclick="return confirm('Sei sicuro di voler eliminare questo blog?')">Elimina Blog</a>
                        <a href="../pubblico/my_profile.php?action=edit_blog&id_blog=<?php echo $blog['id_blog']; ?>">Modifica Blog</a>

                    </div>
                    
                    <?php
                    // Recupera i post associati a questo blog
                    $postsQuery = "SELECT id_post, titolo_post, descrizione_post, img_post FROM post WHERE id_blog = ?";
                    $stmt = $conn->prepare($postsQuery);
                    $stmt->bind_param("i", $blog['id_blog']);
                    $stmt->execute();
                    $postsResult = $stmt->get_result();
                    $posts = $postsResult->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();
                    ?>
                    
                    <?php if (!empty($posts)): ?>
                        <h4>Post:</h4>
                        <ul>
                            <?php foreach ($posts as $post): ?>
                                <li class="post-item">
                                    <h5><?php echo $post['titolo_post']; ?></h5>
                                    <p><?php echo $post['descrizione_post']; ?></p>
                                    <img src="../photo_post/<?php echo $post['img_post']; ?>" alt="Immagine del Post" width="100">
                                    <div class="post-actions">
                                        <a href="../pubblico/my_profile.php?action=delete_post&id_post=<?php echo $post['id_post']; ?>" onclick="return confirm('Sei sicuro di voler eliminare questo post?')">Elimina Post</a>
                                        <a href="../risorse/edit_blog_post.php?id_post=<?php echo $post['id_post']; ?>">Modifica Post</a>
                                    </div>


                                    <!-- Recupero dei commenti per questo post -->
                                    <?php
                                        $commentsQuery = "
                                        SELECT c.id_comm, c.contenuto, c.data_comm, u.username
                                        FROM commento c
                                        INNER JOIN utente u ON c.id_utente = u.id_utente
                                        WHERE c.id_post = ?
                                        ";
                                                    
                                        $stmt_comments = $conn->prepare($commentsQuery);
                                        $stmt_comments->bind_param("i", $post['id_post']);
                                        $stmt_comments->execute();
                                        $commentsResult = $stmt_comments->get_result();
                                        $comments = $commentsResult->fetch_all(MYSQLI_ASSOC);
                                        $stmt_comments->close();
                                    ?>

                                    <?php if (!empty($comments)): ?>
                                        <h5>Commenti:</h5>
                                        <ul class="list-unstyled">
                                            <?php foreach ($comments as $comment): ?>
                                                <li>
                                                    <strong><?php echo $comment['username']; ?>  <small class="text-muted"><?php echo $comment['data_comm']; ?></small>:</strong>
                                                    <?php echo $comment['contenuto']; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p>Nessun commento disponibile.</p>
                                    <?php endif; ?>

                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Non hai ancora creato alcun blog.</p>
        <?php endif; ?>
    </div>

    <div>
        <a href="../pubblico/create_blog.php">Crea Blog</a>
        <a href="../pubblico/create_post.php">Crea Post</a>
    </div>
</body>
</html>