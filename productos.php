<?php
require_once 'includes/auth.php';
include 'includes/menu.php';

if (!verificarSesion() || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';

$productosList = $conn->query("
    SELECT p.*, c.nombre AS categoria_nombre 
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id
");

$productosArray = $productosList->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Gestión de Productos</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f9fafb;
            color: #333;
            margin: 0px;
        }

        h2 {
            text-align: center;
            font-weight: 600;
            font-size: 28px;
            color: #222;
            margin-bottom: 18px;
        }

        .btn-action {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin: 2px 4px;
            text-decoration: none;
            user-select: none;
            transition: background-color 0.3s ease;
            color: white;
        }
        .btn-add {
            background-color: #0d9488;
            margin-bottom: 18px;
        }
        .btn-add:hover {
            background-color: #115e59;
        }
        .btn-edit {
            background-color: #2563eb;
        }
        .btn-edit:hover {
            background-color: #1e40af;
        }
        .btn-del {
            background-color: #dc2626;
        }
        .btn-del:hover {
            background-color: #991b1b;
        }
        .btn-download {
            background-color: #16a34a;
            font-size: 12px;
            padding: 5px 9px;
        }
        .btn-download:hover {
            background-color: #14532d;
        }
        .btn-regenerate {
            background-color: #f59e0b;
            font-size: 12px;
            padding: 5px 9px;
            margin-left: 6px;
        }
        .btn-regenerate:hover {
            background-color: #78350f;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        thead tr {
            background-color: #0f172a;
            color: #f8fafc;
            font-weight: 700;
            text-align: center;
        }

        thead th {
            padding: 12px 16px;
        }

        tbody tr {
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: background-color 0.25s ease;
        }

        tbody tr:hover {
            background-color: #e0f2fe;
        }

        tbody td {
            padding: 14px 12px;
            text-align: center;
            vertical-align: middle;
            font-size: 14px;
            color: #334155;
        }

        .qr-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .qr-img,
        canvas.qr-canvas {
            width: 70px;
            height: 70px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: white;
        }
    </style>
</head>
<body>

    <h2>Lista de Productos</h2>

    <a href="producto_form.php" class="btn-action btn-add">+ Añadir Producto</a>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Compra (Q)</th>
                <th>Venta (Q)</th>
                <th>Stock</th>
                <th>QR</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productosArray as $producto): ?>
                <tr>
                    <td><?= htmlspecialchars($producto['nombre']) ?></td>
                    <td><?= htmlspecialchars($producto['categoria_nombre'] ?? 'No asignada') ?></td>
                    <td><?= number_format($producto['precio_compra'], 2) ?></td>
                    <td><?= number_format($producto['precio_venta'], 2) ?></td>
                    <td><?= $producto['existencia'] ?></td>
                    <td>
                        <div class="qr-wrapper">
                            <?php if (!empty($producto['qr_code_path']) && file_exists($producto['qr_code_path'])): ?>
                                <img src="<?= $producto['qr_code_path'] ?>" alt="Código QR" class="qr-img" />
                                <button class="btn-action btn-download" onclick="downloadStoredQR('<?= $producto['qr_code_path'] ?>', <?= $producto['id'] ?>)">Descargar</button>
                            <?php else: ?>
                                <canvas id="canvas_qr_<?= $producto['id'] ?>" class="qr-canvas"></canvas>
                                <button class="btn-action btn-download" onclick="downloadDynamicQR('canvas_qr_<?= $producto['id'] ?>', <?= $producto['id'] ?>)">Descargar</button>
                                <button class="btn-action btn-regenerate" onclick="regenerateQR(<?= $producto['id'] ?>)">Regenerar</button>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <a href="producto_form.php?id=<?= $producto['id'] ?>" class="btn-action btn-edit">Editar</a>
                        <a href="producto_eliminar.php?id=<?= $producto['id'] ?>" onclick="return confirm('¿Deseas eliminar este producto?');" class="btn-action btn-del">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<script>
document.addEventListener('DOMContentLoaded', () => {
    <?php foreach ($productosArray as $producto): ?>
        <?php if (empty($producto['qr_code_path']) || !file_exists($producto['qr_code_path'])): ?>
            const canvasEl_<?= $producto['id'] ?> = document.getElementById('canvas_qr_<?= $producto['id'] ?>');
            if (canvasEl_<?= $producto['id'] ?>) {
                QRCode.toCanvas(canvasEl_<?= $producto['id'] ?>, '<?= $producto['id'] ?>', {
                    width: 70,
                    height: 70,
                    colorDark: '#111827',
                    colorLight: '#f9fafb'
                }, (err) => {
                    if (err) console.error('QR error for product <?= $producto['id'] ?>:', err);
                });
            }
        <?php endif; ?>
    <?php endforeach; ?>
});

function downloadDynamicQR(canvasId, productId) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const link = document.createElement('a');
    link.href = canvas.toDataURL('image/png');
    link.download = `QR_Producto_${productId}.png`;
    link.click();
}

function downloadStoredQR(path, productId) {
    const link = document.createElement('a');
    link.href = path;
    link.download = `QR_Producto_${productId}.png`;
    link.click();
}

async function regenerateQR(productId) {
    if (!confirm('¿Regenerar código QR para este producto?')) return;

    try {
        const response = await fetch('regenerar_qr.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ producto_id: productId })
        });
        const data = await response.json();

        if (data.success) {
            alert('QR regenerado con éxito');
            location.reload();
        } else {
            alert('Error regenerando QR: ' + (data.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error(error);
        alert('Error al comunicarse con el servidor');
    }
}
</script>

</body>
</html>
