<?php
require_once 'conn.php';

// Definisci un array associativo con le categorie e le sottocategorie
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
];

// Funzione per creare categorie e sottocategorie
function createCategoriesAndSubcategories($conn, $categoriesAndSubcategories)
{
    foreach ($categoriesAndSubcategories as $categoryName => $subcategories) {
        // Verifica se la categoria esiste già
        $checkCategoryQuery = "SELECT id_categoria FROM categoria WHERE nome_categoria = ?";
        $stmt = $conn->prepare($checkCategoryQuery);
        $stmt->bind_param('s', $categoryName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Crea la categoria
            $sql = "INSERT INTO categoria (nome_categoria) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $categoryName);
            $stmt->execute();

            // Recupera l'ID della categoria appena creata
            $categoryId = $stmt->insert_id;

            // Crea le sottocategorie
            foreach ($subcategories as $subcategoryName) {
                $sql = "INSERT INTO sottocat (id_categoria, nome_sottocat) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('is', $categoryId, $subcategoryName);
                $stmt->execute();
            }
        } else {
            // Recupera l'ID della categoria esistente
            $row = $result->fetch_assoc();
            $categoryId = $row['id_categoria'];

            // Crea le sottocategorie per la categoria esistente
            foreach ($subcategories as $subcategoryName) {
                $sql = "INSERT INTO sottocat (id_categoria, nome_sottocat) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('is', $categoryId, $subcategoryName);
                $stmt->execute();
            }
        }
    }
}

// Connessione al database
require_once 'conn.php';

// Creazione delle categorie e sottocategorie
createCategoriesAndSubcategories($conn, $categoriesAndSubcategories);
?>