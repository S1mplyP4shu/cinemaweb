<?php
session_start();

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Si es una petición AJAX (registro)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    include('includes/db.php');

    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo '<response><status>error</status><message>Token CSRF inválido.</message></response>';
        exit;
    }

    // Recibir y sanitizar datos
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $contraseña = $_POST['contraseña'] ?? '';
    $confirmcontraseña = $_POST['confirmcontraseña'] ?? '';

    $nombre = $firstName . ' ' . $lastName;

    // Validaciones
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($contraseña) || empty($confirmcontraseña)) {
        echo '<response><status>error</status><message>Todos los campos son obligatorios.</message></response>';
        exit;
    }

    if ($contraseña !== $confirmcontraseña) {
        echo '<response><status>error</status><message>Las contraseñas no coinciden.</message></response>';
        exit;
    }

    if (strlen($contraseña) < 8) {
        echo '<response><status>error</status><message>La contraseña debe tener al menos 8 caracteres.</message></response>';
        exit;
    }

    // Encriptar contraseña
    $contraseñaHash = contraseña_hash($contraseña, contraseña_DEFAULT);

    // Insertar en la BD
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contraseña, rol) VALUES (?, ?, ?, 'cliente')");
    $stmt->bind_param("sss", $nombre, $email, $contraseñaHash);

    if ($stmt->execute()) {
        echo '<response><status>success</status><message>login.php?registro=exitoso</message></response>';
    } else {
        echo '<response><status>error</status><message>Error al registrar: ' . htmlspecialchars($stmt->error) . '</message></response>';
    }

    $stmt->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CinemaWeb - Registro</title>
    <link rel="stylesheet" href="styles/register_styles.css">
</head>
<body>
    <div class="back-home">
        <a href="index.php">← Volver al inicio</a>
    </div>

    <div class="register-container">
        <div class="logo">Cinema<span class="gold-accent">Web</span></div>

        <form id="registerForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="firstName">Nombre</label>
                    <input type="text" id="firstName" name="firstName" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Apellido</label>
                    <input type="text" id="lastName" name="lastName" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="contraseña">Contraseña</label>
                <input type="contraseña" id="contraseña" name="contraseña" required>
                <div class="contraseña-requirements">
                    Mínimo 8 caracteres, incluye mayúsculas, minúsculas y números
                </div>
            </div>

            <div class="form-group">
                <label for="confirmcontraseña">Confirmar contraseña</label>
                <input type="contraseña" id="confirmcontraseña" name="confirmcontraseña" required>
            </div>

            <input type="hidden" id="csrf_token" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="checkbox-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    Acepto los <a href="#">términos y condiciones</a> y la <a href="#">política de privacidad</a>
                </label>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="newsletter" name="newsletter">
                <label for="newsletter">
                    Quiero recibir promociones y noticias por correo electrónico
                </label>
            </div>

            <button type="submit" class="btn">Crear Cuenta</button>
        </form>

        <div id="responseMessage" style="text-align:center; margin-top:10px;"></div>

        <div class="divider">
            <span>o</span>
        </div>

        <div class="login-link">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
        </div>
    </div>

    <script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new URLSearchParams(new FormData(this)).toString();

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'registro.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const xml = xhr.responseXML;
                const status = xml.getElementsByTagName('status')[0].textContent;
                const message = xml.getElementsByTagName('message')[0].textContent;

                if (status === 'success') {
                    window.location.href = message;
                } else {
                    document.getElementById('responseMessage').innerHTML = `<span style="color:red;">${message}</span>`;
                }
            }
        };

        xhr.send(formData);
    });
    </script>
</body>
</html>
