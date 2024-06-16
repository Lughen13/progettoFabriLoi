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

$id_utente = $_SESSION['id'] ?? null;

if (empty($id_utente)) {
    die("Errore: id_utente non è settato nella sessione.");
}

// Recupera i dati dal modulo
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$category_id = $_POST['category'] ?? '';
$style_id = $_POST['style'] ?? '';
$co_autore = $_POST['co_autore'] ?? '';

// Verifica che tutti i dati necessari siano stati forniti
if (empty($title) || empty($description) || empty($category_id) || empty($style_id)) {
    die("Errore: Tutti i campi sono obbligatori.");
}

// Carica l'immagine del logo
$logo_name = '';
$logo_destination = '';
if (!empty($_FILES['logo']['name'])) {
    $logo_name = $_FILES['logo']['name'];
    $logo_tmp = $_FILES['logo']['tmp_name'];
    $logo_destination = 'uploads/' . $logo_name;
    move_uploaded_file($logo_tmp, $logo_destination);
} else {
    $logo_destination = 'default.png';
}

// Inserisci il nuovo blog nel database
$sql = "INSERT INTO blog (data_blog, titolo_blog, descrizione, img_logo, id_categoria, id_stile, id_proprietario, followers_count) VALUES (NOW(), ?, ?, ?, ?, ?, ?, 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiii", $title, $description, $logo_destination, $category_id, $style_id, $id_utente);

if ($stmt->execute()) {
    $blog_id = $stmt->insert_id; // Ottieni l'ID del blog appena inserito
    
    // Se è stato specificato un co-autore, aggiungilo nella tabella co_autore
    if (!empty($co_autore)) {
        $insert_co_autore_query = "INSERT INTO co_autore (id_utente, id_blog) VALUES (?, ?)";
        $stmt_co_autore = $conn->prepare($insert_co_autore_query);
        $stmt_co_autore->bind_param("ii", $co_autore, $blog_id);

        if ($stmt_co_autore->execute()) {
            echo "Blog creato con successo con co-autore!";
        } else {
            echo "Errore durante l'inserimento del coautore: " . $stmt_co_autore->error;
        }
        $stmt_co_autore->close();
    } else {
        echo "Blog creato con successo!";
    }
} else {
    echo "Errore durante la creazione del blog: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>