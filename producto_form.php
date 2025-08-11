<?php
require_once 'includes/auth.php';

if (!verificarSesion() || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';

$prodId = $_GET['id'] ?? null;
$prodData = ['nombre' => '', 'descripcion' => '', 'precio_compra' => '', 'precio_venta' => '', 'existencia' => '', 'categoria_id' => ''];

if ($prodId) {
    $result = $conn->query("SELECT * FROM productos WHERE id = $prodId");
    if ($result->num_rows) {
        $prodData = $result->fetch_assoc();
    }
}

$listaCategorias = $conn->query("SELECT * FROM categorias");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $prodId ? 'Modificar' : 'Agregar' ?> Producto</title>
    <link rel="stylesheet" href="assets/css/estilos.css" />
    <style>
        body {
            background: #f8fafc;
            font-family: 'Tahoma', sans-serif;
            color: #344054;
            margin: 0;
            padding: 30px 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .form-wrapper {
            background: #ffffff;
            padding: 36px 32px;
            border-radius: 14px;
            box-shadow: 0 3px 18px rgba(102, 102, 102, 0.15);
            max-width: 520px;
            width: 100%;
            animation: fadeInUp 0.6s ease forwards;
        }

        h1.form-title {
            font-weight: 600;
            font-size: 26px;
            margin-bottom: 28px;
            text-align: center;
            color: #1e293b;
        }

        form.product-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            padding: 14px 18px;
            font-size: 15px;
            border-radius: 8px;
            border: 1.8px solid #cbd5e1;
            background-color: #f9fafb;
            color: #1e293b;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
        }

        textarea {
            resize: vertical;
            min-height: 90px;
        }

        select {
            cursor: pointer;
        }

        select option {
            background: #ffffff;
            color: #344054;
        }

        button.submit-btn {
            margin-top: 24px;
            background-color: #2563eb;
            color: white;
            font-weight: 700;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            padding: 14px 0;
            cursor: pointer;
            transition: background-color 0.25s ease;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            user-select: none;
        }

        button.submit-btn:hover {
            background-color: #1d4ed8;
        }

        ::placeholder {
            color: #9ca3af;
            font-style: italic;
        }

        /* Animación */
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(25px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Validación básica */
        input:required:invalid,
        select:required:invalid {
            border-color: #ef4444;
        }

        input:required:valid,
        select:required:valid {
            border-color: #22c55e;
        }

        /* Responsive */
        @media (max-width: 600px) {
            body {
                padding: 20px 10px;
            }

            .form-wrapper {
                padding: 28px 24px;
            }

            h1.form-title {
                font-size: 22px;
                margin-bottom: 24px;
            }
        }
    </style>
</head>
<body>

    <div class="form-wrapper">
        <h1 class="form-title"><?= $prodId ? '📝 Modificar' : '➕ Agregar' ?> Producto</h1>
        <form method="POST" action="producto_guardar.php" class="product-form" autocomplete="off">
            <input type="hidden" name="id" value="<?= $prodId ?>" />

            <input type="text" name="nombre" placeholder="Nombre del producto" value="<?= htmlspecialchars($prodData['nombre']) ?>" required />

            <textarea name="descripcion" placeholder="Descripción (opcional)"><?= htmlspecialchars($prodData['descripcion']) ?></textarea>

            <select name="categoria_id" required>
                <option value="" disabled <?= $prodData['categoria_id'] ? '' : 'selected' ?>>Seleccione categoría</option>
                <?php while ($cat = $listaCategorias->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?= $prodData['categoria_id'] == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <input type="number" name="precio_compra" step="0.01" placeholder="Costo de compra (Q)" value="<?= $prodData['precio_compra'] ?>" required />

            <input type="number" name="precio_venta" step="0.01" placeholder="Precio de venta (Q)" value="<?= $prodData['precio_venta'] ?>" required />

            <input type="number" name="existencia" placeholder="Stock disponible" value="<?= $prodData['existencia'] ?>" required />

            <button type="submit" class="submit-btn"><?= $prodId ? 'Guardar Cambios' : 'Crear Producto' ?></button>
        </form>
    </div>

    <script>
        // Validación sencilla para precios
        document.addEventListener('DOMContentLoaded', () => {
            const compraInput = document.querySelector('input[name="precio_compra"]');
            const ventaInput = document.querySelector('input[name="precio_venta"]');

            function checkPrices() {
                const compra = parseFloat(compraInput.value) || 0;
                const venta = parseFloat(ventaInput.value) || 0;

                if (compra > 0 && venta > 0 && venta <= compra) {
                    ventaInput.style.borderColor = '#dc2626';
                    ventaInput.title = 'El precio de venta debe ser mayor al costo de compra';
                } else {
                    ventaInput.style.borderColor = '';
                    ventaInput.title = '';
                }
            }

            compraInput.addEventListener('input', checkPrices);
            ventaInput.addEventListener('input', checkPrices);
        });
    </script>

</body>
</html>
