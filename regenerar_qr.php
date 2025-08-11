<?php
require_once 'includes/auth.php';
if (!verificarSesion() || $_SESSION['rol_id'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once 'includes/db.php';

// Leer datos JSON
$input = json_decode(file_get_contents('php://input'), true);
$producto_id = $input['producto_id'] ?? null;

if (!$producto_id) {
    echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
    exit;
}

// Función para generar QR
function generarQR($producto_id) {
    // Crear directorio si no existe
    if (!file_exists('qr_codes')) {
        mkdir('qr_codes', 0777, true);
    }
    
    // Usar API gratuita de QR Server para generar el QR
    $qr_data = $producto_id;
    $qr_size = '200x200';
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

try {
    // Generar nuevo QR
    $qr_path = generarQR($producto_id);
    
    if ($qr_path) {
        // Actualizar la base de datos
        $stmt = $conn->prepare("UPDATE productos SET qr_code_path = ? WHERE id = ?");
        $stmt->bind_param("si", $qr_path, $producto_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'QR regenerado exitosamente',
                'qr_path' => $qr_path
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar base de datos']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al generar QR']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>