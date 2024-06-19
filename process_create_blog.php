<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

// Connessione al database
require_once 'conn.php';

// Verifica se l'utente è loggato al sito 
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Recupera i dati della sessione
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$category_id = $_POST['category'] ?? '';
$style_id = $_POST['style'] ?? '';
$co_autore_id = $_POST['co_autore'] ?? null; // Recupero l'id dell'utente che è stato selezionato come co-autore 
$current_user_id = $_SESSION['id']; 

// Inizializza la variabile del nome del file del logo
$logoName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
    $logoFile = $_FILES['logo'];
    $logoTmpName = $logoFile['tmp_name'];
    $logoFileName = $logoFile['name'];
    $logoSize = $logoFile['size'];
    $logoError = $logoFile['error'];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $logoExtension = strtolower(pathinfo($logoFileName, PATHINFO_EXTENSION));

    if (in_array($logoExtension, $allowedExtensions) && $logoError === 0) {
        $logoName = $title . '.' . $logoExtension; // Nuovo nome del file del logo
        $logoDestination = 'blog_logo/' . $logoName;

        if (move_uploaded_file($logoTmpName, $logoDestination)) {
            echo "Logo caricato con successo.";
        } else {
            echo "Errore durante il caricamento del logo.";
        }
    } else {
        echo "Formato del logo non valido o errore durante il caricamento.";
    }
}

// Inserisci il nuovo blog nel database
$insert_blog_query = "INSERT INTO blog (data_blog, titolo_blog, descrizione, img_logo, id_categoria, id_stile, id_proprietario, followers_count) VALUES (NOW(), ?, ?, ?, ?, ?, ?, 0)";
$stmt_blog = $conn->prepare($insert_blog_query);
$stmt_blog->bind_param("ssssii", $title, $description, $logoName, $category_id, $style_id, $current_user_id);

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
        echo "Blog creato con successo con co-autore!";
        echo '<script>setTimeout(function(){ window.location.href = "home.php"; }, 2000);</script>';
    } else {
        echo "Blog creato con successo!";
        echo '<script>setTimeout(function(){ window.location.href = "home.php"; }, 2000);</script>';
    }
} else {
    echo "Errore nella creazione del blog: " . $stmt_blog->error;
}

$conn->close();
?>