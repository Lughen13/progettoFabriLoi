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


// recuppera i dati della sessione
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$category_id = $_POST['category'] ?? '';
$style_id = $_POST['style'] ?? '';
$co_autore_id = $_POST['co_autore'] ?? null; // recupero l'id dell'utente che è stato selezionato come co-autore 

// Carica logo del blog
$logo_destination = 'default.png'; // Valore di default
if (!empty($_FILES['logo']['name'])) {
    $logo_name = $_FILES['logo']['name'];
    $logo_tmp = $_FILES['logo']['tmp_name'];
    $logo_destination = 'uploads/' . $logo_name;
    move_uploaded_file($logo_tmp, $logo_destination);
}

// sessione per inserire il nuovo blog nel db 
$insert_blog_query = "INSERT INTO blog (data_blog, titolo_blog, descrizione, img_logo, id_categoria, id_stile, id_proprietario, followers_count) VALUES (NOW(), ?, ?, ?, ?, ?, ?, 0)";
$stmt_blog = $conn->prepare($insert_blog_query);
$stmt_blog->bind_param("ssssii", $title, $description, $logo_destination, $category_id, $style_id, $_SESSION['id']);
if ($stmt_blog->execute()) {
    $blog_id = $stmt_blog->insert_id; // Recupera l'id del blog appena inserito
    $stmt_blog->close();

    // Inserisci il proprietario nella tabella 'co_autore'
  //  $insert_owner_query = "INSERT INTO co_autore (id_utente, id_blog) VALUES (?, ?)";
  //  $stmt_owner = $conn->prepare($insert_owner_query);
  //  $stmt_owner->bind_param("ii", $_SESSION['id'], $blog_id);
  //  $stmt_owner->execute();
  //  $stmt_owner->close();

    // Se è stato selezionato un co-autore, inseriscilo nella tabella 'co_autore'
    if (!empty($co_autore_id)) {
        $insert_co_author_query = "INSERT INTO co_autore (id_utente, id_blog) VALUES (?, ?)";
        $stmt_co_author = $conn->prepare($insert_co_author_query);
        $stmt_co_author->bind_param("ii", $co_autore_id, $blog_id);
        $stmt_co_author->execute();
        $stmt_co_author->close();
        echo "Blog creato con successo con co-autore!";
    } else {
        echo "Blog creato con successo!";
    }
} else {
    echo "Errore durante la creazione del blog: " . $stmt_blog->error;
}

$conn->close();
?>