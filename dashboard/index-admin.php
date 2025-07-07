<?php
include('../includes/session.php');
verificarRol('admin');
include('../includes/db.php');

// Total de películas
$peliculas = $conn->query("SELECT COUNT(*) as total FROM peliculas")->fetch_assoc()['total'];

// Total de salas (DISTINCT sala en funciones)
$salas = $conn->query("SELECT COUNT(DISTINCT sala) as total FROM funciones")->fetch_assoc()['total'];

// Total de funciones
$funciones = $conn->query("SELECT COUNT(*) as total FROM funciones")->fetch_assoc()['total'];

// Total de usuarios
$usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];

$admin_nombre = $_SESSION['nombre'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Admin - CinemaWeb</title>
    <link rel="stylesheet" href="styles/admin_styles.css">
</head>

<body>
    <header>
        <span style="font-size:18px;">CinemaWeb - Admin</span>
        <span style="float:right;">Bienvenido, <?= htmlspecialchars($admin_nombre) ?> | <a href="logout.php"
                style="color:#fff;">Salir</a></span>
    </header>
    <div class="container">
        <div class="bienvenida">
            <h1>Panel de Administración</h1>
            <p>Hola <?= htmlspecialchars($admin_nombre) ?>, aquí puedes gestionar el sistema.</p>
            <p style="font-size:12px;color:#666;">Fecha: <?= date('d/m/Y H:i') ?></p>
        </div>
        <div class="estadisticas">
            <div class="estadistica">
                <strong><?= $peliculas ?></strong>
                Películas
            </div>
            <div class="estadistica">
                <strong><?= $salas ?></strong>
                Salas
            </div>
            <div class="estadistica">
                <strong><?= $funciones ?></strong>
                Funciones
            </div>
            <div class="estadistica">
                <strong><?= $usuarios ?></strong>
                Usuarios
            </div>
        </div>
        <div class="acciones">
            <a href="catalogos/peliculas/">Películas</a>
            <a href="catalogos/salas/">Salas</a>
            <a href="catalogos/funciones/">Funciones</a>
            <a href="catalogos/usuarios/">Usuarios</a>
            <button onclick="alert('Función en desarrollo')">Otra Acción</button>
        </div>
        
    </div>
    <footer class="footer">
        &copy; 2024 CinemaWeb | Panel Admin
    </footer>
</body>

</html>