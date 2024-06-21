<?php
require_once 'conn.php';

// Verifica se è stato passato un parametro di ricerca
if (isset($_GET['query'])) {
    $searchQuery = $_GET['query'];

    // Query per cercare nei blog
    $blogQuery = "SELECT b.id_blog, b.titolo_blog, b.descrizione, u.username
                  FROM blog b
                  JOIN utente u ON b.id_proprietario = u.id_utente
                  WHERE b.titolo_blog LIKE '%$searchQuery%'
                  OR b.descrizione LIKE '%$searchQuery%'
                  OR u.username LIKE '%$searchQuery%'";
    $blogResult = $conn->query($blogQuery);

    // Query per cercare nei post
    $postQuery = "SELECT p.id_post, p.titolo_post, p.descrizione_post, u.username, b.titolo_blog
                  FROM post p
                  JOIN utente u ON p.id_autore = u.id_utente
                  JOIN blog b ON p.id_blog = b.id_blog
                  WHERE p.titolo_post LIKE '%$searchQuery%'
                  OR p.descrizione_post LIKE '%$searchQuery%'
                  OR u.username LIKE '%$searchQuery%'
                  OR b.titolo_blog LIKE '%$searchQuery%'";
    $postResult = $conn->query($postQuery);
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
</head>
<body>
    <!-- Intestazione -->
    <header class="bg-dark text-white py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col">
                    <h1 class="text-center">Risultati della ricerca</h1>
                </div>
            </div>
        </div>
    </header>

    <!-- Menu di navigazione -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">The Social Network</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_profile.php">Il mio profilo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="account_settings.php">Impostazioni profilo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenuto principale -->
    <main class="py-4">
        <div class="container">
            <?php if (isset($blogResult) && $blogResult->num_rows > 0): ?>
                <section>
                    <h2 class="text-center mb-4">Risultati della ricerca per i blog:</h2>
                    <div class="list-group">
                        <?php while ($row = $blogResult->fetch_assoc()): ?>
                            <a href="#" class="list-group-item list-group-item-action">
                                <h4 class="mb-1"><?php echo htmlspecialchars($row['titolo_blog']); ?></h4>
                                <p class="mb-1"><?php echo htmlspecialchars($row['descrizione']); ?></p>
                                <small>Proprietario: <?php echo htmlspecialchars($row['username']); ?></small>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (isset($postResult) && $postResult->num_rows > 0): ?>
                <section>
                    <h2 class="text-center mb-4">Risultati della ricerca per i post:</h2>
                    <div class="list-group">
                        <?php while ($row = $postResult->fetch_assoc()): ?>
                            <a href="#" class="list-group-item list-group-item-action">
                                <h4 class="mb-1"><?php echo htmlspecialchars($row['titolo_post']); ?></h4>
                                <p class="mb-1"><?php echo htmlspecialchars($row['descrizione_post']); ?></p>
                                <small>Autore: <?php echo htmlspecialchars($row['username']); ?></small>
                                <br>
                                <small>Blog: <?php echo htmlspecialchars($row['titolo_blog']); ?></small>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ((!isset($blogResult) || $blogResult->num_rows === 0) && (!isset($postResult) || $postResult->num_rows === 0)): ?>
                <section>
                    <p class="text-center">Nessun risultato trovato per la ricerca "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>".</p>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <!-- Piede di pagina -->
    <footer class="text-center mt-4 mb-4">
        <p>&copy; <?php echo date('Y'); ?> The Social Network. Tutti I diritti riservati.</p>
    </footer>

    <!-- Bootstrap JS e script necessari -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>