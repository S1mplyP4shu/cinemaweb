<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

$resultado = $conn->query("SELECT * FROM usuarios ORDER BY rol DESC, fecha_registro DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - CinemaWeb</title>
    <link rel="stylesheet" href="styles/style.css">
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
            <div class="dropdown">
                <span class="admin-badge">Administrador</span>
                <div class="dropdown-content">
                    <a href="../../logout.php">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<main>
    <div class="welcome-section">
        <h1>Gestión de Usuarios</h1>
        <p>Lista completa de usuarios registrados</p>
        <a href="crear.php" class="btn btn-primary">+ Añadir Usuario</a>
        <a href="../../index-admin.php" class="btn logout-btn" style="margin-left:10px;">← Volver al Panel</a>
    </div>

    <div class="stats-section">
        <table border="1" width="100%" style="border-collapse: collapse;">
            <thead>
                <tr style="background-color:#35506b; color:white;">
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['nombre']) ?></td>
                    <td><?= htmlspecialchars($u['correo']) ?></td>
                    <td><?= $u['rol'] ?></td>
                    <td>
                        <a class="btn btn-primary" href="editar.php?id=<?= $u['id'] ?>">Editar</a>
                        <?php if ($u['id'] != 1): ?>
                            <a class="btn logout-btn" href="eliminar.php?id=<?= $u['id'] ?>" onclick="return confirm('¿Eliminar usuario?')">Eliminar</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
