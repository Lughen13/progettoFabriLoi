<?php
// in home l'utente potrà vedere il post dei blog che segue
//avrà accesso al proprio profilo per visualizzarlo, al setting per modificare i propri dati, ai comandi per creare poste e blog

include 'conn.php';
session_start();

// Controlla se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Recupera i dati dell'utente dalla sessione
$username = $_SESSION['username'];
$genere = $_SESSION['genere']; // Recupera il genere dell'utente dalla sessione

function getSaluto($genere) {
    switch ($genere) {
        case 'Maschio':
            return 'Benvenuto';
        case 'Femmina':
            return 'Benvenuta';
        case 'Altro':
            return 'Benvenut*';
        default:
            return 'Benvenut*'; // Restituisce "Benvenuto" come valore predefinito
    }
}


// Ottieni il saluto appropriato
$saluto = getSaluto($genere);
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

    <h1><?php echo $saluto . ', ' . $username; ?> nella tua home</h1>
    <p>Questi sono i post più recenti dei blog che segui.</p>

    <!-- Codice per visualizzare i post dei blog seguiti -->

    <a href="create_blog.php">Crea un nuovo blog</a>
    <a href="create_post.php">Crea un nuovo post</a>
</body>
</html>