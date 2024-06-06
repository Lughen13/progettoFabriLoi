<?php

//pagina per creare post, ci si accede dalla pagina home tramite un button, prima di iniziare a creare il post verrà chiesto in qual blog va inserito se il blog non esiste va creato 

require_once 'conn.php';
session_start();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Crea un nuovo post</title>
</head>
<body>
    <h1>Crea un nuovo post</h1>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
        <label for="blog">Seleziona il blog:</label>
        <select name="blog_id" id="blog" required>
            <?php while ($blog = $blogsResult->fetch_assoc()): ?>
                <option value="<?php echo $blog['id_blog']; ?>"><?php echo $blog['titolo_blog']; ?></option>
            <?php endwhile; ?>
        </select>

        <label for="title">Titolo del post:</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Descrizione del post:</label>
        <textarea name="description" id="description" required></textarea>

        <label for="category">Categoria:</label>
        <select name="category" id="category" required>
            <?php
            // Recupera le categorie dal database e genera le opzioni
            $categoriesQuery = "SELECT id_categoria, descrizione FROM categoria";
            $categoriesResult = $conn->query($categoriesQuery);
            while ($category = $categoriesResult->fetch_assoc()) {
                echo "<option value='" . $category['id_categoria'] . "'>" . $category['descrizione'] . "</option>";
            }
            ?>
        </select>

        <label for="image">Immagine del post (opzionale):</label>
        <input type="file" name="image" id="image">

        <button type="submit">Crea post</button>
    </form>
</body>
</html>
