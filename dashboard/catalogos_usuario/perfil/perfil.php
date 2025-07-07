<?php
include('../../../includes/session.php');
verificarRol('cliente');
$nombre = $_SESSION['nombre'];
$correo = isset($_SESSION['correo']) ? $_SESSION['correo'] : 'No disponible';
$inicial = strtoupper(substr($nombre, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil | CinemaWeb</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

<header>
    <nav>
        <div class="logo">Cinema<span class="gold-accent">Web</span></div>
        <div class="nav-links">
            <a href="../../index-cliente.php">Inicio</a>
            <a href="perfil.php" class="active">Perfil</a>
        </div>
        <div class="user-menu">
            <div class="user-info">¡Hola, <?= htmlspecialchars($nombre) ?>!</div>
            <div class="dropdown">
                <div class="user-avatar"><?= $inicial ?></div>
                <div class="dropdown-content">
                    <a href="perfil.php">Mi Perfil</a>
                    <a href="../../../logout.php">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<main>
    <section class="perfil-container">
        <h2>Mi Perfil</h2>
        <div class="perfil-dato">
            <label>Nombre:</label>
            <span><?= htmlspecialchars($nombre) ?></span>
        </div>
        <div class="perfil-dato">
            <label>Correo electrónico:</label>
            <span><?= htmlspecialchars($correo) ?></span>
        </div>
        <!-- (Opcional) botón para futura edición -->
        <a href="editar.php" class="btn-editar">Editar Perfil</a>
    </section>
</main>

<footer>
    <p>&copy; 2025 <span class="gold-accent">CinemaWeb</span>. Todos los derechos reservados.</p>
</footer>

</body>
</html>
