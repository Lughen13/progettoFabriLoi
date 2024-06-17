<?php
require_once 'conn.php';


// Inserisci  categorie standard
$categories = array(
    "Tecnologia",
    "Moda",
    "Viaggi",
    "Cucina",
    "Salute",
    "Economia",
    "Notizie",
    "Sport",
    "Educazione",
    "Affari",
    "Musica",
    "Arte",
    "cultura",
    "Ambiente",
    "Automotive",
    "Lifestyle"
);

// Preparare la query SQL
$sql = "INSERT INTO categoria (nome_categoria) VALUES (?)";

// Preparare la dichiarazione
$stmt = $conn->prepare($sql);

// Controllare se la preparazione è andata a buon fine
if ($stmt === false) {
    die("Errore nella preparazione della query: " . $conn->error);
}

// Legare i parametri
$stmt->bind_param("s", $descrizione);

// Eseguire la query per ogni categoria nell'array
foreach ($categories as $categoria) {
    $descrizione = $categoria;
    if ($stmt->execute() === false) {
        echo "Errore nell'inserimento della categoria: " . $stmt->error;
    } else {
        echo "Categoria '$descrizione' inserita con successo.<br>";
    }
}

// Chiudere la dichiarazione e la connessione
$stmt->close();
$conn->close();


?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestione Categorie e Sottocategorie</title>
</head>
<body>
    <h1>Gestione Categorie e Sottocategorie</h1>

    <h2>Categorie</h2>
    <ul>
        <?php while ($category = $categoriesResult->fetch_assoc()): ?>
            <li>
                <?php echo $category['nome_cateforia']; ?>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="category_id" value="<?php echo $category['id_categoria']; ?>">
                    <input type="text" name="subcategory_name" placeholder="Nuova sottocategoria" required>
                    <button type="submit" name="create_subcategory">Aggiungi Sottocategoria</button>
                </form>
            </li>
        <?php endwhile; ?>
    </ul>

    <h2>Crea una nuova categoria</h2>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="text" name="nome_categoria" placeholder="Nome della categoria" required>
        <button type="submit" name="create_category">Crea Categoria</button>
    </form>
</body>
</html>
