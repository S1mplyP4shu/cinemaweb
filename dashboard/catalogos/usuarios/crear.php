<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO usuarios (id, nombre, correo, contraseña, rol) VALUES (NULL, ?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $email, $password, $rol);

    if ($stmt->execute()) {
        $mensaje = "Usuario creado exitosamente.";
    } else {
        $mensaje = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario</title>
    <link rel="stylesheet" href="styles/styles_crear.css">
</head>
<body>
<main>
    <div class="welcome-section">
        <h1>Nuevo Usuario</h1>
        <a href="index.php" class="btn logout-btn">← Volver</a>
    </div>

    <div class="stats-section">
        <?php if ($mensaje): ?>
            <div class="mensaje mensaje-exito"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="text" name="email" required>
            </div>
            <div class="form-group">
                <label>Contraseña:</label>
                <input type="text" name="password" required>
            </div>
            <div class="form-group">
                <label>Rol:</label>
                <select name="rol" required>
                    <option value="cliente">Cliente</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Usuario</button>
        </form>
    </div>
</main>
</body>
</html>
