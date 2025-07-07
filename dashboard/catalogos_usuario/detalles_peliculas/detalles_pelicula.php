<?php
include('../../../includes/session.php');
verificarRol('cliente');
include('../../../includes/db.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../index-cliente.php');
    exit;
}

$id_pelicula = intval($_GET['id']);

// Obtener datos de la película
$stmt = $conn->prepare("SELECT * FROM peliculas WHERE id = ?");
$stmt->bind_param("i", $id_pelicula);
$stmt->execute();
$resultado = $stmt->get_result();
$pelicula = $resultado->fetch_assoc();

if (!$pelicula) {
    echo "Película no encontrada.";
    exit;
}

// Obtener funciones de la película
$funciones_stmt = $conn->prepare("SELECT * FROM funciones WHERE pelicula_id = ? ORDER BY fecha, hora");
$funciones_stmt->bind_param("i", $id_pelicula);
$funciones_stmt->execute();
$funciones_result = $funciones_stmt->get_result();
$funciones = [];
while ($f = $funciones_result->fetch_assoc()) {
    $funciones[] = $f;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pelicula['titulo']) ?> - Detalles</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

<header>
    <nav>
        <div class="logo">Cinema<span class="gold-accent">Web</span></div>
        <div class="nav-links">
            <a href="../../index-cliente.php">Inicio</a>
            <a href="../reservas/reservas.html">Reservar</a>
        </div>
        <div class="user-menu">
            <span>Hola, <?= htmlspecialchars($_SESSION['nombre']) ?></span>
        </div>
    </nav>
</header>

<main>
    <section class="welcome-section">
        <h1><?= htmlspecialchars($pelicula['titulo']) ?></h1>
        <p><?= htmlspecialchars($pelicula['genero']) ?> • <?= htmlspecialchars($pelicula['clasificacion']) ?> • <?= htmlspecialchars($pelicula['duracion_min']) ?> min</p>
        <img src="../../../images/<?= htmlspecialchars($pelicula['imagen_url']) ?>" style="max-width:200px; border-radius:8px;" alt="<?= htmlspecialchars($pelicula['titulo']) ?>">
        <p style="margin-top:1rem;"><?= htmlspecialchars($pelicula['sinopsis']) ?></p>
    </section>

    <section class="stats-section">
        <h2>Funciones disponibles</h2>
        <div class="funciones-grid">
        <?php if (count($funciones) > 0): ?>
            <?php foreach ($funciones as $funcion): 
                $total_asientos = (int)$funcion['total_asientos'];
                $asientos_ocupados = [];
                // CORRECCIÓN: se quitó 'AND confirmado = 1'
                $asientos_stmt = $conn->prepare("SELECT asiento FROM asientos_reservados WHERE funcion_id = ?");
                $asientos_stmt->bind_param("i", $funcion['id']);
                $asientos_stmt->execute();
                $asientos_result = $asientos_stmt->get_result();
                while ($row = $asientos_result->fetch_assoc()) {
                    $asientos_ocupados[] = $row['asiento'];
                }
                $asientos_stmt->close();

                // Calcular filas y columnas
                $max_por_fila = 10;
                $filas_necesarias = ceil($total_asientos / $max_por_fila);
                $letras = range('A', 'Z');
                $asiento_num = 1;
            ?>
            <div class="funcion-card" style="margin-bottom:2rem; border:1px solid #35506b; border-radius:10px; padding:1rem; background:#22344a;">
                <form action="../reservas/procesar_reserva.php" method="POST">
                    <input type="hidden" name="id_funcion" value="<?= $funcion['id'] ?>">
                    <p>
                        <strong>Fecha:</strong> <?= htmlspecialchars($funcion['fecha']) ?> |
                        <strong>Hora:</strong> <?= htmlspecialchars(substr($funcion['hora'], 0, 5)) ?> |
                        <strong>Sala:</strong> <?= htmlspecialchars($funcion['sala']) ?>
                    </p>
                    <div>
                        <strong>Selecciona tus asientos:</strong><br>
                        <?php
                        for ($fila = 0; $fila < $filas_necesarias; $fila++) {
                            echo "<div class='seat-row'>";
                            for ($col = 1; $col <= $max_por_fila; $col++) {
                                if ($asiento_num > $total_asientos) break;
                                $asiento = $letras[$fila] . $col;
                                $ocupado = in_array($asiento, $asientos_ocupados);
                                $disabled = $ocupado ? 'disabled' : '';
                                $class = 'seat-btn' . ($ocupado ? ' disabled' : '');
                                echo "<label>";
                                echo "<input type='checkbox' name='asientos[]' value='$asiento' style='display:none;' $disabled>";
                                echo "<span class='$class'>$asiento</span>";
                                echo "</label>";
                                $asiento_num++;
                            }
                            echo "</div>";
                        }
                        ?>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:8px;">Reservar asientos</button>
                </form>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay funciones disponibles para esta película.</p>
        <?php endif; ?>
        </div>
    </section>

    <div class="leyenda">
        <div class="leyenda-item"><span class="leyenda-color disponible"></span> Disponible</div>
        <div class="leyenda-item"><span class="leyenda-color ocupado"></span> Ocupado</div>
        <div class="leyenda-item"><span class="leyenda-color seleccionado-leyenda"></span> Seleccionado</div>
    </div>
</main>

<footer>
    <p>&copy; 2025 CinemaWeb. Todos los derechos reservados.</p>
</footer>

</body>
</html>
<script>
document.querySelectorAll('form').forEach(form => {
    form.querySelectorAll('label').forEach(label => {
        const input = label.querySelector('input[type=checkbox]');
        const span = label.querySelector('span');
        if (input && span && !input.disabled) {
            // Al hacer clic en el label, cambia el estado del checkbox y el estilo visual
            label.addEventListener('click', function(e) {
                // Solo si no está deshabilitado
                if (input.disabled) return;
                // Espera a que el checkbox cambie y luego actualiza el estilo
                setTimeout(() => {
                    span.classList.toggle('selected', input.checked);
                }, 0);
            });
            // También escucha el evento change por si el usuario usa el teclado
            input.addEventListener('change', function() {
                span.classList.toggle('selected', input.checked);
            });
        }
    });

    // Manejo del envío del formulario por AJAX
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        const asientos = [];
        formData.forEach((value, key) => {
            if (key === 'asientos[]') asientos.push(value);
        });
        const id_funcion = form.querySelector('input[name="id_funcion"]').value;
        // Redirige a pago.php con los datos
        window.location.href = '../reservas/pago.php?id_funcion=' + encodeURIComponent(id_funcion) + '&asientos=' + encodeURIComponent(asientos.join(','));
    });
});
</script>
<?php
// Fin del archivo
?>