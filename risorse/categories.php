<?php
require_once '../configurazione/conn.php';
// array che definisce una serie di categorie e sottocategorie 
$categories = [
    'Tecnologia' => ['Internet', 'Hardware', 'Software','Altro'],
    'Moda' => ['Abbigliamento', 'Accessori','Altro'],
    'Tempo libero' => ['Benessere', 'Vacanze', 'Sport', 'Cinema', 'Musica', 'Televisione', 'Libri','Altro'],
    'Viaggi' => ['Destinazioni', 'Trasporti', 'Cultura', 'Alloggi','Altro'],
    'Cucina' => ['Ricette', 'Tecniche di cucina', 'Attrezzi da cucina','Altro'],
    'Scienze' => ['Biologia', 'Medicina', 'Fisica', 'Chimica','Altro'],
    'Recensioni' => ['Auto', 'Alberghi', 'Ristoranti', 'Veicoli','Altro'],
    'Arredamento' => ['Interni', 'Giardino','Altro'],
    'Altro' => ['Altro']
    
];

// funzione che utilizzo per inserire categorie e sottocategorie nelle apposite tabella
function inserisci_cat_sottocat($conn, $categories) {       

    foreach ($categories as $categoryName => $subcategories) {
        // verifico se la categoria esiste già nella tabella categoria
        $sql = "SELECT id_categoria FROM categoria WHERE nome_categoria = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $categoryName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // se non esiste la categoria la inserisco 
            $sql = "INSERT INTO categoria (nome_categoria) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $categoryName);
            $stmt->execute();
            $categoryId = $stmt->insert_id;
            $stmt->close();
        } else {
            $row = $result->fetch_assoc();
            $categoryId = $row['id_categoria'];
        }
        // stessa verifica ed inserimento fatto precedentemente ma per le sottocategorie
        foreach ($subcategories as $subcategoryName) {
            $sql = "SELECT id_sottocat FROM sottocat WHERE nome_sottocat = ? AND id_categoria = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $subcategoryName, $categoryId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $sql = "INSERT INTO sottocat (nome_sottocat, id_categoria) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('si', $subcategoryName, $categoryId);
                $stmt->execute();
                $stmt->close(); 
            } else {
                $stmt->close(); 
            }
        }
    }
}

// chiamo la funzione per inserire le categorie e le sottocategorie
inserisci_cat_sottocat($conn, $categories);

?>