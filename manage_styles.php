<?php
require_once 'conn.php';

// Recupera tutti gli stili esistenti
$stylesQuery = "SELECT * FROM stile";
$stylesResult = $conn->query($stylesQuery);

// Gestione dell'invio del form per la creazione di un nuovo stile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_style'])) {
    $styleName = $_POST['style_name'];
    $font = $_POST['font'];
    $textColor = $_POST['text_color'];
    $backgroundColor = $_POST['background_color'];

    $createStyleQuery = "INSERT INTO stile (nome, font, colore_testo, background)
                         VALUES ('$styleName', '$font', '$textColor', '$backgroundColor')";
    $conn->query($createStyleQuery);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestione Stili</title>
</head>
<body>
    <h1>Gestione Stili</h1>

    <h2>Stili Esistenti</h2>
    <ul>
        <?php while ($style = $stylesResult->fetch_assoc()): ?>
            <li>
                <div style="font-family: <?php echo $style['font']; ?>; color: <?php echo $style['colore_testo']; ?>; background-color: <?php echo $style['background']; ?>;">
                    <?php echo $style['nome']; ?>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>

    <h2>Crea un nuovo stile</h2>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="style_name">Nome dello stile:</label>
        <input type="text" name="style_name" id="style_name" required>

        <label for="font">Font:</label>
        <input type="text" name="font" id="font" required>

        <label for="text_color">Colore del testo:</label>
        <input type="color" name="text_color" id="text_color" required>

        <label for="background_color">Colore di sfondo:</label>
        <input type="color" name="background_color" id="background_color" required>

        <button type="submit" name="create_style">Crea Stile</button>
    </form>
</body>
</html>
