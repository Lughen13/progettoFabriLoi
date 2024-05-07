<?php
// my_blogs.php

// Connessione al database
require_once 'conn.php';

// Recupera i blog dell'utente corrente dal database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM blog WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Visualizza i blog
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='blog-card'>";
        echo "<img src='" . $row['logo'] . "' alt='Logo del blog'>";
        echo "<h2>" . $row['title'] . "</h2>";
        echo "<p>" . $row['description'] . "</p>";
        echo "<a href='edit_blog.php?id=" . $row['id'] . "'>Modifica</a>";
        echo "<a href='delete_blog.php?id=" . $row['id'] . "'>Elimina</a>";
        echo "</div>";
    }
} else {
    echo "Nessun blog trovato.";
}
// Recupera l'ID del blog
$blog_id = $_GET['blog_id'];

// Recupera i co-autori del blog dal database
$sql = "SELECT u.username
        FROM co_autore ca
        JOIN utente u ON ca.id_utente = u.id_utente
        WHERE ca.id_blog = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Co-autori</h2>
<?php
if ($result->num_rows > 0) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . $row['username'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "Nessun co-autore trovato.";
}
?>
// Recupera i co-autori del blog dal database
$sql = "SELECT u.id_utente, u.username
        FROM co_autore ca
        JOIN utente u ON ca.id_utente = u.id_utente
        WHERE ca.id_blog = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Co-autori</h2>
<?php
if ($result->num_rows > 0) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . $row['username'] . " <a href='remove_coauthor.php?blog_id=" . $blog_id . "&coauthor_id=" . $row['id_utente'] . "'>Rimuovi</a></li>";
    }
    echo "</ul>";
} else {
    echo "Nessun co-autore trovato.";
}
// Recupera i dati dalla query string
$blog_id = $_GET['blog_id'];
$coauthor_id = $_GET['coauthor_id'];

// Rimuovi il co-autore dal database
$sql = "DELETE FROM co_autore WHERE id_utente = ? AND id_blog = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $coauthor_id, $blog_id);

if ($stmt->execute()) {
    echo "Co-autore rimosso con successo!";
} else {
    echo "Errore durante la rimozione del co-autore: " . $stmt->error;
}
?>
<a href="add_coauthor.php?blog_id=<?php echo $blog_id; ?>">Aggiungi co-autore</a>
<a href="add_coauthor.php?blog_id=<?php echo $blog_id; ?>">Aggiungi co-autore</a>
$stmt->close();
$conn->close();