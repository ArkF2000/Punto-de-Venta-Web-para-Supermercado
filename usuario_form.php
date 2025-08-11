<?php
require_once 'includes/auth.php';
if (!verificarSesion() || $_SESSION['rol_id'] != 1) {
    header("Location: login.php");
    exit;
}
require_once 'includes/db.php';

$uid = $_GET['id'] ?? null;
$usuarioData = ['nombre' => '', 'correo' => '', 'rol_id' => ''];
$reqPass = true;

if ($uid) {
    $result = $conn->query("SELECT * FROM usuarios WHERE id = $uid");
    if ($result->num_rows) {
        $usuarioData = $result->fetch_assoc();
        $reqPass = false; // No pedir contraseña al editar
    }
}

$rolesList = $conn->query("SELECT * FROM roles");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title><?= $uid ? 'Modificar' : 'Agregar' ?> Usuario</title>
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0; padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding-top: 40px;
            color: #1e293b;
        }
        .form-wrapper {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(100, 116, 139, 0.15);
            width: 380px;
        }
        h2 {
            text-align: center;
            margin-bottom: 24px;
            font-weight: 700;
            font-size: 24px;
            color: #0f172a;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            padding: 12px 14px;
            border: 1.8px solid #94a3b8;
            border-radius: 8px;
            font-size: 15px;
            color: #334155;
            transition: border-color 0.25s ease;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 8px #2563ebaa;
        }
        select {
            cursor: pointer;
            background-color: white;
        }
        button[type="submit"] {
            background-color: #2563eb;
            color: white;
            padding: 14px 0;
            border: none;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }
        button[type="submit"]:hover {
            background-color: #1d4ed8;
        }
        ::placeholder {
            color: #64748b;
        }
    </style>
</head>
<body>

<div class="form-wrapper">
    <h2><?= $uid ? 'Modificar' : 'Agregar' ?> Usuario</h2>
    <form method="POST" action="usuario_guardar.php" novalidate>
        <input type="hidden" name="id" value="<?= $uid ?>">
        
        <input type="text" name="nombre" placeholder="Nombre completo" value="<?= htmlspecialchars($usuarioData['nombre']) ?>" required>

        <input type="email" name="correo" placeholder="Correo electrónico" value="<?= htmlspecialchars($usuarioData['correo']) ?>" required>

        <select name="rol_id" required>
            <option value="" disabled <?= !$usuarioData['rol_id'] ? 'selected' : '' ?>>-- Elegir rol --</option>
            <?php while ($rol = $rolesList->fetch_assoc()): ?>
                <option value="<?= $rol['id'] ?>" <?= $usuarioData['rol_id'] == $rol['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($rol['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <input
            type="password"
            name="password"
            placeholder="<?= $reqPass ? 'Contraseña' : 'Contraseña (opcional para cambiar)' ?>"
            <?= $reqPass ? 'required' : '' ?>
            autocomplete="new-password"
        >

        <button type="submit"><?= $uid ? 'Guardar Cambios' : 'Registrar Usuario' ?></button>
    </form>
</div>

</body>
</html>
