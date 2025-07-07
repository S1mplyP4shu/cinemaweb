<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$resultado = $conn->query("SELECT * FROM peliculas");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Películas - Admin</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">Cinema<span style="color: #FFC857;">Web</span></div>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?></div>
                    <span><?= htmlspecialchars($_SESSION['nombre']) ?></span>
                </div>
                <div class="dropdown">
                    <span class="admin-badge">Administrador</span>
                    <div class="dropdown-content">
                        <a href="../../index-admin.php">Panel Principal</a>
                        <a href="../../logout.php">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="welcome-section">
            <h1>Gestión de Películas</h1>
            <p>Aquí puedes ver, agregar, editar o eliminar películas</p>
            <a href="crear.php" class="btn btn-primary">+ Agregar nueva película</a>
        </div>

        <div class="stats-section">
            <h2 class="stats-title">Lista de Películas</h2>
            <div class="stats-grid">
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($pelicula = $resultado->fetch_assoc()): ?>
                        <div class="stat-item">
                            <div class="stat-number"><?= htmlspecialchars($pelicula['titulo']) ?></div>
                            <div class="stat-label">
                                <?= htmlspecialchars($pelicula['genero']) ?> | <?= htmlspecialchars($pelicula['duracion_min']) ?> min
                            </div>
                            <div>
                                <a href="editar.php?id=<?= $pelicula['id'] ?>" class="btn btn-primary">
                                    Editar
                                </a>
                                <button type="button" class="btn logout-btn eliminar-btn" data-id="<?= $pelicula['id'] ?>">
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="stat-item" style="grid-column: 1 / -1;">
                        <div class="stat-number">No hay películas</div>
                        <div class="stat-label">Agrega tu primera película usando el botón de arriba</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 <span style="color: #FFC857;">CinemaWeb</span>. Todos los derechos reservados.</p>
    </footer>

    <!-- Modal de confirmación -->
    <div id="modal-confirm" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
      <div style="background: rgb(255, 72, 59); padding:2rem; border-radius:8px; max-width:90vw; text-align:center;">
        <h2>¿Estás seguro de eliminar esta película?</h2>
        <div style="margin-top:1.5rem;">
          <button id="btn-confirmar" class="btn btn-primary">Sí, eliminar</button>
          <button id="btn-cancelar" class="btn logout-btn">Cancelar</button>
        </div>
      </div>
    </div>

    <script>
const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
let peliculaAEliminar = null;

document.querySelectorAll('.eliminar-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        peliculaAEliminar = this.getAttribute('data-id');
        document.getElementById('modal-confirm').style.display = 'flex';
    });
});

document.getElementById('btn-cancelar').onclick = function() {
    document.getElementById('modal-confirm').style.display = 'none';
    peliculaAEliminar = null;
};

document.getElementById('btn-confirmar').onclick = function() {
    if (!peliculaAEliminar) return;
    const formData = new FormData();
    formData.append('id', peliculaAEliminar);
    formData.append('csrf_token', csrfToken);

    fetch('eliminar.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(str => (new window.DOMParser()).parseFromString(str, "text/xml"))
    .then(data => {
        const status = data.querySelector('status').textContent;
        if (status === 'success') location.reload();
        else document.getElementById('modal-confirm').style.display = 'none';
    });
};
    </script>
</body>
</html>
