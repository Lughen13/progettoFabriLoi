<?php
require_once '../configurazione/conn.php';
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../pubblico/login.php");
    exit();
}

$userId = $_SESSION['id'];
$blogId = isset($_GET['id']) ? $_GET['id'] : null;
$source = isset($_GET['source']) ? $_GET['source'] : 'profile';

// Recupera i dettagli del blog
$blogQuery = "SELECT * FROM blog WHERE id_blog = ? AND id_proprietario = ?";
$stmt = $conn->prepare($blogQuery);
$stmt->bind_param("ii", $blogId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();

// Recupera le categorie
$categorieQuery = "SELECT id_categoria, nome_categoria FROM categoria";
$categorieResult = $conn->query($categorieQuery);

// Recupera gli utenti per il coautore
$utentiQuery = "SELECT id_utente, username FROM utente WHERE id_utente != ?";
$stmtUtenti = $conn->prepare($utentiQuery);
$stmtUtenti->bind_param("i", $userId);
$stmtUtenti->execute();
$utentiResult = $stmtUtenti->get_result();

// Gestione dell'invio del form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titoloBlog = $_POST['edit_titolo_blog'];
    $descrizione = $_POST['edit_descrizione'];
    $categoriaId = $_POST['edit_categoria'];
    $coautoreId = !empty($_POST['edit_coautore']) ? $_POST['edit_coautore'] : null;

    // Gestione dell'upload del logo
    $logoName = $blog['img_logo']; // Mantieni il logo esistente come default
    if (isset($_FILES['edit_logo']) && $_FILES['edit_logo']['error'] == 0) {
        $logoName = 'blog_logo_' . $blogId . '.' . pathinfo($_FILES['edit_logo']['name'], PATHINFO_EXTENSION);
        $logoPath = '../blog_logo/' . $logoName;
        move_uploaded_file($_FILES['edit_logo']['tmp_name'], $logoPath);
    }

    // Aggiorna il blog nel database
    $updateBlogQuery = "UPDATE blog SET titolo_blog = ?, descrizione = ?, id_categoria = ?, img_logo = ? WHERE id_blog = ? AND id_proprietario = ?";
    $stmtUpdate = $conn->prepare($updateBlogQuery);
    $stmtUpdate->bind_param("ssssii", $titoloBlog, $descrizione, $categoriaId, $logoName, $blogId, $userId);
    

    if ($stmtUpdate->execute()) {
        // Gestione del coautore
        if ($coautoreId) {
            $coautoreQuery = "INSERT INTO co_autore (id_blog, id_utente) VALUES (?, ?) ON DUPLICATE KEY UPDATE id_utente = ?";
            $stmtCoautore = $conn->prepare($coautoreQuery);
            $stmtCoautore->bind_param("iii", $blogId, $coautoreId, $coautoreId);
            $stmtCoautore->execute();
        } else {
            $deleteCoautoreQuery = "DELETE FROM co_autore WHERE id_blog = ?";
            $stmtDeleteCoautore = $conn->prepare($deleteCoautoreQuery);
            $stmtDeleteCoautore->bind_param("i", $blogId);
            $stmtDeleteCoautore->execute();
        }
    
        if ($source === 'blog') {
            header("Location: ../pubblico/my_blog.php?id_blog=" . $blogId);
        } else {
            header("Location: ../pubblico/my_profile.php");
        }
        exit();
    } else {
        $error = "Errore durante l'aggiornamento del blog: " . $conn->error;
    }

}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Blog</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Modifica Blog</h2>
        <form action="process_update_blog.php?id=<?php echo $blogId; ?>" method="post" enctype="multipart/form-data">
        <form action="process_update_blog.php?id=<?php echo $blogId; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="source" value="<?php echo htmlspecialchars($source); ?>">    
        <div class="form-group">
                <label for="edit_titolo_blog">Titolo Blog</label>
                <input type="text" class="form-control" id="edit_titolo_blog" name="edit_titolo_blog" value="<?php echo htmlspecialchars($blog['titolo_blog']); ?>" required>
            </div>
            <div class="form-group">
                <label for="edit_descrizione">Descrizione</label>
                <textarea class="form-control" id="edit_descrizione" name="edit_descrizione" rows="3" required><?php echo htmlspecialchars($blog['descrizione']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="edit_categoria">Categoria</label>
                <select class="form-control" id="edit_categoria" name="edit_categoria" required>
                    <?php while ($categoria = $categorieResult->fetch_assoc()): ?>
                        <option value="<?php echo $categoria['id_categoria']; ?>" <?php if ($blog['id_categoria'] == $categoria['id_categoria']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_coautore">Coautore</label>
                <select class="form-control" id="edit_coautore" name="edit_coautore">
                <option value="">Nessun coautore</option>
                    <?php while ($utente = $utentiResult->fetch_assoc()): ?>
                        <option value="<?php echo $utente['id_utente']; ?>" 
                            <?php if (isset($blog['id_coautore']) && $blog['id_coautore'] == $utente['id_utente']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($utente['username']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="edit_logo">Logo del Blog</label>
                <input type="file" class="form-control-file" id="edit_logo" name="edit_logo">
                <?php if (!empty($blog['img_logo'])): ?>
                    <img src="../blog_logo/<?php echo htmlspecialchars($blog['img_logo']); ?>" alt="Logo attuale" class="mt-2" style="max-width: 200px;">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Salva modifiche</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
