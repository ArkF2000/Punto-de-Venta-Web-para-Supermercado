<?php
session_start();
require_once 'db.php';

// Función para autenticar usuario
function login($correo, $password) {
    global $conn;
    $sql = "SELECT * FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (hash('sha256', $password) === $usuario['password']) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['rol_id'] = $usuario['rol_id'];
            return true;
        }
    }

    return false;
}

// Verifica si hay sesión activa
function verificarSesion() {
    return isset($_SESSION['usuario_id']);
}

// Cierra sesión
function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
