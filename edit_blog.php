<?php
// edit_blog.php

// Connessione al database
require_once 'conn.php';

// Recupera i dettagli del blog dal database
$blog_id = $_GET['id'];
$sql = "SELECT * FROM blog WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();

// Visualizza il modulo di modifica con i valori attuali
?>
<form action="process_edit_blog.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">

    <label for="title">Titolo del blog:</label>
    <input type="text" id="title" name="title" value="<?php echo $blog['title']; ?>" required>

    <label for="description">Descrizione:</label>
    <textarea id="description" name="description" required><?php echo $blog['description']; ?></textarea>

    <label for="logo">Immagine del logo:</label>
    <input type="file" id="logo" name="logo" accept="image/*">
    <img src="<?php echo $blog['logo']; ?>" alt="Logo attuale">

    <label for="category">Categoria:</label>
    <select id="category" name="category" required>
        <?php
        // Recupera le categorie dal database e genera le opzioni
        require_once 'database.php';
        $categories = getAllCategories();
        foreach ($categories as $category) {
            $selected = ($category['id'] == $blog['category_id']) ? 'selected' : '';
            echo "<option value='" . $category['id'] . "' $selected>" . $category['name'] . "</option>";
        }
        ?>
    </select>

    <label for="style">Stile:</label>
    <select id="style" name="style" required>
        <?php
        // Recupera gli stili dal database e genera le opzioni
        require_once 'database.php';
        $styles = getAllStyles();
        foreach ($styles as $style) {
            $selected = ($style['id'] == $blog['style_id']) ? 'selected' : '';
            echo "<option value='" . $style['id'] . "' $selected>" . $style['name'] . "</option>";
        }
        ?>
    </select>

    <input type="submit" value="Salva modifiche">
</form>

</form>

</form>

</form>