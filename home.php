<?php

include 'conn.php';
session_start();

// Controlla se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}


// recupera l'id dell'utente dalla sessione
$userId = $_SESSION['id'];

// recupero genere e username dell'utente dal db per gestire il codice del saluto 
$query = "SELECT genere, username FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$genere = $row['genere'];
$username = $row['username'];
$stmt->close();


function Saluta($genere) {
    if ($genere === 'Maschio') {
        return 'Benvenuto';
    } elseif ($genere === 'Femmina') {
        return 'Benvenuta';
    } elseif ($genere === 'Altro') {
        return 'Benvenut*';
    } else {
        return 'Benvenut*'; // Restituisce "Benvenut*" come valore predefinito
    }
}

$saluto = Saluta($genere);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>    
    <nav>
        <ul>
            <li><a href="home.php"> Home </a></li>
            <li><a href="my_profile.php">Il mio profilo </a></li>
            <li><a href="account_settings.php">Impostazioni profilo</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
        <form action="search.php" method="GET">
            <input type="text" name="query" placeholder="Cerca blog o post">
            <button type="submit">Cerca</button>
        </form>
    </nav>

<body>

    <h1><?php echo $saluto . ', ' . $username; ?> nella tua home </h1>
    <p>Questi sono i post più recenti dei blog che segui.</p>

    <!-- Codice per visualizzare i post dei blog seguiti -->

    <a href="create_blog.php">Crea un nuovo blog</a>
    <a href="create_post.php">Crea un nuovo post</a>
</body>
</html>