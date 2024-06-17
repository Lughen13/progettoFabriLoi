<?php
// Connessione al database
require_once 'conn.php';

// Verifica se la categoria è stata inviata tramite POST
if (isset($_POST['categoria'])) {
    $categoria = $_POST['categoria'];

    // Query per recuperare le sottocategorie in base alla categoria selezionata
    $sql = "SELECT id_sottocat, nome_sottocat FROM sottocat WHERE id_categoria = (SELECT id_categoria FROM categoria WHERE nome_categoria = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $categoria);
    $stmt->execute();
    $result = $stmt->get_result();

    // Costruisci le opzioni per le sottocategorie
    $options = "<option value=''>Seleziona una sottocategoria</option>";
    while ($row = $result->fetch_assoc()) {
        $options .= "<option value='{$row['id_sottocat']}'>{$row['nome_sottocat']}</option>";
    }

    // Ritorna le opzioni al client (browser)
    echo $options;
}
?>