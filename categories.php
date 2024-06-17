<?php
require_once 'conn.php';

// arra associativo che definisce una serie di categorie e sottocategorie (che serve per dare una gerarchia di ordine)
$categoriesAndSubcategories = [
    'Tecnologia' => [
        'Internet',
        'Hardware',
        'Software',
        'Sicurezza informatica',
    ],
    'Moda' => [
        'Abbigliamento',
        'Accessori',
    ],
    'Lifestyle' => [
        'Benessere',
        'Tempo libero',
    ],
    'Viaggi' => [
        'Destinazioni',
        'Trasporti',
    ],
    'Cucina' => [
        'Ricette',
        'Tecniche di cucina',
    ],
    'Scienze' => [
        'Biologia',
        'Medicina',
        'Fisica',
        'Chimica',
    ],
    'Recensioni' => [
        'Auto',
        'Alberghi',
        'Ristoranti',
        'Veicoli',
    ],
    'Arredamento' => [
        'Interni',
        'GIardino',
    ],
];

function createCategoriesAndSubcategories($conn, $categoriesAndSubcategories)
{
    // Crea tutte le categorie nella tabella 'categoria'
    foreach ($categoriesAndSubcategories as $categoryName => $subcategories) {
        $sql = "INSERT INTO categoria (nome_categoria) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $categoryName);
        $stmt->execute();
        $categoryId = $stmt->insert_id;

        foreach ($subcategories as $subcategoryName) {
            $sql = "INSERT INTO categoria (nome_categoria) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $subcategoryName);
            $stmt->execute();
            $subcategoryId = $stmt->insert_id;

            // inserisco la sottocategorizzazione nella tabella sottocat
            $sql = "INSERT INTO sottocat (id_categoria, id_sottocat, nome_sottocat) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iis', $categoryId, $subcategoryId, $subcategoryName);
            $stmt->execute();
        }
    }
}

// Creazione delle categorie e sottocategorie
createCategoriesAndSubcategories($conn, $categoriesAndSubcategories);
?>