<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

// Recibe el nombre de la sala por GET
if (!isset($_GET['sala'])) {
    header('Location: index.php');
    exit;
}

$sala_vieja = $_GET['sala'];

// Obtén la capacidad máxima actual de la sala
$stmt = $conn->prepare("SELECT MAX(total_asientos) as capacidad FROM funciones WHERE sala = ?");
$stmt->bind_param("s", $sala_vieja);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$capacidad_actual = $row ? $row['capacidad'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sala_nueva = trim($_POST['sala_nueva']);
    $nueva_capacidad = intval($_POST['nueva_capacidad']);

    $errores = [];
    if (!$sala_nueva || $nueva_capacidad < 1) {
        $errores[] = "Todos los campos son obligatorios y la capacidad debe ser mayor a 0.";
    }

    // Validar para cada función de la sala
    $stmt = $conn->prepare("SELECT id FROM funciones WHERE sala = ?");
    $stmt->bind_param("s", $sala_vieja);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($funcion = $result->fetch_assoc()) {
        $funcion_id = $funcion['id'];
        // Obtener todos los asientos reservados
        $stmt2 = $conn->prepare("SELECT asiento FROM asientos_reservados WHERE funcion_id = ?");
        $stmt2->bind_param("i", $funcion_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        $filas = [];
        while ($row = $result2->fetch_assoc()) {
            // Ejemplo de asiento: "A10", "B3", etc.
            if (preg_match('/^([A-Z])(\d+)$/i', $row['asiento'], $matches)) {
                $fila = strtoupper($matches[1]);
                $num = intval($matches[2]);
                if (!isset($filas[$fila]) || $num > $filas[$fila]) {
                    $filas[$fila] = $num;
                }
            }
        }
        $stmt2->close();

        if (!empty($filas)) {
            // Ordenar filas por letra
            ksort($filas);
            $letras = array_keys($filas);
            $ultima_fila = end($letras);
            $capacidad_minima = 0;
            foreach ($filas as $fila => $max_num) {
                if ($fila !== $ultima_fila) {
                    $capacidad_minima += 10; 
                } else {
                    $capacidad_minima += $max_num;
                }
            }
            if ($nueva_capacidad < $capacidad_minima) {
                $errores[] = "No puedes reducir la capacidad de la función por debajo de $capacidad_minima, ya que el asiento reservado más alto es $ultima_fila{$filas[$ultima_fila]}.";
            }
        }
    }
    $stmt->close();

    if (empty($errores)) {
        // Cambia el nombre de la sala y la capacidad en todas las funciones
        $stmt = $conn->prepare("UPDATE funciones SET sala = ?, total_asientos = ? WHERE sala = ?");
        $stmt->bind_param("sis", $sala_nueva, $nueva_capacidad, $sala_vieja);
        $stmt->execute();

        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Sala</title>
    <link rel="stylesheet" href="../peliculas/styles/styles_crear.css">
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
        </div>
    </nav>
</header>

<main>
    <div class="welcome-section">
        <h1>Editar <span class="gold-accent">Sala</span></h1>
        <a href="index.php" class="btn logout-btn">← Volver</a>
    </div>

    <div class="stats-section">
        <?php if (!empty($errores)): ?>
            <div style="color:#ff4d4d; text-align:center;"><?= implode('<br>', array_map('htmlspecialchars', $errores)) ?></div>
        <?php endif; ?>
        <form method="POST" id="form-editar">
            <div class="form-group">
                <label>Nombre de sala:</label>
                <input type="text" name="sala_nueva" value="<?= htmlspecialchars($sala_vieja) ?>" required>
            </div>
            <div class="form-group">
                <label>Capacidad (asientos):</label>
                <input type="number" name="nueva_capacidad" value="<?= htmlspecialchars($capacidad_actual) ?>" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="index.php" class="btn logout-btn">Cancelar</a>
        </form>
    </div>
</main>
</body>
</html>