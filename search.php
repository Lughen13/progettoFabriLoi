<?php
require_once 'conn.php';

// Verifica se il form di ricerca è stato inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchQuery = $_POST['search_query'];

    // Esegui la query di ricerca per i blog
    $blogQuery = "SELECT b.id_blog, b.titolo_blog, b.descrizione, u.username
                  FROM blog b
                  JOIN utente u ON b.id_proprietario = u.id_utente
                  WHERE b.titolo_blog LIKE '%$searchQuery%'
                  OR b.descrizione LIKE '%$searchQuery%'
                  OR u.username LIKE '%$searchQuery%'";

    $blogResult = $conn->query($blogQuery);

    // Esegui la query di ricerca per i post
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
<html>
<head>
    <title>Ricerca Blog e Post</title>
</head>
<body>
    <h1>Ricerca Blog e Post</h1>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="text" name="search_query" placeholder="Cerca blog o post" required>
        <button type="submit">Cerca</button>
    </form>

    <?php if (isset($blogResult) && $blogResult->num_rows > 0): ?>
        <h2>Risultati della ricerca per i blog:</h2>
        <ul>
            <?php while ($row = $blogResult->fetch_assoc()): ?>
                <li>
                    <h3><?php echo $row['titolo_blog']; ?></h3>
                    <p><?php echo $row['descrizione']; ?></p>
                    <p>Proprietario: <?php echo $row['username']; ?></p>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>

    <?php if (isset($postResult) && $postResult->num_rows > 0): ?>
        <h2>Risultati della ricerca per i post:</h2>
        <ul>
            <?php while ($row = $postResult->fetch_assoc()): ?>
                <li>
                    <h3><?php echo $row['titolo_post']; ?></h3>
                    <p><?php echo $row['descrizione_post']; ?></p>
                    <p>Autore: <?php echo $row['username']; ?></p>
                    <p>Blog: <?php echo $row['titolo_blog']; ?></p>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
