<?php
require_once 'includes/auth.php';
if (!verificarSesion() || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $conn->query("DELETE FROM usuarios WHERE id = $id");
}
header("Location: usuarios.php");
