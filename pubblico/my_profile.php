<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/php_error.log');
session_start();
require_once '../configurazione/conn.php';

// Verifica se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../pubblico/login.php");
    exit();
}

$userId = $_SESSION['id'];  // ID dell'utente autenticato
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Gestione dell'eliminazione del blog
if ($action == 'delete_blog' && isset($_GET['id_blog'])) {
    $id_blog = $_GET['id_blog'];

    // Prima eliminare i post associati al blog
    $deletePostsQuery = "DELETE FROM post WHERE id_blog = ?";
    $stmt = $conn->prepare($deletePostsQuery);
    $stmt->bind_param("i", $id_blog);
    $stmt->execute();
    $stmt->close();

    // Ora eliminare il blog
    $deleteBlogQuery = "DELETE FROM blog WHERE id_blog = ? AND id_proprietario = ?";
    $stmt = $conn->prepare($deleteBlogQuery);
    $stmt->bind_param("ii", $id_blog, $userId);
    if ($stmt->execute()) {
        // Eliminazione del blog eseguita con successo
        header("Location: ../pubblico/my_profile.php");  // Reindirizza alla pagina del profilo
        exit();
    } else {
        echo "Errore durante l'eliminazione del blog: " . $stmt->error;
    }
    $stmt->close();
}

// Gestione dell'aggiornamento della bio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bio'])) {
    $newBio = $_POST['bio'];
    $id_utente = $_SESSION['id'];

    $updateBioQuery = "UPDATE utente SET bio = ? WHERE id_utente = ?";
    $stmt = $conn->prepare($updateBioQuery);
    if ($stmt->execute([$newBio, $id_utente])) {
        // Aggiornamento della bio eseguito con successo
        header('Location: ../pubblico/my_profile.php'); // Redirect alla pagina del profilo
        exit();
    } else {
        echo "Errore durante l'aggiornamento della bio.";
    }
}



// Recupero informazioni utente
$userQuery = "SELECT username, email, nome, cognome, data_nascita, genere, bio, img_profilo, numero_telefono FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();


// Caricamento dell'immagine del profilo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['img_profilo'])) {
    $imgFile = $_FILES['img_profilo'];
    $imgFileName = $imgFile['name'];
    $imgTmpName = $imgFile['tmp_name'];
    $imgSize = $imgFile['size'];
    $imgError = $imgFile['error'];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $imgExtension = strtolower(pathinfo($imgFileName, PATHINFO_EXTENSION));

    if (in_array($imgExtension, $allowedExtensions) && $imgError === 0) {
        $newImgFileName = $user['username'] . '_profilo.' . $imgExtension;
        $imgDestination = '../uploads/' . $newImgFileName;

        if (move_uploaded_file($imgTmpName, $imgDestination)) {
            // Aggiorna l'immagine del profilo nel database
            $updateImgQuery = "UPDATE utente SET img_profilo = ? WHERE id_utente = ?";
            $stmt = $conn->prepare($updateImgQuery);
            $stmt->bind_param('si', $newImgFileName, $userId);
            if ($stmt->execute()) {
                // Aggiornamento dell'immagine del profilo eseguito con successo
                $user['img_profilo'] = $newImgFileName;
            } else {
                echo "Errore durante l'aggiornamento dell'immagine del profilo: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Errore durante il caricamento dell'immagine.";
        }
    } else {
        echo "Formato dell'immagine non valido o errore durante il caricamento.";
    }
}

// Caricamento dei blog dell'utente con il nome della categoria
$blogsQuery = "SELECT b.id_blog, b.titolo_blog, b.descrizione, b.img_logo, b.id_categoria, c.nome_categoria 
               FROM blog b 
               INNER JOIN categoria c ON b.id_categoria = c.id_categoria 
               WHERE b.id_proprietario = ?";
$stmt = $conn->prepare($blogsQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$blogs = $result->fetch_all(MYSQLI_ASSOC);

// Caricamento delle categorie
$categorieQuery = "SELECT id_categoria, nome_categoria FROM categoria";
$stmt = $conn->prepare($categorieQuery);
$stmt->execute();
$result = $stmt->get_result();
$categorie = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Gestione della modifica del blog
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_blog'])) {
    $editBlogId = $_POST['blog_id'];
    $editTitoloBlog = $_POST['edit_titolo_blog'];
    $editDescrizione = $_POST['edit_descrizione'];
    $editCategoriaId = $_POST['edit_categoria'];
    $editImgLogo = '';

    if (isset($_FILES['edit_img_logo']) && $_FILES['edit_img_logo']['error'] === UPLOAD_ERR_OK) {
        $editImgFile = $_FILES['edit_img_logo'];
        $editImgFileName = $editImgFile['name'];
        $editImgTmpName = $editImgFile['tmp_name'];
        $editImgSize = $editImgFile['size'];
        $editImgError = $editImgFile['error'];

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $editImgExtension = strtolower(pathinfo($editImgFileName, PATHINFO_EXTENSION));

        if (in_array($editImgExtension, $allowedExtensions) && $editImgError === 0) {
            $editImgLogo = $editTitoloBlog . '_logo.' . $editImgExtension;
            $editImgDestination = '../blog_logo/' . $editImgLogo;

            if (!move_uploaded_file($editImgTmpName, $editImgDestination)) {
                echo "Errore durante il caricamento dell'immagine.";
            }
        } else {
            echo "Formato dell'immagine non valido o errore durante il caricamento.";
        }
    }

    $editBlogQuery = "UPDATE blog SET titolo_blog = ?, descrizione = ?, id_categoria = ?";
    if (!empty($editImgLogo)) {
        $editBlogQuery .= ", img_logo = ?";
    }
    $editBlogQuery .= " WHERE id_blog = ? AND id_proprietario = ?";

    $stmt = $conn->prepare($editBlogQuery);
    if (!empty($editImgLogo)) {
        $stmt->bind_param('ssisii', $editTitoloBlog, $editDescrizione, $editCategoriaId, $editImgLogo, $editBlogId, $userId);
    } else {
        $stmt->bind_param('ssiii', $editTitoloBlog, $editDescrizione, $editCategoriaId, $editBlogId, $userId);
    }

    if ($stmt->execute()) {
        // Aggiornamento del blog eseguito con successo
        header("Location: ../pubblico/my_profile.php");  // Reindirizza alla pagina del profilo
        exit();
    } else {
        echo "Errore durante l'aggiornamento del blog: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToteBlog</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .form-group label {
            font-weight: bold;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .navbar {
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="container mt-4">
        <h1>ToteBlog</h1>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
         <!-- <a class="navbar-brand" href="#">Il Mio Profilo</a> -->
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
                <form class="form-inline my-2 my-lg-0" action="search.php" method="GET">
                    <input class="form-control mr-sm-2" type="text" name="query" placeholder="Cerca blog o post">
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Cerca</button>
                </form>
            </div>
        </nav>

        <div class="row">
            <div class="col-md-4">
                <h3>Informazioni Personali</h3>
                <?php if (!empty($user['img_profilo'])): ?>
                    <img src="../uploads/<?php echo $user['img_profilo']; ?>" alt="Immagine del profilo" class="img-thumbnail mb-3">
                <?php endif; ?>
                <form id="updateImgForm" action="my_profile.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="img_profilo">Carica nuova immagine profilo</label>
                        <input type="file" class="form-control-file" id="img_profilo" name="img_profilo" accept=".jpg, .jpeg, .png, .gif">
                    </div>
                    <button type="submit" class="btn btn-primary" id="btnUpdateImg" disabled>Aggiorna Immagine</button>
                </form>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($user['nome']); ?></p>
                <p><strong>Cognome:</strong> <?php echo htmlspecialchars($user['cognome']); ?></p>
                <p><strong>Data di Nascita:</strong> <?php echo htmlspecialchars($user['data_nascita']); ?></p>
                <p><strong>Genere:</strong> <?php echo htmlspecialchars($user['genere']); ?></p>
                <p><strong>Numero di Telefono:</strong> <?php echo htmlspecialchars($user['numero_telefono']); ?></p>
                <div class="form-group">
                    <label for="bio">Bio:</label>
                    <p id="currentBio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></p>
                </div>
                <!-- bottone per aprire il modale di modifica della bio  -->
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editBioModal">Modifica Bio</button>

<div class="modal fade" id="editBioModal" tabindex="-1" role="dialog" aria-labelledby="editBioModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBioModalLabel">Modifica Bio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editBioForm" action="my_profile.php" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="bio">Nuova Bio:</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo $user['bio']; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                    <button type="submit" name="update_bio" class="btn btn-primary" id="btnUpdateBio">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>
            </div>

            <div class="col-md-8">
                <h3>I Miei Blog</h3>
                <?php foreach ($blogs as $blog): ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($blog['titolo_blog']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($blog['descrizione']); ?></p>
                            <p class="card-text"><strong>Categoria:</strong> <?php echo htmlspecialchars($blog['nome_categoria']); ?></p>
                            <?php if (!empty($blog['img_logo'])): ?>
                                <img src="../blog_logo/<?php echo htmlspecialchars($blog['img_logo']); ?>" alt="Logo del blog" class="img-thumbnail mb-3">
                            <?php endif; ?>
                            <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#editBlogModal<?php echo $blog['id_blog']; ?>">Modifica</a>
                            <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#deleteBlogModal<?php echo $blog['id_blog']; ?>">Elimina</a>
                            <a href="../pubblico/my_blog.php?id_blog=<?php echo $blog['id_blog']; ?>" class="btn btn-info">Visualizza</a>
                        </div>
                    </div>

                    <!-- Modale di Modifica Blog -->
                    <div class="modal fade" id="editBlogModal<?php echo $blog['id_blog']; ?>" tabindex="-1" role="dialog" aria-labelledby="editBlogModalLabel<?php echo $blog['id_blog']; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editBlogModalLabel<?php echo $blog['id_blog']; ?>">Modifica Blog</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form action="my_profile.php" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="blog_id" value="<?php echo $blog['id_blog']; ?>">
                                        <div class="form-group">
                                            <label for="edit_titolo_blog">Titolo Blog</label>
                                            <input type="text" class="form-control" id="edit_titolo_blog" name="edit_titolo_blog" value="<?php echo htmlspecialchars($blog['titolo_blog']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_descrizione">Descrizione</label>
                                            <textarea class="form-control" id="edit_descrizione" name="edit_descrizione" rows="3"><?php echo htmlspecialchars($blog['descrizione']); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_categoria">Categoria</label>
                                            <select class="form-control" id="edit_categoria" name="edit_categoria">
                                                <?php foreach ($categorie as $categoria): ?>
                                                    <option value="<?php echo $categoria['id_categoria']; ?>" <?php if ($blog['id_categoria'] == $categoria['id_categoria']) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($categoria['nome_categoria']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_img_logo">Carica nuova immagine logo</label>
                                            <input type="file" class="form-control-file" id="edit_img_logo" name="edit_img_logo">
                                        </div>
                                        <button type="submit" name="edit_blog" class="btn btn-primary">Salva modifiche</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modale di Eliminazione Blog -->
                    <div class="modal fade" id="deleteBlogModal<?php echo $blog['id_blog']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteBlogModalLabel<?php echo $blog['id_blog']; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteBlogModalLabel<?php echo $blog['id_blog']; ?>">Elimina Blog</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Sei sicuro di voler eliminare il blog "<?php echo htmlspecialchars($blog['titolo_blog']); ?>"?</p>
                                </div>
                                <div class="modal-footer">
                                    <form action="my_profile.php" method="post">
                                        <input type="hidden" name="blog_id" value="<?php echo $blog['id_blog']; ?>">
                                        <button type="submit" name="delete_blog" class="btn btn-danger">Elimina</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>

                <!-- Bottone per creare un nuovo blog -->
<div class="text-center">
    <a href="../pubblico/create_blog.php" class="btn btn-success btn-lg mt-4">Crea Nuovo Blog</a>
    <a href=" ../pubblico/create_post.php" class="btn btn-success btn-lg mt-4">Crea Nuovo Post</a>
</div>
            </div>
        </div>
    </div>
    
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var inputImg = document.getElementById('img_profilo');
        var btnUpdateImg = document.getElementById('btnUpdateImg');

        inputImg.addEventListener('change', function() {
            if (this.files.length > 0) {
                var fileName = this.files[0].name;
                var extension = fileName.split('.').pop().toLowerCase();
                var allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (allowedExtensions.includes(extension)) {
                    btnUpdateImg.removeAttribute('disabled');
                } else {
                    btnUpdateImg.setAttribute('disabled', 'disabled');
                    alert('Formato dell\'immagine non valido. Sono ammessi solo file JPG, JPEG, PNG o GIF.');
                }
            } else {
                btnUpdateImg.setAttribute('disabled', 'disabled');
            }
        });
    });
</script>
<script>
        $(document).ready(function() {
            $('#bioForm').on('submit', function(e) {
                e.preventDefault();
                var bioData = $(this).serialize();
                $.ajax({
                    type: 'POST',
                    url: 'my_profile.php',
                    data: bioData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Bio aggiornata con successo!');
                            location.reload();
                        } else {
                            alert('Errore durante l\'aggiornamento della bio: ' + response.error);
                        }
                    },
                    error: function() {
                        alert('Errore durante l\'invio della richiesta.');
                    }
                });
            });
        });
    </script>
</body>
</html>