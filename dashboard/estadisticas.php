<?php
include('../includes/session.php');
verificarRol('admin');
include('../includes/db.php');

header('Content-Type: application/json');

$stats = [];
$stats['peliculas'] = $conn->query("SELECT COUNT(*) as total FROM peliculas")->fetch_assoc()['total'] ?? 0;
$stats['salas'] = $conn->query("SELECT COUNT(*) as total FROM salas")->fetch_assoc()['total'] ?? 0;
$stats['funciones'] = $conn->query("SELECT COUNT(*) as total FROM funciones WHERE fecha >= CURDATE()")->fetch_assoc()['total'] ?? 0;
$stats['usuarios'] = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente'")->fetch_assoc()['total'] ?? 0;

echo json_encode($stats);