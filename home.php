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
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        .error {color: red;}
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Benvenuto, <?php echo htmlspecialchars($username); ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="home.php"> Home </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="my_profile.php">Il mio profilo </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="account_settings.php">Impostazioni profilo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0" action="search.php" method="GET">
                <input class="form-control mr-sm-2" type="text" name="query" placeholder="Cerca blog o post">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Cerca</button>
            </form>
        </div>
    </nav>

    <div class="container mt-5">
        <h1><?php echo $saluto . ', ' . $username; ?> nella tua home </h1>
        <p>Questi sono i blog degli altri utenti.</p>

        <?php if (!empty($blogs)): ?>
            <div class="row">
                <?php foreach ($blogs as $blog): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="uploads/<?php echo $blog['img_logo']; ?>" class="card-img-top" alt="Logo del Blog">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $blog['titolo_blog']; ?></h5>
                                <p class="card-text"><?php echo $blog['descrizione']; ?></p>
                                <p class="card-text">Proprietario: <?php echo $blog['username']; ?></p>
                                <a href="view_blog.php?id_blog=<?php echo $blog['id_blog']; ?>" class="btn btn-primary">Visualizza Post</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Non ci sono blog da mostrare.</p>
        <?php endif; ?>

        <a href="create_blog.php" class="btn btn-success">Crea un nuovo blog</a>
        <a href="create_post.php" class="btn btn-info">Crea un nuovo post</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>