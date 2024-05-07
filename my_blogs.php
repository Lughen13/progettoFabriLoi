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

$stmt->close();
$conn->close();