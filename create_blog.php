<form action="process_create_blog.php" method="post" enctype="multipart/form-data">
    <label for="title">Titolo del blog:</label>
    <input type="text" id="title" name="title" required>

    <label for="description">Descrizione:</label>
    <textarea id="description" name="description" required></textarea>

    <label for="logo">Immagine del logo:</label>
    <input type="file" id="logo" name="logo" accept="image/*" required>

    <label for="category">Categoria:</label>
    <select id="category" name="category" required>
        <?php
        // Recupera le categorie dal database e genera le opzioni
        $categories = getAllCategories();
        foreach ($categories as $category) {
            echo "<option value='" . $category['id'] . "'>" . $category['name'] . "</option>";
        }
        ?>
    </select>

    <label for="style">Stile:</label>
    <select id="style" name="style" required>
        <?php
        // Recupera gli stili dal database e genera le opzioni
        $styles = getAllStyles();
        foreach ($styles as $style) {
            echo "<option value='" . $style['id'] . "'>" . $style['name'] . "</option>";
        }
        ?>
    </select>

    <input type="submit" value="Crea blog">
</form>