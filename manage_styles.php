<?php
require_once 'conn.php';

// array con degli stili di defoult 
$styles = [
    [
        'nome' => 'Moderno',
        'font' => 'Arial',
        'colore_testo' => '#333333',
        'background' => '#FFFFFF'
    ],
    [
        'nome' => 'Classico',
        'font' => 'Times New Roman',
        'colore_testo' => '#000000',
        'background' => '#F5F5F5'
    ],
    [
        'nome' => 'Minimalista',
        'font' => 'Helvetica',
        'colore_testo' => '#555555',
        'background' => '#FFFFFF'
    ],
    [
        'nome' => 'Vivace',
        'font' => 'Verdana',
        'colore_testo' => '#FF6600',
        'background' => '#FFFFCC'
    ],
    [
        'nome' => 'Elegante',
        'font' => 'Georgia',
        'colore_testo' => '#663300',
        'background' => '#F8F8F8'
    ]

];

// funzione per inserire gli stili nel db, verifica che lo stile non esiste (o meglio se esiste non fa nulla, se non esiste lo inserisce )
function insertStyles($conn, $styles)
{
    foreach ($styles as $style) {
        $nome = $style['nome'];
        $font = $style['font'];
        $colore_testo = $style['colore_testo'];
        $background = $style['background'];

        $sql = "SELECT id_stile FROM stile WHERE nome = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $nome);
        $stmt->execute();
        $result = $stmt->get_result();

        // numero di riga maggiore di 0 vuol dire che lo stile esiste, non restituisce nulla e va avanti 
        if ($result->num_rows > 0) {
            $stmt->close();
            continue;
        }

        // questa nello specifico inserisce lo stile nel db 
        $sql = "INSERT INTO stile (nome, font, colore_testo, background) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $nome, $font, $colore_testo, $background);
        $stmt->execute();
        $stmt->close();
    }
}
insertStyles($conn, $styles);

// Funzione per recuperare gli stili
function getStyles()
{
    global $conn;
    $stylesQuery = "SELECT * FROM stile";
    $stylesResult = $conn->query($stylesQuery);
    return $stylesResult;
}

?>
