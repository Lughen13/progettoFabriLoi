<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Se l'utente non è autenticato, reindirizza alla pagina di login
    header("Location: login.php");
    exit();
}

require_once 'conn.php';

// Recupero i dati dell'utente dalla tabella utente del db
$userId = $_SESSION['id'];
$sql = "SELECT username, email, nome, cognome, data_nascita, genere, bio, img_profilo FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Gestione dell'aggiornamento della bio
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_bio'])) {
    $newBio = $_POST['bio'];
    $updateBioQuery = "UPDATE utente SET bio = ? WHERE id_utente = ?";
    $stmt = $conn->prepare($updateBioQuery);
    $stmt->bind_param("si", $newBio, $userId);
    if ($stmt->execute()) {
        $bioUpdateSuccess = true;
        $user['bio'] = $newBio; // Aggiornamento della bio nell'array $user
    } else {
        echo "Errore durante l'aggiornamento della bio: " . $stmt->error;
    }
    $stmt->close();
}

// Gestione del caricamento dell'immagine del profilo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    $fileName = basename($_FILES['profile_image']['name']);
    $targetPath = $uploadDir . $fileName;

    // Controllo del tipo di file
    $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
            // Aggiornamento del nome del file nell'array $user
            $updateImageQuery = "UPDATE utente SET img_profilo = ? WHERE id_utente = ?";
            $stmt = $conn->prepare($updateImageQuery);
            $stmt->bind_param("si", $fileName, $userId);
            if ($stmt->execute()) {
                $imageUpdateSuccess = true;
                $user['img_profilo'] = $fileName; // Aggiornamento del nome del file nell'array $user
            } else {
                echo "Errore durante l'aggiornamento dell'immagine del profilo: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Errore durante il caricamento dell'immagine.";
        }
    } else {
        echo "Tipo di file non supportato. Sono ammessi solo file immagine (JPG, JPEG, PNG, GIF).";
    }
}

// Recupero dei blog dell'utente e dei relativi post
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

// Inserimento di un nuovo post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_post'])) {
    $id_blog = $_POST['id_blog'];
    $titolo_post = $_POST['titolo_post'];
    $descrizione_post = $_POST['descrizione_post'];
    
    // Controllo se è stato caricato un file per il post
    if ($_FILES['img_post']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $fileName = basename($_FILES['img_post']['name']);
        $targetPath = $uploadDir . $fileName;

        // Controllo del tipo di file
        $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['img_post']['tmp_name'], $targetPath)) {
                $insertPostQuery = "INSERT INTO post (id_blog, titolo_post, descrizione_post, img_post) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insertPostQuery);
                $stmt->bind_param("isss", $id_blog, $titolo_post, $descrizione_post, $fileName);
                if ($stmt->execute()) {
                    $postInsertSuccess = true;
                } else {
                    echo "Errore durante l'inserimento del post: " . $stmt->error;
                }
                $stmt->close();
            } else {
                echo "Errore durante il caricamento dell'immagine del post.";
            }
        } else {
            echo "Tipo di file non supportato per l'immagine del post. Sono ammessi solo file immagine (JPG, JPEG, PNG, GIF).";
        }
    } else {
        echo "Errore durante il caricamento dell'immagine del post.";
    }
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
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id_blog" value="<?php echo $blog['id_blog']; ?>">
                        <input type="text" name="titolo_post" placeholder="Titolo del post">
                        <textarea name="descrizione_post" placeholder="Descrizione del post"></textarea>
                        <input type="file" name="img_post">
                        <button type="submit" name="submit_post">Aggiungi Post</button>
                    </form>
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
