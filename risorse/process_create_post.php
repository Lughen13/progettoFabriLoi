<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

require_once '../configurazione/conn.php';
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../pubblico/login.php");
    exit();
}

$userId = $_SESSION['id'];
$blogId = $_POST['id_blog'];
$title = $_POST['title'];
$description = $_POST['description'];
$subcategoryId = $_POST['subcategory'];
$photos = [];

// Recupera lo stato premium dell'utente
$queryPremium = "SELECT premium FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($queryPremium);
$stmt->bind_param("i", $userId);
$stmt->execute();
$resultPremium = $stmt->get_result();
$isPremium = $resultPremium->fetch_assoc()['premium'];
$stmt->close();

// Controlla il numero di post esistenti dell'utente
$queryPostCount = "SELECT COUNT(*) AS post_count FROM post WHERE id_blog = ? AND id_autore = ?";
$stmt = $conn->prepare($queryPostCount);
$stmt->bind_param("ii", $blogId, $userId);
$stmt->execute();
$resultPostCount = $stmt->get_result();
$postCount = $resultPostCount->fetch_assoc()['post_count'];
$stmt->close();

// Se l'utente non è premium e ha già creato un post, impedisci la creazione di nuovi post
if (!$isPremium && $postCount >= 1) {
    $_SESSION['error_msg'] = "Gli utenti non premium possono creare solo un post. Passa a premium per crearne di più.";
    header("Location: ../pubblico/create_post.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['immagini']) && !empty($_FILES['immagini']['name'][0])) {
    $files = $_FILES['immagini'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    for ($i = 0; $i < count($files['name']); $i++) {
        $fileName = $files['name'][$i];
        $fileTmpName = $files['tmp_name'][$i];
        $fileSize = $files['size'][$i];
        $fileError = $files['error'][$i];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedExtensions) && $fileError === 0 && $fileSize > 0) {
            $newFileName = uniqid() . '_' . $title . '_' . ($i + 1) . '.' . $fileExtension;
            $fileDestination = '../photo_post/' . $newFileName;

            if (!is_dir('../photo_post')) {
                mkdir('../photo_post', 0775, true);
            }

            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                $photos[] = $newFileName;
            } else {
                echo "Errore durante il caricamento dell'immagine $fileName.";
            }
        } else {
            echo "Formato dell'immagine $fileName non valido o errore durante il caricamento.";
        }
    }
}

$photosJson = json_encode($photos);

$sql = "INSERT INTO post (titolo_post, descrizione_post, img_post, id_autore, id_sottocat, id_blog, data_post) VALUES (?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiii", $title, $description, $photosJson, $userId, $subcategoryId, $blogId);

if ($stmt->execute()) {
    $_SESSION['success_msg'] = "Post creato con successo!";
    header("Location: ../pubblico/my_profile.php");
    exit();
} else {
    $_SESSION['error_msg'] = "Errore durante la creazione del post: " . $stmt->error;
    header("Location: ../pubblico/create_post.php");
    exit();
}

?>
