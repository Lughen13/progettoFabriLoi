<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');

// Connessione al database
require_once 'conn.php';

// Verifica se l'utente è autenticato
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Recupera i dati dal modulo
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$category_id = $_POST['category'] ?? '';
$style_id = $_POST['style'] ?? '';
$co_author = $_POST['co_author'] ?? null;

// Carica l'immagine del logo
$logo_destination = 'default.png'; // Valore di default
if (!empty($_FILES['logo']['name'])) {
    $logo_name = $_FILES['logo']['name'];
    $logo_tmp = $_FILES['logo']['tmp_name'];
    $logo_destination = 'uploads/' . $logo_name;
    move_uploaded_file($logo_tmp, $logo_destination);
}

// Inserisci il nuovo blog nel database
$insert_blog_query = "INSERT INTO blog (data_blog, titolo_blog, descrizione, img_logo, id_categoria, id_stile, id_proprietario, followers_count) VALUES (NOW(), ?, ?, ?, ?, ?, ?, 0)";
$stmt_blog = $conn->prepare($insert_blog_query);
$stmt_blog->bind_param("ssssii", $title, $description, $logo_destination, $category_id, $style_id, $_SESSION['id']);

if ($stmt_blog->execute()) {
    $blog_id = $stmt_blog->insert_id; // Recupera l'id del blog appena inserito
    $stmt_blog->close();

    // Inserisci il co-autore se specificato
    if (!empty($co_author)) {
        $insert_co_author_query = "INSERT INTO co_autore (id_utente, id_blog) VALUES (?, ?)";
        $stmt_co_author = $conn->prepare($insert_co_author_query);
        $stmt_co_author->bind_param("ii", $co_author, $blog_id);

        if ($stmt_co_author->execute()) {
            echo "Blog creato con successo con co-autore!";
        } else {
            echo "Errore durante l'inserimento del coautore: " . $stmt_co_author->error;
        }

        $stmt_co_author->close();
    } else {
        echo "Blog creato con successo!";
    }
} else {
    echo "Errore durante la creazione del blog: " . $stmt_blog->error;
}

$conn->close();
?>