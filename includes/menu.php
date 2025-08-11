<?php 
// Iniciar sesión si no existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si hay sesión activa
if (!verificarSesion()) {
    header("Location: login.php");
    exit;
}
?>

<style>
    nav.main-nav {
        background: linear-gradient(90deg, #2b2f3a, #3a4a63);
        padding: 20px 28px;
        font-family: 'Arial', sans-serif;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 3px 10px rgba(50, 150, 200, 0.15);
        border-bottom: 3px solid #5aa9e6;
    }

    .logo-section {
        font-size: 26px;
        font-weight: 700;
        color: #5aa9e6;
        letter-spacing: 2px;
        font-style: italic;
    }

    .menu-links {
        display: flex;
        gap: 24px;
        align-items: center;
    }

    .menu-links a {
        color: #e1e6f0;
        font-size: 17px;
        text-decoration: none;
        padding: 7px 16px;
        border-radius: 6px;
        border: 1.5px solid transparent;
        transition: all 0.25s ease-in-out;
    }

    .menu-links a:hover {
        background-color: rgba(90, 169, 230, 0.15);
        border-color: #5aa9e6;
        box-shadow: 0 0 8px #5aa9e6;
        color: #5aa9e6;
        transform: scale(1.08);
    }

    .logout-link {
        margin-left: 28px;
        font-weight: 600;
        color: #f95f62;
        cursor: pointer;
        transition: color 0.3s ease, text-shadow 0.3s ease;
    }

    .logout-link:hover {
        color: #fbb6b8;
        text-shadow: 0 0 7px #f95f62;
    }
</style>

<nav class="main-nav">
    <div class="logo-section">SuperMarket Lipe</div>
    <div class="menu-links">
        <a href="index.php">Panel</a>

        <?php if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1): // Admin ?>
            <a href="usuarios.php">Gestión Usuarios</a>
            <a href="productos.php">Catálogo</a>
            <a href="ventas.php">Historial Ventas</a>
        <?php elseif (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2): // Cajero ?>
            <a href="ventas.php">Registrar Venta</a>
        <?php endif; ?>

        <a href="logout.php" class="logout-link">Salir</a>
    </div>
</nav>
