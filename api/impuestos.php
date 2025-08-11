<?php
header('Content-Type: application/json');

// Parámetros esperados (pueden venir por GET o POST)
$subtotal = isset($_REQUEST['subtotal']) ? floatval($_REQUEST['subtotal']) : null;

if ($subtotal === null || $subtotal < 0) {
    echo json_encode([
        'error' => true,
        'mensaje' => 'Parámetro subtotal inválido o no enviado.'
    ]);
    exit;
}

// Porcentaje IVA fijo (12%)
$iva_porcentaje = 12;

// Calcular IVA y total
$iva = $subtotal * ($iva_porcentaje / 100);
$total = $subtotal + $iva;

echo json_encode([
    'error' => false,
    'subtotal' => round($subtotal, 2),
    'iva_porcentaje' => $iva_porcentaje,
    'iva' => round($iva, 2),
    'total' => round($total, 2)
]);
