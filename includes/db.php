<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$bd = "super_joshua";

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $bd);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
