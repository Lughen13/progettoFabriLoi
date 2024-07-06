<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
require_once '../configurazione/conn.php';
session_start();

// Controlla se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../pubblico/login.php');
    exit;
}

// Recupera l'id dell'utente dalla sessione
$userId = $_SESSION['id'];

// recupero genere e username dell'utente per gestire il benvenuto
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

// recupero i blog preferiti dall'utente per mostrarli con priorità
$queryFavoriteBlogs = "SELECT b.id_blog, b.titolo_blog, b.descrizione, b.img_logo, u.username, c.nome_categoria
                       FROM follow f
                       INNER JOIN blog b ON f.id_blog = b.id_blog
                       INNER JOIN utente u ON b.id_proprietario = u.id_utente
                       INNER JOIN categoria c ON b.id_categoria = c.id_categoria
                       WHERE f.id_utente = ?";
$stmt = $conn->prepare($queryFavoriteBlogs);
$stmt->bind_param("i", $userId);
$stmt->execute();
$resultFavoriteBlogs = $stmt->get_result();
$favoriteBlogs = $resultFavoriteBlogs->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$favoriteBlogIds = array_column($favoriteBlogs, 'id_blog');
$favoriteBlogIdsStr = implode(',', array_map('intval', $favoriteBlogIds));

// recupero tutti gli altri blog ad ecceione di quelli dell'utente loggato e quelli seguiti (già mostrati precedentemente) 
$query = "SELECT b.id_blog, b.titolo_blog, b.descrizione, b.img_logo, u.username, c.nome_categoria
          FROM blog b
          INNER JOIN utente u ON b.id_proprietario = u.id_utente
          INNER JOIN categoria c ON b.id_categoria = c.id_categoria
          WHERE b.id_proprietario != ?";

if (!empty($favoriteBlogIdsStr)) {
    $query .= " AND b.id_blog NOT IN ($favoriteBlogIdsStr)";
}

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> <!-- Font Awesome per icone -->
    <style>
        .error {color: red;}
        .card {
            margin-bottom: 20px;
        }
        .comment-container {
            padding: 10px;
            background-color: #f9f9f9;
            margin-bottom: 10px;
        }
        .comment-actions {
            margin-top: 10px;
        }
        .sidebar {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 300px;
        }
        .category-logo {
            font-size: 50px;
            color: #333;
        }
        .category-card {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Ciao <?php echo htmlspecialchars($username); ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="../pubblico/home.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pubblico/my_profile.php">Il mio profilo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pubblico/account_settings.php">Impostazioni profilo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pubblico/logout.php">Logout</a>
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0" action="../pubblico/search.php" method="GET">
                <input class="form-control mr-sm-2" type="text" name="query" placeholder="Cerca blog o post">
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Cerca</button>
            </form>
        </div>
    </nav>

    <div class="container mt-5">
        <h1><?php echo $saluto . ', ' . $username; ?> nella tua home</h1>
        <p>Questi sono i blog degli altri utenti.</p>

        <!-- Sezione per le categorie con loghi -->
        <div class="row">
    <div class="col-md-12 mb-4">
        <h2>Categorie</h2>
        <div class="row">
            <div class="col-md-2 category-card">
                <a href="../pubblico/search.php?categoria=Tecnologia">
                    <div class="category-logo"><i class="fas fa-laptop-code"></i></div>
                    <p>Tecnologia</p>
                </a>
            </div>
            <div class="col-md-2 category-card">
                <a href="../pubblico/search.php?categoria=Moda">
                    <div class="category-logo"><i class="fas fa-tshirt"></i></div>
                    <p>Moda</p>
                </a>
            </div>
            <div class="col-md-2 category-card">
                <a href="../pubblico/search.php?categoria=Tempo libero">
                    <div class="category-logo"><i class="fas fa-theater-masks"></i></div>
                    <p>Tempo libero</p>
                </a>
            </div>
            <div class="col-md-2 category-card">
                <a href="../pubblico/search.php?categoria=Viaggi">
                    <div class="category-logo"><i class="fas fa-plane"></i></div>
                    <p>Viaggi</p>
                </a>
            </div>
            <div class="col-md-2 category-card">
                <a href="../pubblico/search.php?categoria=Cucina">
                    <div class="category-logo"><i class="fas fa-utensils"></i></div>
                    <p>Cucina</p>
                </a>
            </div>
            <div class="col-md-2 category-card">
                <a href="../pubblico/search.php?categoria=Scienze">
                    <div class="category-logo"><i class="fas fa-flask"></i></div>
                    <p>Scienze</p>
                </a>
            </div>
            <div class="col-md-2 category-card">
                <a href="../pubblico/search.php?categoria=Recensioni">
                    <div class="category-logo"><i class="fas fa-star"></i></div>
                    <p>Recensioni</p>
                </a>
            </div>
            <div class="col-md-2 category-card">
                <a href="../pubblico/search.php?categoria=Arredamento">
                    <div class="category-logo"><i class="fas fa-couch"></i></div>
                    <p>Arredamento</p>
                </a>
            </div>
            <div class="col-md-2 category-card">
                <a href="../pubblico/search.php?categoria=Altro">
                    <div class="category-logo"><i class="fas fa-ellipsis-h"></i></div>
                    <p>Altro</p>
                </a>
            </div>
        </div>
    </div>
</div>

        <!-- Sezione per i blog preferiti -->
        <div class="row">
            <div class="col-md-8">
                <h2>I tuoi blog preferiti</h2>
                <?php if (!empty($favoriteBlogs)): ?>
                    <div class="row">
                        <?php foreach ($favoriteBlogs as $favoriteBlog): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <img src="../blog_logo/<?php echo htmlspecialchars($favoriteBlog['img_logo']); ?>" class="card-img-top" alt="Logo del Blog">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($favoriteBlog['titolo_blog']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($favoriteBlog['descrizione']); ?></p>
                                        <p class="card-text">Proprietario: <?php echo htmlspecialchars($favoriteBlog['username']); ?></p>
                                        <p class="card-text">Categoria: <?php echo htmlspecialchars($favoriteBlog['nome_categoria']); ?></p>
                                        <a href="../pubblico/view_blog.php?id_blog=<?php echo $favoriteBlog['id_blog']; ?>" class="btn btn-primary">Visualizza Blog</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Non stai seguendo nessun blog.</p>
                <?php endif; ?>

                <!-- Sezione per i blog degli altri utenti -->
                <h2>Blog degli altri utenti</h2>
                <?php if (!empty($blogs)): ?>
                    <div class="row">
                        <?php foreach ($blogs as $blog): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <img src="../blog_logo/<?php echo $blog['img_logo']; ?>" class="card-img-top" alt="Logo del Blog">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $blog['titolo_blog']; ?></h5>
                                        <p class="card-text"><?php echo $blog['descrizione']; ?></p>
                                        <p class="card-text">Proprietario: <?php echo $blog['username']; ?></p>
                                        <p class="card-text">Categoria: <?php echo $blog['nome_categoria']; ?></p>
                                        <a href="../pubblico/view_blog.php?id_blog=<?php echo $blog['id_blog']; ?>" class="btn btn-primary">Visualizza Blog</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Non ci sono blog disponibili.</p>
                <?php endif; ?>
            </div>

             <!-- Sidebar per Ultimo Commento -->
             <div class="col-md-4">
                <div class="card sidebar">
                    <div class="card-body">
                        <h5 class="card-title">Ultimo commento ricevuto</h5>
                        <ul class="list-group">
                            <?php
                            // Query per ottenere l'ultimo Commento
                            $queryUltimoCommento = "SELECT c.id_post, c.data_comm, u.username, b.id_blog, b.titolo_blog
                                                    FROM commento c
                                                    JOIN utente u ON c.id_utente = u.id_utente
                                                    JOIN post p ON c.id_post = p.id_post
                                                    JOIN blog b ON p.id_blog = b.id_blog
                                                    ORDER BY c.data_comm DESC
                                                    LIMIT 1";
                            $resultUltimoCommento = mysqli_query($conn, $queryUltimoCommento);
                            $ultimoCommento = mysqli_fetch_assoc($resultUltimoCommento);

                            // Output dell'ultimo commento con link al blog
                            if ($ultimoCommento) {
                                $ultimoCommentoUsername = htmlspecialchars($ultimoCommento['username']);
                                $idBlog = $ultimoCommento['id_blog'];
                                $titoloBlog = htmlspecialchars($ultimoCommento['titolo_blog']);
                                echo "<li class='list-group-item'><i class='fas fa-comment text-primary'></i> Ultimo commento ricevuto da: <a href='../pubblico/view_blog.php?id_blog=$idBlog'>$ultimoCommentoUsername su \"$titoloBlog\"</a></li>";
                            } else {
                                echo "<li class='list-group-item'><i class='fas fa-comment text-primary'></i> Nessun commento recente</li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>