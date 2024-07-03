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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['immagini'])) {
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
    echo "<p>Post creato con successo!</p>";
    echo "<script>
        setTimeout(function() {
            window.location.href = '../pubblico/my_profile.php';
        }, 2000);
    </script>";
} else {
    echo "Errore durante la creazione del post: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>