<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM funciones WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$funcion = $res->fetch_assoc();

$peliculas = $conn->query("SELECT id, titulo FROM peliculas");

// Obtener todas las salas distintas (por nombre) ya existentes
$salas = $conn->query("SELECT DISTINCT sala FROM funciones ORDER BY sala");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pelicula_id = $_POST['pelicula_id'];
    $sala = trim($_POST['sala']);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    $update = $conn->prepare("UPDATE funciones SET pelicula_id = ?, sala = ?, fecha = ?, hora = ? WHERE id = ?");
    $update->bind_param("isssi", $pelicula_id, $sala, $fecha, $hora, $id);
    $update->execute();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Función</title>
    <link rel="stylesheet" href="styles/styles_editar.css">
</head>
<body>
    <h1>Editar Función</h1>
    <form method="POST">
        <label>Película:</label>
        <select name="pelicula_id" required>
            <?php while($p = $peliculas->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id'] == $funcion['pelicula_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['titulo']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Sala:</label>
        <select name="sala" required>
            <?php while($s = $salas->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($s['sala']) ?>" <?= $s['sala'] == $funcion['sala'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['sala']) ?>
                </option>
            <?php endwhile; ?>
            <!-- Permitir escribir una sala nueva -->
            <option value="<?= htmlspecialchars($funcion['sala']) ?>" <?php if ($funcion['sala'] && !$salas->num_rows) echo 'selected'; ?>>
                <?= htmlspecialchars($funcion['sala']) ?>
            </option>
        </select>
        <br>
        <label>Fecha:</label>
        <input type="date" name="fecha" value="<?= $funcion['fecha'] ?>" required>

        <label>Hora:</label>
        <input type="time" name="hora" value="<?= substr($funcion['hora'], 0, 5) ?>" required>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="index.php" class="btn logout-btn">Cancelar</a>
    </form>
</body>
</html>
