<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['rol'])) {
    header('Location: ../login.php');
    exit;
}

function verificarRol($rolRequerido) {
    if ($_SESSION['rol'] !== $rolRequerido) {
        header('Location: ../login.php');
        exit;
    }
}
