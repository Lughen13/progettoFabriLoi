<?php
require_once 'conn.php';

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Verifica se l'utente è loggato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}


//$userId = $_SESSION['user_id'];

// Recupera le informazioni dell'utente
$userQuery = "SELECT * FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Recupera le statistiche dell'utente
function getPostCount($userId) {
    global $conn;
    $query = "SELECT COUNT(*) AS post_count
              FROM post p
              JOIN blog b ON p.id_blog = b.id_blog
              WHERE b.id_proprietario = ? OR b.id_blog IN (
                  SELECT id_blog
                  FROM co_autore
                  WHERE id_utente = ?
              )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['post_count'];
}

$postCount = getPostCount($userId);

function getFollowingCount($userId) {
    global $conn;
    $query = "SELECT COUNT(*) AS following_count
              FROM follow
              WHERE id_utente = ?"; // Supponiamo che la colonna per l'ID dell'utente sia "id_utente"
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['following_count'];
}


$followingCount = getFollowingCount($userId);

/*function getLikesCount($userId) {
    global $conn;
    $query = "SELECT SUM(CASE WHEN like_value = 1 THEN 1 ELSE 0 END) AS likes_count
              FROM 'like' 
              JOIN post p ON l.id_post = p.id_post  
              JOIN blog b ON p.id_blog = b.id_blog
              WHERE b.id_proprietario = ? OR b.id_blog IN (
                  SELECT id_blog
                  FROM co_autore
                  WHERE id_utente = ?
              )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['likes_count'];
              }

function getCommentsCount($userId) {
    global $conn;
    $query = "SELECT COUNT(*) AS comments_count 
              FROM commento c
              JOIN post p ON c.id_post = p.id_post
              JOIN blog b ON p.id_blog = b.id_blog
              WHERE b.id_proprietario = ? OR b.id_blog IN (
                  SELECT id_blog
                  FROM co_autore
                  WHERE id_utente = ?
              )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['comments_count'];
}

$commentsCount = getCommentsCount($userId);


// Recupera il feed di post recenti
$feedQuery = "SELECT p.*, u.username, b.titolo_blog
              FROM post p
              JOIN utente u ON p.id_autore = u.id_utente
              JOIN blog b ON p.id_blog = b.id_blog
              WHERE p.id_blog IN (
                  SELECT id_blog
                  FROM follow
                  WHERE id_utente = ?
              )
              ORDER BY p.data_post DESC
              LIMIT 10";
$stmt = $conn->prepare($feedQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$feedResult = $stmt->get_result();

// Recupera i blog dell'utente
$blogsQuery = "SELECT b.id_blog, b.titolo_blog, b.descrizione, b.img_logo
               FROM blog b
               WHERE b.id_proprietario = ? OR b.id_blog IN (
                   SELECT id_blog
                   FROM co_autore
                   WHERE id_utente = ?
               )";
$stmt = $conn->prepare($blogsQuery);
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$blogsResult = $stmt->get_result();

// Recupera le notifiche dell'utente
/*$notificationsQuery = "SELECT n.*, p.titolo_post, u.username
                       FROM notifica n
                       JOIN post p ON n.id_post = p.id_post
                       JOIN utente u ON p.id_autore = u.id_utente
                       WHERE n.id_utente = ? AND n.letto = 0
                       ORDER BY n.data_notifica DESC";
$stmt = $conn->prepare($notificationsQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$notificationsResult = $stmt->get_result();*/
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <!-- Includi fogli di stile e script JavaScript qui -->
</head>
<body>
    <header>
        <nav>
            <!-- Menu di navigazione -->
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="my_blogs.php">I Miei Blog</a></li>
                <li><a href="create_blog.php">Crea Nuovo Blog</a></li>
                <li><a href="create_post.php">Crea Nuovo Post</a></li>
                <li><a href="account_settings.php">Impostazioni Account</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
            <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="Cerca blog o post">
                <button type="submit">Cerca</button>
            </form>
        </nav>
    </header>

    <main>
        <section>
            <h1>Benvenuto, <?php echo $user['username']; ?>!</h1>
            <p><?php echo $user['bio']; ?></p>
            <img src="<?php echo $user['img_profilo']; ?>" alt="Immagine del profilo">
        </section>

        <section>
            <h2>Statistiche</h2>
            <ul>
                <li>Post pubblicati: <?php echo $postCount; ?></li>
                <li>Blog seguiti: <?php echo $followingCount; ?></li>
                <li>Like ricevuti: <?php echo $likesCount; ?></li>
                <li>Commenti ricevuti: <?php echo $commentsCount; ?></li>
            </ul>
        </section>

        <section>
            <h2>Feed di Post Recenti</h2>
            <?php while ($post = $feedResult->fetch_assoc()): ?>
                <div>
                    <h3><?php echo $post['titolo_post']; ?></h3>
                    <p><?php echo $post['descrizione_post']; ?></p>
                    <p>Autore: <?php echo $post['username']; ?></p>
                    <p>Blog: <?php echo $post['titolo_blog']; ?></p>
                    <p>Data: <?php echo $post['data_post']; ?></p>
                    <?php if (!empty($post['img_post'])): ?>
                        <img src="<?php echo $post['img_post']; ?>" alt="Immagine del post">
                    <?php endif; ?>
                    <a href="post_details.php?post_id=<?php echo $post['id_post']; ?>">Visualizza dettagli</a>
                </div>
            <?php endwhile; ?>
        </section>

        <section>
            <h2>I Miei Blog</h2>
            <?php while ($blog = $blogsResult->fetch_assoc()): ?>
                <div>
                    <h3><?php echo $blog['titolo_blog']; ?></h3>
                    <p><?php echo $blog['descrizione']; ?></p>
                    <?php if (!empty($blog['img_logo'])): ?>
                        <img src="<?php echo $blog['img_logo']; ?>" alt="Logo del blog">
                    <?php endif; ?>
                    <a href="blog_details.php?blog_id=<?php echo $blog['id_blog']; ?>">Visualizza dettagli</a>
                    <a href="edit_blog.php?blog_id=<?php echo $blog['id_blog']; ?>">Modifica</a>
                    <a href="delete_blog.php?blog_id=<?php echo $blog['id_blog']; ?>">Elimina</a>
                </div>
            <?php endwhile; ?>
        </section>

        <section>
            <h2>Notifiche</h2>
            <!-- Implementa la logica per visualizzare le notifiche qui -->
            <?php while ($notification = $notificationsResult->fetch_assoc()): ?>
                <div>
                    <p><?php echo $notification['username']; ?> ha <?php echo $notification['tipo']; ?> il tuo post "<?php echo $notification['titolo_post']; ?>"</p>
                    <p>Data: <?php echo $notification['data_notifica']; ?></p>
                </div>
            <?php endwhile; ?>
        </section>

        <section>
            <h2>Suggerimenti e Consigli</h2>
            <p>Ecco alcuni suggerimenti per sfruttare al meglio il nostro sito di blog:</p>
            <ul>
                <li>Crea un nuovo blog per condividere i tuoi interessi e passioni.</li>
                <li>Segui altri blog per rimanere aggiornato sugli argomenti che ti interessano.</li>
                <li>Interagisci con gli altri utenti commentando e mettendo "mi piace" ai loro post.</li>
                <li>Personalizza le impostazioni del tuo account per una migliore esperienza.</li>
            </ul>
        </section>
    </main>

    <footer>
        <!-- Piè di pagina -->
    </footer>

    <!-- Includi script JavaScript qui -->
</body>
</html>
