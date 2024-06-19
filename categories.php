<?php
require_once 'conn.php';

// array che definisce una serie di categorie e sottocategorie di default
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
    { // la funzione serve per inserire categorie e sottocategorie nel db
        foreach ($categories as $categoryName) {
            $sql = "INSERT INTO categoria (nome_categoria) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $categoryName);
            $stmt->execute();
            $categoryId = $stmt->insert_id;
    
            $sql = "INSERT INTO sottocat (nome_sottocat, id_categoria) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $categoryName, $categoryId);
            $stmt->execute();
        }
    }

// Creazione delle categorie e sottocategorie
createCategories($conn, $categories);

?>