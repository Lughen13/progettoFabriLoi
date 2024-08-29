<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

// Connessione al database
require_once '../configurazione/conn.php';

// Verifica se l'utente è loggato al sito 
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../pubblico/login.php");
    exit();
}

// Recupera i dati della sessione
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$category_id = $_POST['category'] ?? '';
$co_autore_id = $_POST['co_autore'] ?? null; // Recupero l'id dell'utente che è stato selezionato come co-autore 
$current_user_id = $_SESSION['id']; 

// Recupera lo stato premium dell'utente
$queryPremium = "SELECT premium FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($queryPremium);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$resultPremium = $stmt->get_result();
$isPremium = $resultPremium->fetch_assoc()['premium'];
$stmt->close();

// Controlla il numero di blog esistenti dell'utente
$queryBlogCount = "SELECT COUNT(*) AS blog_count FROM blog WHERE id_proprietario = ?";
$stmt = $conn->prepare($queryBlogCount);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$resultBlogCount = $stmt->get_result();
$blogCount = $resultBlogCount->fetch_assoc()['blog_count'];
$stmt->close();

// Se l'utente non è premium e ha già creato un blog, impedisci la creazione di nuovi blog
if (!$isPremium && $blogCount >= 1) {
    $_SESSION['error_msg'] = "Gli utenti non premium possono creare solo un blog. Passa a premium per crearne di più.";
    header("Location: ../pubblico/create_blog.php");
    exit();
}

$logoName = 'default.png'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $logoFile = $_FILES['logo'];
    $logoTmpName = $logoFile['tmp_name'];
    $logoFileName = $logoFile['name'];
    $logoSize = $logoFile['size'];
    $logoError = $logoFile['error'];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $logoExtension = strtolower(pathinfo($logoFileName, PATHINFO_EXTENSION));

    if (in_array($logoExtension, $allowedExtensions) && $logoError === 0) {
        $logoName = $title . '.' . $logoExtension; // Nuovo nome del file del logo
        $logoDestination = '../blog_logo/' . $logoName;

        if (!move_uploaded_file($logoTmpName, $logoDestination)) {
            $logoName = 'default.png'; 
        }
    }
}

// Inserisci il nuovo blog nel database
$insert_blog_query = "INSERT INTO blog (data_blog, titolo_blog, descrizione, img_logo, id_categoria, id_proprietario, followers_count) VALUES (NOW(), ?, ?, ?, ?, ?, 0)";
$stmt_blog = $conn->prepare($insert_blog_query);
$stmt_blog->bind_param("sssii", $title, $description, $logoName, $category_id, $current_user_id);

if ($stmt_blog->execute()) {
    $blog_id = $stmt_blog->insert_id; // Recupera l'id del blog appena inserito
    $stmt_blog->close();

    // Se è stato selezionato un co-autore, inseriscilo nella tabella 'co_autore'
    if (!empty($co_autore_id)) {
        $insert_co_author_query = "INSERT INTO co_autore (id_utente, id_blog) VALUES (?, ?)";
        $stmt_co_author = $conn->prepare($insert_co_author_query);
        $stmt_co_author->bind_param("ii", $co_autore_id, $blog_id);
        $stmt_co_author->execute();
        $stmt_co_author->close();
        $_SESSION['success_msg'] = "Blog creato con successo con co-autore!";
        header("Location: ../pubblico/my_profile.php");
        exit();
    } else {
        $_SESSION['success_msg'] = "Blog creato con successo!";
        header("Location: ../pubblico/my_profile.php");
        exit();
    }
} else {
    $_SESSION['error_msg'] = "Errore nella creazione del blog: " . $stmt_blog->error;
    header("Location: ../pubblico/create_blog.php");
    exit();
}

?>