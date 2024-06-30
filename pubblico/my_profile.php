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
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
        }
        nav {
            margin-bottom: 20px;
        }
        nav form {
            margin-bottom: 10px;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
        }
        nav ul li {
            display: inline;
            margin-right: 10px;
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
            margin-bottom: 30px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
            text-align: left;
        }
        .blog-section h3 {
            margin-bottom: 10px;
        }
        .blog-section p {
            margin-bottom: 5px;
        }
        .blog-section .blog-actions {
            margin-top: 10px;
        }
        .blog-section .blog-actions a {
            margin-right: 10px;
            color: #ff0000;
            text-decoration: none;
        }
        .blog-section .blog-actions a:hover {
            text-decoration: underline;
        }
        .blog-section ul {
            padding-left: 20px;
            margin-top: 10px;
        }
        .post-item {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #eaeaea;
            border-radius: 8px;
        }
        .post-item h5 {
            margin-bottom: 5px;
        }
        .post-item p {
            margin-bottom: 5px;
        }
        .post-item img {
            max-width: 100px;
            margin-top: 5px;
            border-radius: 4px;
        }
        form textarea {
            width: 100%;
            height: 100px;
            margin-bottom: 10px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
        }
        form button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        form button[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Il Mio Profilo </h1>
    <nav>        
        <form action="../pubblico/search.php" method="GET">
            <input type="text" name="query" placeholder="Cerca blog o post">
            <button type="submit">Cerca</button>
        </form>
        <ul>
            <li><a href="../pubblico/home.php">Home</a></li>
            <li><a href="../pubblico/my_profile.php">Il mio profilo</a></li>
            <li><a href="../pubblico/account_settings.php">Impostazioni profilo</a></li>
            <li><a href="../pubblico/logout.php">Logout</a></li>
        </ul>
    </nav>

    <form method="post" enctype="multipart/form-data">
        <label for="img_profilo">Immagine del profilo:</label>
        <input type="file" name="img_profilo" id="img_profilo">
        <input type="submit" value="Carica immagine">
    </form>

    <img src="../uploads/<?php echo $user['img_profilo']; ?>" class="profile-picture" alt="Immagine del profilo">

    <div>
        <h2>Informazioni Personali</h2>
        <p>Username: <?php echo $user['username']; ?></p>
        <p>Nome: <?php echo $user['nome']; ?></p>
        <p>Cognome: <?php echo $user['cognome']; ?></p>
        <p>Genere: <?php echo $user['genere']; ?></p>
        <p>Numero di telefono: <?php echo $user['numero_telefono']; ?></p>
        <p>Bio:</p>
        <form method="post">
            <textarea name="bio"><?php echo $user['bio']; ?></textarea>
            <button type="submit" name="update_bio">Aggiorna Bio</button>
        </form>
        <?php if (isset($bioUpdateSuccess) && $bioUpdateSuccess): ?>
            <p>Bio aggiornata con successo.</p>
        <?php endif; ?>
    </div>

    <div>
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
                        <a href="../pubblico/edit_blog.php?id_blog=<?php echo $blog['id_blog']; ?>">Modifica Blog</a>
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
                                        <a href="../pubblico/edit_post.php?id_post=<?php echo $post['id_post']; ?>">Modifica Post</a>
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