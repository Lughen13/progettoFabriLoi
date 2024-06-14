<?php
require_once 'conn.php';

// Funzione per recuperare gli stili
function getStyles() {
    global $conn;
    $stylesQuery = "SELECT * FROM stile";
    $stylesResult = $conn->query($stylesQuery);
    return $stylesResult;
}
