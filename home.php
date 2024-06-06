<?php
include 'conn.php';
//come prima cosa in hom c'è il login perchè se non si è loggato non si può accedere al sito
// in home l'utente potrà vedere i lpost dei blog che segue
//avrà accesso al proprio profilo per visualizzarlo, al setting per modificare i propri dati, ai comandi per creare poste e blog


// login per vedere se l'utente è loggato, altrimenti può registrarsi 
session_start();

// Verifica se l'utente è loggato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}
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
            <li><a href="create_blog.php">Crea Nuovo Blog</a></li>
            <li><a href="create_post.php">Crea Nuovo Post</a></li>
            <li><a href="account_settings.php">Impostazioni profilo</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
        <form action="search.php" method="GET">
            <input type="text" name="query" placeholder="Cerca blog o post">
            <button type="submit">Cerca</button>
        </form>
    </nav>

<body>

    <h1>Benvenuto nella Home</h1>
    <p>Questa è la pagina di home del nostro sito web.</p>


</body>
</html>
