<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM peliculas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$pelicula = $resultado->fetch_assoc();

if (!$pelicula) {
    echo "Película no encontrada.";
    exit;
}

$funciones = [];
$res_funciones = $conn->query("SELECT id, hora, fecha FROM funciones WHERE pelicula_id = $id ORDER BY hora ASC");
while ($f = $res_funciones->fetch_assoc()) {
    $funciones[] = $f;
}
$fecha_funcion = isset($funciones[0]['fecha']) ? $funciones[0]['fecha'] : date('Y-m-d');
$salas = $conn->query("SELECT DISTINCT sala, total_asientos FROM funciones");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        exit('Token CSRF inválido.');
    }

    $titulo = trim($_POST['titulo']);
    $genero = trim($_POST['genero']);
    $duracion = intval($_POST['duracion_min']);
    $clasificacion = trim($_POST['clasificacion']);
    $sinopsis = trim($_POST['sinopsis']);
    $imagen_url = trim($_POST['imagen_url']);
    $fecha = trim($_POST['fecha']);
    $sala = trim($_POST['sala']);
    $asientos = intval($_POST['total_asientos']);
    $horarios = explode(',', $_POST['horarios']);

    $errores = [];

    if (!$titulo || !$genero || !$duracion || !$fecha || !$sala || !$asientos) {
        $errores[] = "Todos los campos son obligatorios.";
    }

    $horarios_validos = [];
    foreach ($horarios as $hora) {
        $hora = trim($hora);
        if (preg_match('/^\d{2}:\d{2}$/', $hora)) {
            $horarios_validos[] = $hora;
        }
    }

    if (empty($horarios_validos)) {
        $errores[] = "Debes ingresar al menos un horario válido.";
    }

    if (empty($errores)) {
        $update = $conn->prepare("UPDATE peliculas SET titulo = ?, genero = ?, duracion_min = ?, clasificacion = ?, sinopsis = ?, imagen_url = ? WHERE id = ?");
        $update->bind_param("ssisssi", $titulo, $genero, $duracion, $clasificacion, $sinopsis, $imagen_url, $id);
        if ($update->execute()) {
            $conn->query("DELETE FROM funciones WHERE pelicula_id = $id");

            foreach ($horarios_validos as $hora) {
                $stmt_funcion = $conn->prepare("INSERT INTO funciones (pelicula_id, fecha, hora, sala, total_asientos) VALUES (?, ?, ?, ?, ?)");
                $stmt_funcion->bind_param("isssi", $id, $fecha, $hora, $sala, $asientos);
                $stmt_funcion->execute();
                $stmt_funcion->close();
            }

            header("Location: index.php");
            exit;
        } else {
            $errores[] = "Error al actualizar la película.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Película</title>
    <link rel="stylesheet" href="styles/styles_editar.css">
</head>
<body>
<header>
    <nav>
        <div class="logo">Cinema<span class="gold-accent">Web</span></div>
    </nav>
</header>

<main>
    <section class="welcome-section">
        <h1>Editar Película: <span class="gold-accent"><?= htmlspecialchars($pelicula['titulo']) ?></span></h1>
    </section>

    <form method="POST" class="form" style="margin-top: 2rem;">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="form-group">
            <label>Título:</label>
            <input type="text" name="titulo" value="<?= htmlspecialchars($pelicula['titulo']) ?>" required>
        </div>

        <div class="form-group">
            <label>Género:</label>
            <input type="text" name="genero" value="<?= htmlspecialchars($pelicula['genero']) ?>" required>
        </div>

        <div class="form-group">
            <label>Clasificación:</label>
            <input type="text" name="clasificacion" value="<?= htmlspecialchars($pelicula['clasificacion']) ?>">
        </div>

        <div class="form-group">
            <label>Duración (minutos):</label>
            <input type="number" name="duracion_min" value="<?= htmlspecialchars($pelicula['duracion_min']) ?>" required>
        </div>

        <div class="form-group">
            <label>Sinopsis:</label>
            <textarea name="sinopsis" placeholder="Resumen o descripción de la película"><?= htmlspecialchars($pelicula['sinopsis']) ?></textarea>
        </div>

        <div class="form-group">
            <label>URL de imagen (nombre de archivo o ruta relativa):</label>
            <input type="text" name="imagen_url" value="<?= htmlspecialchars($pelicula['imagen_url']) ?>">
        </div>

        <div class="form-group">
            <label>Sala:</label>
            <input type="text" name="sala" value="<?= htmlspecialchars($funciones[0]['sala'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Capacidad de sala:</label>
            <input type="number" name="total_asientos" value="<?= htmlspecialchars($funciones[0]['total_asientos'] ?? 100) ?>" required>
        </div>

        <div class="form-group">
            <label>Fecha:</label>
            <input type="date" name="fecha" value="<?= htmlspecialchars($fecha_funcion) ?>" required>
        </div>

        <div class="form-group">
            <label>Horarios (separados por coma, ej: 16:00,19:30):</label>
            <input type="text" name="horarios" value="<?= htmlspecialchars(implode(',', array_column($funciones, 'hora'))) ?>" required>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="mensaje mensaje-error">
                <?= implode('<br>', array_map('htmlspecialchars', $errores)) ?>
            </div>
        <?php endif; ?>

        <div style="text-align:center;">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="index.php" class="btn logout-btn">Cancelar</a>
        </div>
    </form>
</main>

<footer>
    <p>&copy; 2025 CinemaWeb. Todos los derechos reservados.</p>
</footer>
</body>
</html>
