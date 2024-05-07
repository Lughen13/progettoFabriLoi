<?php
// Informazioni di connessione al database
$host = "localhost";
$user = "root";
$password = "root"; // Inserisci la password di root per MAMP
$database = "progettoFabriLoi";

// Connessione al database
$conn = new mysqli($host, $user, $password, $database);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Funzione per eseguire una query
function executeQuery($query, $params = []) {
    global $conn;
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

// Funzione per recuperare un singolo record
function fetchRecord($query, $params = []) {
    $result = executeQuery($query, $params);
    return $result->fetch_assoc();
}

// Funzione per recuperare più record
function fetchRecords($query, $params = []) {
    $result = executeQuery($query, $params);
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    return $records;
}

// Funzione per eseguire un'istruzione INSERT, UPDATE o DELETE
function executeStatement($query, $params = []) {
    global $conn;
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $stmt->close();
    return $conn->affected_rows;
}
