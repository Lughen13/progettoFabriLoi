<?php
<<<<<<< HEAD
require_once 'conn.php';

// Definisci un array associativo con le categorie e le sottocategorie
$categoriesAndSubcategories = [
    'Tecnologia' => [
        'Internet',
        'Hardware',
        'Software',
        'Sicurezza informatica',
    ],
    'Moda' => [
        'Abbigliamento',
        'Accessori',
    ],
    'Lifestyle' => [
        'Benessere',
        'Tempo libero',
    ],
    'Viaggi' => [
        'Destinazioni',
        'Trasporti',
    ],
    'Cucina' => [
        'Ricette',
        'Tecniche di cucina',
    ],
];

// Funzione per creare categorie e sottocategorie
function createCategoriesAndSubcategories($conn, $categoriesAndSubcategories)
{
    foreach ($categoriesAndSubcategories as $categoryName => $subcategories) {
        // Verifica se la categoria esiste già
        $checkCategoryQuery = "SELECT id_categoria FROM categoria WHERE nome_categoria = ?";
        $stmt = $conn->prepare($checkCategoryQuery);
        $stmt->bind_param('s', $categoryName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Crea la categoria
            $sql = "INSERT INTO categoria (nome_categoria) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $categoryName);
            $stmt->execute();

            // Recupera l'ID della categoria appena creata
            $categoryId = $stmt->insert_id;

            // Crea le sottocategorie
            foreach ($subcategories as $subcategoryName) {
                $sql = "INSERT INTO sottocat (id_categoria, nome_sottocat) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('is', $categoryId, $subcategoryName);
                $stmt->execute();
            }
        } else {
            // Recupera l'ID della categoria esistente
            $row = $result->fetch_assoc();
            $categoryId = $row['id_categoria'];

            // Crea le sottocategorie per la categoria esistente
            foreach ($subcategories as $subcategoryName) {
                $sql = "INSERT INTO sottocat (id_categoria, nome_sottocat) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('is', $categoryId, $subcategoryName);
                $stmt->execute();
            }
        }
    }
}

// Connessione al database
require_once 'conn.php';

// Creazione delle categorie e sottocategorie
createCategoriesAndSubcategories($conn, $categoriesAndSubcategories);
?>
=======
// categorie_sottocategorie.php

// Connessione al database
require_once 'conn.php';

// Funzione per ottenere le categorie
function getCategories($conn) {
    $sql = "SELECT id_categoria, nome_categoria FROM categoria ORDER BY nome_categoria";
    $result = $conn->query($sql);
    $categories = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

// Funzione per ottenere le sottocategorie di una categoria specifica
function getSubcategories($conn, $categoria) {
    $sql = "SELECT * FROM sottocat WHERE id_categoria = (SELECT id_categoria FROM categoria WHERE nome_categoria = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $categoria);
    $stmt->execute();
    $result = $stmt->get_result();
    $subcategories = [];
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }
    return $subcategories;
}

// Inizializza la sessione per gestire l'autenticazione dell'utente
session_start();

// Controlla se l'utente è autenticato
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Recupera i dati dell'utente dalla sessione
$username = $_SESSION['username'];
$genere = $_SESSION['genere'];

// Funzione per ottenere il saluto in base al genere
function getSaluto($genere) {
    switch ($genere) {
        case 'Maschio':
            return 'Benvenuto';
        case 'Femmina':
            return 'Benvenuta';
        case 'Altro':
            return 'Benvenut*';
        default:
            return 'Benvenut*'; // Default a Benvenut*
    }
}

// Ottieni il saluto appropriato
$saluto = getSaluto($genere);

// Messaggi di errore e successo
$errorMessage = '';
$successMessage = '';

// Gestione del form di creazione della categoria
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['crea_categoria'])) {
        $nuova_categoria = $_POST['nuova_categoria'];
        $sql = "INSERT INTO categoria (nome_categoria) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nuova_categoria);
        if ($stmt->execute()) {
            $successMessage = "Categoria '$nuova_categoria' creata con successo!";
        } else {
            $errorMessage = "Errore durante la creazione della categoria: " . $stmt->error;
        }
    } elseif (isset($_POST['crea_sottocategoria'])) {
        $categoria_sottocategoria = $_POST['categoria_sottocategoria'];
        $nuova_sottocategoria = $_POST['nuova_sottocategoria'];
        $sql = "INSERT INTO sottocat (id_categoria, nome_sottocat) VALUES ((SELECT id_categoria FROM categoria WHERE nome_categoria = ?), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $categoria_sottocategoria, $nuova_sottocategoria);
        if ($stmt->execute()) {
            $successMessage = "Sottocategoria '$nuova_sottocategoria' creata con successo per la categoria '$categoria_sottocategoria'!";
        } else {
            $errorMessage = "Errore durante la creazione della sottocategoria: " . $stmt->error;
        }
    }
}

// Recupera le categorie esistenti
$categories = getCategories($conn);

// Recupera le sottocategorie esistenti
$subcategories = [];
if (!empty($_POST['categoria'])) {
    $categoria_selezionata = $_POST['categoria'];
    $subcategories = getSubcategories($conn, $categoria_selezionata);
}

// Chiudi la connessione
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Categorie e Sottocategorie</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1><?php echo $saluto . ', ' . $username; ?>, gestisci le categorie e le sottocategorie</h1>

    <h2>Creazione di una nuova categoria</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="nuova_categoria">Nuova categoria:</label>
        <input type="text" id="nuova_categoria" name="nuova_categoria" required>
        <button type="submit" name="crea_categoria">Crea Categoria</button>
    </form>

    <h2>Creazione di una nuova sottocategoria</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="categoria_sottocategoria">Categoria:</label>
        <select id="categoria_sottocategoria" name="categoria_sottocategoria" required>
            <option value="">Seleziona una categoria</option>
            <?php foreach ($categories as $categoria): ?>
                <option value="<?php echo $categoria['nome_categoria']; ?>"><?php echo $categoria['nome_categoria']; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="nuova_sottocategoria">Nuova sottocategoria:</label>
        <input type="text" id="nuova_sottocategoria" name="nuova_sottocategoria" required>
        <button type="submit" name="crea_sottocategoria">Crea Sottocategoria</button>
    </form>

    <h2>Messaggi</h2>
    <p class="error"><?php echo $errorMessage; ?></p>
    <p class="success"><?php echo $successMessage; ?></p>

    <hr>

    <h2>Filtra le sottocategorie per categoria</h2>
    <form id="filterForm">
        <label for="categoria">Seleziona una categoria:</label>
        <select id="categoria" name="categoria">
            <option value="">Seleziona una categoria</option>
            <?php foreach ($categories as $categoria): ?>
                <option value="<?php echo $categoria['nome_categoria']; ?>"><?php echo $categoria['nome_categoria']; ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <h2>Sottocategorie disponibili:</h2>
    <select id="sottocategorie">
        <option value="">Seleziona una categoria per visualizzare le sottocategorie</option>
        <?php foreach ($subcategories as $sottocategoria): ?>
            <option value="<?php echo $sottocategoria['nome_sottocat']; ?>"><?php echo $sottocategoria['nome_sottocat']; ?></option>
        <?php endforeach; ?>
    </select>

    <script>
        $(document).ready(function() {
            $('#categoria').change(function() {
                var categoria = $(this).val();
                $.ajax({
                    url: 'get_sottocategorie.php',
                    method: 'POST',
                    data: {categoria: categoria},
                    success: function(response) {
                        $('#sottocategorie').html(response);
                    }
                });
            });
        });
    </script>
</body>
</html>
>>>>>>> cd95c6220dc8cff006e3afd0baaa040e5dbe9afe
