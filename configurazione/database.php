<?php
// Abilita la visualizzazione di tutti gli errori PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Includi il file di connessione al database
require_once '../configurazione/conn.php';

// Funzione per eseguire una query e restituire un singolo record
function fetchRecord($query, $params = []) {
    global $conn;
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        trigger_error('Errore nella preparazione della query: ' . $conn->error, E_USER_ERROR);
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $record = $result->fetch_assoc();
    $stmt->close();
    return $record;
}

// Funzione per eseguire una query e restituire più record
function fetchRecords($query, $params = []) {
    global $conn;
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        trigger_error('Errore nella preparazione della query: ' . $conn->error, E_USER_ERROR);
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    $stmt->close();
    return $records;
}

// Funzione per eseguire un'istruzione INSERT, UPDATE o DELETE
function executeStatement($query, $params = []) {
    global $conn;
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        trigger_error('Errore nella preparazione della query: ' . $conn->error, E_USER_ERROR);
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    
    $stmt->execute();
    $stmt->close();
    return $conn->affected_rows;
}

?>