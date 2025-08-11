<?php
require_once 'includes/auth.php';
include 'includes/menu.php';

if (!verificarSesion() || $_SESSION['rol_id'] != 2) {
    header("Location: login.php");
    exit;
}
require_once 'includes/db.php';

// Obtener productos disponibles (existencia > 0)
$productos = $conn->query("SELECT * FROM productos WHERE existencia > 0");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Generar Venta</title>
    <link rel="stylesheet" href="assets/css/estilos.css" />
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fa;
            color: #2d3e50;
            margin: 0;
            padding: 0px;
        }

        .contenedor-venta {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .encabezado-formulario {
            text-align: center;
        }

        .encabezado-formulario h2 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #34495e;
        }

        form {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .columna {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        label {
            font-weight: 600;
        }

        input[type="text"], input[type="number"], input[type="file"], select {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            width: 100%;
        }

        .qr-box {
            padding: 15px;
            background-color: #f0f4f8;
            border-left: 4px solid #2980b9;
            border-radius: 8px;
        }

        .acciones {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        button {
            background-color: #3498db;
            border: none;
            padding: 12px 22px;
            border-radius: 6px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: #217dbb;
        }

        table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #f2f2f2;
            font-weight: 600;
        }

        .resumen-total {
            margin-top: 20px;
            background-color: #fdfdfd;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #dce3ea;
            max-width: 350px;
        }

        .resumen-total p {
            margin: 6px 0;
            font-size: 15px;
        }

        .resumen-total strong {
            font-size: 18px;
            color: #2c3e50;
        }

        .preview-image {
            max-width: 100%;
            margin-top: 10px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="contenedor-venta">
        <div class="encabezado-formulario">
            <h2>Registrar una nueva venta</h2>
        </div>

        <form id="ventaForm" method="POST" action="venta_guardar.php">
            <div class="columna">
                <label for="cliente">Cliente:</label>
                <input type="text" name="cliente" id="cliente" placeholder="Ej. Juan Pérez">

                <label for="producto">Producto:</label>
                <select id="producto">
                    <option value="">-- Escoge un producto --</option>
                    <?php while ($p = $productos->fetch_assoc()): ?>
                        <option value="<?= $p['id'] ?>"
                            data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                            data-precio="<?= $p['precio_venta'] ?>"
                            data-stock="<?= $p['existencia'] ?>">
                            <?= htmlspecialchars($p['nombre']) ?> - Q<?= number_format($p['precio_venta'], 2) ?> (<?= $p['existencia'] ?> uds)
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="cantidad">Cantidad:</label>
                <input type="number" id="cantidad" min="1" value="1">

                <div class="acciones">
                    <button type="button" onclick="agregarProducto()">Agregar</button>
                </div>
            </div>

            <div class="columna">
                <div class="qr-box">
                    <label for="imagen_qr">Escaneo por imagen QR:</label>
                    <input type="file" id="imagen_qr" accept="image/*" />
                    <div id="qr-preview"></div>
                </div>

                <div class="resumen-total">
                    <p>Subtotal: Q<span id="subtotal">0.00</span></p>
                    <p>IVA (<span id="iva_porcentaje">--</span>%): Q<span id="iva">0.00</span></p>
                    <p><strong>Total: Q<span id="total">0.00</span></strong></p>
                </div>

                <input type="hidden" name="detalle" id="detalleInput" />
                <button type="submit">Guardar venta</button>
            </div>
        </form>

        <table id="tablaVenta">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Unidades</th>
                    <th>Importe</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>


    <script>
        const productos = [];

        // Actualiza los totales consultando la API para impuestos
        async function actualizarTotales() {
            const subtotal = productos.reduce((acc, p) => acc + p.precio * p.cantidad, 0);

            if (subtotal === 0) {
                document.getElementById("subtotal").innerText = "0.00";
                document.getElementById("iva").innerText = "0.00";
                document.getElementById("iva_porcentaje").innerText = "--";
                document.getElementById("total").innerText = "0.00";
                return;
            }

            try {
                const response = await fetch(`api/impuestos.php?subtotal=${subtotal}`);
                const data = await response.json();

                if (!data.error) {
                    document.getElementById("subtotal").innerText = data.subtotal.toFixed(2);
                    document.getElementById("iva").innerText = data.iva.toFixed(2);
                    document.getElementById("iva_porcentaje").innerText = data.iva_porcentaje;
                    document.getElementById("total").innerText = data.total.toFixed(2);
                } else {
                    alert("Error al calcular impuestos: " + data.mensaje);
                }
            } catch (error) {
                alert("Error al conectar con la API de impuestos.");
            }
        }

        // Agrega producto al listado de venta validando stock
        function agregarProducto() {
            const select = document.getElementById("producto");
            const cantidadInput = document.getElementById("cantidad");
            const cantidad = parseInt(cantidadInput.value);
            const selected = select.options[select.selectedIndex];

            if (!selected.value || cantidad < 1) {
                alert("Seleccione un producto y cantidad válida.");
                return;
            }

            const id = selected.value;
            const nombre = selected.dataset.nombre;
            const precio = parseFloat(selected.dataset.precio);
            const stock = parseInt(selected.dataset.stock);

            // Verificar cantidad acumulada
            const existente = productos.find(p => p.id === id);
            const cantidadExistente = existente ? existente.cantidad : 0;
            const cantidadTotal = cantidad + cantidadExistente;

            if (cantidadTotal > stock) {
                alert(`No hay suficientes unidades en stock. Solo hay ${stock} disponibles.`);
                return;
            }

            if (existente) {
                existente.cantidad += cantidad;
            } else {
                productos.push({ id, nombre, precio, cantidad });
            }

            renderTabla();
            cantidadInput.value = 1;
            select.selectedIndex = 0;
        }

        // Renderiza la tabla de productos agregados
        function renderTabla() {
            const tbody = document.querySelector("#tablaVenta tbody");
            tbody.innerHTML = "";

            productos.forEach((p, index) => {
                const total = p.precio * p.cantidad;
                const fila = `
                    <tr>
                        <td>${p.nombre}</td>
                        <td>Q${p.precio.toFixed(2)}</td>
                        <td>${p.cantidad}</td>
                        <td>Q${total.toFixed(2)}</td>
                        <td><button type="button" onclick="eliminar(${index})">Eliminar</button></td>
                    </tr>
                `;
                tbody.innerHTML += fila;
            });

            // Actualizar input hidden para enviar al servidor
            document.getElementById("detalleInput").value = JSON.stringify(productos);
            actualizarTotales();
        }

        // Elimina un producto del listado
        function eliminar(index) {
            productos.splice(index, 1);
            renderTabla();
        }

        // Detectar código QR al subir imagen
        document.getElementById("imagen_qr").addEventListener("change", function (event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function () {
                // Mostrar preview
                const preview = document.getElementById("qr-preview");
                preview.innerHTML = `<img src="${reader.result}" class="preview-image" alt="QR Preview" /><p style="color: #00c2ff;">📍 Procesando QR...</p>`;

                detectarQRDesdeImagen(file);
            };
            reader.readAsDataURL(file);
        });

        // Llama a API externa para decodificar QR
        async function detectarQRDesdeImagen(file) {
            try {
                const formData = new FormData();
                formData.append('file', file);

                const response = await fetch('https://api.qrserver.com/v1/read-qr-code/', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                console.log("Respuesta API QR:", result);

                if (result && result[0] && result[0].symbol && result[0].symbol[0] && result[0].symbol[0].data) {
                    const qrData = result[0].symbol[0].data;
                    console.log("QR detectado:", qrData);
                    detectarProductoPorID(qrData);
                } else {
                    console.log("QR no válido:", result);
                    const preview = document.getElementById("qr-preview");
                    preview.innerHTML += `<p style="color: #ff6b6b; margin-top: 10px;">❌ No se detectó código QR válido.</p>`;
                }
            } catch (error) {
                console.error("Error API QR:", error);
                // Método alternativo
                detectarQRLocal(file);
            }
        }

        // Método alternativo simple para casos de error con la API
        function detectarQRLocal(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    // Crear canvas para procesar imagen (placeholder, no decodifica realmente)
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    ctx.drawImage(img, 0, 0);

                    const preview = document.getElementById("qr-preview");
                    preview.innerHTML += `
                        <p style="color: #ffeb3b; margin-top: 10px;">⚠️ No se pudo leer automáticamente.</p>
                        <p style="color: #e0f7fa; font-size: 14px;">Ingresa manualmente el ID del producto:</p>
                        <input type="number" id="manual_id" placeholder="ID del producto" style="width: 150px; margin: 5px;" />
                        <button onclick="agregarManual()" style="padding: 5px 10px;">Agregar</button>
                    `;
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        // Agrega manualmente producto usando ID ingresado
        function agregarManual() {
            const manualId = document.getElementById("manual_id").value;
            if (manualId) {
                detectarProductoPorID(manualId);
            }
        }

        // Busca producto por ID y lo agrega al carrito
        function detectarProductoPorID(producto_id) {
            const select = document.getElementById("producto");
            const cantidadInput = document.getElementById("cantidad");
            let encontrado = false;

            for (let i = 0; i < select.options.length; i++) {
                const option = select.options[i];
                if (option.value === producto_id) {
                    select.selectedIndex = i;
                    cantidadInput.value = 1;
                    agregarProducto();

                    const preview = document.getElementById("qr-preview");
                    preview.innerHTML += `<p style="color: #00ffc8; margin-top: 10px;">✅ Producto agregado: ${option.dataset.nombre}</p>`;

                    encontrado = true;
                    break;
                }
            }

            if (!encontrado) {
                alert("Producto no encontrado con ID: " + producto_id);
            }
        }
    </script>
</body>
</html>
