<?php
require_once 'includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT id, nombre, precio_venta FROM productos WHERE id = $id AND existencia > 0 LIMIT 1";
$res = $conn->query($sql);

if ($producto = $res->fetch_assoc()) {
    echo json_encode($producto);
} else {
    echo json_encode(null);
}
