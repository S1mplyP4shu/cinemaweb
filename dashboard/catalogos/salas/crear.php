<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

$mensaje = "";
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sala = trim($_POST['sala']);
    $capacidad = intval($_POST['capacidad']);

    // Usar el ID de la película dummy "Sin asignar"
    $pelicula_id = 4;
    $fecha = '1970-01-01'; // Fecha válida simbólica
    $hora = '00:00:00'; // Hora por defecto, si aplica

    if (!$sala || !$capacidad) {
        $errores[] = "Todos los campos son obligatorios.";
    }

    if (empty($errores)) {
        $stmt = $conn->prepare("INSERT INTO funciones (pelicula_id, fecha, hora, sala, total_asientos) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $pelicula_id, $fecha, $hora, $sala, $capacidad);

        if ($stmt->execute()) {
            $mensaje = "Sala creada correctamente. Ahora puedes asignarle película y horario.";
        } else {
            $errores[] = "Error al crear la sala: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Sala</title>
    <link rel="stylesheet" href="styles/styles_crear.css">
</head>
<body>
<header>
    <nav>
        <div class="logo">CinemaWeb</div>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?></div>
                <?= htmlspecialchars($_SESSION['nombre']) ?>
            </div>
        </div>
    </nav>
</header>

<main>
    <div class="welcome-section">
        <h1>Nueva <span class="gold-accent">Sala</span></h1>
        <a href="index.php" class="btn logout-btn">← Volver al listado</a>
    </div>

    <div class="stats-section">
        <?php if ($mensaje): ?>
            <div class="mensaje mensaje-exito"><?= $mensaje ?></div>
        <?php endif; ?>
        <?php if (!empty($errores)): ?>
            <div class="mensaje mensaje-error"><?= implode('<br>', array_map('htmlspecialchars', $errores)) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="sala">Nombre de sala:</label>
                <input type="text" id="sala" name="sala" required>
            </div>
            <div class="form-group">
                <label for="capacidad">Capacidad:</label>
                <input type="number" id="capacidad" name="capacidad" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Sala</button>
        </form>
    </div>
</main>
</body>
</html>
