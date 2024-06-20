<?php
require_once 'conn.php';

// array che definisce una serie di categorie e sottocategorie di default
$categories = [
    'Tecnologia',
    'Internet',
    'Hardware',
    'Software',
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
    'Parcheggio',
];



/* vecchio codice che inseriva categorie e sottocaterogie all'infinito 

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


*/


// nuovo codice che si occupa dell'iserimento delle categorie 
function insert_cat ($conn, $categories) { 

    foreach ($categories as $categoryName) {

        //verifico che le categorie già non siano presenti nel db     
        $sql = "SELECT id_categoria FROM categoria WHERE nome_categoria = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $categoryName);
        $stmt->execute();
        $result = $stmt->get_result();

        // se la categoria già esiste quindi non fa nulla e procede con la prossima 
        if ($result->num_rows > 0) {
            $stmt->close();
            continue;
        }

        $sql = "INSERT INTO categoria (nome_categoria) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $categoryName);
        $stmt->execute();
        $categoryId = $stmt->insert_id;
    }
}



// nuovo codice che si occupa dell'iserimento delle categorie 
function insert_subcat ($conn, $categories) { 

    foreach ($categories as $subcategories) {
            $categoryName = $subcategories;

          // Verifica se la categoria esiste nella tabella categoria
        $sql = "SELECT id_categoria FROM categoria WHERE nome_categoria = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $categoryName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $categoryId = $row['id_categoria'];

        
        // Verifica se la sottocategoria esiste già nella tabella sottocat
        $sql = "SELECT id_sottocat FROM sottocat WHERE nome_sottocat = ? AND id_categoria = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $categoryName, $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();

        // se il numero di riga è maggiore di uno vuol dire che quella categoria già esiste quindi non fa nullla e procede con la prossima
        if ($result->num_rows > 0) {
            $stmt->close();
            continue;
    }

                
        // inserisco la sottocategorizzazione nella tabella sottocat
        $sql = "INSERT INTO sottocat (nome_sottocat, id_categoria) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $categoryName, $categoryId);
            $stmt->execute();
        $categoryId = $stmt->insert_id;

        }
    }
}



/*            
        // verifico che nella tabella sottocategorie non ci siano le categorie che voglio inseire 
        $sql = "SELECT id_sottocat FROM sottocat WHERE nome_sottocat = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $subcategories);
        $stmt->execute();
        $result = $stmt->get_result();

        // se il numero di riga è maggiore di uno vuol dire che quella categoria già esiste quindi non fa nullla e procede con la prossima
        if ($result->num_rows > 0) {
            $stmt->close();
            continue;
        }


        // inserisco la sottocategorizzazione nella tabella sottocat
        $sql = "INSERT INTO sottocat (id_categoria, id_sottocat, nome_sottocat) VALUES (?, ?, ?)" ;
        $stmt = $conn->prepare($sql);
      //  $stmt->bind_param('iis', $subcategories);
        $stmt->bind_param('iis', $categoryId, $subcategoryId, $subcategoryName);
        $stmt->execute();
        $categoryId = $stmt->insert_id;
*/



// Creazione delle categorie e sottocategorie
insert_cat($conn, $categories);
insert_subcat($conn, $categories);

?>