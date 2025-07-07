<?php
include('../../../includes/db.php');
include('../../../includes/session.php');
verificarRol('admin');

header('Content-Type: application/xml');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo '<response><status>error</status><message>Solicitud inválida</message></response>';
    exit;
}

$id = intval($_POST['id']);

// Elimina funciones relacionadas primero
$stmtFunciones = $conn->prepare("DELETE FROM funciones WHERE pelicula_id = ?");
$stmtFunciones->bind_param("i", $id);
$stmtFunciones->execute();
$stmtFunciones->close();

// Ahora elimina la película
$stmt = $conn->prepare("DELETE FROM peliculas WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo '<response><status>success</status><message>Película eliminada</message></response>';
} else {
    echo '<response><status>error</status><message>Error al eliminar la película.</message></response>';
}
?>