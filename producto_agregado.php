<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Confirmación: Producto Guardado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        /* Cambios menores en estilos para personalizar */
        .header-success {
            background-color: #198754 !important; /* verde bootstrap alternativo */
            color: #fff !important;
        }
        .btn-download {
            background: linear-gradient(90deg, #2563eb, #1e40af);
            color: white !important;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .btn-download:hover {
            background: linear-gradient(90deg, #1e40af, #2563eb);
            color: white !important;
        }
        .btn-action {
            border-radius: 8px;
            font-weight: 500;
        }
        .qr-img {
            max-width: 180px;
            margin-bottom: 20px;
            border: 2px solid #198754;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <main class="container py-5">
        <div class="row justify-content-center">
            <section class="col-lg-5 col-md-7 col-sm-9">
                <div class="card shadow-sm">
                    <header class="card-header header-success text-center">
                        <h3 class="mb-0">✔️ Producto registrado con éxito</h3>
                    </header>
                    <article class="card-body text-center px-4 py-5">
                        <?php
                        require_once 'includes/db.php';
                        $prodId = $_GET['id'] ?? 0;

                        // Consulta para obtener nombre y QR
                        $stmt = $conn->prepare("SELECT nombre, qr_code_path FROM productos WHERE id = ?");
                        $stmt->bind_param("i", $prodId);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $prodData = $res->fetch_assoc();
                        ?>

                        <h4 class="fw-semibold"><?= htmlspecialchars($prodData['nombre']) ?></h4>
                        <p>ID del producto: <strong><?= $prodId ?></strong></p>

                        <?php if ($prodData['qr_code_path'] && file_exists($prodData['qr_code_path'])): ?>
                            <div class="mb-4">
                                <h6 class="mb-3">Código QR disponible:</h6>
                                <img src="<?= $prodData['qr_code_path'] ?>" alt="Código QR producto" class="qr-img img-fluid mx-auto d-block" />
                                <a href="<?= $prodData['qr_code_path'] ?>" download="CodigoQR_Producto_<?= $prodId ?>.png" class="btn btn-download d-block mx-auto px-4 py-2">
                                    📲 Descargar QR
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mt-4 d-flex align-items-center" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 24px; height: 24px; margin-right: 10px;" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
                                  <path d="M8.982 1.566a1.13 1.13 0 0 0-1.964 0L.165 13.233c-.457.778.091 1.767.982 1.767h13.706c.89 0 1.438-.99.982-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                </svg>
                                <div>No se generó código QR automáticamente.</div>
                            </div>
                        <?php endif; ?>

                        <div class="mt-5 d-flex justify-content-center gap-3">
                            <a href="productos.php" class="btn btn-outline-secondary btn-action">
                                ← Regresar a lista
                            </a>
                            <a href="producto_form.php" class="btn btn-success btn-action">
                                + Nuevo producto
                            </a>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
