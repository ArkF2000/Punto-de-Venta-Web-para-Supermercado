<?php
require_once 'includes/auth.php';
include 'includes/menu.php';

// Validar sesión y rol
if (!verificarSesion() || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';

// Consultar datos para resumen
$usuarios_totales = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
$productos_totales = $conn->query("SELECT COUNT(*) AS total FROM productos")->fetch_assoc()['total'];
$ventas_totales = $conn->query("SELECT COUNT(*) AS total FROM ventas")->fetch_assoc()['total'];
$total_ingresos = $conn->query("SELECT SUM(total) AS total FROM ventas")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Principal - Admin</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <style>
        /* Estilos básicos para layout limpio y ordenado */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #e9eef3;
            margin: 0;
            color: #2a3b4c;
        }

        nav.top-bar {
            background-color: #34495e;
            padding: 18px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #ecf0f1;
        }

        .brand-info {
            display: flex;
            flex-direction: column;
        }

        .brand-info h1 {
            font-size: 24px;
            margin: 0;
        }

        .brand-info span {
            font-size: 14px;
            opacity: 0.8;
            margin-top: 4px;
        }

        a.exit-link {
            background-color: #2980b9;
            padding: 9px 16px;
            border-radius: 7px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: background-color 0.25s ease-in-out;
        }

        a.exit-link:hover {
            background-color: #1f6391;
        }

        main.dashboard-container {
            max-width: 960px;
            margin: 30px auto;
            padding: 0 25px;
        }

        main.dashboard-container h2 {
            font-weight: 600;
            font-size: 26px;
            margin-bottom: 30px;
            border-bottom: 2px solid #2980b9;
            padding-bottom: 8px;
        }

        section.overview-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: space-between;
        }

        article.stat-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
            padding: 28px 22px;
            flex: 1 1 220px;
            transition: box-shadow 0.3s ease;
            cursor: default;
        }

        article.stat-card:hover {
            box-shadow: 0 6px 18px rgba(0,0,0,0.2);
        }

        article.stat-card h3 {
            margin: 0 0 14px 0;
            font-size: 19px;
            color: #34495e;
        }

        article.stat-card p {
            font-size: 23px;
            font-weight: 700;
            color: #1a2a40;
            margin: 0;
        }
    </style>
</head>
<body>

    <nav class="top-bar">
        <div class="brand-info">
            <span>Panel de control</span>
        </div>
        <a href="logout.php" class="exit-link">Salir</a>
    </nav>

    <main class="dashboard-container">
        <h2>Vista rápida</h2>

        <section class="overview-cards">
            <article class="stat-card">
                <h3>Cantidad de usuarios</h3>
                <p><?php echo $usuarios_totales; ?></p>
            </article>

            <article class="stat-card">
                <h3>Productos disponibles</h3>
                <p><?php echo $productos_totales; ?></p>
            </article>

            <article class="stat-card">
                <h3>Ventas hechas</h3>
                <p><?php echo $ventas_totales; ?></p>
            </article>

            <article class="stat-card">
                <h3>Ingreso acumulado</h3>
                <p>Q<?php echo number_format($total_ingresos, 2); ?></p>
            </article>
        </section>
    </main>

</body>
</html>
