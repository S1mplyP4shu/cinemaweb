<?php
include('../../../includes/session.php');
verificarRol('cliente');
include('../../../includes/db.php');

$data = json_decode(file_get_contents('php://input'), true);
$ids = isset($data['ids']) ? $data['ids'] : '';

if (!$ids) {
    echo json_encode(['status' => 'error', 'message' => 'No se recibieron IDs.']);
    exit;
}

$ids_arr = array_filter(array_map('intval', explode(',', $ids)));
if (empty($ids_arr)) {
    echo json_encode(['status' => 'error', 'message' => 'IDs inválidos.']);
    exit;
}

$ids_str = implode(',', $ids_arr);
$usuario_id = $_SESSION['id'];
$sql = "DELETE FROM asientos_reservados WHERE id IN ($ids_str) AND usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar.']);
}
$stmt->close();
$conn->close();
?>