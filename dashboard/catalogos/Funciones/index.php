<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

$resultado = $conn->query("
    SELECT funciones.id, peliculas.titulo, funciones.sala, funciones.fecha, funciones.hora
    FROM funciones
    JOIN peliculas ON funciones.pelicula_id = peliculas.id
    WHERE funciones.fecha IS NOT NULL
    ORDER BY funciones.fecha DESC, funciones.hora ASC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Funciones</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <h1>Gestión de Funciones</h1>
    <a href="crear.php" class="btn btn-primary">+ Nueva Función</a>
    <a href="../../index-admin.php" class="btn logout-btn" style="margin-left:10px;">← Volver al Panel</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Película</th>
                <th>Sala</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['titulo']) ?></td>
                <td><?= $row['sala'] ?></td>
                <td><?= $row['fecha'] ?></td>
                <td><?= substr($row['hora'], 0, 5) ?></td>
                <td>
                    <a href="editar.php?id=<?= $row['id'] ?>" class="btn btn-primary">Editar</a>
                    <a href="eliminar.php?id=<?= $row['id'] ?>" class="btn logout-btn" onclick="return confirm('¿Eliminar esta función?')">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>