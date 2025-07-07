<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);
$usuario = $conn->query("SELECT * FROM usuarios WHERE id = $id")->fetch_assoc();
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, rol = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nombre, $correo, $rol, $id);

    if ($stmt->execute()) {
        $mensaje = "Usuario actualizado.";
        $usuario = $conn->query("SELECT * FROM usuarios WHERE id = $id")->fetch_assoc();
    } else {
        $mensaje = "Error al actualizar.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="styles/styles_editar.css">
</head>
<body>
<main>
    <div class="welcome-section">
        <h1>Editar Usuario</h1>
        <a href="index.php" class="btn logout-btn">← Volver</a>
    </div>

    <div class="stats-section">
        <?php if ($mensaje): ?>
            <div class="mensaje mensaje-exito"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="text" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
            </div>
            <div class="form-group">
                <label>Rol:</label>
                <select name="rol" required>
                    <option value="cliente" <?= $usuario['rol'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                    <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>
</main>
</body>
</html>
