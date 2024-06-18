<?php
require_once 'conn.php';

// arra associativo che definisce una serie di categorie e sottocategorie (che serve per dare una gerarchia di ordine)
$categories = [
    'Tecnologia',
    'Internet',
    'Hardware',
    'Software',
    'Sicurezza informatica',
    'Sicurezza fisica',
    'Sicurezza ambientale',
    'Sicurezza personale',
    'Sicurezza in generale',
    'Moda',
    'Abbigliamento',
    'Accessori',
    'Lifestyle',
    'Benessere',
    'Tempo libero',
    'Viaggi',
    'Cultura',
    'Sport',
    'Cine',
    'Musica',
    'Film',
    'Televisione',
    'Libri',
    'Destinazioni',
    'Trasporti',
    'Cucina',
    'Ricette',
    'Tecniche di cucina',
    'Scienze',
    'Biologia',
    'Medicina',
    'Fisica',
    'Chimica',
    'Recensioni',
    'Auto',
    'Alberghi',
    'Ristoranti',
    'Veicoli',
    'Arredamento',
    'Interni',
    'Giardino',  
];

    function createCategories($conn, $categories)
    {
        // Crea tutte le categorie nella tabella categoria e le sottocategorie nella tabella sottocat
        foreach ($categories as $categoryName) {
            $sql = "INSERT INTO categoria (nome_categoria) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $categoryName);
            $stmt->execute();
            $categoryId = $stmt->insert_id;
    
            // Inserisci la stessa categoria nella tabella 'sottocategoria'
            $sql = "INSERT INTO sottocat (nome_sottocat, id_categoria) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $categoryName, $categoryId);
            $stmt->execute();
        }
    }

    

// Creazione delle categorie e sottocategorie
createCategories($conn, $categories);

?>