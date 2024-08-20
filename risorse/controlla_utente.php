<?php
require_once '../configurazione/conn.php';

$conn = new mysqli($host, $user, $password, $database);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$response = array('exists' => false, 'valid' => false, 'error' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'];
    $value = $_POST['value'];

    if (empty($value)) {
        $response['error'] = 'Il campo non può essere vuoto.';
        echo json_encode($response);
        exit();
    }

    if ($type == 'username') {
        // Verifica se l'username esiste
        $sql = "SELECT id_utente FROM utente WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $response['exists'] = true;
        }
        $stmt->close();
    } else if ($type == 'email') {
        // Verifica se l'email esiste
        $sql = "SELECT id_utente FROM utente WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $response['exists'] = true;
        }
        $stmt->close();
    } else if ($type == 'password') {
        // Verifica se la password è valida (effettua un confronto basato su hash)
        $username = $_POST['username'];
        $sql = "SELECT password FROM utente WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($stored_password_hash);
            $stmt->fetch();
            if (password_verify($value, $stored_password_hash)) {
                $response['valid'] = true;
            }
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode($response);
?>
<script>
$(document).ready(function() {
    // Mostra/Nascondi campi premium
    $('#premium').change(function() {
        if ($(this).is(':checked')) {
            $('#premium-fields').show();
        } else {
            $('#premium-fields').hide();
        }
    });

    // Funzione per mostrare gli errori
    function showError(elementId, message) {
        $('#' + elementId + '-error').text(message);
    }

    // Verifica username
    $('#username').on('input', function() {
        var username = $(this).val();
        if (username.length > 0) {
            $.post('../risorse/controlla_utente.php', { type: 'username', value: username }, function(data) {
                let response = JSON.parse(data);
                if (response.error) {
                    showError('username', response.error);
                } else if (response.exists) {
                    showError('username', 'Questo username è già in utilizzo.');
                } else {
                    showError('username', '');
                }
            });
        } else {
            showError('username', 'Il campo username non può essere vuoto.');
        }
    });

    // Verifica email
    $('#email').on('input', function() {
        var email = $(this).val();
        if (email.length > 0) {
            $.post('../risorse/controlla_utente.php', { type: 'email', value: email }, function(data) {
                let response = JSON.parse(data);
                if (response.error) {
                    showError('email', response.error);
                } else if (response.exists) {
                    showError('email', 'Questa email è già in utilizzo.');
                } else {
                    showError('email', '');
                }
            });
        } else {
            showError('email', 'Il campo email non può essere vuoto.');
        }
    });

    // Verifica password
    $('#password').on('input', function() {
        var password = $(this).val();
        var regex = /^(?=.*[A-Z])(?=.*[\W_]).{8,}$/;
        if (password.length > 0) {
            if (regex.test(password)) {
                showError('password', '');
            } else {
                showError('password', 'Password non valida. Deve contenere almeno una lettera maiuscola e un carattere speciale.');
            }
        } else {
            showError('password', 'Il campo password non può essere vuoto.');
        }
    });

    // Convalida password di conferma
    $('#confirm_password').on('input', function() {
        var confirmPassword = $(this).val();
        var password = $('#password').val();
        if (confirmPassword.length > 0) {
            if (confirmPassword === password) {
                showError('confirm_password', '');
            } else {
                showError('confirm_password', 'Le password non corrispondono.');
            }
        } else {
            showError('confirm_password', 'Il campo conferma password non può essere vuoto.');
        }
    });
});
</script>