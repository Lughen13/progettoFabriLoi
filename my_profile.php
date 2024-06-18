<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
session_start();
require_once 'conn.php';

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
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
        header("Location: my_profile.php");  // Reindirizza alla pagina del profilo
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
        header("Location: my_profile.php");  // Reindirizza alla pagina del profilo
        exit();
    } else {
        echo "Errore durante l'eliminazione del post: " . $stmt->error;
    }
    $stmt->close();
}

// Recupero i blog dell'utente e dei relativi post
$blogQuery = "SELECT b.id_blog, b.titolo_blog, b.descrizione, b.img_logo, p.id_post, p.titolo_post, p.descrizione_post, p.img_post
              FROM blog b
              LEFT JOIN post p ON b.id_blog = p.id_blog
              WHERE b.id_proprietario = ?";
$stmt = $conn->prepare($blogQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$blogs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Recupero informazioni utente
$userQuery = "SELECT username, email, nome, cognome, data_nascita, genere, bio, img_profilo FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Verifica se un blog è già seguito dall'utente
function isBlogFollowed($blogs, $id_blog) {
    foreach ($blogs as $blog) {
        if ($blog['id_blog'] == $id_blog) {
            return true;
        }
    }
    return false;
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il Mio Profilo</title>
</head>
<body>
    <h1>Il Mio Profilo</h1>

    <div>
        <img src="uploads/<?php echo $user['img_profilo']; ?>" alt="Immagine del Profilo" width="200">
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="profile_image">
            <button type="submit">Carica Immagine</button>
        </form>
        <?php if (isset($imageUpdateSuccess) && $imageUpdateSuccess): ?>
            <p>Immagine del profilo aggiornata con successo.</p>
        <?php endif; ?>
    </div>

    <div>
        <h2>Informazioni Personali</h2>       
        <p>Username: <?php echo $user['username']; ?></p>
        <p>Nome: <?php echo $user['nome']; ?></p>
        <p>Cognome: <?php echo $user['cognome']; ?></p>
 
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
                <div>
                    <h3><?php echo $blog['titolo_blog']; ?></h3>
                    <p><?php echo $blog['descrizione']; ?></p>
                    <img src="uploads/<?php echo $blog['img_logo']; ?>" alt="Logo del Blog" width="100">
                    
                    <?php if (isBlogFollowed($blogs, $blog['id_blog'])): ?>
                        <a href="my_profile.php?action=unfollow&id_blog=<?php echo $blog['id_blog']; ?>">Non Seguire</a>
                    <?php else: ?>
                        <a href="my_profile.php?action=follow&id_blog=<?php echo $blog['id_blog']; ?>">Segui</a>
                    <?php endif; ?>
                    
                    <a href="my_profile.php?action=delete_blog&id_blog=<?php echo $blog['id_blog']; ?>" onclick="return confirm('Sei sicuro di voler eliminare questo blog?')">Elimina Blog</a>
                    
                    <?php if (!empty($blog['id_post'])): ?>
                        <h4>Post:</h4>
                        <ul>
                            <?php
                            $posts = array_filter($blogs, function ($item) use ($blog) {
                                return $item['id_blog'] == $blog['id_blog'];
                            });
                            foreach ($posts as $post):
                                ?>
                                <li>
                                    <h5><?php echo $post['titolo_post']; ?></h5>
                                    <p><?php echo $post['descrizione_post']; ?></p>
                                    <img src="uploads/<?php echo $post['img_post']; ?>" alt="Immagine del Post" width="100">
                                    <a href="my_profile.php?action=delete_post&id_post=<?php echo $post['id_post']; ?>" onclick="return confirm('Sei sicuro di voler eliminare questo post?')">Elimina Post</a>
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
</body>
</html>