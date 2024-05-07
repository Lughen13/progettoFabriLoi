<?php
require_once 'conn.php';
session_start();

// Verifica se l'utente è loggato
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Recupera le informazioni dell'utente dal database
$query = "SELECT * FROM utente WHERE id_utente = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Gestione dell'invio del modulo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];
    $password = $_POST['password'];

    // Convalida dei dati di input (omessa per brevità)

    // Aggiorna le informazioni dell'utente nel database
    $updateQuery = "UPDATE utente SET nome = ?, cognome = ?, email = ?, bio = ?";
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateQuery .= ", pw = ?";
    }
    $updateQuery .= " WHERE id_utente = ?";

    $stmt = $conn->prepare($updateQuery);
    if (!empty($password)) {
        $stmt->bind_param("sssssi", $nome, $cognome, $email, $bio, $hashedPassword, $userId);
    } else {
        $stmt->bind_param("ssssi", $nome, $cognome, $email, $bio, $userId);
    }

    if ($stmt->execute()) {
        echo "Informazioni dell'account aggiornate con successo.";
    } else {
        echo "Errore durante l'aggiornamento delle informazioni dell'account: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Impostazioni Account</title>
</head>
<body>
    <h1>Impostazioni Account</h1>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo $user['nome']; ?>" required>

        <label for="cognome">Cognome:</label>
        <input type="text" id="cognome" name="cognome" value="<?php echo $user['cognome']; ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>

        <label for="bio">Bio:</label>
        <textarea id="bio" name="bio"><?php echo $user['bio']; ?></textarea>

        <label for="password">Nuova Password (lascia vuoto per non modificare):</label>
        <input type="password" id="password" name="password">

        <button type="submit">Salva Modifiche</button>
    </form>
</body>
</html>
