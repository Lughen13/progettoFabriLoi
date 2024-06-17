<?php
// Connessione al database
require_once 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categoria = $_POST['categoria'];

    $sql = "SELECT * FROM sottocat WHERE id_categoria = (SELECT id_categoria FROM categoria WHERE id_categoria = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoria);
    $stmt->execute();
    $result = $stmt->get_result();

    $options = '<option value="">Seleziona una sottocategoria</option>';
    while ($row = $result->fetch_assoc()) {
        $options .= '<option value="' . $row['id_sottocat'] . '">' . $row['nome_sottocat'] . '</option>';
    }

    echo $options;
}

$stmt->close();
$conn->close();
?>