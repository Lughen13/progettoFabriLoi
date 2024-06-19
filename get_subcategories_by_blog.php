<?php
require_once 'conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_blog'])) {
    $blogId = $_POST['id_blog'];

    // seleziono le sottogategorie ad esclusione di quella a cui appartiene il blog per permettere all'utente di selezionare una sottocategroia per il post
    $sql = "SELECT sc.id_sottocat, sc.nome_sottocat
            FROM sottocat sc
            WHERE id_categoria NOT IN (
                SELECT id_categoria
                FROM blog
                WHERE id_blog = ?
            )"; // seleziono tutti gli id_sottocategoria ad eccezione di quello in cui id_categoria di id_Sottocategoria è diverso dell'id_categoria del blog 

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $blogId);
    $stmt->execute();
    $result = $stmt->get_result();

    // cegero le opzioni della  select 
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['id_sottocat'] . '">' . $row['nome_sottocat'] . '</option>';
        }
    } else {
        echo '<option value="">Nessuna sottocategoria disponibile</option>';
    }

    $stmt->close();
} else {
    echo '<option value="">Errore nel caricamento delle sottocategorie</option>';
}

$conn->close();
?>