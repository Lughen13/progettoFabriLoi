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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_blog') {
    $blogId = $_POST['id_blog'];
    $titoloBlog = $_POST['edit_titolo_blog'];
    $descrizione = $_POST['edit_descrizione'];
    $coautoreId = !empty($_POST['edit_coautore']) ? $_POST['edit_coautore'] : null;
    
    $logoName = null;
    if (isset($_FILES['edit_logo']) && $_FILES['edit_logo']['error'] === UPLOAD_ERR_OK) {
        $logoFile = $_FILES['edit_logo'];
        $logoName = 'blog_logo_' . $blogId . '.' . pathinfo($logoFile['name'], PATHINFO_EXTENSION);
        move_uploaded_file($logoFile['tmp_name'], '../blog_logo/' . $logoName);
    }

    $updateBlogQuery = "UPDATE blog SET titolo_blog = ?, descrizione = ?" . ($logoName ? ", img_logo = ?" : "") . " WHERE id_blog = ? AND id_proprietario = ?";
    $stmt = $conn->prepare($updateBlogQuery);
    
    if ($logoName) {
        $stmt->bind_param("sssii", $titoloBlog, $descrizione, $logoName, $blogId, $userId);
    } else {
        $stmt->bind_param("ssii", $titoloBlog, $descrizione, $blogId, $userId);
    }
    
    $blogsQuery = "SELECT b.*, c.nome_categoria, co.id_utente AS id_coautore, u.username AS coautore_username
    FROM blog b 
    LEFT JOIN categoria c ON b.id_categoria = c.id_categoria
    LEFT JOIN co_autore co ON b.id_blog = co.id_blog
    LEFT JOIN utente u ON co.id_utente = u.id_utente
    WHERE b.id_proprietario = ?";


    if ($stmt->execute()) {
        if ($coautoreId) {  // Controlla se esiste già un coautore per questo blog
            $checkCoautoreQuery = "SELECT id_utente FROM co_autore WHERE id_blog = ?";
            $stmtCheck = $conn->prepare($checkCoautoreQuery);
            $stmtCheck->bind_param("i", $blogId);
            $stmtCheck->execute();
            $result = $stmtCheck->get_result();
        
            if ($result->num_rows > 0) { // Aggiorna il coautore esistente
                $updateCoautoreQuery = "UPDATE co_autore SET id_utente = ? WHERE id_blog = ?";
                $stmtCoautore = $conn->prepare($updateCoautoreQuery);
                $stmtCoautore->bind_param("ii", $coautoreId, $blogId);
            } else { // Inserisci un nuovo coautore
                $insertCoautoreQuery = "INSERT INTO co_autore (id_blog, id_utente) VALUES (?, ?)";
                $stmtCoautore = $conn->prepare($insertCoautoreQuery);
                $stmtCoautore->bind_param("ii", $blogId, $coautoreId);
            }
            $stmtCoautore->execute();
        } else { // Rimuovi il coautore se è stato selezionato "Nessun coautore"
            $deleteCoautoreQuery = "DELETE FROM co_autore WHERE id_blog = ?";
            $stmtDeleteCoautore = $conn->prepare($deleteCoautoreQuery);
            $stmtDeleteCoautore->bind_param("i", $blogId);
            $stmtDeleteCoautore->execute();
        }
        
        
        echo "Blog aggiornato con successo!";
    } else {
        echo "Errore durante l'aggiornamento del blog: " . $stmt->error;
    }
    exit();
}

// Gestione dell'eliminazione del blog
if ($action == 'delete_blog') {
    $id_blog = $_GET['id_blog'];
    // Prima eliminare le notifiche associate al blog e ai post del blog
    $deleteNotificationsQuery = "DELETE FROM notifiche WHERE contenuto_id = ? OR contenuto_id IN (SELECT id_post FROM post WHERE id_blog = ?)";
    $stmt = $conn->prepare($deleteNotificationsQuery);
    $stmt->bind_param("ii", $id_blog, $id_blog);
    $stmt->execute();
    $stmt->close();
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
            // Update the database with the new image filename
            $updateImgQuery = "UPDATE utente SET img_profilo = ? WHERE id_utente = ?";
            $stmt = $conn->prepare($updateImgQuery);
            $stmt->bind_param('si', $newImgFileName, $userId);
            $stmt->execute();
            $stmt->close();

            // Update the user array with the new image filename
            $user['img_profilo'] = $newImgFileName;
        }
    }
}

// query per i blog dell'utente con il recupero della categoria, da mostrare 
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

$utentiQuery = "SELECT id_utente, username FROM utente WHERE id_utente != ?";
$stmtUtenti = $conn->prepare($utentiQuery);
$stmtUtenti->bind_param("i", $userId);
$stmtUtenti->execute();
$utentiResult = $stmtUtenti->get_result();
$utenti = $utentiResult->fetch_all(MYSQLI_ASSOC);

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

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown">
                                <h6 class="dropdown-header">Notifiche recenti</h6>
                                <div id="notificationList">
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="row">
            <div class="col-md-4">
                <h3>Informazioni Personali</h3>
                <?php if (!empty($user['img_profilo'])) : ?>
                    <img src="../uploads/<?php echo htmlspecialchars($user['img_profilo']); ?>?v=<?php echo time(); ?>" alt="Immagine del profilo di <?php echo htmlspecialchars($user['username']); ?>" class="img-profilo" style="max-width: 250px">
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
                <?php foreach ($blogs as $blog) : ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($blog['titolo_blog']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($blog['descrizione']); ?></p>
                            <p class="card-text"><strong>Categoria:</strong> <?php echo htmlspecialchars($blog['nome_categoria']); ?></p>
                            <?php if (!empty($blog['img_logo'])) : ?>
                                <img src="../blog_logo/<?php echo htmlspecialchars($blog['img_logo']); ?>" alt="Logo del blog '<?php echo htmlspecialchars($blog['titolo_blog']); ?>'" class="img-thumbnail mb-3">
                            <?php endif; ?>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#editBlogModal<?php echo $blog['id_blog']; ?>">Modifica Blog</button>
                            <button class="btn btn-danger" data-toggle="modal" data-target="#deleteBlogModal<?php echo $blog['id_blog']; ?>">Elimina</button>
                            <button class="btn btn-info" onclick="location.href='../pubblico/my_blog.php?id_blog=<?php echo $blog['id_blog']; ?>'">Visualizza</button>
                        </div>
                    </div>
                    
                    <!-- Modale per modificare il blog -->
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
                                    <form id="editBlogForm<?php echo $blog['id_blog']; ?>" enctype="multipart/form-data">
                                        <input type="hidden" name="id_blog" value="<?php echo $blog['id_blog']; ?>">
                                        <div class="form-group">
                                            <label for="edit_titolo_blog<?php echo $blog['id_blog']; ?>">Titolo Blog</label>
                                            <input type="text" class="form-control" id="edit_titolo_blog<?php echo $blog['id_blog']; ?>" name="edit_titolo_blog" value="<?php echo htmlspecialchars($blog['titolo_blog']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_descrizione<?php echo $blog['id_blog']; ?>">Descrizione</label>
                                            <textarea class="form-control" id="edit_descrizione<?php echo $blog['id_blog']; ?>" name="edit_descrizione" rows="3" required><?php echo htmlspecialchars($blog['descrizione']); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_coautore<?php echo $blog['id_blog']; ?>">Coautore</label>
                                            <div class="form-group">
                                                <label for="edit_coautore<?php echo $blog['id_blog']; ?>">Coautore</label>
                                                <select class="form-control" id="edit_coautore<?php echo $blog['id_blog']; ?>" name="edit_coautore">
                                                    <option value="">Nessun coautore</option>
                                                    <?php foreach ($utenti as $utente): ?>
                                                        <option value="<?php echo $utente['id_utente']; ?>" 
                                                            <?php echo (isset($blog['id_coautore']) && $blog['id_coautore'] == $utente['id_utente']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($utente['username']); ?>
                                                            <?php echo (isset($blog['id_coautore']) && $blog['id_coautore'] == $utente['id_utente']) ? ' (Coautore attuale)' : ''; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_logo<?php echo $blog['id_blog']; ?>">Logo del Blog</label>
                                            <input type="file" class="form-control-file" id="edit_logo<?php echo $blog['id_blog']; ?>" name="edit_logo">
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                                    <button type="button" class="btn btn-primary" onclick="updateBlog(<?php echo $blog['id_blog']; ?>)">Salva modifiche</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- per eliminare il blog -->
                    <div class="modal fade" id="deleteBlogModal<?php echo $blog['id_blog']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteBlogModalLabel<?php echo $blog['id_blog']; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteBlogModalLabel<?php echo $blog['id_blog']; ?>">Elimina Blog</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times; </button>
                                </div>
                                <div class="modal-body">
                                    <p>Sei sicuro di voler eliminare il blog "<?php echo htmlspecialchars($blog['titolo_blog']); ?>"?</p>
                                </div>
                                <div class="modal-footer">
                                    <form action="my_profile.php" method="get">
                                        <input type="hidden" name="action" value="delete_blog">
                                        <input type="hidden" name="id_blog" value="<?php echo $blog['id_blog']; ?>">
                                        <button type="submit" class="btn btn-danger">Elimina Blog</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="text-center mt-4">
                    <button class="btn btn-success btn-lg" onclick="location.href='../pubblico/create_blog.php'">Crea Nuovo Blog</button>
                    <button class="btn btn-success btn-lg" onclick="location.href='../pubblico/create_post.php'">Crea Nuovo Post</button>
                </div>
            </div>
        </div>



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
                        alert('Inserisci del testo nella bio!');
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            var originalBio = "<?php echo htmlspecialchars($user['bio'] ?? ''); ?>";
            // Funzione per verificare se la bio è stata modificata
            function isBioChanged() {
                var currentBio = $('#bio').val().trim();
                return currentBio !== originalBio;
            }
            // Verifica al caricamento del modale
            $('#editBioModal').on('show.bs.modal', function() {
                $('#bio').val(originalBio);
                $('#btnUpdateBio').prop('disabled', true); // Disabilita il pulsante Salva inizialmente
            });

            // Verifica il cambiamento nel campo bio
            $('#bio').on('input', function() {
                var bioChanged = isBioChanged();
                if (bioChanged) {
                    $('#btnUpdateBio').prop('disabled', false); // Abilita il pulsante Salva se la bio è stata modificata
                } else {
                    $('#btnUpdateBio').prop('disabled', true); // Disabilita il pulsante Salva se non ci sono modifiche
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            function fetchNotifications() {
                $.ajax({
                    url: '../risorse/get_notifications.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        var notificationList = $('#notificationList');
                        notificationList.empty();

                        if (data.length === 0) {
                            notificationList.append('<a class="dropdown-item">Non ci sono notifiche.</a>');
                        } else {
                            data.forEach(function(notification) {
                                var listItem = '<a class="dropdown-item">';
                                listItem += '<strong>' + notification.sender_username + '</strong> ';
                                if (notification.tipo === 'comment') {
                                    listItem += 'ha commentato il tuo post: ';
                                    listItem += '<a href="my_post.php?id_post=' + notification.contenuto_id + '">' + notification.contenuto_titolo + '</a>';
                                } else if (notification.tipo === 'like') {
                                    listItem += 'ha messo mi piace al tuo post: ';
                                    listItem += '<a href="my_post.php?id_post=' + notification.id_blog + '&id_post=' + notification.contenuto_id + '">' + notification.contenuto_titolo + '</a>';
                                } else if (notification.tipo === 'follow') {
                                    listItem += 'ha iniziato a seguirti nel blog: ';
                                    listItem += '<a href="my_blog.php?id_blog=' + notification.contenuto_id + '">' + notification.contenuto_titolo + '</a>';
                                } else {
                                    listItem += 'ha eseguito un\'azione.';
                                }
                                listItem += '<br><small>' + notification.data + '</small>';
                                listItem += '</a>';
                                notificationList.append(listItem);
                            });
                        }
                    },
                    error: function() {
                        $('#notificationList').append('<a class="dropdown-item text-danger">Errore nel caricamento delle notifiche.</a>');
                    }
                });
            }
            // Carica le notifiche all'apertura del dropdown
            $('#notificationDropdown').on('click', function() {
                fetchNotifications();
            });
        });
        
        function updateBlog(blogId) {
            var form = $('#editBlogForm' + blogId)[0];
            var formData = new FormData(form);
            formData.append('action', 'edit_blog');

            $.ajax({
                url: 'my_profile.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    alert(response);
                    $('#editBlogModal' + blogId).modal('hide');
                    location.reload();
                },
                error: function() {
                    alert('Si è verificato un errore durante la richiesta.');
                }
            });
        }

   <script>
    
</body>
</html>