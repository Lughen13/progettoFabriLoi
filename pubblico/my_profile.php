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

        <!-- Sezione Immagine Profilo -->
        <div class="text-center">
            <form method="post" enctype="multipart/form-data">
                <label for="img_profilo">Immagine del profilo:</label>
                <input type="file" name="img_profilo" id="img_profilo" class="form-control-file">
                <button type="submit" class="btn btn-primary mt-2">Carica immagine</button>
            </form>
            <img src="../uploads/<?php echo $user['img_profilo']; ?>" class="profile-picture mt-3" alt="Immagine del profilo">
        </div>

       <!-- Sezione Informazioni Personali e Bio -->
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

<!-- Sezione I Miei Blog -->
<div class="mt-4">
    <h2>I Miei Blog</h2>
    <div class="row">
        <?php if (!empty($blogs)): ?>
            <?php foreach ($blogs as $blog): ?>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <?php if (!empty($blog['img_logo'])): ?>
                            <img src="../blog_logo/<?php echo basename($blog['img_logo']); ?>" class="card-img-top" alt="Logo del blog">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $blog['titolo_blog']; ?></h5>
                            <p class="card-text"><?php echo $blog['descrizione']; ?></p>
                            <a href="../pubblico/my_profile.php?action=delete_blog&id_blog=<?php echo $blog['id_blog']; ?>" class="btn btn-danger" onclick="return confirm('Sei sicuro di voler eliminare questo blog?')">Elimina Blog</a>
                        </div>
                        <ul class="list-group list-group-flush">
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
                                <li class="list-group-item">
                                    <h5>Post:</h5>
                                    <ul class="list-unstyled">
                                        <?php foreach ($posts as $post): ?>
                                            <li class="media">
                                                <img src="../photo_post/<?php echo $post['img_post']; ?>" class="mr-3" alt="Immagine del Post" style="width: 100px;">
                                                <div class="media-body">
                                                    <h6 class="mt-0 mb-1"><?php echo $post['titolo_post']; ?></h6>
                                                    <p><?php echo $post['descrizione_post']; ?></p>
                                                    <a href="../pubblico/my_profile.php?action=delete_post&id_post=<?php echo $post['id_post']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Sei sicuro di voler eliminare questo post?')">Elimina Post</a>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li class="list-group-item">Nessun post disponibile.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col">
                <p>Non hai ancora creato alcun blog.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Link per creare un nuovo blog o post -->
<div class="mt-4">
    <a href="../pubblico/create_blog.php" class="btn btn-primary">Crea Blog</a>
    <a href="../pubblico/create_post.php" class="btn btn-success">Crea Post</a>
</div>

<!-- JavaScript e librerie Bootstrap -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>