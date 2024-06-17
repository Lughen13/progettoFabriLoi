<?php
// categories.php

require_once 'conn.php';

// Inserisci categorie
$sqlCategorie = "INSERT INTO categoria (nome_categoria) VALUES (?)";
$stmtCategorie = $conn->prepare($sqlCategorie);

$categorie = array("Tecnologia", "Sport", "Economia", "Politica");

foreach ($categorie as $categoria) {
    $stmtCategorie->bind_param("s", $categoria);
    $stmtCategorie->execute();
}

// Inserisci sottocategorie
$sqlSottocategorie = "INSERT INTO sottocat (id_categoria, nome_sottocat) VALUES (?, ?)";
$stmtSottocategorie = $conn->prepare($sqlSottocategorie);

$sottocategorie = array(
    array(1, "Internet"),
    array(1, "Hardware"),
    array(2, "Calcio"),
    array(2, "Basket"),
    array(3, "Finanza"),
    array(3, "Mercato azionario"),
    array(4, "Elezioni"),
    array(4, "Politica estera")
);

foreach ($sottocategorie as $sottocategoria) {
    $stmtSottocategorie->bind_param("is", $sottocategoria[0], $sottocategoria[1]);
    $stmtSottocategorie->execute();
}

$stmtCategorie->close();
$stmtSottocategorie->close();

$conn->close();
?>