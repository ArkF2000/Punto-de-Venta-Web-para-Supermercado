<?php
require_once 'includes/auth.php';
if (!verificarSesion() || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}
require_once 'includes/db.php';

$id = $_POST['id'] ?? null;
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$rol_id = $_POST['rol_id'];
$password = $_POST['password'];

if ($id) {
    // Editar usuario
    if (!empty($password)) {
        $password_hash = hash('sha256', $password);
        $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, rol_id=?, password=? WHERE id=?");
        $stmt->bind_param("ssssi", $nombre, $correo, $rol_id, $password_hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, correo=?, rol_id=? WHERE id=?");
        $stmt->bind_param("sssi", $nombre, $correo, $rol_id, $id);
    }
    $stmt->execute();
} else {
    // Nuevo usuario - password obligatorio
    $password_hash = hash('sha256', $password);
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, rol_id, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $nombre, $correo, $rol_id, $password_hash);
    $stmt->execute();
}

header("Location: usuarios.php");
