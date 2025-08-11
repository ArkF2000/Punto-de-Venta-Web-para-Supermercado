<?php
require_once 'includes/auth.php';
if (!verificarSesion()) {
    header("Location: login.php");
    exit;
}
require_once 'includes/db.php';

// Cargar librería FPDF
require('pdf/fpdf/fpdf.php');

$venta_id = $_GET['id'] ?? null;
if (!$venta_id) {
    die("ID de venta no proporcionado.");
}

// Obtener datos de la venta
$stmt = $conn->prepare("
    SELECT v.*, u.nombre AS cajero 
    FROM ventas v 
    JOIN usuarios u ON v.usuario_id = u.id
    WHERE v.id = ?
");
$stmt->bind_param("i", $venta_id);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();

if (!$venta) {
    die("Venta no encontrada.");
}

// DEBUG: Verificar estructura de tablas primero
echo "<h3>DEBUG INFO:</h3>";
echo "<p><strong>Venta ID:</strong> " . $venta_id . "</p>";

// Verificar si existe la tabla detalle_venta
$tables_query = $conn->query("SHOW TABLES LIKE 'detalle_venta'");
if ($tables_query->num_rows == 0) {
    die("ERROR: La tabla 'detalle_venta' no existe. ¿Se llama de otra forma?");
}

// Verificar registros en detalle_venta para esta venta
$debug_query = $conn->query("SELECT COUNT(*) as total FROM detalle_venta WHERE venta_id = $venta_id");
$debug_result = $debug_query->fetch_assoc();
echo "<p><strong>Registros en detalle_venta para venta $venta_id:</strong> " . $debug_result['total'] . "</p>";

// Mostrar todos los registros sin JOIN para ver qué hay
$debug_detail = $conn->query("SELECT * FROM detalle_venta WHERE venta_id = $venta_id");
echo "<p><strong>Datos directos de detalle_venta:</strong></p>";
while ($row = $debug_detail->fetch_assoc()) {
    echo "<pre>" . print_r($row, true) . "</pre>";
}

// Ahora intentar con diferentes posibles nombres/estructuras
$possible_queries = [
    // Opción 1: detalle_venta con producto_id
    "SELECT dv.*, p.nombre 
     FROM detalle_venta dv 
     JOIN productos p ON dv.producto_id = p.id
     WHERE dv.venta_id = ?",
    
    // Opción 2: Si se llama detalle_ventas (plural)
    "SELECT dv.*, p.nombre 
     FROM detalle_ventas dv 
     JOIN productos p ON dv.producto_id = p.id
     WHERE dv.venta_id = ?",
     
    // Opción 3: Si el campo se llama producto_id pero con diferente nombre
    "SELECT dv.*, p.nombre 
     FROM detalle_venta dv 
     JOIN productos p ON dv.id_producto = p.id
     WHERE dv.venta_id = ?",
     
    // Opción 4: Si venta_id se llama diferente
    "SELECT dv.*, p.nombre 
     FROM detalle_venta dv 
     JOIN productos p ON dv.producto_id = p.id
     WHERE dv.id_venta = ?"
];

$detalle = [];
$query_used = "";

foreach ($possible_queries as $index => $query) {
    try {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $venta_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $temp_detalle = $result->fetch_all(MYSQLI_ASSOC);
        
        if (!empty($temp_detalle)) {
            $detalle = $temp_detalle;
            $query_used = "Consulta " . ($index + 1);
            break;
        }
    } catch (Exception $e) {
        echo "<p>Consulta " . ($index + 1) . " falló: " . $e->getMessage() . "</p>";
    }
}

echo "<p><strong>Consulta que funcionó:</strong> $query_used</p>";
echo "<p><strong>Productos encontrados:</strong> " . count($detalle) . "</p>";

if (!empty($detalle)) {
    echo "<p><strong>Detalle de productos:</strong></p>";
    foreach ($detalle as $item) {
        echo "<pre>" . print_r($item, true) . "</pre>";
    }
}

// Si llegamos aquí y no hay productos, mostrar estructura de tablas
if (empty($detalle)) {
    echo "<h4>Estructura de tabla detalle_venta:</h4>";
    $structure = $conn->query("DESCRIBE detalle_venta");
    while ($column = $structure->fetch_assoc()) {
        echo "<p>" . $column['Field'] . " - " . $column['Type'] . "</p>";
    }
    
    echo "<h4>Todas las ventas en detalle_venta:</h4>";
    $all_details = $conn->query("SELECT venta_id, COUNT(*) as items FROM detalle_venta GROUP BY venta_id");
    while ($venta_detail = $all_details->fetch_assoc()) {
        echo "<p>Venta ID: " . $venta_detail['venta_id'] . " - Items: " . $venta_detail['items'] . "</p>";
    }
    
    die("DETENIDO PARA DEBUG - No se encontraron productos");
}

// Crear PDF
class PDF extends FPDF {
    // Cabecera
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->SetTextColor(0, 100, 0);
        $this->Cell(0,15,'SUPERMERCADO Joshua',0,1,'C');
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'FACTURA DE VENTA',0,1,'C');
        $this->Ln(5);
        
        // Línea decorativa
        $this->SetDrawColor(0, 200, 100);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }

    // Pie de página
    function Footer() {
        $this->SetY(-20);
        $this->SetDrawColor(0, 200, 100);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(100);
        $this->Cell(0,10,'Gracias por tu compra - Página '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Información de la venta en dos columnas
$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(0, 100, 0);

// Columna izquierda
$pdf->Cell(50,8,'Factura No:',0,0);
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(0);
$pdf->Cell(40,8,str_pad($venta['id'], 6, '0', STR_PAD_LEFT),0,0);

// Columna derecha
$pdf->Cell(50,8,'',0,0); // Espacio
$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(0, 100, 0);
$pdf->Cell(30,8,'Fecha:',0,0);
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(0);
$pdf->Cell(0,8,date('d/m/Y H:i', strtotime($venta['fecha'])),0,1);

$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(0, 100, 0);
$pdf->Cell(50,8,'Cajero:',0,0);
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(0);
$pdf->Cell(40,8,$venta['cajero'],0,0);

$pdf->Cell(50,8,'',0,0); // Espacio
$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(0, 100, 0);
$pdf->Cell(30,8,'Cliente:',0,0);
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(0);
$pdf->Cell(0,8,($venta['cliente_nombre'] ?: 'Consumidor Final'),0,1);

$pdf->Ln(10);

// Tabla de productos - CORREGIDO
$pdf->SetFont('Arial','B',11);
$pdf->SetFillColor(230, 255, 230);
$pdf->SetTextColor(0, 100, 0);

$pdf->Cell(80,12,'PRODUCTO',1,0,'C',true);
$pdf->Cell(25,12,'CANT.',1,0,'C',true);
$pdf->Cell(35,12,'PRECIO UNIT.',1,0,'C',true);
$pdf->Cell(35,12,'TOTAL',1,1,'C',true);

$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0);
$pdf->SetFillColor(250, 250, 250);

$fill = false;
$subtotal_calculado = 0;

// Recorrer los productos - AQUÍ ESTABA EL ERROR
foreach ($detalle as $item) {
    $total_item = $item['precio_unitario'] * $item['cantidad'];
    $subtotal_calculado += $total_item;
    
    $pdf->Cell(80,10,substr($item['nombre'], 0, 35),1,0,'L',$fill);
    $pdf->Cell(25,10,$item['cantidad'],1,0,'C',$fill);
    $pdf->Cell(35,10,'Q'.number_format($item['precio_unitario'],2),1,0,'R',$fill);
    $pdf->Cell(35,10,'Q'.number_format($total_item,2),1,1,'R',$fill);
    
    $fill = !$fill; // Alternar color de fondo
}

// Verificar que los totales coincidan
if (abs($subtotal_calculado - $venta['subtotal']) > 0.01) {
    // Si hay diferencia, usar el subtotal calculado
    $venta['subtotal'] = $subtotal_calculado;
    $venta['total'] = $venta['subtotal'] + $venta['impuesto'];
}

// Totales con mejor diseño
$pdf->Ln(5);
$pdf->SetFont('Arial','',11);

// Línea separadora
$pdf->SetDrawColor(200);
$pdf->Line(120, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(3);

$pdf->Cell(120,8,'',0,0);
$pdf->Cell(35,8,'Subtotal:',0,0,'R');
$pdf->SetFont('Arial','B',11);
$pdf->Cell(35,8,'Q'.number_format($venta['subtotal'],2),0,1,'R');

$pdf->SetFont('Arial','',11);
$pdf->Cell(120,8,'',0,0);
$pdf->Cell(35,8,'IVA (12%):',0,0,'R');
$pdf->SetFont('Arial','B',11);
$pdf->Cell(35,8,'Q'.number_format($venta['impuesto'],2),0,1,'R');

// Total final destacado
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(0, 100, 0);
$pdf->SetFillColor(230, 255, 230);
$pdf->Cell(120,12,'',0,0);
$pdf->Cell(35,12,'TOTAL:',1,0,'R',true);
$pdf->Cell(35,12,'Q'.number_format($venta['total'],2),1,1,'R',true);

// Mensaje final
$pdf->Ln(10);
$pdf->SetFont('Arial','I',10);
$pdf->SetTextColor(100);
$pdf->Cell(0,8,'Esta factura es válida sin firma ni sello',0,1,'C');

$pdf->Output('D', 'Factura_' . str_pad($venta['id'], 6, '0', STR_PAD_LEFT) . '.pdf');
?>