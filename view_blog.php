<?php

include 'conn.php';
session_start();

// Controlla se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Recupera l'id dell'utente dalla sessione
$userId = $_SESSION['id'];

// Recupera l'id del blog dalla query string
$id_blog = isset($_GET['id_blog']) ? $_GET['id_blog'] : null;

// Recupera i dettagli del blog
$query = "SELECT b.titolo_blog, b.descrizione, b.img_logo, p.id_post, p.titolo_post, p.descrizione_post, p.img_post
          FROM blog b
          LEFT JOIN post p ON b.id_blog = p.id_blog
          WHERE b.id_blog = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_blog);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();
$stmt->close();

// Gestione dell'inserimento del commento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['comment']) && !empty($_POST['comment'])) {
        $comment = $_POST['comment'];
        $postId = $_POST['post_id'];

        // Inserisci il commento nel database
        $insertCommentQuery = "INSERT INTO commento (data_comm, contenuto, id_utente, id_post) VALUES (NOW(), ?, ?, ?)";
        $stmt = $conn->prepare($insertCommentQuery);
        $stmt->bind_param("sii", $comment, $userId, $postId);
        if ($stmt->execute()) {
            // Commento inserito con successo
            $stmt->close();
            header("Location: view_blog.php?id_blog={$id_blog}");
            exit;
        } else {
            echo "Errore durante l'inserimento del commento: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Recupera i commenti relativi ai post del blog
$queryComments = "SELECT c.id_comm, c.data_comm, c.contenuto, u.username, p.id_post
                  FROM commento c
                  INNER JOIN utente u ON c.id_utente = u.id_utente
                  INNER JOIN post p ON c.id_post = p.id_post
                  WHERE p.id_blog = ?";
$stmt = $conn->prepare($queryComments);
$stmt->bind_param("i", $id_blog);
$stmt->execute();
$resultComments = $stmt->get_result();
$comments = $resultComments->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Visualizza Blog</title>
</head>    
<body>
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

    <h1>Benvenuto nella home di <?php echo $blog['titolo_blog']; ?></h1>
    <p><?php echo $blog['descrizione']; ?></p>
    <img src="uploads/<?php echo $blog['img_logo']; ?>" alt="Logo del Blog" width="100">

    <?php if (!empty($blog['id_post'])): ?>
        <h2>Post:</h2>
        <ul>
            <?php foreach ($result as $post): ?>
                <li>
                    <h3><?php echo $post['titolo_post']; ?></h3>
                    <p><?php echo $post['descrizione_post']; ?></p>
                    <img src="uploads/<?php echo $post['img_post']; ?>" alt="Immagine del Post" width="100">
                    
                    <h4>Commenti:</h4>
                    <?php foreach ($comments as $comment): ?>
                        <?php if ($comment['id_post'] == $post['id_post']): ?>
                            <p><strong><?php echo $comment['username']; ?></strong> (<?php echo $comment['data_comm']; ?>): <?php echo $comment['contenuto']; ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <form method="post">
                        <input type="hidden" name="post_id" value="<?php echo $post['id_post']; ?>">
                        <textarea name="comment" placeholder="Inserisci il tuo commento"></textarea>
                        <button type="submit">Inserisci Commento</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Questo blog non ha ancora post.</p>
    <?php endif; ?>
</body>
</html>