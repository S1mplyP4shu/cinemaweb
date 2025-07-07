<?php
include('../../../includes/session.php');
verificarRol('cliente');
include('../../../includes/db.php');

$usuario_id = $_SESSION['id'];

$query = "
SELECT 
    p.titulo AS pelicula,
    f.sala,
    CONCAT(f.fecha, ' ', LEFT(f.hora, 5)) AS horario,
    GROUP_CONCAT(ar.asiento ORDER BY ar.asiento SEPARATOR ', ') AS boletos,
    GROUP_CONCAT(ar.id ORDER BY ar.asiento SEPARATOR ',') AS ids
FROM asientos_reservados ar
JOIN funciones f ON ar.funcion_id = f.id
JOIN peliculas p ON f.pelicula_id = p.id
WHERE ar.usuario_id = ?
GROUP BY ar.funcion_id, f.fecha, f.hora, f.sala, p.titulo
ORDER BY f.fecha DESC, f.hora DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$reservas = [];
while ($row = $result->fetch_assoc()) {
    $reservas[] = $row;
}

header('Content-Type: application/json');
echo json_encode($reservas);
?>