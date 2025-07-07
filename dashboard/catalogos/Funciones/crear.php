<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

// Establecer la zona horaria
date_default_timezone_set('America/Mexico_City');

$mensaje = "";

// Películas para el select (excluye la dummy)
$peliculas = $conn->query("SELECT id, titulo FROM peliculas WHERE id <> 4");

// Solo salas válidas (las que tienen función dummy)
$salas = $conn->query("SELECT DISTINCT sala FROM funciones WHERE pelicula_id = 4 AND fecha = '0000-00-00' AND hora = '00:00:00' ORDER BY sala");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pelicula_id = $_POST['pelicula_id'];
    $sala = trim($_POST['sala']);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    // Validación de fecha y hora futuras
    $fechaHoraSeleccionada = strtotime("$fecha $hora");
    $ahora = strtotime(date('Y-m-d H:i')); // Redondea a minutos

    if ($fechaHoraSeleccionada < $ahora) {
        $mensaje = "No puedes seleccionar una fecha y hora pasada.";
    } else {
        // Verificar que no exista ya una función para esa sala, fecha y hora
        $stmt_check = $conn->prepare("SELECT id FROM funciones WHERE sala = ? AND fecha = ? AND hora = ?");
        $stmt_check->bind_param("sss", $sala, $fecha, $hora);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $mensaje = "Ya existe una función para esa sala, fecha y hora.";
        } else {
            // Buscar la capacidad de la sala seleccionada (de la función dummy)
            $stmt_cap = $conn->prepare("SELECT total_asientos FROM funciones WHERE sala = ? AND pelicula_id = 4 AND fecha = '0000-00-00' AND hora = '00:00:00' LIMIT 1");
            $stmt_cap->bind_param("s", $sala);
            $stmt_cap->execute();
            $res_cap = $stmt_cap->get_result();
            $row_cap = $res_cap->fetch_assoc();
            $capacidad = $row_cap ? intval($row_cap['total_asientos']) : 0;

            if ($capacidad > 0) {
                // Insertar nueva función
                $stmt = $conn->prepare("INSERT INTO funciones (pelicula_id, sala, fecha, hora, total_asientos) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isssi", $pelicula_id, $sala, $fecha, $hora, $capacidad);

                if ($stmt->execute()) {
                    header("Location: index.php");
                    exit;
                } else {
                    $mensaje = "Error: " . $stmt->error;
                }
            } else {
                $mensaje = "Error: No se encontró la capacidad de la sala seleccionada.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Función</title>
    <link rel="stylesheet" href="styles/styles_crear.css">
</head>
<body>
    <h1>Agregar Función</h1>
    <?php if ($mensaje): ?>
        <p><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>Película:</label>
        <select name="pelicula_id" required>
            <?php while($p = $peliculas->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['titulo']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Sala:</label>
        <select name="sala" required>
            <?php while($s = $salas->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($s['sala']) ?>"><?= htmlspecialchars($s['sala']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Fecha:</label>
        <input type="date" name="fecha" id="fecha" required>

        <label>Hora:</label>
        <input type="time" name="hora" id="hora" required>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="index.php" class="btn logout-btn">Cancelar</a>
    </form>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('fecha');
    const horaInput = document.getElementById('hora');

    // Establece la fecha mínima y el valor inicial a hoy (local)
    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, '0');
    const dd = String(hoy.getDate()).padStart(2, '0');
    const hoyStr = `${yyyy}-${mm}-${dd}`;
    fechaInput.min = hoyStr;
    fechaInput.value = hoyStr;

    function setMinHora() {
        const hoy = new Date();
        const fechaSeleccionada = fechaInput.value;
        if (fechaSeleccionada === hoyStr) {
            let horas = hoy.getHours().toString().padStart(2, '0');
            let minutos = hoy.getMinutes().toString().padStart(2, '0');
            horaInput.min = `${horas}:${minutos}`;
        } else {
            horaInput.min = '';
        }
    }

    fechaInput.addEventListener('change', function() {
        setMinHora();
        // Si la hora seleccionada es menor que el nuevo mínimo, límpiala
        if (horaInput.value && horaInput.value < horaInput.min) {
            horaInput.value = '';
        }
    });

    horaInput.addEventListener('focus', setMinHora);

    // Inicializa al cargar
    setMinHora();
});
</script>
</body>
</html>
