<?php
require_once 'conn.php';
session_start();

// Verifica se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$blogId = $_GET['blog_id'];

// Recupera i dettagli del blog
$blogQuery = "SELECT b.*, u.username, c.descrizione AS category_name
              FROM blog b
              JOIN utente u ON b.id_proprietario = u.id_utente
              JOIN categoria c ON b.id_categoria = c.id_categoria
              WHERE b.id_blog = $blogId";
$blogResult = $conn->query($blogQuery);
$blog = $blogResult->fetch_assoc();

// Recupera i post del blog
$postsQuery = "SELECT p.*, u.username
               FROM post p
               JOIN utente u ON p.id_autore = u.id_utente
               WHERE p.id_blog = $blogId
               ORDER BY p.data_post DESC";
$postsResult = $conn->query($postsQuery);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $blog['titolo_blog']; ?></title>
</head>
<body>
    <h1><?php echo $blog['titolo_blog']; ?></h1>
    <p><?php echo $blog['descrizione']; ?></p>
    <p>Proprietario: <?php echo $blog['username']; ?></p>
    <p>Categoria: <?php echo $blog['category_name']; ?></p>
    <?php if (!empty($blog['img_logo'])): ?>
        <img src="<?php echo $blog['img_logo']; ?>" alt="Logo del blog">
    <?php endif; ?>

    <h2>Post</h2>
    <?php while ($post = $postsResult->fetch_assoc()): ?>
        <div>
            <h3><?php echo $post['titolo_post']; ?></h3>
            <p><?php echo $post['descrizione_post']; ?></p>
            <p>Autore: <?php echo $post['username']; ?></p>
            <p>Data: <?php echo $post['data_post']; ?></p>
            <?php if (!empty($post['img_post'])): ?>
                <img src="<?php echo $post['img_post']; ?>" alt="Immagine del post">
            <?php endif; ?>
            <a href="post_details.php?post_id=<?php echo $post['id_post']; ?>">Visualizza dettagli</a>
        </div>
    <?php endwhile; ?>
</body>
</html>
