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

// Inserisci gli stili predefiniti nel database
$predefinedStyles = array(
    array(
        'nome' => 'Moderno',
        'font' => 'Arial',
        'colore_testo' => '#333333',
        'background' => '#FFFFFF'
    ),
    array(
        'nome' => 'Classico',
        'font' => 'Times New Roman',
        'colore_testo' => '#000000',
        'background' => '#F5F5F5'
    ),
    array(
        'nome' => 'Minimalista',
        'font' => 'Helvetica',
        'colore_testo' => '#555555',
        'background' => '#FFFFFF'
    ),
    array(
        'nome' => 'Vivace',
        'font' => 'Verdana',
        'colore_testo' => '#FF6600',
        'background' => '#FFFFCC'
    ),
    array(
        'nome' => 'Elegante',
        'font' => 'Georgia',
        'colore_testo' => '#663300',
        'background' => '#F8F8F8'
    ),
    array(
        'nome' => 'Naturale',
        'font' => 'Trebuchet MS',
        'colore_testo' => '#336600',
        'background' => '#F0F8E0'
    ),
    array(
        'nome' => 'Retrò',
        'font' => 'Courier New',
        'colore_testo' => '#663399',
        'background' => '#FFFFCC'
    ),
    array(
        'nome' => 'Audace',
        'font' => 'Impact',
        'colore_testo' => '#CC0000',
        'background' => '#FFFFFF'
    ),
    array(
        'nome' => 'Sofisticato',
        'font' => 'Garamond',
        'colore_testo' => '#333333',
        'background' => '#F0F0F0'
    ),
    array(
        'nome' => 'Fresco',
        'font' => 'Tahoma',
        'colore_testo' => '#006699',
        'background' => '#FFFFFF'
    )
);

foreach ($predefinedStyles as $style) {
    $insertStyleQuery = "INSERT INTO stile (nome, font, colore_testo, background)
                         VALUES ('{$style['nome']}', '{$style['font']}', '{$style['colore_testo']}', '{$style['background']}')";
    $conn->query($insertStyleQuery);
}

// Funzione per recuperare gli stili
function getStyles() {
    global $conn;
    $stylesQuery = "SELECT * FROM stile";
    $stylesResult = $conn->query($stylesQuery);
    return $stylesResult;
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
