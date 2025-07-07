<?php
include('../../../includes/session.php');
verificarRol('admin');
include('../../../includes/db.php');

if (isset($_GET['sala'])) {
    $sala = $_GET['sala'];

    $stmt = $conn->prepare("DELETE FROM funciones WHERE sala = ?");
    $stmt->bind_param("s", $sala);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error al eliminar: " . $stmt->error;
    }
}
?>
