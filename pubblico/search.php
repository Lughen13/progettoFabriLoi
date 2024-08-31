<?php
require_once '../configurazione/conn.php';

// Inizializza le variabili
$blogResult = null;
$postResult = null;
$searchQuery = '';

// Verifica se è stato passato un parametro di ricerca o una categoria
if (isset($_GET['query'])) {
    $searchQuery = trim($_GET['query']);

    // Se la ricerca è vuota o contiene solo spazi, mostra un messaggio o non eseguire la query
    if (!empty($searchQuery)) {
        // Query per cercare nei blog
        $blogQuery = "SELECT b.id_blog, b.titolo_blog, b.descrizione, u.username, c.nome_categoria
                      FROM blog b
                      JOIN utente u ON b.id_proprietario = u.id_utente
                      JOIN categoria c ON b.id_categoria = c.id_categoria
                      WHERE b.titolo_blog LIKE '%$searchQuery%'
                      OR b.descrizione LIKE '%$searchQuery%'
                      OR u.username LIKE '%$searchQuery%'
                      OR c.nome_categoria LIKE '%$searchQuery%'";
        $blogResult = $conn->query($blogQuery);

        // Query per cercare nei post
        $postQuery = "SELECT p.id_post, p.titolo_post, p.descrizione_post, u.username, b.titolo_blog, p.id_blog
                      FROM post p
                      JOIN utente u ON p.id_autore = u.id_utente
                      JOIN blog b ON p.id_blog = b.id_blog
                      WHERE p.titolo_post LIKE '%$searchQuery%'
                      OR p.descrizione_post LIKE '%$searchQuery%'
                      OR u.username LIKE '%$searchQuery%'
                      OR b.titolo_blog LIKE '%$searchQuery%'";
        $postResult = $conn->query($postQuery);
    }
} elseif (isset($_GET['categoria'])) {
    $categoria = $_GET['categoria'];

    // Query per cercare i blog per categoria
    $blogQuery = "SELECT b.id_blog, b.titolo_blog, b.descrizione, u.username, c.nome_categoria
                  FROM blog b
                  JOIN utente u ON b.id_proprietario = u.id_utente
                  JOIN categoria c ON b.id_categoria = c.id_categoria
                  WHERE c.nome_categoria = '$categoria'";
    $blogResult = $conn->query($blogQuery);

    $searchQuery = $categoria; // Per visualizzare la categoria ricercata nei risultati
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Risultati Ricerca</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> 
    <link rel="stylesheet" href="../risorse/stile.css">
</head>
<body>
    <div class="container mt-4">
    <h1>
            <img src="../blog_logo/logo.png" alt="Logo ToteBlog" style="max-width: 25%; height: auto;">
        </h1>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link" href="../pubblico/home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pubblico/my_profile.php">Il mio profilo</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pubblico/account_settings.php">Impostazioni profilo</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pubblico/logout.php">Logout</a></li>
                </ul>

                <div class="d-flex align-items-right">
                    <form class="form-inline my-2 my-lg-0" action="search.php" method="GET">
                        <input class="form-control mr-sm-2" type="text" name="query" placeholder="Cerca blog o post">
                        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Cerca</button>
                    </form>
                </div>
            </div>
        </nav>

    <main class="py-4">
        <div class="container">
            <?php if (!empty($searchQuery) && isset($blogResult) && $blogResult->num_rows > 0): ?>
                <section>
                    <h2 class="text-center mb-4">Risultati della ricerca per "<?php echo htmlspecialchars($searchQuery); ?>"</h2>
                    <div class="list-group">
                        <?php while ($row = $blogResult->fetch_assoc()): ?>
                            <a href="../pubblico/view_blog.php?id_blog=<?php echo $row['id_blog']; ?>" class="list-group-item list-group-item-action">
                                <h4 class="mb-1"><?php echo htmlspecialchars($row['titolo_blog']); ?></h4>
                                <p class="mb-1"><?php echo htmlspecialchars($row['descrizione']); ?></p>
                                <small>Proprietario: <?php echo htmlspecialchars($row['username']); ?></small><br>
                                <small>Categoria: <?php echo htmlspecialchars($row['nome_categoria']); ?></small>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!empty($searchQuery) && isset($postResult) && $postResult->num_rows > 0): ?>
                <section>
                    <h2 class="text-center mb-4">Risultati della ricerca per i post:</h2>
                    <div class="list-group">
                        <?php while ($row = $postResult->fetch_assoc()): ?>
                            <a href="../pubblico/view_blog.php?id_blog=<?php echo $row['id_blog']; ?>" class="list-group-item list-group-item-action">
                                <h4 class="mb-1"><?php echo htmlspecialchars($row['titolo_post']); ?></h4>
                                <p class="mb-1"><?php echo htmlspecialchars($row['descrizione_post']); ?></p>
                                <small>Autore: <?php echo htmlspecialchars($row['username']); ?></small><br>
                                <small>Blog: <?php echo htmlspecialchars($row['titolo_blog']); ?></small>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (empty($searchQuery) || ((!isset($blogResult) || $blogResult->num_rows === 0) && (!isset($postResult) || $postResult->num_rows === 0))): ?>
                <section>
                    <p class="text-center">Nessun risultato trovato per la ricerca "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>".</p>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
   
</body>
</html>