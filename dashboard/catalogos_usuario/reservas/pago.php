<?php
include('../../../includes/session.php');
verificarRol('cliente');
include('../../../includes/db.php');

$id_funcion = isset($_GET['id_funcion']) ? intval($_GET['id_funcion']) : 0;
$asientos = isset($_GET['asientos']) ? explode(',', $_GET['asientos']) : [];

$errores = [];
$exito = false;

// Obtener info de la función y película para el resumen
$info = null;
if ($id_funcion > 0) {
    $stmt = $conn->prepare("SELECT f.fecha, f.hora, f.sala, p.titulo FROM funciones f JOIN peliculas p ON f.pelicula_id = p.id WHERE f.id = ?");
    $stmt->bind_param("i", $id_funcion);
    $stmt->execute();
    $res = $stmt->get_result();
    $info = $res->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_funcion = intval($_POST['id_funcion']);
    $asientos = explode(',', $_POST['asientos']);
    // ...validaciones de pago...
    if (empty($errores)) {
        // Insertar los asientos SOLO AHORA
        $stmt_check = $conn->prepare("SELECT id FROM asientos_reservados WHERE funcion_id = ? AND asiento = ?");
        // CORRECCIÓN: Elimina la columna 'confirmado' pues no existe en tu modelo de datos
        $stmt_insert = $conn->prepare("INSERT INTO asientos_reservados (usuario_id, funcion_id, asiento) VALUES (?, ?, ?)");
        $usuario_id = $_SESSION['id'];
        $insertados = 0;
        foreach ($asientos as $asiento) {
            $stmt_check->bind_param("is", $id_funcion, $asiento);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows === 0) {
                $stmt_insert->bind_param("iis", $usuario_id, $id_funcion, $asiento);
                $stmt_insert->execute();
                $insertados++;
            }
        }
        $stmt_insert->close();

        // Verifica si todos los asientos ya están reservados por el usuario
        if ($insertados > 0) {
            $exito = true;
        } else {
            // Checa si ya existen para este usuario y función
            $todos_reservados = true;
            foreach ($asientos as $asiento) {
                $stmt_check->bind_param("is", $id_funcion, $asiento);
                $stmt_check->execute();
                $stmt_check->store_result();
                if ($stmt_check->num_rows === 0) {
                    $todos_reservados = false;
                    break;
                }
            }
            $exito = $todos_reservados;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pago de Reserva - CinemaWeb</title>
    <link rel="stylesheet" href="../reservas/styles/style.css">
</head>
<body>
    <main>
    <div class="pago-form">
        <h2 class="titulo-pago">Pago de Reserva</h2>
        <div class="resumen-pago">
            <h4>Resumen:</h4>
            <p><strong>Película:</strong> <?= htmlspecialchars($info['titulo']) ?></p>
            <p><strong>Fecha:</strong> <?= htmlspecialchars($info['fecha']) ?> | <strong>Hora:</strong> <?= htmlspecialchars(substr($info['hora'],0,5)) ?></p>
            <p><strong>Sala:</strong> <?= htmlspecialchars($info['sala']) ?></p>
            <p><strong>Asientos:</strong> <?= htmlspecialchars(implode(', ', $asientos)) ?></p>
        </div>
        <hr>
        <?php if ($exito): ?>
            <div class="comprobante">
                <h2>¡Pago realizado con éxito!</h2>
                <p><strong>Código de reserva:</strong> <?= uniqid('RES-') ?></p>
                <p><strong>Película:</strong> <?= htmlspecialchars($info['titulo']) ?></p>
                <p><strong>Sala:</strong> <?= htmlspecialchars($info['sala']) ?></p>
                <p><strong>Fecha:</strong> <?= htmlspecialchars($info['fecha']) ?></p>
                <p><strong>Hora:</strong> <?= htmlspecialchars(substr($info['hora'],0,5)) ?></p>
                <p><strong>Asientos:</strong> <?= htmlspecialchars(implode(', ', $asientos)) ?></p>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($_SESSION['nombre']) ?></p>
                <button onclick="window.print()" class="btn-reservar">Imprimir comprobante</button>
            </div>
            <a href="../../index-cliente.php" class="btn-reservar" style="margin-top:1rem;">Volver al inicio</a>
        <?php endif; ?>
        <?php if (!$exito): ?>
            <?php if ($errores): ?>
                <div class="errores"><?= implode('<br>', array_map('htmlspecialchars', $errores)) ?></div>
            <?php endif; ?>
            <form method="POST" autocomplete="off" id="form-pago" class="formulario-pago">
                <input type="hidden" name="id_funcion" value="<?= htmlspecialchars($id_funcion) ?>">
                <input type="hidden" name="asientos" value="<?= htmlspecialchars(implode(',', $asientos)) ?>">
                <label>Nombre en la tarjeta:
                    <input type="text" name="nombre" required minlength="3" maxlength="60" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios">
                </label>
                <label>Número de tarjeta:
                    <input type="text" name="tarjeta" required maxlength="19" pattern="\d{16}" inputmode="numeric" placeholder="1234 5678 9012 3456">
                </label>
                <label>Expiración (MM/AA):
                    <input type="text" name="exp" required pattern="^(0[1-9]|1[0-2])\/\d{2}$" placeholder="MM/AA">
                </label>
                <label>CVV:
                    <input type="password" name="cvv" required maxlength="4" pattern="\d{3,4}" inputmode="numeric">
                </label>
                <label>Email de confirmación:
                    <input type="email" name="email" required maxlength="80">
                </label>
                <button type="submit" class="btn-reservar">Simular pago</button>
            </form>
        <?php endif; ?>
    </div>
</main>
    <script>
    // Validación extra en frontend
    document.getElementById('form-pago')?.addEventListener('submit', function(e) {
        const nombre = this.nombre.value.trim();
        const tarjeta = this.tarjeta.value.replace(/\D/g, '');
        const exp = this.exp.value.trim();
        const cvv = this.cvv.value.trim();
        const email = this.email.value.trim();

        let errores = [];
        if (nombre.length < 3 || nombre.length > 60) errores.push("Nombre inválido.");
        if (!/^\d{16}$/.test(tarjeta)) errores.push("Número de tarjeta inválido.");
        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(exp)) errores.push("Fecha de expiración inválida.");
        if (!/^\d{3,4}$/.test(cvv)) errores.push("CVV inválido.");
        if (!/^[^@]+@[^@]+\.[^@]+$/.test(email)) errores.push("Correo electrónico inválido.");

        if (errores.length > 0) {
            e.preventDefault();
            alert(errores.join('\n'));
        }
    });
    </script>
</body>
</html>