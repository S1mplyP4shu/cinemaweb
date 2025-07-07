<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);

// Opcional: No permitir eliminar admins
$usuario = $conn->query("SELECT rol FROM usuarios WHERE id = $id")->fetch_assoc();
if (!$usuario || $usuario['rol'] === 'admin') {
    header('Location: index.php');
    exit;
}

// Eliminar usuario
$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header('Location: index.php');
exit;