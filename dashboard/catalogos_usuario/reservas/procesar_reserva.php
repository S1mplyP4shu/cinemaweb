<?php
include('../../../includes/session.php');
verificarRol('cliente');
include('../../../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['id'];
    $funcion_id = intval($_POST['id_funcion']);
    $asientos = $_POST['asientos'] ?? [];

    if (empty($asientos)) {
        echo json_encode(['status' => 'error', 'message' => 'No se seleccionaron asientos.']);
        exit;
    }

    $stmt_check = $conn->prepare("SELECT id FROM asientos_reservados WHERE funcion_id = ? AND asiento = ?");
    $stmt_insert = $conn->prepare("INSERT INTO asientos_reservados (usuario_id, funcion_id, asiento, confirmado) VALUES (?, ?, ?, 0)");

    $insertados = 0;
    $asientos_ids = [];
    foreach ($asientos as $asiento) {
        $stmt_check->bind_param("is", $funcion_id, $asiento);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows === 0) {
            $stmt_insert->bind_param("iis", $usuario_id, $funcion_id, $asiento);
            if ($stmt_insert->execute()) {
                $insertados++;
                $asientos_ids[] = $conn->insert_id;
            }
        }
    }

    $stmt_check->close();
    $stmt_insert->close();
    $conn->close();

    if ($insertados > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Reserva realizada correctamente.',
            'asientos_ids' => $asientos_ids
        ]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => "Los asientos seleccionados ya están ocupados."]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>
