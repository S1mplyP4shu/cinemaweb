<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

// Obtener salas distintas y su capacidad máxima registrada
$resultado = $conn->query("
    SELECT sala, MAX(total_asientos) as capacidad
    FROM funciones
    GROUP BY sala
    ORDER BY sala
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Salas - Administración</title>
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
                    <a href="../../index-admin.php">← Volver al panel</a>
                    <a href="../../dashboard/logout.php">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<main>
    <div class="welcome-section">
        <h1>Gestión de <span class="gold-accent">Salas</span></h1>
        <p>Visualiza las salas registradas a través de las funciones</p>
        <a href="crear.php" class="btn btn-primary">Nueva Sala (crea una función con nueva sala)</a>
    </div>

    <div class="stats-section">
        <table style="width:100%; color:white; text-align:left; border-collapse:collapse;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre de Sala</th>
                    <th>Capacidad Máxima</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                while($sala = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($sala['sala']) ?></td>
                        <td><?= htmlspecialchars($sala['capacidad']) ?></td>
                        <td>
                            <a class="btn btn-primary" href="editar.php?sala=<?= urlencode($sala['sala']) ?>">Editar</a>
                            <a class="btn logout-btn" href="eliminar.php?sala=<?= urlencode($sala['sala']) ?>" onclick="return confirm('¿Seguro que deseas eliminar la sala <?= htmlspecialchars($sala['sala']) ?>? Se eliminarán todas sus funciones.');">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <!--
        <p style="color:#ffc857; margin-top:1rem;">
            Para agregar una sala, crea una nueva función con un nombre de sala distinto.<br>
            Para editar la capacidad, edita la función correspondiente.
        </p>
        -->
    </div>

    <!-- Modal Editar Sala -->
    <div id="modal-editar" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
        <form id="form-editar-sala" style="background:#23283a; padding:2rem; border-radius:10px; max-width:350px; margin:auto;">
            <h2 style="color:#ffc857;">Editar Sala</h2>
            <input type="hidden" name="sala_vieja" id="editar-sala-vieja">
            <div class="form-group">
                <label>Nuevo nombre:</label>
                <input type="text" name="sala_nueva" id="editar-sala-nueva" required>
            </div>
            <div class="form-group">
                <label>Nueva capacidad:</label>
                <input type="number" name="nueva_capacidad" id="editar-capacidad" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <button type="button" class="btn logout-btn" onclick="cerrarModalEditar()">Cancelar</button>
        </form>
    </div>

    <!-- Modal Eliminar Sala -->
    <div id="modal-eliminar" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
        <form id="form-eliminar-sala" style="background:#23283a; padding:2rem; border-radius:10px; max-width:350px; margin:auto;">
            <h2 style="color:#ff4d4d;">Eliminar Sala</h2>
            <input type="hidden" name="eliminar_sala" id="eliminar-sala-nombre">
            <p style="color:white;">¿Seguro que deseas eliminar <span id="eliminar-sala-label"></span>? Se eliminarán todas sus funciones.</p>
            <button type="submit" class="btn logout-btn">Eliminar</button>
            <button type="button" class="btn btn-primary" onclick="cerrarModalEliminar()">Cancelar</button>
        </form>
    </div>
</main>

<footer>
    <p>&copy; 2025 CinemaWeb - Administración de Salas</p>
</footer>

<script>
function cerrarModalEditar() {
    document.getElementById('modal-editar').style.display = 'none';
}
function cerrarModalEliminar() {
    document.getElementById('modal-eliminar').style.display = 'none';
}

document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.onclick = function() {
        document.getElementById('editar-sala-vieja').value = this.dataset.sala;
        document.getElementById('editar-sala-nueva').value = this.dataset.sala;
        document.getElementById('editar-capacidad').value = this.dataset.capacidad;
        document.getElementById('modal-editar').style.display = 'flex';
    }
});
document.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.onclick = function() {
        document.getElementById('eliminar-sala-nombre').value = this.dataset.sala;
        document.getElementById('eliminar-sala-label').textContent = this.dataset.sala;
        document.getElementById('modal-eliminar').style.display = 'flex';
    }
});

// Enviar editar sala
document.getElementById('form-editar-sala').onsubmit = function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('editar_sala.php', {method:'POST', body:fd})
    .then(r=>r.json()).then(data=>{
        alert(data.mensaje);
        if(data.status==='success') location.reload();
    });
};

// Enviar eliminar sala
document.getElementById('form-eliminar-sala').onsubmit = function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch('eliminar_sala.php', {method:'POST', body:fd})
    .then(r=>r.json()).then(data=>{
        alert(data.mensaje);
        if(data.status==='success') location.reload();
    });
};
</script>
</body>
</html>
