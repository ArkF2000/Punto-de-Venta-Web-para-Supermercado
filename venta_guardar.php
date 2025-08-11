<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Verificar que el usuario esté logueado
if (!verificarSesion()) {
    header("Location: login.php");
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: venta.php");
    exit;
}

try {
    // Obtener datos del formulario
    $cliente_nombre = isset($_POST['cliente']) ? trim($_POST['cliente']) : 'Cliente General';
    $detalle_json = isset($_POST['detalle']) ? $_POST['detalle'] : '[]';
    $usuario_id = $_SESSION['usuario_id'];
    
    // Decodificar el JSON de productos
    $productos = json_decode($detalle_json, true);
    
    if (!$productos || empty($productos)) {
        throw new Exception("No se han agregado productos a la venta");
    }
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    // Calcular totales
    $subtotal = 0;
    foreach ($productos as $producto) {
        $subtotal += floatval($producto['precio']) * intval($producto['cantidad']);
    }
    
    // Calcular IVA (12%)
    $iva_porcentaje = 0.12;
    $impuesto = $subtotal * $iva_porcentaje;
    $total = $subtotal + $impuesto;
    
    // Insertar la venta principal
    $fecha_venta = date('Y-m-d H:i:s');
    $sql_venta = "INSERT INTO ventas (usuario_id, cliente_nombre, fecha, subtotal, impuesto, total) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_venta = $conn->prepare($sql_venta);
    $stmt_venta->bind_param("issddd", $usuario_id, $cliente_nombre, $fecha_venta, $subtotal, $impuesto, $total);
    
    if (!$stmt_venta->execute()) {
        throw new Exception("Error al guardar la venta: " . $stmt_venta->error);
    }
    
    $venta_id = $conn->insert_id;
    
    // Insertar detalles de la venta y actualizar stock
    $sql_detalle = "INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
    $stmt_detalle = $conn->prepare($sql_detalle);
    
    $sql_update_stock = "UPDATE productos SET existencia = existencia - ? WHERE id = ?";
    $stmt_stock = $conn->prepare($sql_update_stock);
    
    foreach ($productos as $producto) {
        $producto_id = intval($producto['id']);
        $cantidad = intval($producto['cantidad']);
        $precio_unitario = floatval($producto['precio']);
        
        // Verificar stock disponible
        $sql_check = "SELECT existencia FROM productos WHERE id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $producto_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($row = $result_check->fetch_assoc()) {
            if ($row['existencia'] < $cantidad) {
                throw new Exception("Stock insuficiente para el producto ID: $producto_id");
            }
        } else {
            throw new Exception("Producto no encontrado ID: $producto_id");
        }
        
        // Insertar detalle
        $stmt_detalle->bind_param("iiid", $venta_id, $producto_id, $cantidad, $precio_unitario);
        if (!$stmt_detalle->execute()) {
            throw new Exception("Error al guardar detalle: " . $stmt_detalle->error);
        }
        
        // Actualizar stock
        $stmt_stock->bind_param("ii", $cantidad, $producto_id);
        if (!$stmt_stock->execute()) {
            throw new Exception("Error al actualizar stock: " . $stmt_stock->error);
        }
        
        // Registrar movimiento de stock
        $sql_movimiento = "INSERT INTO movimientos_stock (producto_id, tipo, cantidad, fecha) VALUES (?, 'egreso', ?, ?)";
        $stmt_movimiento = $conn->prepare($sql_movimiento);
        $stmt_movimiento->bind_param("iis", $producto_id, $cantidad, $fecha_venta);
        $stmt_movimiento->execute();
    }
    
    // Registrar en historial
    $sql_historial = "INSERT INTO historial (venta_id, usuario_id, fecha) VALUES (?, ?, ?)";
    $stmt_historial = $conn->prepare($sql_historial);
    $stmt_historial->bind_param("iis", $venta_id, $usuario_id, $fecha_venta);
    $stmt_historial->execute();
    
    // Confirmar transacción
    $conn->commit();
    
    // GENERAR FACTURA EN PDF
    generarFacturaPDF($venta_id, $conn);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    
    // Escapar el mensaje para JavaScript
    $mensaje_error = addslashes($e->getMessage());
    $mensaje_error = str_replace(["\r", "\n"], ' ', $mensaje_error);
    
    echo "<script>alert('Error: " . $mensaje_error . "'); window.location.href='venta.php';</script>";
    exit;
}

function generarFacturaPDF($venta_id, $conn) {
    // Incluir la librería FPDF
    require_once 'fpdf/fpdf.php';
    
    // Obtener datos de la venta
    $sql_venta = "SELECT v.*, u.nombre as vendedor_nombre FROM ventas v 
                  LEFT JOIN usuarios u ON v.usuario_id = u.id 
                  WHERE v.id = ?";
    $stmt = $conn->prepare($sql_venta);
    $stmt->bind_param("i", $venta_id);
    $stmt->execute();
    $venta = $stmt->get_result()->fetch_assoc();
    
    if (!$venta) {
        throw new Exception("Venta no encontrada para generar PDF");
    }
    
    // Obtener detalle de la venta
    $sql_detalle = "SELECT dv.*, p.nombre as producto_nombre 
                    FROM detalle_venta dv 
                    INNER JOIN productos p ON dv.producto_id = p.id 
                    WHERE dv.venta_id = ? 
                    ORDER BY p.nombre";
    $stmt = $conn->prepare($sql_detalle);
    $stmt->bind_param("i", $venta_id);
    $stmt->execute();
    $detalle_result = $stmt->get_result();
    
    // Crear PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    
    // Encabezado de la empresa
    $pdf->Cell(0, 10, 'SUPERMERCADO XYZ', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'RUC: 1234567890001', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Direccion: Av. Principal 123', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Telefono: (04) 123-4567', 0, 1, 'C');
    $pdf->Ln(10);
    
    // Línea separadora
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(5);
    
    // Información de la factura
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'FACTURA N° ' . str_pad($venta_id, 6, '0', STR_PAD_LEFT), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Datos de la venta
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 6, 'Fecha:', 0, 0);
    $pdf->Cell(70, 6, date('d/m/Y H:i', strtotime($venta['fecha'])), 0, 0);
    $pdf->Cell(40, 6, 'Cliente:', 0, 0);
    $pdf->Cell(0, 6, $venta['cliente_nombre'], 0, 1);
    
    $pdf->Cell(50, 6, 'Vendedor:', 0, 0);
    $pdf->Cell(70, 6, $venta['vendedor_nombre'] ?: 'ID: ' . $venta['usuario_id'], 0, 1);
    $pdf->Ln(10);
    
    // Encabezado de la tabla de productos
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(20, 8, 'Cant.', 1, 0, 'C', true);
    $pdf->Cell(80, 8, 'Producto', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Precio Unit.', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Subtotal', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'ID Prod.', 1, 1, 'C', true);
    
    // Contenido de la tabla
    $pdf->SetFont('Arial', '', 9);
    $y_position = $pdf->GetY();
    
    while ($item = $detalle_result->fetch_assoc()) {
        // Verificar si necesitamos una nueva página
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            // Repetir encabezado de tabla
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(20, 8, 'Cant.', 1, 0, 'C', true);
            $pdf->Cell(80, 8, 'Producto', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Precio Unit.', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Subtotal', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'ID Prod.', 1, 1, 'C', true);
            $pdf->SetFont('Arial', '', 9);
        }
        
        $subtotal_item = $item['cantidad'] * $item['precio_unitario'];
        
        $pdf->Cell(20, 6, number_format($item['cantidad'], 0), 1, 0, 'C');
        $pdf->Cell(80, 6, substr($item['producto_nombre'], 0, 40), 1, 0, 'L');
        $pdf->Cell(30, 6, 'Q' . number_format($item['precio_unitario'], 2), 1, 0, 'R');
        $pdf->Cell(30, 6, 'Q' . number_format($subtotal_item, 2), 1, 0, 'R');
        $pdf->Cell(30, 6, $item['producto_id'], 1, 1, 'C');
    }
    
    $pdf->Ln(10);
    
    // Totales
    $pdf->SetFont('Arial', 'B', 10);
    $x_start = 130;
    
    $pdf->SetXY($x_start, $pdf->GetY());
    $pdf->Cell(35, 6, 'Subtotal:', 0, 0, 'R');
    $pdf->Cell(25, 6, 'Q' . number_format($venta['subtotal'], 2), 1, 1, 'R');
    
    $pdf->SetX($x_start);
    $pdf->Cell(35, 6, 'IVA (12%):', 0, 0, 'R');
    $pdf->Cell(25, 6, 'Q' . number_format($venta['impuesto'], 2), 1, 1, 'R');
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetX($x_start);
    $pdf->Cell(35, 8, 'TOTAL:', 0, 0, 'R');
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(25, 8, 'Q' . number_format($venta['total'], 2), 1, 1, 'R', true);
    
    $pdf->Ln(10);
    
    // Pie de página
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 5, 'Gracias por su compra', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Factura generada el ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    
    // Generar el archivo PDF
    $nombre_archivo = 'Factura_' . str_pad($venta_id, 6, '0', STR_PAD_LEFT) . '_' . date('YmdHis') . '.pdf';
    
    // Crear directorio si no existe
    if (!file_exists('facturas/')) {
        mkdir('facturas/', 0777, true);
    }
    
    $ruta_archivo = 'facturas/' . $nombre_archivo;
    
    // Guardar archivo en servidor
    $pdf->Output('F', $ruta_archivo);
    
    // Enviar archivo al navegador para descarga
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
    header('Content-Length: ' . filesize($ruta_archivo));
    readfile($ruta_archivo);
    
    // Opcional: eliminar archivo temporal después de la descarga
    // unlink($ruta_archivo);
    
    exit;
}
?>