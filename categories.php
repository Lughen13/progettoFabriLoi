<?php
require_once 'conn.php';

// Recupera tutte le categorie
$categoriesQuery = "SELECT * FROM categoria";
$categoriesResult = $conn->query($categoriesQuery);

// Gestione dell'invio del form per la creazione di una nuova categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {
    $categoryName = $_POST['category_name'];
    $createCategoryQuery = "INSERT INTO categoria (descrizione) VALUES ('$categoryName')";
    $conn->query($createCategoryQuery);
}

// Gestione dell'invio del form per la creazione di una nuova sottocategoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_subcategory'])) {
    $subcategoryName = $_POST['subcategory_name'];
    $categoryId = $_POST['category_id'];
    $createSubcategoryQuery = "INSERT INTO sottocat (id_categoria, descrizione) VALUES ($categoryId, '$subcategoryName')";
    $conn->query($createSubcategoryQuery);
}
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
                <?php echo $category['descrizione']; ?>
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
        <input type="text" name="category_name" placeholder="Nome della categoria" required>
        <button type="submit" name="create_category">Crea Categoria</button>
    </form>
</body>
</html>
