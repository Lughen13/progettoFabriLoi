<?php
require_once 'conn.php';

// Recupera tutti gli stili esistenti
$stylesQuery = "SELECT * FROM stile";
$stylesResult = $conn->query($stylesQuery);

// Inserisci gli stili predefiniti nel database solo se la tabella è vuota
if ($stylesResult->num_rows === 0) {
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
        )
    );

    foreach ($predefinedStyles as $style) {
        // Verifica se lo stile esiste già nel database
        $stmt = $conn->prepare("SELECT id_stile FROM stile WHERE nome = ?");
        $stmt->bind_param("s", $style['nome']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Inserisci lo stile nel database solo se non esiste già
            $insertStyleQuery = "INSERT INTO stile (nome, font, colore_testo, background)
                                 VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insertStyleQuery);
            $stmt->bind_param("ssss", $style['nome'], $style['font'], $style['colore_testo'], $style['background']);
            $stmt->execute();
        }

        $stmt->close();
    }
}

// Funzione per recuperare gli stili
function getStyles() {
    global $conn;
    $stylesQuery = "SELECT * FROM stile";
    $stylesResult = $conn->query($stylesQuery);
    return $stylesResult;
}

