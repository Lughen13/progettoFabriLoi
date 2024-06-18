<?php
session_start();
require_once 'conn.php';
// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

//$userId = $_SESSION['id'];  // ID dell'utente autenticato
//$action = isset($_GET['action']) ? $_GET['action'] : '';

// recupero 
$userId = $_SESSION['id'];
$sql = "SELECT username, email, nome, cognome, data_nascita, genere, bio, img_profilo, pw FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();


// Gestione del caricamento dell'immagine del profilo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['img_profilo']['name'])) {
        $imgFile = $_FILES['img_profilo'];
        $imgFileName = $imgFile['name'];
        $imgTmpName = $imgFile['tmp_name'];
        $imgSize = $imgFile['size'];
        $imgError = $imgFile['error'];

        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $imgExtension = strtolower(pathinfo($imgFileName, PATHINFO_EXTENSION));

        if (in_array($imgExtension, $allowedExtensions) && $imgError === 0) {
            $newImgFileName =  $user['username'] . '_profilo.' . $imgExtension;
            $imgDestination = 'uploads/' . $newImgFileName;

            if (move_uploaded_file($imgTmpName, $imgDestination)) {
                // Aggiorna l'immagine del profilo nel database
                $updateQuery = "UPDATE utente SET img_profilo = ? WHERE id_utente = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param('si', $newImgFileName, $userId);
                $stmt->execute();

                // Aggiorna i dati dell'utente
                $sql = "SELECT * FROM utente WHERE id_utente = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                echo "Errore durante il caricamento dell'immagine.";
            }
        } else {
            echo "Formato dell'immagine non valido.";
        }}}

// recupero i blog e i relativi post dell'utente 
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

// Recupero informazioni utente da mettere nei dati personali 
$userQuery = "SELECT username, email, nome, cognome, data_nascita, genere, bio, img_profilo, numero_telefono FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

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
    <style>
        .profile-picture {
            max-width: 200px;
            max-height: 200px;
        }
    </style>

<form method="post" enctype="multipart/form-data">
        <label for="img_profilo">Immagine del profilo:</label>
        <input type="file" name="img_profilo" id="img_profilo">
        <input type="submit" value="Carica immagine">
    </form>

    <div>
         
    <img src="uploads/<?php echo $user['img_profilo']; ?>" class="profile-picture" alt="Immagine del profilo">
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
                <div>
                    <h3><?php echo $blog['titolo_blog']; ?></h3>
                    <p><?php echo $blog['descrizione']; ?></p>
                    <img src="uploads/<?php echo $blog['img_logo']; ?>" alt="Logo del Blog" width="100">
                    
                    <?php if (isBlogFollowed($blogs, $blog['id_blog'])): ?>
                        <a href="my_profile.php?action=unfollow&id_blog=<?php echo $blog['id_blog']; ?>">Non Seguire</a>
                    <?php else: ?>
                        <a href="my_profile.php?action=follow&id_blog=<?php echo $blog['id_blog']; ?>">Segui</a>
                    <?php endif; ?>
                    
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