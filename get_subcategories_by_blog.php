<?php
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blogId = $_POST['id_blog'];

    $sql = "SELECT s.id_sottocat, s.nome_sottocat
            FROM sottocat s
            INNER JOIN categoria c ON s.id_categoria = c.id_categoria
            INNER JOIN blog b ON c.id_categoria = b.id_categoria
            WHERE b.id_blog = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $blogId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<option value="">Seleziona una sottocategoria</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['id_sottocat'] . '">' . $row['nome_sottocat'] . '</option>';
        }
    } else {
        echo '<option value="">Nessuna sottocategoria trovata</option>';
    }

    $stmt->close();
}

$conn->close();
?>
