
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log'); // Assicurati che il percorso sia corretto

require_once '../configurazione/conn.php'; // Assumendo che il file di connessione si chiami conn.php
session_start();

// Controlla se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../pubblico/login.php');
    exit;
}

// Recupera l'id dell'utente dalla sessione
$userId = $_SESSION['id'];

// Prepara la query SQL
$sql = "
SELECT 
    n.id, 
    u.username AS sender_username, 
    n.tipo, 
    n.data, 
    CASE 
        WHEN n.tipo = 'comment' THEN p.titolo_post 
        WHEN n.tipo = 'like' THEN p.titolo_post 
        WHEN n.tipo = 'follow' THEN b.titolo_blog 
    END AS contenuto_titolo,
    CASE 
        WHEN n.tipo = 'comment' THEN p.id_post 
        WHEN n.tipo = 'like' THEN p.id_post 
        WHEN n.tipo = 'follow' THEN b.id_blog 
    END AS contenuto_id
FROM notifiche n
LEFT JOIN utente u ON n.sender_id = u.id_utente
LEFT JOIN post p ON (n.contenuto_id = p.id_post AND (n.tipo = 'comment' OR n.tipo = 'like'))
LEFT JOIN blog b ON (n.contenuto_id = b.id_blog AND n.tipo = 'follow')
WHERE n.user_id = ?
ORDER BY n.data DESC
";

if ($stmt = $conn->prepare($sql)) {
    // Assumendo che l'ID utente sia passato come parametro
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = array();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    // Restituisce le notifiche come JSON
    echo json_encode($notifications);
    
    $stmt->close();
} else {
    echo "Errore nella preparazione della query: " . $conn->error;
}

$conn->close();
?>