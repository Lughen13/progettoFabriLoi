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

// Recupera tutti i blog tranne quelli dell'utente autenticato
$query = "SELECT b.id_blog, b.titolo_blog, b.descrizione, b.img_logo, u.username
          FROM blog b
          INNER JOIN utente u ON b.id_proprietario = u.id_utente
          WHERE b.id_proprietario != ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$blogs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>    
<body>
    
<style>
        .blog_logo {
            max-width: 75px;
            max-height: 75px;
        }
    </style>
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

    <h1><?php echo $saluto . ', ' . $username; ?> nella tua home </h1>
    <p>Questi sono i blog degli altri utenti.</p>

    <?php if (!empty($blogs)): ?>
        <ul>
            <?php foreach ($blogs as $blog): ?>
                <li>
                    <h2><?php echo $blog['titolo_blog']; ?></h2>
                    <p><?php echo $blog['descrizione']; ?></p>
                    <p>Proprietario: <?php echo $blog['username']; ?></p>
                    <img src="blog_logo/<?php echo  basename($blog['img_logo']); ?>" alt="Logo del Blog">

                    <a href="view_blog.php?id_blog=<?php echo $blog['id_blog']; ?>">Visualizza Post</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Non ci sono blog da mostrare.</p>
    <?php endif; ?>

    <a href="create_blog.php">Crea un nuovo blog</a>
    <a href="create_post.php">Crea un nuovo post</a>
</body>
</html>