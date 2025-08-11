<?php
require_once 'includes/auth.php';

$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['correo'];
    $pass = $_POST['password'];

    if (login($email, $pass)) {
        if ($_SESSION['rol_id'] == 1) {
            header("Location: index.php");
        } else {
            header("Location: ventas.php");
        }
        exit;
    } else {
        $errorMsg = "Usuario o contraseña inválidos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Acceso - Sistema de Ventas</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
    <style>
        /* Contenedor principal centrado */
        .access-wrapper {
            height: 100vh;
            margin: 0;
            font-family: 'Verdana', sans-serif;
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Caja del formulario */
        .access-container {
            background: #ffffff;
            padding: 36px 40px;
            border-radius: 14px;
            box-shadow: 0 6px 15px rgba(100, 100, 100, 0.15);
            width: 100%;
            max-width: 380px;
            text-align: center;
        }

        /* Título */
        .access-header {
            font-size: 28px;
            color: #374151;
            margin-bottom: 32px;
            font-weight: 600;
            user-select: none;
        }

        /* Formulario */
        form.login-form {
            display: flex;
            flex-direction: column;
        }

        /* Campos de entrada */
        input.field-input {
            padding: 14px 18px;
            margin-bottom: 22px;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            font-size: 16px;
            color: #1e293b;
            transition: border-color 0.25s ease;
            outline: none;
        }

        input.field-input:focus {
            border-color: #2563eb;
            background-color: #f0f4ff;
        }

        /* Botón */
        button.submit-btn {
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px 0;
            font-weight: 700;
            font-size: 17px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button.submit-btn:hover {
            background-color: #1e40af;
        }

        /* Mensaje de error */
        .error-message {
            color: #dc2626;
            font-weight: 600;
            margin-top: 20px;
            user-select: none;
        }
    </style>
</head>
<body>
    <div class="access-wrapper">
        <section class="access-container">
            <h1 class="access-header">Sistema de Inventario</h1>
            <form method="POST" class="login-form" autocomplete="off">
                <input type="email" name="correo" class="field-input" placeholder="Correo electrónico" required />
                <input type="password" name="password" class="field-input" placeholder="Clave secreta" required />
                <button type="submit" class="submit-btn">Ingresar</button>
            </form>
            <?php if ($errorMsg): ?>
                <p class="error-message"><?php echo $errorMsg; ?></p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
