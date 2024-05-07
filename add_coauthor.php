<?php
// Connessione al database
require_once 'conn.php';

// Recupera l'ID del blog dalla query string o da un'altra fonte
$blog_id = $_GET['blog_id'];

// Recupera gli utenti dal database, escluso l'utente corrente
$user_id = $_SESSION['user_id'];
$sql = "SELECT id_utente, username FROM utente WHERE id_utente != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Aggiungi co-autore</h2>
<form action="process_add_coauthor.php" method="post">
    <input type="hidden" name="blog_id" value="<?php echo $blog_id; ?>">

    <label for="coauthor">Seleziona un utente:</label>
    <select id="coauthor" name="coauthor_id" required>
        <option value="">Seleziona un utente</option>
        <?php
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['id_utente'] . "'>" . $row['username'] . "</option>";
        }
        ?>
    </select>

    <input type="submit" value="Aggiungi co-autore">
</form>