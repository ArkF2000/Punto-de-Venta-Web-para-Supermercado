<?php
require_once 'includes/auth.php';
if (!verificarSesion() || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}
require_once 'includes/db.php';

$id = $_POST['id'] ?? null;
$nombre = $_POST['nombre'] ?? null;
$descripcion = $_POST['descripcion'] ?? null;
$categoria_id = $_POST['categoria_id'] ?: null;
$precio_compra = $_POST['precio_compra'] ?? 0;
$precio_venta = $_POST['precio_venta'] ?? 0;
$existencia = $_POST['existencia'] ?? 0;

if ($id) {
    // Actualizar producto existente
    $stmt = $conn->prepare("UPDATE productos SET nombre=?, descripcion=?, categoria_id=?, precio_compra=?, precio_venta=?, existencia=? WHERE id=?");
    $stmt->bind_param("ssiddii", $nombre, $descripcion, $categoria_id, $precio_compra, $precio_venta, $existencia, $id);
} else {
    // Insertar nuevo producto - CORREGIDO: removí el parámetro extra
    $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, categoria_id, precio_compra, precio_venta, existencia) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiddi", $nombre, $descripcion, $categoria_id, $precio_compra, $precio_venta, $existencia);
}

$stmt->execute();

if (!$id) {
    $id = $conn->insert_id;
}

// NUEVA FUNCIONALIDAD: Generar código QR
function generarQR($producto_id) {
    // Crear directorio si no existe
    if (!file_exists('qr_codes')) {
        mkdir('qr_codes', 0777, true);
    }
    
    // Usar API gratuita de QR Server para generar el QR
    $qr_data = $producto_id; // El ID del producto será el contenido del QR
    $qr_size = '200x200'; // Tamaño del QR
    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size={$qr_size}&data={$qr_data}";
    
    // Descargar y guardar la imagen QR
    $qr_filename = "qr_codes/producto_{$producto_id}.png";
    $qr_image = file_get_contents($qr_url);
    
    if ($qr_image !== false) {
        file_put_contents($qr_filename, $qr_image);
        return $qr_filename;
    }
    
    return false;
}

// Generar QR para el producto (nuevo o actualizado)
$qr_path = generarQR($id);

// Actualizar la base de datos con la ruta del QR si se generó exitosamente
if ($qr_path) {
    $stmt_qr = $conn->prepare("UPDATE productos SET qr_code_path=? WHERE id=?");
    $stmt_qr->bind_param("si", $qr_path, $id);
    $stmt_qr->execute();
}

header("Location: producto_agregado.php?id=" . $id);
exit;
?>